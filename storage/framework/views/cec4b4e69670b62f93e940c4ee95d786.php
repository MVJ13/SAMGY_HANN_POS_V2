<div>
    <h2 style="margin-bottom:20px;color:#ff6b6b;">Sales Statistics</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?php echo e($totalOrders); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₱<?php echo e(number_format($totalRevenue, 0)); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Customers</div>
            <div class="stat-value"><?php echo e($totalCustomers); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Average Order Value</div>
            <div class="stat-value">₱<?php echo e(number_format($avgOrder, 2)); ?></div>
        </div>
    </div>

    <h3 style="margin-top:20px;margin-bottom:20px;color:#ff6b6b;">Order History</h3>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentOrders->isEmpty()): ?>
        <div class="empty-state"><p>No completed orders yet</p></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>People</th>
                    <th>Packages</th>
                    <th>Subtotal</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Started</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>#<?php echo e($order->receipt_number); ?></td>
                        <td><?php echo e($order->total_people); ?></td>
                        <td style="font-size:0.85em;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($pkg['people']); ?>x <?php echo e($pkg['name']); ?> (₱<?php echo e($pkg['price']); ?>)<?php echo e(!$loop->last ? ', ' : ''); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td>₱<?php echo e(number_format($order->subtotal, 2)); ?></td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->discount_percent > 0): ?>
                                <span style="color:#4caf50;"><?php echo e($order->discount_percent); ?>% (-₱<?php echo e(number_format($order->discount_amount, 2)); ?>)</span>
                            <?php else: ?>
                                —
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td style="color:#ff6b6b;font-weight:700;">₱<?php echo e(number_format($order->total, 2)); ?></td>
                        <td><?php echo e($order->payment); ?></td>
                        <td><?php echo e($order->created_at->format('m/d/Y g:i A')); ?></td>
                        <td><?php echo e($order->completed_at?->format('m/d/Y g:i A') ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Mark\samgyeopsal-pos\resources\views/livewire/statistics.blade.php ENDPATH**/ ?>