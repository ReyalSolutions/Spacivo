<?php
$currentRole = $_SESSION['role'] ?? null;
$currentUserName = $_SESSION['name'] ?? null;
$url = $_GET['url'] ?? '';

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
    <title><?= htmlspecialchars($siteName) ?> Admin</title>
    <link rel="icon" type="image/png" href="/tenant/<?= htmlspecialchars($siteLogo ?? 'public/assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/tenant/public/assets/css/style.css">
    <link rel="stylesheet" href="/tenant/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/tenant/public/assets/css/admin.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<body class="admin-body">
    <script>
        // Check localStorage before render to avoid flicker
        if (localStorage.getItem('adminSidebarCollapsed') === 'true') {
            document.body.classList.add('no-transition'); 
            document.write('<div class="admin-wrapper collapsed">');
        } else {
            document.write('<div class="admin-wrapper">');
        }
    </script>
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <?php if ($siteLogo): ?>
                    <img src="/tenant/<?= htmlspecialchars($siteLogo) ?>" class="sidebar-logo">
                <?php else: ?>
                    <i class="fa-solid fa-house-chimney-crack"></i>
                <?php endif; ?>
                <span class="text-truncate" title="<?= htmlspecialchars($siteName) ?>"><?= htmlspecialchars($siteName) ?></span>
            </div>

            <nav class="admin-nav">
                <!-- Group 1: Core -->
                <div class="admin-nav-group">
                    <div class="admin-nav-label" data-group="core">Core Overview</div>
                    <?php if ($this->hasPermission('view_dashboard')): ?>
                    <a href="/tenant/?url=admin/index" class="nav-pill <?= ($url === 'admin/index') ? 'active' : '' ?>" data-group="core">
                        <i class="fa-solid fa-square-poll-vertical"></i>
                        <span>Dashboard</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($this->hasPermission('view_notifications')): ?>
                    <a href="/tenant/?url=admin/notifications" class="nav-pill <?= ($url === 'admin/notifications') ? 'active' : '' ?>" data-group="core">
                        <i class="fa-solid fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Group 2: Users -->
                <?php 
                $canListUsers = $this->hasPermission('list_users');
                $canManageOwners = $this->hasPermission('manage_owners');
                $canManageTenants = $this->hasPermission('manage_tenants');
                $canManageRoles = $this->hasPermission('manage_roles');
                
                if ($canListUsers || $canManageOwners || $canManageTenants || $canManageRoles):
                    $isUsersGrp = (strpos($url, 'admin/users') === 0 || strpos($url, 'admin/roles') === 0); 
                ?>
                <div class="admin-nav-group">
                    <div class="admin-nav-label" data-group="users">Identity Mgmt</div>
                    <div class="nav-dropdown <?= $isUsersGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isUsersGrp ? 'active' : '' ?>" data-group="users">
                            <div class="pill-main">
                                <i class="fa-solid fa-users-gear"></i>
                                <span>User Management</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                            <?php if ($canListUsers): ?>
                            <a href="/tenant/?url=admin/users" class="nav-pill sub-nav-pill <?= ($url === 'admin/users') ? 'active' : '' ?>">
                                <i class="fa-solid fa-users"></i>
                                <span>All Users</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canManageOwners): ?>
                            <a href="/tenant/?url=admin/users/owners" class="nav-pill sub-nav-pill <?= ($url === 'admin/users/owners') ? 'active' : '' ?>">
                                <i class="fa-solid fa-user-tie"></i>
                                <span>Owners</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canManageTenants): ?>
                            <a href="/tenant/?url=admin/users/tenants" class="nav-pill sub-nav-pill <?= ($url === 'admin/users/tenants') ? 'active' : '' ?>">
                                <i class="fa-solid fa-people-roof"></i>
                                <span>Tenants</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canManageRoles): ?>
                            <a href="/tenant/?url=admin/roles" class="nav-pill sub-nav-pill <?= ($url === 'admin/roles') ? 'active' : '' ?>">
                                <i class="fa-solid fa-user-shield"></i>
                                <span>Roles & Permissions</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Group 3: Assets -->
                <?php 
                $canViewHouses = $this->hasPermission('view_houses');
                $canApproveHouses = $this->hasPermission('approve_houses');
                $canManageBookings = $this->hasPermission('manage_bookings');
                $canManageAmenities = $this->hasPermission('manage_amenities');
                
                if ($canViewHouses || $canApproveHouses || $canManageBookings || $canManageAmenities):
                ?>
                <div class="admin-nav-group">
                    <div class="admin-nav-label" data-group="assets">Property Portfolio</div>
                    
                    <?php if ($canViewHouses || $canApproveHouses): 
                        $isHousesGrp = (strpos($url, 'admin/houses') === 0);
                    ?>
                    <div class="nav-dropdown <?= $isHousesGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isHousesGrp ? 'active' : '' ?>" data-group="assets">
                            <div class="pill-main">
                                <i class="fa-solid fa-city"></i>
                                <span>Boarding Houses</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                            <?php if ($canViewHouses): ?>
                            <a href="/tenant/?url=admin/houses" class="nav-pill sub-nav-pill <?= ($url === 'admin/houses') ? 'active' : '' ?>">
                                <i class="fa-solid fa-list-ul"></i>
                                <span>All Listings</span>
                            </a>
                            <a href="/tenant/?url=admin/map" class="nav-pill sub-nav-pill <?= ($url === 'admin/map') ? 'active' : '' ?>">
                                <i class="fa-solid fa-map-location-dot"></i>
                                <span>Property Map</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canManageAmenities): ?>
                            <a href="/tenant/?url=admin/amenities" class="nav-pill sub-nav-pill <?= ($url === 'admin/amenities') ? 'active' : '' ?>">
                                <i class="fa-solid fa-couch"></i>
                                <span>Global Amenities</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canApproveHouses): ?>
                            <a href="/tenant/?url=admin/houses/pending" class="nav-pill sub-nav-pill <?= ($url === 'admin/houses/pending') ? 'active' : '' ?>">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <span>Pending Approval</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($canManageBookings): 
                        $isBookingsGrp = (strpos($url, 'admin/bookings') === 0);
                    ?>
                    <div class="nav-dropdown <?= $isBookingsGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isBookingsGrp ? 'active' : '' ?>" data-group="assets">
                            <div class="pill-main">
                                <i class="fa-solid fa-rectangle-list"></i>
                                <span>Tenant Management</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                            <a href="/tenant/?url=admin/bookings" class="nav-pill sub-nav-pill <?= ($url === 'admin/bookings') ? 'active' : '' ?>">
                                <i class="fa-solid fa-clipboard-list"></i>
                                <span>Global Tenant Ledger</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Group 4: Finance -->
                <?php 
                $canAuditFinance = $this->hasPermission('audit_finances');
                $canManagePlans = $this->hasPermission('manage_plans');
                $canManageSubs = $this->hasPermission('manage_subscriptions');

                if ($canAuditFinance || $canManagePlans || $canManageSubs):
                ?>
                <div class="admin-nav-group">
                    <div class="admin-nav-label" data-group="finance">Finance & Growth</div>
                    
                    <?php if ($canAuditFinance): 
                        $isPayGrp = (strpos($url, 'admin/payments') === 0);
                    ?>
                    <div class="nav-dropdown <?= $isPayGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isPayGrp ? 'active' : '' ?>" data-group="finance">
                            <div class="pill-main">
                                <i class="fa-solid fa-wallet"></i>
                                <span>Payments</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                            <a href="/tenant/?url=admin/payments/transactions" class="nav-pill sub-nav-pill <?= ($url === 'admin/payments/transactions') ? 'active' : '' ?>">
                                <i class="fa-solid fa-money-bill-transfer"></i>
                                <span>Transactions</span>
                            </a>
                           
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($canManagePlans || $canManageSubs): 
                        $isPlanGrp = (strpos($url, 'admin/plans') === 0 || strpos($url, 'admin/subscriptions') === 0);
                    ?>
                    <div class="nav-dropdown <?= $isPlanGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isPlanGrp ? 'active' : '' ?>" data-group="finance">
                            <div class="pill-main">
                                <i class="fa-solid fa-gem"></i>
                                <span>Plans & Subs</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                            <?php if ($canManagePlans): ?>
                            <a href="/tenant/?url=admin/plans" class="nav-pill sub-nav-pill <?= (strpos($url, 'admin/plans') === 0) ? 'active' : '' ?>">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>Plans</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canManageSubs): ?>
                            <a href="/tenant/?url=admin/subscriptions" class="nav-pill sub-nav-pill <?= (strpos($url, 'admin/subscriptions') === 0) ? 'active' : '' ?>">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                <span>Subscriptions</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canAuditFinance): ?>
                            <a href="/tenant/?url=admin/plan_payments" class="nav-pill sub-nav-pill <?= (strpos($url, 'admin/plan_payments') === 0) ? 'active' : '' ?>">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>Plan Payments Audit</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Group 5: Engagement -->
                <?php 
                $canManageReviews = $this->hasPermission('manage_reviews');
                $canGenerateReports = $this->hasPermission('generate_reports');

                if ($canManageReviews || $canGenerateReports):
                    $isInsightsGrp = (strpos($url, 'admin/reviews') === 0 || strpos($url, 'admin/reports') === 0); 
                ?>
                <div class="admin-nav-group">
                    <div class="admin-nav-label" data-group="core">Insights</div>
                    <div class="nav-dropdown <?= $isInsightsGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isInsightsGrp ? 'active' : '' ?>" data-group="core">
                            <div class="pill-main">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Reviews & Reports</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                            <?php if ($canManageReviews): ?>
                            <a href="/tenant/?url=admin/reviews" class="nav-pill sub-nav-pill <?= ($url === 'admin/reviews') ? 'active' : '' ?>">
                                <i class="fa-solid fa-star"></i>
                                <span>Reviews</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canGenerateReports): ?>
                            <a href="/tenant/?url=admin/reports" class="nav-pill sub-nav-pill <?= ($url === 'admin/reports') ? 'active' : '' ?>">
                                <i class="fa-solid fa-chart-simple"></i>
                                <span>Reports / Analytics</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Group 6: System -->
                <?php 
                $canSystemSettings = $this->hasPermission('system_settings');
                $canFeatureFlags = $this->hasPermission('feature_flags');

                if ($canSystemSettings || $canFeatureFlags):
                    $isSystemGrp = (strpos($url, 'admin/settings') === 0); 
                ?>
                <div class="admin-nav-group">
                    <div class="admin-nav-label" data-group="config">System Admin</div>
                    <div class="nav-dropdown <?= $isSystemGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isSystemGrp ? 'active' : '' ?>" data-group="config">
                            <div class="pill-main">
                                <i class="fa-solid fa-sliders"></i>
                                <span>System Settings</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                            <?php if ($canSystemSettings): ?>
                            <a href="/tenant/?url=admin/settings/general" class="nav-pill sub-nav-pill <?= ($url === 'admin/settings/general') ? 'active' : '' ?>">
                                <i class="fa-solid fa-gear"></i>
                                <span>General Settings</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($canFeatureFlags): ?>
                            <a href="/tenant/?url=admin/settings/features" class="nav-pill sub-nav-pill <?= ($url === 'admin/settings/features') ? 'active' : '' ?>">
                                <i class="fa-solid fa-toggle-on"></i>
                                <span>Feature Controls</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Group 7: Owner Management -->
                <?php if ($currentRole === 'owner' || $currentRole === 'admin'): ?>
                <?php $isMyHousesGrp = (strpos($url, 'owner/houses') === 0); ?>
                <?php $isRoomsGrp = (strpos($url, 'owner/rooms') === 0); ?>
                <?php $isOwnerBookingsGrp = (strpos($url, 'owner/bookings') === 0); ?>
                <?php $isEarningsGrp = (strpos($url, 'owner/payments') === 0); ?>
                <?php $isOwnerSubGrp = (strpos($url, 'owner/subscription') === 0); ?>
                
                <div class="admin-nav-group">
                    <div class="admin-nav-label" data-group="assets">Property Management</div>
                    <div class="nav-dropdown <?= $isMyHousesGrp ? 'open' : '' ?>">
                      
                          <a href="/tenant/?url=owner/houses" class="nav-pill sub-nav-pill <?= ($url === 'owner/houses') ? 'active' : '' ?>">
                                <i class="fa-solid fa-table-list"></i>
                                <span>My Boarding Houses</span>
                            </a>
                         

                        
                       
                    </div>
                    <div class="nav-dropdown <?= $isRoomsGrp ? 'open' : '' ?>">
                        <a href="/tenant/?url=owner/rooms" class="nav-pill sub-nav-pill <?= ($url === 'owner/rooms') ? 'active' : '' ?>">
                                <i class="fa-solid fa-bed"></i>
                                <span>Rooms</span>
                            </a>
                    </div>
                    <?php if ($canManageAmenities): ?>
                    <?php $isAmenitiesGrp = (strpos($url, 'admin/amenities') === 0); ?>
                    <div class="nav-dropdown <?= $isAmenitiesGrp ? 'open' : '' ?>">
                        <a href="/tenant/?url=admin/amenities" class="nav-pill sub-nav-pill <?= ($url === 'admin/amenities') ? 'active' : '' ?>">
                            <i class="fa-solid fa-couch"></i>
                            <span>Amenities</span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="nav-dropdown <?= $isOwnerBookingsGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isOwnerBookingsGrp ? 'active' : '' ?>" data-group="assets">
                            <div class="pill-main">
                                <i class="fa-solid fa-users"></i>
                                <span>Tenant Management</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                          
                            <a href="/tenant/?url=owner/bookings/approved" class="nav-pill sub-nav-pill <?= ($url === 'owner/bookings/approved') ? 'active' : '' ?>">
                                <i class="fa-solid fa-user-check"></i>
                                <span>Active Tenants</span>
                            </a>
                            <a href="/tenant/?url=owner/bookings/history" class="nav-pill sub-nav-pill <?= ($url === 'owner/bookings/history') ? 'active' : '' ?>">
                                <i class="fa-solid fa-history"></i>
                                <span>Tenant History</span>
                            </a>
                        </div>
                    </div>
                    <div class="nav-dropdown <?= $isEarningsGrp ? 'open' : '' ?>">
                        <div class="nav-pill nav-pill-dropdown <?= $isEarningsGrp ? 'active' : '' ?>" data-group="finance">
                            <div class="pill-main">
                                <i class="fa-solid fa-sack-dollar"></i>
                                <span>Earnings</span>
                            </div>
                            <i class="fa-solid fa-chevron-right dropdown-chevron"></i>
                        </div>
                        <div class="sub-nav">
                            <a href="/tenant/?url=owner/payments/earnings" class="nav-pill sub-nav-pill <?= ($url === 'owner/payments/earnings') ? 'active' : '' ?>">
                                <i class="fa-solid fa-money-bill-trend-up"></i>
                                <span>Total Earnings</span>
                            </a>
                            <a href="/tenant/?url=owner/payments/transactions" class="nav-pill sub-nav-pill <?= ($url === 'owner/payments/transactions') ? 'active' : '' ?>">
                                <i class="fa-solid fa-receipt"></i>
                                <span>Transactions</span>
                            </a>
                        </div>
                    </div>
                    <a href="/tenant/?url=owner/tenants" class="nav-pill <?= ($url === 'owner/tenants') ? 'active' : '' ?>" data-group="users">
                        <i class="fa-solid fa-user-group"></i>
                        <span>Current Tenants</span>
                    </a>
                    <a href="/tenant/?url=owner/messages" class="nav-pill <?= ($url === 'owner/messages') ? 'active' : '' ?>" data-group="core">
                        <i class="fa-solid fa-comments"></i>
                        <span>Messages (Chat)</span>
                    </a>
                    <div class="nav-dropdown <?= $isOwnerSubGrp ? 'open' : '' ?>">
                       
                        <div class="sub-nav">
                            <a href="/tenant/?url=owner/subscription" class="nav-pill sub-nav-pill <?= ($url === 'owner/subscription') ? 'active' : '' ?>">
                                <i class="fa-solid fa-id-badge"></i>
                                <span>Current Plan</span>
                            </a>
                            <a href="/tenant/?url=owner/subscription/upgrade" class="nav-pill sub-nav-pill <?= ($url === 'owner/subscription/upgrade') ? 'active' : '' ?>">
                                <i class="fa-solid fa-rocket"></i>
                                <span>Upgrade Plan</span>
                            </a>
                        </div>
                    </div>
                    <a href="/tenant/?url=owner/analytics" class="nav-pill <?= ($url === 'owner/analytics') ? 'active' : '' ?>" data-group="core">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Analytics (Premium)</span>
                    </a>
                </div>
                <?php endif; ?>

                <div class="admin-nav-group">
                    <div class="admin-nav-label" data-group="core">General</div>
                    <?php if ($this->hasPermission('view_logs')): ?>
                    <a href="/tenant/?url=admin/logs" class="nav-pill <?= ($url === 'admin/logs') ? 'active' : '' ?>" data-group="config">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Activity Logs</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($this->hasPermission('manage_profile')): ?>
                    <a href="/tenant/?url=admin/profile" class="nav-pill <?= ($url === 'admin/profile') ? 'active' : '' ?>" data-group="config">
                        <i class="fa-solid fa-circle-user"></i>
                        <span>My Profile</span>
                    </a>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="admin-sidebar-footer">
                <div class="profile-unit">
                    <div class="user-img">
                        <?= strtoupper(substr($currentUserName ?? 'S', 0, 1)) ?>
                    </div>
                    <div class="user-meta" title="<?= htmlspecialchars($currentUserName ?? 'Staff', ENT_QUOTES, 'UTF-8') ?>">
                        <span class="u-name"><?= htmlspecialchars($currentUserName ?? 'Staff', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="u-role"><?= htmlspecialchars($currentRole ?? 'Member', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <form id="logoutFormFooter" action="/tenant/?url=auth/logout" method="POST" style="display: none;"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>"></form>
                    <div class="logout-icon" onclick="confirmLogout('logoutFormFooter');" title="Sign Out">
                        <i class="fa-solid fa-power-off"></i>
                    </div>
                </div>
            </div>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-top-bar">
                <div class="top-bar-left">
                    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>
                    
                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search for bookings, tenants, or properties...">
                    </div>
                </div>

                <div class="top-bar-right">
                    <div class="icon-action animate-pulse" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        <div class="notification-dot"></div>
                    </div>
                    
                    <div style="height: 24px; width: 1px; background: var(--border-color); margin: 0 8px;"></div>
                    
                    <div class="dropdown">
                        <button class="admin-user-card border-0" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer; background: white;">
                            <div class="admin-user-info" style="text-align: right;">
                                <span class="admin-user-name"><?= htmlspecialchars($currentUserName ?? 'Staff', ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="admin-user-role"><?= htmlspecialchars($currentRole ?? 'Member', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="admin-user-avatar">
                                <?= strtoupper(substr($currentUserName ?? 'S', 0, 1)) ?>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end premium-dropdown shadow-lg px-2" aria-labelledby="userDropdown">
                            <li>
                                <div class="dropdown-user-header">
                                    <div class="header-avatar">
                                        <?= strtoupper(substr($currentUserName ?? 'S', 0, 1)) ?>
                                    </div>
                                    <div class="header-meta">
                                        <div class="header-name"><?= htmlspecialchars($currentUserName ?? 'Staff', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="header-role text-primary"><?= htmlspecialchars($currentRole ?? 'Member', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>
                            </li>
                            <li><a class="dropdown-item" href="/tenant/?url=admin/profile"><i class="fa-solid fa-circle-user"></i> My Profile</a></li>
                            <?php if (getenv('ORGANIZATIONS_ENABLED') === 'true'): ?>
                            <li><a class="dropdown-item" href="/tenant/?url=organization/index"><i class="fas fa-building"></i> Organizations</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/tenant/?url=admin/settings/general"><i class="fa-solid fa-sliders"></i> Account Settings</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-circle-question"></i> Help Center</a></li>
                            <li><hr class="dropdown-divider mx-2"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); confirmLogout('logoutFormAdmin');">
                                    <i class="fa-solid fa-power-off"></i> Sign Out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <div class="admin-content">
                    <div class="admin-page-header mb-4">
                        <div class="admin-breadcrumb d-flex align-items-center gap-2 mb-2">
                            <span class="text-muted small fw-semibold"><?= htmlspecialchars($roleLabel ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
                            <i class="fa-solid fa-chevron-right text-muted" style="font-size: 0.6rem;"></i>
                            <?php 
                                $segment = explode('/', $url)[1] ?? 'Index';
                                if ($segment === 'index') $segment = 'Dashboard';
                                if (strtolower($segment) === 'bookings') $segment = 'Tenants';
                            ?>
                            <span class="small fw-bold text-dark"><?= ucfirst($segment) ?></span>
                        </div>
                        <?php if (!isset($hideAdminHeaderTitle)): ?>
                            <h1 class="h3 fw-bold m-0"><?= $pageTitle ?? ucfirst(explode('/', $url)[1] ?? 'Management') ?></h1>
                        <?php endif; ?>
                    </div>

                <form id="logoutFormAdmin" action="/tenant/?url=auth/logout" method="POST" style="display: none;"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>"></form>

                <script>
                    $(document).ready(function() {
                        // Dropdown Toggling
                        $('.nav-pill-dropdown').click(function() {
                            const parent = $(this).closest('.nav-dropdown');
                            parent.toggleClass('open');
                        });

                        // Auto-open current group
                        $('.sub-nav-pill.active').closest('.nav-dropdown').addClass('open');
                    });

                    function confirmLogout(formId) {
                        Swal.fire({
                            title: 'Terminate Session?',
                            text: "You are about to sign out of the administrative dashboard. Any unsaved changes may be lost.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#2563eb',
                            cancelButtonColor: '#ff4b5c',
                            confirmButtonText: 'Yes, Sign Out',
                            cancelButtonText: 'Stay Logged In',
                            reverseButtons: true,
                            background: '#ffffff',
                            color: '#0f172a',
                            iconColor: '#f43f5e',
                            customClass: {
                                popup: 'premium-swal-popup',
                                title: 'premium-swal-title',
                                confirmButton: 'premium-swal-confirm'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById(formId).submit();
                            }
                        });
                    }
                </script>
