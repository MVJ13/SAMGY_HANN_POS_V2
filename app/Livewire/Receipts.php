<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class Receipts extends Component
{
    public function markAsPaid(int $orderId): void
    {
        Order::active()->where('id', $orderId)->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
        $this->skipRender();
    }

    public function cancelOrder(int $orderId): void
    {
        Order::active()->where('id', $orderId)->delete();
        $this->skipRender();
    }

    public function render()
    {
        $orders = Order::active()->latest()->get();
        return view('livewire.receipts', compact('orders'));
    }
}
