<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samgyeopsal POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div class="pos-container">

        <header class="pos-header">
            <h1>🥩 Unlimited Samgyeopsal POS System</h1>
        </header>

        <div class="tabs" wire:ignore>
            <button class="tab-btn active" id="btn-orders"     onclick="switchTab('orders')">New Order</button>
            <button class="tab-btn"        id="btn-receipts"   onclick="switchTab('receipts')">Receipts</button>
            <button class="tab-btn"        id="btn-inventory"  onclick="switchTab('inventory')">Inventory</button>
            <button class="tab-btn"        id="btn-statistics" onclick="switchTab('statistics')">Statistics</button>
        </div>

        {{-- wire:ignore prevents Livewire from morphing the tab-panel class
             attributes, which would strip the 'active' class and hide the panel --}}
        <div class="tab-panels">
            <div class="tab-panel" id="tab-orders">
                @livewire('new-order')
            </div>
            <div class="tab-panel" id="tab-receipts">
                @livewire('receipts')
            </div>
            <div class="tab-panel" id="tab-inventory">
                @livewire('inventory')
            </div>
            <div class="tab-panel" id="tab-statistics">
                @livewire('statistics')
            </div>
        </div>

    </div>

    @livewireScripts

    <script>
        var currentTab = sessionStorage.getItem('pos-tab') || 'orders';

        function switchTab(name) {
            // Use CSS class toggling instead of inline styles so Livewire's
            // DOM morphing cannot strip the visibility state from the panels.
            document.querySelectorAll('.tab-panel').forEach(function(p) {
                p.classList.remove('active');
            });
            var panel = document.getElementById('tab-' + name);
            if (panel) panel.classList.add('active');

            document.querySelectorAll('.tab-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            var btn = document.getElementById('btn-' + name);
            if (btn) btn.classList.add('active');

            currentTab = name;
            sessionStorage.setItem('pos-tab', name);
        }

        // Apply correct tab on first load
        switchTab(currentTab);

        document.addEventListener('livewire:init', function () {
            // NOTE: The morph.updated hook has been removed intentionally.
            // It was hiding all tab panels on every Livewire interaction (blur,
            // click, any server round-trip), causing the "auto-refresh" flicker.
            // Tab visibility is managed exclusively by switchTab().

            Livewire.on('order-created', function (event) {
                // Switch tab first, then open the receipt modal once the
                // receipts panel is visible and Alpine is ready to receive it.
                switchTab('receipts');
                var orderData = event[0] && event[0].order ? event[0].order : null;
                if (orderData) {
                    setTimeout(function () {
                        window.dispatchEvent(new CustomEvent('open-receipt', { detail: { order: orderData } }));
                    }, 50);
                }
            });
        });
    </script>
</body>
</html>
