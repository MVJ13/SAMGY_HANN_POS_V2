<div x-data="{
        open: false,
        order: null,
        show(data) { this.order = data; this.open = true; },
        close()    { this.open = false; this.order = null; }
     }"
     @open-receipt.window="show($event.detail.order)"
     @keydown.escape.window="close()">

    <h2 style="margin-bottom:20px;color:#ff6b6b;">Receipts</h2>
    <p style="color:#999;margin-bottom:20px;">All order receipts for easy bookkeeping. Click a receipt to view details or mark as paid.</p>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders->isEmpty()): ?>
        <div class="empty-state">
            <h3>No receipts yet</h3>
            <p>Create a new order to generate a receipt</p>
        </div>
    <?php else: ?>
        
        <div class="tab-grid" :style="open ? 'pointer-events:none;user-select:none;' : ''">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $orderData = [
                        'id'               => $order->id,
                        'receipt_number'   => $order->receipt_number,
                        'created_at'       => $order->created_at->format('m/d/Y, g:i:s A'),
                        'payment'          => $order->payment,
                        'total_people'     => $order->total_people,
                        'packages'         => $order->packages    ?? [],
                        'addons'           => $order->addons      ?? [],
                        'extra_items'      => $order->extra_items ?? [],
                        'subtotal'         => (float) $order->subtotal,
                        'discount_percent' => (float) $order->discount_percent,
                        'discount_amount'  => (float) $order->discount_amount,
                        'total'            => (float) $order->total,
                    ];
                ?>
                <div class="tab-card" @click="show(<?php echo e(json_encode($orderData)); ?>)">
                    <div class="tab-card-header">
                        <div class="tab-number">Receipt #<?php echo e($order->receipt_number); ?></div>
                        <div class="tab-time"><?php echo e($order->created_at->format('m/d/Y, g:i:s A')); ?></div>
                    </div>
                    <div class="tab-details">
                        <div class="tab-detail-row">
                            <span>👥 Total People:</span>
                            <span style="font-weight:600;"><?php echo e($order->total_people); ?></span>
                        </div>
                        <div class="tab-detail-row">
                            <span>📦 Packages:</span>
                            <span style="font-weight:600;font-size:0.9em;"><?php echo e($order->package_summary); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->addons_summary): ?>
                            <div class="tab-detail-row">
                                <span>➕ Add-ons:</span>
                                <span style="font-weight:600;font-size:0.85em;"><?php echo e($order->addons_summary); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="tab-detail-row">
                            <span>💳 Payment:</span>
                            <span style="font-weight:600;"><?php echo e($order->payment); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->discount_percent > 0): ?>
                            <div class="tab-detail-row">
                                <span>🎫 Discount:</span>
                                <span style="font-weight:600;color:#4caf50;"><?php echo e($order->discount_percent); ?>%</span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="tab-total">
                        <span>TOTAL:</span>
                        <span class="tab-total-amount">₱<?php echo e(number_format($order->total, 2)); ?></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <template x-teleport="body">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-backdrop"
             @click.self="close()"
             style="display:none;">

            <div class="modal-box receipt-modal" @click.stop>
                <div class="modal-header">
                    <h2 style="color:#ff6b6b;">Receipt</h2>
                    <button class="close-modal" @click="close()">×</button>
                </div>

                <template x-if="order">
                    <div>
                        <div id="print-area" style="background:#fff;color:#000;padding:30px;border-radius:8px;margin:20px 0;">
                            <div class="receipt-paper">
                                <div class="receipt-header">
                                    <div class="receipt-title">UNLIMITED SAMGYEOPSAL</div>
                                    <div>Self-Service POS</div>
                                    <div style="margin-top:10px;font-size:0.9em;">================================</div>
                                </div>

                                <div style="margin:20px 0;">
                                    <div class="receipt-line">
                                        <span>Receipt #:</span>
                                        <span><strong x-text="'#' + order.receipt_number"></strong></span>
                                    </div>
                                    <div class="receipt-line">
                                        <span>Date:</span>
                                        <span x-text="order.created_at"></span>
                                    </div>
                                    <div class="receipt-line">
                                        <span>Payment:</span>
                                        <span x-text="order.payment"></span>
                                    </div>
                                    <div class="receipt-line">
                                        <span>Total People:</span>
                                        <span><strong x-text="order.total_people"></strong></span>
                                    </div>
                                </div>

                                <div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:15px 0;margin:15px 0;">

                                    
                                    <template x-for="pkg in order.packages" :key="pkg.name">
                                        <div>
                                            <div class="receipt-line">
                                                <span x-text="pkg.name + ' (₱' + pkg.price + '):'"></span>
                                                <span></span>
                                            </div>
                                            <div class="receipt-line" style="margin-left:20px;">
                                                <span x-text="pkg.people + (pkg.people == 1 ? ' person' : ' people') + ' × ₱' + pkg.price"></span>
                                                <span x-text="'₱' + (pkg.people * pkg.price).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                                            </div>
                                        </div>
                                    </template>

                                    
                                    <template x-if="order.addons && order.addons.length > 0">
                                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid #ccc;">
                                            <template x-for="addon in order.addons" :key="addon.name">
                                                <div>
                                                    <div class="receipt-line">
                                                        <span x-text="addon.name + ' (₱' + addon.price + '/person):'"></span>
                                                        <span></span>
                                                    </div>
                                                    <div class="receipt-line" style="margin-left:20px;">
                                                        <span x-text="addon.people + (addon.people == 1 ? ' person' : ' people') + ' × ₱' + addon.price"></span>
                                                        <span x-text="'₱' + (addon.people * addon.price).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    
                                    <template x-if="order.extra_items && order.extra_items.length > 0">
                                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid #ccc;">
                                            <div style="font-weight:700;margin-bottom:4px;">Extra Items:</div>
                                            <template x-for="extra in order.extra_items" :key="extra.name">
                                                <div class="receipt-line" style="margin-left:20px;">
                                                    <span x-text="extra.qty + 'x ' + extra.name"></span>
                                                    <span x-text="'₱' + Number(extra.amount).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <div class="receipt-line" style="margin-top:10px;padding-top:10px;border-top:1px solid #ccc;">
                                        <span>Subtotal:</span>
                                        <span x-text="'₱' + Number(order.subtotal).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                                    </div>
                                    <template x-if="order.discount_percent > 0">
                                        <div class="receipt-line" style="color:green;">
                                            <span x-text="'Discount (' + order.discount_percent + '%):'"></span>
                                            <span x-text="'-₱' + Number(order.discount_amount).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                                        </div>
                                    </template>
                                </div>

                                <div class="receipt-total">
                                    <div class="receipt-line">
                                        <span>TOTAL AMOUNT:</span>
                                        <span x-text="'₱' + Number(order.total).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                                    </div>
                                </div>

                                <div class="receipt-footer">
                                    <div>Thank you for dining with us!</div>
                                    <div style="margin-top:10px;">================================</div>
                                    <div style="margin-top:10px;font-size:0.85em;">Please come again!</div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-green"
                                @click="$wire.markAsPaid(order.id).then(() => { close(); setTimeout(() => $wire.$refresh(), 150); })">
                            Mark as Paid
                        </button>
                        <button class="btn btn-blue" onclick="window.print()">
                            Print Receipt
                        </button>
                        <button class="btn btn-red"
                                @click="if(confirm('Cancel this order?')) { $wire.cancelOrder(order.id).then(() => { close(); setTimeout(() => $wire.$refresh(), 150); }); }">
                            Cancel Order
                        </button>
                    </div>
                </template>

            </div>
        </div>
    </template>

</div>
<?php /**PATH C:\Users\Mark\samgyeopsal-pos\resources\views/livewire/receipts.blade.php ENDPATH**/ ?>