<?php
$sysSettings = (new SystemSetting($this->db()))->getAll();
$siteName = $sysSettings['site_name'] ?? 'Spacivo';
$siteFavicon = $sysSettings['site_favicon'] ?? 'public/assets/images/favicon.png';
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
<title>Upgrade your plan — <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></title>
<?php require __DIR__ . '/../../../admin/components/links.php'; ?>
<style>body{background:#fafafa}.upgrade-page{padding:24px 16px;min-height:100vh}.upgrade-page .upgrade-layout{margin:0 auto!important}.upgrade-page .upgrade-content{box-shadow:none!important}@media(max-width:575.98px){.upgrade-page{padding:12px 0}}</style>
</head><body>
<main class="upgrade-page">
<?php require __DIR__ . '/../components/upgrade_plans.php'; ?>
</main>
<?php require __DIR__ . '/../components/payment_modal.php'; ?>
<script src="/tenant/admin/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script>document.addEventListener('DOMContentLoaded', function(){setUpgradeBilling(<?= json_encode($billingCycle) ?>);});</script>
</body></html>
