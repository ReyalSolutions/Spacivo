<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between px-4 py-3">
            <a href="index.php" class="text-nowrap logo-img d-flex align-items-center gap-2 text-decoration-none">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                    <i class="ti ti-building-community fs-6"></i>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bold fs-5 text-dark lh-1" style="letter-spacing: -0.5px;"><?= htmlspecialchars($siteName ?? 'StayHub') ?></span>
                    <small class="text-muted" style="font-size: 0.68rem; font-weight: 600; letter-spacing: 0.5px;">ADMIN PANEL</small>
                </div>
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-8"></i>
            </div>
        </div>

        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
            <ul id="sidebarnav">
                <!-- CORE OVERVIEW -->
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">OVERVIEW</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="index.php">
                        <span><i class="ti ti-layout-dashboard"></i></span>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'notifications.php') ? 'active' : '' ?>" href="notifications.php">
                        <span><i class="ti ti-bell"></i></span>
                        <span class="hide-menu">Notifications</span>
                    </a>
                </li>

                <!-- IDENTITY & ACCESS -->
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">IDENTITY & ACCESS</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'users.php') ? 'active' : '' ?>" href="users.php">
                        <span><i class="ti ti-users"></i></span>
                        <span class="hide-menu">User Accounts</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'roles.php') ? 'active' : '' ?>" href="roles.php">
                        <span><i class="ti ti-shield-lock"></i></span>
                        <span class="hide-menu">Roles & Permissions</span>
                    </a>
                </li>

                <!-- PROPERTIES -->
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">PROPERTY MANAGEMENT</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'houses.php') ? 'active' : '' ?>" href="houses.php">
                        <span><i class="ti ti-home-2"></i></span>
                        <span class="hide-menu">Boarding Houses</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'amenities.php') ? 'active' : '' ?>" href="amenities.php">
                        <span><i class="ti ti-sparkles"></i></span>
                        <span class="hide-menu">Amenities Catalog</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'reviews.php') ? 'active' : '' ?>" href="reviews.php">
                        <span><i class="ti ti-star"></i></span>
                        <span class="hide-menu">Reviews & Ratings</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'map.php') ? 'active' : '' ?>" href="map.php">
                        <span><i class="ti ti-map-pin"></i></span>
                        <span class="hide-menu">Property Map</span>
                    </a>
                </li>

                <!-- OPERATIONS -->
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">OPERATIONS</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'bookings.php') ? 'active' : '' ?>" href="bookings.php">
                        <span><i class="ti ti-calendar-event"></i></span>
                        <span class="hide-menu">Bookings</span>
                    </a>
                </li>

                <!-- FINANCIAL -->
                <?php
                $billingPages = ['plans.php', 'subscriptions.php', 'payments.php', 'plan_payments.php', 'payment_settings.php'];
                $isBillingActive = in_array($currentPage, $billingPages);
                ?>
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">FINANCIAL & BILLING</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= $isBillingActive ? 'active' : '' ?>" href="javascript:void(0)" onclick="toggleSubmenu(this, 'billingSubmenu')">
                        <span><i class="ti ti-credit-card"></i></span>
                        <span class="hide-menu d-flex align-items-center justify-content-between w-100">
                            Billing Center
                            <i class="ti ti-chevron-down ms-auto" style="font-size:13px; transition:transform 0.25s ease; <?= $isBillingActive ? 'transform:rotate(180deg);' : '' ?>"></i>
                        </span>
                    </a>
                    <ul id="billingSubmenu" style="list-style:none; padding-left:16px; overflow:hidden; max-height:<?= $isBillingActive ? '500px' : '0' ?>; transition:max-height 0.3s ease;">
                        <li class="sidebar-item">
                            <a class="sidebar-link <?= ($currentPage === 'plans.php') ? 'active' : '' ?>" href="plans.php">
                                <span><i class="ti ti-tags"></i></span>
                                <span class="hide-menu">Subscription Plans</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link <?= ($currentPage === 'subscriptions.php') ? 'active' : '' ?>" href="subscriptions.php">
                                <span><i class="ti ti-crown"></i></span>
                                <span class="hide-menu">Owner Subscriptions</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link <?= ($currentPage === 'payments.php') ? 'active' : '' ?>" href="payments.php">
                                <span><i class="ti ti-receipt-2"></i></span>
                                <span class="hide-menu">Tenant Payments</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link <?= ($currentPage === 'plan_payments.php') ? 'active' : '' ?>" href="plan_payments.php">
                                <span><i class="ti ti-file-invoice"></i></span>
                                <span class="hide-menu">Plan Transactions</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link <?= ($currentPage === 'payment_settings.php') ? 'active' : '' ?>" href="payment_settings.php">
                                <span><i class="ti ti-adjustments"></i></span>
                                <span class="hide-menu">Payment Gateways</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- REPORTS & SYSTEM -->
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">REPORTS & SYSTEM</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'reports.php') ? 'active' : '' ?>" href="reports.php">
                        <span><i class="ti ti-chart-bar"></i></span>
                        <span class="hide-menu">Analytics & Reports</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'logs.php') ? 'active' : '' ?>" href="logs.php">
                        <span><i class="ti ti-history"></i></span>
                        <span class="hide-menu">Audit Logs</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'settings.php') ? 'active' : '' ?>" href="settings.php">
                        <span><i class="ti ti-settings"></i></span>
                        <span class="hide-menu">System Settings</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link <?= ($currentPage === 'profile.php') ? 'active' : '' ?>" href="profile.php">
                        <span><i class="ti ti-user-check"></i></span>
                        <span class="hide-menu">Admin Profile</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
