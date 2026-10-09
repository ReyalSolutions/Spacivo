<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="card">
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <h1>Admin Panel (MVP)</h1>
        <p style="color: rgba(233,238,248,0.7);">
            User management, plan management, verification, analytics, and disputes will be implemented next.
        </p>

        <div class="card" style="background: rgba(255,255,255,0.03);">
            <h2>Next Milestones</h2>
            <ul style="color: rgba(233,238,248,0.85); margin-top: 6px;">
                <li>Database schema + seed data</li>
                <li>Owner onboarding (subscribe to plans)</li>
                <li>Admin verification of boarding houses</li>
            </ul>
        </div>

    <?php elseif ($_SESSION['role'] === 'owner'): ?>
        <h1>Owner Dashboard</h1>

        <div class="grid" style="margin-top: 12px;">
            <div class="card">
                <h3>Pending Approvals</h3>
                <div style="font-size: 28px; font-weight: 700;"><?= (int)($pendingCount ?? 0) ?></div>
            </div>
            <div class="card">
                <h3>Approved Bookings</h3>
                <div style="font-size: 28px; font-weight: 700;"><?= (int)($approvedCount ?? 0) ?></div>
            </div>
        </div>

        <div class="card" style="margin-top: 14px;">
            <h3>Total Earnings (MVP)</h3>
            <div style="font-size: 28px; font-weight: 700;">PHP <?= htmlspecialchars((string)($earnings ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
        </div>

        <div style="margin-top: 14px;">
            <a class="btn primary" href="/tenant/?url=owner/bookings">Review bookings</a>
            <a class="btn" href="/tenant/?url=owner/houses">Manage houses (MVP placeholder)</a>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/header.php'; ?>
