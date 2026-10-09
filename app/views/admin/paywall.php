<?php
$reason = $paywallReason ?? 'no_subscription';
$sub    = is_array($subscriptionData ?? null) ? $subscriptionData : [];
$planName     = $sub['plan_name']     ?? 'No Plan';
$billingCycle = $sub['billing_cycle'] ?? 'monthly';
$expiresOn    = $sub['expires_on']    ?? 'N/A';
$priceMonthly = (float)($sub['price_monthly'] ?? 0);
$priceYearly  = (float)($sub['price_yearly']  ?? 0);
$subId        = (int)($sub['id']            ?? 0);
$planId       = (int)($sub['plan_id']       ?? 0);

$cycleLabel  = ($billingCycle === 'yearly') ? 'Yearly' : 'Monthly';
$amountDue   = ($billingCycle === 'yearly') ? $priceYearly : $priceMonthly;

if (empty($availablePlans)) {
    $db = Database::get();
    $res = $db->query("SELECT * FROM plans WHERE is_deleted = 0 ORDER BY price_monthly ASC");
    $availablePlans = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

if (empty($paymentSettings)) {
    require_once __DIR__ . '/../../models/SystemSetting.php';
    $psModel = new SystemSetting(Database::get());
    $paymentSettings = $psModel->getAll();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= ($reason === 'no_subscription') ? 'Subscription Required' : 'Subscription Renewal Required' ?> — StayHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/tenant/public/assets/css/toast.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/tenant/public/assets/js/toast.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: #0b0f19;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 32px 16px;
            position: relative;
            color: #f1f5f9;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 65% 55% at 20% 15%, rgba(99,102,241,0.18) 0%, transparent 65%),
                radial-gradient(ellipse 55% 65% at 85% 85%, rgba(244,63,94,0.14) 0%, transparent 65%),
                radial-gradient(ellipse 45% 45% at 50% 50%, rgba(16,185,129,0.08) 0%, transparent 60%);
            pointer-events: none;
        }
        .paywall-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
        }
        .paywall-card {
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 32px;
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            box-shadow: 0 32px 80px rgba(0,0,0,0.6);
            padding: 44px 36px;
            text-align: center;
        }
        .paywall-icon-ring {
            width: 88px; height: 88px;
            margin: 0 auto 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(236,72,153,0.25));
            border: 2px solid rgba(165,180,252,0.3);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 35px rgba(99,102,241,0.3);
        }
        .paywall-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }
        .paywall-sub {
            color: rgba(241,245,249,0.7);
            font-size: 1rem;
            line-height: 1.6;
            max-width: 620px;
            margin: 0 auto 32px;
        }
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99,102,241,0.15);
            border: 1px solid rgba(99,102,241,0.3);
            color: #a5b4fc;
            border-radius: 30px;
            padding: 6px 16px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .badge-status.overdue {
            background: rgba(239,68,68,0.15);
            border-color: rgba(239,68,68,0.35);
            color: #f87171;
        }
        .btn-pay-now {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 36px;
            border-radius: 16px;
            font-size: 1.05rem;
            font-weight: 800;
            border: none;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 28px rgba(99,102,241,0.4);
            text-decoration: none;
        }
        .btn-pay-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(99,102,241,0.6);
            color: #fff;
        }
        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 28px;
            padding: 10px 24px;
            font-size: 0.88rem;
            font-weight: 600;
            color: rgba(241,245,249,0.6);
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(241,245,249,0.3);
            color: #fff;
        }

        /* Billing toggle */
        .billing-toggle-container {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 100px;
            padding: 6px;
            margin-bottom: 32px;
        }
        .billing-toggle-btn {
            border: none;
            background: transparent;
            color: rgba(241,245,249,0.6);
            font-size: 0.88rem;
            font-weight: 700;
            padding: 8px 22px;
            border-radius: 100px;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .billing-toggle-btn.active {
            background: #4f46e5;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(79,70,229,0.5);
        }
        .discount-pill {
            font-size: 0.72rem;
            background: rgba(16,185,129,0.2);
            color: #34d399;
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 100px;
            padding: 2px 8px;
            font-weight: 700;
            margin-left: 4px;
        }

        /* Plans Grid */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 10px;
            text-align: left;
        }
        .plan-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        .plan-card:hover {
            transform: translateY(-6px);
            border-color: rgba(99,102,241,0.5);
            background: rgba(255,255,255,0.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .plan-card.featured {
            border-color: #6366f1;
            background: rgba(99,102,241,0.07);
            box-shadow: 0 0 30px rgba(99,102,241,0.2);
        }
        .plan-badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            letter-spacing: 0.08em;
            box-shadow: 0 4px 12px rgba(99,102,241,0.4);
        }
        .plan-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
        }
        .plan-price-wrap {
            margin-bottom: 20px;
        }
        .plan-price {
            font-size: 2.2rem;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.1;
        }
        .plan-price-period {
            font-size: 0.85rem;
            color: rgba(241,245,249,0.5);
            font-weight: 600;
        }
        .plan-limits {
            font-size: 0.82rem;
            font-weight: 600;
            color: #a5b4fc;
            background: rgba(99,102,241,0.1);
            border-radius: 8px;
            padding: 6px 10px;
            margin-bottom: 16px;
        }
        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 24px 0;
            font-size: 0.85rem;
            color: rgba(241,245,249,0.75);
            flex-grow: 1;
        }
        .plan-features li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        .plan-features li i {
            color: #34d399;
            font-size: 0.85rem;
            margin-top: 3px;
            flex-shrink: 0;
        }
        .btn-select-plan {
            width: 100%;
            padding: 13px;
            border-radius: 14px;
            font-size: 0.92rem;
            font-weight: 800;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            color: #ffffff;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .btn-select-plan:hover, .plan-card.featured .btn-select-plan {
            background: #4f46e5;
            border-color: #4f46e5;
            box-shadow: 0 8px 20px rgba(79,70,229,0.4);
        }
        .btn-select-plan:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div id="toast-stack"></div>

<div class="paywall-wrapper">
    <div class="paywall-card">
        <div class="paywall-icon-ring">
            <i class="fa-solid fa-crown" style="font-size: 2.4rem; color: #a5b4fc;"></i>
        </div>

        <?php if ($reason === 'no_subscription'): ?>
            <div class="badge-status">
                <i class="fa-solid fa-sparkles"></i> Welcome Property Owner
            </div>
            <div class="paywall-title">No Active Subscription Plan</div>
            <p class="paywall-sub">
                You do not have an active subscription yet. Choose a plan below to unlock your property management console, list boarding houses, and accept tenant reservations.
            </p>
        <?php else: ?>
            <div class="badge-status overdue">
                <i class="fa-solid fa-triangle-exclamation"></i> Renewal Required — Expired <?= htmlspecialchars($expiresOn) ?>
            </div>
            <div class="paywall-title">Subscription Renewal Required</div>
            <p class="paywall-sub">
                Your <strong><?= htmlspecialchars($planName) ?></strong> plan cycle has ended. Please renew or pick a new plan to resume property operations.
            </p>
        <?php endif; ?>

        <!-- Billing Cycle Switch -->
        <div class="billing-toggle-container">
            <button type="button" class="billing-toggle-btn active" data-cycle="monthly" onclick="switchBillingCycle('monthly')">
                Monthly Billing
            </button>
            <button type="button" class="billing-toggle-btn" data-cycle="yearly" onclick="switchBillingCycle('yearly')">
                Yearly Billing <span class="discount-pill">Save 20%</span>
            </button>
        </div>

        <!-- Available Plans Grid -->
        <div class="plans-grid">
            <?php foreach ($availablePlans as $idx => $plan): 
                $features = json_decode($plan['features'] ?? '[]', true) ?: [];
                $isFeatured = ($idx === 1 || strtolower($plan['name']) === 'standard');
            ?>
                <div class="plan-card <?= $isFeatured ? 'featured' : '' ?>" 
                     data-plan-id="<?= (int)$plan['id'] ?>"
                     data-plan-name="<?= htmlspecialchars($plan['name']) ?>"
                     data-monthly="<?= (float)$plan['price_monthly'] ?>"
                     data-yearly="<?= (float)$plan['price_yearly'] ?>">
                    
                    <?php if ($isFeatured): ?>
                        <div class="plan-badge">Recommended</div>
                    <?php endif; ?>

                    <div>
                        <div class="plan-name"><?= htmlspecialchars($plan['name']) ?></div>
                        <div class="plan-price-wrap">
                            <span class="plan-price">₱<?= number_format((float)$plan['price_monthly']) ?></span>
                            <span class="plan-price-period">/ month</span>
                        </div>
                        <div class="plan-limits">
                            <i class="fa-solid fa-building me-1"></i> <?= (int)$plan['bhouse_limit'] ?> <?= (int)$plan['bhouse_limit'] === 1 ? 'House' : 'Houses' ?> • 
                            <i class="fa-solid fa-door-open ms-1 me-1"></i> <?= (int)$plan['room_limit'] ?> Rooms
                        </div>
                    </div>

                    <ul class="plan-features">
                        <?php foreach (array_slice($features, 0, 5) as $f): ?>
                            <li><i class="fa-solid fa-circle-check"></i> <span><?= htmlspecialchars($f) ?></span></li>
                        <?php endforeach; ?>
                        <?php if (count($features) > 5): ?>
                            <li><i class="fa-solid fa-circle-check"></i> <span>+<?= count($features) - 5 ?> additional features</span></li>
                        <?php endif; ?>
                    </ul>

                    <div>
                        <button type="button" class="btn-select-plan" onclick="processPlanSelection(<?= (int)$plan['id'] ?>, '<?= addslashes(htmlspecialchars($plan['name'])) ?>')">
                            <i class="fa-solid fa-credit-card me-2"></i> Select &amp; Pay Now
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <form method="POST" action="/tenant/?url=auth/logout" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
                <button type="submit" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Sign out
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include __DIR__ . '/../components/payment_modal.php'; ?>

<script>
let currentBillingCycle = 'monthly';

function switchBillingCycle(cycle) {
    currentBillingCycle = cycle;
    document.querySelectorAll('.billing-toggle-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.cycle === cycle);
    });

    document.querySelectorAll('.plan-card').forEach(card => {
        const monthly = parseFloat(card.dataset.monthly);
        const yearly = parseFloat(card.dataset.yearly);
        const priceEl = card.querySelector('.plan-price');
        const periodEl = card.querySelector('.plan-price-period');

        if (cycle === 'yearly') {
            priceEl.textContent = '₱' + Math.round(yearly).toLocaleString();
            periodEl.textContent = '/ year';
        } else {
            priceEl.textContent = '₱' + Math.round(monthly).toLocaleString();
            periodEl.textContent = '/ month';
        }
    });
}

function processPlanSelection(planId, planName) {
    const card = document.querySelector(`.plan-card[data-plan-id="${planId}"]`);
    if (!card) return;

    const monthly = parseFloat(card.dataset.monthly);
    const yearly = parseFloat(card.dataset.yearly);
    const amount = (currentBillingCycle === 'yearly') ? yearly : monthly;

    if (typeof openPaymentSelection === 'function') {
        openPaymentSelection({
            subId: <?= (int)$subId ?>, // 0 if brand new
            planId: planId,
            planName: `${planName} (${currentBillingCycle === 'yearly' ? 'Yearly' : 'Monthly'})`,
            amount: amount,
            cycle: currentBillingCycle,
            initiateUrl: '/tenant/?url=admin/upgrade_plan'
        });
    } else {
        ToastStack.danger('Payment modal initialization failed. Please reload the page.', 'System Error');
    }
}
</script>

</body>
</html>
