<footer class="py-4 px-4 border-top mt-auto bg-white">
    <div class="container-fluid">
        <div class="d-flex flex-row justify-content-between align-items-center gap-2 flex-wrap" style="font-size: 0.8rem;">
            <div>
                <p class="mb-0 text-muted">&copy; <?= date('Y') ?> <strong><?= htmlspecialchars($siteName ?? 'StayHub') ?></strong>. All rights reserved.</p>
            </div>
            <div class="d-flex gap-3 text-muted">
                <span>Version 2.0 (Monolith)</span>
                <span>&bull;</span>
                <a href="/tenant/" target="_blank" class="text-muted text-decoration-none">Main Site</a>
            </div>
        </div>
    </div>
</footer>
