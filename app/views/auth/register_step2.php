<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container full-width">
    <div class="auth-hero">
        <div class="auth-hero-content">
            <div class="brand-logo">
                <i class="fas fa-house-chimney-window"></i>
                <span>StayHub</span>
            </div>
            <h1>Secure Your <br>Account.</h1>
            <p>Create a strong password to protect your account and personal information. Security is our top priority.</p>
        </div>
    </div>
    
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="mobile-brand">
                <i class="fas fa-house-chimney-window"></i>
                <span>StayHub</span>
            </div>
            <h2>Set Your Password</h2>
            <p class="subtitle">Last step! Create a secure password for your new StayHub account.</p>

            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/tenant/?url=auth/register_step2" id="pwd-form">
                <div class="form-group">
                    <label>Username <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user-shield field-icon"></i>
                        <input type="text" name="username" id="username" placeholder="johndoe123" autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="new-password">
                        <i class="fas fa-eye toggle-password" id="toggle-pwd"></i>
                    </div>
                    
                    <div class="strength-meter">
                        <div class="meter-bar"><div id="bar-1" class="bar"></div></div>
                        <div class="meter-bar"><div id="bar-2" class="bar"></div></div>
                        <div class="meter-bar"><div id="bar-3" class="bar"></div></div>
                        <div class="meter-bar"><div id="bar-4" class="bar"></div></div>
                    </div>
                    <p class="strength-text" id="strength-msg">Password strength</p>
                    
                    <ul class="pwd-requirements">
                        <li id="req-length"><i class="fas fa-circle"></i> Min. 8 characters</li>
                        <li id="req-upper"><i class="fas fa-circle"></i> At least 1 uppercase letter</li>
                        <li id="req-number"><i class="fas fa-circle"></i> At least 1 number</li>
                        <li id="req-special"><i class="fas fa-circle"></i> At least 1 special character</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-check-double field-icon"></i>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" autocomplete="new-password">
                        <i class="fas fa-eye toggle-password" id="toggle-confirm"></i>
                    </div>
                </div>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <button class="auth-btn" type="submit" id="submit-btn" disabled>
                    <span>Complete Registration</span>
                    <i class="fas fa-check"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const $pwdInput = $('#password');
    const $confirmInput = $('#confirm_password');
    const $usernameInput = $('#username');
    const $submitBtn = $('#submit-btn');
    const $strengthMsg = $('#strength-msg');
    const $form = $('#pwd-form');
    
    const reqs = {
        length: $('#req-length'),
        upper: $('#req-upper'),
        number: $('#req-number'),
        special: $('#req-special')
    };

    const bars = [
        $('#bar-1'),
        $('#bar-2'),
        $('#bar-3'),
        $('#bar-4')
    ];

    function updateReq($el, valid) {
        const $icon = $el.find('i');
        if (valid) {
            $el.addClass('valid');
            $icon.attr('class', 'fas fa-check-circle');
        } else {
            $el.removeClass('valid');
            $icon.attr('class', 'fas fa-circle');
        }
    }

    $pwdInput.on('input', function() {
        const val = $(this).val();
        let score = 0;

        const hasLength = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSpecial = /[^A-Za-z0-9]/.test(val);

        updateReq(reqs.length, hasLength);
        updateReq(reqs.upper, hasUpper);
        updateReq(reqs.number, hasNumber);
        updateReq(reqs.special, hasSpecial);

        if (hasLength) score++;
        if (hasUpper) score++;
        if (hasNumber) score++;
        if (hasSpecial) score++;

        bars.forEach(($bar, i) => {
            $bar.attr('class', 'bar');
            if (i < score) {
                if (score === 1) $bar.addClass('weak');
                else if (score === 2) $bar.addClass('fair');
                else if (score === 3) $bar.addClass('good');
                else if (score === 4) $bar.addClass('strong');
            }
        });

        const texts = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        $strengthMsg.text(texts[score] ? `Strength: ${texts[score]}` : 'Password strength');
        
        validateForm();
    });

    $confirmInput.on('input', validateForm);
    $usernameInput.on('input', validateForm);

    function validateForm() {
        const u = $usernameInput.val().trim();
        const p1 = $pwdInput.val();
        const p2 = $confirmInput.val();
        const isValid = u.length > 0 && p1.length >= 8 && /[A-Z]/.test(p1) && /[0-9]/.test(p1) && /[^A-Za-z0-9]/.test(p1) && p1 === p2;
        $submitBtn.prop('disabled', !isValid);
    }

    $form.on('submit', function(e) {
        e.preventDefault();
        
        const originalBtnText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Finalizing...');

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
                        title: 'Registration Complete!',
                        text: 'Your account has been successfully created.',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Feedback.fire({
                        icon: 'error',
                        title: 'Setup Error',
                        text: response.message || 'An unexpected error occurred.',
                        confirmButtonColor: '#2563eb'
                    });
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function() {
                Feedback.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'Unable to finalize registration. Please try again.',
                    confirmButtonColor: '#2563eb'
                });
                $submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    function setupToggle(btnId, inputId) {
        const $btn = $('#' + btnId);
        const $input = $('#' + inputId);
        $btn.on('click', () => {
            const type = $input.attr('type') === 'password' ? 'text' : 'password';
            $input.attr('type', type);
            $btn.toggleClass('fa-eye fa-eye-slash');
        });
    }

    setupToggle('toggle-pwd', 'password');
    setupToggle('toggle-confirm', 'confirm_password');
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
