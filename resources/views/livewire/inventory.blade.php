<div x-data="{
    productModal: { open: false, saving: false, error: '', product: null, _timer: null },
    stockModal:   { open: false, saving: false, error: '', product: null, _timer: null },
    deleteModal:       { open: false, product: null },
    resetStockModal:   { open: false },
    restoreSampleModal:{ open: false },
    deleteAllModal:    { open: false },
    activeTab: 'products',
    search: '',
    activeCategory: 'All',
    flash: '',
    flashType: 'success',
    flashTimer: null,
    sortCol: 'name',
    sortDir: 'asc',
    movSearch: '',
    movFilter: 'all',
    statusFilter: 'all',

    allProducts: {{ json_encode($products->map(fn($p) => [
        'id'               => $p->id,
        'name'             => $p->name,
        'category'         => $p->category,
        'stock'            => (float) $p->stock,
        'unit'             => $p->unit,
        'cost'             => (float) $p->cost,
        'reorder_level'    => (float) $p->reorder_level,
        'is_extra'         => (bool)  $p->is_extra,
        'selling_price'    => (float) $p->selling_price,
        'is_low_stock'     => (bool)  $p->is_low_stock,
        'is_available_now' => (bool)  $p->is_available_now,
        'available_from'   => $p->available_from  ? $p->available_from->format('Y-m-d')  : '',
        'available_until'  => $p->available_until ? $p->available_until->format('Y-m-d') : '',
    ])->values()) }},

    movements: {{ json_encode($movements->map(fn($m) => [
        'id'             => $m->id,
        'product_name'   => $m->product_name,
        'type'           => $m->type,
        'quantity'       => (float) $m->quantity,
        'unit_cost'      => (float) $m->unit_cost,
        'total_cost'     => round((float) $m->quantity * (float) $m->unit_cost, 2),
        'previous_stock' => (float) $m->previous_stock,
        'new_stock'      => (float) $m->new_stock,
        'notes'          => $m->notes ?? '',
        'created_at'     => $m->created_at->format('M j, Y · g:i A'),
    ])->values()) }},

    get categories() {
        var cats = ['All'];
        this.allProducts.forEach(function(p) {
            if (!cats.includes(p.category)) cats.push(p.category);
        });
        return cats;
    },

    get filtered() {
        var self = this;
        var list = this.allProducts.filter(function(p) {
            var matchCat    = self.activeCategory === 'All' || p.category === self.activeCategory;
            var matchSearch = !self.search || p.name.toLowerCase().includes(self.search.toLowerCase()) || p.category.toLowerCase().includes(self.search.toLowerCase());
            var matchStatus = self.statusFilter === 'all'
                || (self.statusFilter === 'low'   && p.is_low_stock && p.stock > 0)
                || (self.statusFilter === 'ok'    && !p.is_low_stock && p.stock > 0)
                || (self.statusFilter === 'zero'  && p.stock <= 0)
                || (self.statusFilter === 'extra' && p.is_extra);
            return matchCat && matchSearch && matchStatus;
        });
        var col = self.sortCol;
        var dir = self.sortDir === 'asc' ? 1 : -1;
        list.sort(function(a, b) {
            var av = a[col], bv = b[col];
            if (typeof av === 'string') return dir * av.localeCompare(bv);
            return dir * (av - bv);
        });
        return list;
    },

    get lowStockList()   { return this.allProducts.filter(function(p) { return p.is_low_stock; }); },
    get outOfStockList() { return this.allProducts.filter(function(p) { return p.stock <= 0; }); },
    get healthyCount()   { return this.allProducts.filter(function(p) { return !p.is_low_stock && p.stock > 0; }).length; },
    get extrasCount()    { return this.allProducts.filter(function(p) { return p.is_extra; }).length; },
    get totalValue()     { return this.allProducts.reduce(function(s, p) { return s + p.stock * p.cost; }, 0); },

    get filteredMovements() {
        var self = this;
        return this.movements.filter(function(m) {
            var matchSearch = !self.movSearch
                || m.product_name.toLowerCase().includes(self.movSearch.toLowerCase())
                || (m.notes && m.notes.toLowerCase().includes(self.movSearch.toLowerCase()));
            var matchType = self.movFilter === 'all' || m.type === self.movFilter;
            return matchSearch && matchType;
        });
    },

    get categoryBreakdown() {
        var map = {};
        this.allProducts.forEach(function(p) {
            if (!map[p.category]) map[p.category] = { count: 0, value: 0 };
            map[p.category].count++;
            map[p.category].value += p.stock * p.cost;
        });
        var entries = Object.entries(map).sort(function(a,b){ return b[1].count - a[1].count; });
        var max = entries.length > 0 ? entries[0][1].count : 1;
        var colors = ['#FF4D4D','#3B9EDB','#22C98B','#F5A623','#A78BFA','#F472B6','#34D399','#FB923C'];
        return entries.map(function(e, i) {
            return { name: e[0], count: e[1].count, value: e[1].value, pct: Math.round((e[1].count/max)*100), color: colors[i % colors.length] };
        });
    },

    setSort(col) {
        if (this.sortCol === col) {
            this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortCol = col;
            this.sortDir = 'asc';
        }
    },

    sortIcon(col) {
        if (this.sortCol !== col) return '⇅';
        return this.sortDir === 'asc' ? '↑' : '↓';
    },

    formatPHP(n) {
        return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    openProduct(data) {
        clearTimeout(this.productModal._timer);
        this.productModal.error  = '';
        this.productModal.saving = false;
        this.productModal.product = data ? {
            id:             data.id,
            name:           data.name,
            category:       data.category,
            stock:          Number(data.stock),
            unit:           data.unit,
            cost:           Number(data.cost),
            reorder_level:  Number(data.reorder_level),
            is_extra:       Boolean(data.is_extra),
            selling_price:  Number(data.selling_price),
            available_from: data.available_from  || '',
            available_until:data.available_until || '',
        } : {
            id: null, name: '', category: 'Meat', stock: 0,
            unit: 'kg', cost: 0, reorder_level: 10, is_extra: false, selling_price: 0,
            available_from: '', available_until: ''
        };
        this.productModal.open = true;
    },
    closeProduct() {
        this.productModal.open = false;
        this.productModal._timer = setTimeout(() => {
            this.productModal.product = null;
            this.productModal.error   = '';
            this.productModal.saving  = false;
        }, 250);
    },

    openStock(data) {
        clearTimeout(this.stockModal._timer);
        this.stockModal.error   = '';
        this.stockModal.saving  = false;
        this.stockModal.product = {
            id:            data.id,
            name:          data.name,
            stock:         data.stock,
            unit:          data.unit,
            reorder_level: data.reorder_level,
            type:          'in',
            qty:           '',
            notes:         ''
        };
        this.stockModal.open = true;
    },
    closeStock() {
        this.stockModal.open = false;
        this.stockModal._timer = setTimeout(() => {
            this.stockModal.product = null;
            this.stockModal.error   = '';
            this.stockModal.saving  = false;
        }, 250);
    },

    confirmDelete(item) {
        this.deleteModal.product = item;
        this.deleteModal.open    = true;
    },
    cancelDelete() {
        this.deleteModal.open    = false;
        this.deleteModal.product = null;
    },
    doDelete() {
        if (!this.deleteModal.product) return;
        this.$wire.deleteProduct(this.deleteModal.product.id);
        this.deleteModal.open    = false;
        this.deleteModal.product = null;
    },

    showFlash(msg, type = 'success') {
        this.flash = msg;
        this.flashType = type;
        clearTimeout(this.flashTimer);
        this.flashTimer = setTimeout(() => { this.flash = ''; }, 3500);
    },

    saveProduct() {
        if (!this.productModal.product) return;
        this.productModal.saving = true;
        this.productModal.error  = '';
        this.$wire.saveProduct(this.productModal.product)
            .finally(() => { this.productModal.saving = false; });
    },

    saveStock() {
        if (!this.stockModal.product) return;
        this.stockModal.saving = true;
        this.stockModal.error  = '';
        this.$wire.saveStockMovement({
            product_id: this.stockModal.product.id,
            type:       this.stockModal.product.type,
            qty:        this.stockModal.product.qty,
            notes:      this.stockModal.product.notes,
        }).finally(() => { this.stockModal.saving = false; });
    },

    confirmResetAllStock() {
        this.resetStockModal.open = true;
    },
    doResetAllStock() {
        this.resetStockModal.open = false;
        this.$wire.resetAllStock();
    },

    confirmRestoreSample() {
        if (!confirm('Restore sample product data?\n\nThis will DELETE all current products and stock history. Cannot be undone.')) return;
        this.$wire.resetSampleData();
    },

    confirmDeleteAll() {
        if (!confirm('DELETE ALL PRODUCTS?\n\nThis permanently removes every product and all stock history. Cannot be undone.')) return;
        this.$wire.deleteAllProducts();
    }
}"
    @product-save-error.window="productModal.error = $event.detail.message; productModal.saving = false"
    @stock-save-error.window="stockModal.error = $event.detail.message; stockModal.saving = false"
    @inventory-flash.window="showFlash($event.detail.message)"
    @close-product-modal.window="closeProduct()"
    @close-stock-modal.window="closeStock()"
    @inventory-sync.window="
        allProducts = $event.detail.data.products;
        movements   = $event.detail.data.movements;
    "
    @keydown.escape.window="closeProduct(); closeStock(); cancelDelete()">
    <div wire:ignore>

    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="color:var(--primary);margin:0;font-size:24px;font-weight:900;">Inventory</h2>
            <div style="font-size:14px;color:var(--text-3);margin-top:4px;"
                 style="font-size:14px;" x-text="allProducts.length + ' products · Est. value: ' + formatPHP(totalValue)"></div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <div x-data="{ resetMenu: false }" style="position:relative;">
                <button class="btn btn-sm"
                        style="background:#F5F2EF;color:var(--text-secondary);border:1.5px solid var(--border);display:flex;align-items:center;gap:5px;"
                        @click="resetMenu = !resetMenu"
                        @click.outside="resetMenu = false">
                    ↺ Reset Stock
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="resetMenu" x-cloak
                     style="position:absolute;top:calc(100% + 6px);right:0;background:#fff;border:1.5px solid var(--border-strong);border-radius:var(--r-md);box-shadow:0 8px 32px rgba(0,0,0,0.16);min-width:240px;z-index:200;overflow:hidden;">
                    <div style="padding:8px 14px 6px;font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-3);border-bottom:1px solid var(--border);">⚙️ Reset Options</div>
                    <button style="width:100%;text-align:left;padding:11px 14px;background:transparent;border:none;border-bottom:1px solid var(--border);color:var(--text-1);font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:all 0.15s;"
                            @mouseenter="$el.style.background='var(--bg-overlay)'"
                            @mouseleave="$el.style.background='transparent'"
                            @click="resetMenu=false; confirmResetAllStock()">
                        🔴 &nbsp;Zero All Stock
                        <div style="font-size:10.5px;color:var(--text-3);font-weight:400;margin-top:3px;">Set every item to 0 — keeps products &amp; history</div>
                    </button>
                    <button style="width:100%;text-align:left;padding:11px 14px;background:transparent;border:none;border-bottom:1px solid var(--border);color:var(--text-1);font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:all 0.15s;"
                            @mouseenter="$el.style.background='var(--bg-overlay)'"
                            @mouseleave="$el.style.background='transparent'"
                            @click="resetMenu=false; confirmRestoreSample()">
                        🔄 &nbsp;Restore Sample Data
                        <div style="font-size:10.5px;color:var(--text-3);font-weight:400;margin-top:3px;">Wipes everything, loads demo products</div>
                    </button>
                    <button style="width:100%;text-align:left;padding:11px 14px;background:transparent;border:none;color:var(--danger);font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:all 0.15s;"
                            @mouseenter="$el.style.background='rgba(212,64,64,0.08)'"
                            @mouseleave="$el.style.background='transparent'"
                            @click="resetMenu=false; confirmDeleteAll()">
                        🗑️ &nbsp;Delete All Products
                        <div style="font-size:10.5px;color:var(--text-3);font-weight:400;margin-top:3px;">Permanently removes everything — blank slate</div>
                    </button>
                </div>
            </div>
            <button class="btn btn-primary btn-sm" @click="openProduct(null)">＋ Add Product</button>
        </div>
    </div>

    {{-- ── Low stock alert banner ─────────────────────────────── --}}
    <template x-if="lowStockList.length > 0">
        <div style="background:rgba(192,57,43,0.08);border:1.5px solid rgba(192,57,43,0.35);border-radius:var(--radius-sm);padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span style="color:var(--warning);font-weight:700;white-space:nowrap;font-size:15px;">⚠️ Low Stock:</span>
            <template x-for="item in lowStockList" :key="item.id">
                <button @click="openStock(item)"
                        style="background:rgba(192,57,43,0.15);color:var(--primary-light);padding:3px 10px;border-radius:20px;font-size:0.8em;font-weight:600;border:1px solid rgba(192,57,43,0.35);cursor:pointer;transition:all 0.15s;"
                        :title="'Add stock to ' + item.name"
                        x-text="item.name + ' (' + item.stock + ' ' + item.unit + ')'"></button>
            </template>
        </div>
    </template>

    {{-- ── Sub-tabs ──────────────────────────────────────────────── --}}
    <div style="display:flex;gap:4px;margin-bottom:16px;background:#F5F2EF;padding:4px;border-radius:var(--radius-sm);width:fit-content;">
        <button @click="activeTab='products'"
                :class="activeTab==='products' ? 'inv-subtab active' : 'inv-subtab'">
            📦 Products <span x-text="'(' + allProducts.length + ')'"></span>
        </button>
        <button @click="activeTab='overview'"
                :class="activeTab==='overview' ? 'inv-subtab active' : 'inv-subtab'">
            📊 Overview
        </button>
        <button @click="activeTab='movements'"
                :class="activeTab==='movements' ? 'inv-subtab active' : 'inv-subtab'">
            📋 History <span x-text="'(' + movements.length + ')'"></span>
        </button>
    </div>

    {{-- ══ PRODUCTS TAB ═══════════════════════════════════════════ --}}
    <div x-show="activeTab==='products'" x-cloak>

        {{-- Search + status filter --}}
        <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap;align-items:center;">
            <input type="text" x-model="search"
                   placeholder="🔍 Search by name or category…"
                   style="flex:1;min-width:180px;padding:8px 12px;font-size:0.88em;">
            <select x-model="statusFilter"
                    style="padding:8px 10px;font-size:0.85em;min-width:140px;background:#F5F2EF;color:var(--text-1);">
                <option value="all">All Status</option>
                <option value="ok">✅ OK</option>
                <option value="low">⚠️ Low Stock</option>
                <option value="zero">🔴 Out of Stock</option>
                <option value="extra">⭐ Extras Only</option>
            </select>
        </div>

        {{-- Category pills --}}
        <div style="display:flex;gap:5px;margin-bottom:10px;flex-wrap:wrap;">
            <template x-for="cat in categories" :key="cat">
                <button @click="activeCategory = cat"
                        :class="activeCategory === cat ? 'cat-pill active' : 'cat-pill'"
                        x-text="cat"></button>
            </template>
        </div>

        {{-- Result count --}}
        <div style="font-size:11px;color:var(--text-3);margin-bottom:8px;"
             x-text="filtered.length + ' of ' + allProducts.length + ' products shown'"></div>

        <div style="border:1.5px solid var(--border);border-radius:var(--radius-md);overflow:hidden;">
            <table style="margin:0;background:transparent;box-shadow:none;">
                <thead>
                    <tr style="background:var(--bg-overlay);">
                        <th style="width:28%;cursor:pointer;user-select:none;" @click="setSort('name')">
                            Product <span style="font-family:monospace;font-size:11px;opacity:0.55;" x-text="sortIcon('name')"></span>
                        </th>
                        <th style="width:12%;cursor:pointer;user-select:none;" @click="setSort('category')">
                            Category <span style="font-family:monospace;font-size:11px;opacity:0.55;" x-text="sortIcon('category')"></span>
                        </th>
                        <th style="width:14%;text-align:center;cursor:pointer;user-select:none;" @click="setSort('stock')">
                            Stock <span style="font-family:monospace;font-size:11px;opacity:0.55;" x-text="sortIcon('stock')"></span>
                        </th>
                        <th style="width:10%;text-align:center;cursor:pointer;user-select:none;" @click="setSort('cost')">
                            Cost Price <span style="font-family:monospace;font-size:11px;opacity:0.55;" x-text="sortIcon('cost')"></span>
                        </th>
                        <th style="width:10%;text-align:center;">Sell Price</th>
                        <th style="width:8%;text-align:center;">Status</th>
                        <th style="width:8%;text-align:center;">Menu</th>
                        <th style="width:20%;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">
                                <div style="font-size:2em;margin-bottom:8px;">🔍</div>
                                No products match your filters
                            </td>
                        </tr>
                    </template>
                    <template x-for="item in filtered" :key="item.id">
                        <tr :style="item.is_extra && !item.is_available_now
                                ? 'background:rgba(0,0,0,0.03);opacity:0.65;'
                                : (item.stock <= 0
                                    ? 'background:rgba(192,57,43,0.04);'
                                    : (item.is_low_stock ? 'background:rgba(184,119,13,0.04);' : ''))">

                            {{-- Product name + reorder hint --}}
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-weight:700;color:var(--text-primary);font-size:15px;" x-text="item.name"></span>
                                    {{-- Availability badge: only show on extras --}}
                                    <template x-if="item.is_extra">
                                        <span x-show="!item.is_available_now"
                                              style="background:#FEE2E2;color:#991B1B;font-size:10px;font-weight:800;padding:2px 7px;border-radius:10px;white-space:nowrap;">
                                            UNAVAILABLE
                                        </span>
                                    </template>
                                </div>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;font-family:'DM Mono',monospace;" x-text="'Reorder @ ' + item.reorder_level + ' ' + item.unit"></div>
                                {{-- Availability dates (only for extras that have them) --}}
                                <template x-if="item.is_extra && (item.available_from || item.available_until)">
                                    <div style="font-size:10px;margin-top:3px;color:#6B7280;font-family:'DM Mono',monospace;">
                                        <span x-show="item.available_from"  x-text="'From: ' + item.available_from"></span>
                                        <span x-show="item.available_from && item.available_until"> · </span>
                                        <span x-show="item.available_until" x-text="'Until: ' + item.available_until"></span>
                                    </div>
                                </template>
                            </td>

                            {{-- Category --}}
                            <td>
                                <span style="background:#F5F2EF;color:var(--text-secondary);padding:3px 10px;border-radius:6px;font-size:13px;font-weight:600;"
                                      x-text="item.category"></span>
                            </td>

                            {{-- Stock + mini bar --}}
                            <td style="text-align:center;min-width:110px;">
                                {{-- Stock number --}}
                                <div :style="item.stock <= 0
                                        ? 'color:#B91C1C;font-weight:800;font-family:DM Mono,monospace;font-size:16px;'
                                        : (item.is_low_stock
                                            ? 'color:#B45309;font-weight:700;font-family:DM Mono,monospace;font-size:16px;'
                                            : 'color:#166534;font-weight:700;font-family:DM Mono,monospace;font-size:16px;')"
                                     x-text="item.stock + ' ' + item.unit"></div>

                                {{-- 5-zone colour bar --}}
                                {{-- Zones (% of reorder_level * 4 as "full"):
                                     0%        → out of stock  → red         #DC2626
                                     1–33%     → critical low  → orange-red  #EA580C
                                     34–66%    → low / warning → amber       #D97706
                                     67–99%    → near reorder  → yellow-grn  #65A30D
                                     100%+     → healthy stock → green       #16A34A
                                --}}
                                <div style="height:8px;background:#E5E7EB;border-radius:6px;margin-top:7px;overflow:hidden;box-shadow:inset 0 1px 2px rgba(0,0,0,0.08);">
                                    <div :style="(() => {
                                        if (item.stock <= 0) {
                                            return 'height:100%;border-radius:6px;width:100%;background:#DC2626;transition:width 0.5s ease;';
                                        }
                                        const full = item.reorder_level > 0 ? item.reorder_level * 4 : (item.stock > 0 ? item.stock * 2 : 1);
                                        const pct  = Math.min(100, Math.round((item.stock / full) * 100));
                                        let color;
                                        if (pct <= 33) {
                                            color = '#EA580C';   /* critical — orange-red */
                                        } else if (pct <= 50) {
                                            color = '#D97706';   /* low — amber */
                                        } else if (pct <= 74) {
                                            color = '#65A30D';   /* getting low — lime green */
                                        } else {
                                            color = '#16A34A';   /* healthy — green */
                                        }
                                        return 'height:100%;border-radius:6px;width:' + pct + '%;background:' + color + ';transition:width 0.5s ease;';
                                    })()">
                                    </div>
                                </div>

                                {{-- Zone label below bar --}}
                                <div style="font-size:11px;margin-top:4px;font-weight:700;letter-spacing:0.3px;"
                                     :style="item.stock <= 0
                                        ? 'color:#B91C1C;'
                                        : (item.is_low_stock ? 'color:#B45309;' : 'color:#166534;')"
                                     x-text="item.stock <= 0 ? 'Out of stock' : (item.is_low_stock ? 'Low stock' : 'In stock')">
                                </div>
                            </td>

                            {{-- Cost Price --}}
                            <td style="text-align:center;color:var(--text-secondary);font-size:15px;font-family:'DM Mono',monospace;"
                                x-text="'₱' + item.cost.toFixed(2)"></td>

                            {{-- Sell Price + margin badge --}}
                            <td style="text-align:center;">
                                <template x-if="item.is_extra && item.selling_price > 0">
                                    <div>
                                        <div style="font-family:'DM Mono',monospace;font-size:14px;font-weight:700;color:var(--text-primary);"
                                             x-text="'₱' + item.selling_price.toFixed(2)"></div>
                                        <div x-data="{ get margin() { return item.selling_price > 0 ? (((item.selling_price - item.cost) / item.selling_price) * 100).toFixed(0) : 0; } }">
                                            <span style="font-size:11px;font-weight:700;padding:1px 6px;border-radius:8px;"
                                                  :style="(item.selling_price - item.cost) >= 0
                                                    ? 'background:rgba(34,201,139,0.15);color:var(--success);'
                                                    : 'background:rgba(217,85,85,0.15);color:var(--danger);'"
                                                  x-text="margin + '% margin'"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="item.is_extra && item.selling_price <= 0">
                                    <span style="font-size:12px;color:var(--danger);font-weight:600;">⚠️ Not set</span>
                                </template>
                                <template x-if="!item.is_extra">
                                    <span style="font-size:12px;color:var(--text-muted);">—</span>
                                </template>
                            </td>

                            {{-- Status badge --}}
                            <td style="text-align:center;">
                                <span x-show="item.stock <= 0"
                                      style="background:rgba(217,85,85,0.18);color:var(--danger);padding:3px 12px;border-radius:10px;font-size:13px;font-weight:700;white-space:nowrap;">Out</span>
                                <span x-show="item.stock > 0 && item.is_low_stock"
                                      style="background:rgba(245,166,35,0.18);color:var(--warning);padding:3px 12px;border-radius:10px;font-size:13px;font-weight:700;white-space:nowrap;">Low</span>
                                <span x-show="item.stock > 0 && !item.is_low_stock"
                                      style="background:rgba(34,201,139,0.15);color:var(--success);padding:3px 12px;border-radius:10px;font-size:13px;font-weight:700;white-space:nowrap;">OK</span>
                            </td>

                            {{-- Menu/Extra toggle --}}
                            <td style="text-align:center;">
                                <button @click="$wire.toggleExtraFromCard(item.id)"
                                        :title="item.is_extra ? 'Remove from extras menu' : 'Add to extras menu'"
                                        :style="item.is_extra
                                            ? 'background:rgba(46,184,122,0.15);color:var(--success);border:1px solid var(--success);'
                                            : 'background:#F5F2EF;color:var(--text-muted);border:1px solid var(--border);'"
                                        style="border-radius:6px;padding:4px 8px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;transition:all 0.18s;display:inline-flex;align-items:center;gap:3px;">
                                    <span x-text="item.is_extra ? '✓ On' : '+ Add'"></span>
                                </button>
                            </td>

                            {{-- Actions --}}
                            <td style="text-align:center;">
                                <div style="display:inline-flex;gap:5px;align-items:center;justify-content:center;">
                                    <button style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:var(--info);color:#fff;border:none;border-radius:var(--r-sm);font-size:11.5px;font-weight:700;cursor:pointer;white-space:nowrap;font-family:inherit;transition:all 0.15s;"
                                            @click="openStock(item)" title="Add or remove stock">
                                        ⚖️ Stock
                                    </button>
                                    <button style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:var(--bg-overlay);color:var(--text-2);border:1px solid var(--border-strong);border-radius:var(--r-sm);font-size:11.5px;font-weight:700;cursor:pointer;white-space:nowrap;font-family:inherit;transition:all 0.15s;"
                                            @click="openProduct(item)" title="Edit product details">
                                        ✏️ Edit
                                    </button>
                                    <button style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;background:transparent;color:var(--danger);border:1px solid rgba(217,85,85,0.35);border-radius:var(--r-sm);font-size:14px;cursor:pointer;font-family:inherit;transition:all 0.15s;flex-shrink:0;"
                                            @click="confirmDelete(item)" title="Delete this product">
                                        🗑
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══ OVERVIEW TAB ════════════════════════════════════════════ --}}
    <div x-show="activeTab==='overview'" x-cloak>

        {{-- Stat cards --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:4px;">
            <div class="ov-card ov-card-info">
                <div class="ov-card-label">Total Products</div>
                <div class="ov-card-num" style="color:var(--info);font-size:40px;" x-text="allProducts.length"></div>
                <div class="ov-card-sub" x-text="(categories.length - 1) + ' categories'"></div>
            </div>
            <div class="ov-card ov-card-success">
                <div class="ov-card-label">Above Reorder</div>
                <div class="ov-card-num" style="color:var(--success);" x-text="healthyCount"></div>
                <div class="ov-card-sub" x-text="allProducts.length > 0 ? Math.round(healthyCount/allProducts.length*100) + '% of total' : '—'"></div>
            </div>
            <div class="ov-card" :class="lowStockList.length > 0 ? 'ov-card-danger' : 'ov-card-success'">
                <div class="ov-card-label">Low / Out of Stock</div>
                <div class="ov-card-num"
                     :style="lowStockList.length > 0 ? 'color:var(--danger)' : 'color:var(--success)'"
                     x-text="lowStockList.length + ' / ' + outOfStockList.length"></div>
                <div class="ov-card-sub" x-text="lowStockList.length > 0 ? 'need attention' : 'all stocked'"></div>
            </div>
            <div class="ov-card" style="border-color:rgba(34,201,139,0.3);">
                <div class="ov-card-label">Est. Total Value</div>
                <div class="ov-card-num" style="color:var(--success);font-size:20px;" x-text="formatPHP(totalValue)"></div>
                <div class="ov-card-sub" x-text="extrasCount + ' on extras menu'"></div>
            </div>
        </div>

        {{-- Category breakdown + Low stock --}}
        <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:10px;margin-top:10px;">

            <div style="background:#fff;border:1.5px solid var(--border-strong);border-radius:var(--r-md);padding:20px;">
                <div class="ov-section-label">📊 Products by Category</div>
                <div x-show="categoryBreakdown.length === 0" style="color:var(--text-3);font-size:0.85em;text-align:center;padding:30px 0;">No products yet</div>
                <template x-for="row in categoryBreakdown" :key="row.name">
                    <div style="display:grid;grid-template-columns:110px 1fr 80px 52px;align-items:center;gap:10px;margin-bottom:10px;">
                        <div style="display:flex;align-items:center;gap:7px;">
                            <div style="width:8px;height:8px;border-radius:2px;flex-shrink:0;" :style="'background:' + row.color"></div>
                            <span style="font-size:0.82em;font-weight:600;color:var(--text-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="row.name"></span>
                        </div>
                        <div style="height:7px;background:rgba(255,255,255,0.08);border-radius:4px;overflow:hidden;">
                            <div style="height:100%;border-radius:4px;min-width:4px;" :style="'width:' + row.pct + '%;background:' + row.color + ';opacity:0.85'"></div>
                        </div>
                        <span style="font-size:0.76em;color:var(--text-3);text-align:right;font-family:'DM Mono',monospace;" x-text="formatPHP(row.value)"></span>
                        <span style="font-size:0.78em;font-weight:700;color:var(--text-1);text-align:right;" x-text="row.count"></span>
                    </div>
                </template>
            </div>

            {{-- Low stock list with Restock buttons --}}
            <div style="background:#fff;border:1.5px solid var(--border-strong);border-radius:var(--r-md);padding:20px;">
                <div class="ov-section-label">🔴 Needs Restocking</div>
                <div x-show="lowStockList.length === 0"
                     style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;text-align:center;padding:20px 0;">
                    <div style="font-size:2em;">🎉</div>
                    <div style="color:var(--success);font-weight:700;font-size:0.88em;">All stocked up!</div>
                    <div style="color:var(--text-3);font-size:0.75em;">Every item is above its reorder level</div>
                </div>
                <div x-show="lowStockList.length > 0"
                     style="display:flex;flex-direction:column;gap:6px;max-height:280px;overflow-y:auto;scrollbar-width:thin;">
                    <template x-for="item in lowStockList" :key="item.id">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:rgba(192,57,43,0.06);border:1px solid rgba(192,57,43,0.18);border-radius:var(--r-sm);gap:8px;">
                            <div style="min-width:0;">
                                <div style="font-size:0.82em;color:var(--text-1);font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.name"></div>
                                <div style="font-size:0.75em;font-family:'DM Mono',monospace;color:var(--warning);" x-text="item.stock + ' ' + item.unit + ' left'"></div>
                            </div>
                            <button @click="openStock(item)"
                                    style="flex-shrink:0;padding:4px 10px;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:0.75em;font-weight:700;cursor:pointer;white-space:nowrap;transition:all 0.15s;">
                                📥 Restock
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MOVEMENTS TAB ════════════════════════════════════════════ --}}
    <div x-show="activeTab==='movements'" x-cloak>

        {{-- Search + type filter --}}
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;align-items:center;">
            <input type="text" x-model="movSearch"
                   placeholder="🔍 Search product name or notes…"
                   style="flex:1;min-width:180px;padding:8px 12px;font-size:0.88em;">
            <div style="display:flex;gap:3px;background:#F5F2EF;padding:3px;border-radius:var(--r-sm);">
                <button @click="movFilter='all'"
                        :style="movFilter==='all'
                            ? 'background:var(--bg-elevated);color:var(--text-1);'
                            : 'background:transparent;color:var(--text-3);'"
                        style="padding:5px 12px;border:none;border-radius:6px;font-size:0.8em;font-weight:600;cursor:pointer;font-family:inherit;">
                    All
                </button>
                <button @click="movFilter='in'"
                        :style="movFilter==='in'
                            ? 'background:rgba(34,201,139,0.2);color:var(--success);'
                            : 'background:transparent;color:var(--text-3);'"
                        style="padding:5px 12px;border:none;border-radius:6px;font-size:0.8em;font-weight:600;cursor:pointer;font-family:inherit;">
                    ↑ IN
                </button>
                <button @click="movFilter='out'"
                        :style="movFilter==='out'
                            ? 'background:rgba(230,57,70,0.2);color:var(--danger);'
                            : 'background:transparent;color:var(--text-3);'"
                        style="padding:5px 12px;border:none;border-radius:6px;font-size:0.8em;font-weight:600;cursor:pointer;font-family:inherit;">
                    ↓ OUT
                </button>
            </div>
            <div style="font-size:11px;color:var(--text-3);white-space:nowrap;"
                 x-text="filteredMovements.length + ' of ' + movements.length + ' records'"></div>
        </div>

        <template x-if="filteredMovements.length === 0">
            <div style="text-align:center;padding:48px;color:var(--text-muted);">
                <div style="font-size:2em;margin-bottom:8px;">📋</div>
                <p>No stock movements match your search</p>
            </div>
        </template>
        <template x-if="filteredMovements.length > 0">
            <div style="border:1.5px solid var(--border);border-radius:var(--radius-md);overflow:hidden;">
                <table style="margin:0;background:transparent;box-shadow:none;">
                    <thead>
                        <tr style="background:var(--bg-overlay);">
                            <th style="width:20%;">Product</th>
                            <th style="width:8%;text-align:center;">Type</th>
                            <th style="width:8%;text-align:center;">Qty</th>
                            <th style="width:9%;text-align:center;">Cost/Unit</th>
                            <th style="width:10%;text-align:center;">Total Value</th>
                            <th style="width:9%;text-align:center;">Before</th>
                            <th style="width:9%;text-align:center;">After</th>
                            <th style="width:14%;">Notes</th>
                            <th style="width:13%;">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="m in filteredMovements" :key="m.id">
                            <tr>
                                <td style="font-weight:600;color:var(--text-primary);font-size:13px;" x-text="m.product_name"></td>
                                <td style="text-align:center;">
                                    <span :style="m.type === 'in'
                                        ? 'background:rgba(6,214,160,0.15);color:var(--success);padding:3px 10px;border-radius:20px;font-size:0.8em;font-weight:700;'
                                        : 'background:rgba(230,57,70,0.15);color:var(--danger);padding:3px 10px;border-radius:20px;font-size:0.8em;font-weight:700;'"
                                        x-text="m.type === 'in' ? '↑ IN' : '↓ OUT'"></span>
                                </td>
                                <td style="text-align:center;font-weight:700;font-family:'DM Mono',monospace;" x-text="m.quantity"></td>
                                <td style="text-align:center;font-family:'DM Mono',monospace;font-size:0.88em;color:var(--text-muted);"
                                    x-text="m.unit_cost > 0 ? '₱' + m.unit_cost.toFixed(2) : '—'"></td>
                                <td style="text-align:center;font-family:'DM Mono',monospace;font-size:0.88em;font-weight:700;"
                                    :style="m.type === 'out' ? 'color:var(--danger);' : 'color:var(--success);'"
                                    x-text="m.unit_cost > 0 ? (m.type === 'out' ? '−' : '+') + '₱' + m.total_cost.toFixed(2) : '—'"></td>
                                <td style="text-align:center;color:var(--text-muted);font-family:'DM Mono',monospace;font-size:0.88em;" x-text="m.previous_stock"></td>
                                <td style="text-align:center;color:var(--text-primary);font-family:'DM Mono',monospace;font-weight:600;" x-text="m.new_stock"></td>
                                <td style="color:var(--text-secondary);font-size:0.85em;" x-text="m.notes || '—'"></td>
                                <td style="color:var(--text-muted);font-size:0.78em;white-space:nowrap;" x-text="m.created_at"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    {{-- ══ ADD / EDIT PRODUCT MODAL ══════════════════════════════ --}}
    <div x-show="productModal.open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-backdrop"
         @click.self="closeProduct()">
        <div class="modal-box" @click.stop>
            <div class="modal-header">
                <h2 style="color:var(--primary);"
                    style="font-size:20px;" x-text="productModal.product && productModal.product.id ? '✏️ Edit Product' : '＋ Add New Product'"></h2>
                <button class="close-modal" @click="closeProduct()">×</button>
            </div>
            <div x-show="productModal.product" style="display:none;">
                <div x-show="productModal.error"
                     style="color:var(--danger);margin-bottom:12px;background:rgba(230,57,70,0.1);padding:10px;border-radius:var(--radius-sm);"
                     x-text="productModal.error"></div>
                <template x-if="productModal.product">
                <div>
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" x-model="productModal.product.name"
                               placeholder="e.g. Pork Belly, Kimchi…"
                               @keydown.enter="saveProduct()">
                    </div>
                    <div class="two-col">
                        <div class="form-group">
                            <label>Category</label>
                            <select x-model="productModal.product.category">
                                <option value="Meat">🥩 Meat</option>
                                <option value="Vegetables">🥦 Vegetables</option>
                                <option value="Sauce">🫙 Sauce</option>
                                <option value="Drinks">🥤 Drinks</option>
                                <option value="Alcohol">🍶 Alcohol</option>
                                <option value="Ice Cream">🍦 Ice Cream</option>
                                <option value="Processed">🍱 Processed</option>
                                <option value="Grains">🌾 Grains</option>
                                <option value="Other">🛒 Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Unit *</label>
                            <select x-model="productModal.product.unit">
                                <option value="kg">kg</option>
                                <option value="g">g</option>
                                <option value="pcs">pcs</option>
                                <option value="servings">servings</option>
                                <option value="L">L</option>
                                <option value="mL">mL</option>
                                <option value="cans">cans</option>
                                <option value="bottles">bottles</option>
                                <option value="packs">packs</option>
                                <option value="boxes">boxes</option>
                                <option value="trays">trays</option>
                                <option value="bags">bags</option>
                                <option value="cups">cups</option>
                                <option value="tbsp">tbsp</option>
                            </select>
                        </div>
                    </div>
                    {{-- Edit mode: show current stock (read-only) --}}
                    <template x-if="productModal.product.id">
                        <div style="background:rgba(36,113,163,0.10);border:1.5px solid rgba(36,113,163,0.28);border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:14px;">
                            <div>
                                <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;color:var(--info);margin-bottom:4px;">Current Stock</div>
                                <div style="font-size:30px;font-weight:900;color:var(--text-1);font-family:'DM Mono',monospace;line-height:1;" x-text="productModal.product.stock + ' ' + productModal.product.unit"></div>
                            </div>
                            <div style="margin-left:auto;text-align:right;">
                                <div style="font-size:13px;color:var(--text-2);line-height:1.6;">Use <strong style="color:var(--primary);">⚖️ Add/Remove Stock</strong><br>to change quantity.</div>
                            </div>
                        </div>
                    </template>
                    {{-- Reorder level — shown for both new and edit --}}
                    <div class="form-group">
                        <label>Reorder Alert Level</label>
                        <input type="number" x-model.number="productModal.product.reorder_level" min="0" step="0.01" placeholder="e.g. 10">
                        <div style="font-size:13px;color:var(--text-muted);margin-top:5px;">A warning will appear when stock drops to or below this number</div>
                    </div>
                    <div class="form-group">
                        <label>Cost Price (₱) <span style="color:var(--text-muted);font-size:0.82em;">— what the store pays to buy/make this item</span></label>
                        <input type="number" x-model.number="productModal.product.cost" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <template x-if="productModal.product.is_extra">
                        <div>
                            <div class="form-group">
                                <label>Selling Price (₱) <span style="color:var(--success);font-size:0.82em;">— what the customer pays (shown on Extras menu)</span></label>
                                <input type="number" x-model.number="productModal.product.selling_price" min="0" step="0.01" placeholder="0.00">
                            </div>
                            {{-- Live margin indicator --}}
                            <template x-if="productModal.product.selling_price > 0 && productModal.product.cost > 0">
                                <div x-data="{
                                    get profit() { return productModal.product.selling_price - productModal.product.cost; },
                                    get margin() { return productModal.product.selling_price > 0 ? ((this.profit / productModal.product.selling_price) * 100).toFixed(1) : 0; }
                                }" style="margin-bottom:14px;padding:10px 14px;border-radius:var(--radius-sm);border:1.5px solid;"
                                :style="profit >= 0 ? 'background:#F0FDF4;border-color:#86EFAC;' : 'background:#FEF2F2;border-color:#FECACA;'">
                                    <div style="font-size:12px;font-weight:700;" :style="profit >= 0 ? 'color:#166534;' : 'color:#991B1B;'">
                                        <span x-text="profit >= 0 ? '📈 Profit per unit: ₱' + profit.toFixed(2) + ' (' + margin + '% margin)' : '⚠️ Selling below cost! Loss per unit: ₱' + Math.abs(profit).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="productModal.product.selling_price > 0 && productModal.product.cost <= 0">
                                <div style="margin-bottom:14px;padding:10px 14px;border-radius:var(--radius-sm);background:#FFFBEB;border:1.5px solid #FCD34D;font-size:12px;color:#78350F;font-weight:600;">
                                    ⚠️ Set a <strong>Cost Price</strong> above to calculate profit margin.
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!productModal.product.is_extra">
                        <div class="form-group" style="background:rgba(6,214,160,0.08);border:1.5px solid rgba(6,214,160,0.25);border-radius:var(--radius-sm);padding:10px 14px;">
                            <p style="color:var(--text-muted);font-size:0.82em;margin:0;">
                                🛒 Toggle <strong style="color:var(--success);">+ Extra</strong> on this product's row to add it to the Extras menu and set a selling price.
                            </p>
                        </div>
                    </template>
                    {{-- Availability window (only shown when is_extra = true) --}}
                    <template x-if="productModal.product.is_extra">
                        <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:14px;">
                            <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;color:#166534;margin-bottom:10px;">
                                📅 Availability Window <span style="font-weight:500;color:#6B7280;font-size:11px;text-transform:none;">(optional — leave blank for always available)</span>
                            </div>
                            <div class="two-col">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="color:#166534;">Available As Of</label>
                                    <input type="date" x-model="productModal.product.available_from"
                                           style="font-family:'DM Mono',monospace;">
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">First day item appears on menu</div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="color:#991B1B;">Expires As Of</label>
                                    <input type="date" x-model="productModal.product.available_until"
                                           style="font-family:'DM Mono',monospace;">
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Last day item appears on menu</div>
                                </div>
                            </div>
                            <div style="margin-top:10px;font-size:12px;color:#6B7280;"
                                 x-show="productModal.product.available_from || productModal.product.available_until">
                                <span x-show="productModal.product.available_from && !productModal.product.available_until"
                                      x-text="'✅ Available from ' + productModal.product.available_from + ' onwards'"></span>
                                <span x-show="!productModal.product.available_from && productModal.product.available_until"
                                      x-text="'⏳ Available until ' + productModal.product.available_until + ' only'"></span>
                                <span x-show="productModal.product.available_from && productModal.product.available_until"
                                      x-text="'📅 ' + productModal.product.available_from + ' → ' + productModal.product.available_until"></span>
                            </div>
                        </div>
                    </template>

                    <button class="btn btn-primary" style="width:100%;margin-top:4px;"
                            @click="saveProduct()"
                            :disabled="productModal.saving">
                        <span x-text="productModal.saving ? 'Saving…' : (productModal.product.id ? '✅ Save Changes' : '✅ Add Product')"></span>
                    </button>
                </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══ STOCK ADJUSTMENT MODAL ════════════════════════════════ --}}
    <div x-show="stockModal.open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-backdrop"
         @click.self="closeStock()">
        <div class="modal-box" @click.stop style="max-width:440px;">
            <div class="modal-header">
                <h2 style="color:var(--primary);font-size:20px;">⚖️ Add / Remove Stock</h2>
                <button class="close-modal" @click="closeStock()">×</button>
            </div>
            <template x-if="stockModal.product">
                <div>
                    {{-- Product info --}}
                    <div style="background:#F5F2EF;border-radius:var(--radius-sm);padding:14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:700;color:var(--text-primary);font-size:14px;" x-text="stockModal.product.name"></div>
                            <div style="font-size:0.78em;color:var(--text-muted);margin-top:3px;">
                                Reorder @ <span style="font-family:'DM Mono',monospace;color:var(--warning);"
                                    x-text="stockModal.product.reorder_level + ' ' + stockModal.product.unit"></span>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.7em;color:var(--text-muted);margin-bottom:2px;">Current stock</div>
                            <div style="font-size:1.7em;font-weight:800;color:var(--primary);font-family:'DM Mono',monospace;"
                                 x-text="stockModal.product.stock + ' ' + stockModal.product.unit"></div>
                        </div>
                    </div>

                    <template x-if="stockModal.error">
                        <p style="color:var(--danger);margin-bottom:12px;background:rgba(230,57,70,0.1);padding:10px;border-radius:var(--radius-sm);"
                           x-text="stockModal.error"></p>
                    </template>

                    {{-- IN / OUT toggle --}}
                    <div style="margin-bottom:6px;">
                        <div style="font-size:13px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:9px;">What do you want to do?</div>
                        <div style="display:flex;gap:6px;">
                            <button @click="stockModal.product.type = 'in'"
                                    :style="stockModal.product.type === 'in'
                                        ? 'flex:1;background:rgba(46,184,122,0.18);color:var(--success);border:2px solid var(--success);border-radius:var(--radius-sm);padding:10px 8px;font-weight:700;cursor:pointer;font-family:inherit;text-align:left;'
                                        : 'flex:1;background:#F5F2EF;color:var(--text-muted);border:2px solid var(--border);border-radius:var(--radius-sm);padding:10px 8px;cursor:pointer;font-family:inherit;text-align:left;'">
                                <div style="font-size:16px;font-weight:800;">↑ Received Stock</div>
                                <div style="font-size:12px;opacity:0.75;margin-top:3px;">Delivery arrived / restocked</div>
                            </button>
                            <button @click="stockModal.product.type = 'out'"
                                    :style="stockModal.product.type === 'out'
                                        ? 'flex:1;background:rgba(217,85,85,0.18);color:var(--danger);border:2px solid var(--danger);border-radius:var(--radius-sm);padding:10px 8px;font-weight:700;cursor:pointer;font-family:inherit;text-align:left;'
                                        : 'flex:1;background:#F5F2EF;color:var(--text-muted);border:2px solid var(--border);border-radius:var(--radius-sm);padding:10px 8px;cursor:pointer;font-family:inherit;text-align:left;'">
                                <div style="font-size:16px;font-weight:800;">↓ Removed Stock</div>
                                <div style="font-size:12px;opacity:0.75;margin-top:3px;">Used / damaged / wasted</div>
                            </button>
                        </div>
                    </div>
                    <div style="background:rgba(192,57,43,0.07);border:1px solid rgba(192,57,43,0.2);border-radius:var(--radius-sm);padding:8px 12px;margin-bottom:14px;font-size:0.8em;color:var(--text-2);line-height:1.5;">
                        💡 <strong style="color:var(--warning);">Note:</strong> Sales automatically deduct stock. Use this only for manual changes like deliveries, waste, or corrections.
                    </div>

                    {{-- Quick preset buttons --}}
                    <div style="margin-bottom:14px;">
                        <div style="font-size:13px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:9px;">Quick Amount</div>
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <template x-for="preset in [1, 5, 10, 20, 50, 100]" :key="preset">
                                <button @click="stockModal.product.qty = preset"
                                        :style="Number(stockModal.product.qty) === preset
                                            ? 'padding:5px 12px;border-radius:6px;font-size:0.82em;font-weight:700;cursor:pointer;background:var(--primary);color:#fff;border:1.5px solid var(--primary);font-family:inherit;'
                                            : 'padding:5px 12px;border-radius:6px;font-size:0.82em;font-weight:600;cursor:pointer;background:#F5F2EF;color:var(--text-2);border:1.5px solid var(--border);font-family:inherit;'"
                                        x-text="'+' + preset"></button>
                            </template>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Quantity <span style="color:var(--text-muted);font-weight:400;">(or enter custom amount)</span></label>
                        <input type="number" x-model.number="stockModal.product.qty"
                               min="0.01" step="0.01" placeholder="0.00"
                               @keydown.enter="saveStock()">
                    </div>

                    {{-- Live result preview --}}
                    <template x-if="stockModal.product.qty > 0">
                        <div style="background:#F5F2EF;border-radius:var(--r-sm);padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;font-size:0.85em;">
                            <span style="color:var(--text-3);">Stock after adjustment:</span>
                            <span style="font-weight:800;font-family:'DM Mono',monospace;"
                                  :style="stockModal.product.type === 'in' ? 'color:var(--success)' : 'color:var(--warning)'"
                                  x-text="(stockModal.product.type === 'in'
                                    ? (stockModal.product.stock + Number(stockModal.product.qty))
                                    : Math.max(0, stockModal.product.stock - Number(stockModal.product.qty))
                                  ).toFixed(2) + ' ' + stockModal.product.unit"></span>
                        </div>
                    </template>

                    <div class="form-group">
                        <label>Notes <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                        <input type="text" x-model="stockModal.product.notes"
                               placeholder="e.g. Delivery from supplier, Used for prep…"
                               @keydown.enter="saveStock()">
                    </div>

                    <button class="btn btn-primary" style="width:100%;"
                            @click="saveStock()"
                            :disabled="stockModal.saving || !(stockModal.product.qty > 0)">
                        <span x-text="stockModal.saving ? 'Saving…' : 'Confirm Adjustment'"></span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- ══ DELETE CONFIRM MODAL ════════════════════════════════════ --}}
    <div x-show="deleteModal.open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-backdrop"
         @click.self="cancelDelete()">
        <div class="confirm-dialog" @click.stop>
            <div class="confirm-dialog-icon confirm-dialog-icon-danger">🗑️</div>
            <h3 class="confirm-dialog-title">Delete Product?</h3>
            <p class="confirm-dialog-body">
                You're about to delete <strong x-text="deleteModal.product ? deleteModal.product.name : ''"></strong>.
                All stock movement history for this product will also be removed.
            </p>
            <p class="confirm-dialog-warning">This cannot be undone.</p>
            <div class="confirm-dialog-actions">
                <button class="confirm-btn confirm-btn-cancel" @click="cancelDelete()">Keep Product</button>
                <button class="confirm-btn confirm-btn-danger" @click="doDelete()">Yes, Delete</button>
            </div>
        </div>
    </div>


    {{-- ══ RESET ALL STOCK MODAL ══ --}}
    <div x-show="resetStockModal.open" x-cloak class="modal-backdrop" @click.self="resetStockModal.open=false"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="confirm-dialog" @click.stop>
            <div class="confirm-dialog-icon confirm-dialog-icon-warning">⚠️</div>
            <h3 class="confirm-dialog-title">Zero Out All Stock?</h3>
            <p class="confirm-dialog-body">Every item will be set to <strong>0</strong>. A stock-out movement will be logged for each one.</p>
            <p class="confirm-dialog-warning">This cannot be undone.</p>
            <div class="confirm-dialog-actions">
                <button class="confirm-btn confirm-btn-cancel" @click="resetStockModal.open=false">Keep Stock</button>
                <button class="confirm-btn confirm-btn-danger" @click="doResetAllStock()">Yes, Zero Out</button>
            </div>
        </div>
    </div>

    {{-- ══ RESTORE SAMPLE DATA MODAL ══ --}}
    <div x-show="restoreSampleModal.open" x-cloak class="modal-backdrop" @click.self="restoreSampleModal.open=false"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="confirm-dialog" @click.stop>
            <div class="confirm-dialog-icon confirm-dialog-icon-warning">🔄</div>
            <h3 class="confirm-dialog-title">Restore Sample Data?</h3>
            <p class="confirm-dialog-body">This will <strong>delete all current products</strong> and their stock history, then restore the original sample data.</p>
            <p class="confirm-dialog-warning">This cannot be undone.</p>
            <div class="confirm-dialog-actions">
                <button class="confirm-btn confirm-btn-cancel" @click="restoreSampleModal.open=false">Cancel</button>
                <button class="confirm-btn confirm-btn-danger" @click="doRestoreSample()">Yes, Restore</button>
            </div>
        </div>
    </div>

    {{-- ══ DELETE ALL PRODUCTS MODAL ══ --}}
    <div x-show="deleteAllModal.open" x-cloak class="modal-backdrop" @click.self="deleteAllModal.open=false"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="confirm-dialog" @click.stop>
            <div class="confirm-dialog-icon confirm-dialog-icon-danger">🗑️</div>
            <h3 class="confirm-dialog-title">Delete ALL Products?</h3>
            <p class="confirm-dialog-body">This permanently removes <strong>every product</strong> and all stock movement history from the system.</p>
            <p class="confirm-dialog-warning">This is irreversible and cannot be undone.</p>
            <div class="confirm-dialog-actions">
                <button class="confirm-btn confirm-btn-cancel" @click="deleteAllModal.open=false">Keep Products</button>
                <button class="confirm-btn confirm-btn-danger" @click="doDeleteAll()">Yes, Delete All</button>
            </div>
        </div>
    </div>
    </div>{{-- /wire:ignore --}}

    {{-- ── Toast Notification ── --}}
    <div x-show="flash !== ''"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         :class="flashType === 'success' ? 'inv-toast inv-toast-success' : 'inv-toast inv-toast-error'"
         @click="flash = ''; clearTimeout(flashTimer)">
        <div class="inv-toast-icon">
            <template x-if="flashType === 'success'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </template>
            <template x-if="flashType !== 'success'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </template>
        </div>
        <span x-text="flash" class="inv-toast-msg"></span>
        <button class="inv-toast-close" @click.stop="flash = ''; clearTimeout(flashTimer)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="inv-toast-bar" :class="flashType === 'success' ? 'inv-toast-bar-success' : 'inv-toast-bar-error'"></div>
    </div>

    <style>
        .inv-toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px 18px;
            border-radius: 14px;
            min-width: 280px;
            max-width: 380px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.45), 0 2px 8px rgba(0,0,0,0.3);
            cursor: pointer;
            overflow: hidden;
        }
        .inv-toast-success {
            background: #f0fdf4;
            border: 1.5px solid #16a34a;
            color: #166534;
        }
        .inv-toast-error {
            background: #fef2f2;
            border: 1.5px solid #dc2626;
            color: #991b1b;
        }
        .inv-toast-icon {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .inv-toast-success .inv-toast-icon { background: rgba(74,222,138,0.15); }
        .inv-toast-error   .inv-toast-icon { background: rgba(255,107,107,0.15); }
        .inv-toast-msg {
            flex: 1;
            font-size: 1rem;
            font-weight: 600;
            color: #f0f0f0;
            line-height: 1.4;
        }
        .inv-toast-close {
            flex-shrink: 0;
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            padding: 2px;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }
        .inv-toast-close:hover { color: #ccc; }
        .inv-toast-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            border-radius: 0 0 14px 14px;
            animation: inv-toast-shrink 3.5s linear forwards;
        }
        .inv-toast-bar-success { background: #16A34A; }
        .inv-toast-bar-error   { background: #DC2626; }
        @keyframes inv-toast-shrink {
            from { width: 100%; }
            to   { width: 0%; }
        }
    </style>

</div>
