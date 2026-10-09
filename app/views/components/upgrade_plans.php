<?php
// Determine recommended plan index (Standard = index 1)
$_upgradeRecommendedIdx = 1;
?>
<!-- Upgrade Plan Modal Component -->
<style>
#upgradePlans{color:#27272a}
#upgradePlans .upgrade-layout{max-width:1120px;margin:24px auto}
#upgradePlans .upgrade-content{background:#fff;border:1px solid #e4e4e7;border-radius:12px;box-shadow:0 16px 48px #18181b24;overflow:hidden;max-height:none}
#upgradePlans .upgrade-header{background:#fff;border-bottom:1px solid #e4e4e7;padding:24px;flex-shrink:0}
#upgradePlans .upgrade-icon{width:40px;height:40px;background:#f4f4f5;border:1px solid #e4e4e7;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#3f3f46}
#upgradePlans .upgrade-header h5{font-size:20px;font-weight:600;letter-spacing:-.3px;color:#18181b}
#upgradePlans .upgrade-subtitle,#upgradePlans .savings-global-note{font-size:12px;color:#71717a}
#upgradePlans .upgrade-close{width:32px;height:32px;background:#fff;border:1px solid #e4e4e7;border-radius:7px;color:#52525b;flex-shrink:0}
#upgradePlans .billing-toggle-group{display:inline-flex;background:#f4f4f5;border:1px solid #e4e4e7;border-radius:8px;padding:3px}
#upgradePlans .billing-toggle-group button{border:0;background:transparent;color:#71717a;font-size:12px;font-weight:500;padding:8px 18px;border-radius:6px;cursor:pointer;white-space:nowrap}
#upgradePlans .billing-toggle-group button.active{background:#18181b;color:#fff}
#upgradePlans .savings-label{font-size:10px;padding:2px 5px;background:#eaf7ee;color:#237344;border-radius:4px}
#upgradePlans .savings-global-note{text-align:center;margin-top:8px}
#upgradePlans .upgrade-body{background:#fafafa;overflow:visible;min-height:0;padding:24px}
#upgradePlans .upgrade-plan-card{background:#fff;border:1px solid #e4e4e7;border-radius:10px;padding:20px!important}
#upgradePlans .upgrade-plan-card.recommended{border-color:#2878f0;box-shadow:0 0 0 1px #2878f0}
#upgradePlans .upgrade-plan-name{font-size:12px;font-weight:600;color:#52525b}
#upgradePlans .popular-badge{font-size:10px;font-weight:500;padding:3px 7px;background:#eff6ff;color:#2563eb;border-radius:5px}
#upgradePlans .price-amount-lg{font-size:30px;font-weight:600;color:#18181b;line-height:1.2;letter-spacing:-1px}
#upgradePlans .price-period,#upgradePlans .price-original{font-size:12px;color:#71717a}
#upgradePlans .price-original{text-decoration:line-through;display:none}
#upgradePlans .annual-badge{font-size:10px;color:#237344;background:#eaf7ee;padding:2px 6px;border-radius:4px;display:none}
#upgradePlans .limit-chip{display:inline-flex;align-items:center;gap:5px;background:#f4f4f5;border:1px solid #e4e4e7;border-radius:6px;padding:4px 7px;font-size:11px;color:#52525b}
#upgradePlans .upgrade-feature-list{list-style:none;padding:0;margin:0}
#upgradePlans .upgrade-feature-list li{display:flex;align-items:flex-start;gap:8px;font-size:12px;line-height:1.5;color:#52525b;padding:6px 0;border-bottom:1px solid #f4f4f5}
#upgradePlans .upgrade-feature-list li:last-child{border-bottom:0}
#upgradePlans .upgrade-feature-list i{color:#2878f0;font-size:10px;margin-top:4px;flex-shrink:0}
#upgradePlans .plan-cta-btn{width:100%;padding:10px 12px;border-radius:7px;font-size:12px;font-weight:500;border:1px solid #dedee3;background:#fff;color:#27272a;cursor:pointer}
#upgradePlans .plan-cta-btn:hover{background:#f4f4f5}
#upgradePlans .recommended .plan-cta-btn{background:#18181b;border-color:#18181b;color:#fff}
#upgradePlans .recommended .plan-cta-btn:hover{background:#3f3f46}
#upgradePlans button:focus-visible{outline:2px solid #2878f0;outline-offset:3px}
@media(max-width:575.98px){#upgradePlans .upgrade-layout{margin:12px}#upgradePlans .upgrade-content{max-height:none}#upgradePlans .upgrade-header,#upgradePlans .upgrade-body{padding:16px}#upgradePlans .upgrade-header h5{font-size:18px}}
</style>

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

<section id="upgradePlans" aria-labelledby="upgradePlansTitle">
    <div class="upgrade-layout">
        <div class="upgrade-content">
            <input type="hidden" id="upgradePlanActiveSubId" value="<?= isset($activeSubId) ? (int)$activeSubId : 0 ?>">

            <!-- Shared neutral management design -->
            <div class="upgrade-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-3">
                        <div class="upgrade-icon">
                            <i class="fa-solid fa-arrow-up"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="upgradePlansTitle">Upgrade Your Plan</h5>
                            <p class="mb-0 upgrade-subtitle">Unlock more properties, rooms &amp; features</p>
                        </div>
                    </div>
                    <a href="/tenant/?url=admin/subscriptions" class="btn btn-outline-primary">Back to subscriptions</a>
                </div>

                <!-- Billing toggle -->
                <div class="text-center mt-4">
                    <div class="billing-toggle-group">
                        <button data-cycle="monthly" class="active" onclick="setUpgradeBilling('monthly')">Monthly</button>
                        <button data-cycle="yearly" onclick="setUpgradeBilling('yearly')">
                            Yearly &nbsp;<span class="savings-label">Save 17%</span>
                        </button>
                    </div>
                    <div class="savings-global-note mt-2">Yearly billing saves up to 17%</div>
                </div>
            </div>

            <!-- Body (white/light) -->
            <div class="upgrade-body">
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
                                    <?php if ($isRec): ?><span class="popular-badge">Popular</span><?php endif; ?>
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
                                    <?= $isRec ? 'Upgrade Now' : 'Select Plan' ?>
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
</section>
