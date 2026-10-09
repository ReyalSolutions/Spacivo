<?php require __DIR__ . '/../layouts/management_header.php'; ?>
<?php require __DIR__ . '/../components/upgrade_plans.php'; ?>
<?php require __DIR__ . '/../components/payment_modal.php'; ?>
<script>document.addEventListener('DOMContentLoaded', function(){setUpgradeBilling(<?= json_encode($billingCycle) ?>);});</script>
<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
