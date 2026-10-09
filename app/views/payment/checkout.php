<?php 
require __DIR__ . '/../layouts/header.php'; 
include __DIR__ . '/../components/payment_modal.php';
?>

<div class="dash-container">
    <div class="welcome-header">
        <h1>Complete Your Payment</h1>
        <p>Review your booking details below and proceed with the secure financial orchestration.</p>
    </div>

    <div class="page-layout">
        <!-- Booking Summary -->
        <div>
            <div class="section-title">
                <i class="fas fa-file-invoice-dollar" style="color: var(--dash-primary);"></i>
                Booking Summary
            </div>
            
            <div class="residence-card">
                <div class="residence-cover" style="height: 120px;">
                    <div class="residence-avatar" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <div class="residence-body" style="padding-top: 10px; margin-top: 50px;">
                    <h3 class="residence-title" style="font-size: 1.4rem;">
                        <?= htmlspecialchars((string)$details['boarding_house_name'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>
                    <p class="residence-address" style="margin-bottom: 20px;">
                        <i class="fas fa-door-open" style="color: var(--dash-primary);"></i>
                        <?= htmlspecialchars((string)$details['room_name'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <?php 
                    $taxEnabled = ($paymentSettings['payment_tax_enabled'] ?? '0') === '1';
                    $taxPercent = (float)($paymentSettings['payment_tax_percent'] ?? 0);
                    $subtotal = (float)($payment['amount'] ?? $details['total_amount'] ?? 0);
                    $taxAmount = $taxEnabled ? ($subtotal * ($taxPercent / 100)) : 0;
                    $grandTotal = $subtotal + $taxAmount;
                    ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #f1f5f9;">
                        <div>
                            <span style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Lease Duration</span>
                            <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.95rem;">
                                <?= date('M d, Y', strtotime($payment['start_date'] ?? $details['start_date'])) ?>
                                — 
                                <?php 
                                $endDateVal = $payment['end_date'] ?? $details['end_date'] ?? null;
                                if (!empty($endDateVal) && $endDateVal !== '0000-00-00'): 
                                    echo date('M d, Y', strtotime((string)$endDateVal));
                                else: 
                                    echo '<span style="color: var(--dash-text-muted); font-style: italic; font-weight: 500;">Not yet decided</span>';
                                endif; 
                                ?>
                            </span>
                        </div>
                        <div style="text-align: right;">
                            <span style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                                <?= $taxEnabled ? 'Subtotal' : 'Total Deposit' ?>
                            </span>
                            <span style="font-size: 1.25rem; font-weight: 900; color: var(--dash-primary);">
                                ₱<?= number_format((float)$subtotal, 2) ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($taxEnabled): ?>
                    <div style="margin-bottom: 24px; padding: 0 20px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.85rem; color: var(--dash-text-muted); font-weight: 600;">Service Fee (<?= $taxPercent ?>%)</span>
                            <span style="font-size: 0.85rem; color: var(--dash-text-main); font-weight: 700;">+ ₱<?= number_format($taxAmount, 2) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.9rem; color: var(--dash-text-main); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Grand Total</span>
                            <span style="font-size: 1.4rem; color: var(--dash-primary); font-weight: 900;">₱<?= number_format($grandTotal, 2) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 0 10px;">
                        <div style="font-size: 0.85rem; color: var(--dash-text-muted); display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-fingerprint"></i>
                            Booking ID: #<?= htmlspecialchars((string)$details['booking_id'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div style="text-align: right;">
                            <span style="display: block; font-size: 0.65rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Settlement Amount</span>
                            <span style="font-weight: 700; color: var(--dash-text-main); font-size: 0.9rem;">
                                ₱<?= number_format((float)$grandTotal, 2) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method -->
        <div>
            <div class="section-title">
                <i class="fas fa-credit-card" style="color: #10b981;"></i>
                Secure Gateway
            </div>

            <div class="premium-stat-card" style="padding: 30px;">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                    <div style="width: 50px; height: 50px; background: #e0f2fe; color: #0ea5e9; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fas fa-shield-check"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--dash-text-main);">PayMongo Protocol</h4>
                        <p style="margin: 2px 0 0; font-size: 0.85rem; color: var(--dash-text-muted);">Verified PCI-DSS Secure Clearance</p>
                    </div>
                </div>

                <div style="background: rgba(16, 185, 129, 0.05); border: 1px dashed #10b981; border-radius: 14px; padding: 16px; margin-bottom: 24px;">
                    <p style="margin: 0; font-size: 0.85rem; color: var(--dash-text-main); line-height: 1.5;">
                        <i class="fas fa-lock" style="color: #10b981; margin-right: 6px;"></i>
                        <strong>Secure Encryption:</strong> Your payment is processed through a bank-grade encrypted layer using verified PayMongo APIs.
                    </p>
                </div>

                <button class="btn primary" id="trigger-paymongo" 
                        data-payment-id="<?= (int)$payment['id'] ?>" 
                        data-amount="<?= (float)($payment['amount'] ?? 0) ?>"
                        data-desc="<?= htmlspecialchars((string)($payment['description'] ?? 'Rent Payment'), ENT_QUOTES, 'UTF-8') ?>"
                        style="width: 100%; padding: 18px; height: 60px; background: var(--dash-primary); color: white; border: none; border-radius: 16px; font-weight: 800; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 4px 15px rgba(109, 94, 252, 0.3); transition: all 0.2s;">
                    <i class="fas fa-credit-card"></i> PROCEED TO SECURE PAYMENT
                </button>
                
                <p style="text-align: center; margin-top: 16px; font-size: 0.8rem; color: var(--dash-text-muted);">
                    <i class="fas fa-lock" style="font-size: 0.7rem;"></i> SSL Secure Checkout · ₱<?= number_format((float)($payment['amount'] ?? 0), 2) ?>
                </p>
            </div>

            <div class="support-card" style="margin-top: 24px;">
                <h3><i class="fas fa-headset" style="color: var(--dash-primary);"></i> Need help?</h3>
                <p>If you encounter any issues during payment, please contact our 24/7 support team.</p>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('#trigger-paymongo').on('click', function(){
            const btn = $(this);
            const data = {
                paymentId: btn.data('payment-id'),
                amount: btn.data('amount'),
                planName: btn.data('desc'),
                cycle: 'One-time',
                initiateUrl: '/tenant/?url=payment/initiate_paymongo'
            };
            openPaymentSelection(data);
        });
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

