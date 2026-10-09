<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card border-0 shadow-lg rounded-5 overflow-hidden">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <div class="display-1 text-primary mb-3">
                            <i class="fa-solid fa-circle-check animate__animated animate__bounceIn"></i>
                        </div>
                        <h2 class="fw-900 mb-0">Subscription Initialized</h2>
                        <p class="lead text-muted">Verification Protocol: <?= htmlspecialchars($status) ?></p>
                    </div>

                    <div class="p-4 bg-light rounded-4 mb-4 text-start border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">TARGET PLAN</span>
                            <span class="fw-bold"><?= htmlspecialchars($planName) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">ORDER ID</span>
                            <span class="fw-bold">SHB-<?= strtoupper(uniqid()) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">STATUS</span>
                            <span class="badge bg-primary rounded-pill px-3">PENDING VERIFICATION</span>
                        </div>
                    </div>

                    <?php if ($status === 'simulated'): ?>
                        <div class="alert alert-info border-0 rounded-4 smaller text-start">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            <strong>Demo Mode Active:</strong> No real-world API keys were detected in the system settings. This transaction has been logged as a simulation for demonstration purposes.
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <a href="/tenant/?url=owner/subscription" class="btn btn-primary rounded-pill py-3 fw-800 shadow-sm">
                            <i class="fa-solid fa-arrow-left me-2"></i>RETURN TO SUBSCRIPTIONS
                        </a>
                        <a href="/tenant/?url=owner/dashboard" class="btn btn-outline-secondary border-0 rounded-pill py-3 fw-800">
                            GO TO DASHBOARD
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>

<style>
.fw-900 { font-weight: 900; }
.fw-800 { font-weight: 800; }
.rounded-5 { border-radius: 2rem !important; }
.animate__bounceIn {
    animation-duration: 1s;
    animation-fill-mode: both;
}
</style>
