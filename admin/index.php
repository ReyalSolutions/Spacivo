<?php
require_once __DIR__ . '/components/auth_check.php';

$userModel = new User($db);
$houseModel = new BoardingHouse($db);
$bookingModel = new Booking($db);
$subModel = new Subscription($db);
$activityModel = new ActivityLog($db);

// 1. KPI Counts
$users = $userModel->all();
$totalUsers = count($users);
$totalOwners = count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'owner'));
$totalTenants = count(array_filter($users, fn($u) => strtolower($u['role'] ?? '') === 'tenant'));

$houses = $houseModel->all();
$totalHouses = count($houses);
$pendingApprovals = count(array_filter($houses, fn($h) => ($h['status'] ?? 'pending') === 'pending'));

$bookings = $bookingModel->all();
$totalBookings = count($bookings);

$planPaymentsRes = $db->query("SELECT amount FROM plan_payments WHERE status = 'paid'");
$totalRevenue = 0.0;
if ($planPaymentsRes) {
    while ($row = $planPaymentsRes->fetch_assoc()) {
        $totalRevenue += (float)($row['amount'] ?? 0);
    }
}

$activeSubsRes = $db->query("SELECT COUNT(*) as cnt FROM subscriptions WHERE status = 'active'");
$activeSubscriptions = $activeSubsRes ? (int)($activeSubsRes->fetch_assoc()['cnt'] ?? 0) : 0;

// 2. Recent Activities
$recentActivities = $activityModel->getRecentLogs(6);

// 3. Subscription & Revenue Trend
$subTrend = $subModel->getSubscriptionTrend('monthly');
$trendLabels = [];
$trendValues = [];
if (!empty($subTrend)) {
    foreach ($subTrend as $s) {
        $trendLabels[] = $s['label'];
        $trendValues[] = (float)$s['total'];
    }
} else {
    $trendLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
    $trendValues = [0, 0, 0, 0, 0, 0, 0];
}

// 4. Recent Houses
$recentHousesRes = $db->query("SELECT bh.*, u.first_name, u.last_name FROM boarding_houses bh LEFT JOIN users u ON bh.owner_id = u.id ORDER BY bh.id DESC LIMIT 5");
$recentHouses = $recentHousesRes ? $recentHousesRes->fetch_all(MYSQLI_ASSOC) : [];

// 5. Plans Distribution
$plansRes = $db->query("SELECT p.name, p.price_monthly, COUNT(s.id) as sub_count FROM plans p LEFT JOIN subscriptions s ON p.id = s.plan_id AND s.status = 'active' GROUP BY p.id ORDER BY sub_count DESC");
$plansList = $plansRes ? $plansRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard – <?= htmlspecialchars($siteName) ?> Admin</title>
  <?php include __DIR__ . '/components/links.php'; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    .kpi-card { border-radius:16px; transition:all .3s ease; border:none; }
    .kpi-card:hover { transform:translateY(-4px); box-shadow:0 12px 28px rgba(0,0,0,.08)!important; }
    .kpi-icon { width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem; }
    .avatar-initials { width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0; }
    .user-avatar-sm  { width:38px;height:38px;border-radius:50%;object-fit:cover;flex-shrink:0; }
    .progress { border-radius:20px; }
    .progress-bar { border-radius:20px;transition:width 1.2s ease; }
    .stat-badge { font-size:.72rem;padding:4px 10px;border-radius:20px;font-weight:600; }
  </style>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
     data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
  
  <?php include __DIR__ . '/components/sidebar.php'; ?>
  
  <div class="body-wrapper">
    <?php include __DIR__ . '/components/header.php'; ?>
    
    <div class="container-fluid">

      <!-- Welcome Banner matching reyal_solutions design -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card border-0 animate__animated animate__fadeInDown shadow-sm"
               style="background:linear-gradient(135deg, #0369a1 0%, #0d9488 100%);border-radius:18px;">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3 py-4 text-white">
              <div>
                <h4 class="text-white fw-bold mb-1">Welcome back, <?= $userName ?>! 👋</h4>
                <p class="text-white opacity-75 mb-0">
                  <?= date('l, F j, Y') ?> &bull;
                  <span class="badge fw-semibold" style="background:rgba(255,255,255,0.2);color:#fff;"><?= $userRole ?></span>
                </p>
              </div>
              <div class="d-flex gap-2">
                <a href="houses.php" class="btn fw-semibold px-4" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.25);border-radius:10px;">
                  <i class="ti ti-home-2 me-1"></i> Houses
                  <?php if ($pendingApprovals > 0): ?>
                    <span class="badge bg-warning text-dark ms-1"><?= $pendingApprovals ?> Pending</span>
                  <?php endif; ?>
                </a>
                <a href="users.php" class="btn btn-outline-light fw-semibold px-4" style="border-radius:10px;">
                  <i class="ti ti-users me-1"></i> Users
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Cards matching reyal_solutions design -->
      <div class="row g-4 mb-4 animate__animated animate__fadeInUp">
        <!-- Total Users -->
        <div class="col-lg-3 col-md-6">
          <div class="card kpi-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-primary text-primary"><i class="ti ti-users"></i></div>
              <div>
                <p class="mb-0 text-muted small fw-semibold">Total Users</p>
                <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalUsers) ?></h4>
                <small class="text-muted"><?= $totalOwners ?> Owners &bull; <?= $totalTenants ?> Tenants</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Boarding Houses -->
        <div class="col-lg-3 col-md-6">
          <div class="card kpi-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-success text-success"><i class="ti ti-home-2"></i></div>
              <div>
                <p class="mb-0 text-muted small fw-semibold">Boarding Houses</p>
                <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalHouses) ?></h4>
                <small class="text-muted"><?= $pendingApprovals ?> awaiting approval</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Bookings -->
        <div class="col-lg-3 col-md-6">
          <div class="card kpi-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-info text-info"><i class="ti ti-calendar-event"></i></div>
              <div>
                <p class="mb-0 text-muted small fw-semibold">Total Bookings</p>
                <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalBookings) ?></h4>
                <small class="text-muted">Recorded tenant stays</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-lg-3 col-md-6">
          <div class="card kpi-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-warning text-warning"><i class="ti ti-crown"></i></div>
              <div>
                <p class="mb-0 text-muted small fw-semibold">Subscription Revenue</p>
                <h4 class="fw-bold mb-0 text-dark">₱<?= number_format($totalRevenue, 2) ?></h4>
                <small class="text-muted"><?= $activeSubscriptions ?> active subscriptions</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Dashboard Row -->
      <div class="row g-4">
        <!-- Left Column: Chart & Recent Houses -->
        <div class="col-lg-8">
          
          <!-- Subscription Trend Chart Card -->
          <div class="card shadow-sm mb-4 border-0" style="border-radius:16px;">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                  <h5 class="card-title fw-bold mb-0">Subscription & Revenue Trends</h5>
                  <p class="text-muted small mb-0">Monthly recurring owner subscription volume</p>
                </div>
                <span class="badge bg-light-primary text-primary px-3 py-2 rounded-pill">
                  <i class="ti ti-chart-line me-1"></i> Growth Index
                </span>
              </div>
              <div id="subscriptionChart" style="height:300px;"></div>
            </div>
          </div>

          <!-- Recent Boarding Houses -->
          <div class="card shadow-sm border-0" style="border-radius:16px;">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="card-title fw-bold mb-0">Recent Boarding Houses</h5>
                <a href="houses.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
              </div>
              <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="border-0">Property Name</th>
                      <th class="border-0">Owner</th>
                      <th class="border-0">Status</th>
                      <th class="border-0 text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($recentHouses)): ?>
                      <?php foreach ($recentHouses as $house): ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center gap-2">
                              <div class="bg-light-primary text-primary rounded p-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                <i class="ti ti-building fs-5"></i>
                              </div>
                              <span class="fw-semibold text-dark"><?= htmlspecialchars($house['name']) ?></span>
                            </div>
                          </td>
                          <td><?= htmlspecialchars(($house['first_name'] ?? '') . ' ' . ($house['last_name'] ?? '')) ?></td>
                          <td>
                            <?php if ($house['status'] === 'approved'): ?>
                              <span class="badge bg-light-success text-success stat-badge">Approved</span>
                            <?php elseif ($house['status'] === 'pending'): ?>
                              <span class="badge bg-light-warning text-warning stat-badge">Pending</span>
                            <?php else: ?>
                              <span class="badge bg-light-danger text-danger stat-badge"><?= ucfirst($house['status']) ?></span>
                            <?php endif; ?>
                          </td>
                          <td class="text-end">
                            <a href="houses.php?id=<?= $house['id'] ?>" class="btn btn-sm btn-light text-primary rounded-pill px-3">
                              Inspect
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="4" class="text-center text-muted py-4">No boarding houses registered yet.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Column: Subscriptions & Activity -->
        <div class="col-lg-4">
          
          <!-- Subscription Plans Distribution -->
          <div class="card shadow-sm mb-4 border-0" style="border-radius:16px;">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="card-title fw-bold mb-0">Subscription Plans</h5>
                <a href="plans.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage</a>
              </div>
              <div class="d-flex flex-column gap-3">
                <?php if (!empty($plansList)): ?>
                  <?php foreach ($plansList as $plan): ?>
                    <div class="p-3 border rounded-3 bg-light-subtle d-flex justify-content-between align-items-center">
                      <div>
                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($plan['name']) ?></h6>
                        <small class="text-muted">₱<?= number_format((float)$plan['price_monthly'], 2) ?> / mo</small>
                      </div>
                      <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">
                        <?= (int)$plan['sub_count'] ?> Active
                      </span>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p class="text-muted small mb-0">No active plans configured.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Recent System Activity Logs -->
          <div class="card shadow-sm border-0" style="border-radius:16px;">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="card-title fw-bold mb-0">Recent Activity</h5>
                <a href="logs.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">All Logs</a>
              </div>
              <div class="d-flex flex-column gap-3">
                <?php if (!empty($recentActivities)): ?>
                  <?php foreach ($recentActivities as $act): ?>
                    <div class="d-flex align-items-start gap-3">
                      <div class="bg-light-primary text-primary rounded-circle p-2 mt-1 d-flex align-items-center justify-content-center" style="width:32px;height:32px;flex-shrink:0;">
                        <i class="ti ti-activity fs-4"></i>
                      </div>
                      <div class="flex-grow-1">
                        <p class="mb-0 text-dark fw-semibold small"><?= htmlspecialchars($act['action'] ?? $act['description'] ?? 'System event') ?></p>
                        <small class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($act['created_at'] ?? 'Recently') ?></small>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p class="text-muted small mb-0">No recent activity logged.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
    
    <?php include __DIR__ . '/components/footer.php'; ?>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var chartOptions = {
        series: [{
            name: "Subscription Count",
            data: <?= json_encode($trendValues) ?>
        }],
        chart: {
            type: 'area',
            height: 280,
            toolbar: { show: false },
            fontFamily: 'Plus Jakarta Sans, sans-serif'
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3, colors: ['#0369a1'] },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        colors: ['#0369a1'],
        xaxis: {
            categories: <?= json_encode($trendLabels) ?>,
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return Math.round(val);
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " subscriptions";
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#subscriptionChart"), chartOptions);
    chart.render();
});
</script>
</body>
</html>
