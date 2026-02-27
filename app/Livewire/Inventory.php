<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;

class Inventory extends Component
{
    // Push ALL fresh data to Alpine via one event — no $refresh ever needed
    private function syncAll(): void
    {
        $products  = Product::orderBy('category')->orderBy('name')->get();
        $movements = StockMovement::latest()->take(50)->get();

        $this->dispatch('inventory-sync', data: [
            'products' => $products->map(fn($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'category'      => $p->category,
                'stock'         => (float) $p->stock,
                'unit'          => $p->unit,
                'cost'          => (float) $p->cost,
                'reorder_level' => (float) $p->reorder_level,
                'is_extra'      => (bool)  $p->is_extra,
                'selling_price' => (float) $p->selling_price,
                'is_low_stock'  => (bool)  $p->is_low_stock,
            ])->values()->toArray(),

            'lowStock' => $products
                ->filter(fn($p) => $p->is_low_stock)
                ->map(fn($p) => ['name' => $p->name, 'stock' => (float) $p->stock, 'unit' => $p->unit])
                ->values()->toArray(),

            'movements' => $movements->map(fn($m) => [
                'id'             => $m->id,
                'product_name'   => $m->product_name,
                'type'           => $m->type,
                'quantity'       => (float) $m->quantity,
                'previous_stock' => (float) $m->previous_stock,
                'new_stock'      => (float) $m->new_stock,
                'notes'          => $m->notes ?? '',
                'created_at'     => $m->created_at->format('M j, Y · g:i A'),
            ])->toArray(),
        ]);
    }

    #[Renderless]
    #[On('order-created')]
    public function refreshFromOrder(): void
    {
        $this->syncAll();
    }

    #[Renderless]
    public function saveProduct(?array $data): void
    {
        if (!$data) return;

        $id      = $data['id']      ?? null;
        $isExtra = (bool) ($data['is_extra'] ?? false);

        $validated = [
            'name'          => trim($data['name']          ?? ''),
            'category'      => trim($data['category']      ?? 'Meat'),
            'stock'         => max(0, (float) ($data['stock']         ?? 0)),
            'unit'          => trim($data['unit']          ?? ''),
            'cost'          => max(0, (float) ($data['cost']          ?? 0)),
            'reorder_level' => max(0, (float) ($data['reorder_level'] ?? 10)),
            'is_extra'      => $isExtra,
            'selling_price' => $isExtra ? max(0, (float) ($data['selling_price'] ?? $data['cost'] ?? 0)) : 0,
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
            $this->dispatch('inventory-flash', message: '✅ Product updated!');
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
            $this->dispatch('inventory-flash', message: '✅ Product added!');
        }

        $this->dispatch('extras-updated');
        $this->dispatch('close-product-modal');
        $this->syncAll();
    }

    #[Renderless]
    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->stockMovements()->delete();
        $product->delete();
        $this->dispatch('inventory-flash', message: '🗑️ Product deleted.');
        $this->dispatch('extras-updated');
        $this->syncAll();
    }

    #[Renderless]
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
        $newStock = $type === 'in' ? $prev + $qty : max(0, $prev - $qty);

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

        $this->dispatch('inventory-flash', message: '✅ Stock updated!');
        $this->dispatch('close-stock-modal');
        $this->syncAll();
    }

    #[Renderless]
    public function toggleExtraFromCard(int $id): void
    {
        $product  = Product::findOrFail($id);
        $nowExtra = !$product->is_extra;
        $product->update([
            'is_extra'      => $nowExtra,
            'selling_price' => $nowExtra ? (float) $product->cost : 0,
        ]);
        $this->dispatch('inventory-flash', message: $nowExtra
            ? "✅ {$product->name} added to extras menu!"
            : "🚫 {$product->name} removed from extras menu."
        );
        $this->dispatch('extras-updated');
        $this->syncAll();
    }

    #[Renderless]
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
        $this->dispatch('inventory-flash', message: '🔄 Sample data restored!');
        $this->syncAll();
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
