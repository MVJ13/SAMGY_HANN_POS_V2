<div x-data="{
    productModal: { open: false, saving: false, error: '', product: null, _timer: null },
    stockModal:   { open: false, saving: false, error: '', product: null, _timer: null },
    deleteModal:  { open: false, product: null },
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
        'id'            => $p->id,
        'name'          => $p->name,
        'category'      => $p->category,
        'stock'         => (float) $p->stock,
        'unit'          => $p->unit,
        'cost'          => (float) $p->cost,
        'reorder_level' => (float) $p->reorder_level,
        'is_extra'      => (bool)  $p->is_extra,
        'selling_price' => (float) $p->selling_price,
        'is_low_stock'  => (bool)  $p->is_low_stock,
    ])->values()) }},

    movements: {{ json_encode($movements->map(fn($m) => [
        'id'             => $m->id,
        'product_name'   => $m->product_name,
        'type'           => $m->type,
        'quantity'       => (float) $m->quantity,
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
            id:            data.id,
            name:          data.name,
            category:      data.category,
            stock:         Number(data.stock),
            unit:          data.unit,
            cost:          Number(data.cost),
            reorder_level: Number(data.reorder_level),
            is_extra:      Boolean(data.is_extra),
            selling_price: Number(data.selling_price),
        } : {
            id: null, name: '', category: 'Meat', stock: 0,
            unit: 'kg', cost: 0, reorder_level: 10, is_extra: false, selling_price: 0
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
            <h2 style="color:var(--primary);margin:0;font-size:20px;">Inventory</h2>
            <div style="font-size:11px;color:var(--text-3);margin-top:3px;"
                 x-text="allProducts.length + ' products · Est. value: ' + formatPHP(totalValue)"></div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            
            <button class="btn btn-sm"
                    style="background:var(--bg-input);color:var(--text-secondary);border:1.5px solid var(--border);"
                    @click="if(confirm('Reset inventory to sample data? This cannot be undone.')) { $wire.resetSampleData(); }">
                ↺ Reset
            </button>
            <button class="btn btn-primary btn-sm" @click="openProduct(null)">＋ Add Product</button>
        </div>
    </div>

    {{-- ── Low stock alert banner ─────────────────────────────── --}}
    <template x-if="lowStockList.length > 0">
        <div style="background:rgba(230,57,70,0.08);border:1.5px solid rgba(230,57,70,0.35);border-radius:var(--radius-sm);padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span style="color:var(--danger);font-weight:700;white-space:nowrap;font-size:13px;">⚠️ Low Stock:</span>
            <template x-for="item in lowStockList" :key="item.id">
                <button @click="openStock(item)"
                        style="background:rgba(230,57,70,0.12);color:#ff8a80;padding:3px 10px;border-radius:20px;font-size:0.8em;font-weight:600;border:1px solid rgba(230,57,70,0.3);cursor:pointer;transition:all 0.15s;"
                        :title="'Click to restock ' + item.name"
                        x-text="item.name + ' (' + item.stock + ' ' + item.unit + ')'"></button>
            </template>
        </div>
    </template>

    {{-- ── Sub-tabs ──────────────────────────────────────────────── --}}
    <div style="display:flex;gap:4px;margin-bottom:16px;background:var(--bg-input);padding:4px;border-radius:var(--radius-sm);width:fit-content;">
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
                    style="padding:8px 10px;font-size:0.85em;min-width:140px;background:var(--bg-input);color:var(--text-1);">
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
                    <tr style="background:var(--bg-dark);">
                        <th style="width:26%;cursor:pointer;user-select:none;" @click="setSort('name')">
                            Product <span style="font-family:monospace;font-size:11px;opacity:0.55;" x-text="sortIcon('name')"></span>
                        </th>
                        <th style="width:11%;cursor:pointer;user-select:none;" @click="setSort('category')">
                            Category <span style="font-family:monospace;font-size:11px;opacity:0.55;" x-text="sortIcon('category')"></span>
                        </th>
                        <th style="width:14%;text-align:center;cursor:pointer;user-select:none;" @click="setSort('stock')">
                            Stock <span style="font-family:monospace;font-size:11px;opacity:0.55;" x-text="sortIcon('stock')"></span>
                        </th>
                        <th style="width:10%;text-align:center;cursor:pointer;user-select:none;" @click="setSort('cost')">
                            Cost <span style="font-family:monospace;font-size:11px;opacity:0.55;" x-text="sortIcon('cost')"></span>
                        </th>
                        <th style="width:9%;text-align:center;">Status</th>
                        <th style="width:10%;text-align:center;">Extra</th>
                        <th style="width:20%;text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="filtered.length === 0">
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                                <div style="font-size:2em;margin-bottom:8px;">🔍</div>
                                No products match your filters
                            </td>
                        </tr>
                    </template>
                    <template x-for="item in filtered" :key="item.id">
                        <tr :style="item.stock <= 0
                                ? 'background:rgba(230,57,70,0.07);'
                                : (item.is_low_stock ? 'background:rgba(245,166,35,0.04);' : '')">

                            {{-- Product name + reorder hint --}}
                            <td>
                                <div style="font-weight:600;color:var(--text-primary);font-size:13px;" x-text="item.name"></div>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:1px;font-family:'DM Mono',monospace;"
                                     x-text="'Reorder @ ' + item.reorder_level + ' ' + item.unit"></div>
                            </td>

                            {{-- Category --}}
                            <td>
                                <span style="background:var(--bg-input);color:var(--text-secondary);padding:2px 8px;border-radius:4px;font-size:0.78em;font-weight:600;"
                                      x-text="item.category"></span>
                            </td>

                            {{-- Stock + mini bar --}}
                            <td style="text-align:center;">
                                <div :style="item.stock <= 0
                                        ? 'color:var(--danger);font-weight:800;font-family:DM Mono,monospace;font-size:14px;'
                                        : (item.is_low_stock
                                            ? 'color:var(--warning);font-weight:700;font-family:DM Mono,monospace;font-size:14px;'
                                            : 'color:var(--success);font-weight:700;font-family:DM Mono,monospace;font-size:14px;')"
                                     x-text="item.stock + ' ' + item.unit"></div>
                                <div style="height:3px;background:rgba(255,255,255,0.06);border-radius:2px;margin-top:4px;overflow:hidden;">
                                    <div :style="'height:100%;border-radius:2px;transition:width 0.4s;background:'
                                        + (item.stock <= 0 ? 'var(--danger)' : (item.is_low_stock ? 'var(--warning)' : 'var(--success)'))
                                        + ';width:' + Math.min(100, item.reorder_level > 0 ? Math.round((item.stock / (item.reorder_level * 3)) * 100) : (item.stock > 0 ? 100 : 0)) + '%'">
                                    </div>
                                </div>
                            </td>

                            {{-- Cost --}}
                            <td style="text-align:center;color:var(--text-secondary);font-size:0.85em;font-family:'DM Mono',monospace;"
                                x-text="'₱' + item.cost.toFixed(2)"></td>

                            {{-- Status badge --}}
                            <td style="text-align:center;">
                                <span x-show="item.stock <= 0"
                                      style="background:rgba(230,57,70,0.18);color:var(--danger);padding:2px 8px;border-radius:10px;font-size:0.73em;font-weight:700;white-space:nowrap;">Out</span>
                                <span x-show="item.stock > 0 && item.is_low_stock"
                                      style="background:rgba(245,166,35,0.18);color:var(--warning);padding:2px 8px;border-radius:10px;font-size:0.73em;font-weight:700;white-space:nowrap;">Low</span>
                                <span x-show="item.stock > 0 && !item.is_low_stock"
                                      style="background:rgba(34,201,139,0.15);color:var(--success);padding:2px 8px;border-radius:10px;font-size:0.73em;font-weight:700;white-space:nowrap;">OK</span>
                            </td>

                            {{-- Extra toggle --}}
                            <td style="text-align:center;">
                                <button @click="$wire.toggleExtraFromCard(item.id)"
                                        :style="item.is_extra
                                            ? 'background:rgba(6,214,160,0.15);color:var(--success);border:1px solid var(--success);'
                                            : 'background:var(--bg-input);color:var(--text-muted);border:1px solid var(--border);'"
                                        style="border-radius:20px;padding:3px 10px;font-size:0.75em;font-weight:600;cursor:pointer;white-space:nowrap;transition:all 0.18s;">
                                    <span x-text="item.is_extra ? '✓ Extra' : '+ Extra'"></span>
                                </button>
                            </td>

                            {{-- Actions --}}
                            <td style="text-align:right;">
                                <div style="display:flex;gap:4px;justify-content:flex-end;">
                                    <button class="btn btn-sm"
                                            style="padding:5px 10px;background:var(--info);color:#fff;font-size:0.76em;"
                                            @click="openStock(item)" title="Adjust stock">
                                        📥 Stock
                                    </button>
                                    <button class="btn btn-sm"
                                            style="padding:5px 10px;background:var(--bg-input);color:var(--text-secondary);border:1px solid var(--border);font-size:0.76em;"
                                            @click="openProduct(item)" title="Edit product">
                                        ✏️ Edit
                                    </button>
                                    <button class="btn btn-sm"
                                            style="padding:5px 10px;background:transparent;color:var(--danger);border:1px solid rgba(230,57,70,0.35);font-size:0.76em;"
                                            @click="confirmDelete(item)" title="Delete product">
                                        🗑️
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
                <div class="ov-card-num" style="color:var(--info);" x-text="allProducts.length"></div>
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

            <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);padding:20px;">
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
            <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);padding:20px;">
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
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:rgba(255,77,77,0.06);border:1px solid rgba(255,77,77,0.18);border-radius:var(--r-sm);gap:8px;">
                            <div style="min-width:0;">
                                <div style="font-size:0.82em;color:var(--text-1);font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.name"></div>
                                <div style="font-size:0.75em;font-family:'DM Mono',monospace;color:var(--warning);" x-text="item.stock + ' ' + item.unit + ' left'"></div>
                            </div>
                            <button @click="openStock(item)"
                                    style="flex-shrink:0;padding:4px 10px;background:var(--info);color:#fff;border:none;border-radius:6px;font-size:0.75em;font-weight:700;cursor:pointer;white-space:nowrap;transition:all 0.15s;">
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
            <div style="display:flex;gap:3px;background:var(--bg-input);padding:3px;border-radius:var(--r-sm);">
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
                        <tr style="background:var(--bg-dark);">
                            <th style="width:22%;">Product</th>
                            <th style="width:9%;text-align:center;">Type</th>
                            <th style="width:10%;text-align:center;">Qty</th>
                            <th style="width:11%;text-align:center;">Before</th>
                            <th style="width:11%;text-align:center;">After</th>
                            <th style="width:22%;">Notes</th>
                            <th style="width:15%;">Time</th>
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
                    x-text="productModal.product && productModal.product.id ? '✏️ Edit Product' : '＋ Add New Product'"></h2>
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
                    <div class="two-col">
                        <div class="form-group">
                            <label>Initial Stock</label>
                            <input type="number" x-model.number="productModal.product.stock" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Reorder Level</label>
                            <input type="number" x-model.number="productModal.product.reorder_level" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Cost Price (₱)</label>
                        <input type="number" x-model.number="productModal.product.cost" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <template x-if="productModal.product.is_extra">
                        <div class="form-group">
                            <label>Selling Price (₱) <span style="color:var(--success);font-size:0.82em;">(shown on Extras menu)</span></label>
                            <input type="number" x-model.number="productModal.product.selling_price" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </template>
                    <template x-if="!productModal.product.is_extra">
                        <div class="form-group" style="background:rgba(6,214,160,0.08);border:1.5px solid rgba(6,214,160,0.25);border-radius:var(--radius-sm);padding:10px 14px;">
                            <p style="color:var(--text-muted);font-size:0.82em;margin:0;">
                                🛒 Toggle <strong style="color:var(--success);">+ Extra</strong> on this product's row to add it to the Extras menu and set a selling price.
                            </p>
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
                <h2 style="color:var(--primary);">📥 Adjust Stock</h2>
                <button class="close-modal" @click="closeStock()">×</button>
            </div>
            <template x-if="stockModal.product">
                <div>
                    {{-- Product info --}}
                    <div style="background:var(--bg-input);border-radius:var(--radius-sm);padding:14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
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
                    <div style="display:flex;gap:6px;margin-bottom:14px;">
                        <button @click="stockModal.product.type = 'in'"
                                :style="stockModal.product.type === 'in'
                                    ? 'flex:1;background:rgba(6,214,160,0.2);color:var(--success);border:2px solid var(--success);border-radius:var(--radius-sm);padding:10px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;'
                                    : 'flex:1;background:var(--bg-input);color:var(--text-muted);border:2px solid var(--border);border-radius:var(--radius-sm);padding:10px;cursor:pointer;font-size:14px;font-family:inherit;'">
                            ↑ Stock In
                        </button>
                        <button @click="stockModal.product.type = 'out'"
                                :style="stockModal.product.type === 'out'
                                    ? 'flex:1;background:rgba(230,57,70,0.2);color:var(--danger);border:2px solid var(--danger);border-radius:var(--radius-sm);padding:10px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;'
                                    : 'flex:1;background:var(--bg-input);color:var(--text-muted);border:2px solid var(--border);border-radius:var(--radius-sm);padding:10px;cursor:pointer;font-size:14px;font-family:inherit;'">
                            ↓ Stock Out
                        </button>
                    </div>

                    {{-- Quick preset buttons --}}
                    <div style="margin-bottom:14px;">
                        <div style="font-size:10px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:7px;">Quick Amount</div>
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <template x-for="preset in [1, 5, 10, 20, 50, 100]" :key="preset">
                                <button @click="stockModal.product.qty = preset"
                                        :style="Number(stockModal.product.qty) === preset
                                            ? 'padding:5px 12px;border-radius:6px;font-size:0.82em;font-weight:700;cursor:pointer;background:var(--primary);color:#fff;border:1.5px solid var(--primary);font-family:inherit;'
                                            : 'padding:5px 12px;border-radius:6px;font-size:0.82em;font-weight:600;cursor:pointer;background:var(--bg-input);color:var(--text-2);border:1.5px solid var(--border);font-family:inherit;'"
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
                        <div style="background:var(--bg-input);border-radius:var(--r-sm);padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;font-size:0.85em;">
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
        <div class="modal-box" @click.stop style="max-width:360px;text-align:center;padding:28px 24px;">
            <div style="font-size:2.5em;margin-bottom:12px;">🗑️</div>
            <h3 style="color:var(--text-1);margin-bottom:8px;font-size:16px;">Delete Product?</h3>
            <p style="color:var(--text-3);font-size:0.88em;margin-bottom:6px;line-height:1.5;">
                You're about to delete <strong style="color:var(--text-1);"
                    x-text="deleteModal.product ? deleteModal.product.name : ''"></strong>.
            </p>
            <p style="color:var(--danger);font-size:0.8em;margin-bottom:22px;line-height:1.5;">
                All stock movement history for this product will also be deleted. This cannot be undone.
            </p>
            <div style="display:flex;gap:10px;">
                <button class="btn" style="flex:1;background:var(--bg-input);color:var(--text-2);border:1px solid var(--border);"
                        @click="cancelDelete()">Cancel</button>
                <button class="btn" style="flex:1;background:var(--danger);color:#fff;"
                        @click="doDelete()">Yes, Delete</button>
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
            background: #1a2e25;
            border: 1.5px solid #2a5a3a;
            color: #4ade8a;
        }
        .inv-toast-error {
            background: #2e1a1a;
            border: 1.5px solid #5a2a2a;
            color: #ff6b6b;
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
            font-size: 0.9rem;
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
        .inv-toast-bar-success { background: #22c97a; }
        .inv-toast-bar-error   { background: #ff4d4d; }
        @keyframes inv-toast-shrink {
            from { width: 100%; }
            to   { width: 0%; }
        }
    </style>

</div>
