<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;

class NewOrder extends Component
{
    private function parsePayload(string $json = '{}'): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function computeTotals(array $payload): array
    {
        $pkgs   = $payload['pkgs']   ?? ['p199' => 0, 'p269' => 0, 'p349' => 0];
        $addons = $payload['addons'] ?? ['icedTea' => 0, 'cheese' => 0];
        $qtys   = $payload['qtys']   ?? [];

        $people199 = max(0, (int) ($pkgs['p199'] ?? 0));
        $people269 = max(0, (int) ($pkgs['p269'] ?? 0));
        $people349 = max(0, (int) ($pkgs['p349'] ?? 0));

        $icedTeaPeople = max(0, (int) ($addons['icedTea'] ?? 0));
        $cheesePeople  = max(0, (int) ($addons['cheese']  ?? 0));

        // Per-person discounts (senior/pwd = 20%, child = 10% off their package price)
        $discountPersons = $payload['discountPersons'] ?? [];
        $prices      = Setting::packagePrices();
        $pkgPriceMap = ['p199' => $prices['basic'], 'p269' => $prices['premium'], 'p349' => $prices['deluxe']];
        $addonPrice  = $prices['addon'];

        $totalPeople     = $people199 + $people269 + $people349;
        $packageSubtotal = ($people199 * $pkgPriceMap['p199']) + ($people269 * $pkgPriceMap['p269']) + ($people349 * $pkgPriceMap['p349']);
        $addonSubtotal   = ($icedTeaPeople * $addonPrice) + ($cheesePeople * $addonPrice);

        // Only allow available extras — prevents expired items being submitted
        $extraProducts  = Product::extras()->get()->filter(fn($p) => $p->is_available_now)->keyBy('id');
        $extrasSubtotal = 0;
        $extrasBreakdown = [];

        foreach ($qtys as $id => $qty) {
            $qty     = (int) $qty;
            if ($qty <= 0) continue;
            $product = $extraProducts->get((int) $id);
            if (!$product) continue;
            $amount           = $qty * (float) $product->selling_price;
            $extrasSubtotal  += $amount;
            $extrasBreakdown[] = [
                'id'         => $product->id,
                'name'       => $product->name,
                'category'   => $product->category,
                'cost_price' => (float) $product->cost,           // locked-in at time of sale for accurate historical P&L
                'price'      => (float) $product->selling_price,  // selling price locked-in at time of sale
                'qty'        => $qty,
                'amount'     => $amount,
            ];
        }

        $subtotal = $packageSubtotal + $addonSubtotal + $extrasSubtotal;

        // Calculate discount from per-person entries
        // Clamp: can't discount more persons per package than actually purchased
        $discountCounts = ['p199' => 0, 'p269' => 0, 'p349' => 0];
        $pkgTotals = ['p199' => $people199, 'p269' => $people269, 'p349' => $people349];
        $discountAmount = 0.0;
        $discountPersonsSanitized = [];
        foreach ($discountPersons as $dp) {
            $pkg  = $dp['pkg']  ?? '';
            $type = $dp['type'] ?? '';
            if (!isset($pkgPriceMap[$pkg])) continue;
            if (!in_array($type, ['senior', 'pwd', 'child'])) continue;
            if ($discountCounts[$pkg] >= $pkgTotals[$pkg]) continue;
            $pct = ($type === 'child') ? 10 : 20;
            $discountAmount += $pkgPriceMap[$pkg] * ($pct / 100);
            $discountCounts[$pkg]++;
            $discountPersonsSanitized[] = ['pkg' => $pkg, 'type' => $type, 'pct' => $pct];
        }
        $discountAmount  = round($discountAmount, 2);
        $discountPercent = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100, 2) : 0;
        $total           = round($subtotal - $discountAmount, 2);

        $packagesBreakdown = [];
        if ($people199 > 0) $packagesBreakdown[] = ['name' => 'Basic',   'price' => $pkgPriceMap['p199'], 'people' => $people199, 'amount' => $people199 * $pkgPriceMap['p199']];
        if ($people269 > 0) $packagesBreakdown[] = ['name' => 'Premium', 'price' => $pkgPriceMap['p269'], 'people' => $people269, 'amount' => $people269 * $pkgPriceMap['p269']];
        if ($people349 > 0) $packagesBreakdown[] = ['name' => 'Deluxe',  'price' => $pkgPriceMap['p349'], 'people' => $people349, 'amount' => $people349 * $pkgPriceMap['p349']];

        $addonsBreakdown = [];
        if ($icedTeaPeople > 0) $addonsBreakdown[] = ['name' => 'Unlimited Iced Tea', 'price' => $addonPrice, 'people' => $icedTeaPeople, 'amount' => $icedTeaPeople * $addonPrice];
        if ($cheesePeople  > 0) $addonsBreakdown[] = ['name' => 'Unlimited Cheese',   'price' => $addonPrice, 'people' => $cheesePeople,  'amount' => $cheesePeople  * $addonPrice];

        return compact(
            'totalPeople', 'subtotal', 'discountAmount', 'total', 'discountPercent',
            'packagesBreakdown', 'addonsBreakdown', 'extrasBreakdown', 'discountPersonsSanitized'
        );
    }

    #[Renderless]
    public function syncAndCreate(string $payloadJson = '{}'): void
    {
        // Always clear previous state first — dispatch event instead of property
        $this->dispatch('order-error', message: '');

        $payload = $this->parsePayload($payloadJson);
        $data    = $this->computeTotals($payload);

        $payment = trim($payload['payment'] ?? 'Cash');
        if (!in_array($payment, ['Cash', 'QRPH'])) {
            $payment = 'Cash';
        }

        $amountReceived = (float) ($payload['amount_received'] ?? 0);

        // Validation
        if ($data['totalPeople'] === 0 && empty($data['extrasBreakdown']) && empty($data['addonsBreakdown'])) {
            $this->dispatch('order-error', message: 'Please add at least one item to the order.');
            return;
        }

        if ($payment === 'Cash' && $amountReceived < $data['total']) {
            $this->dispatch('order-error', message: 'Cash received (₱' . number_format($amountReceived, 2) . ') must be at least ₱' . number_format($data['total'], 2));
            return;
        }

        // Create order + deduct stock atomically
        $created = \DB::transaction(function () use ($data, $payment, $amountReceived) {
            $order = Order::create([
                'total_people'     => $data['totalPeople'],
                'packages'         => $data['packagesBreakdown'],
                'addons'           => array_values(array_map(
                    fn($a) => ['name' => $a['name'], 'price' => $a['price'], 'people' => $a['people']],
                    $data['addonsBreakdown']
                )),
                'extra_items'      => array_values(array_map(
                    // id: for cancel stock reversal by product ID (resilient to renames)
                    // cost_price: locked-in at sale time so P&L stays accurate if costs change later
                    fn($e) => ['id' => $e['id'], 'name' => $e['name'], 'category' => $e['category'], 'cost_price' => $e['cost_price'], 'price' => $e['price'], 'qty' => $e['qty'], 'amount' => $e['amount']],
                    $data['extrasBreakdown']
                )),
                'subtotal'         => $data['subtotal'],
                'discount_percent' => $data['discountPercent'],
                'discount_amount'  => $data['discountAmount'],
                'discount_persons' => $data['discountPersonsSanitized'],
                'total'            => $data['total'],
                'payment'          => $payment,
                'amount_received'  => $payment === 'Cash' ? $amountReceived : $data['total'],
                'change_given'     => $payment === 'Cash' ? max(0, round($amountReceived - $data['total'], 2)) : 0,
                'status'           => 'active',
            ]);

            // Deduct stock for extras
            foreach ($data['extrasBreakdown'] as $item) {
                $product = Product::find($item['id']);
                if (!$product) continue;
                $prev     = (float) $product->stock;
                $newStock = max(0, $prev - $item['qty']);
                $product->update(['stock' => $newStock]);
                StockMovement::create([
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'type'           => 'out',
                    'quantity'       => $item['qty'],
                    'unit_cost'      => $item['cost_price'], // locked-in cost at time of sale
                    'previous_stock' => $prev,
                    'new_stock'      => $newStock,
                    'notes'          => 'Sold via order #' . $order->receipt_number,
                ]);
            }

            return $order;
        });

        // Push fresh stock to the frontend immediately after deducting
        $this->getExtras();

        $this->dispatch('order-created', order: [
            'id'               => $created->id,
            'receipt_number'   => $created->receipt_number,
            'status'           => 'active',
            'created_at'       => $created->created_at->format('M j, Y · g:i A'),
            'completed_at'     => '',
            'payment'          => $created->payment,
            'total_people'     => $created->total_people,
            'packages'         => $created->packages    ?? [],
            'addons'           => $created->addons      ?? [],
            'extra_items'      => $created->extra_items ?? [],
            'package_summary'  => $created->package_summary ?? '',
            'subtotal'         => (float) $created->subtotal,
            'discount_percent' => (float) $created->discount_percent,
            'discount_amount'  => (float) $created->discount_amount,
            'discount_persons' => $created->discount_persons ?? [],
            'total'            => (float) $created->total,
            'amount_received'  => (float) $created->amount_received,
            'change_given'     => (float) $created->change_given,
        ]);
    }


    #[Renderless]
    #[On('extras-updated')]
    public function getExtras(): void
    {
        $extras = Product::extras()
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->filter(fn($p) => $p->is_available_now)
            ->values()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'category' => $p->category,
                'price'    => (float) $p->selling_price,
                'stock'    => (float) $p->stock,
                'unit'     => $p->unit,
                'lowStock' => (bool) $p->is_low_stock,
            ])->values()->toArray();

        $this->dispatch('extras-sync', products: $extras);
    }

    public function render()
    {
        $extraProducts = Product::extras()
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->filter(fn($p) => $p->is_available_now)
            ->groupBy('category');

        $prices = Setting::packagePrices();
        return view('livewire.new-order', compact('extraProducts', 'prices'));
    }
}
