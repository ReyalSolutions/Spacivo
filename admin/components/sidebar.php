<?php
$currentRole = strtolower($_SESSION['role'] ?? '');
$portalCan = $portalCan ?? static function (string $permission): bool { return function_exists('hasPermission') && hasPermission($permission); };
$portalAdmin = $currentRole === 'admin';
$portalOwner = $currentRole === 'owner';
$portalGroups = [];
$portalAdd = static function ($group, $label, $icon, $href, $allowed) use (&$portalGroups): void {
    if ($allowed) $portalGroups[$group][] = [$label, $icon, $href];
};
$portalAdd('Overview', 'Dashboard', 'layout-dashboard', $portalAdmin ? '/tenant/admin/index.php' : '/tenant/?url=admin/index', $portalAdmin || $portalCan('view_dashboard'));
$portalAdd('Overview', 'Notifications', 'bell', $portalAdmin ? '/tenant/admin/notifications.php' : '/tenant/?url=admin/notifications', $portalAdmin || $portalCan('view_notifications'));
$portalAdd('Identity & Access', 'User Accounts', 'users', '/tenant/admin/users.php', $portalAdmin && $portalCan('list_users'));
$portalAdd('Identity & Access', 'Roles & Permissions', 'shield-lock', '/tenant/admin/roles.php', $portalAdmin && $portalCan('manage_roles'));
$portalAdd('Property Management', 'Boarding Houses', 'home', $portalAdmin ? '/tenant/admin/houses.php' : '/tenant/?url=owner/houses', ($portalAdmin && $portalCan('view_houses')) || $portalOwner);
$portalAdd('Property Management', 'Rooms', 'door', '/tenant/?url=owner/rooms', $portalOwner);
$portalAdd('Property Management', 'Amenities Catalog', 'sparkles', $portalAdmin ? '/tenant/admin/amenities.php' : '/tenant/?url=admin/amenities', ($portalAdmin && $portalCan('manage_amenities')) || $portalOwner);
$portalAdd('Property Management', 'Reviews & Ratings', 'star', '/tenant/admin/reviews.php', $portalAdmin && $portalCan('manage_reviews'));
$portalAdd('Property Management', 'Property Map', 'map-pin', '/tenant/admin/map.php', $portalAdmin && $portalCan('view_houses'));
$portalAdd('Property Management', 'Organizations', 'building-community', '/tenant/?url=organization/index', getenv('ORGANIZATIONS_ENABLED') === 'true');
$portalAdd('Property Management', 'Space Categories', 'category', '/tenant/?url=category/index', $portalAdmin && getenv('CATEGORIES_ENABLED') === 'true');
$portalAdd('Property Management', 'Inventory Review', 'building', '/tenant/?url=property/index', $portalAdmin && getenv('INVENTORY_ENABLED') === 'true');
$portalAdd('Operations', 'Bookings', 'calendar', $portalAdmin ? '/tenant/admin/bookings.php' : '/tenant/?url=owner/bookings', ($portalAdmin && $portalCan('manage_bookings')) || $portalOwner);
$portalAdd('Operations', 'Booking History', 'history', '/tenant/?url=owner/bookings_history', $portalOwner);
$portalAdd('Operations', 'Tenants', 'users', '/tenant/?url=owner/tenants', $portalOwner);
$portalAdd('Financial & Billing', 'Subscription Plans', 'crown', '/tenant/admin/plans.php', $portalAdmin && $portalCan('manage_plans'));
$portalAdd('Financial & Billing', 'Subscriptions', 'file-invoice', $portalAdmin ? '/tenant/admin/subscriptions.php' : '/tenant/?url=admin/subscriptions', ($portalAdmin && $portalCan('manage_subscriptions')) || $portalOwner);
$portalAdd('Financial & Billing', 'Payments', 'credit-card', $portalAdmin ? '/tenant/admin/payments.php' : '/tenant/?url=owner/payments', ($portalAdmin && $portalCan('audit_finances')) || $portalOwner);
$portalAdd('Financial & Billing', 'Plan Payments', 'receipt', '/tenant/admin/plan_payments.php', $portalAdmin && $portalCan('manage_subscriptions'));
$portalAdd('Financial & Billing', 'Payment Settings', 'settings', '/tenant/admin/payment_settings.php', $portalAdmin && $portalCan('system_settings'));
$portalAdd('Reports & System', 'Reports', 'chart-line', '/tenant/admin/reports.php', $portalAdmin && $portalCan('generate_reports'));
$portalAdd('Reports & System', 'Activity Logs', 'activity', '/tenant/admin/logs.php', $portalAdmin && $portalCan('view_logs'));
$portalAdd('Reports & System', 'Settings', 'settings', '/tenant/admin/settings.php', $portalAdmin && $portalCan('system_settings'));
$portalAdd('Reports & System', 'My Profile', 'user', $portalAdmin ? '/tenant/admin/profile.php' : '/tenant/?url=admin/profile', $portalAdmin || $portalOwner || $portalCan('manage_profile'));
$portalCurrentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$portalCurrentRoute = trim($_GET['url'] ?? '', '/');
?>
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between px-4 py-3">
            <a href="<?= $currentRole === 'admin' ? '/tenant/admin/index.php' : '/tenant/?url=admin/index' ?>" class="text-nowrap logo-img d-flex align-items-center gap-2 text-decoration-none">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                    <i class="ti ti-building-community fs-6"></i>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bold fs-5 text-dark lh-1" style="letter-spacing: -0.5px;"><?= htmlspecialchars($siteName ?? 'StayHub') ?></span>
                    <small class="text-muted" style="font-size: 0.68rem; font-weight: 600; letter-spacing: 0.5px;"><?= htmlspecialchars(strtoupper($currentRole), ENT_QUOTES, 'UTF-8') ?> PANEL</small>
                </div>
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-8"></i>
            </div>
        </div>

        <nav class="sidebar-nav scroll-sidebar" data-simplebar=""><ul id="sidebarnav">
        <?php foreach ($portalGroups as $portalGroup => $portalItems): ?>
        <li class="nav-small-cap"><span class="hide-menu"><?= htmlspecialchars(strtoupper($portalGroup), ENT_QUOTES, 'UTF-8') ?></span></li>
        <?php foreach ($portalItems as $portalItem):
            $portalTargetRoute = [];
            parse_str(parse_url($portalItem[2], PHP_URL_QUERY) ?? '', $portalTargetRoute);
            $portalActive = isset($portalTargetRoute['url']) ? $portalCurrentRoute === $portalTargetRoute['url'] : $portalCurrentPath === $portalItem[2];
        ?>
        <li class="sidebar-item"><a class="sidebar-link <?= $portalActive ? 'active' : '' ?>" href="<?= htmlspecialchars($portalItem[2], ENT_QUOTES, 'UTF-8') ?>" <?= $portalActive ? 'aria-current="page"' : '' ?>>
        <span><i class="ti ti-<?= htmlspecialchars($portalItem[1], ENT_QUOTES, 'UTF-8') ?>"></i></span><span class="hide-menu"><?= htmlspecialchars($portalItem[0], ENT_QUOTES, 'UTF-8') ?></span></a></li>
        <?php endforeach; endforeach; ?>
        </ul></nav>
    </div>
</aside>
