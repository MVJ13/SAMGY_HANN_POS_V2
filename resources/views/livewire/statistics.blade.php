<div x-data="{
    orderModal: { open: false, order: null },
    openOrder(order) { this.orderModal.order = order; this.orderModal.open = true; },
    closeOrder() { this.orderModal.open = false; setTimeout(() => this.orderModal.order = null, 200); }
}"
@keydown.escape.window="closeOrder()">

    {{-- ── Header bar ── --}}
    <div class="st-header">
        <div>
            <h2 class="st-title">Sales Dashboard</h2>
            @if($activeOrdersCount > 0)
                <span class="st-active-badge">⏳ {{ $activeOrdersCount }} pending</span>
            @endif
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="st-last-updated">Live · updates on new orders</span>
            <button wire:click="$refresh" class="st-refresh-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Refresh
            </button>

            {{-- Generate Report --}}
            <div x-data="{
                open: false,
                tab: 'preset',
                specificDay: '',
                specificMonth: '',
                reportUrl() {
                    if (this.tab === 'day' && this.specificDay)
                        return '/report/download?period=specific_day&date=' + this.specificDay;
                    if (this.tab === 'month' && this.specificMonth)
                        return '/report/download?period=specific_month&month=' + this.specificMonth;
                    return null;
                }
            }" style="position:relative;">
                <button @click="open = !open" class="st-report-btn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Generate Report
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>

                <div x-show="open" @click.outside="open = false" x-cloak class="st-report-modal">
                    <div class="st-report-modal-title">📄 Generate Report</div>

                    {{-- Tab switcher --}}
                    <div class="st-report-tabs">
                        <button type="button" @click="tab='preset'" :class="tab==='preset' ? 'st-rtab active' : 'st-rtab'">Quick</button>
                        <button type="button" @click="tab='day'"    :class="tab==='day'    ? 'st-rtab active' : 'st-rtab'">By Day</button>
                        <button type="button" @click="tab='month'"  :class="tab==='month'  ? 'st-rtab active' : 'st-rtab'">By Month</button>
                    </div>

                    {{-- Preset --}}
                    <div x-show="tab==='preset'" class="st-report-tab-body">
                        <a href="/report/download?period=today"   class="st-report-option" target="_blank" @click="open=false">📅 Today</a>
                        <a href="/report/download?period=week"    class="st-report-option" target="_blank" @click="open=false">📅 This Week</a>
                        <a href="/report/download?period=month"   class="st-report-option" target="_blank" @click="open=false">📅 This Month</a>
                        <a href="/report/download?period=alltime" class="st-report-option" target="_blank" @click="open=false">📋 All-Time</a>
                    </div>

                    {{-- Specific Day --}}
                    <div x-show="tab==='day'" class="st-report-tab-body">
                        <label class="st-report-input-label">Select a specific date</label>
                        <input type="date" x-model="specificDay" class="st-report-dateinput"
                               :max="new Date().toISOString().split('T')[0]">
                        <a :href="reportUrl()" target="_blank"
                           :class="specificDay ? 'st-report-go-btn' : 'st-report-go-btn disabled'"
                           :onclick="!specificDay ? 'return false;' : 'document.querySelector(\"[x-data]\").open=false'"
                           @click="specificDay && (open=false)">
                            📄 Generate Day Report
                        </a>
                    </div>

                    {{-- Specific Month --}}
                    <div x-show="tab==='month'" class="st-report-tab-body">
                        <label class="st-report-input-label">Select a month</label>
                        <input type="month" x-model="specificMonth" class="st-report-dateinput"
                               :max="new Date().toISOString().slice(0,7)">
                        <a :href="reportUrl()" target="_blank"
                           :class="specificMonth ? 'st-report-go-btn' : 'st-report-go-btn disabled'"
                           @click="specificMonth && (open=false)">
                            📄 Generate Month Report
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── KPI strip ── --}}
    <div class="st-kpi-strip">
        <div class="st-kpi">
            <div class="st-kpi-label">Today's Orders</div>
            <div class="st-kpi-val st-kpi-primary">{{ $todayStats->orders ?? 0 }}</div>
        </div>
        <div class="st-kpi-divider"></div>
        <div class="st-kpi">
            <div class="st-kpi-label">Today's Revenue</div>
            <div class="st-kpi-val st-kpi-success">₱{{ number_format($todayStats->revenue ?? 0, 0) }}</div>
        </div>
        <div class="st-kpi-divider"></div>
        <div class="st-kpi">
            <div class="st-kpi-label">Today's Guests</div>
            <div class="st-kpi-val st-kpi-warning">{{ $todayStats->customers ?? 0 }}</div>
        </div>
        <div class="st-kpi-divider"></div>
        <div class="st-kpi">
            <div class="st-kpi-label">All-time Orders</div>
            <div class="st-kpi-val">{{ $totalOrders }}</div>
        </div>
        <div class="st-kpi-divider"></div>
        <div class="st-kpi">
            <div class="st-kpi-label">All-time Revenue</div>
            <div class="st-kpi-val">₱{{ number_format($totalRevenue, 0) }}</div>
        </div>
        <div class="st-kpi-divider"></div>
        <div class="st-kpi">
            <div class="st-kpi-label">Avg Order Value</div>
            <div class="st-kpi-val">₱{{ number_format($avgOrder, 0) }}</div>
        </div>
        <div class="st-kpi-divider"></div>
        <div class="st-kpi">
            <div class="st-kpi-label">Total Guests</div>
            <div class="st-kpi-val">{{ $totalCustomers }}</div>
        </div>
    </div>

    {{-- ── Charts grid ── --}}
    <div class="st-charts-grid">

        {{-- Revenue 14 days (wide) --}}
        <div class="st-chart-card st-chart-wide">
            <div class="st-chart-title">📈 Revenue · Last 14 Days</div>
            <div class="st-chart-body">
                <canvas id="chart-revenue-daily"></canvas>
            </div>
        </div>

        {{-- Payment split --}}
        <div class="st-chart-card">
            <div class="st-chart-title">💳 Payment Split</div>
            <div class="st-chart-body st-chart-doughnut">
                <canvas id="chart-payment"></canvas>
                @if(count($chartData['payment']) === 0)
                    <div class="st-chart-empty">No data yet</div>
                @endif
            </div>
        </div>

        {{-- Hourly today --}}
        <div class="st-chart-card">
            <div class="st-chart-title">⏰ Revenue by Hour · Today</div>
            <div class="st-chart-body">
                <canvas id="chart-hourly"></canvas>
            </div>
        </div>

        {{-- Package popularity --}}
        <div class="st-chart-card">
            <div class="st-chart-title">📦 Package Popularity</div>
            <div class="st-chart-body st-chart-doughnut">
                <canvas id="chart-packages"></canvas>
            </div>
        </div>

    </div>

    {{-- ── Order History ── --}}
    <div class="st-history">
        <div class="st-history-header">
            <div class="st-chart-title" style="margin:0;">🧾 Order History</div>
            <div style="display:flex;gap:6px;">
                <button wire:click="setHistoryTab('all')"       class="{{ $historyTab==='all'       ? 'rcpt-pill rcpt-pill-active' : 'rcpt-pill' }}">All</button>
                <button wire:click="setHistoryTab('completed')" class="{{ $historyTab==='completed' ? 'rcpt-pill rcpt-pill-active' : 'rcpt-pill' }}">Paid</button>
                <button wire:click="setHistoryTab('active')"    class="{{ $historyTab==='active'    ? 'rcpt-pill rcpt-pill-active' : 'rcpt-pill' }}">Pending</button>
                <button wire:click="setHistoryTab('cancelled')" class="{{ $historyTab==='cancelled' ? 'rcpt-pill rcpt-pill-active' : 'rcpt-pill' }}">Cancelled</button>
            </div>
        </div>

        @if($recentOrders->isEmpty())
            <div class="rcpt-empty" style="padding:40px 0;">
                <div class="rcpt-empty-icon">📋</div>
                <div class="rcpt-empty-text">No orders yet</div>
            </div>
        @else
        <div class="st-table-wrap">
            <table class="st-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>Guests</th>
                        <th>Packages</th>
                        <th>Payment</th>
                        <th>Subtotal</th>
                        <th>Discount</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    @php
                        $sc = match($order->status) {
                            'completed' => ['bg'=>'rgba(6,214,160,0.15)',  'fg'=>'var(--success)', 'label'=>'✓ Paid'],
                            'cancelled' => ['bg'=>'rgba(230,57,70,0.15)',  'fg'=>'var(--danger)',  'label'=>'✕ Cancelled'],
                            default     => ['bg'=>'rgba(255,183,3,0.15)', 'fg'=>'var(--warning)', 'label'=>'⏳ Pending'],
                        };
                        // Normalise packages/addons so old orders (missing 'amount') still display correctly in modal
                        $packages = collect($order->packages ?? [])->map(fn($p) => array_merge(
                            $p, ['amount' => (float)($p['amount'] ?? ($p['people'] * $p['price']))]
                        ))->values()->toArray();
                        $addons = collect($order->addons ?? [])->map(fn($a) => array_merge(
                            $a, ['amount' => (float)($a['amount'] ?? ($a['people'] * $a['price']))]
                        ))->values()->toArray();
                    @endphp
                    <tr wire:key="stat-order-{{ $order->id }}">
                        <td><strong>#{{ $order->receipt_number }}</strong></td>
                        <td><span class="st-status-chip" style="background:{{ $sc['bg'] }};color:{{ $sc['fg'] }};">{{ $sc['label'] }}</span></td>
                        <td>{{ $order->total_people }}</td>
                        <td class="st-td-sm">
                            @if(!empty($order->packages))
                                @foreach($order->packages as $pkg){{ $pkg['people'] }}× {{ $pkg['name'] }}{{ !$loop->last ? ', ' : '' }}@endforeach
                            @else —@endif
                        </td>
                        <td>
                            <span class="st-payment-chip {{ $order->payment === 'Cash' ? 'st-cash' : 'st-qr' }}">
                                {{ $order->payment === 'Cash' ? '💵' : '📱' }} {{ $order->payment }}
                            </span>
                        </td>
                        <td>₱{{ number_format($order->subtotal, 2) }}</td>
                        <td>
                            @if($order->discount_percent > 0)
                                <span style="color:var(--success);font-weight:600;">{{ $order->discount_percent }}%</span>
                            @else —@endif
                        </td>
                        <td class="st-td-total">₱{{ number_format($order->total, 2) }}</td>
                        <td class="st-td-sm">{{ $order->created_at->format('M j · g:i A') }}</td>
                        <td>
                            <button
                                @click.stop="openOrder({{ Js::from([
                                    'receipt_number'   => $order->receipt_number,
                                    'status'           => $order->status,
                                    'created_at'       => $order->created_at->format('M j, Y · g:i A'),
                                    'total_people'     => $order->total_people,
                                    'packages'         => $packages,
                                    'addons'           => $addons,
                                    'extra_items'      => $order->extra_items ?? [],
                                    'payment'          => $order->payment,
                                    'subtotal'         => (float)$order->subtotal,
                                    'discount_percent' => (float)$order->discount_percent,
                                    'discount_amount'  => (float)$order->discount_amount,
                                    'total'            => (float)$order->total,
                                ]) }})"
                                style="background:#F5F2EF;color:var(--text-secondary);border:1px solid var(--border);border-radius:6px;padding:3px 10px;font-size:0.78em;cursor:pointer;white-space:nowrap;transition:all 0.15s;"
                                onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'"
                                onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-secondary)'"
                            >👁 View</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Chart data --}}
    <div id="stats-chart-data" data-chart='@json($chartData)' style="display:none;"></div>

    <script>
    (function() {
        function destroyChart(id) {
            var el = document.getElementById(id);
            if (el && el._ci) { el._ci.destroy(); el._ci = null; }
        }

        function initCharts() {
            if (typeof Chart === 'undefined') { setTimeout(initCharts, 100); return; }
            var dataEl = document.getElementById('stats-chart-data');
            if (!dataEl) return;
            var chartData = JSON.parse(dataEl.getAttribute('data-chart'));

            ['chart-revenue-daily','chart-payment','chart-hourly','chart-packages'].forEach(destroyChart);

            Chart.defaults.color = '#374151';
            Chart.defaults.borderColor = '#E5E7EB';
            var g = { color: 'rgba(0,0,0,0.04)', borderColor: 'rgba(0,0,0,0.08)' };

            // Revenue bar+line
            var el = document.getElementById('chart-revenue-daily');
            if (el) {
                el._ci = new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: chartData.daily.labels,
                        datasets: [
                            { label: 'Revenue (₱)', data: chartData.daily.revenue, backgroundColor: 'rgba(212,69,26,0.70)', borderColor: '#D4451A', borderWidth: 0, borderRadius: 5, yAxisID: 'yR' },
                            { label: 'Orders', data: chartData.daily.orders, type: 'line', borderColor: '#1E40AF', backgroundColor: 'rgba(30,64,175,0.08)', borderWidth: 2, pointRadius: 3, pointBackgroundColor: '#1E40AF', tension: 0.4, fill: true, yAxisID: 'yO' }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 }, padding: 16 } },
                            tooltip: { callbacks: { label: function(c) {
                                return c.datasetIndex === 0
                                    ? ' ₱' + c.parsed.y.toLocaleString('en-PH',{minimumFractionDigits:2})
                                    : ' ' + c.parsed.y + ' orders';
                            }}}
                        },
                        scales: {
                            x: { grid: g, ticks: { font: { size: 10 } } },
                            yR: { position: 'left', grid: g, ticks: { font: { size: 10 }, callback: function(v){ return '₱'+(v>=1000?(v/1000).toFixed(1)+'K':v); } } },
                            yO: { position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 10 }, stepSize: 1 } }
                        }
                    }
                });
            }

            // Payment doughnut
            el = document.getElementById('chart-payment');
            if (el && chartData.payment.length > 0) {
                el._ci = new Chart(el, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.payment.map(function(p){ return p.label; }),
                        datasets: [{ data: chartData.payment.map(function(p){ return p.cnt; }), backgroundColor: ['rgba(6,214,160,0.85)','rgba(17,138,178,0.85)','rgba(255,183,3,0.85)'], borderColor: ['#06D6A0','#118AB2','#FFB703'], borderWidth: 2, hoverOffset: 6 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '68%',
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 12 } },
                            tooltip: { callbacks: { label: function(c) {
                                var p = chartData.payment[c.dataIndex];
                                return ' ' + p.label + ': ' + p.cnt + ' (₱' + Number(p.total).toLocaleString('en-PH',{minimumFractionDigits:2}) + ')';
                            }}}
                        }
                    }
                });
            }

            // Hourly line
            el = document.getElementById('chart-hourly');
            if (el) {
                el._ci = new Chart(el, {
                    type: 'line',
                    data: {
                        labels: chartData.hourly.labels,
                        datasets: [{ data: chartData.hourly.revenue, borderColor: '#FFB703', backgroundColor: 'rgba(255,183,3,0.12)', borderWidth: 2.5, pointRadius: 3, pointBackgroundColor: '#FFB703', tension: 0.4, fill: true }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c){ return ' ₱'+c.parsed.y.toLocaleString('en-PH',{minimumFractionDigits:2}); }}}},
                        scales: {
                            x: { grid: g, ticks: { font: { size: 10 }, maxRotation: 45 } },
                            y: { grid: g, ticks: { font: { size: 10 }, callback: function(v){ return '₱'+(v>=1000?(v/1000).toFixed(1)+'K':v); } } }
                        }
                    }
                });
            }

            // Packages doughnut
            el = document.getElementById('chart-packages');
            if (el) {
                el._ci = new Chart(el, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(chartData.packages),
                        datasets: [{ data: Object.values(chartData.packages), backgroundColor: ['rgba(212,69,26,0.85)','rgba(212,175,55,0.85)','rgba(30,64,175,0.85)'], borderColor: ['#D4451A','#D4AF37','#1E40AF'], borderWidth: 2, hoverOffset: 6 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '62%',
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 12 } },
                            tooltip: { callbacks: { label: function(c){ return ' '+c.label+': '+c.parsed+' guests'; }}}
                        }
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }

        function hookLivewire() {
            Livewire.hook('commit', function({ succeed }) {
                succeed(function() {
                    requestAnimationFrame(function() {
                        if (document.getElementById('stats-chart-data')) initCharts();
                    });
                });
            });
        }

        if (typeof Livewire !== 'undefined') { hookLivewire(); }
        else { document.addEventListener('livewire:init', hookLivewire); }
    })();
    </script>
    {{-- ── Order Detail Modal ── --}}
    <div x-show="orderModal.open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closeOrder()"
         style="position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;">

        <div x-show="orderModal.open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             style="background:var(--bg-card);border:2px solid var(--border);border-radius:var(--radius-md);width:100%;max-width:540px;max-height:88vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.5);">

            <template x-if="orderModal.order">
                <div style="padding:24px;">

                    {{-- Header --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                        <div>
                            <div style="font-size:1.1em;font-weight:700;color:var(--text-primary);" x-text="'Order #' + orderModal.order.receipt_number"></div>
                            <div style="font-size:0.82em;color:var(--text-muted);margin-top:2px;" x-text="orderModal.order.created_at"></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:0.8em;font-weight:700;padding:4px 12px;border-radius:20px;"
                                :style="orderModal.order.status === 'completed' ? 'background:rgba(6,214,160,0.15);color:var(--success);' : orderModal.order.status === 'cancelled' ? 'background:rgba(230,57,70,0.15);color:var(--danger);' : 'background:rgba(255,183,3,0.15);color:var(--warning);'"
                                x-text="orderModal.order.status === 'completed' ? '✓ Paid' : orderModal.order.status === 'cancelled' ? '✕ Cancelled' : '⏳ Pending'">
                            </span>
                            <button @click="closeOrder()" style="background:transparent;border:none;color:var(--text-muted);font-size:1.4em;cursor:pointer;line-height:1;padding:0;">&times;</button>
                        </div>
                    </div>

                    {{-- Guests & Payment --}}
                    <div style="display:flex;gap:10px;margin-bottom:16px;">
                        <div style="background:#F5F2EF;border-radius:8px;padding:10px 14px;flex:1;text-align:center;">
                            <div style="font-size:0.75em;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Guests</div>
                            <div style="font-size:1.2em;font-weight:700;color:var(--text-primary);" x-text="'👥 ' + orderModal.order.total_people + ' pax'"></div>
                        </div>
                        <div style="background:#F5F2EF;border-radius:8px;padding:10px 14px;flex:1;text-align:center;">
                            <div style="font-size:0.75em;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Payment</div>
                            <div style="font-size:1.2em;font-weight:700;color:var(--text-primary);" x-text="(orderModal.order.payment === 'Cash' ? '💵 ' : '📱 ') + orderModal.order.payment"></div>
                        </div>
                    </div>

                    {{-- Packages --}}
                    <template x-if="orderModal.order.packages && orderModal.order.packages.length > 0">
                        <div style="margin-bottom:14px;">
                            <div style="font-size:0.75em;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:8px;">🍖 Packages</div>
                            <template x-for="pkg in orderModal.order.packages" :key="pkg.name">
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 12px;background:#F5F2EF;border-radius:6px;margin-bottom:4px;">
                                    <span style="color:var(--text-primary);" x-text="pkg.people + '× ' + pkg.name"></span>
                                    <span style="color:var(--primary);font-weight:600;" x-text="'₱' + (pkg.amount ?? (pkg.people * pkg.price)).toLocaleString()"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Addons --}}
                    <template x-if="orderModal.order.addons && orderModal.order.addons.length > 0">
                        <div style="margin-bottom:14px;">
                            <div style="font-size:0.75em;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:8px;">✨ Add-ons</div>
                            <template x-for="addon in orderModal.order.addons" :key="addon.name">
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 12px;background:#F5F2EF;border-radius:6px;margin-bottom:4px;">
                                    <span style="color:var(--text-primary);" x-text="addon.people + '× ' + addon.name"></span>
                                    <span style="color:var(--primary);font-weight:600;" x-text="'₱' + (addon.amount ?? (addon.people * addon.price)).toLocaleString()"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Extras --}}
                    <template x-if="orderModal.order.extra_items && orderModal.order.extra_items.length > 0">
                        <div style="margin-bottom:14px;">
                            <div style="font-size:0.75em;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:8px;">🛒 Extras</div>
                            <template x-for="item in orderModal.order.extra_items" :key="item.name">
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 12px;background:#F5F2EF;border-radius:6px;margin-bottom:4px;">
                                    <span style="color:var(--text-primary);" x-text="item.qty + '× ' + item.name"></span>
                                    <span style="color:var(--primary);font-weight:600;" x-text="'₱' + item.amount.toLocaleString()"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Totals --}}
                    <div style="border-top:2px solid var(--border);padding-top:14px;margin-top:4px;">
                        <div style="display:flex;justify-content:space-between;padding:4px 0;color:var(--text-muted);font-size:0.9em;">
                            <span>Subtotal</span>
                            <span x-text="'₱' + orderModal.order.subtotal.toLocaleString('en', {minimumFractionDigits:2})"></span>
                        </div>
                        <template x-if="orderModal.order.discount_percent > 0">
                            <div style="display:flex;justify-content:space-between;padding:4px 0;color:var(--success);font-size:0.9em;">
                                <span x-text="'Discount (' + orderModal.order.discount_percent + '%)'"></span>
                                <span x-text="'-₱' + orderModal.order.discount_amount.toLocaleString('en', {minimumFractionDigits:2})"></span>
                            </div>
                        </template>
                        <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:1.1em;font-weight:700;color:var(--text-primary);border-top:1px solid var(--border);margin-top:6px;">
                            <span>Total</span>
                            <span style="color:var(--primary);" x-text="'₱' + orderModal.order.total.toLocaleString('en', {minimumFractionDigits:2})"></span>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </div>


</div>
