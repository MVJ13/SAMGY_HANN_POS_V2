<div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:#ff6b6b;margin:0;">Create New Order</h2>
        <span style="color:#999;font-size:0.85em;">Extras are managed via the <strong style="color:#ff6b6b;">Inventory</strong> tab</span>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orderCreated): ?>
        <div class="flash-success" wire:ignore id="flash-new-order">
            ✅ Order created successfully! Switching to Receipts…
        </div>
        <script>
            (function() {
                var el = document.getElementById('flash-new-order');
                if (el) {
                    setTimeout(function() {
                        el.style.display = 'none';
                        var wid = el.closest('[wire\\:id]');
                        if (wid && typeof Livewire !== 'undefined') {
                            Livewire.find(wid.getAttribute('wire:id'))?.call('dismissFlash');
                        }
                    }, 3000);
                }
            })();
        </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orderError): ?>
        <div style="background:#3a1a1a;border:1px solid #f44336;border-radius:8px;padding:14px 18px;margin-bottom:18px;color:#f44336;font-weight:600;">
            ⚠️ <?php echo e($orderError); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-data="orderForm({
            products: <?php echo e(json_encode(
                $extraProducts->flatten()->map(fn($p) => [
                    'id'       => $p->id,
                    'name'     => $p->name,
                    'category' => $p->category,
                    'price'    => (float) $p->selling_price,
                    'stock'    => (float) $p->stock,
                    'unit'     => $p->unit,
                    'lowStock' => (bool) $p->is_low_stock,
                ])->values()
            )); ?>,
            packagePrices: { p199: 199, p269: 269, p349: 349 }
        })">

        
        <div class="form-group">
            <label>Packages</label>
            <div class="package-selector">
                <div class="package-card">
                    <h3>Basic — ₱199</h3>
                    <input type="number" x-model.number="pkgs.p199"
                           @input="setPkg('p199', $event.target.value)"
                           min="0" placeholder="# of people">
                    <p style="margin-top:10px;color:#999;">per person</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['people199'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="error-msg"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="package-card">
                    <h3>Premium — ₱269</h3>
                    <input type="number" x-model.number="pkgs.p269"
                           @input="setPkg('p269', $event.target.value)"
                           min="0" placeholder="# of people">
                    <p style="margin-top:10px;color:#999;">per person</p>
                </div>
                <div class="package-card">
                    <h3>Deluxe — ₱349</h3>
                    <input type="number" x-model.number="pkgs.p349"
                           @input="setPkg('p349', $event.target.value)"
                           min="0" placeholder="# of people">
                    <p style="margin-top:10px;color:#999;">per person</p>
                </div>
            </div>
        </div>

        
        <div class="form-group">
            <label>Per-Person Add-ons <span style="color:#999;font-weight:400;">(₱25 per person)</span></label>
            <div class="two-col">
                <div style="background:#1a1a1a;padding:15px;border-radius:8px;border:2px solid #424242;">
                    <label style="color:#fff;margin:0 0 10px 0;">🧊 Unlimited Iced Tea</label>
                    <input type="number" x-model.number="addons.icedTea"
                           @input="setAddon('icedTea', $event.target.value)"
                           min="0" placeholder="# of people">
                </div>
                <div style="background:#1a1a1a;padding:15px;border-radius:8px;border:2px solid #424242;">
                    <label style="color:#fff;margin:0 0 10px 0;">🧀 Unlimited Cheese</label>
                    <input type="number" x-model.number="addons.cheese"
                           @input="setAddon('cheese', $event.target.value)"
                           min="0" placeholder="# of people">
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($extraProducts->isNotEmpty()): ?>
            <div class="form-group">
                <label>Extra Items <span style="color:#999;font-weight:400;">(optional, per piece)</span></label>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $extraProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="extras-category">
                        <div class="extras-category-label">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category === 'Alcohol'): ?>       🍶
                            <?php elseif($category === 'Drinks'): ?>    🥤
                            <?php elseif($category === 'Ice Cream'): ?> 🍦
                            <?php elseif($category === 'Meat'): ?>      🥩
                            <?php elseif($category === 'Vegetables'): ?>🥦
                            <?php else: ?>                              🛒
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php echo e($category); ?>

                        </div>
                        <div class="extras-grid">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $outOfStock = $product->stock <= 0; ?>
                                <div class="extra-item-card"
                                     :class="{ 'extra-item-active': qty(<?php echo e($product->id); ?>) > 0 }"
                                     style="<?php echo e($outOfStock ? 'opacity:0.5;pointer-events:none;' : ''); ?>">
                                    <div class="extra-item-name"><?php echo e($product->name); ?></div>
                                    <div class="extra-item-price">₱<?php echo e(number_format($product->selling_price, 0)); ?></div>
                                    <div style="font-size:0.75em;margin-bottom:4px;color:<?php echo e($outOfStock ? '#f44336' : ($product->is_low_stock ? '#ff9800' : '#4caf50')); ?>;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($outOfStock): ?> ❌ Out of stock
                                        <?php elseif($product->is_low_stock): ?> ⚠️ <?php echo e($product->stock); ?> <?php echo e($product->unit); ?> left
                                        <?php else: ?> ✓ <?php echo e($product->stock); ?> <?php echo e($product->unit); ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$outOfStock): ?>
                                        <div class="extra-qty-control">
                                            <button type="button" class="qty-btn"
                                                    @click="decrement(<?php echo e($product->id); ?>)">−</button>
                                            <input type="number"
                                                   class="qty-input"
                                                   :value="qty(<?php echo e($product->id); ?>)"
                                                   @change="set(<?php echo e($product->id); ?>, $event.target.value)"
                                                   min="0" max="<?php echo e((int) $product->stock); ?>">
                                            <button type="button" class="qty-btn"
                                                    @click="increment(<?php echo e($product->id); ?>, <?php echo e((int) $product->stock); ?>)">+</button>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="two-col">
            <div class="form-group">
                <label>Payment Type</label>
                <select wire:model.blur="paymentType">
                    <option value="Cash">Cash</option>
                    <option value="QRPH">QRPH</option>
                </select>
            </div>
            <div class="form-group">
                <label>Discount (%) <span style="color:#999;font-weight:400;">— Optional</span></label>
                <input type="number" x-model.number="discount"
                       @input="setDiscount($event.target.value)"
                       min="0" max="100" step="0.01" placeholder="0">
            </div>
        </div>

        
        <template x-if="hasAnything">
            <div class="order-summary">
                <h3>Order Summary</h3>

                
                <template x-if="pkgs.p199 > 0">
                    <p>• Basic (₱199): <span x-text="pkgs.p199"></span> <span x-text="pkgs.p199 === 1 ? 'person' : 'people'"></span> = ₱<span x-text="fmt(pkgs.p199 * 199)"></span></p>
                </template>
                <template x-if="pkgs.p269 > 0">
                    <p>• Premium (₱269): <span x-text="pkgs.p269"></span> <span x-text="pkgs.p269 === 1 ? 'person' : 'people'"></span> = ₱<span x-text="fmt(pkgs.p269 * 269)"></span></p>
                </template>
                <template x-if="pkgs.p349 > 0">
                    <p>• Deluxe (₱349): <span x-text="pkgs.p349"></span> <span x-text="pkgs.p349 === 1 ? 'person' : 'people'"></span> = ₱<span x-text="fmt(pkgs.p349 * 349)"></span></p>
                </template>

                
                <template x-if="addons.icedTea > 0">
                    <p>• Unlimited Iced Tea: <span x-text="addons.icedTea"></span> <span x-text="addons.icedTea === 1 ? 'person' : 'people'"></span> × ₱25 = ₱<span x-text="fmt(addons.icedTea * 25)"></span></p>
                </template>
                <template x-if="addons.cheese > 0">
                    <p>• Unlimited Cheese: <span x-text="addons.cheese"></span> <span x-text="addons.cheese === 1 ? 'person' : 'people'"></span> × ₱25 = ₱<span x-text="fmt(addons.cheese * 25)"></span></p>
                </template>

                
                <template x-for="item in activeExtras" :key="item.id">
                    <p>• <span x-text="item.name"></span>: <span x-text="item.qty"></span> × ₱<span x-text="fmt(item.price)"></span> = ₱<span x-text="fmt(item.qty * item.price)"></span></p>
                </template>

                <div style="border-top:2px solid #424242;margin:15px 0;padding-top:15px;">
                    <p><strong>Total People:</strong> <span x-text="totalPeople"></span></p>
                    <p><strong>Subtotal:</strong> ₱<span x-text="fmt(subtotal)"></span></p>
                    <template x-if="discount > 0">
                        <p><strong>Discount (<span x-text="discount"></span>%):</strong>
                            <span style="color:#4caf50;">-₱<span x-text="fmt(discountAmount)"></span></span></p>
                    </template>
                    <p><strong>Total:</strong>
                        <span style="color:#ff6b6b;font-size:1.5em;font-weight:700;">₱<span x-text="fmt(total)"></span></span></p>
                </div>
            </div>
        </template>

        
        <button class="btn btn-primary"
                wire:loading.attr="disabled"
                wire:loading.class="btn-loading"
                @click="submitOrder()">
            <span wire:loading.remove wire:target="syncAndCreate">Create Order</span>
            <span wire:loading wire:target="syncAndCreate">Creating…</span>
        </button>

    </div>

</div>

<script>
function orderForm({ products, packagePrices }) {
    return {
        products: products,
        qtys: {},
        pkgs:    { p199: 0, p269: 0, p349: 0 },
        addons:  { icedTea: 0, cheese: 0 },
        discount: 0,
        payment: 'Cash',

        init() {
            products.forEach(p => { this.qtys[p.id] = 0; });

            // Sync payment select with Alpine state on change
            var self = this;
            this.$nextTick(function() {
                var sel = self.$el.querySelector('select[wire\\:model\\.blur]');
                if (sel) {
                    sel.addEventListener('change', function() {
                        self.payment = sel.value;
                    });
                }
            });
        },

        // ── Submit: collect all Alpine state and send to Livewire ──
        submitOrder() {
            // Read current select value directly in case it wasn't intercepted
            var sel = this.$el.querySelector('select[wire\\:model\\.blur]');
            if (sel) this.payment = sel.value;

            var payload = JSON.stringify({
                qtys:     this.qtys,
                pkgs:     this.pkgs,
                addons:   this.addons,
                discount: this.discount,
                payment:  this.payment,
            });

            this.$wire.syncAndCreate(payload).then(() => {
                // Reset Alpine state after successful order creation
                this.qtys    = {};
                this.products.forEach(p => { this.qtys[p.id] = 0; });
                this.pkgs    = { p199: 0, p269: 0, p349: 0 };
                this.addons  = { icedTea: 0, cheese: 0 };
                this.discount = 0;
                this.payment  = 'Cash';
            });
        },

        // ── Setters called from @input on package/addon inputs ──
        setPkg(key, val)    { this.pkgs[key]    = Math.max(0, parseInt(val)   || 0); },
        setAddon(key, val)  { this.addons[key]  = Math.max(0, parseInt(val)   || 0); },
        setDiscount(val)    { this.discount      = Math.max(0, parseFloat(val) || 0); },

        // ── Extra item qty helpers ───────────────────────────────
        qty(id)       { return this.qtys[id] || 0; },
        set(id, val)  { this.qtys[id] = Math.max(0, parseInt(val) || 0); },
        increment(id, max) {
            const cur = this.qtys[id] || 0;
            const cap = max !== undefined ? Math.floor(max) : Infinity;
            this.qtys[id] = cur < cap ? cur + 1 : cap;
        },
        decrement(id) { this.qtys[id] = Math.max(0, (this.qtys[id] || 0) - 1); },

        // ── Derived values ───────────────────────────────────────
        get totalPeople() {
            return this.pkgs.p199 + this.pkgs.p269 + this.pkgs.p349;
        },

        get activeExtras() {
            return this.products
                .filter(p => (this.qtys[p.id] || 0) > 0)
                .map(p => ({ ...p, qty: this.qtys[p.id] }));
        },

        get subtotal() {
            const pkgTotal   = this.pkgs.p199 * 199 + this.pkgs.p269 * 269 + this.pkgs.p349 * 349;
            const addonTotal = this.addons.icedTea * 25 + this.addons.cheese * 25;
            const extraTotal = this.products.reduce((s, p) => s + (this.qtys[p.id] || 0) * p.price, 0);
            return pkgTotal + addonTotal + extraTotal;
        },

        get discountAmount() {
            return Math.round(this.subtotal * (this.discount / 100) * 100) / 100;
        },

        get total() {
            return Math.round((this.subtotal - this.discountAmount) * 100) / 100;
        },

        get hasAnything() {
            return this.totalPeople > 0
                || this.addons.icedTea > 0
                || this.addons.cheese  > 0
                || this.activeExtras.length > 0;
        },

        fmt(n) {
            return Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
<?php /**PATH C:\Users\Mark\samgyeopsal-pos\resources\views/livewire/new-order.blade.php ENDPATH**/ ?>