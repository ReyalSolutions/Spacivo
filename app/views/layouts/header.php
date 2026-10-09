<?php
// Shared layout header.
$currentRole = $_SESSION['role'] ?? null;
$currentUserName = $_SESSION['name'] ?? null;

// Fetch System Settings
$settingsModel = new SystemSetting($this->db());
$sysSettings = $settingsModel->getAll();
$siteLogo = $sysSettings['company_logo'] ?? null;
$siteName = $sysSettings['site_name'] ?? 'StayHub';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($siteName) ?></title>
    <link rel="icon" type="image/png" href="/tenant/<?= htmlspecialchars($siteLogo ?? 'public/assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/tenant/public/assets/css/style.css">
    <link rel="stylesheet" href="/tenant/public/assets/css/skeleton.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php 
    $url = $_GET['url'] ?? '';
    if (strpos($url, 'auth/') === 0): ?>
        <link rel="stylesheet" href="/tenant/public/assets/css/auth.css">
    <?php endif; ?>

    <?php if (strpos($url, 'tenant') === 0 || strpos($url, 'owner') === 0 || strpos($url, 'admin') === 0 || strpos($url, 'boarding') === 0 || strpos($url, 'payment') === 0): ?>
        <link rel="stylesheet" href="/tenant/public/assets/css/dashboard.css">
    <?php endif; ?>

    <link rel="stylesheet" href="/tenant/public/assets/css/toast.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/tenant/public/assets/js/toast.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<?php 
$isDashboard = (
    strpos($url, 'tenant') === 0 ||
    strpos($url, 'owner') === 0 ||
    strpos($url, 'admin') === 0 ||
    strpos($url, 'payment') === 0 ||
    (strpos($url, 'boarding') === 0 && 
     trim($url, '/') !== 'boarding/index' && 
     trim($url, '/') !== 'boarding' && 
     ((strpos(trim($url, '/'), 'boarding/explore') !== 0 && strpos(trim($url, '/'), 'boarding/show') !== 0) || !empty($currentRole)))
);
?>
<?php $isAuth = strpos($url, 'auth/') === 0; ?>
<?php $isExplore = trim($url, '/') === 'boarding/explore'; ?>
<body class="<?= $isDashboard ? 'dashboard-page' : '' ?> <?= $isAuth ? 'auth-page' : '' ?> <?= $isExplore ? 'explore-page' : '' ?> <?= !empty($currentUserName) ? 'logged-in' : '' ?>">
    <!-- Global Premium Loader -->
    <div id="page-loader" class="loader-template">
        <div class="loader-content">
            <div class="stayhub-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-core">
                    <i class="fa-solid fa-house-chimney-window"></i>
                </div>
            </div>
            <span class="loader-text"><?= htmlspecialchars($siteName) ?></span>
        </div>
    </div>

    <style>
        .loader-template {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: all 0.5s ease;
        }

        .loader-template.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .stayhub-spinner {
            position: relative;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .spinner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid rgba(79, 70, 229, 0.1);
            border-top: 4px solid #4f46e5;
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        .spinner-core {
            font-size: 2.5rem;
            color: #4f46e5;
            animation: pulse-logo 2s ease-in-out infinite;
        }

        .loader-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
            color: #1e293b;
            background: linear-gradient(135deg, #1e293b 0%, #4f46e5 100%);
            -webkit-background-clip: text;
            background-clip: text; /* Fixed compatibility lint */
            -webkit-text-fill-color: transparent;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes pulse-logo {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(0.9); opacity: 0.7; }
        }
    </style>
<?php if ($isDashboard): ?>
    <header class="dash-header" id="dashHeader">
        <div class="dash-header-inner">
            <a href="/tenant/" class="brand">
                <?php if ($siteLogo): ?>
                    <img src="/tenant/<?= htmlspecialchars($siteLogo) ?>" style="height: 32px; width: auto; object-fit: contain;">
                <?php else: ?>
                    <i class="fa-solid fa-house-chimney-window"></i>
                <?php endif; ?>
                <span><?= htmlspecialchars($siteName) ?></span>
            </a>

        <nav class="dash-nav">
            <a href="/tenant/?url=boarding/explore" class="<?= (strpos($url, 'boarding/') === 0 && $url !== 'boarding/index') ? 'active' : '' ?>">Explore</a>
            <?php if ($currentRole === 'tenant'): ?>
                <a href="/tenant/?url=tenant/dashboard" class="<?= ($url === 'tenant/dashboard') ? 'active' : '' ?>">Dashboard</a>
                <a href="/tenant/?url=tenant/bookings" class="<?= ($url === 'tenant/bookings') ? 'active' : '' ?>">My Bhouse</a>
                <a href="/tenant/?url=tenant/payments" class="<?= ($url === 'tenant/payments') ? 'active' : '' ?>">Payments</a>
            <?php elseif ($currentRole === 'owner'): ?>
                <a href="/tenant/?url=admin/index" class="<?= ($url === 'admin/index') ? 'active' : '' ?>">Owner Dashboard</a>
                <a href="/tenant/?url=owner/houses" class="<?= ($url === 'owner/houses') ? 'active' : '' ?>">My Houses</a>
                <a href="/tenant/?url=owner/bookings" class="<?= ($url === 'owner/bookings') ? 'active' : '' ?>">Tenants</a>
            <?php elseif ($currentRole === 'admin'): ?>
                <a href="/tenant/admin/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') !== false || $url === 'admin/index') ? 'active' : '' ?>">Admin Panel</a>
            <?php endif; ?>
        </nav>

        <?php if (!empty($currentRole)): ?>
            <div class="header-actions">
                <button class="action-btn">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notification-badge"></span>
                </button>
                <div class="profile-trigger" id="profile-trigger">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>

            <div class="profile-dropdown" id="profile-dropdown">
                <div class="dropdown-header">
                    <div class="user-avatar-lg">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info-text">
                        <span class="user-name"><?= htmlspecialchars($currentUserName ?? 'User', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="user-role"><?= htmlspecialchars($currentRole ?? 'Tenant', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
                <div style="padding: 0 8px;">
                <a href="/tenant/?url=tenant/profile" class="dropdown-item">
                    <i class="fa-regular fa-user"></i>
                    Profile
                </a>
                <a href="/tenant/?url=tenant/settings" class="dropdown-item">
                    <i class="fas fa-gear"></i> Settings
                </a>
                <?php if (getenv('ORGANIZATIONS_ENABLED') === 'true'): ?>
                <a href="/tenant/?url=organization/index" class="dropdown-item"><i class="fas fa-building"></i> Organizations</a>
                <?php endif; ?>
                <div style="height: 1px; background: #f1f5f9; margin: 4px 8px;"></div>
                <form id="logoutForm" action="/tenant/?url=auth/logout" method="POST" style="display: none;"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>"></form>
                <a href="#" class="dropdown-item logout" onclick="event.preventDefault(); confirmLogout('logoutForm');">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Logout
                </a>
                </div>
            </div>
        <?php else: ?>
            <div class="header-actions" style="gap: 12px; margin-right: 16px;">
                <a href="/tenant/?url=auth/login" style="text-decoration: none; font-weight: 700; color: #475569; padding: 8px 12px;">Login</a>
                <a href="/tenant/?url=auth/register" style="text-decoration: none; font-weight: 700; color: white; background: var(--dash-primary); padding: 8px 16px; border-radius: 8px;">Register</a>
            </div>
        <?php endif; ?>
            </div>
        </div>
    </header>
<?php endif; ?>

<style>
    /* Sticky Footer Logic */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        margin: 0;
    }
    main {
        flex: 1 0 auto;
        display: flex;
        flex-direction: column;
    }

    /* Premium Header Unified Styles (Dashboard & Landing) */
    <?php if (!$isDashboard && !$isAuth): ?>
    .app-header {
        display: flex !important; /* Force visibility exclusively for landing app-header against style.css constraints */
    }
    <?php endif; ?>
    .app-header, .dash-header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100px;
        z-index: 9999;
        display: flex;
        align-items: center;
        transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
        font-family: 'Outfit', sans-serif;
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .app-header.sticky, .dash-header.sticky {
        height: 75px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .header-inner, .dash-header-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: 0 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .brand, .dash-header .brand {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: #4f46e5;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -1px;
        transition: transform 0.3s ease;
    }

    .brand i, .dash-header .brand i {
        font-size: 1.75rem;
        color: #6366f1;
    }

    .brand:hover, .dash-header .brand:hover {
        transform: scale(1.05);
    }

    .app-nav, .dash-nav {
        display: flex;
        gap: 32px;
        align-items: center;
    }

    .app-nav a, .dash-nav a {
        text-decoration: none !important;
        color: #334155;
        font-weight: 700;
        font-size: 0.95rem;
        position: relative;
        padding: 8px 0;
        transition: all 0.3s ease;
    }

    .app-nav a::after, .dash-nav a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: #6366f1;
        transition: width 0.3s ease;
        border-radius: 2px;
    }

    .app-nav a:hover, .app-nav a.active,
    .dash-nav a:hover, .dash-nav a.active {
        color: #6366f1;
        background: transparent !important;
    }

    .app-nav a:hover::after, .app-nav a.active::after,
    .dash-nav a:hover::after, .dash-nav a.active::after {
        width: 100%;
    }

    .user-area, .header-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .btn-auth {
        padding: 12px 28px;
        border-radius: 100px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem;
    }

    .btn-login {
        color: #475569;
    }

    .btn-login:hover {
        color: var(--landing-primary, #6366f1);
        transform: translateY(-2px);
    }

    .btn-register {
        background: #4f46e5;
        color: white;
        box-shadow: 0 10px 20px rgba(79, 70, 229, 0.15);
    }

    .btn-register:hover {
        background: #4338ca;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(79, 70, 229, 0.3);
    }

    /* Mobile Menu Toggle */
    .menu-toggle {
        display: none;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.4rem;
        color: #0f172a;
        background: #f8fafc;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    /* Mobile Auth Buttons */
    .mobile-auth-buttons {
        display: none;
    }

    /* Main content padding for fixed header clearance */
    main.container, 
    main.landing-main {
        padding-top: 100px;
        transition: padding 0.5s ease;
    }

    @media (max-width: 991px) {
        main.container, 
        main.landing-main {
            padding-top: 80px;
        }

        .menu-toggle {
            display: flex;
        }
    }

    .menu-toggle:hover {
        background: rgba(79, 70, 229, 0.15);
        transform: scale(1.05);
    }

    @media (max-width: 991px) {
        .app-nav, .dash-nav { 
            display: none !important; 
        }

        .app-nav.active {
            display: flex !important;
            flex-direction: column;
            position: fixed;
            top: 80px;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            padding: 24px;
            gap: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            border-top: 1px solid rgba(0,0,0,0.05);
            z-index: 9998;
            animation: slideDownNav 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes slideDownNav {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .app-nav.active a {
            font-size: 1.15rem;
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            background: rgba(79, 70, 229, 0.03);
        }

        .app-nav.active a:hover {
            background: rgba(79, 70, 229, 0.08);
        }
        
        .header-inner, .dash-header-inner {
            padding: 0 1rem !important;
        }

        .brand span {
            font-size: 1.25rem;
        }

        .app-header, .dash-header {
            height: 80px !important;
            background: #ffffff !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
        }

        .mobile-auth-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            margin-top: 10px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .mobile-auth-buttons a {
            text-align: center;
            width: 100%;
        }

        .mobile-auth-buttons .btn-login {
            background: transparent !important;
            color: #475569 !important;
            box-shadow: none !important;
        }

        .mobile-auth-buttons .btn-register {
            background: #4f46e5 !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2) !important;
        }

        .user-area.guest-user-area {
            display: none !important;
        }

        /* Ensure User Area is always visible on mobile since toggle is gone */
        .user-area, .header-actions {
            display: flex !important;
            gap: 15px !important;
        }

        .user-trigger span {
            display: none; /* Hide 'Hi, Name' on mobile to save space */
        }

        .user-trigger::before {
            content: '\f007'; /* fa-user */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 1.1rem;
            color: #4f46e5;
        }
    }
    /* Premium User Area & Dropdown */
    .app-notification-trigger {
        position: relative;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(79, 70, 229, 0.05);
        color: #4f46e5;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .app-notification-trigger:hover {
        background: rgba(79, 70, 229, 0.1);
        transform: translateY(-2px);
    }

    .app-pulse-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 8px;
        height: 8px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid white;
    }

    .app-pulse-badge::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid #ef4444;
        animation: badgePulse 2s infinite;
        opacity: 0;
    }

    @keyframes badgePulse {
        0% { transform: scale(0.5); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }

    .app-user-dropdown {
        position: relative;
        cursor: pointer;
    }

    .user-trigger {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        background: white;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 100px;
        font-weight: 700;
        color: #0f172a;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }

    .app-user-dropdown:hover .user-trigger {
        border-color: rgba(79, 70, 229, 0.2);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.08);
    }

    .app-dropdown-portal {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 240px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
        padding: 12px;
        transform: translateY(10px);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 10000;
    }

    .app-user-dropdown.show .app-dropdown-portal {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }

    .dropdown-header {
        padding: 12px 16px;
        display: flex;
        flex-direction: column;
    }

    .user-role-badge {
        font-size: 0.7rem;
        font-weight: 800;
        color: #4f46e5;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    .app-dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 14px;
        text-decoration: none;
        color: #475569;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .app-dropdown-item i {
        font-size: 1.1rem;
        opacity: 0.7;
    }

    .app-dropdown-item:hover {
        background: rgba(79, 70, 229, 0.05);
        color: #4f46e5;
    }

    .app-dropdown-item.logout-link {
        color: #ef4444;
    }

    .app-dropdown-item.logout-link:hover {
        background: rgba(239, 68, 68, 0.05);
    }

    .dropdown-divider {
        height: 1px;
        background: rgba(0,0,0,0.05);
        margin: 8px;
    }
</style>

<header class="app-header" id="appHeader" <?= ($isDashboard || $isAuth) ? 'style="display:none;"' : '' ?>>
    <div class="header-inner">
        <a class="brand" href="/tenant/">
            <?php if ($siteLogo): ?>
                <img src="/tenant/<?= htmlspecialchars($siteLogo) ?>" style="height: 38px; width: auto; object-fit: contain;">
            <?php else: ?>
                <i class="fa-solid fa-house-chimney-window"></i>
            <?php endif; ?>
            <span><?= htmlspecialchars($siteName) ?></span>
        </a>
        
        <?php 
            $isLanding = (strpos($url, 'boarding/index') !== false || $url === 'boarding' || $url === '');
            $baseUrl = $isLanding ? '' : '/tenant/?url=boarding/index';
        ?>
        <nav class="app-nav">
            <?php if (getenv('INVENTORY_ENABLED') === 'true'): ?><a href="/tenant/?url=property/browse">All spaces</a><?php endif; ?>
            <a href="<?= $baseUrl ?>#features" class="no-loader">Features</a>
            <a href="<?= $baseUrl ?>#how-it-works" class="no-loader">How it Works</a>
            <a href="<?= $baseUrl ?>#pricing" class="no-loader">Pricing</a>
            <a href="<?= $baseUrl ?>#faq" class="no-loader">FAQ</a>
            <a href="/tenant/?url=boarding/explore">Explore</a>
            
            <?php if (empty($currentUserName)): ?>
                <div class="mobile-auth-buttons">
                    <a class="btn-auth btn-login" href="/tenant/?url=auth/login">Login</a>
                </div>
            <?php endif; ?>
        </nav>

        <div class="user-area <?= empty($currentUserName) ? 'guest-user-area' : '' ?>">
            <?php if (!empty($currentUserName)): ?>
                <!-- Notifications Bell -->
                <div class="app-notification-trigger" title="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <span class="app-pulse-badge"></span>
                </div>

                <!-- User Dropdown Premium -->
                <div class="app-user-dropdown" id="appUserDropdown">
                    <div class="user-trigger">
                        <span>Hi, <?= explode(' ', htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'))[0] ?></span>
                        <i class="fa-solid fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                    </div>
                    <div class="app-dropdown-portal">
                        <div class="dropdown-header">
                            <strong><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="user-role-badge">Verified Tenant</span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="/tenant/?url=tenant/profile" class="app-dropdown-item">
                            <i class="fa-regular fa-user"></i> My Profile
                        </a>
                        <a href="/tenant/?url=tenant/dashboard" class="app-dropdown-item">
                            <i class="fa-solid fa-chart-line"></i> Dashboard
                        </a>
                        <a href="/tenant/?url=tenant/settings" class="app-dropdown-item">
                            <i class="fas fa-cog"></i>
                            <div>
                                <span style="display: block; font-weight: 700; color: var(--dash-text-main);">Settings</span>
                                <span style="display: block; font-size: 0.75rem; color: var(--dash-text-muted);">Account preferences</span>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form id="logoutFormApp" action="/tenant/?url=auth/logout" method="POST" style="display: none;"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>"></form>
                        <?php if (getenv('ORGANIZATIONS_ENABLED') === 'true'): ?>
                        <a href="/tenant/?url=organization/index" class="app-dropdown-item"><i class="fas fa-building"></i> Organizations</a>
                        <?php endif; ?>
                        <a href="#" onclick="confirmLogout('logoutFormApp');" class="app-dropdown-item logout-link">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a class="btn-auth btn-login" href="/tenant/?url=auth/login">Login</a>
                <a class="btn-auth btn-register" href="/tenant/?url=auth/register">RentNow</a>
            <?php endif; ?>
        </div>
        
        <div class="menu-toggle">
            <i class="fa-solid fa-bars-staggered"></i>
        </div>
    </div>
</header>


<script>
    // Header Scroll Effect - Unified for App and Dash
    window.addEventListener('scroll', () => {
        const appHeader = document.getElementById('appHeader');
        const dashHeader = document.getElementById('dashHeader');
        
        if (window.scrollY > 50) {
            if (appHeader) appHeader.classList.add('sticky');
            if (dashHeader) dashHeader.classList.add('sticky');
        } else {
            if (appHeader) appHeader.classList.remove('sticky');
            if (dashHeader) dashHeader.classList.remove('sticky');
        }
    });

    // Mobile Menu Toggle Logic
    const menuToggle = document.querySelector('.menu-toggle');
    const appNav = document.querySelector('.app-nav');
    const toggleIcon = menuToggle.querySelector('i');

    menuToggle.addEventListener('click', () => {
        appNav.classList.toggle('active');
        if (appNav.classList.contains('active')) {
            toggleIcon.classList.remove('fa-bars-staggered');
            toggleIcon.classList.add('fa-xmark');
            document.body.style.overflow = 'hidden'; // Prevent scroll
        } else {
            toggleIcon.classList.remove('fa-xmark');
            toggleIcon.classList.add('fa-bars-staggered');
            document.body.style.overflow = ''; // Restore scroll
        }
    });

    // Close menu when a link is clicked
    appNav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            appNav.classList.remove('active');
            toggleIcon.classList.remove('fa-xmark');
            toggleIcon.classList.add('fa-bars-staggered');
            document.body.style.overflow = '';
        });
    });

    // User Dropdown Toggle
    const userDropdown = document.getElementById('appUserDropdown');
    if (userDropdown) {
        userDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        // Close when clicking outside
        document.addEventListener('click', () => {
            userDropdown.classList.remove('show');
        });
    }
</script>

<?php
$mainClass = 'container';
$isLandingSub = (strpos($url, 'boarding/') === 0);

if (strpos($url, 'auth/') === 0) {
    $mainClass = 'auth-main';
} elseif ($isDashboard) {
    $mainClass = 'dash-main';
} elseif ($isLandingSub) {
    $mainClass = 'landing-main';
}
?>
<main class="<?= $mainClass ?>">

<script>
window.isAuthenticated = <?= !empty($currentRole) ? 'true' : 'false' ?>;

function confirmLogout(formId) {
    Feedback.fire({
        title: 'Ready to leave?',
        text: 'Are you sure you want to logout of your account?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--dash-primary)',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        backdrop: 'rgba(15, 23, 42, 0.4)'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>
