<?php
require_once __DIR__ . '/components/auth_check.php';

$query = "
    SELECT p.*, 
           CONCAT(u.first_name, ' ', u.last_name) as tenant_name,
           u.email as tenant_email,
           b.start_date, b.end_date,
           r.room_name,
           bh.name as house_name
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN bookings b ON p.booking_id = b.id
    LEFT JOIN rooms r ON b.room_id = r.id
    LEFT JOIN boarding_houses bh ON r.boarding_house_id = bh.id
    ORDER BY p.id DESC
";
$paymentsRes = $db->query($query);
$paymentsList = $paymentsRes ? $paymentsRes->fetch_all(MYSQLI_ASSOC) : [];

$totalPaid = 0.0;
foreach ($paymentsList as $p) {
    if (($p['status'] ?? '') === 'paid') {
        $totalPaid += (float)($p['amount'] ?? 0);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tenant Payments – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Tenant Rental Payments</h4>
          <p class="text-muted small mb-0">Audit rent remittances and transactions across boarding houses</p>
        </div>
      </div>

      <!-- Quick KPI Counters -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-primary text-primary"><i class="ti ti-receipt-2"></i></div>
              <div>
                <small class="text-muted d-block">Total Transactions</small>
                <h5 class="fw-bold mb-0"><?= count($paymentsList) ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-success text-success"><i class="ti ti-cash"></i></div>
              <div>
                <small class="text-muted d-block">Total Remitted Rent</small>
                <h5 class="fw-bold mb-0 text-success">₱<?= number_format($totalPaid, 2) ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payments Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="paymentsTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Tenant</th>
                  <th class="border-0">House & Room</th>
                  <th class="border-0">Method & Ref</th>
                  <th class="border-0">Amount</th>
                  <th class="border-0">Status</th>
                  <th class="border-0 text-end">Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($paymentsList as $p): ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($p['tenant_name'] ?? 'Tenant') ?></div>
                      <small class="text-muted"><?= htmlspecialchars($p['tenant_email'] ?? '') ?></small>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($p['house_name'] ?? 'Property') ?></span>
                      <small class="text-muted"><?= htmlspecialchars($p['room_name'] ?? 'Room') ?></small>
                    </td>
                    <td>
                      <span class="badge bg-light-primary text-primary px-3 py-1 rounded-pill">
                        <?= htmlspecialchars(strtoupper($p['payment_method'] ?? 'ONLINE')) ?>
                      </span>
                      <small class="text-muted d-block mt-1 font-monospace"><?= htmlspecialchars($p['transaction_ref'] ?? 'N/A') ?></small>
                    </td>
                    <td>
                      <span class="fw-bold text-dark">₱<?= number_format((float)($p['amount'] ?? 0), 2) ?></span>
                    </td>
                    <td>
                      <?php if ($p['status'] === 'paid'): ?>
                        <span class="badge bg-light-success text-success stat-badge">Paid</span>
                      <?php elseif ($p['status'] === 'pending'): ?>
                        <span class="badge bg-light-warning text-warning stat-badge">Pending</span>
                      <?php else: ?>
                        <span class="badge bg-light-danger text-danger stat-badge"><?= ucfirst($p['status']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <small class="text-muted"><?= date('M j, Y H:i', strtotime($p['created_at'])) ?></small>
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
    $('#paymentsTable').DataTable({
        pageLength: 10,
        language: { search: "_INPUT_", searchPlaceholder: "Search payments..." }
    });
});
</script>
</body>
</html>
