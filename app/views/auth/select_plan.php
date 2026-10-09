<?php 
require __DIR__ . '/../layouts/header.php'; 
include __DIR__ . '/../components/payment_modal.php';
?>

<style>
/* Remove inner scrollbar - let the modal be auto height */
body {
    background-color: #f8fafc;
}

.select-plan-container {
    max-width: 1200px;
    margin: 40px auto;
}

/* Dark gradient header strip */
.upgrade-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
    border-radius: 28px;
    padding: 40px 40px 32px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    margin-bottom: 24px;
}

/* Billing toggle */
.billing-toggle-group {
    display: inline-flex;
    background: rgba(255,255,255,0.1);
    border-radius: 100px;
    padding: 4px;
}
.billing-toggle-group button {
    border: none;
    background: transparent;
    color: rgba(255,255,255,0.55);
    font-size: 0.9rem;
    font-weight: 700;
    padding: 10px 28px;
    border-radius: 100px;
    cursor: pointer;
    transition: all 0.25s ease;
    white-space: nowrap;
}
.billing-toggle-group button.active {
    background: #6366f1;
    color: #fff;
    box-shadow: 0 4px 14px rgba(99,102,241,0.45);
}

/* Plan card */
.upgrade-plan-card {
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    height: 100%;
}
.upgrade-plan-card:hover {
    transform: translateY(-5px);
    border-color: #a5b4fc;
    box-shadow: 0 16px 40px rgba(99,102,241,0.12);
}
.upgrade-plan-card.recommended {
    border-color: #6366f1;
    box-shadow: 0 0 0 1px #6366f1, 0 16px 40px rgba(99,102,241,0.18);
    position: relative;
}

.upgrade-plan-name {
    font-size: 0.85rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #64748b;
}
.upgrade-plan-card.recommended .upgrade-plan-name { color: #6366f1; }

.popular-badge {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 4px 12px;
    background: rgba(99,102,241,0.1);
    color: #6366f1;
    border: 1px solid rgba(99,102,241,0.25);
    border-radius: 100px;
}

.price-amount-lg {
    font-size: 2.8rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1;
    letter-spacing: -0.04em;
}
.price-period { font-size: 0.9rem; color: #94a3b8; font-weight: 600; }
.price-original {
    font-size: 0.9rem;
    color: #94a3b8;
    text-decoration: line-through;
    display: none;
}
.annual-badge {
    font-size: 0.75rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 100px;
    background: rgba(16,185,129,0.1);
    color: #059669;
    border: 1px solid rgba(16,185,129,0.25);
    display: none;
}

.limit-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 100px;
    padding: 4px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
}
.upgrade-plan-card.recommended .limit-chip {
    background: rgba(99,102,241,0.07);
    border-color: rgba(99,102,241,0.2);
    color: #4338ca;
}

.upgrade-feature-list { list-style: none; padding: 0; margin: 0; }
.upgrade-feature-list li {
    font-size: 0.9rem;
    color: #64748b;
    padding: 8px 0;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    border-bottom: 1px solid #f8fafc;
}
.upgrade-feature-list li:last-child { border-bottom: none; }
.upgrade-feature-list li i { color: #10b981; font-size: 0.8rem; margin-top: 4px; flex-shrink: 0; }

.plan-cta-btn {
    width: 100%;
    padding: 14px 0;
    border-radius: 14px;
    font-weight: 800;
    font-size: 1rem;
    border: 2px solid #e2e8f0;
    background: #fff;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: auto;
}
.plan-cta-btn:hover {
    border-color: #6366f1;
    color: #6366f1;
    background: rgba(99,102,241,0.05);
}
.upgrade-plan-card.recommended .plan-cta-btn {
    background: #6366f1;
    border-color: #6366f1;
    color: #fff;
    box-shadow: 0 8px 20px rgba(99,102,241,0.35);
}
.upgrade-plan-card.recommended .plan-cta-btn:hover {
    background: #4f46e5;
    border-color: #4f46e5;
}

.savings-global-note {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.7);
    text-align: center;
    margin-top: 14px;
}

.error-msg {
    display: none;
    background: #fee2e2;
    color: #b91c1c;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    font-size: 0.95rem;
    font-weight: 600;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.error-msg i { font-size: 1.25rem; }
</style>

<div class="container select-plan-container px-3 px-md-4">
    <div class="error-msg" id="select-plan-error">
        <i class="fas fa-exclamation-circle"></i>
        <span></span>
    </div>

    <div class="upgrade-header">
        <div class="d-flex flex-column align-items-center justify-content-center">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#6366f1,#a855f7);border-radius:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(99,102,241,0.4);margin-bottom:20px;">
                <i class="fa-solid fa-crown" style="font-size:1.8rem;color:#fff;"></i>
            </div>
            <h2 class="mb-2 fw-900 text-white" style="letter-spacing:-0.02em;">Choose Your Subscription</h2>
            <p class="mb-0 fs-5" style="color:rgba(255,255,255,0.7);">Select a plan to activate your owner account</p>
        </div>

        <!-- Billing toggle -->
        <div class="text-center mt-5">
            <div class="billing-toggle-group">
                <button data-cycle="monthly" class="active" onclick="setUpgradeBilling('monthly')">Monthly</button>
                <button data-cycle="yearly" onclick="setUpgradeBilling('yearly')">
                    Yearly &nbsp;<span style="font-size:0.75rem;font-weight:800;padding:2px 8px;background:rgba(16,185,129,0.2);color:#6ee7b7;border-radius:100px;">Save 20%</span>
                </button>
            </div>
            <div class="savings-global-note">Yearly billing saves up to 20%</div>
        </div>
    </div>

    <!-- Plans Row -->
    <div class="row g-4 justify-content-center">
        <?php 
        $plansList = $plans ?? []; 
        $recIdx = 1; // 2nd plan is recommended
        ?>
        <?php if (!empty($plansList)): ?>
            <?php foreach ($plansList as $i => $plan):
                $features = json_decode($plan['features'] ?? '[]', true);
                $isRec = ($i === $recIdx) || (count($plansList) === 1);
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="upgrade-plan-card d-flex flex-column p-4 pt-5 <?= $isRec ? 'recommended' : '' ?>"
                     data-monthly="<?= (float)$plan['price_monthly'] ?>"
                     data-yearly="<?= (float)$plan['price_yearly'] ?>">

                    <!-- Name + badge -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="upgrade-plan-name"><?= htmlspecialchars($plan['name']) ?></span>
                        <?php if ($isRec): ?><span class="popular-badge">⭐ Popular</span><?php endif; ?>
                    </div>

                    <!-- Price -->
                    <div class="mb-1">
                        <span class="price-original"></span>
                    </div>
                    <div class="d-flex align-items-baseline gap-1 mb-2">
                        <span class="price-amount-lg">₱<?= number_format($plan['price_monthly'], 0) ?></span>
                        <span class="price-period">/mo</span>
                    </div>
                    <div class="mb-4" style="height: 20px;"><span class="annual-badge">Billed annually</span></div>

                    <!-- Limits -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="limit-chip">
                            <i class="fa-solid fa-house-chimney fa-xs opacity-75"></i>
                            <?= $plan['bhouse_limit'] >= 9999 ? 'Unlimited' : $plan['bhouse_limit'] ?> Properties
                        </span>
                        <span class="limit-chip">
                            <i class="fa-solid fa-door-open fa-xs opacity-75"></i>
                            <?= $plan['room_limit'] >= 9999 ? 'Unlimited' : $plan['room_limit'] ?> Rooms
                        </span>
                    </div>

                    <!-- Features -->
                    <ul class="upgrade-feature-list mb-5 flex-grow-1">
                        <?php if (is_array($features)): foreach ($features as $f): ?>
                            <li><i class="fa-solid fa-check"></i><?= htmlspecialchars($f) ?></li>
                        <?php endforeach; endif; ?>
                    </ul>

                    <!-- CTA -->
                    <button type="button" class="plan-cta-btn" onclick="submitPlanSelection(<?= (int)$plan['id'] ?>, this)">
                        <?= $isRec ? 'Proceed to Payment' : 'Select Plan' ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-4 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-warning fa-3x mb-3"></i>
                    <h4>No Plans Available</h4>
                    <p class="text-muted mb-0">Please contact the administrator to configure subscription plans.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function setUpgradeBilling(cycle) {
    document.querySelectorAll('.billing-toggle-group button').forEach(b => b.classList.remove('active'));
    document.querySelector('.billing-toggle-group [data-cycle="' + cycle + '"]').classList.add('active');

    document.querySelectorAll('.upgrade-plan-card').forEach(card => {
        const monthly = parseFloat(card.dataset.monthly);
        const yearly  = parseFloat(card.dataset.yearly);
        const amountEl   = card.querySelector('.price-amount-lg');
        const periodEl   = card.querySelector('.price-period');
        const origEl     = card.querySelector('.price-original');
        const annualEl   = card.querySelector('.annual-badge');

        if (!amountEl) return;

        if (cycle === 'yearly') {
            amountEl.textContent = '₱' + Math.round(yearly).toLocaleString();
            if (periodEl) periodEl.textContent = '/yr';
            if (origEl)   { origEl.textContent = '₱' + Math.round(monthly).toLocaleString() + '/mo'; origEl.style.display = 'inline'; }
            if (annualEl) annualEl.style.display = 'inline-block';
        } else {
            amountEl.textContent = '₱' + Math.round(monthly).toLocaleString();
            if (periodEl) periodEl.textContent = '/mo';
            if (origEl)   origEl.style.display = 'none';
            if (annualEl) annualEl.style.display = 'none';
        }
    });
}

function submitPlanSelection(planId, btnElement) {
    const isYearly = document.querySelector('.billing-toggle-group [data-cycle="yearly"]').classList.contains('active');
    const cycle = isYearly ? 'yearly' : 'monthly';
    
    // Get plan details from the card
    const card = btnElement.closest('.upgrade-plan-card');
    const planName = card.querySelector('.upgrade-plan-name').textContent;
    const amount = cycle === 'yearly' ? parseFloat(card.dataset.yearly) : parseFloat(card.dataset.monthly);

    // Use the comprehensive payment modal
    openPaymentSelection({
        planId: planId,
        planName: planName,
        amount: amount,
        cycle: cycle,
        initiateUrl: '/tenant/?url=auth/create_plan_payment'
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
