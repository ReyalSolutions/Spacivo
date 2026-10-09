<?php
// Determine recommended plan index (Standard = index 1)
$_upgradeRecommendedIdx = 1;
?>
<!-- Upgrade Plan Modal Component -->
<style>
#upgradePlanModal { z-index: 9999 !important; }
.modal-backdrop { z-index: 9998 !important; }

/* Remove inner scrollbar - let the modal be auto height */
#upgradePlanModal .modal-dialog {
    max-height: none !important;
}
#upgradePlanModal .modal-content {
    background: #ffffff;
    border-radius: 28px;
    border: none;
    box-shadow: 0 32px 80px rgba(0,0,0,0.22);
    overflow: visible;
}
#upgradePlanModal .modal-body {
    overflow: visible !important;
    max-height: none !important;
}

/* Dark gradient header strip */
#upgradePlanModal .upgrade-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
    border-radius: 28px 28px 0 0;
    padding: 36px 40px 28px;
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
    font-size: 0.84rem;
    font-weight: 700;
    padding: 8px 22px;
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
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}
.upgrade-plan-card:hover {
    transform: translateY(-5px);
    border-color: #a5b4fc;
    box-shadow: 0 16px 40px rgba(99,102,241,0.12);
}
.upgrade-plan-card.recommended {
    background: #fff;
    border-color: #6366f1;
    box-shadow: 0 0 0 1px #6366f1, 0 16px 40px rgba(99,102,241,0.18);
}

.upgrade-plan-name {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #64748b;
}
.upgrade-plan-card.recommended .upgrade-plan-name { color: #6366f1; }

.popular-badge {
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 3px 10px;
    background: rgba(99,102,241,0.1);
    color: #6366f1;
    border: 1px solid rgba(99,102,241,0.25);
    border-radius: 100px;
}

.price-amount-lg {
    font-size: 2.2rem;
    font-weight: 900;
    color: #0f172a;
    line-height: 1;
    letter-spacing: -0.04em;
}
.price-period { font-size: 0.78rem; color: #94a3b8; font-weight: 600; }
.price-original {
    font-size: 0.78rem;
    color: #94a3b8;
    text-decoration: line-through;
    display: none;
}
.annual-badge {
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 100px;
    background: rgba(16,185,129,0.1);
    color: #059669;
    border: 1px solid rgba(16,185,129,0.25);
    display: none;
}

.limit-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 100px;
    padding: 3px 10px;
    font-size: 0.76rem;
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
    font-size: 0.81rem;
    color: #64748b;
    padding: 5px 0;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    border-bottom: 1px solid #f1f5f9;
}
.upgrade-feature-list li:last-child { border-bottom: none; }
.upgrade-feature-list li i { color: #10b981; font-size: 0.68rem; margin-top: 4px; flex-shrink: 0; }

.plan-cta-btn {
    width: 100%;
    padding: 11px 0;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.875rem;
    border: 2px solid #e2e8f0;
    background: #fff;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
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
    box-shadow: 0 6px 18px rgba(99,102,241,0.35);
}
.upgrade-plan-card.recommended .plan-cta-btn:hover {
    background: #4f46e5;
    border-color: #4f46e5;
}

.savings-global-note {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.5);
    text-align: center;
    margin-top: 12px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('upgradePlanModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
});

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
            if (annualEl) annualEl.style.display = 'inline';
        } else {
            amountEl.textContent = '₱' + Math.round(monthly).toLocaleString();
            if (periodEl) periodEl.textContent = '/mo';
            if (origEl)   origEl.style.display = 'none';
            if (annualEl) annualEl.style.display = 'none';
        }
    });
}

function submitDirectUpgrade(planId) {
    const subId = document.getElementById('upgradePlanActiveSubId').value;
    const isYearly = document.querySelector('.billing-toggle-group [data-cycle="yearly"]').classList.contains('active');
    const cycle = isYearly ? 'yearly' : 'monthly';

    // Find plan details
    const card = document.querySelector(`.upgrade-plan-card[onclick*="${planId}"]`) || document.querySelector(`.plan-cta-btn[onclick*="${planId}"]`).closest('.upgrade-plan-card');
    const planName = card.querySelector('.upgrade-plan-name').textContent;
    const amount = isYearly ? parseFloat(card.dataset.yearly) : parseFloat(card.dataset.monthly);

    const targetSubId = (subId && subId !== '0') ? parseInt(subId) : 0;
    const isNewSub = (targetSubId === 0);

    // Hide this modal
    bootstrap.Modal.getInstance(document.getElementById('upgradePlanModal')).hide();

    // Open Payment Selection Modal
    if (typeof openPaymentSelection === 'function') {
        openPaymentSelection({
            planId: planId,
            planName: isNewSub ? `${planName} Subscription` : `${planName} Upgrade`,
            amount: amount,
            cycle: cycle,
            subId: targetSubId
        });
    } else {
        console.error('payment_modal.php not included correctly.');
        Feedback.fire('Error', 'Payment system initialized incorrectly.', 'error');
    }
}
</script>

<div class="modal fade" id="upgradePlanModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <input type="hidden" id="upgradePlanActiveSubId" value="<?= isset($activeSubId) ? (int)$activeSubId : 0 ?>">

            <!-- Header (dark gradient) -->
            <div class="upgrade-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:50px;height:50px;background:linear-gradient(135deg,#6366f1,#a855f7);border-radius:14px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 20px rgba(99,102,241,0.4);flex-shrink:0;">
                            <i class="fa-solid fa-rocket" style="font-size:1.3rem;color:#fff;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-900 text-white" style="letter-spacing:-0.02em;">Upgrade Your Plan</h5>
                            <p class="mb-0 small fw-500" style="color:rgba(255,255,255,0.5);">Unlock more properties, rooms &amp; features</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
                            data-bs-dismiss="modal"
                            style="width:34px;height:34px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.6);flex-shrink:0;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Billing toggle -->
                <div class="text-center mt-4">
                    <div class="billing-toggle-group">
                        <button data-cycle="monthly" class="active" onclick="setUpgradeBilling('monthly')">Monthly</button>
                        <button data-cycle="yearly" onclick="setUpgradeBilling('yearly')">
                            Yearly &nbsp;<span style="font-size:0.6rem;font-weight:800;padding:1px 7px;background:rgba(16,185,129,0.2);color:#6ee7b7;border-radius:100px;">Save 17%</span>
                        </button>
                    </div>
                    <div class="savings-global-note mt-2">Yearly billing saves up to 17%</div>
                </div>
            </div>

            <!-- Body (white/light) -->
            <div class="modal-body px-4 pt-4 pb-4" style="background:#f8fafc; border-radius: 0 0 28px 28px;">
                <div class="row g-3">
                    <?php if (isset($allPlans) && is_array($allPlans)):
                        $plans = array_values($allPlans);
                        $recIdx = 1;
                    ?>
                        <?php foreach ($plans as $i => $plan):
                            $features = json_decode($plan['features'] ?? '[]', true);
                            $isRec = ($i === $recIdx);
                        ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="upgrade-plan-card h-100 d-flex flex-column p-4 <?= $isRec ? 'recommended' : '' ?>"
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
                                <div class="d-flex align-items-baseline gap-1 mb-1">
                                    <span class="price-amount-lg">₱<?= number_format($plan['price_monthly'], 0) ?></span>
                                    <span class="price-period">/mo</span>
                                </div>
                                <div class="mb-3"><span class="annual-badge">Billed annually</span></div>

                                <!-- Limits -->
                                <div class="d-flex flex-wrap gap-2 mb-4">
                                    <span class="limit-chip"><i class="fa-solid fa-house-chimney fa-xs"></i><?= $plan['bhouse_limit'] ?> Properties</span>
                                    <span class="limit-chip"><i class="fa-solid fa-door-open fa-xs"></i><?= $plan['room_limit'] ?> Rooms</span>
                                </div>

                                <!-- Features -->
                                <ul class="upgrade-feature-list mb-4 flex-grow-1">
                                    <?php if (is_array($features)): foreach ($features as $f): ?>
                                        <li><i class="fa-solid fa-check"></i><?= htmlspecialchars($f) ?></li>
                                    <?php endforeach; endif; ?>
                                </ul>

                                <!-- CTA -->
                                <button type="button" class="plan-cta-btn"
                                        onclick="submitDirectUpgrade(<?= (int)$plan['id'] ?>)">
                                    <?= $isRec ? '⚡ Upgrade Now' : 'Select Plan' ?>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-4 text-muted">No plans configured. Contact your administrator.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
