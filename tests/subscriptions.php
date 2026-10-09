<?php
declare(strict_types=1);

$subscriptionFixture = ['id' => 1, 'owner_id' => 1, 'plan_id' => 1, 'status' => 'active',
    'billing_cycle' => 'monthly', 'start_date' => '2026-01-31', 'created_at' => '2026-01-31 10:00:00', 'end_date' => null];
$evaluate = static function (array $changes, ?string $paidAt, string $date) use ($subscriptionFixture): array {
    return Subscription::evaluateEntitlement(array_merge($subscriptionFixture, $changes),
        $paidAt === null ? null : ['paid_at' => $paidAt], new DateTimeImmutable($date));
};
$state = $evaluate([], null, '2026-02-27');
$check(!$state['is_expired'] && $state['expires_on'] === '2026-02-28', 'New active monthly subscription lasts one full clamped period');
$check($evaluate([], null, '2026-02-28')['is_expired'], 'Monthly entitlement expires at the next due date');
$state = $evaluate(['status' => 'expired'], '2026-10-09 10:00:00', '2026-10-09');
$check(!$state['is_expired'] && $state['status'] === 'active' && $state['expires_on'] === '2026-11-09', 'Paid renewal restores expired subscription for one period');
$check($evaluate(['status' => 'expired'], '2026-08-09', '2026-10-09')['is_expired'], 'Historical payment does not grant indefinite access');
$check($evaluate(['status' => 'cancelled'], '2026-10-09', '2026-10-09')['is_expired'], 'Payment does not override cancellation');
$check($evaluate(['status' => 'pending'], '2026-10-09', '2026-10-09')['is_expired'], 'Payment does not bypass pending activation');
$check($evaluate(['status' => 'expired'], '2026-10-10', '2026-10-09')['is_expired'], 'Future payment cannot grant access today');
$state = $evaluate(['billing_cycle' => 'yearly', 'start_date' => '2024-02-29'], null, '2025-02-27');
$check(!$state['is_expired'] && $state['expires_on'] === '2025-02-28', 'Yearly leap-day expiry clamps to February end');
$check($evaluate(['end_date' => '2026-10-08', 'status' => 'expired'], '2026-10-09', '2026-10-09')['is_expired'], 'Explicit subscription end remains enforced');
$check($evaluate(['start_date' => 'invalid'], null, '2026-10-09')['is_expired'], 'Invalid subscription dates deny entitlement');

$server->begin_transaction();
try {
    $model = new Subscription($server);
    $check($model->changePlan($fixtureSubscription, $fixturePlan, 'monthly', $fixtureOwner), 'Same-plan renewal succeeds when plan columns do not change');
    $check(!$model->changePlan($fixtureSubscription, $fixturePlan, 'monthly', $fixtureOtherOwner), 'Same-plan renewal still rejects foreign owner');
    $server->query("UPDATE subscriptions SET status = 'expired', start_date = DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH) WHERE id = " . $fixtureSubscription);
    $server->query("UPDATE plan_payments SET status = 'paid', paid_at = NOW() WHERE subscription_id = " . $fixtureSubscription);
    $status = $model->getOwnerSubscriptionStatus($fixtureOwner);
    $check($status && !$status['is_expired'] && $model->getLimitsForOwner($fixtureOwner)['room_limit'] === 50, 'Renewed entitlement and inventory limits agree');
    $listed = $model->allWithDetails($fixtureOwner);
    $check($listed[0]['status'] === 'active' && $listed[0]['expires_on'] === $status['expires_on'], 'Subscription list and gate share effective status and expiry');
    $server->query("INSERT INTO plans (name, price_monthly, price_yearly, bhouse_limit, room_limit) VALUES ('Unrelated plan', 50, 500, 1, 1)");
    $unrelatedPlan = (int)$server->insert_id;
    foreach (["status = 'pending'", 'owner_id = ' . $fixtureOtherOwner, "billing_cycle = 'yearly'", 'plan_id = ' . $unrelatedPlan] as $change) {
        $server->query('SAVEPOINT entitlement_probe');
        $server->query('UPDATE plan_payments SET ' . $change . ' WHERE subscription_id = ' . $fixtureSubscription);
        $check($model->getOwnerSubscriptionStatus($fixtureOwner)['is_expired'], 'Unrelated or unsettled payment cannot unlock owner: ' . $change);
        $server->query('ROLLBACK TO SAVEPOINT entitlement_probe');
    }
} finally {
    $server->rollback();
}
