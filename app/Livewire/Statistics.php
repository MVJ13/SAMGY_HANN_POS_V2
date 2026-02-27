<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\On;

class Statistics extends Component
{
    public string $historyTab = 'all';

    public function setHistoryTab(string $tab): void
    {
        $this->historyTab = $tab;
    }

    // Auto-refresh whenever a new order is created from the NewOrder component
    #[On('order-created')]
    public function refresh(): void
    {
        // Triggering a re-render is enough — render() fetches fresh data
    }

    public function render()
    {
        // Order history filtered by tab
        $allRecentOrders = Order::latest('created_at')->take(100)->get();
        $recentOrders = $this->historyTab === 'all'
            ? $allRecentOrders->take(50)
            : $allRecentOrders->where('status', $this->historyTab)->take(50)->values();

        // Financial totals — completed orders only (confirmed revenue)
        $totals = Order::completed()->selectRaw('
            COUNT(*)                       AS total_orders,
            COALESCE(SUM(total), 0)        AS total_revenue,
            COALESCE(SUM(total_people), 0) AS total_customers
        ')->first();

        $totalOrders    = (int)   $totals->total_orders;
        $totalRevenue   = (float) $totals->total_revenue;
        $totalCustomers = (int)   $totals->total_customers;
        $avgOrder       = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        // Revenue by day (last 14 days) — completed orders only
        $driver   = \DB::getDriverName();
        $dateExpr = "DATE(completed_at)";

        $revenueByDay = Order::completed()
            ->where('completed_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw("{$dateExpr} as day, SUM(total) as revenue, COUNT(*) as orders")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $dailyLabels  = [];
        $dailyRevenue = [];
        $dailyOrders  = [];
        for ($i = 13; $i >= 0; $i--) {
            $date           = now()->subDays($i)->format('Y-m-d');
            $dailyLabels[]  = now()->subDays($i)->format('M j');
            $dailyRevenue[] = $revenueByDay->has($date) ? (float) $revenueByDay[$date]->revenue : 0;
            $dailyOrders[]  = $revenueByDay->has($date) ? (int)   $revenueByDay[$date]->orders  : 0;
        }

        // Revenue by hour today — completed orders, DB-agnostic (MySQL and SQLite)
        $hourExpr = $driver === 'sqlite'
            ? "CAST(strftime('%H', completed_at) AS INTEGER)"
            : "HOUR(completed_at)";

        $hourlyData = Order::completed()
            ->whereDate('completed_at', today())
            ->selectRaw("{$hourExpr} as hour, SUM(total) as revenue, COUNT(*) as orders")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $hourlyLabels  = [];
        $hourlyRevenue = [];
        for ($h = 7; $h <= 22; $h++) {
            $hourlyLabels[]  = ($h % 12 ?: 12) . ($h >= 12 ? 'PM' : 'AM');
            $hourlyRevenue[] = $hourlyData->has($h) ? (float) $hourlyData[$h]->revenue : 0;
        }

        // Payment breakdown — completed + active orders (exclude cancelled)
        // This ensures QRPH and Cash orders show up even before being marked paid
        $paymentBreakdown = Order::whereIn('status', ['active', 'completed'])
            ->selectRaw("payment, COUNT(*) as cnt, SUM(total) as total")
            ->groupBy('payment')
            ->get();

        // Package popularity — active + completed orders (exclude cancelled)
        $packageCounts = ['Basic' => 0, 'Premium' => 0, 'Deluxe' => 0];
        Order::whereNotNull('packages')
            ->whereIn('status', ['active', 'completed'])
            ->select('packages')
            ->get()
            ->each(function ($order) use (&$packageCounts) {
                foreach (($order->packages ?? []) as $pkg) {
                    $name = $pkg['name'] ?? '';
                    if (isset($packageCounts[$name])) {
                        $packageCounts[$name] += (int) ($pkg['people'] ?? 0);
                    }
                }
            });

        // Today's stats — completed orders only (consistent with revenue figures)
        $todayStats = Order::completed()
            ->whereDate('completed_at', today())
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total),0) as revenue, COALESCE(SUM(total_people),0) as customers')
            ->first();

        // Active (unpaid) count for context
        $activeOrdersCount = Order::active()->count();

        $chartData = [
            'daily'    => ['labels' => $dailyLabels, 'revenue' => $dailyRevenue, 'orders' => $dailyOrders],
            'hourly'   => ['labels' => $hourlyLabels, 'revenue' => $hourlyRevenue],
            'payment'  => $paymentBreakdown->map(fn($p) => [
                'label' => $p->payment,
                'cnt'   => (int) $p->cnt,
                'total' => (float) $p->total,
            ])->values(),
            'packages' => $packageCounts,
        ];

        $historyTab = $this->historyTab;

        return view('livewire.statistics', compact(
            'totalOrders', 'totalRevenue', 'totalCustomers', 'avgOrder',
            'recentOrders', 'chartData', 'todayStats', 'activeOrdersCount', 'historyTab'
        ));
    }
}
