<div x-data="{
        open: false,
        order: null,
        search: '',
        cancelConfirm: false,
        openOrder(data) {
            this.order = data;
            this.open  = true;
        },
        closeModal() {
            this.open = false;
        },
        confirmCancel() {
            this.cancelConfirm = true;
        },
        doCancel() {
            const id = this.order.id;
            this.cancelConfirm = false;
            this.$wire.cancelOrder(id);
        }
     }"
     @open-receipt.window="openOrder($event.detail.order)"
     @close-receipt-modal.window="closeModal()"
     @keydown.escape.window="closeModal()">

    
    <div class="rcpt-header">
        <div>
            <h2 class="rcpt-title">Order Queue</h2>
            <p class="rcpt-subtitle"><?php echo e($orders->count()); ?> active · <?php echo e($completedToday); ?> paid today</p>
        </div>
        <button wire:click="$refresh" class="rcpt-refresh-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Refresh
        </button>
    </div>

    
    <div class="rcpt-filterbar">
        <div class="rcpt-filters">
            <button wire:click="setTab('active')"    class="<?php echo e($activeTab === 'active'    ? 'rcpt-pill rcpt-pill-active' : 'rcpt-pill'); ?>">
                <span class="rcpt-dot rcpt-dot-warning"></span> Active
                <span class="rcpt-count"><?php echo e($orders->count()); ?></span>
            </button>
            <button wire:click="setTab('completed')" class="<?php echo e($activeTab === 'completed' ? 'rcpt-pill rcpt-pill-active' : 'rcpt-pill'); ?>">
                <span class="rcpt-dot rcpt-dot-success"></span> Paid
                <span class="rcpt-count"><?php echo e($completedOrders->count()); ?></span>
            </button>
            <button wire:click="setTab('cancelled')" class="<?php echo e($activeTab === 'cancelled' ? 'rcpt-pill rcpt-pill-active' : 'rcpt-pill'); ?>">
                <span class="rcpt-dot rcpt-dot-danger"></span> Cancelled
                <span class="rcpt-count"><?php echo e($cancelledOrders->count()); ?></span>
            </button>
        </div>
        <div class="rcpt-search-wrap">
            <svg class="rcpt-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input x-model="search" type="text" placeholder="Search receipt #..." class="rcpt-search">
        </div>
    </div>

    
    <div class="rcpt-grid">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $visibleOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $od = [
                    'id'               => $order->id,
                    'receipt_number'   => $order->receipt_number,
                    'status'           => $order->status,
                    'created_at'       => $order->created_at->format('M j, Y · g:i A'),
                    'completed_at'     => $order->completed_at?->format('M j, Y · g:i A') ?? '',
                    'payment'          => $order->payment,
                    'total_people'     => $order->total_people,
                    'packages'         => $order->packages    ?? [],
                    'addons'           => $order->addons      ?? [],
                    'extra_items'      => $order->extra_items ?? [],
                    'subtotal'         => (float) $order->subtotal,
                    'discount_percent' => (float) $order->discount_percent,
                    'discount_amount'  => (float) $order->discount_amount,
                    'discount_persons' => $order->discount_persons ?? [],
                    'total'            => (float) $order->total,
                    'amount_received'  => (float) $order->amount_received,
                    'change_given'     => (float) $order->change_given,
                ];
            ?>
            <div class="rcpt-card rcpt-card-<?php echo e($order->status); ?>"
                 wire:key="order-<?php echo e($order->id); ?>"
                 x-show="search === '' || '<?php echo e(strtolower($order->receipt_number)); ?>'.includes(search.toLowerCase())"
                 @click="openOrder(<?php echo e(Js::from($od)); ?>)">
                <div class="rcpt-card-top">
                    <div class="rcpt-card-num">#<?php echo e($order->receipt_number); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'active'): ?>
                        <span class="rcpt-badge rcpt-badge-warning">⏳ Pending</span>
                    <?php elseif($order->status === 'completed'): ?>
                        <span class="rcpt-badge rcpt-badge-success">✓ Paid</span>
                    <?php else: ?>
                        <span class="rcpt-badge rcpt-badge-danger">✕ Cancelled</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="rcpt-card-total">₱<?php echo e(number_format($order->total, 2)); ?></div>
                <div class="rcpt-card-meta">
                    <span class="rcpt-meta-chip rcpt-meta-people">👥 <?php echo e($order->total_people); ?> pax</span>
                    <span class="rcpt-meta-chip rcpt-meta-payment <?php echo e($order->payment === 'Cash' ? 'rcpt-meta-cash' : 'rcpt-meta-qr'); ?>">
                        <?php echo e($order->payment === 'Cash' ? '💵' : '📱'); ?> <?php echo e($order->payment); ?>

                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->discount_amount > 0): ?>
                        <span class="rcpt-meta-chip rcpt-meta-discount">🎫 −₱<?php echo e(number_format($order->discount_amount, 2)); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="rcpt-card-pkg"><?php echo e($order->package_summary); ?></div>
                <div class="rcpt-card-time"><?php echo e($order->created_at->diffForHumans()); ?></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($visibleOrders->isEmpty()): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'active'): ?>
                <div class="rcpt-empty">
                    <div class="rcpt-empty-icon">🍖</div>
                    <div class="rcpt-empty-text">No active orders</div>
                    <div class="rcpt-empty-sub">New orders will appear here</div>
                </div>
            <?php elseif($activeTab === 'completed'): ?>
                <div class="rcpt-empty">
                    <div class="rcpt-empty-icon">✓</div>
                    <div class="rcpt-empty-text">No paid orders yet</div>
                </div>
            <?php else: ?>
                <div class="rcpt-empty">
                    <div class="rcpt-empty-icon">—</div>
                    <div class="rcpt-empty-text">No cancelled orders</div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position:fixed; inset:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(4px); z-index:9999; display:flex; align-items:center; justify-content:center; padding:20px;"
         @click.self="closeModal()">

        <div class="rcpt-modal" @click.stop>

            
            <div class="rcpt-modal-header">
                <div>
                    <div class="rcpt-modal-num" x-text="order ? 'Receipt #' + order.receipt_number : ''"></div>
                    <div class="rcpt-modal-time" x-text="order ? order.created_at : ''"></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <template x-if="order">
                        <span :class="{
                            'rcpt-badge rcpt-badge-warning':  order.status === 'active',
                            'rcpt-badge rcpt-badge-success':  order.status === 'completed',
                            'rcpt-badge rcpt-badge-danger':   order.status === 'cancelled'
                        }" x-text="order.status === 'active' ? '⏳ Pending' : order.status === 'completed' ? '✓ Paid' : '✕ Cancelled'"></span>
                    </template>
                    <button class="close-modal" @click="closeModal()">×</button>
                </div>
            </div>

            
            <div id="print-area" class="rcpt-paper-wrap">
                <div class="rcpt-paper">

                    <div class="rcpt-paper-brand">
                        <img src="/samgyhann-logo.png" alt="SamgyHann 199" class="rcpt-paper-logo">
                        <div class="rcpt-paper-brandname">SamgyHann 199</div>
                        <div class="rcpt-paper-tagline">✦ Unlimited Samgyeopsal ✦</div>
                        <div class="rcpt-paper-divider">· · · · · · · · · · · · · · · ·</div>
                    </div>

                    <div class="rcpt-paper-meta">
                        <div class="rcpt-paper-row">
                            <span>Receipt No.</span>
                            <strong x-text="order ? '#' + order.receipt_number : ''"></strong>
                        </div>
                        <div class="rcpt-paper-row">
                            <span>Date & Time</span>
                            <span x-text="order ? order.created_at : ''"></span>
                        </div>
                        <template x-if="order && order.completed_at">
                            <div class="rcpt-paper-row">
                                <span>Paid At</span>
                                <span x-text="order.completed_at"></span>
                            </div>
                        </template>
                        <div class="rcpt-paper-row">
                            <span>Payment</span>
                            <strong x-text="order ? order.payment : ''"></strong>
                        </div>
                        <div class="rcpt-paper-row">
                            <span>Guests</span>
                            <strong x-text="order ? order.total_people + ' pax' : ''"></strong>
                        </div>
                    </div>

                    <div class="rcpt-paper-section-label">Order Summary</div>
                    <hr class="rcpt-paper-rule">

                    <div class="rcpt-paper-items">
                        <template x-if="order && order.packages.length > 0">
                            <div>
                                <template x-for="pkg in order.packages" :key="pkg.name">
                                    <div class="rcpt-paper-item">
                                        <div>
                                            <div class="rcpt-paper-item-name" x-text="pkg.name + ' Package'"></div>
                                            <div class="rcpt-paper-item-sub" x-text="pkg.people + ' pax × ₱' + Number(pkg.price).toLocaleString('en-PH',{minimumFractionDigits:2})"></div>
                                        </div>
                                        <div class="rcpt-paper-item-amt" x-text="'₱' + (pkg.people * pkg.price).toLocaleString('en-PH',{minimumFractionDigits:2})"></div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="order && order.addons.length > 0">
                            <div>
                                <template x-for="addon in order.addons" :key="addon.name">
                                    <div class="rcpt-paper-item">
                                        <div>
                                            <div class="rcpt-paper-item-name" x-text="addon.name + ' Add-on'"></div>
                                            <div class="rcpt-paper-item-sub" x-text="addon.people + ' pax × ₱' + Number(addon.price).toLocaleString('en-PH',{minimumFractionDigits:2})"></div>
                                        </div>
                                        <div class="rcpt-paper-item-amt" x-text="'₱' + (addon.people * addon.price).toLocaleString('en-PH',{minimumFractionDigits:2})"></div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="order && order.extra_items.length > 0">
                            <div>
                                <template x-for="extra in order.extra_items" :key="extra.name">
                                    <div class="rcpt-paper-item">
                                        <div>
                                            <div class="rcpt-paper-item-name" x-text="extra.name"></div>
                                            <div class="rcpt-paper-item-sub" x-text="extra.qty + ' pc(s) × ₱' + Number(extra.price).toLocaleString('en-PH',{minimumFractionDigits:2})"></div>
                                        </div>
                                        <div class="rcpt-paper-item-amt" x-text="'₱' + Number(extra.amount).toLocaleString('en-PH',{minimumFractionDigits:2})"></div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="order && order.discount_amount > 0">
                            <div>
                                <template x-if="order.discount_persons && order.discount_persons.length > 0">
                                    <div>
                                        <template x-for="(dp, i) in order.discount_persons" :key="i">
                                            <div class="rcpt-paper-row rcpt-paper-row-discount" style="font-size:0.88em;">
                                                <span x-text="(dp.type==='senior'?'👴 Senior SC':dp.type==='pwd'?'♿ PWD':'🧒 Child')+' · '+(dp.pkg==='p199'?'Basic':dp.pkg==='p269'?'Premium':'Deluxe')+' −'+dp.pct+'%'"></span>
                                                <span x-text="'−₱'+Number((dp.pkg==='p199'?199:dp.pkg==='p269'?269:349)*(dp.pct/100)).toLocaleString('en-PH',{minimumFractionDigits:2})"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <div class="rcpt-paper-row rcpt-paper-row-discount" style="font-weight:700;">
                                    <span>🎫 Total Discount</span>
                                    <span x-text="'−₱'+Number(order.discount_amount).toLocaleString('en-PH',{minimumFractionDigits:2})"></span>
                                </div>
                            </div>
                        </template>
                        <div class="rcpt-paper-total-row">
                            <span>TOTAL</span>
                            <span x-text="order ? '₱' + Number(order.total).toLocaleString('en-PH',{minimumFractionDigits:2}) : ''"></span>
                        </div>
                        <template x-if="order && order.payment === 'Cash'">
                            <div class="rcpt-paper-cash-section">
                                <div class="rcpt-paper-row rcpt-paper-row-muted">
                                    <span>💵 Cash Received</span>
                                    <span x-text="'₱' + Number(order.amount_received).toLocaleString('en-PH',{minimumFractionDigits:2})"></span>
                                </div>
                                <div class="rcpt-paper-row rcpt-paper-row-muted">
                                    <span>Change</span>
                                    <span x-text="'₱' + Number(order.change_given).toLocaleString('en-PH',{minimumFractionDigits:2})"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="rcpt-paper-footer">
                        <div class="rcpt-paper-divider" style="margin-top:18px;">· · · · · · · · · · · · · · · ·</div>
                        <div class="rcpt-paper-footer-msg">Salamat! Come back soon 🙏</div>
                        <div class="rcpt-paper-footer-addr">Olongapo City, Zambales</div>
                        <div class="rcpt-paper-tear"></div>
                    </div>
                </div>
            </div>

            
            
            <div class="rcpt-modal-actions">

                <button class="rcpt-action-btn rcpt-action-pay"
                        x-show="order && order.status === 'active'"
                        @click="const id = order.id; $wire.markAsPaid(id)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Mark as Paid
                </button>

                <button class="rcpt-action-btn rcpt-action-cancel"
                        x-show="order && order.status === 'active'"
                        @click="confirmCancel()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Cancel Order
                </button>

                <button class="rcpt-action-btn rcpt-action-print"
                        x-show="order && order.status === 'active'"
                        onclick="window.print()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print
                </button>

                <button class="rcpt-action-btn rcpt-action-print"
                        x-show="order && order.status !== 'active'"
                        style="flex:1;"
                        onclick="window.print()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Receipt
                </button>

            </div>
        </div>
    </div>

    
    <div x-show="cancelConfirm"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="cancelConfirm = false"
         style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;"
         x-cloak>

        
        <div @click="cancelConfirm = false"
             style="position:absolute;inset:0;background:rgba(0,0,0,0.75);"></div>

        
        <div x-show="cancelConfirm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             style="position:relative;background:#242428;border:1px solid #3a3a3f;border-radius:16px;padding:2rem;width:100%;max-width:380px;box-shadow:0 32px 64px rgba(0,0,0,0.6);text-align:center;">

            
            <div style="width:56px;height:56px;border-radius:50%;background:#3a1a1a;border:1.5px solid #7a2a2a;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff4d4d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </div>

            <h3 style="font-size:1.15rem;font-weight:700;color:#f0f0f0;margin:0 0 0.5rem;">Cancel Order?</h3>
            <p style="font-size:0.9rem;color:#a0a0a8;margin:0 0 1.75rem;line-height:1.5;">
                Order <span x-text="'#' + (order ? order.receipt_number : '')" style="font-weight:600;color:#f0f0f0;"></span> will be marked as cancelled. This cannot be undone.
            </p>

            <div style="display:flex;gap:0.75rem;">
                <button @click="cancelConfirm = false"
                        style="flex:1;padding:0.75rem 1rem;border-radius:10px;border:1.5px solid #3a3a3f;background:#1c1c1f;color:#e0e0e0;font-size:0.9rem;font-weight:600;cursor:pointer;transition:background 0.15s;"
                        onmouseover="this.style.background='#2c2c31'" onmouseout="this.style.background='#1c1c1f'">
                    Keep Order
                </button>
                <button @click="doCancel()"
                        style="flex:1;padding:0.75rem 1rem;border-radius:10px;border:1.5px solid #cc2a2a;background:#cc2a2a;color:#fff;font-size:0.9rem;font-weight:600;cursor:pointer;transition:background 0.15s;"
                        onmouseover="this.style.background='#b02020'" onmouseout="this.style.background='#cc2a2a'">
                    Yes, Cancel
                </button>
            </div>
        </div>
    </div>

</div>
<?php /**PATH C:\Users\Mark\samgyeopsal-pos - Copy\resources\views/livewire/receipts.blade.php ENDPATH**/ ?>