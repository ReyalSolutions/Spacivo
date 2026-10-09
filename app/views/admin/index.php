<?php require __DIR__ . '/../layouts/admin_header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-0">
    <!-- Personalized Welcome Section -->
    <div class="welcome-section mb-4 p-4 rounded-4 bg-white border shadow-sm d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-4">
            <div class="welcome-avatar">
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <i class="fa-solid fa-user-shield"></i>
                <?php else: ?>
                    <i class="fa-solid fa-user-tie"></i>
                <?php endif; ?>
            </div>
            <div>
                <h2 class="h4 fw-bold mb-1 text-dark">Welcome back, <?= htmlspecialchars($firstName ?? 'User') ?>! 👋</h2>
                <p class="text-muted mb-0 small">Here's a snapshot of your <?= strtolower($roleLabel ?? 'role') ?> activities and metrics for today.</p>
            </div>
        </div>
        <div class="welcome-date text-end d-none d-md-block">
            <div class="fw-bold text-dark"><?= date('F j, Y') ?></div>
            <div class="text-muted small"><?= date('l') ?></div>
        </div>
    </div>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <!-- ==========================================
             ADMIN DASHBOARD LAYOUT
             ========================================== -->
        <!-- 1. TOP SUMMARY CARDS (ADMIN) -->
        <div class="row g-4 mb-4">
            <!-- Users Card -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="premium-stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Total Users</span>
                        <span class="stat-value"><?= number_format((float)($totalUsers ?? 0)) ?></span>
                        <div class="stat-subtext">
                            <span class="text-primary"><?= (int)($totalOwners ?? 0) ?> Owners</span> • 
                            <span class="text-success"><?= (int)($totalTenants ?? 0) ?> Tenants</span>
                        </div>
                    </div>
                    <div class="stat-icon bg-blue-subtle text-blue">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Houses Card -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="premium-stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Boarding Houses</span>
                        <span class="stat-value"><?= number_format((float)($totalHouses ?? 0)) ?></span>
                        <span class="stat-trend <?= ($pendingApprovals ?? 0) > 0 ? 'down' : 'up' ?>">
                            <i class="fa-solid <?= ($pendingApprovals ?? 0) > 0 ? 'fa-clock' : 'fa-check-double' ?>"></i> 
                            <?= (int)($pendingApprovals ?? 0) ?> Pending
                        </span>
                    </div>
                    <div class="stat-icon bg-emerald-subtle text-emerald">
                        <i class="fa-solid fa-hotel"></i>
                    </div>
                </div>
            </div>

            <!-- Bookings Card -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="premium-stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Total Rentals</span>
                        <span class="stat-value"><?= number_format((float)($totalBookings ?? 0)) ?></span>
                        <span class="stat-trend up"><i class="fa-solid fa-caret-up"></i> Live Tracking</span>
                    </div>
                    <div class="stat-icon bg-purple-subtle text-purple">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="premium-stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Total Revenue</span>
                        <span class="stat-value">₱<?= number_format((float)($totalRevenue ?? 0)) ?></span>
                        <span class="stat-trend up"><i class="fa-solid fa-bolt"></i> Global Growth</span>
                    </div>
                    <div class="stat-icon bg-amber-subtle text-amber">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. ANALYTICS ROW -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8">
                <div class="premium-stat-card d-block p-4 overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="m-0 fw-bold fs-5">Revenue Analytics</h3>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active">Monthly</button>
                            <button class="btn btn-outline-secondary">Daily</button>
                        </div>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="premium-stat-card d-block p-4 overflow-hidden h-100">
                    <h3 class="m-0 fw-bold fs-5 mb-4">User Growth</h3>
                    <div style="height: 300px;">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. ACTIONABLE ROW -->
        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="premium-stat-card d-block p-0 overflow-hidden h-100">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h3 class="m-0 fw-bold fs-5 text-amber">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>Pending Approvals
                        </h3>
                        <span class="badge bg-amber-subtle text-amber"><?= count($approvalList ?? []) ?> Awaiting</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Property</th>
                                    <th>Owner</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($approvalList)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No pending approvals.</td>
                                </tr>
                                <?php else: foreach ($approvalList as $house): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($house['name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($house['address']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($house['first_name'] . ' ' . $house['last_name']) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-success px-3 btn-approve-house"
                                                data-house-id="<?= $house['id'] ?>"
                                                data-house-name="<?= htmlspecialchars($house['name']) ?>">
                                                <i class="fa-solid fa-check me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-reject-house"
                                                data-house-id="<?= $house['id'] ?>"
                                                data-house-name="<?= htmlspecialchars($house['name']) ?>">
                                                <i class="fa-solid fa-xmark me-1"></i>Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="premium-stat-card d-block p-0 overflow-hidden h-100">
                    <div class="p-4 border-bottom">
                        <h3 class="m-0 fw-bold fs-5">Live Activity Feed</h3>
                    </div>
                    <div class="p-3">
                        <?php foreach (($recentActivities ?? []) as $act): ?>
                        <div class="activity-item d-flex gap-3 p-3 mb-2 rounded-3 interact-hover">
                            <div class="activity-icon-wrap bg-<?= $act['color'] ?>-subtle text-<?= $act['color'] ?>">
                                <i class="fa-solid <?= $act['icon'] ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text text-dark fw-semibold"><?= htmlspecialchars($act['text']) ?></div>
                                <div class="activity-time text-muted small"><?= $act['time'] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif (($_SESSION['role'] ?? '') === 'owner'): ?>
        <!-- ==========================================
             OWNER DASHBOARD LAYOUT
             ========================================== -->
        <!-- 1. TOP SUMMARY CARDS (OWNER) -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="premium-stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Properties</span>
                        <span class="stat-value"><?= (int)($totalHouses ?? 0) ?></span>
                        <span class="stat-subtext text-muted">Managed Units</span>
                    </div>
                    <div class="stat-icon bg-emerald-subtle text-emerald"><i class="fa-solid fa-building"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="premium-stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Occupancy</span>
                        <span class="stat-value"><?= (int)($occupancyRate ?? 0) ?>%</span>
                        <span class="stat-subtext text-emerald"><?= (int)($occupiedSlots ?? 0) ?>/<?= (int)($totalRooms * 2) ?> Slots</span>
                    </div>
                    <div class="stat-icon bg-blue-subtle text-blue"><i class="fa-solid fa-bed"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="premium-stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Monthly Earnings</span>
                        <span class="stat-value">₱<?= number_format((float)($earnings ?? 0)) ?></span>
                        <span class="stat-trend up"><i class="fa-solid fa-arrow-trend-up"></i> Current Month</span>
                    </div>
                    <div class="stat-icon bg-amber-subtle text-amber"><i class="fa-solid fa-money-bill-trend-up"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="premium-stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Pending Requests</span>
                        <span class="stat-value"><?= count($pendingRequests ?? []) ?></span>
                        <span class="stat-subtext text-amber">Immediate Action</span>
                    </div>
                    <div class="stat-icon bg-purple-subtle text-purple"><i class="fa-solid fa-envelope-open-text"></i></div>
                </div>
            </div>
        </div>

        <!-- 2. ANALYTICS ROW (OWNER) -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8">
                <div class="premium-stat-card d-block p-4 h-100">
                    <h3 class="m-0 fw-bold fs-5 mb-4">Earnings Analytics</h3>
                    <div style="height: 300px;"><canvas id="earningsChart"></canvas></div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="premium-stat-card d-block p-4 h-100">
                    <h3 class="m-0 fw-bold fs-5 mb-4">Occupancy Rate</h3>
                    <div style="height: 300px;"><canvas id="occupancyChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- 3. ACTIONABLE ROW (OWNER) -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-7">
                <div class="premium-stat-card d-block p-0 overflow-hidden h-100">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h3 class="m-0 fw-bold fs-5">Booking Requests</h3>
                        <a href="#" class="text-primary small fw-bold">View History</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php if (empty($pendingRequests)): ?>
                                    <tr><td class="text-center py-4 text-muted">No new booking requests.</td></tr>
                                <?php else: foreach (array_slice($pendingRequests, 0, 4) as $req): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold"><?= htmlspecialchars($req['tenant_name'] ?? 'Tenant') ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($req['room_name'] ?? 'Unit') ?></div>
                                        </td>
                                        <td><span class="badge bg-amber-subtle text-amber">Pending</span></td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-success btn-approve-booking"
                                                data-booking-id="<?= $req['id'] ?>"
                                                data-tenant="<?= htmlspecialchars($req['tenant_name'] ?? '') ?>">
                                                <i class="fa-solid fa-check me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger ms-2 btn-reject-booking"
                                                data-booking-id="<?= $req['id'] ?>"
                                                data-tenant="<?= htmlspecialchars($req['tenant_name'] ?? '') ?>">
                                                <i class="fa-solid fa-xmark me-1"></i>Reject
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="premium-stat-card d-block p-0 overflow-hidden h-100">
                    <div class="p-4 border-bottom"><h3 class="m-0 fw-bold fs-5">Recent Activities</h3></div>
                    <div class="p-3">
                        <?php foreach (($recentActivities ?? []) as $act): ?>
                            <div class="activity-item d-flex gap-3 p-3 mb-2 rounded-3 interact-hover">
                                <div class="activity-icon-wrap bg-<?= $act['color'] ?>-subtle text-<?= $act['color'] ?>"><i class="fa-solid <?= $act['icon'] ?>"></i></div>
                                <div>
                                    <div class="text-dark fw-semibold small"><?= htmlspecialchars($act['text']) ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><?= $act['time'] ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. BOTTOM ROW (ROOMS & HOUSES) -->
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="premium-stat-card d-block p-0 h-100">
                    <div class="p-4 border-bottom d-flex justify-content-between">
                        <h3 class="m-0 fw-bold fs-5">My Boarding Houses</h3>
                    </div>
                    <div class="p-3">
                        <?php foreach (array_slice($myHouses ?? [], 0, 3) as $h): ?>
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 mb-2">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($h['name']) ?></div>
                                    <div class="text-muted small mb-1">
                                        Utilized: <span class="text-primary fw-bold"><?= (int)$h['utilized_slots'] ?></span> | 
                                        Available: <span class="text-success fw-bold"><?= (int)$h['available_slots'] ?></span>
                                    </div>
                                    <span class="badge <?= $h['status'] === 'approved' ? 'bg-success-subtle text-success' : 'bg-amber-subtle text-amber' ?>">
                                        <?= ucfirst($h['status']) ?>
                                    </span>
                                </div>
                                <a href="/tenant/?url=admin/houses" class="btn btn-sm btn-primary px-3 shadow-sm">Manage</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="premium-stat-card d-block p-0 h-100 border-purple" style="border-top: 4px solid var(--clr-purple);">
                    <div class="p-4 border-bottom"><h3 class="m-0 fw-bold fs-5 text-purple">Subscription: <?= htmlspecialchars($subscription['plan'] ?? 'Free') ?></h3></div>
                    <div class="p-4">
                        <div class="mb-3 d-flex justify-content-between">
                            <span>Usage: <strong class="text-purple"><?= $subscription['usage'] ?></strong></span>
                            <span class="text-muted small">Expires: <?= $subscription['expiry'] ?></span>
                        </div>
                        <div class="progress mb-4" style="height: 8px;"><div class="progress-bar bg-purple" style="width: <?= (int)$subscription['percent'] ?>%;"></div></div>
                        <button class="btn btn-purple w-100 py-2 fw-bold">🚀 Upgrade to Unlimited</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const _csrfToken = '<?= Csrf::token() ?>';

function dashboardAction(url, payload, successMsg, $row) {
    $.post(url, { csrf_token: _csrfToken, ...payload }, function(res) {
        if (res.success) {
            Swal.fire({ icon: 'success', title: 'Done!', text: res.message, timer: 2500, confirmButtonColor: '#2563eb', showConfirmButton: false });
            // Fade out the row
            if ($row) $row.fadeOut(400, function() { $(this).remove(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Action Failed', text: res.message, confirmButtonColor: '#2563eb' });
        }
    }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.', confirmButtonColor: '#2563eb' });
    });
}

$(document).ready(function() {

    // ─── Admin: Approve Boarding House ───
    $(document).on('click', '.btn-approve-house', function() {
        const $btn  = $(this), houseId = $btn.data('house-id'), houseName = $btn.data('house-name');
        const $row  = $btn.closest('tr');
        Swal.fire({
            icon: 'question',
            title: 'Approve Boarding House?',
            html: `Approve <strong>${houseName}</strong> and make it visible to tenants?`,
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Cancel'
        }).then(r => {
            if (r.isConfirmed) dashboardAction('/tenant/?url=admin/approve_house', { house_id: houseId }, null, $row);
        });
    });

    // ─── Admin: Reject Boarding House ───
    $(document).on('click', '.btn-reject-house', function() {
        const $btn  = $(this), houseId = $btn.data('house-id'), houseName = $btn.data('house-name');
        const $row  = $btn.closest('tr');
        Swal.fire({
            icon: 'warning',
            title: 'Reject Boarding House?',
            html: `Reject listing for <strong>${houseName}</strong>? The owner will be notified.`,
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        }).then(r => {
            if (r.isConfirmed) dashboardAction('/tenant/?url=admin/reject_house', { house_id: houseId }, null, $row);
        });
    });

    // ─── Owner: Approve Booking ───
    $(document).on('click', '.btn-approve-booking', function() {
        const $btn  = $(this), bookingId = $btn.data('booking-id'), tenant = $btn.data('tenant');
        const $row  = $btn.closest('tr');
        Swal.fire({
            icon: 'question',
            title: 'Approve Booking?',
            html: `Approve booking request from <strong>${tenant}</strong>?`,
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Cancel'
        }).then(r => {
            if (r.isConfirmed) dashboardAction('/tenant/?url=owner/approve_booking', { booking_id: bookingId }, null, $row);
        });
    });

    // ─── Owner: Reject Booking ───
    $(document).on('click', '.btn-reject-booking', function() {
        const $btn  = $(this), bookingId = $btn.data('booking-id'), tenant = $btn.data('tenant');
        const $row  = $btn.closest('tr');
        Swal.fire({
            icon: 'warning',
            title: 'Reject Booking?',
            html: `Decline booking request from <strong>${tenant}</strong>?`,
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        }).then(r => {
            if (r.isConfirmed) dashboardAction('/tenant/?url=owner/reject_booking', { booking_id: bookingId }, null, $row);
        });
    });


    const isOwner = <?= (($_SESSION['role'] ?? '') === 'owner') ? 'true' : 'false' ?>;
    
    // Revenue Analytics (Global or Owner Specific)
    const ctxTable = isOwner ? 'earningsChart' : 'revenueChart';
    const chartEl = document.getElementById(ctxTable);
    
    if (chartEl) {
        const ctxRev = chartEl.getContext('2d');
        const trendData = <?= json_encode(($_SESSION['role'] ?? '') === 'owner' ? ($earningsTrend ?? []) : ($revenueTrend ?? [])) ?>;
        
        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: trendData.labels || [],
                datasets: [{
                    label: isOwner ? 'Daily Income' : 'Monthly Revenue',
                    data: trendData.data || [],
                    borderColor: isOwner ? '#10b981' : '#6366f1',
                    backgroundColor: isOwner ? 'rgba(16, 185, 129, 0.1)' : 'rgba(99, 102, 241, 0.1)',
                    fill: true, tension: 0.4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Occupancy/Growth Analytics
    const growthEl = document.getElementById(isOwner ? 'occupancyChart' : 'growthChart');
    if (growthEl) {
        const ctxGrowth = growthEl.getContext('2d');
        new Chart(ctxGrowth, {
            type: 'doughnut',
            data: {
                labels: isOwner ? ['Occupied', 'Available'] : ['Owners', 'Tenants', 'Staff/Admins'],
                datasets: [{ 
                    data: isOwner ? [<?= (int)($occupiedSlots ?? 1) ?>, <?= (int)($availableSlots ?? 1) ?>] : [<?= (int)($totalOwners ?? 0) ?>, <?= (int)($totalTenants ?? 0) ?>, <?= (int)($totalStaff ?? 0) ?>], 
                    backgroundColor: isOwner ? ['#2563eb', '#f1f5f9'] : ['#6366f1', '#10b981', '#f59e0b'], 
                    borderWidth: 0, cutout: '75%' 
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
});
</script>

<style>
.bg-blue-subtle { background: #eff6ff; }
.bg-emerald-subtle { background: #f0fdf4; }
.bg-purple-subtle { background: #f5f3ff; }
.bg-amber-subtle { background: #fffbeb; }
.bg-success-subtle { background: #f0fdf4; }
.text-blue { color: #2563eb; }
.text-emerald { color: #10b981; }
.text-purple { color: #7c3aed; }
.text-amber { color: #d97706; }
.bg-purple { background: var(--clr-purple) !important; color: white; }
.btn-purple { background: var(--clr-purple); color: white; border: none; }
.btn-purple:hover { background: #6d28d9; color: white; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3); }
.border-purple { border-top: 4px solid var(--clr-purple) !important; }

.interact-hover { transition: all 0.2s; cursor: pointer; }
.interact-hover:hover { background: #f8fafc !important; transform: translateX(4px); }

.activity-icon-wrap { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-subtext { font-size: 0.75rem; margin-top: 8px; opacity: 0.8; font-weight: 500; }

/* Welcome Section Styles */
.welcome-section {
    background: linear-gradient(to right, #ffffff, #f8fafc);
}
.welcome-avatar {
    width: 60px;
    height: 60px;
    background: var(--accent);
    color: white;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 800;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}

/* Responsive Dashboard Tweaks */
@media (max-width: 768px) {
    .welcome-section {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem !important;
        padding: 1.5rem !important;
    }
    .welcome-section .d-flex.align-items-center.gap-4 {
        flex-direction: column;
        gap: 1rem !important;
    }
    .welcome-avatar {
        width: 70px;
        height: 70px;
        font-size: 1.8rem;
    }
    .premium-stat-card {
        padding: 1.25rem !important;
    }
}
</style>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
