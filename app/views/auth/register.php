<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container full-width">
    <div class="auth-hero">
        <div class="auth-hero-content">
            <div class="brand-logo">
                <i class="fas fa-house-chimney-window"></i>
                <span>StayHub</span>
            </div>
            <h1>Find Your <br>Perfect Space.</h1>
            <p>Join thousands of others finding and listing the best boarding houses in the city. Secure, easy, and reliable.</p>
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
            <h2>Create Account</h2>
            <p class="subtitle">Join the StayHub community today. It only takes a minute.</p>

            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/tenant/?url=auth/register" id="reg-form">
                <div class="grid">
                    <div class="form-group">
                        <label>First Name <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-circle field-icon"></i>
                            <input type="text" name="first_name" id="first_name" placeholder="John">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-circle field-icon"></i>
                            <input type="text" name="last_name" id="last_name" placeholder="Doe">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Middle Name (Optional)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" name="middle_name" id="middle_name" placeholder="Quincy">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope field-icon"></i>
                        <input type="email" name="email" id="email" placeholder="john@example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>Phone Number <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone field-icon"></i>
                        <input type="tel" name="phone" id="phone" placeholder="0912 345 6789">
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label>Who are you registering as? <span class="required">*</span></label>
                    <div class="role-selection">
                        <label class="role-option">
                            <input type="radio" name="role_id" value="3" checked>
                            <div class="role-card">
                                <i class="fas fa-user-tag"></i>
                                <span>Tenant</span>
                                <p>Looking for a place</p>
                            </div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role_id" value="2">
                            <div class="role-card">
                                <i class="fas fa-house-user"></i>
                                <span>Owner</span>
                                <p>Listing my properties</p>
                            </div>
                        </label>
                    </div>
                </div>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <button class="auth-btn" type="submit">
                    <span>Continue</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? 
                <a href="/tenant/?url=auth/login">Log in here</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const $form = $('#reg-form');
    
    $form.on('submit', function(e) {
        e.preventDefault();
        
        const requiredFields = [
            { id: 'first_name', name: 'First Name' },
            { id: 'last_name', name: 'Last Name' },
            { id: 'email', name: 'Email Address' },
            { id: 'phone', name: 'Phone Number' }
        ];

        let missing = [];
        requiredFields.forEach(field => {
            const $input = $('#' + field.id);
            if (!$input.val().trim()) {
                missing.push(field.name);
                $input.closest('.form-group').addClass('has-error');
            } else {
                $input.closest('.form-group').removeClass('has-error');
            }
        });

        if (missing.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields',
                text: 'Please fill out the following fields: ' + missing.join(', '),
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        // AJAX Submission
        const formData = $form.serialize();
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Error',
                        text: response.message || 'An unexpected error occurred.',
                        confirmButtonColor: '#2563eb'
                    });
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'Unable to process registration. Please try again later.',
                    confirmButtonColor: '#2563eb'
                });
                $submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Clear error on input
    $form.on('input', 'input', function() {
        $(this).closest('.form-group').removeClass('has-error');
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
