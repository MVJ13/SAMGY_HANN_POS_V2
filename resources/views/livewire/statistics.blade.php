<div>
    <h2 style="margin-bottom:20px;color:#ff6b6b;">Sales Statistics</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ $totalOrders }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₱{{ number_format($totalRevenue, 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Customers</div>
            <div class="stat-value">{{ $totalCustomers }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Average Order Value</div>
            <div class="stat-value">₱{{ number_format($avgOrder, 2) }}</div>
        </div>
    </div>

    <h3 style="margin-top:20px;margin-bottom:20px;color:#ff6b6b;">Order History</h3>

    @if($recentOrders->isEmpty())
        <div class="empty-state"><p>No completed orders yet</p></div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>People</th>
                    <th>Packages</th>
                    <th>Subtotal</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Started</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->receipt_number }}</td>
                        <td>{{ $order->total_people }}</td>
                        <td style="font-size:0.85em;">
                            @foreach($order->packages as $pkg)
                                {{ $pkg['people'] }}x {{ $pkg['name'] }} (₱{{ $pkg['price'] }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </td>
                        <td>₱{{ number_format($order->subtotal, 2) }}</td>
                        <td>
                            @if($order->discount_percent > 0)
                                <span style="color:#4caf50;">{{ $order->discount_percent }}% (-₱{{ number_format($order->discount_amount, 2) }})</span>
                            @else
                                —
                            @endif
                        </td>
                        <td style="color:#ff6b6b;font-weight:700;">₱{{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->payment }}</td>
                        <td>{{ $order->created_at->format('m/d/Y g:i A') }}</td>
                        <td>{{ $order->completed_at?->format('m/d/Y g:i A') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
