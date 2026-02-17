<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;

class Inventory extends Component
{
    public string $flashMessage = '';

    // ── Save / Delete — called from Alpine via $wire ──────────────────────────

    public function saveProduct(array $data): void
    {
        $id       = $data['id']       ?? null;
        $isExtra  = (bool) ($data['is_extra'] ?? false);

        $validated = [
            'name'          => trim($data['name']          ?? ''),
            'category'      => trim($data['category']      ?? 'Meat'),
            'stock'         => max(0, (float) ($data['stock']         ?? 0)),
            'unit'          => trim($data['unit']          ?? ''),
            'cost'          => max(0, (float) ($data['cost']          ?? 0)),
            'reorder_level' => max(0, (float) ($data['reorder_level'] ?? 10)),
            'is_extra'      => $isExtra,
            'selling_price' => $isExtra ? max(0, (float) ($data['selling_price'] ?? 0)) : 0,
        ];

        if (empty($validated['name'])) {
            $this->dispatch('product-save-error', message: 'Product name is required.');
            return;
        }
        if (empty($validated['unit'])) {
            $this->dispatch('product-save-error', message: 'Unit is required.');
            return;
        }

        if ($id) {
            $product  = Product::findOrFail($id);
            $oldStock = (float) $product->stock;
            $product->update($validated);

            if (abs($oldStock - $validated['stock']) > 0.001) {
                StockMovement::create([
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'type'           => $validated['stock'] > $oldStock ? 'in' : 'out',
                    'quantity'       => abs($validated['stock'] - $oldStock),
                    'previous_stock' => $oldStock,
                    'new_stock'      => $validated['stock'],
                    'notes'          => 'Manual adjustment via edit',
                ]);
            }
            $this->flashMessage = '✅ Product updated!';
            $this->skipRender();
        } else {
            $product = Product::create($validated);
            if ($validated['stock'] > 0) {
                StockMovement::create([
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'type'           => 'in',
                    'quantity'       => $validated['stock'],
                    'previous_stock' => 0,
                    'new_stock'      => $validated['stock'],
                    'notes'          => 'Initial stock',
                ]);
            }
            $this->flashMessage = '✅ Product added!';
        }

    }

    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->stockMovements()->delete();
        $product->delete();
        $this->flashMessage = '🗑️ Product deleted.';
        $this->skipRender();
    }

    public function saveStockMovement(array $data): void
    {
        $productId = (int) ($data['product_id'] ?? 0);
        $type      = $data['type']  ?? 'in';
        $qty       = (float) ($data['qty']   ?? 0);
        $notes     = trim($data['notes'] ?? '');

        if ($qty <= 0) {
            $this->dispatch('stock-save-error', message: 'Quantity must be greater than 0.');
            return;
        }
        if (!in_array($type, ['in', 'out'])) {
            $this->dispatch('stock-save-error', message: 'Invalid movement type.');
            return;
        }

        $product  = Product::findOrFail($productId);
        $prev     = (float) $product->stock;
        $newStock = $type === 'in' ? $prev + $qty : $prev - $qty;

        $product->update(['stock' => $newStock]);
        StockMovement::create([
            'product_id'     => $product->id,
            'product_name'   => $product->name,
            'type'           => $type,
            'quantity'       => $qty,
            'previous_stock' => $prev,
            'new_stock'      => $newStock,
            'notes'          => $notes ?: ($type === 'in' ? 'Stock added' : 'Stock removed'),
        ]);

        $this->flashMessage = '✅ Stock updated!';
        $this->skipRender();
    }

    public function toggleExtraFromCard(int $id): void
    {
        $product  = Product::findOrFail($id);
        $nowExtra = !$product->is_extra;
        $product->update([
            'is_extra'      => $nowExtra,
            'selling_price' => ($nowExtra && $product->selling_price == 0) ? $product->cost : $product->selling_price,
        ]);
        $this->flashMessage = $nowExtra
            ? "✅ {$product->name} added to extras menu!"
            : "🚫 {$product->name} removed from extras menu.";
        $this->skipRender();
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = '';
    }

    public function resetSampleData(): void
    {
        $driver = \DB::getDriverName();
        if ($driver === 'mysql') {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            \DB::statement('PRAGMA foreign_keys = OFF');
        }

        StockMovement::truncate();
        Product::truncate();

        if ($driver === 'mysql') {
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            \DB::statement('PRAGMA foreign_keys = ON');
        }

        app(\Database\Seeders\ProductSeeder::class)->run();
        $this->flashMessage = '🔄 Sample data restored!';
    }

    public function render()
    {
        $products     = Product::orderBy('category')->orderBy('name')->get();
        $lowStock     = $products->filter(fn($p) => $p->is_low_stock);
        $healthyStock = $products->reject(fn($p) => $p->is_low_stock);
        $movements    = StockMovement::latest()->take(50)->get();

        return view('livewire.inventory', compact('products', 'lowStock', 'healthyStock', 'movements'));
    }
}
