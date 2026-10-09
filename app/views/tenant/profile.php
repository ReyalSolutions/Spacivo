<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php
    $fullName = trim(
        htmlspecialchars($user['first_name'] ?? '', ENT_QUOTES, 'UTF-8') . ' ' .
        htmlspecialchars($user['middle_name'] ?? '', ENT_QUOTES, 'UTF-8') . ' ' .
        htmlspecialchars($user['last_name'] ?? '', ENT_QUOTES, 'UTF-8')
    );
    $initials = strtoupper(
        substr($user['first_name'] ?? 'U', 0, 1) .
        substr($user['last_name'] ?? '', 0, 1)
    );
    $memberSince = isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'N/A';
?>

<div class="dash-container">
    <div class="welcome-header">
        <h1>My Profile</h1>
        <p>Manage your personal information and account details.</p>
    </div>

    <!-- Profile Hero Card -->
    <div class="residence-card" style="margin-top: 24px; margin-bottom: 24px;">
        <div class="residence-cover">
            <!-- Avatar -->
            <div style="position: absolute; bottom: -44px; left: 0; right: 0; display: flex; justify-content: center;">
                <div style="width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, var(--dash-primary), var(--dash-accent)); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; color: white; border: 5px solid white; box-shadow: 0 10px 25px -5px rgba(37,99,235,0.4); z-index: 2;">
                    <?= $initials ?>
                </div>
            </div>
        </div>
        <div class="residence-body" style="padding-top: 52px; text-align: center;">
            <h3 class="residence-title" style="margin-bottom: 4px;"><?= $fullName ?></h3>
            <p style="color: var(--dash-text-muted); font-size: 0.95rem; margin: 0 0 8px;">
                @<?= htmlspecialchars($user['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                <span class="residence-tag verified" style="font-size: 0.8rem;">
                    <i class="fas fa-shield-halved"></i> Verified Tenant
                </span>
                <span class="residence-tag" style="font-size: 0.8rem;">
                    <i class="fas fa-calendar"></i> Member since <?= $memberSince ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="stats-grid">

        <!-- Personal Information -->
        <div class="premium-stat-card" style="padding: 28px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #eff6ff, #dbeafe); display: flex; align-items: center; justify-content: center; color: var(--dash-primary); font-size: 1rem; flex-shrink: 0;">
                    <i class="fas fa-user"></i>
                </div>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--dash-text-main);">Personal Information</h3>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;">
                    <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Full Name</span>
                    <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.95rem; text-align: right;"><?= $fullName ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;">
                    <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Username</span>
                    <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.95rem; text-align: right;">@<?= htmlspecialchars($user['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;">
                    <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Email</span>
                    <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.95rem; word-break: break-all; text-align: right;"><?= htmlspecialchars($user['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                    <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Phone</span>
                    <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.95rem; text-align: right;"><?= htmlspecialchars($user['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>

        <!-- Current Residence Summary -->
        <div class="premium-stat-card" style="padding: 28px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #f0fdf4, #dcfce7); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 1rem; flex-shrink: 0;">
                    <i class="fas fa-house-user"></i>
                </div>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--dash-text-main);">Current Residence</h3>
            </div>

            <?php if ($activeRental): ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Boarding House</span>
                        <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.95rem; text-align: right; word-break: break-word;"><?= htmlspecialchars($activeRental['boarding_house_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Room</span>
                        <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.95rem; text-align: right;"><?= htmlspecialchars($activeRental['room_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Lease Start</span>
                        <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.95rem; text-align: right;"><?= date('M d, Y', strtotime($activeRental['start_date'])) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Monthly Rate</span>
                        <span style="font-weight: 900; color: var(--dash-primary); font-size: 1.1rem; text-align: right;">&#8369;<?= number_format((float)$activeRental['room_price'], 2) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 24px 0; color: var(--dash-text-muted);">
                    <i class="fas fa-house-circle-xmark" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 12px;"></i>
                    <p style="font-weight: 600; margin: 0;">No active residence found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="premium-stat-card" style="padding: 28px; margin-top: 0;">
        <h3 style="margin: 0 0 20px; font-size: 1rem; font-weight: 800; color: var(--dash-text-main);">
            <i class="fas fa-bolt" style="color: var(--dash-primary); margin-right: 8px;"></i>Quick Actions
        </h3>
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            <a href="/tenant/?url=tenant/payments" class="btn-res-action btn-res-primary" style="text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-receipt"></i> View Payments
            </a>
            <a href="/tenant/?url=tenant/bookings" class="btn-res-action btn-res-secondary" style="text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-building"></i> My Bhouse
            </a>
            <a href="/tenant/?url=tenant/dashboard" class="btn-res-action btn-res-secondary" style="text-decoration: none; font-size: 0.9rem;">
                <i class="fas fa-house"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
