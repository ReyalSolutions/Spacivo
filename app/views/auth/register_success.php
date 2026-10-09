<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container full-width">
    <div class="auth-hero">
        <div class="auth-hero-content">
            <div class="brand-logo">
                <i class="fas fa-house-chimney-window"></i>
                <span>StayHub</span>
            </div>
            <h1>One Step Away.</h1>
            <p>Your basic information has been saved. Next, we'll set up your secure password to complete your account.</p>
        </div>
    </div>
    
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="success-icon" style="font-size: 3rem; color: var(--ok); margin-bottom: 1.5rem; text-align: center;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 style="text-align: center;">Registration Started!</h2>
            <p class="subtitle" style="text-align: center; margin-bottom: 2rem;">
                Welcome to StayHub. We've collected your basic details. 
            </p>

            <div class="info-box" style="background: var(--soft); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
                <p style="margin: 0; color: var(--text); font-size: 0.95rem; line-height: 1.6;">
                    <strong>What's next?</strong><br>
                    Normally, you would set your password now. We are currently refining the second step of our registration process.
                </p>
            </div>

            <a href="/tenant/?url=auth/login" class="auth-btn" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                <span>Go to Login</span>
                <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
