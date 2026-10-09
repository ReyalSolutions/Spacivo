<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container full-width admin-portal">
    <div class="auth-hero admin-hero">
        <div class="auth-hero-content">
            <div class="brand-logo" id="admin-portal-logo">
                <i class="fas fa-shield-halved"></i>
                <span>StayHub System</span>
            </div>
            <h1>Portal <br>Access.</h1>
            <p>Dedicated entry for System Administrators and Boarding House Owners. Securely manage the StayHub ecosystem.</p>
        </div>
    </div>
    
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="mobile-brand">
                <i class="fas fa-shield-halved"></i>
                <span>StayHub Portal</span>
            </div>
            <a href="/tenant/?url=auth/login" style="display: inline-flex; align-items: center; gap: 8px; color: var(--dash-text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 700; margin-bottom: 20px; transition: color 0.2s;" onmouseover="this.style.color='var(--dash-primary)'" onmouseout="this.style.color='var(--dash-text-muted)'">
                <i class="fas fa-arrow-left"></i> Back to Public Login
            </a>
            <h2>Administrative Gateway</h2>
            <p class="subtitle">Access the System Management & Partner Console.</p>

            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/tenant/?url=auth/portal_login" id="admin-login-form">
                <div class="form-group">
                    <label>System Username <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user-shield field-icon"></i>
                        <input type="text" name="username" id="username" placeholder="admin_user" autocomplete="username" autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Security Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-key field-icon"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password">
                        <i class="fas fa-eye toggle-password" id="toggle-admin-pwd"></i>
                    </div>
                </div>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <button class="auth-btn portal-btn" type="submit">
                    <span>Verify Credentials</span>
                    <i class="fas fa-fingerprint"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.admin-portal {
    background: #0f172a;
}
.admin-hero {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
}
.admin-hero::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
    opacity: 0.05;
}
.portal-btn {
    background: #475569 !important;
}
.portal-btn:hover {
    background: #334155 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
</style>

<script>
$(function() {
    const $form = $('#admin-login-form');
    const $submitBtn = $('.portal-btn');
    const originalBtnText = $submitBtn.html();

    $form.on('submit', function(e) {
        e.preventDefault();
        
        // Basic jQuery validation
        const u = $('#username').val().trim();
        const p = $('#password').val();

        if (!u || !p) {
            Feedback.fire({
                icon: 'warning',
                title: 'Missing Credentials',
                text: 'Please enter both system username and security password.',
                confirmButtonColor: '#475569',
                background: '#1e293b',
                color: '#f8fafc'
            });
            return;
        }

        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Authorizing...');

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
                    Feedback.fire({
                        icon: 'success',
                        title: 'Access Granted',
                        text: 'Welcome to the management portal.',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        background: '#1e293b',
                        color: '#f8fafc'
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Feedback.fire({
                        icon: 'error',
                        title: 'Authorization Failed',
                        text: response.message || 'Invalid credentials or insufficient permissions.',
                        confirmButtonColor: '#475569',
                        background: '#1e293b',
                        color: '#f8fafc'
                    });
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function() {
                Feedback.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'Unable to connect to the secure server.',
                    confirmButtonColor: '#475569',
                    background: '#1e293b',
                    color: '#f8fafc'
                });
                $submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Toggle Password Visibility with jQuery
    $('#toggle-admin-pwd').on('click', function() {
        const $input = $('#password');
        const isPwd = $input.attr('type') === 'password';
        $input.attr('type', isPwd ? 'text' : 'password');
        $(this).toggleClass('fa-eye fa-eye-slash');
    });

    // Add a subtle jQuery fade-in effect on load
    $('.auth-card').hide().fadeIn(800);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
