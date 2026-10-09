<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="card">
    <h1>Owner Dashboard</h1>

    <div class="grid" style="margin-top: 12px;">
        <div class="card">
            <h3>Pending Approvals</h3>
            <div style="font-size: 28px; font-weight: 700;"><?= (int)$pendingCount ?></div>
        </div>
        <div class="card">
            <h3>Active Tenants</h3>
            <div style="font-size: 28px; font-weight: 700;"><?= (int)$approvedCount ?></div>
        </div>
    </div>

    <div class="card" style="margin-top: 14px;">
        <h3>Total Earnings (MVP)</h3>
        <div style="font-size: 28px; font-weight: 700;">PHP <?= htmlspecialchars((string)$earnings, ENT_QUOTES, 'UTF-8') ?></div>
    </div>

    <div style="margin-top: 14px;">
        <a class="btn primary" href="/tenant/?url=owner/bookings">Manage Tenants</a>
        <a class="btn" href="/tenant/?url=owner/houses">Manage houses (MVP placeholder)</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

