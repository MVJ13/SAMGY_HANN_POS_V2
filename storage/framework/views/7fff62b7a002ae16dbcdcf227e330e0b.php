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
        const cap = max !== undefined ? Math.floor(max) : Infinity;
        this.qtys = { ...this.qtys, [id]: cur < cap ? cur + 1 : cur };
    },
    decrement(id) { this.qtys = { ...this.qtys, [id]: Math.max(0, (this.qtys[id] || 0) - 1) }; },

    get totalPeople()  { return (this.pkgs.p199||0) + (this.pkgs.p269||0) + (this.pkgs.p349||0); },
    get activeExtras() {
        return this.products.filter(p => (this.qtys[p.id]||0) > 0).map(p => ({ ...p, qty: this.qtys[p.id] }));
    },
    get subtotal() {
        return (this.pkgs.p199||0)*199 + (this.pkgs.p269||0)*269 + (this.pkgs.p349||0)*349
             + (this.addons.icedTea||0)*25 + (this.addons.cheese||0)*25
             + this.products.reduce((s,p) => s + (this.qtys[p.id]||0)*p.price, 0);
    },
    pkgPrice(pkg) { return pkg === 'p199' ? 199 : pkg === 'p269' ? 269 : 349; },
    pkgName(pkg)  { return pkg === 'p199' ? 'Basic' : pkg === 'p269' ? 'Premium' : 'Deluxe'; },

    // discountPersons is now a flat array of {pkg, paxNum, type, pct}
    // one entry per physical person, rebuilt whenever pkg counts change
    get paxRows() {
        const rows = [];
        const pkgs = ['p199','p269','p349'];
        for (const pkg of pkgs) {
            const count = this.pkgs[pkg] || 0;
            for (let i = 1; i <= count; i++) {
                const existing = this.discountPersons.find(d => d.pkg === pkg && d.paxNum === i);
                rows.push({ pkg, paxNum: i, type: existing ? existing.type : 'none' });
            }
        }
        return rows;
    },

    setDiscount(pkg, paxNum, type) {
        // Remove existing entry for this pax slot then add new one if not 'none'
        const filtered = this.discountPersons.filter(d => !(d.pkg === pkg && d.paxNum === paxNum));
        if (type !== 'none') {
            filtered.push({ pkg, paxNum, type, pct: type === 'child' ? 10 : 20 });
        }
        this.discountPersons = filtered;
    },

    // Sync: remove discount entries for pax slots that no longer exist
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
    get total() { return Math.round((this.subtotal - this.discountAmount) * 100) / 100; },
    get change()         { return Math.max(0, (parseFloat(this.amountReceived)||0) - this.total); },
    get hasAnything()    {
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

        // Snapshot current products as plain objects BEFORE replacing (avoid Alpine proxy aliasing)
        const prevSnapshot = JSON.parse(JSON.stringify(products));

        // Preserve quantities for products still present
        const newQtys = {};
        fresh.forEach(p => { newQtys[p.id] = (p.id in qtys) ? qtys[p.id] : 0; });
        qtys = newQtys;
        products = fresh;

        const cats = [...new Set(fresh.map(p => p.category))];

        // Detect category changes by comparing plain snapshots (not reactive proxies)
        let newCategoryTarget = null;
        for (const old of prevSnapshot) {
            const updated = fresh.find(p => p.id === old.id);
            if (updated && updated.category !== old.category) {
                newCategoryTarget = updated.category;
                break;
            }
        }

        if (newCategoryTarget) {
            // A product moved categories — jump the tab to where it landed
            activeExtrasTab = newCategoryTarget;
        } else {
            // No category change — just ensure the active tab still has products
            const tabValid = cats.includes(activeExtrasTab) && fresh.some(p => p.category === activeExtrasTab);
            if (!tabValid) activeExtrasTab = cats[0] || null;
        }
    "
    @order-error.window="orderError = $event.detail.message">

    
    <div wire:ignore style="margin-bottom:4px;">
        <div x-show="flash !== ''" x-cloak
             style="background:rgba(6,214,160,0.15);border:2px solid var(--success);border-radius:var(--radius-sm);padding:10px 16px;margin-bottom:10px;color:var(--success);font-weight:600;font-size:0.88em;"
             x-text="flash"></div>
        <div x-show="orderError !== ''" x-cloak
             style="background:rgba(230,57,70,0.1);border:2px solid var(--danger);border-radius:var(--radius-sm);padding:10px 16px;margin-bottom:10px;color:var(--danger);font-weight:600;font-size:0.88em;"
             x-text="'⚠️ ' + orderError"></div>
    </div>

    <div wire:ignore>
    
    <div class="pos3-layout">

        
        <div class="pos3-col pos3-left">

            
            <div class="pos3-panel">
                <div class="pos3-panel-title">📦 Package</div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                    ['key'=>'p199','label'=>'Basic',  'price'=>199,'accent'=>'#8A8A8F'],
                    ['key'=>'p269','label'=>'Premium','price'=>269,'accent'=>'#FFB703'],
                    ['key'=>'p349','label'=>'Deluxe', 'price'=>349,'accent'=>'#FF6B6B'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="pos3-pkg" :class="pkgs.<?php echo e($pkg['key']); ?> > 0 ? 'pos3-pkg-on' : ''">
                    <div class="pos3-pkg-left">
                        <div class="pos3-pkg-badge" style="background:<?php echo e($pkg['accent']); ?>22;color:<?php echo e($pkg['accent']); ?>;border:1.5px solid <?php echo e($pkg['accent']); ?>44;"><?php echo e($pkg['label']); ?></div>
                        <div class="pos3-pkg-price">₱<?php echo e($pkg['price']); ?><span class="pos3-pkg-unit">/pax</span></div>
                    </div>
                    <div class="pos3-pkg-right">
                        <button class="pos3-qbtn pos3-qbtn-minus" type="button"
                                @click="pkgs.<?php echo e($pkg['key']); ?> = Math.max(0,(pkgs.<?php echo e($pkg['key']); ?>||0)-1)">−</button>
                        <span class="pos3-qval" x-text="pkgs.<?php echo e($pkg['key']); ?> || 0"></span>
                        <button class="pos3-qbtn pos3-qbtn-plus" type="button"
                                @click="pkgs.<?php echo e($pkg['key']); ?> = (pkgs.<?php echo e($pkg['key']); ?>||0)+1">+</button>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="pos3-panel">
                <div class="pos3-panel-title">➕ Add-ons <span style="color:var(--text-muted);font-weight:400;font-size:0.85em;">₱25/pax</span></div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [['key'=>'icedTea','emoji'=>'🧊','label'=>'Iced Tea'],['key'=>'cheese','emoji'=>'🧀','label'=>'Cheese']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="pos3-addon" :class="addons.<?php echo e($addon['key']); ?> > 0 ? 'pos3-addon-on' : ''">
                    <span class="pos3-addon-icon"><?php echo e($addon['emoji']); ?></span>
                    <span class="pos3-addon-name"><?php echo e($addon['label']); ?></span>
                    <div class="pos3-addon-ctrl">
                        <button class="pos3-qbtn pos3-qbtn-minus pos3-qbtn-sm" type="button"
                                @click="addons.<?php echo e($addon['key']); ?> = Math.max(0,(addons.<?php echo e($addon['key']); ?>||0)-1)">−</button>
                        <span class="pos3-qval" x-text="addons.<?php echo e($addon['key']); ?> || 0"></span>
                        <button class="pos3-qbtn pos3-qbtn-plus pos3-qbtn-sm" type="button"
                                @click="addons.<?php echo e($addon['key']); ?> = (addons.<?php echo e($addon['key']); ?>||0)+1">+</button>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="pos3-panel">
                <div class="pos3-panel-title">💳 Payment</div>
                <div style="display:flex;gap:6px;margin-bottom:10px;">
                    <button type="button" @click="payment='Cash'"
                            :class="payment==='Cash' ? 'pay-btn pay-btn-cash active' : 'pay-btn pay-btn-cash'">
                        💵 Cash
                    </button>
                    <button type="button" @click="payment='QRPH'"
                            :class="payment==='QRPH' ? 'pay-btn pay-btn-qr active' : 'pay-btn pay-btn-qr'">
                        📱 QRPH
                    </button>
                </div>
                
                <div x-show="totalPeople > 0" x-cloak>
                    <button type="button" class="disc-trigger-btn"
                            @click="discountPanelOpen = true; syncDiscountPersons()">
                        <span class="disc-trigger-left">
                            <span class="disc-trigger-icon">🎫</span>
                            <span class="disc-trigger-text">
                                <span class="disc-trigger-label">Discounts</span>
                                <span class="disc-trigger-sub"
                                      x-text="discountPersons.length > 0 ? discountPersons.length + ' applied · −₱' + fmt(discountAmount) : 'SC · PWD · Kids'"></span>
                            </span>
                        </span>
                        <span class="disc-trigger-right">
                            <template x-if="discountPersons.length > 0">
                                <span class="disc-trigger-badge" x-text="discountPersons.length"></span>
                            </template>
                            <span class="disc-trigger-arrow">›</span>
                        </span>
                    </button>
                </div>
            </div>

        </div>

        
        <div class="pos3-col pos3-middle" x-show="products.length > 0" x-cloak>
            <div class="pos3-panel pos3-extras-panel">
                <div class="pos3-extras-header">
                    <div class="pos3-panel-title" style="margin:0;">🛒 Extras</div>
                    <div style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
                        <template x-for="cat in extraCategories" :key="cat">
                            <button type="button" @click="activeExtrasTab = cat"
                                    :class="activeExtrasTab === cat ? 'cat-pill active' : 'cat-pill'"
                                    x-text="cat"></button>
                        </template>
                    </div>
                    <template x-if="activeExtras.length > 0">
                        <div class="pos3-extras-badge" x-text="activeExtras.length + ' added'"></div>
                    </template>
                </div>

                <div class="pos3-extras-grid">
                    <template x-for="p in productsInTab" :key="p.id">
                        <div class="pos3-extra-card"
                             :class="qty(p.id) > 0 ? 'pos3-extra-on' : ''"
                             :style="p.stock <= 0 ? 'opacity:0.3;pointer-events:none;' : ''">

                            <div class="pos3-extra-top">
                                <div class="pos3-extra-name" x-text="p.name"></div>
                                <div class="pos3-extra-price" x-text="'₱'+p.price"></div>
                            </div>

                            <div class="pos3-extra-stock"
                                 :style="p.stock<=0?'color:var(--danger)':(p.lowStock?'color:var(--warning)':'color:var(--success)')">
                                <span x-text="p.stock<=0?'Out of stock':(p.lowStock?'⚠ Low · '+p.stock+' '+p.unit:'✓ '+p.stock+' '+p.unit)"></span>
                            </div>

                            <div class="pos3-extra-ctrl">
                                <button class="pos3-qbtn pos3-qbtn-minus pos3-qbtn-sm" type="button"
                                        @click="decrement(p.id)">−</button>
                                <span class="pos3-qval" style="min-width:20px;font-size:0.9em;" x-text="qty(p.id)"></span>
                                <button class="pos3-qbtn pos3-qbtn-plus pos3-qbtn-sm" type="button"
                                        @click="increment(p.id,p.stock)">+</button>
                            </div>

                            
                            <template x-if="qty(p.id) > 0">
                                <div class="pos3-extra-qty-pill" x-text="qty(p.id)"></div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        
        <div class="pos3-col pos3-right">
            <div class="pos3-panel pos3-summary-panel">

                <div class="pos3-panel-title">🧾 Order</div>

                
                <template x-if="!hasAnything">
                    <div class="pos3-empty">
                        <div style="font-size:2.4em;margin-bottom:8px;">🍖</div>
                        <div>Add items to begin</div>
                    </div>
                </template>

                
                <template x-if="hasAnything">
                    <div class="pos3-items">

                        <template x-if="pkgs.p199>0">
                            <div class="pos3-line">
                                <div>
                                    <div class="pos3-line-name">Basic Package</div>
                                    <div class="pos3-line-sub" x-text="pkgs.p199+' pax × ₱199'"></div>
                                </div>
                                <div class="pos3-line-amt" x-text="'₱'+fmt(pkgs.p199*199)"></div>
                            </div>
                        </template>
                        <template x-if="pkgs.p269>0">
                            <div class="pos3-line">
                                <div>
                                    <div class="pos3-line-name">Premium Package</div>
                                    <div class="pos3-line-sub" x-text="pkgs.p269+' pax × ₱269'"></div>
                                </div>
                                <div class="pos3-line-amt" x-text="'₱'+fmt(pkgs.p269*269)"></div>
                            </div>
                        </template>
                        <template x-if="pkgs.p349>0">
                            <div class="pos3-line">
                                <div>
                                    <div class="pos3-line-name">Deluxe Package</div>
                                    <div class="pos3-line-sub" x-text="pkgs.p349+' pax × ₱349'"></div>
                                </div>
                                <div class="pos3-line-amt" x-text="'₱'+fmt(pkgs.p349*349)"></div>
                            </div>
                        </template>
                        <template x-if="addons.icedTea>0">
                            <div class="pos3-line">
                                <div>
                                    <div class="pos3-line-name">🧊 Iced Tea</div>
                                    <div class="pos3-line-sub" x-text="addons.icedTea+' pax × ₱25'"></div>
                                </div>
                                <div class="pos3-line-amt" x-text="'₱'+fmt(addons.icedTea*25)"></div>
                            </div>
                        </template>
                        <template x-if="addons.cheese>0">
                            <div class="pos3-line">
                                <div>
                                    <div class="pos3-line-name">🧀 Cheese</div>
                                    <div class="pos3-line-sub" x-text="addons.cheese+' pax × ₱25'"></div>
                                </div>
                                <div class="pos3-line-amt" x-text="'₱'+fmt(addons.cheese*25)"></div>
                            </div>
                        </template>
                        <template x-for="item in activeExtras" :key="item.id">
                            <div class="pos3-line">
                                <div>
                                    <div class="pos3-line-name" x-text="item.name"></div>
                                    <div class="pos3-line-sub" x-text="item.qty+'× ₱'+item.price"></div>
                                </div>
                                <div class="pos3-line-amt" x-text="'₱'+fmt(item.qty*item.price)"></div>
                            </div>
                        </template>
                    </div>
                </template>

                
                <div class="pos3-totals">
                    <div class="pos3-totals-row">
                        <span>👥 People</span>
                        <span x-text="totalPeople || '—'"></span>
                    </div>
                    <div class="pos3-totals-row">
                        <span>Subtotal</span>
                        <span x-text="'₱'+fmt(subtotal)"></span>
                    </div>
                    <template x-if="discountPersons.length > 0">
                        <template x-for="g in discountBreakdown" :key="g.type+g.pkg">
                            <div class="pos3-totals-row" style="color:var(--success);font-size:0.84em;">
                                <span x-text="(g.type==='senior'?'👴 Senior':g.type==='pwd'?'♿ PWD':'🧒 Child')+' ×'+g.count+' ('+pkgName(g.pkg)+')'"></span>
                                <span x-text="'−₱'+fmt(g.count * pkgPrice(g.pkg) * g.pct/100)"></span>
                            </div>
                        </template>
                        <div class="pos3-totals-row" style="color:var(--success);font-weight:700;border-top:1px solid rgba(6,214,160,0.2);padding-top:3px;margin-top:1px;">
                            <span>Total Discount</span>
                            <span x-text="'−₱'+fmt(discountAmount)"></span>
                        </div>
                    </template>
                    <div class="pos3-grand-total">
                        <span>TOTAL</span>
                        <span x-text="'₱'+fmt(total)"></span>
                    </div>
                </div>

            </div>

            
            <button type="button" class="pos3-charge-btn"
                    @click="handleCreateOrder()"
                    :disabled="submitting || !hasAnything">
                <template x-if="!submitting">
                    <span>
                        <span style="font-size:1.1em;">💳</span>
                        Charge
                        <span class="pos3-charge-amt" x-text="'₱'+fmt(total)"></span>
                    </span>
                </template>
                <template x-if="submitting">
                    <span>⏳ Processing...</span>
                </template>
            </button>

            <button type="button" x-show="hasAnything" x-cloak @click="resetForm()" class="clear-btn" style="width:100%;margin-top:6px;">
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
            <div class="modal-box" @click.stop style="max-width:400px;">
                <div class="modal-header">
                    <h2 style="color:var(--primary);">💵 Cash Payment</h2>
                    <button class="close-modal" @click="showCashModal=false">×</button>
                </div>
                <div style="background:var(--bg-input);padding:20px;border-radius:var(--radius-md);margin-bottom:20px;text-align:center;">
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;">Order Total</div>
                    <div style="font-size:40px;font-weight:800;color:var(--primary);" x-text="'₱'+fmt(total)"></div>
                </div>
                <template x-if="cashError">
                    <div style="background:rgba(230,57,70,0.1);border:2px solid var(--danger);border-radius:var(--radius-sm);padding:12px;margin-bottom:16px;color:var(--danger);font-weight:600;font-size:13px;" x-text="cashError"></div>
                </template>
                <div class="form-group">
                    <label>Amount Received</label>
                    <input type="number" x-model="amountReceived" placeholder="0.00" min="0" step="0.01"
                           style="font-size:24px;text-align:center;font-weight:700;"
                           @keyup.enter="processCashPayment()" x-ref="cashInput">
                </div>
                <div style="background:var(--bg-input);padding:16px;border-radius:var(--radius-md);margin-bottom:20px;text-align:center;">
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">Change</div>
                    <div style="font-size:32px;font-weight:700;color:var(--success);" x-text="'₱'+fmt(change)"></div>
                </div>
                <div style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;">
                    <template x-for="preset in [100,200,500,1000]" :key="preset">
                        <button type="button"
                                @click="amountReceived = String(Math.ceil(total / preset) * preset)"
                                style="flex:1;min-width:60px;padding:8px 4px;background:var(--bg-input);border:2px solid var(--border);border-radius:var(--radius-sm);color:var(--text-secondary);font-size:0.8em;cursor:pointer;transition:var(--transition);"
                                @mouseenter="$el.style.borderColor='var(--primary)';$el.style.color='var(--primary)';"
                                @mouseleave="$el.style.borderColor='var(--border)';$el.style.color='var(--text-secondary)';"
                                x-text="'₱'+preset"></button>
                    </template>
                    <button type="button"
                            @click="amountReceived = String(total)"
                            style="flex:1;min-width:60px;padding:8px 4px;background:var(--bg-input);border:2px solid var(--border);border-radius:var(--radius-sm);color:var(--text-secondary);font-size:0.8em;cursor:pointer;transition:var(--transition);"
                            @mouseenter="$el.style.borderColor='var(--success)';$el.style.color='var(--success)';"
                            @mouseleave="$el.style.borderColor='var(--border)';$el.style.color='var(--text-secondary)';">
                        Exact
                    </button>
                </div>
                <button type="button" class="btn btn-success" @click="processCashPayment()" :disabled="submitting" style="width:100%;font-size:16px;padding:14px;">
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
            <div class="modal-box" @click.stop style="max-width:400px;">
                <div class="modal-header">
                    <h2 style="color:var(--info);">📱 QRPH Payment</h2>
                    <button class="close-modal" @click="showQrphModal=false">×</button>
                </div>
                <div style="background:var(--bg-input);padding:20px;border-radius:var(--radius-md);margin-bottom:20px;text-align:center;">
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;">Order Total</div>
                    <div style="font-size:40px;font-weight:800;color:var(--info);" x-text="'₱'+fmt(total)"></div>
                </div>
                <div style="background:rgba(17,138,178,0.08);border:2px solid rgba(17,138,178,0.3);border-radius:var(--radius-md);padding:16px;margin-bottom:20px;text-align:center;">
                    <div style="font-size:32px;margin-bottom:8px;">📲</div>
                    <div style="color:var(--text-secondary);font-size:0.9em;line-height:1.6;">Ask the customer to scan your QR code and complete the payment before confirming.</div>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="button" class="btn" @click="showQrphModal=false"
                            style="flex:1;background:var(--bg-input);color:var(--text-secondary);border:2px solid var(--border);">
                        ✕ Cancel
                    </button>
                    <button type="button" class="btn btn-success" @click="submitOrder(total)" :disabled="submitting" style="flex:2;font-size:16px;padding:14px;">
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
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
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
                                      x-text="row.type !== 'none' ? (row.type==='senior' ? '−₱'+(pkgPrice(row.pkg)*0.2).toFixed(0) : row.type==='pwd' ? '−₱'+(pkgPrice(row.pkg)*0.2).toFixed(0) : '−₱'+(pkgPrice(row.pkg)*0.1).toFixed(0)) : '₱'+pkgPrice(row.pkg)"></span>
                            </div>
                            
                            <div class="disc-person-btns">
                                <button type="button"
                                        @click="setDiscount(row.pkg, row.paxNum, 'none')"
                                        :class="row.type === 'none' ? 'disc-type-btn disc-type-none active' : 'disc-type-btn disc-type-none'">
                                    —
                                </button>
                                <button type="button"
                                        @click="setDiscount(row.pkg, row.paxNum, 'senior')"
                                        :class="row.type === 'senior' ? 'disc-type-btn disc-type-sc active' : 'disc-type-btn disc-type-sc'">
                                    👴 SC
                                </button>
                                <button type="button"
                                        @click="setDiscount(row.pkg, row.paxNum, 'pwd')"
                                        :class="row.type === 'pwd' ? 'disc-type-btn disc-type-pwd active' : 'disc-type-btn disc-type-pwd'">
                                    ♿ PWD
                                </button>
                                <button type="button"
                                        @click="setDiscount(row.pkg, row.paxNum, 'child')"
                                        :class="row.type === 'child' ? 'disc-type-btn disc-type-kid active' : 'disc-type-btn disc-type-kid'">
                                    🧒 Kid
                                </button>
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
                    <button type="button" class="btn btn-success disc-modal-done"
                            @click="discountPanelOpen = false">
                        ✓ Done
                    </button>
                </div>

            </div>
        </div>
    </template>

    </div>
</div>
<?php /**PATH C:\Users\Mark\samgyeopsal-pos - Copy\resources\views/livewire/new-order.blade.php ENDPATH**/ ?>