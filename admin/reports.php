<?php
require_once __DIR__ . '/components/auth_check.php';

// Revenue analytics
$planRevRes = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM plan_payments WHERE status = 'paid'");
$totalPlanRev = $planRevRes ? (float)$planRevRes->fetch_assoc()['total'] : 0.0;

$rentRevRes = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'paid'");
$totalRentRev = $rentRevRes ? (float)$rentRevRes->fetch_assoc()['total'] : 0.0;

$totalBookingsRes = $db->query("SELECT COUNT(*) as cnt FROM bookings");
$totalBookings = $totalBookingsRes ? (int)$totalBookingsRes->fetch_assoc()['cnt'] : 0;

$totalRoomsRes = $db->query("SELECT COUNT(*) as cnt, COALESCE(SUM(capacity), 0) as capacity FROM rooms");
$roomStats = $totalRoomsRes ? $totalRoomsRes->fetch_assoc() : ['cnt' => 0, 'capacity' => 0];

// Monthly Subscription Revenue Breakdown
$monthlyRes = $db->query("
    SELECT DATE_FORMAT(created_at, '%b %Y') as month_name, 
           COUNT(*) as trans_count, 
           SUM(amount) as total_amount 
    FROM plan_payments 
    WHERE status = 'paid' 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY created_at DESC 
    LIMIT 6
");
$monthlyList = $monthlyRes ? $monthlyRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Analytics & Reports – <?= htmlspecialchars($siteName) ?> Admin</title>
  <?php include __DIR__ . '/components/links.php'; ?>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
     data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
  
  <?php include __DIR__ . '/components/sidebar.php'; ?>
  
  <div class="body-wrapper">
    <?php include __DIR__ . '/components/header.php'; ?>
    
    <div class="container-fluid">
      
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h4 class="fw-bold mb-1 text-dark">Analytics & Financial Reports</h4>
          <p class="text-muted small mb-0">High-level financial summaries and operational capacity metrics</p>
        </div>
        <button class="btn btn-outline-primary rounded-pill px-4" onclick="window.print();">
          <i class="ti ti-printer me-1"></i> Print Report
        </button>
      </div>

      <!-- Financial Metric Cards -->
      <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              <span class="text-muted small fw-semibold">Subscription Revenue</span>
              <h3 class="fw-bold text-dark mt-2 mb-1">₱<?= number_format($totalPlanRev, 2) ?></h3>
              <small class="text-success"><i class="ti ti-arrow-up-right me-1"></i>Platform income</small>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              <span class="text-muted small fw-semibold">Total Rent Processed</span>
              <h3 class="fw-bold text-dark mt-2 mb-1">₱<?= number_format($totalRentRev, 2) ?></h3>
              <small class="text-muted">Tenant remittances</small>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              <span class="text-muted small fw-semibold">Total Reservations</span>
              <h3 class="fw-bold text-dark mt-2 mb-1"><?= $totalBookings ?></h3>
              <small class="text-muted">Lifetime bookings</small>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              <span class="text-muted small fw-semibold">Platform Room Capacity</span>
              <h3 class="fw-bold text-dark mt-2 mb-1"><?= (int)$roomStats['capacity'] ?></h3>
              <small class="text-muted">Across <?= (int)$roomStats['cnt'] ?> registered rooms</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Summary Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <h5 class="fw-bold text-dark mb-3">Subscription Performance by Month</h5>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Billing Month</th>
                  <th class="border-0">Transactions</th>
                  <th class="border-0 text-end">Total Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($monthlyList)): ?>
                  <?php foreach ($monthlyList as $m): ?>
                    <tr>
                      <td class="fw-bold text-dark"><?= htmlspecialchars($m['month_name']) ?></td>
                      <td>
                        <span class="badge bg-light-info text-info stat-badge"><?= (int)$m['trans_count'] ?> orders</span>
                      </td>
                      <td class="text-end fw-bold text-dark">
                        ₱<?= number_format((float)$m['total_amount'], 2) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-4">No monthly records available yet.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
    
    <?php include __DIR__ . '/components/footer.php'; ?>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>
</body>
</html>
