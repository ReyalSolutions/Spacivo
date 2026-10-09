<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $subId = (int)($_POST['sub_id'] ?? 0);
    
    if ($subId > 0 && in_array($action, ['active', 'expired'], true)) {
        $stmt = $db->prepare("UPDATE subscriptions SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $subId);
        if ($stmt->execute()) {
            $msg = "Subscription marked as {$action}!";
        } else {
            $msg = "Error updating subscription: " . $db->error;
            $msgType = 'danger';
        }
    }
}

$query = "
    SELECT s.*, 
           CONCAT(u.first_name, ' ', u.last_name) as owner_name, 
           u.email as owner_email,
           p.name as plan_name,
           p.price_monthly,
           p.room_limit
    FROM subscriptions s
    LEFT JOIN users u ON s.owner_id = u.id
    LEFT JOIN plans p ON s.plan_id = p.id
    ORDER BY s.id DESC
";
$subsRes = $db->query($query);
$subsList = $subsRes ? $subsRes->fetch_all(MYSQLI_ASSOC) : [];

$totalSubs = count($subsList);
$activeSubs = count(array_filter($subsList, fn($s) => ($s['status'] ?? '') === 'active'));
$expiredSubs = count(array_filter($subsList, fn($s) => ($s['status'] ?? '') === 'expired'));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Owner Subscriptions – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Owner Subscriptions</h4>
          <p class="text-muted small mb-0">Track active memberships and tier subscriptions for property owners</p>
        </div>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Quick KPI Counters -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-primary text-primary"><i class="ti ti-crown"></i></div>
              <div>
                <small class="text-muted d-block">Total Subscriptions</small>
                <h5 class="fw-bold mb-0"><?= $totalSubs ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-success text-success"><i class="ti ti-circle-check"></i></div>
              <div>
                <small class="text-muted d-block">Active Memberships</small>
                <h5 class="fw-bold mb-0 text-success"><?= $activeSubs ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-secondary text-secondary"><i class="ti ti-clock-off"></i></div>
              <div>
                <small class="text-muted d-block">Expired</small>
                <h5 class="fw-bold mb-0"><?= $expiredSubs ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subscriptions Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="subsTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Property Owner</th>
                  <th class="border-0">Tier Plan</th>
                  <th class="border-0">Period</th>
                  <th class="border-0">Status</th>
                  <th class="border-0 text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($subsList as $s): ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($s['owner_name'] ?? 'Unknown Owner') ?></div>
                      <small class="text-muted"><?= htmlspecialchars($s['owner_email'] ?? '') ?></small>
                    </td>
                    <td>
                      <span class="badge bg-light-primary text-primary fw-semibold px-3 py-2 rounded-pill">
                        <?= htmlspecialchars($s['plan_name'] ?? 'Custom Plan') ?>
                      </span>
                      <small class="text-muted d-block mt-1">₱<?= number_format((float)($s['price_monthly'] ?? 0), 2) ?>/mo</small>
                    </td>
                    <td>
                      <div class="small fw-medium text-dark"><?= date('M j, Y', strtotime($s['start_date'])) ?></div>
                      <small class="text-muted">Expires: <?= $s['end_date'] ? date('M j, Y', strtotime($s['end_date'])) : 'Never' ?></small>
                    </td>
                    <td>
                      <?php if ($s['status'] === 'active'): ?>
                        <span class="badge bg-light-success text-success stat-badge">Active</span>
                      <?php else: ?>
                        <span class="badge bg-light-secondary text-secondary stat-badge">Expired</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="<?= ($s['status'] === 'active') ? 'expired' : 'active' ?>">
                        <input type="hidden" name="sub_id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-light rounded-pill px-3 text-muted">
                          <?= ($s['status'] === 'active') ? 'Mark Expired' : 'Activate' ?>
                        </button>
                      </form>
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
    $('#subsTable').DataTable({
        pageLength: 10,
        language: { search: "_INPUT_", searchPlaceholder: "Search subscriptions..." }
    });
});
</script>
</body>
</html>
