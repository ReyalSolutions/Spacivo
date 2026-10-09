<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="dash-container">
    <div class="welcome-header">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 8px;">
            <a href="/tenant/?url=tenant/dashboard" style="width: 40px; height: 40px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--dash-text-main); text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: all 0.2s;">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h1>Upcoming Payments</h1>
        </div>
        <p>Detailed breakdown of recurring payments for all your active rentals.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 24px; margin-top: 24px;">
        <?php if (empty($dues)): ?>
            <div style="padding: 60px; text-align: center; background: white; border-radius: 24px; border: 2px dashed #e2e8f0;">
                <i class="fas fa-calendar-xmark" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 16px;"></i>
                <h3 style="color: var(--dash-text-main); margin-bottom: 8px;">No Recurring Payments</h3>
                <p style="color: var(--dash-text-muted);">You don't have any active rentals with upcoming dues at the moment.</p>
            </div>
        <?php else: ?>
            <?php 
                $totalMonthlyRent = 0;
                foreach ($dues as $dueItem) {
                    $totalMonthlyRent += (float)$dueItem['amount'];
                }
            ?>
            
            <?php if (count($dues) > 1): ?>
                <div style="background: linear-gradient(135deg, #2563eb, #1e40af); padding: 24px; border-radius: 20px; color: white; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 25px rgba(37,99,235,0.2);">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.9; margin-bottom: 4px;">Total Monthly Rent (All Rooms)</span>
                            <span style="font-weight: 800; font-size: 1.8rem; letter-spacing: -0.02em;">₱<?= number_format($totalMonthlyRent, 2) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="section-title" style="margin-bottom: 0;">
                <i class="fas fa-list-ul" style="color: var(--dash-primary);"></i>
                Active Room Dues
            </div>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($dues as $due): ?>
                    <div class="premium-stat-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; border: 1px solid #f1f5f9; transition: transform 0.2s; background: white;">
                        <!-- Card Body -->
                        <div style="padding: 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 20px; flex: 1; min-width: 250px;">
                                <div style="width: 56px; height: 56px; background: #f0f9ff; color: #0ea5e9; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); border: 1px solid #e2e8f0;">
                                    <i class="fas fa-house-chimney-window"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--dash-text-main); letter-spacing: -0.01em;">
                                        <?= htmlspecialchars($due['boarding_house_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </h3>
                                    <div style="display: flex; gap: 12px; margin-top: 6px; flex-wrap: wrap;">
                                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--dash-text-muted); display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-door-open" style="color: var(--dash-primary); opacity: 0.8;"></i> 
                                            Room: <?= htmlspecialchars($due['room_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span style="font-size: 0.85rem; font-weight: 700; color: #f59e0b; background: #fffbeb; padding: 2px 10px; border-radius: 20px; display: flex; align-items: center; gap: 6px;">
                                            <i class="far fa-calendar-alt"></i> Next Due: <?= $due['due_date'] ?>
                                        </span>
                                        <?php if ($due['is_paid_ahead']): ?>
                                            <span style="font-size: 0.85rem; font-weight: 700; color: #10b981; background: #ecfdf5; padding: 2px 10px; border-radius: 20px; display: flex; align-items: center; gap: 6px;">
                                                <i class="fas fa-circle-check"></i> Paid Ahead
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div style="text-align: right; min-width: 140px;">
                                <span style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Monthly Rent</span>
                                <span style="font-weight: 900; color: var(--dash-primary); font-size: 1.6rem; letter-spacing: -0.02em;">
                                    ₱<?= number_format((float)$due['amount'], 2) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer Actions -->
                        <div style="background: #f8fafc; padding: 16px 24px; border-top: 1px solid #f1f5f9; display: flex; gap: 16px; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                            <form action="/tenant/?url=payment/initiate" method="POST" style="display: flex; align-items: center; gap: 8px; flex: 1; max-width: 320px;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="booking_id" value="<?= (int)$due['booking_id'] ?>">
                                <input type="hidden" name="payment_type" value="advance">
                                <div style="display: flex; align-items: center; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0 12px; flex: 1;">
                                    <span style="font-size: 0.75rem; font-weight: 800; color: var(--dash-text-muted); white-space: nowrap;">Months:</span>
                                    <select name="num_months" style="border: none; padding: 10px 8px; font-weight: 700; font-family: inherit; color: var(--dash-text-main); background: transparent; outline: none; width: 60px;">
                                        <?php for($i=1; $i<=12; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <button type="submit" class="btn-resile secondary" style="border: none; padding: 10px 14px; font-weight: 800; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; background: transparent; color: var(--dash-primary); transition: all 0.2s; font-family: inherit; border-left: 1px solid #f1f5f9;">
                                        <i class="fas fa-calendar-plus"></i> Pay Advance
                                    </button>
                                </div>
                            </form>
                            <form action="/tenant/?url=payment/initiate" method="POST" style="flex: 0 0 200px;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="booking_id" value="<?= (int)$due['booking_id'] ?>">
                                <input type="hidden" name="payment_type" value="rent">
                                <button type="submit" class="btn-resile <?= $due['is_paid_ahead'] ? 'disabled' : 'primary' ?>" 
                                    <?= $due['is_paid_ahead'] ? 'disabled' : '' ?>
                                    style="width: 100%; padding: 12px; border-radius: 12px; font-weight: 800; border: none; cursor: pointer; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; justify-content: center; transition: all 0.2s; font-family: inherit; 
                                    <?= $due['is_paid_ahead'] ? 'background: #e2e8f0; color: #94a3b8; cursor: not-allowed;' : 'background: var(--dash-primary); color: white; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);' ?>">
                                    <?php if ($due['is_paid_ahead']): ?>
                                        <i class="fas fa-check"></i> Paid Ahead
                                    <?php else: ?>
                                        Pay Next Due <i class="fas fa-arrow-right"></i>
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 20px; padding: 20px; display: flex; gap: 16px; align-items: flex-start;">
                <div style="width: 40px; height: 40px; background: #fef3c7; color: #d97706; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                    <i class="fas fa-circle-exclamation"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 4px; color: #92400e; font-size: 1rem; font-weight: 800;">Recurring Payment Reminder</h4>
                    <p style="margin: 0; color: #b45309; font-size: 0.9rem; line-height: 1.5;">Recurring payments are due every month on the anniversary of your move-in date. Please ensure timely payments to avoid late fees or service interruptions.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
