<header class="app-header">
    <nav class="navbar navbar-expand navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
                <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                    <i class="ti ti-menu-2"></i>
                </a>
            </li>
            <li class="nav-item d-none d-lg-block">
                <div class="d-flex align-items-center gap-2 text-muted px-2">
                    <span class="badge bg-light-primary text-primary fw-semibold px-3 py-2 rounded-pill">
                        <i class="ti ti-shield-check me-1"></i> Management Console
                    </span>
                </div>
            </li>
        </ul>
        <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-between gap-1">
                <li class="nav-item d-none d-md-block me-3">
                    <div class="d-flex align-items-center text-muted small">
                        <i class="ti ti-calendar me-1"></i>
                        <span id="current-date" class="me-3 fw-medium"></span>
                        <i class="ti ti-clock me-1"></i>
                        <span id="current-time" class="fw-medium"></span>
                    </div>
                </li>
                
                <!-- Front Site Jump -->
                <li class="nav-item">
                    <a class="nav-link nav-icon-hover" href="/tenant/" target="_blank" title="View Public Site">
                        <i class="ti ti-external-link"></i>
                    </a>
                </li>

                <!-- Notifications -->
                <li class="nav-item">
                    <a class="nav-link nav-icon-hover position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal" href="javascript:void(0)" title="Notifications">
                        <i class="ti ti-bell-ringing"></i>
                        <div class="notification bg-primary rounded-circle"></div>
                    </a>
                </li>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown ms-2">
                    <a class="nav-link nav-icon-hover d-flex align-items-center gap-2 pe-0" href="javascript:void(0)" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;font-size:0.9rem;">
                            <?= strtoupper(substr($userName ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="d-none d-lg-block text-start">
                            <span class="d-block fw-semibold text-dark fs-3 lh-1"><?= $userName ?></span>
                            <small class="text-muted" style="font-size:0.75rem;"><?= $userRole ?></small>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up shadow-lg border-0 py-2" aria-labelledby="userDropdown" style="min-width: 240px; border-radius: 12px;">
                        <div class="px-3 py-2 border-bottom mb-1">
                            <span class="d-block fw-bold text-dark"><?= $userName ?></span>
                            <small class="text-muted d-block"><?= $userEmail ?></small>
                            <span class="badge bg-light-primary text-primary mt-1" style="font-size:0.7rem;"><?= $userRole ?></span>
                        </div>
                        <div class="message-body">
                            <a href="profile.php" class="d-flex align-items-center gap-2 dropdown-item py-2 px-3">
                                <i class="ti ti-user fs-5 text-primary"></i>
                                <span class="fs-3">My Profile</span>
                            </a>
                            <a href="settings.php" class="d-flex align-items-center gap-2 dropdown-item py-2 px-3">
                                <i class="ti ti-settings fs-5 text-secondary"></i>
                                <span class="fs-3">System Settings</span>
                            </a>
                            <a href="logs.php" class="d-flex align-items-center gap-2 dropdown-item py-2 px-3">
                                <i class="ti ti-activity fs-5 text-info"></i>
                                <span class="fs-3">Activity Logs</span>
                            </a>
                            <div class="dropdown-divider my-1"></div>
                            <?php if (getenv('CATEGORIES_ENABLED') === 'true'): ?>
                            <a href="/tenant/?url=category/index" class="d-flex align-items-center gap-2 dropdown-item py-2 px-3">Space categories</a>
                            <?php endif; ?>
                            <?php if (getenv('ORGANIZATIONS_ENABLED') === 'true'): ?>
                            <a href="/tenant/?url=organization/index" class="d-flex align-items-center gap-2 dropdown-item py-2 px-3">Organizations</a>
                            <?php endif; ?>
                            <a href="#" class="d-flex align-items-center gap-2 dropdown-item py-2 px-3 text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="ti ti-logout fs-5"></i>
                                <span class="fs-3">Logout</span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="logoutModalLabel">Terminate Session?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3 text-muted">
                Are you sure you want to log out of the StayHub administration console? You will need to log in again to access system controls.
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                <form action="/tenant/?url=auth/logout" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-danger px-4" style="border-radius:8px;">Logout Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius:16px;">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-bell text-primary fs-5"></i>
                    <h5 class="modal-title fw-bold" id="notificationModalLabel">System Notifications</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex flex-column gap-2">
                    <div class="p-3 rounded-3 bg-light d-flex gap-3 align-items-start">
                        <div class="p-2 bg-light-primary text-primary rounded-circle"><i class="ti ti-info-circle"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold fs-3">System Operating Normally</h6>
                            <p class="mb-0 text-muted small">StayHub admin console running smoothly with live database synchronization.</p>
                            <small class="text-muted" style="font-size:0.7rem;">Just now</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateHeaderDateTime() {
        const now = new Date();
        const dateOptions = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const dateEl = document.getElementById('current-date');
        const timeEl = document.getElementById('current-time');
        if (dateEl) dateEl.textContent = now.toLocaleDateString('en-US', dateOptions);
        if (timeEl) timeEl.textContent = now.toLocaleTimeString('en-US', timeOptions);
    }
    updateHeaderDateTime();
    setInterval(updateHeaderDateTime, 1000);
</script>
