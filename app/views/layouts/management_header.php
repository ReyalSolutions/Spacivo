<?php
$currentRole = strtolower($_SESSION['role'] ?? '');
$sysSettings = (new SystemSetting($this->db()))->getAll();
$siteName = $sysSettings['site_name'] ?? 'StayHub';
$siteFavicon = $sysSettings['site_favicon'] ?? 'public/assets/images/favicon.png';
$userName = htmlspecialchars($_SESSION['name'] ?? 'User', ENT_QUOTES, 'UTF-8');
$userRole = htmlspecialchars(ucfirst($currentRole), ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');
$portalCan = function (string $permission): bool { return $this->hasPermission($permission); };
?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
<title><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?> Management</title>
<link rel="stylesheet" href="/tenant/public/assets/css/dashboard.css">
<?php require __DIR__ . '/../../../admin/components/links.php'; ?>
</head><body class="management-body">
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
<?php require __DIR__ . '/../../../admin/components/sidebar.php'; ?>
<div class="body-wrapper">
<?php require __DIR__ . '/../../../admin/components/header.php'; ?>
<div class="container-fluid">
