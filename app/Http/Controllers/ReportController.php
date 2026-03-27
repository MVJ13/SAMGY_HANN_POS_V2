<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function download(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        $period = $request->get('period', 'today');

        if ($period === 'specific_day' && $request->filled('date')) {
            try {
                $d = Carbon::parse($request->get('date'));
            } catch (\Exception $e) {
                $d = now();
            }
            $startDate   = $d->copy()->startOfDay();
            $endDate     = $d->copy()->endOfDay();
            $periodLabel = $d->format('F j, Y');
        } elseif ($period === 'specific_month' && $request->filled('month')) {
            try {
                $d = Carbon::parse($request->get('month') . '-01');
            } catch (\Exception $e) {
                $d = now();
            }
            $startDate   = $d->copy()->startOfMonth();
            $endDate     = $d->copy()->endOfMonth();
            $periodLabel = $d->format('F Y');
        } elseif ($period === 'week') {
            $startDate   = now()->startOfWeek();
            $endDate     = now()->endOfWeek();
            $periodLabel = 'This Week — ' . now()->startOfWeek()->format('M j') . ' to ' . now()->endOfWeek()->format('M j, Y');
        } elseif ($period === 'month') {
            $startDate   = now()->startOfMonth();
            $endDate     = now()->endOfMonth();
            $periodLabel = 'This Month — ' . now()->format('F Y');
        } elseif ($period === 'alltime') {
            $startDate   = null;
            $endDate     = null;
            $periodLabel = 'All-Time Report';
        } else {
            $startDate   = now()->startOfDay();
            $endDate     = now()->endOfDay();
            $periodLabel = 'Today — ' . now()->format('F j, Y');
        }

        $base = Order::completed();
        if ($startDate && $endDate) {
            $base->whereBetween('completed_at', [$startDate, $endDate]);
        }

        // ── Summary ──
        $summary = (clone $base)->selectRaw('
            COUNT(*)                          AS total_orders,
            COALESCE(SUM(total), 0)           AS total_revenue,
            COALESCE(SUM(total_people), 0)    AS total_guests,
            COALESCE(AVG(total), 0)           AS avg_order,
            COALESCE(SUM(discount_amount), 0) AS total_discounts,
            COALESCE(AVG(total_people), 0)    AS avg_party_size
        ')->first();

        $revenuePerGuest = ($summary->total_guests > 0)
            ? round($summary->total_revenue / $summary->total_guests, 2)
            : 0;

        // ── Payment breakdown ──
        $paymentBreakdown = (clone $base)
            ->selectRaw('payment, COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->groupBy('payment')
            ->get();

        // ── Package popularity ──
        $packageCounts = ['Basic' => 0, 'Premium' => 0, 'Deluxe' => 0];
        (clone $base)->select('packages')->get()->each(function ($order) use (&$packageCounts) {
            foreach (($order->packages ?? []) as $pkg) {
                $name = $pkg['name'] ?? '';
                if (isset($packageCounts[$name])) {
                    $packageCounts[$name] += (int) ($pkg['people'] ?? 0);
                }
            }
        });

        // ── Daily revenue — PHP-side bucketing to avoid DB timezone issues ──
        $tz = config('app.timezone', 'Asia/Manila');
        $allCompletedOrders = (clone $base)->select('completed_at', 'total', 'total_people')->get();

        $dayBuckets = [];
        foreach ($allCompletedOrders as $ord) {
            $localDate = \Carbon\Carbon::parse($ord->completed_at)->setTimezone($tz)->format('Y-m-d');
            if (!isset($dayBuckets[$localDate])) {
                $dayBuckets[$localDate] = ['revenue' => 0.0, 'orders' => 0, 'guests' => 0];
            }
            $dayBuckets[$localDate]['revenue'] += (float) $ord->total;
            $dayBuckets[$localDate]['orders']++;
            $dayBuckets[$localDate]['guests']  += (int) $ord->total_people;
        }
        ksort($dayBuckets);

        $revenueByDay = collect(array_map(function($day, $data) {
            return (object) [
                'day'     => $day,
                'revenue' => round($data['revenue'], 2),
                'orders'  => $data['orders'],
                'guests'  => $data['guests'],
            ];
        }, array_keys($dayBuckets), $dayBuckets));

        // ── All orders ──
        $orders = (clone $base)->orderBy('completed_at', 'desc')->get();

        // ── PEAK HOURS ──
        // Build hour-by-hour breakdown across the whole period
        // Peak hours — bucket by PHT hour in PHP to avoid DB timezone issues
        $tz = config('app.timezone', 'Asia/Manila');
        $allOrdersForHours = (clone $base)->select('completed_at', 'total', 'total_people')->get();

        $hourBuckets = [];
        foreach ($allOrdersForHours as $ord) {
            $h = (int) \Carbon\Carbon::parse($ord->completed_at)->setTimezone($tz)->format('G');
            if (!isset($hourBuckets[$h])) {
                $hourBuckets[$h] = ['orders' => 0, 'revenue' => 0.0, 'guests' => 0];
            }
            $hourBuckets[$h]['orders']++;
            $hourBuckets[$h]['revenue'] += (float) $ord->total;
            $hourBuckets[$h]['guests']  += (int)   $ord->total_people;
        }

        $peakHours = collect();
        for ($h = 0; $h <= 23; $h++) {
            if (isset($hourBuckets[$h])) {
                $peakHours->push([
                    'label'   => ($h % 12 ?: 12) . ':00 ' . ($h >= 12 ? 'PM' : 'AM'),
                    'hour'    => $h,
                    'orders'  => $hourBuckets[$h]['orders'],
                    'revenue' => round($hourBuckets[$h]['revenue'], 2),
                    'guests'  => $hourBuckets[$h]['guests'],
                ]);
            }
        }
        $maxHourRevenue = $peakHours->max('revenue') ?: 1;
        $busiestHour    = $peakHours->sortByDesc('orders')->first();

        // ── EXTRAS & ADD-ONS BREAKDOWN ──
        $extrasMap  = [];   // name → [qty, revenue, cost, profit]
        $addonsMap  = [];

        // Build product cost map here (used for legacy order fallback AND for P&L below)
        $productCostMap = Product::pluck('cost', 'name')->toArray();

        (clone $base)->select('extra_items', 'addons')->get()->each(function ($order) use (&$extrasMap, &$addonsMap, $productCostMap) {
            foreach (($order->extra_items ?? []) as $item) {
                $name = $item['name'] ?? 'Unknown';
                if (!isset($extrasMap[$name])) $extrasMap[$name] = ['qty' => 0, 'revenue' => 0, 'cost' => 0, 'profit' => 0];
                $qty     = (int)   ($item['qty']    ?? 0);
                $revenue = (float) ($item['amount'] ?? 0);
                // Use cost_price locked in at time of sale if available (new orders),
                // otherwise fall back to current product cost (legacy orders before this fix).
                $unitCost = isset($item['cost_price'])
                    ? (float) $item['cost_price']
                    : (float) ($productCostMap[$name] ?? 0);
                $cost   = $qty * $unitCost;
                $profit = $revenue - $cost;
                $extrasMap[$name]['qty']     += $qty;
                $extrasMap[$name]['revenue'] += $revenue;
                $extrasMap[$name]['cost']    += $cost;
                $extrasMap[$name]['profit']  += $profit;
            }
            foreach (($order->addons ?? []) as $addon) {
                $name = $addon['name'] ?? 'Unknown';
                if (!isset($addonsMap[$name])) $addonsMap[$name] = ['qty' => 0, 'revenue' => 0];
                $addonsMap[$name]['qty']     += (int)   ($addon['people'] ?? 0);
                $addonsMap[$name]['revenue'] += (float) ($addon['amount'] ?? ($addon['people'] * ($addon['price'] ?? 0)));
            }
        });

        // Sort by revenue desc (arsort sorts arrays by value — use uasort for nested arrays)
        uasort($extrasMap, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        uasort($addonsMap, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        $totalExtrasRevenue  = collect($extrasMap)->sum('revenue');
        $totalExtrasCost     = collect($extrasMap)->sum('cost');
        $totalExtrasProfit   = collect($extrasMap)->sum('profit');
        $extrasMarginPct     = $totalExtrasRevenue > 0 ? round(($totalExtrasProfit / $totalExtrasRevenue) * 100, 1) : 0;
        $totalAddonsRevenue  = collect($addonsMap)->sum('revenue');

        // ── DISCOUNT TYPE BREAKDOWN ──
        $discountTypes = [
            'senior' => ['label' => 'Senior Citizen (SC)', 'emoji' => '👴', 'color' => '#16a34a', 'count' => 0, 'amount' => 0],
            'pwd'    => ['label' => 'Person w/ Disability (PWD)', 'emoji' => '♿', 'color' => '#2980b9', 'count' => 0, 'amount' => 0],
            'child'  => ['label' => 'Child / Kid', 'emoji' => '🧒', 'color' => '#e67e22', 'count' => 0, 'amount' => 0],
        ];

        $settingPrices  = Setting::packagePrices();
        $discPkgPrices  = ['p199' => $settingPrices['basic'], 'p269' => $settingPrices['premium'], 'p349' => $settingPrices['deluxe']];

        (clone $base)
            ->whereNotNull('discount_persons')
            ->select('discount_persons', 'packages')
            ->get()
            ->each(function ($order) use (&$discountTypes, $discPkgPrices) {
                foreach (($order->discount_persons ?? []) as $d) {
                    $type = $d['type'] ?? null;
                    if (!isset($discountTypes[$type])) continue;
                    $pkg    = $d['pkg'] ?? 'p199';
                    $pct    = (float) ($d['pct'] ?? ($type === 'child' ? 10 : 20));
                    $price  = $discPkgPrices[$pkg] ?? $discPkgPrices['p199'];
                    $amount = $price * ($pct / 100);
                    $discountTypes[$type]['count']++;
                    $discountTypes[$type]['amount'] += $amount;
                }
            });

        $totalDiscountedGuests = collect($discountTypes)->sum('count');
        // Filter out discount types with zero count so they don't show as empty cards
        $discountTypes = array_filter($discountTypes, fn($dt) => $dt['count'] > 0);

        // ── Opening → Closing stock snapshot ──
        // For each product:
        //   Opening stock = previous_stock of the FIRST movement in the period
        //   Closing stock = new_stock of the LAST movement in the period
        //   Products with no movements show current stock for both (unchanged)

        $allProducts = Product::orderBy('category')->orderBy('name')->get();

        $stockSnapshot = [];
        foreach ($allProducts as $product) {
            // First movement in period for this product
            $firstMov = StockMovement::where('product_name', $product->name)
                ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
                ->orderBy('created_at')
                ->first();

            // Last movement in period for this product
            $lastMov = StockMovement::where('product_name', $product->name)
                ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
                ->orderByDesc('created_at')
                ->first();

            $openingStock = $firstMov ? (float) $firstMov->previous_stock : (float) $product->stock;
            $closingStock = $lastMov  ? (float) $lastMov->new_stock        : (float) $product->stock;
            $hadMovements = $firstMov !== null;

            $stockSnapshot[] = [
                'name'          => $product->name,
                'category'      => $product->category,
                'unit'          => $product->unit,
                'opening_stock' => $openingStock,
                'closing_stock' => $closingStock,
                'net_change'    => $closingStock - $openingStock,
                'had_movements' => $hadMovements,
                'current_stock' => (float) $product->stock,
            ];
        }

        // ── Inventory snapshot ──
        $inventory           = Product::orderBy('category')->orderBy('name')->get();
        $inventoryByCategory = $inventory->groupBy('category');
        $lowStockCount       = $inventory->filter(fn($p) => $p->is_low_stock)->count();
        $outOfStockCount     = $inventory->filter(fn($p) => (float)$p->stock <= 0)->count();
        $totalInventoryValue = $inventory->sum(fn($p) => (float)$p->stock * (float)$p->cost);


        // ── PROFIT & LOSS (COGS vs Revenue) ──
        // COGS = sum of (quantity × product cost) for all 'out' stock movements
        // that were triggered by sales (notes contain 'sale' or 'order', or type='out')
        // We also track manual outs separately so owner can see the split.

        $movementsInPeriod = StockMovement::when($startDate && $endDate, fn($q) =>
                $q->whereBetween('created_at', [$startDate, $endDate])
            )->get();

        $salesCogs   = 0;  // from automatic order deductions
        $manualCogs  = 0;  // from manual stock-out adjustments
        $restockCost = 0;  // cost of stock brought IN (restocking spend)

        // $productCostMap already built above for extras fallback — reused here

        foreach ($movementsInPeriod as $mov) {
            // Use unit_cost locked in at time of movement for accurate historical COGS.
            // Fall back to current product cost for legacy movements created before this fix.
            $unitCost = (float) $mov->unit_cost > 0
                ? (float) $mov->unit_cost
                : (float) ($productCostMap[$mov->product_name] ?? 0);
            $qty      = (float) $mov->quantity;
            $notes    = strtolower($mov->notes ?? '');

            if ($mov->type === 'out') {
                // Auto-deducted by sales system: "Sold via order #XXXX"
                if (str_contains($notes, 'sold via order') || str_contains($notes, 'order #')) {
                    $salesCogs += $qty * $unitCost;
                } else {
                    // Manual removals: waste, spoilage, corrections, reset, etc.
                    $manualCogs += $qty * $unitCost;
                }
            } elseif ($mov->type === 'in') {
                // Skip initial stock, sample data, and cancel reversals
                $isSystemIn = str_contains($notes, 'initial stock')
                           || str_contains($notes, 'sample data')
                           || str_contains($notes, 'stock returned')   // cancel reversal
                           || str_contains($notes, 'stock reset');     // manual reset
                if (!$isSystemIn) {
                    $restockCost += $qty * $unitCost;
                }
            }
        }

        $totalCogs       = $salesCogs + $manualCogs;
        $grossProfit     = (float) $summary->total_revenue - $totalCogs;
        $grossMarginPct  = $summary->total_revenue > 0
            ? round(($grossProfit / (float) $summary->total_revenue) * 100, 1)
            : 0;
        $cogsPerGuest = $summary->total_guests > 0
            ? round($totalCogs / $summary->total_guests, 2)
            : 0;

        $data = [
            'periodLabel'          => $periodLabel,
            'generatedAt'          => now()->format('F j, Y \a\t g:i A'),
            'generatedBy'          => $user->name . ' (' . str_replace('_', ' ', ucwords($user->role, '_')) . ')',
            'reportStartDate'      => $startDate ? $startDate->format('F j, Y') : 'All Time',
            'reportEndDate'        => $endDate   ? $endDate->format('F j, Y')   : now()->format('F j, Y'),
            'printedOn'            => now()->format('F j, Y \a\t g:i A'),
            'summary'              => $summary,
            'revenuePerGuest'      => $revenuePerGuest,
            'paymentBreakdown'     => $paymentBreakdown,
            'packageCounts'        => $packageCounts,
            'revenueByDay'         => $revenueByDay,
            'orders'               => $orders,
            // Peak hours
            'peakHours'            => $peakHours,
            'maxHourRevenue'       => $maxHourRevenue,
            'busiestHour'          => $busiestHour,
            // Extras
            'extrasMap'            => $extrasMap,
            'addonsMap'            => $addonsMap,
            'totalExtrasRevenue'   => $totalExtrasRevenue,
            'totalExtrasCost'      => $totalExtrasCost,
            'totalExtrasProfit'    => $totalExtrasProfit,
            'extrasMarginPct'      => $extrasMarginPct,
            'totalAddonsRevenue'   => $totalAddonsRevenue,
            // Discounts
            'discountTypes'        => $discountTypes,
            'totalDiscountedGuests'=> $totalDiscountedGuests,
            // Inventory
            'stockSnapshot'        => $stockSnapshot,
            'inventory'            => $inventory,
            'inventoryByCategory'  => $inventoryByCategory,
            'lowStockCount'        => $lowStockCount,
            'outOfStockCount'      => $outOfStockCount,
            'totalInventoryValue'  => $totalInventoryValue,
            // P&L
            'salesCogs'            => $salesCogs,
            'manualCogs'           => $manualCogs,
            'totalCogs'            => $totalCogs,
            'restockCost'          => $restockCost,
            'grossProfit'          => $grossProfit,
            'grossMarginPct'       => $grossMarginPct,
            'cogsPerGuest'         => $cogsPerGuest,
        ];

        return view('reports.sales', $data);
    }
}
