<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
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
        $pkgPriceMap = ['p199' => 199, 'p269' => 269, 'p349' => 349];

        $totalPeople     = $people199 + $people269 + $people349;
        $packageSubtotal = ($people199 * 199) + ($people269 * 269) + ($people349 * 349);
        $addonSubtotal   = ($icedTeaPeople * 25) + ($cheesePeople * 25);

        $extraProducts  = Product::extras()->get()->keyBy('id');
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
                'id'       => $product->id,
                'name'     => $product->name,
                'category' => $product->category,
                'price'    => (float) $product->selling_price,
                'qty'      => $qty,
                'amount'   => $amount,
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
        if ($people199 > 0) $packagesBreakdown[] = ['name' => 'Basic',   'price' => 199, 'people' => $people199, 'amount' => $people199 * 199];
        if ($people269 > 0) $packagesBreakdown[] = ['name' => 'Premium', 'price' => 269, 'people' => $people269, 'amount' => $people269 * 269];
        if ($people349 > 0) $packagesBreakdown[] = ['name' => 'Deluxe',  'price' => 349, 'people' => $people349, 'amount' => $people349 * 349];

        $addonsBreakdown = [];
        if ($icedTeaPeople > 0) $addonsBreakdown[] = ['name' => 'Unlimited Iced Tea', 'price' => 25, 'people' => $icedTeaPeople, 'amount' => $icedTeaPeople * 25];
        if ($cheesePeople  > 0) $addonsBreakdown[] = ['name' => 'Unlimited Cheese',   'price' => 25, 'people' => $cheesePeople,  'amount' => $cheesePeople  * 25];

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

        // Create the order
        $created = Order::create([
            'total_people'     => $data['totalPeople'],
            'packages'         => $data['packagesBreakdown'],
            'addons'           => array_values(array_map(
                fn($a) => ['name' => $a['name'], 'price' => $a['price'], 'people' => $a['people']],
                $data['addonsBreakdown']
            )),
            'extra_items'      => array_values(array_map(
                fn($e) => ['name' => $e['name'], 'category' => $e['category'], 'price' => $e['price'], 'qty' => $e['qty'], 'amount' => $e['amount']],
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
                'previous_stock' => $prev,
                'new_stock'      => $newStock,
                'notes'          => 'Sold via order #' . $created->receipt_number,
            ]);
        }

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
            ->groupBy('category');

        return view('livewire.new-order', compact('extraProducts'));
    }
}
