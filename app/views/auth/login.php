<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Global Toast Notification Assets -->
<link rel="stylesheet" href="/tenant/public/assets/css/toast.css">

<div id="toast-stack"></div>

<div class="auth-container full-width">
    <div class="auth-hero">
        <div class="auth-hero-content">
            <a href="/tenant/" class="brand-logo" id="main-brand-logo" style="text-decoration: none; color: white;">
                <i class="fas fa-house-chimney-window"></i>
                <span>StayHub</span>
            </a>
            <h1>Welcome <br>Back.</h1>
            <p>Single sign-on for everyone. Enter your credentials to automatically access your administrator, owner, or tenant portal.</p>

            <div class="hero-role-list" style="margin-top: 32px; display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; opacity: 0.95;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-shield-halved" style="font-size: 0.9rem;"></i>
                    </div>
                    <span><strong>Administrators:</strong> System analytics, user & role management</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; opacity: 0.95;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-building-user" style="font-size: 0.9rem;"></i>
                    </div>
                    <span><strong>Property Owners:</strong> Houses, rooms, and tenant bookings</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; opacity: 0.95;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user" style="font-size: 0.9rem;"></i>
                    </div>
                    <span><strong>Tenants:</strong> Room browsing, payments, and residency history</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="mobile-brand">
                <i class="fas fa-house-chimney-window"></i>
                <span>StayHub</span>
            </div>
            
            <a href="/tenant/" style="display: inline-flex; align-items: center; gap: 8px; color: var(--dash-text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 700; margin-bottom: 20px; transition: color 0.2s;" onmouseover="this.style.color='var(--dash-primary)'" onmouseout="this.style.color='var(--dash-text-muted)'">
                <i class="fas fa-arrow-left"></i> Back to Website
            </a>
            
            <h2>Sign In</h2>
            <p class="subtitle">Enter your username or email address to access your portal.</p>

            <div class="role-badge-chips" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 22px;">
                <span class="role-chip admin" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 0.76rem; font-weight: 600; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                    <i class="fa-solid fa-shield-halved"></i> Admin
                </span>
                <span class="role-chip owner" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 0.76rem; font-weight: 600; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd;">
                    <i class="fa-solid fa-building-user"></i> Owner
                </span>
                <span class="role-chip tenant" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 0.76rem; font-weight: 600; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                    <i class="fa-solid fa-user"></i> Tenant
                </span>
            </div>

            <form method="POST" action="/tenant/?url=auth/login" id="login-form">
                <div class="form-group">
                    <label>Username or Email <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" name="username" id="username" placeholder="Username or email address" autocomplete="username" autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password">
                        <i class="fas fa-eye toggle-password" id="toggle-login-pwd"></i>
                    </div>
                </div>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <button class="auth-btn" type="submit" id="login-submit-btn">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <?php if (getenv('PASSWORD_RECOVERY_ENABLED') === 'true'): ?>
                <p class="mt-3 text-center"><a href="/tenant/?url=auth/forgot_password">Forgot your password?</a></p>
            <?php endif; ?>

            <div class="auth-footer" style="margin-top: 24px; text-align: center; font-size: 0.9rem; color: var(--dash-text-muted);">
                Don't have an account? 
                <a href="/tenant/?url=auth/register" style="font-weight: 700; color: var(--dash-primary); text-decoration: none;">Register here</a>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    const $form = $('#login-form');
    const $submitBtn = $('#login-submit-btn');
    const originalBtnHtml = $submitBtn.html();

    // Trigger toast if server passed an error on initial render
    <?php if (!empty($error)): ?>
        ToastStack.danger(<?= json_encode($error) ?>, 'Sign In Failed');
    <?php endif; ?>

    $form.on('submit', function(e) {
        e.preventDefault();
        
        const username = $('#username').val().trim();
        const password = $('#password').val();
        
        if (!username || !password) {
            ToastStack.warning('Please enter both your username/email and password.', 'Required Fields');
            if (!username) $('#username').focus();
            else $('#password').focus();
            return;
        }

        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Authenticating...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // Distinct role titles and message
                    let toastTitle = 'Welcome Back!';
                    if (response.role === 'admin') {
                        toastTitle = 'Administrator Verified';
                    } else if (response.role === 'owner') {
                        toastTitle = 'Property Owner Verified';
                    } else if (response.role === 'tenant') {
                        toastTitle = 'Tenant Verified';
                    } else if (response.role_label) {
                        toastTitle = response.role_label + ' Verified';
                    }

                    ToastStack.create({
                        type: 'success',
                        title: toastTitle,
                        message: response.message || 'Authenticated successfully! Redirecting...',
                        duration: 3000
                    });

                    $submitBtn.html('<i class="fas fa-check"></i> Verified');

                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1100);
                } else {
                    ToastStack.danger(response.message || 'Invalid username/email or password.', 'Sign In Failed');
                    $submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            },
            error: function(xhr) {
                let msg = 'Unable to connect to the server. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                ToastStack.danger(msg, 'Server Error');
                $submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    // Toggle Password Visibility
    $('#toggle-login-pwd').on('click', function() {
        const $input = $('#password');
        const isPwd = $input.attr('type') === 'password';
        $input.attr('type', isPwd ? 'text' : 'password');
        $(this).toggleClass('fa-eye fa-eye-slash');
    });

    // Subtle fade-in
    $('.auth-card').hide().fadeIn(500);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
