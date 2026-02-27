<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
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
            $d = Carbon::parse($request->get('date'));
            $startDate   = $d->copy()->startOfDay();
            $endDate     = $d->copy()->endOfDay();
            $periodLabel = $d->format('F j, Y');
        } elseif ($period === 'specific_month' && $request->filled('month')) {
            $d = Carbon::parse($request->get('month') . '-01');
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

        // ── Daily revenue ──
        $revenueByDay = (clone $base)
            ->selectRaw('DATE(completed_at) as day, SUM(total) as revenue, COUNT(*) as orders, SUM(total_people) as guests')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // ── All orders ──
        $orders = (clone $base)->orderBy('completed_at', 'desc')->get();

        // ── PEAK HOURS ──
        // Build hour-by-hour breakdown across the whole period
        $hourlyRows = (clone $base)
            ->selectRaw('HOUR(completed_at) as hr, COUNT(*) as orders, SUM(total) as revenue, SUM(total_people) as guests')
            ->groupBy('hr')
            ->orderBy('hr')
            ->get()
            ->keyBy('hr');

        $peakHours = collect();
        for ($h = 0; $h <= 23; $h++) {
            if ($hourlyRows->has($h)) {
                $row = $hourlyRows[$h];
                $peakHours->push([
                    'label'   => ($h % 12 ?: 12) . ':00 ' . ($h >= 12 ? 'PM' : 'AM'),
                    'hour'    => $h,
                    'orders'  => (int)   $row->orders,
                    'revenue' => (float) $row->revenue,
                    'guests'  => (int)   $row->guests,
                ]);
            }
        }
        $maxHourRevenue = $peakHours->max('revenue') ?: 1;
        $busiestHour    = $peakHours->sortByDesc('orders')->first();

        // ── EXTRAS & ADD-ONS BREAKDOWN ──
        $extrasMap  = [];   // name → [qty, revenue]
        $addonsMap  = [];

        (clone $base)->select('extra_items', 'addons')->get()->each(function ($order) use (&$extrasMap, &$addonsMap) {
            foreach (($order->extra_items ?? []) as $item) {
                $name = $item['name'] ?? 'Unknown';
                if (!isset($extrasMap[$name])) $extrasMap[$name] = ['qty' => 0, 'revenue' => 0];
                $extrasMap[$name]['qty']     += (int)   ($item['qty']    ?? 0);
                $extrasMap[$name]['revenue'] += (float) ($item['amount'] ?? 0);
            }
            foreach (($order->addons ?? []) as $addon) {
                $name = $addon['name'] ?? 'Unknown';
                if (!isset($addonsMap[$name])) $addonsMap[$name] = ['qty' => 0, 'revenue' => 0];
                $addonsMap[$name]['qty']     += (int)   ($addon['people'] ?? 0);
                $addonsMap[$name]['revenue'] += (float) ($addon['amount'] ?? ($addon['people'] * ($addon['price'] ?? 0)));
            }
        });

        // Sort by revenue desc
        arsort($extrasMap);
        arsort($addonsMap);

        $totalExtrasRevenue  = collect($extrasMap)->sum('revenue');
        $totalAddonsRevenue  = collect($addonsMap)->sum('revenue');

        // ── DISCOUNT TYPE BREAKDOWN ──
        $discountTypes = [
            'senior' => ['label' => 'Senior Citizen (SC)', 'emoji' => '👴', 'color' => '#16a34a', 'count' => 0, 'amount' => 0],
            'pwd'    => ['label' => 'Person w/ Disability (PWD)', 'emoji' => '♿', 'color' => '#2980b9', 'count' => 0, 'amount' => 0],
            'child'  => ['label' => 'Child / Kid', 'emoji' => '🧒', 'color' => '#e67e22', 'count' => 0, 'amount' => 0],
        ];

        (clone $base)
            ->whereNotNull('discount_persons')
            ->select('discount_persons', 'packages')
            ->get()
            ->each(function ($order) use (&$discountTypes) {
                $pkgPrices = ['p199' => 199, 'p269' => 269, 'p349' => 349];
                foreach (($order->discount_persons ?? []) as $d) {
                    $type = $d['type'] ?? null;
                    if (!isset($discountTypes[$type])) continue;
                    $pkg    = $d['pkg'] ?? 'p199';
                    $pct    = (float) ($d['pct'] ?? ($type === 'child' ? 10 : 20));
                    $price  = $pkgPrices[$pkg] ?? 199;
                    $amount = $price * ($pct / 100);
                    $discountTypes[$type]['count']++;
                    $discountTypes[$type]['amount'] += $amount;
                }
            });

        $totalDiscountedGuests = collect($discountTypes)->sum('count');

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
        ];

        return view('reports.sales', $data);
    }
}
