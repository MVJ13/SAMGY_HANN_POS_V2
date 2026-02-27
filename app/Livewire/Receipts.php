<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class Receipts extends Component
{
    public string $activeTab = 'active';

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    private function orderPayload(Order $order): array
    {
        return [
            'id'               => $order->id,
            'receipt_number'   => $order->receipt_number,
            'status'           => $order->status,
            'created_at'       => $order->created_at->format('M j, Y · g:i A'),
            'completed_at'     => $order->completed_at?->format('M j, Y · g:i A') ?? '',
            'payment'          => $order->payment,
            'total_people'     => $order->total_people,
            'packages'         => $order->packages    ?? [],
            'addons'           => $order->addons      ?? [],
            'extra_items'      => $order->extra_items ?? [],
            'subtotal'         => (float) $order->subtotal,
            'discount_percent' => (float) $order->discount_percent,
            'discount_amount'  => (float) $order->discount_amount,
            'discount_persons' => $order->discount_persons ?? [],
            'total'            => (float) $order->total,
            'amount_received'  => (float) $order->amount_received,
            'change_given'     => (float) $order->change_given,
        ];
    }

    public function markAsPaid(int $orderId): void
    {
        Order::where('id', $orderId)
            ->where('status', 'active')
            ->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'updated_at'   => now(),
            ]);

        $this->activeTab = 'completed';
        $this->dispatch('close-receipt-modal');
    }

    public function cancelOrder(int $orderId): void
    {
        Order::where('id', $orderId)
            ->where('status', 'active')
            ->update([
                'status'     => 'cancelled',
                'updated_at' => now(),
            ]);

        $this->activeTab = 'cancelled';
        $this->dispatch('close-receipt-modal');
    }

    public function render()
    {
        $orders          = Order::active()->latest()->get();
        $completedOrders = Order::completed()->latest('completed_at')->take(50)->get();
        $cancelledOrders = Order::cancelled()->latest()->take(30)->get();
        $completedToday  = Order::completed()->whereDate('completed_at', today())->count();

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
