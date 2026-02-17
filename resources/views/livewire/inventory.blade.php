<div x-data="{
    productModal: { open: false, saving: false, error: '', product: null },
    stockModal:   { open: false, saving: false, error: '', product: null },

    openProduct(data) {
        this.productModal.error = '';
        this.productModal.product = data ? { ...data } : {
            id: null, name: '', category: 'Meat', stock: 0,
            unit: '', cost: 0, reorder_level: 10, is_extra: false, selling_price: 0
        };
        this.productModal.open = true;
    },
    closeProduct() { this.productModal.open = false; this.productModal.product = null; },

    openStock(data) {
        this.stockModal.error = '';
        this.stockModal.product = { id: data.id, name: data.name, type: 'in', qty: '', notes: '' };
        this.stockModal.open = true;
    },
    closeStock() { this.stockModal.open = false; this.stockModal.product = null; },

    saveProduct() {
        this.productModal.saving = true;
        this.productModal.error  = '';
        this.$wire.saveProduct(this.productModal.product)
            .then(() => {
                if (!this.productModal.error) {
                    this.closeProduct();
                    // Refresh the list after modal closes (no flicker during save)
                    setTimeout(() => this.$wire.$refresh(), 150);
                }
            })
            .finally(() => { this.productModal.saving = false; });
    },
    saveStock() {
        this.stockModal.saving = true;
        this.stockModal.error  = '';
        this.$wire.saveStockMovement({
            product_id: this.stockModal.product.id,
            type:       this.stockModal.product.type,
            qty:        this.stockModal.product.qty,
            notes:      this.stockModal.product.notes,
        }).then(() => {
            if (!this.stockModal.error) {
                this.closeStock();
                // Refresh the list after modal closes (no flicker during save)
                setTimeout(() => this.$wire.$refresh(), 150);
            }
        }).finally(() => { this.stockModal.saving = false; });
    }
}"
    @product-save-error.window="productModal.error = $event.detail.message; productModal.saving = false"
    @stock-save-error.window="stockModal.error = $event.detail.message; stockModal.saving = false"
    @keydown.escape.window="closeProduct(); closeStock()">

    <h2 style="margin-bottom:20px;color:#ff6b6b;">Inventory Management</h2>

    @if($flashMessage)
        <div class="flash-success" id="flash-inventory" wire:ignore>{{ $flashMessage }}</div>
        <script>
            (function () {
                var el = document.getElementById('flash-inventory');
                if (el) {
                    setTimeout(function () {
                        el.style.display = 'none';
                        if (typeof Livewire !== 'undefined') {
                            Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'))?.call('dismissFlash');
                        }
                    }, 3000);
                }
            })();
        </script>
    @endif

    {{-- Low Stock Alert --}}
    @if($lowStock->isNotEmpty())
        <div class="low-stock-alert">
            <h3><span style="font-size:1.5em;">⚠️</span> Low Stock Alert</h3>
            <ul>
                @foreach($lowStock as $item)
                    <li>
                        <strong style="color:#fff;font-size:1.1em;">{{ $item->name }}</strong>
                        <span style="color:#ffeb3b;"> → Only {{ $item->stock }} {{ $item->unit }} remaining!</span>
                        <span style="color:#e0e0e0;"> (Reorder at {{ $item->reorder_level }} {{ $item->unit }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Action Buttons --}}
    <button class="btn btn-primary" style="width:auto;display:inline-block;margin-right:10px;"
            @click="openProduct(null)">+ Add New Product</button>
    <button class="btn btn-orange" style="width:auto;display:inline-block;"
            wire:click="resetSampleData"
            wire:confirm="This will reset inventory to sample data. Are you sure?">Reset Sample Data</button>

    {{-- Inventory Cards --}}
    <div class="inventory-grid"
         :style="(productModal.open || stockModal.open) ? 'pointer-events:none;user-select:none;' : ''">
        @forelse($products as $item)
            <div class="inventory-item {{ $item->is_low_stock ? 'low-stock-item-highlight' : '' }}">
                @if($item->is_low_stock)
                    <div class="low-stock-badge">⚠️ LOW STOCK ⚠️</div>
                @endif
                <h3 style="color:{{ $item->is_low_stock ? '#f44336' : '#ff6b6b' }};">{{ $item->name }}</h3>
                <p style="color:#999;">{{ $item->category }}</p>
                <div class="stock-level {{ $item->is_low_stock ? 'stock-low' : 'stock-ok' }}" style="font-size:1.8em;margin:15px 0;">
                    {{ $item->stock }} {{ $item->unit }}
                </div>
                <p style="font-size:0.9em;color:#999;">Cost: ₱{{ number_format($item->cost, 2) }}/{{ $item->unit }}</p>
                <p style="font-size:0.85em;color:#999;margin-top:5px;">Reorder at: {{ $item->reorder_level }} {{ $item->unit }}</p>

                @if($item->is_extra)
                    <div style="margin-top:8px;background:#1a3a1a;border:1px solid #4caf50;border-radius:6px;padding:6px 10px;font-size:0.82em;color:#4caf50;">
                        🛒 In Extras menu · Sell @ ₱{{ number_format($item->selling_price, 2) }}
                    </div>
                @endif

                @php
                    $productJson = json_encode([
                        'id'            => $item->id,
                        'name'          => $item->name,
                        'category'      => $item->category,
                        'stock'         => (float) $item->stock,
                        'unit'          => $item->unit,
                        'cost'          => (float) $item->cost,
                        'reorder_level' => (float) $item->reorder_level,
                        'is_extra'      => (bool)  $item->is_extra,
                        'selling_price' => (float) $item->selling_price,
                    ]);
                    $stockJson = json_encode([
                        'id'   => $item->id,
                        'name' => $item->name,
                    ]);
                @endphp
                <div style="margin-top:15px;">
                    <button class="btn btn-orange btn-sm"
                            @click="openStock({{ $stockJson }})">Adjust Stock</button>
                    <button class="btn btn-orange btn-sm"
                            @click="openProduct({{ $productJson }})">Edit</button>
                    <button class="btn btn-red btn-sm"
                            @click="if(confirm('Delete {{ $item->name }}?')) { $wire.deleteProduct({{ $item->id }}).then(() => setTimeout(() => $wire.$refresh(), 150)); }">Delete</button>
                    <button class="btn btn-sm"
                            style="margin:2px;background:{{ $item->is_extra ? '#4caf50' : '#424242' }};color:#fff;"
                            @click="$wire.toggleExtraFromCard({{ $item->id }}).then(() => setTimeout(() => $wire.$refresh(), 150))">
                        {{ $item->is_extra ? '🛒 In Extras' : '+ Add to Extras' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column:1/-1;">
                <h3>No products in inventory</h3>
                <p>Click "+ Add New Product" or "Reset Sample Data"</p>
            </div>
        @endforelse
    </div>

    {{-- Inventory Overview --}}
    <h3 style="margin-top:40px;margin-bottom:20px;color:#ff6b6b;">Inventory Overview</h3>
    <div class="inv-overview">
        <div class="inv-card" style="border-left:5px solid #2196f3;">
            <div class="inv-card-label">Total Products</div>
            <div class="inv-card-value" style="color:#2196f3;">{{ $products->count() }}</div>
        </div>
        <div class="inv-card" style="border-left:5px solid #f44336;">
            <div class="inv-card-label">⚠️ Low Stock Items</div>
            <div class="inv-card-value" style="color:#f44336;">{{ $lowStock->count() }}</div>
            @if($lowStock->isNotEmpty())
                <div style="margin-top:15px;font-size:0.9em;border-top:1px solid #424242;padding-top:10px;">
                    @foreach($lowStock as $item)
                        <div style="padding:5px 0;color:#ffeb3b;">• {{ $item->name }}: <strong>{{ $item->stock }} {{ $item->unit }}</strong></div>
                    @endforeach
                </div>
            @else
                <div style="color:#4caf50;margin-top:10px;">All items well stocked! 🎉</div>
            @endif
        </div>
        <div class="inv-card" style="border-left:5px solid #4caf50;">
            <div class="inv-card-label">✓ Healthy Stock Items</div>
            <div class="inv-card-value" style="color:#4caf50;">{{ $healthyStock->count() }}</div>
        </div>
    </div>

    {{-- Stock Movement History --}}
    <h3 style="margin-bottom:20px;color:#ff6b6b;">Stock Movement History</h3>
    @if($movements->isEmpty())
        <div class="empty-state"><p>No stock movements recorded</p></div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Product</th><th>Type</th><th>Quantity</th>
                    <th>Previous</th><th>New</th><th>Notes</th><th>Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $m)
                    <tr>
                        <td>{{ $m->product_name }}</td>
                        <td style="color:{{ $m->type === 'in' ? '#4caf50' : '#f44336' }};font-weight:600;">
                            {{ $m->type === 'in' ? '↑ IN' : '↓ OUT' }}
                        </td>
                        <td>{{ $m->quantity }}</td>
                        <td>{{ $m->previous_stock }}</td>
                        <td>{{ $m->new_stock }}</td>
                        <td>{{ $m->notes }}</td>
                        <td>{{ $m->created_at->format('m/d/Y, g:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ══ ADD / EDIT PRODUCT MODAL (Alpine, teleported to body) ══ --}}
    <template x-teleport="body">
        <div x-show="productModal.open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-backdrop"
             @click.self="closeProduct()"
             style="display:none;">

            <div class="modal-box" @click.stop>
                <div class="modal-header">
                    <h2 style="color:#ff6b6b;"
                        x-text="productModal.product && productModal.product.id ? 'Edit Product' : 'Add New Product'"></h2>
                    <button class="close-modal" @click="closeProduct()">×</button>
                </div>

                <template x-if="productModal.product">
                    <div>
                        <template x-if="productModal.error">
                            <p style="color:#f44336;margin-bottom:12px;" x-text="productModal.error"></p>
                        </template>

                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" x-model="productModal.product.name" placeholder="Enter product name">
                        </div>

                        <div class="form-group">
                            <label>Category</label>
                            <select x-model="productModal.product.category">
                                <option value="Meat">Meat</option>
                                <option value="Vegetables">Vegetables</option>
                                <option value="Sauce">Sauce</option>
                                <option value="Drinks">Drinks</option>
                                <option value="Processed">Processed</option>
                                <option value="Grains">Grains</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="two-col">
                            <div class="form-group">
                                <label>Stock Quantity</label>
                                <input type="number" x-model.number="productModal.product.stock" min="0" step="0.01">
                            </div>
                            <div class="form-group">
                                <label>Unit</label>
                                <input type="text" x-model="productModal.product.unit" placeholder="kg, pcs, L">
                            </div>
                        </div>

                        <div class="two-col">
                            <div class="form-group">
                                <label>Cost Price (₱)</label>
                                <input type="number" x-model.number="productModal.product.cost" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Reorder Level</label>
                                <input type="number" x-model.number="productModal.product.reorder_level" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="form-group" style="background:#1a1a1a;border:1px solid #424242;border-radius:8px;padding:14px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;">
                                <input type="checkbox" x-model="productModal.product.is_extra" style="width:18px;height:18px;accent-color:#ff6b6b;">
                                <span style="color:#fff;font-weight:600;">🛒 Show in "Extra Items" on New Order tab</span>
                            </label>
                            <p style="color:#999;font-size:0.85em;margin:8px 0 0 28px;">Stock will be deducted automatically when sold.</p>
                            <div x-show="productModal.product.is_extra" style="margin-top:12px;margin-left:28px;">
                                <label style="font-size:0.9em;color:#ccc;">Selling Price (₱) <span style="color:#ff6b6b;">*</span></label>
                                <input type="number" x-model.number="productModal.product.selling_price" min="0" step="0.01" placeholder="0.00" style="max-width:180px;">
                            </div>
                        </div>

                        <button class="btn btn-primary"
                                @click="saveProduct()"
                                :disabled="productModal.saving">
                            <span x-text="productModal.saving ? 'Saving…' : 'Save Product'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- ══ STOCK ADJUSTMENT MODAL (Alpine, teleported to body) ══ --}}
    <template x-teleport="body">
        <div x-show="stockModal.open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-backdrop"
             @click.self="closeStock()"
             style="display:none;">

            <div class="modal-box" @click.stop>
                <div class="modal-header">
                    <h2 style="color:#ff6b6b;">
                        Adjust Stock — <span x-text="stockModal.product ? stockModal.product.name : ''"></span>
                    </h2>
                    <button class="close-modal" @click="closeStock()">×</button>
                </div>

                <template x-if="stockModal.product">
                    <div>
                        <template x-if="stockModal.error">
                            <p style="color:#f44336;margin-bottom:12px;" x-text="stockModal.error"></p>
                        </template>

                        <div class="form-group">
                            <label>Movement Type</label>
                            <select x-model="stockModal.product.type">
                                <option value="in">Stock In</option>
                                <option value="out">Stock Out</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" x-model.number="stockModal.product.qty" min="0.01" step="0.01" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <input type="text" x-model="stockModal.product.notes" placeholder="Reason for movement">
                        </div>

                        <button class="btn btn-primary"
                                @click="saveStock()"
                                :disabled="stockModal.saving">
                            <span x-text="stockModal.saving ? 'Saving…' : 'Save Movement'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>

</div>
