<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class Statistics extends Component
{
    public function render()
    {
        // Single query — sort by completed_at, take most recent 50
        // and also derive aggregate stats from the same collection
        $recentOrders = Order::completed()
            ->latest('completed_at')
            ->take(50)
            ->get();

        // For accurate totals we use a separate aggregate query
        // so the 50-row limit doesn't skew the numbers
        $totals        = Order::completed()->selectRaw('
            COUNT(*)           AS total_orders,
            COALESCE(SUM(total), 0)        AS total_revenue,
            COALESCE(SUM(total_people), 0) AS total_customers
        ')->first();

        $totalOrders    = (int)   $totals->total_orders;
        $totalRevenue   = (float) $totals->total_revenue;
        $totalCustomers = (int)   $totals->total_customers;
        $avgOrder       = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        return view('livewire.statistics', compact(
            'totalOrders', 'totalRevenue', 'totalCustomers', 'avgOrder', 'recentOrders'
        ));
    }
}
