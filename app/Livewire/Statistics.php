<?php

namespace App\Livewire;

use App\Models\Order;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;

class Statistics extends Component
{
    public string $historyTab = 'all';

    public function setHistoryTab(string $tab): void
    {
        if (in_array($tab, ['all', 'active', 'completed', 'cancelled'])) {
            $this->historyTab = $tab;
        }
    }

    // Auto-refresh whenever a new order is created from the NewOrder component
    #[On('order-created')]
    public function refresh(): void
    {
        // Triggering a re-render is enough — render() fetches fresh data
    }

    // Refresh when an order is marked paid — revenue counts completed orders only
    #[On('order-paid')]
    public function refreshOnPaid(): void {}

    // Refresh when an order is cancelled — keeps order history counts accurate
    #[On('order-cancelled')]
    public function refreshOnCancelled(): void {}

    // Refresh after a factory reset — all orders wiped, stats must zero out
    #[On('system-reset')]
    public function refreshOnReset(): void {}

    public function render()
    {
        $tz = config('app.timezone', 'Asia/Manila'); // Philippines time

        // now() and today() are correct because app.php timezone = Asia/Manila
        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

        // Order history filtered by tab
        $recentOrders = $this->historyTab === 'all'
            ? Order::latest('created_at')->take(50)->get()
            : Order::where('status', $this->historyTab)->latest('created_at')->take(50)->get();

        // Financial totals — completed orders only (active = unpaid, should not count as revenue)
        // Fix #5: Counting active orders inflated revenue with unpaid tables
        $totals = Order::where('status', 'completed')->selectRaw('
            COUNT(*)                       AS total_orders,
            COALESCE(SUM(total), 0)        AS total_revenue,
            COALESCE(SUM(total_people), 0) AS total_customers
        ')->first();

        $totalOrders    = (int)   $totals->total_orders;
        $totalRevenue   = (float) $totals->total_revenue;
        $totalCustomers = (int)   $totals->total_customers;
        $avgOrder       = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        // ── Revenue by day (last 14 days) ──
        // Use PHP-side bucketing to avoid DB timezone issues entirely
        // Fetch all relevant orders and group by their PH-local date in PHP
        $windowStart = now()->subDays(13)->startOfDay();
        // Fix #5: completed orders only for revenue charts
        $rawOrders = Order::where('status', 'completed')
            ->where('created_at', '>=', $windowStart)
            ->select('created_at', 'total')
            ->get();

        // Build a day bucket map keyed by Y-m-d in PHT
        $dayBuckets = [];
        foreach ($rawOrders as $order) {
            // Parse created_at in app timezone (Asia/Manila)
            $localDate = Carbon::parse($order->created_at)->setTimezone($tz)->format('Y-m-d');
            if (!isset($dayBuckets[$localDate])) {
                $dayBuckets[$localDate] = ['revenue' => 0, 'orders' => 0];
            }
            $dayBuckets[$localDate]['revenue'] += (float) $order->total;
            $dayBuckets[$localDate]['orders']++;
        }

        $dailyLabels  = [];
        $dailyRevenue = [];
        $dailyOrders  = [];
        for ($i = 13; $i >= 0; $i--) {
            $date           = now()->subDays($i)->format('Y-m-d');
            $dailyLabels[]  = now()->subDays($i)->format('M j');
            $dailyRevenue[] = isset($dayBuckets[$date]) ? round($dayBuckets[$date]['revenue'], 2) : 0;
            $dailyOrders[]  = isset($dayBuckets[$date]) ? $dayBuckets[$date]['orders']           : 0;
        }

        // ── Revenue by hour today (PHT) ──
        // Fetch today's orders using PHP-computed UTC range to avoid DB timezone issues
        // Fix #5: completed orders only for today's revenue
        $rawTodayOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->select('created_at', 'total')
            ->get();

        // Bucket by PHT hour
        $hourBuckets = [];
        foreach ($rawTodayOrders as $order) {
            $localHour = (int) Carbon::parse($order->created_at)->setTimezone($tz)->format('G');
            if (!isset($hourBuckets[$localHour])) {
                $hourBuckets[$localHour] = 0;
            }
            $hourBuckets[$localHour] += (float) $order->total;
        }

        $hourlyLabels  = [];
        $hourlyRevenue = [];
        for ($h = 0; $h <= 23; $h++) {
            $hourlyLabels[]  = ($h % 12 ?: 12) . ($h >= 12 ? 'PM' : 'AM');
            $hourlyRevenue[] = isset($hourBuckets[$h]) ? round($hourBuckets[$h], 2) : 0;
        }

        // Fix #5: Payment breakdown — completed orders only
        $paymentBreakdown = Order::where('status', 'completed')
            ->selectRaw("payment, COUNT(*) as cnt, SUM(total) as total")
            ->groupBy('payment')
            ->get();

        // Package popularity — completed orders only
        $packageCounts = ['Basic' => 0, 'Premium' => 0, 'Deluxe' => 0];
        Order::whereNotNull('packages')
            ->where('status', 'completed')
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

        // Today's KPI stats — use same PHT window
        $todayOrderCount    = count($rawTodayOrders);
        $todayRevenue       = round($rawTodayOrders->sum('total'), 2);

        $todayGuestRaw = Order::where('status', 'completed')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('total_people');

        $todayStats = (object) [
            'orders'    => $todayOrderCount,
            'revenue'   => $todayRevenue,
            'customers' => (int) $todayGuestRaw,
        ];

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
