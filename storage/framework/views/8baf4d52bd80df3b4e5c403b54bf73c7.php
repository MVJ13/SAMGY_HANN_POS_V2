<div class="login-wrap">
    <div class="login-card">

        
        <div class="login-brand">
            <img src="/samgyhann-logo.png" alt="SamgyHann 199" class="login-logo">
            <div class="login-brandname">SamgyHann 199</div>
            <div class="login-tagline">Point of Sale System</div>
        </div>

        <div class="login-divider"></div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($error): ?>
            <div class="login-error">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo e($error); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <form wire:submit="login" class="login-form">

            <div class="login-field">
                <label class="login-label">Username</label>
                <div class="login-input-wrap">
                    <input
                        type="text"
                        wire:model="username"
                        class="login-input"
                        placeholder="Enter your username"
                        autocomplete="username"
                        autofocus
                    >
                </div>
            </div>

            <div class="login-field" x-data="{ show: false }">
                <label class="login-label">Password</label>
                <div class="login-input-wrap">
                    <input
                        :type="show ? 'text' : 'password'"
                        wire:model="password"
                        class="login-input login-input-pw"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                    <button type="button" class="login-pw-toggle" @click="show = !show" tabindex="-1">
                        <svg x-show="!show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-btn" wire:loading.attr="disabled">
                <span wire:loading.remove>Sign In</span>
                <span wire:loading class="login-btn-loading">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="login-spinner"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Signing in...
                </span>
            </button>

        </form>

        <div class="login-footer">
            SamgyHann 199 &copy; <?php echo e(date('Y')); ?> · Olongapo City, Zambales
        </div>

    </div>
</div>
<?php /**PATH C:\Users\Mark\samgyeopsal-pos - Copy\resources\views/livewire/login.blade.php ENDPATH**/ ?>