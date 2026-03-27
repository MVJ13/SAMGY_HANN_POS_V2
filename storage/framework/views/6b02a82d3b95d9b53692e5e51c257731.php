<div> 

<div x-data="{
    prices: {
        price_basic:   <?php echo e($prices['basic']); ?>,
        price_premium: <?php echo e($prices['premium']); ?>,
        price_deluxe:  <?php echo e($prices['deluxe']); ?>,
        price_addon:   <?php echo e($prices['addon']); ?>,
    },
    saving: false,
    saved: false,
    savedTimer: null,
    save() {
        this.saving = true;
        this.$wire.savePrices(this.prices)
            .finally(() => {
                this.saving = false;
                this.saved  = true;
                clearTimeout(this.savedTimer);
                this.savedTimer = setTimeout(() => { this.saved = false; }, 3000);
            });
    }
}"
@prices-updated.window="$dispatch('notify-price-change', $event.detail)">

<div class="settings-panel">

    
    <div class="settings-header">
        <div>
            <h2 class="settings-title">⚙️ Settings</h2>
            <p class="settings-sub">Manage package pricing and system configuration</p>
        </div>
        <div x-show="saved" x-cloak
             style="background:#F0FDF4;border:1.5px solid #16A34A;border-radius:8px;padding:10px 18px;color:#166534;font-weight:700;font-size:14px;">
            ✅ Settings saved successfully
        </div>
    </div>

    
    <div class="settings-section">
        <div class="settings-section-title">
            <span>📦 Package Prices</span>
            <span class="settings-section-sub">Changes apply immediately to new orders</span>
        </div>

        <div class="settings-grid">
            
            <div class="settings-price-card" style="border-top:3px solid #8E9BAE;">
                <div class="settings-price-label">Basic Package</div>
                <div class="settings-price-desc">All-you-can-eat samgyeopsal</div>
                <div class="settings-price-input-wrap">
                    <span class="settings-price-symbol">₱</span>
                    <input type="number" x-model.number="prices.price_basic"
                           min="1" step="1" class="settings-price-input"
                           @keydown.enter="save()">
                </div>
                <div class="settings-price-hint">per person</div>
            </div>

            
            <div class="settings-price-card" style="border-top:3px solid #D4AF37;">
                <div class="settings-price-label">Premium Package</div>
                <div class="settings-price-desc">+ Wagyu & premium cuts</div>
                <div class="settings-price-input-wrap">
                    <span class="settings-price-symbol">₱</span>
                    <input type="number" x-model.number="prices.price_premium"
                           min="1" step="1" class="settings-price-input"
                           @keydown.enter="save()">
                </div>
                <div class="settings-price-hint">per person</div>
            </div>

            
            <div class="settings-price-card" style="border-top:3px solid #D4451A;">
                <div class="settings-price-label">Deluxe Package</div>
                <div class="settings-price-desc">+ Seafood & premium drinks</div>
                <div class="settings-price-input-wrap">
                    <span class="settings-price-symbol">₱</span>
                    <input type="number" x-model.number="prices.price_deluxe"
                           min="1" step="1" class="settings-price-input"
                           @keydown.enter="save()">
                </div>
                <div class="settings-price-hint">per person</div>
            </div>

            
            <div class="settings-price-card" style="border-top:3px solid #1E40AF;">
                <div class="settings-price-label">Add-on Price</div>
                <div class="settings-price-desc">Iced Tea & Cheese add-ons</div>
                <div class="settings-price-input-wrap">
                    <span class="settings-price-symbol">₱</span>
                    <input type="number" x-model.number="prices.price_addon"
                           min="1" step="1" class="settings-price-input"
                           @keydown.enter="save()">
                </div>
                <div class="settings-price-hint">per person</div>
            </div>
        </div>

        
        <div class="settings-preview">
            <div class="settings-preview-title">📋 Price Preview</div>
            <div class="settings-preview-row">
                <span>Basic</span>
                <span x-text="'₱' + prices.price_basic + ' / pax'"></span>
            </div>
            <div class="settings-preview-row">
                <span>Premium</span>
                <span x-text="'₱' + prices.price_premium + ' / pax'"></span>
            </div>
            <div class="settings-preview-row">
                <span>Deluxe</span>
                <span x-text="'₱' + prices.price_deluxe + ' / pax'"></span>
            </div>
            <div class="settings-preview-row">
                <span>Add-ons (Iced Tea / Cheese)</span>
                <span x-text="'₱' + prices.price_addon + ' / pax'"></span>
            </div>
        </div>

        <div class="settings-notice">
            ⚠️ <strong>Note:</strong> Changing prices only affects <strong>new orders</strong> going forward.
            Existing completed orders keep their original prices in the records.
        </div>

        <button @click="save()" :disabled="saving" class="settings-save-btn">
            <span x-show="!saving">💾 Save Prices</span>
            <span x-show="saving" x-cloak>⏳ Saving...</span>
        </button>
    </div>

</div>
</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'super_admin'): ?>
<div class="settings-section" x-data="{
    confirm: false,
    countdown: 0,
    timer: null,
    done: false,
    startConfirm() {
        this.confirm = true;
        this.countdown = 5;
        clearInterval(this.timer);
        this.timer = setInterval(() => {
            this.countdown--;
            if (this.countdown <= 0) clearInterval(this.timer);
        }, 1000);
    },
    doReset() {
        if (this.countdown > 0) return;
        this.$wire.factoryReset().then(() => {
            this.done = true;
            this.confirm = false;
        });
    }
}"
@factory-reset-done.window="done = true; confirm = false">
    <div class="settings-section-title">
        <span style="color:#991B1B;">⚠️ System Reset</span>
        <span class="settings-section-sub">Irreversible — super admin only</span>
    </div>

    <div x-show="done" style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:8px;padding:14px 18px;color:#166534;font-weight:700;font-size:14px;margin-bottom:16px;">
        ✅ System reset complete. All orders cleared, stock zeroed, prices restored to defaults.
    </div>

    <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:var(--r-md);padding:16px 18px;margin-bottom:16px;">
        <div style="font-weight:800;color:#991B1B;margin-bottom:6px;font-size:14px;">🗑️ Factory Reset will permanently:</div>
        <ul style="margin:0;padding-left:20px;color:#7F1D1D;font-size:13px;line-height:1.8;">
            <li>Delete ALL orders (active, completed, cancelled)</li>
            <li>Delete ALL stock movement history</li>
            <li>Reset ALL product stock quantities to zero</li>
            <li>Clear all product availability dates</li>
            <li>Reset package prices to ₱199 / ₱269 / ₱349 / ₱25</li>
        </ul>
        <div style="margin-top:10px;font-size:12px;color:#991B1B;font-weight:600;">Product names, categories, and cost prices are kept.</div>
    </div>

    <div x-show="!confirm && !done">
        <button @click="startConfirm()"
                style="background:#FEF2F2;color:#991B1B;border:2px solid #FCA5A5;border-radius:var(--r-md);padding:12px 24px;font-size:14px;font-weight:800;cursor:pointer;font-family:inherit;width:100%;">
            🗑️ Perform Factory Reset…
        </button>
    </div>

    <div x-show="confirm && !done" style="background:#FFF7ED;border:2px solid #FCD34D;border-radius:var(--r-md);padding:18px;">
        <div style="font-weight:800;color:#92400E;margin-bottom:12px;font-size:15px;">Are you absolutely sure?</div>
        <div style="font-size:13px;color:#78350F;margin-bottom:16px;">This cannot be undone. All transaction history will be permanently deleted.</div>
        <div style="display:flex;gap:10px;align-items:center;">
            <button @click="confirm = false; clearInterval(timer)"
                    style="flex:1;padding:12px;background:#fff;color:var(--text-2);border:1.5px solid var(--border);border-radius:var(--r-md);font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;">
                Cancel
            </button>
            <button @click="doReset()"
                    :disabled="countdown > 0"
                    :style="countdown > 0 ? 'opacity:0.5;cursor:not-allowed;' : ''"
                    style="flex:2;padding:12px;background:#DC2626;color:#fff;border:none;border-radius:var(--r-md);font-size:14px;font-weight:800;cursor:pointer;font-family:inherit;">
                <span x-show="countdown > 0" x-text="'Wait ' + countdown + 's…'"></span>
                <span x-show="countdown <= 0">🗑️ Yes, Reset Everything</span>
            </button>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div> 
<?php /**PATH C:\Users\Mark\samgyeopsal-pos - Copy\resources\views/livewire/settings.blade.php ENDPATH**/ ?>