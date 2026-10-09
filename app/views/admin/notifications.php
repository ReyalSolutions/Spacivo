<?php require __DIR__ . '/../layouts/management_header.php'; ?>
<div class="animate-fade-up text-center py-5">
    <div class="premium-stat-card d-inline-block p-5 shadow-lg border-0 rounded-4" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); max-width: 600px;">
        <i class="fa-solid fa-screwdriver-wrench display-1 text-primary opacity-25 mb-4 d-block"></i>
        <h2 class="fw-bold text-dark mb-3">Feature Under Construction</h2>
        <p class="text-muted mb-4">We are currently architecting the <strong><?= ucfirst(str_replace('.php', '', basename(__FILE__))) ?></strong> module to provide a premium administrative experience.</p>
        <a href="/tenant/?url=admin/index" class="btn btn-primary rounded-pill px-5 shadow-sm">
            <i class="fa-solid fa-arrow-left me-2"></i>Return to Dashboard
        </a>
    </div>
</div>
<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
