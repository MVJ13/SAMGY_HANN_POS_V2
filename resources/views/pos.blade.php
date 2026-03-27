<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SamgyHann 199 POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" defer></script>
</head>
<body>
<div class="pos-container">

    <header class="pos-header">
        <div class="pos-header-inner">
            <img src="/samgyhann-logo.png" alt="SamgyHann 199 Logo" class="pos-header-logo">
            <div>
                <div class="pos-header-brand">SamgyHann 199</div>
                <div class="pos-header-sub">Point of Sale System</div>
            </div>
        </div>
        <div class="pos-header-user">
            <div class="pos-header-user-info">
                <div class="pos-header-user-name">{{ auth()->user()->name }}</div>
                <div class="pos-header-user-role pos-role-{{ auth()->user()->role }}">
                    @switch(auth()->user()->role)
                        @case('super_admin') 👑 Super Admin @break
                        @case('admin')       🛡️ Admin      @break
                        @case('cashier')     🧾 Cashier    @break
                    @endswitch
                </div>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="pos-logout-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </header>

    @php
        $allowedTabs = auth()->user()->allowedTabs();
        $defaultTab  = auth()->user()->defaultTab();
    @endphp

    <div class="tabs">
        @if(in_array('orders', $allowedTabs))
            <button class="tab-btn" id="btn-orders"     onclick="switchTab('orders')">🍖 New Order</button>
        @endif
        @if(in_array('receipts', $allowedTabs))
            <button class="tab-btn" id="btn-receipts"   onclick="switchTab('receipts')">🧾 Receipts</button>
        @endif
        @if(in_array('inventory', $allowedTabs))
            <button class="tab-btn" id="btn-inventory"  onclick="switchTab('inventory')">📦 Inventory</button>
        @endif
        @if(in_array('statistics', $allowedTabs))
            <button class="tab-btn" id="btn-statistics" onclick="switchTab('statistics')">📊 Statistics</button>
        @endif
        @if(in_array('settings', $allowedTabs))
            <button class="tab-btn" id="btn-settings" onclick="switchTab('settings')">⚙️ Settings</button>
        @endif
    </div>

    <div class="tab-panels">
        @if(in_array('orders', $allowedTabs))
            <div class="tab-panel" id="tab-orders">@livewire('new-order')</div>
        @endif
        @if(in_array('receipts', $allowedTabs))
            <div class="tab-panel" id="tab-receipts">@livewire('receipts')</div>
        @endif
        @if(in_array('inventory', $allowedTabs))
            <div class="tab-panel" id="tab-inventory">@livewire('inventory')</div>
        @endif
        @if(in_array('statistics', $allowedTabs))
            <div class="tab-panel" id="tab-statistics">@livewire('statistics')</div>
        @endif
        @if(in_array('settings', $allowedTabs))
            <div class="tab-panel" id="tab-settings">@livewire('settings')</div>
        @endif
    </div>

</div>

@livewireScripts

<script>
var defaultTab  = '{{ $defaultTab }}';
var allowedTabs = @json($allowedTabs);
var currentTab  = sessionStorage.getItem('pos-tab') || defaultTab;
if (!allowedTabs.includes(currentTab)) currentTab = defaultTab;

function switchTab(name) {
    if (!allowedTabs.includes(name)) return;

    document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function(b)   { b.classList.remove('active'); });

    var panel = document.getElementById('tab-' + name);
    var btn   = document.getElementById('btn-' + name);
    if (panel) panel.classList.add('active');
    if (btn)   btn.classList.add('active');

    currentTab = name;
    sessionStorage.setItem('pos-tab', name);
}

// Initial tab on page load
switchTab(currentTab);

document.addEventListener('livewire:init', function () {

    // When a new order is created: switch to receipts and open the receipt modal
    Livewire.on('order-created', function (event) {
        var payload   = Array.isArray(event) ? event[0] : event;
        var orderData = payload && payload.order ? payload.order : null;
        if (!orderData) return;

        // Add status field since new orders are always active
        orderData.status = orderData.status || 'active';

        switchTab('receipts');

        // Refresh the receipts component then open the modal
        var receiptsEl = document.querySelector('#tab-receipts [wire\\:id]');
        if (receiptsEl) {
            var comp = Livewire.find(receiptsEl.getAttribute('wire:id'));
            if (comp) {
                comp.$refresh().then(function () {
                    setTimeout(function () {
                        window.dispatchEvent(new CustomEvent('open-receipt', { detail: { order: orderData } }));
                    }, 100);
                });
                return;
            }
        }
        // Fallback if component not found yet
        setTimeout(function () {
            window.dispatchEvent(new CustomEvent('open-receipt', { detail: { order: orderData } }));
        }, 300);
    });

});
</script>
</body>
</html>
