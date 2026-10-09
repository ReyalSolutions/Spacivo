<?php
require_once __DIR__ . '/components/auth_check.php';

$query = "
    SELECT pp.*, 
           CONCAT(u.first_name, ' ', u.last_name) as owner_name, 
           u.email as owner_email,
           p.name as plan_name
    FROM plan_payments pp
    LEFT JOIN users u ON pp.owner_id = u.id
    LEFT JOIN plans p ON pp.plan_id = p.id
    ORDER BY pp.id DESC
";
$planPaymentsRes = $db->query($query);
$planPaymentsList = $planPaymentsRes ? $planPaymentsRes->fetch_all(MYSQLI_ASSOC) : [];

$totalRev = 0.0;
foreach ($planPaymentsList as $pp) {
    if (($pp['status'] ?? '') === 'paid') {
        $totalRev += (float)($pp['amount'] ?? 0);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Plan Payments – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Plan Transactions</h4>
          <p class="text-muted small mb-0">Record of owner subscription plan payments and receipts</p>
        </div>
      </div>

      <!-- Quick KPI Counters -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-primary text-primary"><i class="ti ti-file-invoice"></i></div>
              <div>
                <small class="text-muted d-block">Total Transactions</small>
                <h5 class="fw-bold mb-0"><?= count($planPaymentsList) ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-success text-success"><i class="ti ti-crown"></i></div>
              <div>
                <small class="text-muted d-block">Paid Subscription Revenue</small>
                <h5 class="fw-bold mb-0 text-success">₱<?= number_format($totalRev, 2) ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Plan Payments Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="planPaymentsTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Owner</th>
                  <th class="border-0">Plan</th>
                  <th class="border-0">Method & Reference</th>
                  <th class="border-0">Amount</th>
                  <th class="border-0">Status</th>
                  <th class="border-0 text-end">Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($planPaymentsList as $pp): ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($pp['owner_name'] ?? 'Owner') ?></div>
                      <small class="text-muted"><?= htmlspecialchars($pp['owner_email'] ?? '') ?></small>
                    </td>
                    <td>
                      <span class="badge bg-light-primary text-primary fw-semibold px-3 py-1 rounded-pill">
                        <?= htmlspecialchars($pp['plan_name'] ?? 'Plan') ?>
                      </span>
                    </td>
                    <td>
                      <span class="fw-medium text-dark"><?= htmlspecialchars(strtoupper($pp['payment_method'] ?? 'MANUAL')) ?></span>
                      <small class="text-muted d-block font-monospace"><?= htmlspecialchars($pp['reference_number'] ?? $pp['transaction_ref'] ?? 'N/A') ?></small>
                    </td>
                    <td>
                      <span class="fw-bold text-dark">₱<?= number_format((float)($pp['amount'] ?? 0), 2) ?></span>
                    </td>
                    <td>
                      <?php if (($pp['status'] ?? '') === 'paid'): ?>
                        <span class="badge bg-light-success text-success stat-badge">Paid</span>
                      <?php elseif (($pp['status'] ?? '') === 'pending'): ?>
                        <span class="badge bg-light-warning text-warning stat-badge">Pending</span>
                      <?php else: ?>
                        <span class="badge bg-light-danger text-danger stat-badge"><?= ucfirst($pp['status'] ?? 'Unknown') ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <small class="text-muted"><?= date('M j, Y H:i', strtotime($pp['created_at'])) ?></small>
                    </td>
                  </tr>
                <?php endforeach; ?>
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
<script>
$(document).ready(function() {
    $('#planPaymentsTable').DataTable({
        pageLength: 10,
        language: { search: "_INPUT_", searchPlaceholder: "Search transactions..." }
    });
});
</script>
</body>
</html>
