<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;

class NewOrder extends Component
{
    // Only payment type still uses wire:model (select element)
    public string $paymentType = 'Cash';

    // UI flags
    public bool   $orderCreated = false;
    public string $orderError   = '';

    // ── Decode the full form payload passed from Alpine ────────────────────────
    private function parsePayload(string $json = '{}'): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    // ── Compute all totals from the payload ───────────────────────────────────
    private function computeTotals(array $payload): array
    {
        $pkgs   = $payload['pkgs']   ?? ['p199' => 0, 'p269' => 0, 'p349' => 0];
        $addons = $payload['addons'] ?? ['icedTea' => 0, 'cheese' => 0];
        $qtys   = $payload['qtys']   ?? [];

        $people199 = max(0, (int) ($pkgs['p199'] ?? 0));
        $people269 = max(0, (int) ($pkgs['p269'] ?? 0));
        $people349 = max(0, (int) ($pkgs['p349'] ?? 0));

        $icedTeaPeople   = max(0, (int) ($addons['icedTea'] ?? 0));
        $cheesePeople    = max(0, (int) ($addons['cheese']  ?? 0));

        $discountPercent = max(0, min(100, (float) ($payload['discount'] ?? 0)));

        $totalPeople     = $people199 + $people269 + $people349;
        $packageSubtotal = ($people199 * 199) + ($people269 * 269) + ($people349 * 349);
        $addonSubtotal   = ($icedTeaPeople * 25) + ($cheesePeople * 25);

        // Extras
        $extraProducts   = Product::extras()->get()->keyBy('id');
        $extrasSubtotal  = 0;
        $extrasBreakdown = [];

        foreach ($qtys as $id => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) continue;
            $product = $extraProducts->get($id);
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

        $subtotal       = $packageSubtotal + $addonSubtotal + $extrasSubtotal;
        $discountAmount = round($subtotal * ($discountPercent / 100), 2);
        $total          = round($subtotal - $discountAmount, 2);

        $packagesBreakdown = [];
        if ($people199 > 0) $packagesBreakdown[] = ['name' => 'Basic',   'price' => 199, 'people' => $people199, 'amount' => $people199 * 199];
        if ($people269 > 0) $packagesBreakdown[] = ['name' => 'Premium', 'price' => 269, 'people' => $people269, 'amount' => $people269 * 269];
        if ($people349 > 0) $packagesBreakdown[] = ['name' => 'Deluxe',  'price' => 349, 'people' => $people349, 'amount' => $people349 * 349];

        $addonsBreakdown = [];
        if ($icedTeaPeople > 0) $addonsBreakdown[] = ['name' => 'Unlimited Iced Tea', 'price' => 25, 'people' => $icedTeaPeople, 'amount' => $icedTeaPeople * 25];
        if ($cheesePeople  > 0) $addonsBreakdown[] = ['name' => 'Unlimited Cheese',   'price' => 25, 'people' => $cheesePeople,  'amount' => $cheesePeople  * 25];

        return compact(
            'totalPeople', 'subtotal', 'discountAmount', 'total', 'discountPercent',
            'packagesBreakdown', 'addonsBreakdown',
            'extrasBreakdown', 'extrasSubtotal',
            'people199', 'people269', 'people349',
            'icedTeaPeople', 'cheesePeople'
        );
    }

    // ── Create order — all data passed as JSON from Alpine ────────────────────
    public function syncAndCreate(string $payloadJson = '{}'): void
    {
        $this->orderError = '';

        $payload = $this->parsePayload($payloadJson);
        $data    = $this->computeTotals($payload);

        // Payment type from the payload or fallback to component property
        $payment = trim($payload['payment'] ?? $this->paymentType);
        if (!in_array($payment, ['Cash', 'QRPH'])) {
            $payment = 'Cash';
        }

        if ($data['totalPeople'] === 0) {
            $this->orderError = 'Please add at least one person to the order.';
            return;
        }

        $addons = array_map(
            fn($a) => ['name' => $a['name'], 'price' => $a['price'], 'people' => $a['people']],
            $data['addonsBreakdown']
        );

        $extraItems = array_map(
            fn($e) => ['name' => $e['name'], 'category' => $e['category'], 'price' => $e['price'], 'qty' => $e['qty'], 'amount' => $e['amount']],
            $data['extrasBreakdown']
        );

        Order::create([
            'total_people'     => $data['totalPeople'],
            'packages'         => $data['packagesBreakdown'],
            'addons'           => array_values($addons),
            'extra_items'      => array_values($extraItems),
            'subtotal'         => $data['subtotal'],
            'discount_percent' => $data['discountPercent'],
            'discount_amount'  => $data['discountAmount'],
            'total'            => $data['total'],
            'payment'          => $payment,
            'status'           => 'active',
        ]);

        // Deduct stock for each sold extra
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
                'notes'          => 'Sold via order',
            ]);
        }

        $this->paymentType  = 'Cash';
        $this->orderCreated = true;

        // Dispatch the full order data directly so the receipt modal can open
        // immediately without a second Livewire round-trip on the Receipts component.
        $created = Order::active()->latest()->first();
        $this->dispatch('order-created', order: [
            'id'               => $created->id,
            'receipt_number'   => $created->receipt_number,
            'created_at'       => $created->created_at->format('m/d/Y, g:i:s A'),
            'payment'          => $created->payment,
            'total_people'     => $created->total_people,
            'packages'         => $created->packages    ?? [],
            'addons'           => $created->addons      ?? [],
            'extra_items'      => $created->extra_items ?? [],
            'subtotal'         => (float) $created->subtotal,
            'discount_percent' => (float) $created->discount_percent,
            'discount_amount'  => (float) $created->discount_amount,
            'total'            => (float) $created->total,
        ]);
    }

    public function dismissFlash(): void
    {
        $this->orderCreated = false;
        $this->orderError   = '';
    }

    // ── Render ────────────────────────────────────────────────────────────────
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
