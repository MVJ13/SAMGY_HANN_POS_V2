<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Receipts extends Component
{
    public string $activeTab = 'active';

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['active', 'completed', 'cancelled'])) {
            $this->activeTab = $tab;
        }
    }

    // Re-render after factory reset so all order lists clear immediately
    #[On('system-reset')]
    public function refreshOnReset(): void {}

    public function markAsPaid(int $orderId): void
    {
        // Fix #1: Ensure only authorised roles can complete orders
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'cashier'])) return;

        \DB::transaction(function () use ($orderId) {
            Order::where('id', $orderId)
                ->where('status', 'active')
                ->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                    'updated_at'   => now(),
                ]);
        });

        // Notify Statistics to refresh — revenue totals count completed orders only,
        // so the stats panel must update whenever a table is marked as paid.
        $this->dispatch('order-paid');

        // Stay on active tab — the paid order will simply disappear from the list
        // because render() re-fetches only active orders for the active tab
        $this->dispatch('close-receipt-modal');
    }

    public function cancelOrder(int $orderId): void
    {
        // Fix #1: Ensure only authorised roles can cancel orders
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'cashier'])) return;

        $order = Order::where('id', $orderId)
            ->where('status', 'active')
            ->first();

        if (!$order) return;

        \DB::transaction(function () use ($order) {
            // ── Reverse stock for any extra items in this order ──
            foreach (($order->extra_items ?? []) as $item) {
                // Fix #3: Match by product_id first (resilient to renames).
                // Fall back to name lookup for any orders created before this fix.
                $product = isset($item['id'])
                    ? Product::find((int) $item['id'])
                    : Product::where('name', $item['name'])->first();

                if (!$product) continue;

                $prev     = (float) $product->stock;
                $qty      = (float) ($item['qty'] ?? 0);
                $newStock = $prev + $qty;

                $product->update(['stock' => $newStock]);

                StockMovement::create([
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'type'           => 'in',
                    'quantity'       => $qty,
                    'unit_cost'      => isset($item['cost_price']) ? (float) $item['cost_price'] : (float) $product->cost,
                    'previous_stock' => $prev,
                    'new_stock'      => $newStock,
                    'notes'          => 'Stock returned — order #' . $order->receipt_number . ' cancelled',
                ]);
            }

            $order->update([
                'status'     => 'cancelled',
                'updated_at' => now(),
            ]);
        });

        // Push fresh inventory data to the frontend
        $this->dispatch('extras-updated');
        $this->dispatch('order-cancelled'); // triggers Inventory::syncAll so the panel refreshes

        // Stay on active tab — the cancelled order disappears naturally on re-render
        $this->dispatch('close-receipt-modal');
    }

    public function render()
    {
        $orders          = Order::active()->latest()->get();

        // Fix #9: Increased limits so busy days don't silently drop records.
        // Completed orders are scoped to the last 90 days by default so the list
        // stays fast; cancelled orders are kept for 60 days.
        $completedOrders = Order::completed()
            ->latest('completed_at')
            ->take(200)
            ->get();

        $cancelledOrders = Order::cancelled()
            ->latest()
            ->take(100)
            ->get();

        $completedToday  = Order::completed()
            ->whereBetween('completed_at', [now()->startOfDay(), now()->endOfDay()])
            ->count();

        $visibleOrders = match ($this->activeTab) {
            'completed' => $completedOrders,
            'cancelled' => $cancelledOrders,
            default     => $orders,
        };

        return view('livewire.receipts', compact(
            'orders', 'completedOrders', 'cancelledOrders', 'completedToday', 'visibleOrders'
        ));
    }
}
