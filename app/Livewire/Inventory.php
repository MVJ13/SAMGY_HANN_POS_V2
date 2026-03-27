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
                'is_low_stock'    => (bool)   $p->is_low_stock,
                'is_available_now' => (bool)   $p->is_available_now,
                'available_from'   => $p->available_from  ? $p->available_from->format('Y-m-d')  : '',
                'available_until'  => $p->available_until ? $p->available_until->format('Y-m-d') : '',
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
                'unit_cost'      => (float) $m->unit_cost,
                'total_cost'     => round((float) $m->quantity * (float) $m->unit_cost, 2),
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
    #[On('order-cancelled')]
    public function refreshFromCancellation(): void
    {
        $this->syncAll();
    }

    #[Renderless]
    #[On('system-reset')]
    public function refreshFromReset(): void
    {
        $this->syncAll();
    }

    #[Renderless]
    public function saveProduct(?array $data): void
    {
        if (!$data) return;

        $id      = $data['id']      ?? null;
        $isExtra = (bool) ($data['is_extra'] ?? false);

        // Bug C fix: validate date strings before persisting — malformed dates cause a DB error,
        // and available_until must not be before available_from.
        $availableFrom  = null;
        $availableUntil = null;

        if (!empty($data['available_from'])) {
            try {
                $availableFrom = \Carbon\Carbon::createFromFormat('Y-m-d', $data['available_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                $this->dispatch('product-save-error', message: 'Invalid "Available From" date format.');
                return;
            }
        }

        if (!empty($data['available_until'])) {
            try {
                $availableUntil = \Carbon\Carbon::createFromFormat('Y-m-d', $data['available_until'])->format('Y-m-d');
            } catch (\Exception $e) {
                $this->dispatch('product-save-error', message: 'Invalid "Available Until" date format.');
                return;
            }
        }

        if ($availableFrom && $availableUntil && $availableUntil < $availableFrom) {
            $this->dispatch('product-save-error', message: '"Available Until" must be on or after "Available From".');
            return;
        }

        $validated = [
            // Fix #2: strip_tags prevents stored XSS in product names surfacing in reports/receipts
            'name'          => strip_tags(trim($data['name']     ?? '')),
            'category'      => strip_tags(trim($data['category'] ?? 'Meat')),
            'unit'          => strip_tags(trim($data['unit']     ?? '')),
            'stock'         => max(0, (float) ($data['stock']         ?? 0)),
            'cost'          => max(0, (float) ($data['cost']          ?? 0)),
            'reorder_level' => max(0, (float) ($data['reorder_level'] ?? 10)),
            'is_extra'       => $isExtra,
            'selling_price'  => $isExtra ? max(0, (float) ($data['selling_price'] ?? $data['cost'] ?? 0)) : 0,
            'available_from' => $availableFrom,
            'available_until'=> $availableUntil,
        ];

        if (empty($validated['name'])) {
            $this->dispatch('product-save-error', message: 'Product name is required.');
            return;
        }
        if (empty($validated['unit'])) {
            $this->dispatch('product-save-error', message: 'Unit is required.');
            return;
        }

        \DB::transaction(function () use ($id, $validated) {
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
                        'unit_cost'      => (float) $product->cost,
                        'previous_stock' => $oldStock,
                        'new_stock'      => $validated['stock'],
                        'notes'          => 'Manual adjustment via edit',
                    ]);
                }
            } else {
                $product = Product::create($validated);
                if ($validated['stock'] > 0) {
                    StockMovement::create([
                        'product_id'     => $product->id,
                        'product_name'   => $product->name,
                        'type'           => 'in',
                        'quantity'       => $validated['stock'],
                        'unit_cost'      => (float) $validated['cost'],
                        'previous_stock' => 0,
                        'new_stock'      => $validated['stock'],
                        'notes'          => 'Initial stock',
                    ]);
                }
            }
        });

        $this->dispatch('inventory-flash', message: $id ? '✅ Product updated!' : '✅ Product added!');

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

        \DB::transaction(function () use ($productId, $type, $qty, $notes) {
            $product  = Product::findOrFail($productId);
            $prev     = (float) $product->stock;
            $newStock = $type === 'in' ? $prev + $qty : max(0, $prev - $qty);

            $product->update(['stock' => $newStock]);
            StockMovement::create([
                'product_id'     => $product->id,
                'product_name'   => $product->name,
                'type'           => $type,
                'quantity'       => $qty,
                'unit_cost'      => (float) $product->cost,
                'previous_stock' => $prev,
                'new_stock'      => $newStock,
                'notes'          => $notes ?: ($type === 'in' ? 'Stock added' : 'Stock removed'),
            ]);
        });

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
            // When enabling as an extra, keep any existing selling_price if already set;
            // otherwise default to 0 so the admin is forced to set a real sell price.
            // Never auto-default to cost price — that would mean selling at zero profit.
            'selling_price' => $nowExtra ? max((float) $product->selling_price, 0) : 0,
        ]);
        $this->dispatch('inventory-flash', message: $nowExtra
            ? "✅ {$product->name} added to extras menu! Set a selling price to earn profit."
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

    #[Renderless]
    public function resetAllStock(): void
    {
        \DB::transaction(function () {
            $products = Product::all();
            foreach ($products as $product) {
                $prev = (float) $product->stock;
                if ($prev > 0) {
                    $product->update(['stock' => 0]);
                    StockMovement::create([
                        'product_id'     => $product->id,
                        'product_name'   => $product->name,
                        'type'           => 'out',
                        'quantity'       => $prev,
                        'unit_cost'      => (float) $product->cost,
                        'previous_stock' => $prev,
                        'new_stock'      => 0,
                        'notes'          => 'Stock reset to zero',
                    ]);
                }
            }
        });
        $this->dispatch('inventory-flash', message: '🔄 All stock reset to zero!');
        $this->dispatch('extras-updated');
        $this->syncAll();
    }

    #[Renderless]
    public function deleteAllProducts(): void
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

        $this->dispatch('inventory-flash', message: '🗑️ All products deleted.');
        $this->dispatch('extras-updated');
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
