<div x-data="{
    pkgs: { p199: 0, p269: 0, p349: 0 },
    addons: { icedTea: 0, cheese: 0 },
    payment: 'Cash',
    discountPersons: [],
    discountPanelOpen: false,
    qtys: {},
    activeExtrasTab: null,
    products: <?php echo e(json_encode($extraProducts->flatten()->map(fn($p) => [
        'id'       => $p->id,
        'name'     => $p->name,
        'category' => $p->category,
        'price'    => (float) $p->selling_price,
        'stock'    => (float) $p->stock,
        'unit'     => $p->unit,
        'lowStock' => (bool) $p->is_low_stock,
    ])->values())); ?>,

    orderError: '',
    showCashModal:  false,
    showQrphModal:  false,
    flash: '',
    flashTimer: null,
    amountReceived: '',
    cashError:      '',
    submitting:     false,

    init() {
        this.products.forEach(p => { this.qtys[p.id] = 0; });
        const cats = [...new Set(this.products.map(p => p.category))];
        if (cats.length) this.activeExtrasTab = cats[0];
    },

    get extraCategories() { return [...new Set(this.products.map(p => p.category))]; },
    get productsInTab()   { return this.products.filter(p => p.category === this.activeExtrasTab); },

    qty(id)            { return this.qtys[id] || 0; },
    increment(id, max) {
        const cur = this.qtys[id] || 0;
        const cap = (max !== undefined && max !== null) ? Math.floor(max) : Infinity;
        this.qtys = { ...this.qtys, [id]: cur < cap ? cur + 1 : cur };
    },
    decrement(id) { this.qtys = { ...this.qtys, [id]: Math.max(0, (this.qtys[id] || 0) - 1) }; },

    changePkg(key, delta) {
        this.pkgs[key] = Math.max(0, (this.pkgs[key] || 0) + delta);
        this.syncDiscountPersons();
    },

    get totalPeople()  { return (this.pkgs.p199||0) + (this.pkgs.p269||0) + (this.pkgs.p349||0); },
    get activeExtras() {
        return this.products.filter(p => (this.qtys[p.id]||0) > 0).map(p => ({ ...p, qty: this.qtys[p.id] }));
    },
    prices: <?php echo e(json_encode(['p199' => $prices['basic'], 'p269' => $prices['premium'], 'p349' => $prices['deluxe'], 'addon' => $prices['addon']])); ?>,
    pkgPrice(pkg) { return this.prices[pkg] ?? 0; },
    pkgName(pkg)  { return pkg === 'p199' ? 'Basic' : pkg === 'p269' ? 'Premium' : 'Deluxe'; },

    get subtotal() {
        return (this.pkgs.p199||0)*this.prices.p199 + (this.pkgs.p269||0)*this.prices.p269 + (this.pkgs.p349||0)*this.prices.p349
             + (this.addons.icedTea||0)*this.prices.addon + (this.addons.cheese||0)*this.prices.addon
             + this.products.reduce((s,p) => s + (this.qtys[p.id]||0)*p.price, 0);
    },

    get paxRows() {
        const rows = [];
        for (const pkg of ['p199','p269','p349']) {
            const count = this.pkgs[pkg] || 0;
            for (let i = 1; i <= count; i++) {
                const existing = this.discountPersons.find(d => d.pkg === pkg && d.paxNum === i);
                rows.push({ pkg, paxNum: i, type: existing ? existing.type : 'none' });
            }
        }
        return rows;
    },

    setDiscount(pkg, paxNum, type) {
        const filtered = this.discountPersons.filter(d => !(d.pkg === pkg && d.paxNum === paxNum));
        if (type !== 'none') filtered.push({ pkg, paxNum, type, pct: type === 'child' ? 10 : 20 });
        this.discountPersons = filtered;
    },

    syncDiscountPersons() {
        this.discountPersons = this.discountPersons.filter(
            d => d.paxNum <= (this.pkgs[d.pkg] || 0)
        );
    },

    get discountAmount() {
        return Math.round(
            this.discountPersons.reduce((sum, d) => sum + this.pkgPrice(d.pkg) * (d.pct / 100), 0) * 100
        ) / 100;
    },
    get discountBreakdown() {
        const groups = {};
        for (const d of this.discountPersons) {
            const key = d.type + '|' + d.pkg;
            if (!groups[key]) groups[key] = { type: d.type, pkg: d.pkg, pct: d.pct, count: 0 };
            groups[key].count++;
        }
        return Object.values(groups);
    },
    get total()       { return Math.round((this.subtotal - this.discountAmount) * 100) / 100; },
    get change()      { return Math.max(0, (parseFloat(this.amountReceived)||0) - this.total); },
    get hasAnything() {
        return this.totalPeople > 0 || (this.addons.icedTea||0) > 0
            || (this.addons.cheese||0) > 0 || this.activeExtras.length > 0;
    },

    fmt(n) { return Number(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); },

    handleCreateOrder() {
        if (!this.hasAnything) return;
        this.orderError = '';
        if (this.payment === 'Cash') { this.cashError = ''; this.amountReceived = ''; this.showCashModal = true; }
        else { this.showQrphModal = true; }
    },

    resetForm() {
        const ids = this.products.map(p => p.id);
        this.qtys = {};
        ids.forEach(id => { this.qtys[id] = 0; });
        this.pkgs = { p199: 0, p269: 0, p349: 0 };
        this.addons = { icedTea: 0, cheese: 0 };
        this.payment = 'Cash';
        this.discountPersons = [];
        this.discountPanelOpen = false;
        this.orderError = '';
        this.cashError = '';
    },

    showFlash(msg) {
        this.flash = msg;
        clearTimeout(this.flashTimer);
        this.flashTimer = setTimeout(() => { this.flash = ''; }, 3000);
    },

    submitOrder(amountReceived = 0) {
        if (this.submitting) return;
        this.submitting = true;
        const qtysPayload = {};
        Object.keys(this.qtys).forEach(k => { qtysPayload[String(k)] = this.qtys[k]; });
        const payload = JSON.stringify({
            qtys: qtysPayload, pkgs: this.pkgs, addons: this.addons,
            discountPersons: this.discountPersons, payment: this.payment,
            amount_received: amountReceived || this.total,
        });
        this.$wire.syncAndCreate(payload)
            .then(() => {
                if (!this.orderError) {
                    this.showCashModal = false; this.showQrphModal = false;
                    const charged = this.total;
                    this.amountReceived = ''; this.resetForm();
                    this.showFlash('✅ Order created! Charged ₱' + this.fmt(charged));
                }
            })
            .finally(() => { this.submitting = false; });
    },

    processCashPayment() {
        const received = parseFloat(this.amountReceived);
        if (!received || received < this.total) {
            this.cashError = 'Amount must be at least ₱' + this.fmt(this.total); return;
        }
        this.submitOrder(received);
    }
}"
    @extras-sync.window="
        const fresh = $event.detail.products;
        const prevSnapshot = JSON.parse(JSON.stringify(products));
        const newQtys = {};
        fresh.forEach(p => { newQtys[p.id] = (p.id in qtys) ? qtys[p.id] : 0; });
        qtys = newQtys;
        products = fresh;
        const cats = [...new Set(fresh.map(p => p.category))];
        let newCategoryTarget = null;
        for (const old of prevSnapshot) {
            const updated = fresh.find(p => p.id === old.id);
            if (updated && updated.category !== old.category) { newCategoryTarget = updated.category; break; }
        }
        if (newCategoryTarget) {
            activeExtrasTab = newCategoryTarget;
        } else {
            const tabValid = cats.includes(activeExtrasTab) && fresh.some(p => p.category === activeExtrasTab);
            if (!tabValid) activeExtrasTab = cats[0] || null;
        }
    "
    @order-error.window="orderError = $event.detail.message"
    @notify-price-change.window="if($event.detail && $event.detail.p199){ prices.p199=$event.detail.p199; prices.p269=$event.detail.p269; prices.p349=$event.detail.p349; prices.addon=$event.detail.addon; }">

    
    <div wire:ignore style="margin-bottom:6px;">
        <div x-show="flash !== ''" x-cloak
             style="background:rgba(39,174,96,0.15);border:2px solid var(--success);border-radius:var(--r-md);padding:12px 18px;margin-bottom:10px;color:var(--success);font-weight:700;font-size:15px;"
             x-text="flash"></div>
        <div x-show="orderError !== ''" x-cloak
             style="background:rgba(192,57,43,0.1);border:2px solid var(--danger);border-radius:var(--r-md);padding:12px 18px;margin-bottom:10px;color:var(--danger);font-weight:700;font-size:15px;"
             x-text="'⚠️ ' + orderError"></div>
    </div>

    <div wire:ignore>
    
    <div class="ord-layout">

        
        <div class="ord-pkg-row">
            <div class="ord-pkg-row-label">Step 1 — Choose a package for each person</div>
            <div class="ord-pkg-cards">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    ['key'=>'p199','label'=>'Basic',  'price'=>$prices['basic'],   'color'=>'#8E9BAE','glow'=>'rgba(142,155,174,0.12)'],
                    ['key'=>'p269','label'=>'Premium','price'=>$prices['premium'], 'color'=>'#D4AF37','glow'=>'rgba(212,175,55,0.12)'],
                    ['key'=>'p349','label'=>'Deluxe', 'price'=>$prices['deluxe'],  'color'=>'#D4451A','glow'=>'rgba(212,69,26,0.15)'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="ord-pkg-card" :class="pkgs.<?php echo e($pkg['key']); ?> > 0 ? 'ord-pkg-on' : ''"
                     :style="pkgs.<?php echo e($pkg['key']); ?> > 0 ? 'border-color:<?php echo e($pkg['color']); ?>;box-shadow:0 0 0 3px <?php echo e($pkg['color']); ?>30,var(--shadow-md);background:<?php echo e($pkg['glow']); ?>;' : ''">
                    <div class="ord-pkg-top">
                        <span class="ord-pkg-badge" style="background:<?php echo e($pkg['color']); ?>22;color:<?php echo e($pkg['color']); ?>;border:1.5px solid <?php echo e($pkg['color']); ?>55;"><?php echo e($pkg['label']); ?></span>
                        <span class="ord-pkg-price">₱<?php echo e($pkg['price']); ?><span class="ord-pkg-pax">/pax</span></span>
                    </div>
                    <div class="ord-pkg-ctrl">
                        <button class="ord-qbtn ord-qbtn-minus" type="button"
                                title="Remove one person"
                                @click="changePkg('<?php echo e($pkg['key']); ?>', -1)">−</button>
                        <span class="ord-pkg-count-box">
                            <span class="ord-pkg-count-num" x-text="pkgs.<?php echo e($pkg['key']); ?> || 0"></span>
                            <span class="ord-pkg-count-lbl">pax</span>
                        </span>
                        <button class="ord-qbtn ord-qbtn-plus" type="button"
                                title="Add one person"
                                style="border-color:<?php echo e($pkg['color']); ?>66;color:<?php echo e($pkg['color']); ?>;background:<?php echo e($pkg['color']); ?>15;"
                                @click="changePkg('<?php echo e($pkg['key']); ?>', 1)">+</button>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="ord-options-row">
            <div class="ord-options-label">Step 2 — Add-ons &amp; Payment</div>
            <div class="ord-options-inner">

                
                <div class="ord-options-section">
                    <div class="ord-options-section-title">🍶 Add-ons <span style="color:var(--text-3);font-size:13px;font-weight:500;">₱<?php echo e($prices['addon']); ?> per person</span></div>
                    <div class="ord-addons-row">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [['key'=>'icedTea','emoji'=>'🧊','label'=>'Iced Tea'],['key'=>'cheese','emoji'=>'🧀','label'=>'Cheese']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="ord-addon-card" :class="addons.<?php echo e($addon['key']); ?> > 0 ? 'ord-addon-on' : ''">
                            <div class="ord-addon-top">
                                <span class="ord-addon-emoji"><?php echo e($addon['emoji']); ?></span>
                                <span class="ord-addon-name"><?php echo e($addon['label']); ?></span>
                                <span class="ord-addon-price">₱<?php echo e($prices['addon']); ?></span>
                            </div>
                            <div class="ord-addon-ctrl">
                                <button class="ord-qbtn ord-qbtn-lg ord-qbtn-minus" type="button"
                                        @click="addons.<?php echo e($addon['key']); ?> = Math.max(0,(addons.<?php echo e($addon['key']); ?>||0)-1)">−</button>
                                <span class="ord-addon-count" x-text="addons.<?php echo e($addon['key']); ?> || 0"></span>
                                <button class="ord-qbtn ord-qbtn-lg ord-qbtn-plus" type="button"
                                        @click="addons.<?php echo e($addon['key']); ?> = (addons.<?php echo e($addon['key']); ?>||0)+1">+</button>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="ord-options-divider"></div>

                
                <div class="ord-options-section">
                    <div class="ord-options-section-title">💳 Payment Method</div>
                    <div class="ord-pay-row">
                        <button type="button" @click="payment='Cash'"
                                :class="payment==='Cash' ? 'ord-pay-card active ord-pay-cash' : 'ord-pay-card ord-pay-cash'">
                            <span class="ord-pay-icon">💵</span>
                            <span class="ord-pay-label">Cash</span>
                            <span class="ord-pay-check" x-show="payment==='Cash'">✓</span>
                        </button>
                        <button type="button" @click="payment='QRPH'"
                                :class="payment==='QRPH' ? 'ord-pay-card active ord-pay-qr' : 'ord-pay-card ord-pay-qr'">
                            <span class="ord-pay-icon">📱</span>
                            <span class="ord-pay-label">QRPH</span>
                            <span class="ord-pay-check" x-show="payment==='QRPH'">✓</span>
                        </button>
                    </div>
                </div>

                <div class="ord-options-divider"></div>

                
                <div class="ord-options-section" x-show="totalPeople > 0" x-cloak>
                    <div class="ord-options-section-title">🎫 Discounts</div>
                    <button type="button" class="ord-disc-btn"
                            @click="discountPanelOpen = true; syncDiscountPersons()">
                        <span class="ord-disc-btn-left">
                            <span class="ord-disc-btn-label">SC / PWD / Kids</span>
                            <span class="ord-disc-btn-sub"
                                  x-text="discountPersons.length > 0 ? discountPersons.length + ' applied · −₱' + fmt(discountAmount) : 'Tap to assign discounts'"></span>
                        </span>
                        <span class="ord-disc-btn-right">
                            <template x-if="discountPersons.length > 0">
                                <span class="ord-disc-badge" x-text="discountPersons.length"></span>
                            </template>
                            <span style="font-size:20px;color:var(--text-3);">›</span>
                        </span>
                    </button>
                </div>

            </div>
        </div>

        
        <div class="ord-bottom-row">

            
            <div class="ord-extras-panel" x-show="products.length > 0" x-cloak>
                <div class="ord-extras-header">
                    <div class="ord-extras-title">
                        🛒 <span>Extras</span>
                        <template x-if="activeExtras.length > 0">
                            <span class="ord-extras-count" x-text="activeExtras.length + ' added'"></span>
                        </template>
                    </div>
                    <div class="ord-extras-tabs">
                        <template x-for="cat in extraCategories" :key="cat">
                            <button type="button" @click="activeExtrasTab = cat"
                                    :class="activeExtrasTab === cat ? 'ord-tab active' : 'ord-tab'"
                                    x-text="cat"></button>
                        </template>
                    </div>
                </div>
                <div class="ord-extras-grid">
                    <template x-for="p in productsInTab" :key="p.id">
                        <div class="ord-extra-card"
                             :class="qty(p.id) > 0 ? 'ord-extra-on' : ''"
                             :style="p.stock <= 0 ? 'opacity:0.35;pointer-events:none;' : ''">
                            <template x-if="qty(p.id) > 0">
                                <div class="ord-extra-qty-badge" x-text="qty(p.id)"></div>
                            </template>
                            <div class="ord-extra-name" x-text="p.name"></div>
                            <div class="ord-extra-price" x-text="'₱'+p.price"></div>
                            <div class="ord-extra-stock"
                                 :style="p.stock<=0?'color:var(--danger)':(p.lowStock?'color:var(--warning)':'color:var(--success)')">
                                <span x-text="p.stock<=0?'Out of stock':(p.lowStock?'⚠ '+p.stock+' '+p.unit:'✓ '+p.stock+' '+p.unit)"></span>
                            </div>
                            <div class="ord-extra-ctrl">
                                <button class="ord-qbtn ord-qbtn-sm ord-qbtn-minus" type="button" @click="decrement(p.id)">−</button>
                                <span class="ord-qval ord-qval-sm" x-text="qty(p.id)"></span>
                                <button class="ord-qbtn ord-qbtn-sm ord-qbtn-plus" type="button" @click="increment(p.id,p.stock)">+</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            
            <div class="ord-extras-panel ord-extras-empty" x-show="products.length === 0" x-cloak>
                <div style="text-align:center;padding:40px;color:var(--text-3);">
                    <div style="font-size:2.5em;margin-bottom:10px;">🛒</div>
                    <div style="font-size:16px;font-weight:600;">No extras available</div>
                    <div style="font-size:13px;margin-top:4px;">Add products to Inventory and toggle them as extras</div>
                </div>
            </div>

        </div>

        
        <div class="ord-bill-panel">

            <div class="ord-bill-header">
                <span>🧾 Bill</span>
                <span class="ord-bill-pax" x-show="totalPeople > 0" x-cloak>
                    <span x-text="totalPeople"></span> pax
                </span>
            </div>

            
            <div class="ord-bill-empty" x-show="!hasAnything" x-cloak>
                <div style="font-size:2.8em;margin-bottom:10px;">🍖</div>
                <div style="font-size:16px;font-weight:700;color:var(--text-2);">Nothing yet</div>
                <div style="font-size:13px;color:var(--text-3);margin-top:4px;">Select packages above to begin</div>
            </div>

            
            <div class="ord-bill-lines" x-show="hasAnything" x-cloak>
                <div class="ord-bill-line" x-show="pkgs.p199>0">
                    <div>
                        <div class="ord-bill-line-name">Basic Package</div>
                        <div class="ord-bill-line-sub" x-text="pkgs.p199+' pax × ₱'+prices.p199"></div>
                    </div>
                    <div class="ord-bill-line-amt" x-text="'₱'+fmt(pkgs.p199*prices.p199)"></div>
                </div>
                <div class="ord-bill-line" x-show="pkgs.p269>0">
                    <div>
                        <div class="ord-bill-line-name">Premium Package</div>
                        <div class="ord-bill-line-sub" x-text="pkgs.p269+' pax × ₱'+prices.p269"></div>
                    </div>
                    <div class="ord-bill-line-amt" x-text="'₱'+fmt(pkgs.p269*prices.p269)"></div>
                </div>
                <div class="ord-bill-line" x-show="pkgs.p349>0">
                    <div>
                        <div class="ord-bill-line-name">Deluxe Package</div>
                        <div class="ord-bill-line-sub" x-text="pkgs.p349+' pax × ₱'+prices.p349"></div>
                    </div>
                    <div class="ord-bill-line-amt" x-text="'₱'+fmt(pkgs.p349*prices.p349)"></div>
                </div>
                <div class="ord-bill-line" x-show="addons.icedTea>0">
                    <div>
                        <div class="ord-bill-line-name">🧊 Iced Tea</div>
                        <div class="ord-bill-line-sub" x-text="addons.icedTea+' pax × ₱'+prices.addon"></div>
                    </div>
                    <div class="ord-bill-line-amt" x-text="'₱'+fmt(addons.icedTea*prices.addon)"></div>
                </div>
                <div class="ord-bill-line" x-show="addons.cheese>0">
                    <div>
                        <div class="ord-bill-line-name">🧀 Cheese</div>
                        <div class="ord-bill-line-sub" x-text="addons.cheese+' pax × ₱'+prices.addon"></div>
                    </div>
                    <div class="ord-bill-line-amt" x-text="'₱'+fmt(addons.cheese*prices.addon)"></div>
                </div>
                <template x-for="item in activeExtras" :key="item.id">
                    <div class="ord-bill-line">
                        <div>
                            <div class="ord-bill-line-name" x-text="item.name"></div>
                            <div class="ord-bill-line-sub" x-text="item.qty+'× ₱'+item.price"></div>
                        </div>
                        <div class="ord-bill-line-amt" x-text="'₱'+fmt(item.qty*item.price)"></div>
                    </div>
                </template>
            </div>

            
            <div class="ord-bill-totals">
                <div class="ord-bill-total-row">
                    <span>Subtotal</span>
                    <span x-text="'₱'+fmt(subtotal)"></span>
                </div>
                <template x-for="g in discountBreakdown" :key="g.type+g.pkg">
                    <div class="ord-bill-total-row ord-bill-discount-row">
                        <span x-text="(g.type==='senior'?'👴 SC':g.type==='pwd'?'♿ PWD':'🧒 Kid')+' ×'+g.count+' ('+pkgName(g.pkg)+')'"></span>
                        <span x-text="'−₱'+fmt(g.count * pkgPrice(g.pkg) * g.pct/100)"></span>
                    </div>
                </template>
            </div>

            
            <div class="ord-bill-grand">
                <span>TOTAL</span>
                <span x-text="'₱'+fmt(total)"></span>
            </div>

            
            <button type="button" class="ord-charge-btn"
                    @click="handleCreateOrder()"
                    :disabled="submitting || !hasAnything">
                <template x-if="!submitting">
                    <span style="display:flex;align-items:center;gap:10px;justify-content:center;">
                        <span style="font-size:1.1em;">💳</span>
                        <span>Charge</span>
                        <span class="ord-charge-amt" x-text="'₱'+fmt(total)"></span>
                    </span>
                </template>
                <template x-if="submitting">
                    <span>⏳ Processing...</span>
                </template>
            </button>

            <button type="button" x-show="hasAnything" x-cloak @click="resetForm()"
                    class="ord-clear-btn">
                🗑️ Clear Order
            </button>

        </div>
    </div>

    
    <template x-teleport="body">
        <div x-show="showCashModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="modal-backdrop" @click.self="showCashModal=false">
            <div class="modal-box" @click.stop style="max-width:420px;">
                <div class="modal-header">
                    <h2 style="color:var(--primary);">💵 Cash Payment</h2>
                    <button class="close-modal" @click="showCashModal=false">×</button>
                </div>
                <div style="background:#F5F2EF;padding:20px;border-radius:var(--r-md);margin-bottom:20px;text-align:center;border:1.5px solid var(--border-strong);">
                    <div style="font-size:13px;color:var(--text-3);margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;font-weight:700;">Order Total</div>
                    <div style="font-size:44px;font-weight:900;color:var(--primary);font-family:'DM Mono',monospace;" x-text="'₱'+fmt(total)"></div>
                </div>
                <template x-if="cashError">
                    <div style="background:rgba(192,57,43,0.1);border:2px solid var(--danger);border-radius:var(--r-sm);padding:12px;margin-bottom:16px;color:var(--danger);font-weight:700;font-size:14px;" x-text="cashError"></div>
                </template>
                <div class="form-group">
                    <label>Amount Received (₱)</label>
                    <input type="number" x-model="amountReceived" placeholder="0.00" min="0" step="0.01"
                           style="font-size:26px;text-align:center;font-weight:800;"
                           @keyup.enter="processCashPayment()" x-ref="cashInput">
                </div>
                <div style="background:#F5F2EF;padding:16px;border-radius:var(--r-md);margin-bottom:20px;text-align:center;border:1.5px solid var(--border-strong);">
                    <div style="font-size:13px;color:var(--text-3);margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;font-weight:700;">Change</div>
                    <div style="font-size:36px;font-weight:800;color:var(--success);font-family:'DM Mono',monospace;" x-text="'₱'+fmt(change)"></div>
                </div>
                <div style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;">
                    <template x-for="preset in [100,200,500,1000]" :key="preset">
                        <button type="button"
                                @click="amountReceived = String(Math.ceil(total / preset) * preset)"
                                style="flex:1;min-width:60px;padding:10px 4px;background:var(--bg-elevated);border:2px solid var(--border-strong);border-radius:var(--r-sm);color:var(--text-2);font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;transition:var(--transition);"
                                @mouseenter="$el.style.borderColor='var(--primary)';$el.style.color='var(--primary)';"
                                @mouseleave="$el.style.borderColor='var(--border-strong)';$el.style.color='var(--text-2)';"
                                x-text="'₱'+preset"></button>
                    </template>
                    <button type="button"
                            @click="amountReceived = String(total)"
                            style="flex:1;min-width:60px;padding:10px 4px;background:var(--bg-elevated);border:2px solid var(--border-strong);border-radius:var(--r-sm);color:var(--success);font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;transition:var(--transition);"
                            @mouseenter="$el.style.borderColor='var(--success)';"
                            @mouseleave="$el.style.borderColor='var(--border-strong)';">
                        Exact
                    </button>
                </div>
                <button type="button" class="btn btn-success" @click="processCashPayment()" :disabled="submitting" style="width:100%;font-size:17px;padding:15px;">
                    <span x-show="!submitting">✓ Confirm Payment</span>
                    <span x-show="submitting">Processing...</span>
                </button>
            </div>
        </div>
    </template>

    
    <template x-teleport="body">
        <div x-show="showQrphModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="modal-backdrop" @click.self="showQrphModal=false">
            <div class="modal-box" @click.stop style="max-width:420px;">
                <div class="modal-header">
                    <h2 style="color:var(--info);">📱 QRPH Payment</h2>
                    <button class="close-modal" @click="showQrphModal=false">×</button>
                </div>
                <div style="background:#F5F2EF;padding:20px;border-radius:var(--r-md);margin-bottom:20px;text-align:center;border:1.5px solid var(--border-strong);">
                    <div style="font-size:13px;color:var(--text-3);margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;font-weight:700;">Order Total</div>
                    <div style="font-size:44px;font-weight:900;color:var(--info);font-family:'DM Mono',monospace;" x-text="'₱'+fmt(total)"></div>
                </div>
                <div style="background:rgba(36,113,163,0.1);border:2px solid rgba(36,113,163,0.3);border-radius:var(--r-md);padding:20px;margin-bottom:20px;text-align:center;">
                    <div style="font-size:3em;margin-bottom:10px;">📲</div>
                    <div style="color:var(--text-2);font-size:16px;line-height:1.6;font-weight:600;">Ask the customer to scan your QR code<br>and complete the payment first.</div>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="button" class="btn" @click="showQrphModal=false"
                            style="flex:1;background:var(--bg-elevated);color:var(--text-2);border:2px solid var(--border-strong);">
                        ✕ Cancel
                    </button>
                    <button type="button" class="btn btn-success" @click="submitOrder(total)" :disabled="submitting" style="flex:2;font-size:17px;padding:15px;">
                        <span x-show="!submitting">✓ Payment Received</span>
                        <span x-show="submitting">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    
    <template x-teleport="body">
        <div x-show="discountPanelOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="modal-backdrop" @click.self="discountPanelOpen = false">
            <div class="modal-box disc-modal" @click.stop>
                <div class="disc-modal-header">
                    <div>
                        <h2 class="disc-modal-title">🎫 Discounts</h2>
                        <p class="disc-modal-sub">Select a discount type for each person</p>
                    </div>
                    <button class="close-modal" @click="discountPanelOpen = false">×</button>
                </div>
                <div class="disc-modal-legend">
                    <span class="disc-legend-pill disc-legend-sc">👴 Senior Citizen &nbsp;−20%</span>
                    <span class="disc-legend-pill disc-legend-pwd">♿ PWD &nbsp;−20%</span>
                    <span class="disc-legend-pill disc-legend-kid">🧒 Kid &nbsp;−10%</span>
                </div>
                <div class="disc-modal-list">
                    <template x-for="row in paxRows" :key="row.pkg + row.paxNum">
                        <div class="disc-person-row" :class="row.type !== 'none' ? 'disc-person-row-active' : ''">
                            <div class="disc-person-info">
                                <span class="disc-person-name" x-text="pkgName(row.pkg) + ' Pax ' + row.paxNum"></span>
                                <span class="disc-person-pkg"
                                      x-text="row.type !== 'none' ? '−₱'+(pkgPrice(row.pkg)*(row.type==='child'?0.1:0.2)).toFixed(0) : '₱'+pkgPrice(row.pkg)"></span>
                            </div>
                            <div class="disc-person-btns">
                                <button type="button" @click="setDiscount(row.pkg, row.paxNum, 'none')"
                                        :class="row.type === 'none' ? 'disc-type-btn disc-type-none active' : 'disc-type-btn disc-type-none'">—</button>
                                <button type="button" @click="setDiscount(row.pkg, row.paxNum, 'senior')"
                                        :class="row.type === 'senior' ? 'disc-type-btn disc-type-sc active' : 'disc-type-btn disc-type-sc'">👴 SC</button>
                                <button type="button" @click="setDiscount(row.pkg, row.paxNum, 'pwd')"
                                        :class="row.type === 'pwd' ? 'disc-type-btn disc-type-pwd active' : 'disc-type-btn disc-type-pwd'">♿ PWD</button>
                                <button type="button" @click="setDiscount(row.pkg, row.paxNum, 'child')"
                                        :class="row.type === 'child' ? 'disc-type-btn disc-type-kid active' : 'disc-type-btn disc-type-kid'">🧒 Kid</button>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="disc-modal-footer">
                    <template x-if="discountPersons.length > 0">
                        <div class="disc-modal-summary">
                            <span x-text="discountPersons.length + ' discount' + (discountPersons.length > 1 ? 's' : '') + ' applied'"></span>
                            <span class="disc-modal-total" x-text="'−₱' + fmt(discountAmount)"></span>
                        </div>
                    </template>
                    <template x-if="discountPersons.length === 0">
                        <div class="disc-modal-summary disc-modal-summary-empty">No discounts applied</div>
                    </template>
                    <button type="button" class="btn btn-success disc-modal-done" @click="discountPanelOpen = false">✓ Done</button>
                </div>
            </div>
        </div>
    </template>

    </div>
</div>
<?php /**PATH C:\Users\Mark\samgyeopsal-pos - Copy\resources\views/livewire/new-order.blade.php ENDPATH**/ ?>