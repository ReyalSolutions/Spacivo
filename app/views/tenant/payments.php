<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="dash-container">
    <div class="welcome-header">
        <h1><i class="fas fa-receipt" style="color: var(--dash-primary); margin-right: 12px;"></i>Payment Ledger</h1>
        <p>Review your complete transaction history and payment details.</p>
    </div>

    <!-- Filter Section -->
    <div class="premium-stat-card" style="margin-top: 24px; padding: 24px;">
        <form action="/tenant/" method="GET" style="display: flex; gap: 16px; width: 100%; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="url" value="tenant/payments">
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                    <i class="fas fa-building" style="margin-right: 4px; color: var(--dash-primary);"></i> Boarding House
                </label>
                <select name="bhouse_id" style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--dash-border); background: #f8fafc; font-family: inherit; font-size: 0.95rem; color: var(--dash-text-main); outline: none;">
                    <option value="">All Boarding Houses</option>
                    <?php if (!empty($uniqueHouses)): ?>
                        <?php foreach ($uniqueHouses as $id => $name): ?>
                            <option value="<?= $id ?>" <?= $filterHouseId === $id ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                    <i class="fas fa-calendar-alt" style="margin-right: 4px; color: var(--dash-primary);"></i> Month
                </label>
                <input type="month" name="month" value="<?= htmlspecialchars($filterMonth ?? '', ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--dash-border); background: #f8fafc; font-family: inherit; font-size: 0.95rem; color: var(--dash-text-main); outline: none;">
            </div>
            <div style="display: flex; gap: 10px; flex-shrink: 0; flex-wrap: wrap; width: 100%; max-width: 260px;">
                <button type="submit" style="flex: 1; padding: 12px 20px; background: var(--dash-primary); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; font-size: 0.9rem; transition: all 0.2s;">
                    <i class="fas fa-filter" style="margin-right: 6px;"></i>Apply
                </button>
                <?php if ($filterHouseId || $filterMonth): ?>
                    <a href="/tenant/?url=tenant/payments" style="flex: 1; padding: 12px 20px; background: #f1f5f9; color: var(--dash-text-muted); border: 1px solid var(--dash-border); border-radius: 12px; font-weight: 700; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                        <i class="fas fa-xmark" style="margin-right: 4px;"></i>Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Transaction List -->
    <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
        <?php if (empty($payments)): ?>
            <div class="premium-stat-card" style="padding: 48px; text-align: center; color: var(--dash-text-muted);">
                <i class="fa-solid fa-receipt" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.3; display: block;"></i>
                <p style="font-weight: 700; font-size: 1.1rem; margin: 0 0 4px;">No payments found</p>
                <p style="font-size: 0.9rem; margin: 0;">Try adjusting your filters.</p>
            </div>
        <?php else: ?>
            <?php foreach ($payments as $pay): ?>
                <?php
                    $isPaid = $pay['status'] === 'paid';
                    $iconClass = $isPaid ? 'fa-check' : 'fa-clock';
                    $iconBg = $isPaid ? 'background: #ecfdf5; color: #10b981;' : 'background: #fff7ed; color: #f97316;';
                    $amtColor = $isPaid ? '#10b981' : 'var(--dash-text-main)';
                    $statusBg = $isPaid
                        ? 'background: #ecfdf5; color: #10b981; border: 1px solid #bbf7d0;'
                        : 'background: #fff7ed; color: #f97316; border: 1px solid #fed7aa;';
                    $statusLabel = $isPaid ? 'Paid' : ucfirst($pay['status']);
                ?>
                <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: transform 0.2s, box-shadow 0.2s;">

                    <!-- Top: Icon + House Name + Status Badge -->
                    <div style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
                        <div style="width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; <?= $iconBg ?>">
                            <i class="fas <?= $iconClass ?>"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                                <span style="font-size: 1rem; font-weight: 800; color: var(--dash-text-main); line-height: 1.4; word-break: break-word;">
                                    <?= htmlspecialchars($pay['boarding_house_name'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <span style="font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; flex-shrink: 0; background: #f1f5f9; color: var(--dash-text-muted); border: 1px solid var(--dash-border);">
                                        <?= ucfirst($pay['payment_type'] ?? 'Rent') ?>
                                    </span>
                                    <span style="font-size: 0.65rem; font-weight: 800; padding: 4px 104px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; flex-shrink: 0; padding: 4px 10px; <?= $statusBg ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </div>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="font-size: 0.9rem; color: var(--dash-text-main); font-weight: 700; display: block; margin-bottom: 2px;">
                                    <?= htmlspecialchars($pay['description'] ?: 'Monthly Rental Payment', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <span style="font-size: 0.82rem; color: var(--dash-text-muted); font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-credit-card" style="color: var(--dash-primary); font-size: 0.8rem;"></i>
                                <?= htmlspecialchars($pay['payment_method'] ?? 'Manual', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 2px dashed #f1f5f9; margin: 0 0 16px;">

                    <!-- Bottom: Date + Amount -->
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <span style="font-size: 0.85rem; color: var(--dash-text-muted); font-weight: 600; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-calendar-alt" style="color: #94a3b8;"></i>
                            <?= date('M d, Y · h:i A', strtotime($pay['created_at'])) ?>
                        </span>
                        <span style="font-weight: 900; font-size: 1.3rem; color: <?= $amtColor ?>;">
                            &#8369;<?= number_format((float)$pay['amount'], 2) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<?php 
    $footerVariant = 'desktop-only';
    require __DIR__ . '/../layouts/landing_footer.php'; 
?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
