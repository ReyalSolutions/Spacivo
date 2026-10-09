<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';

// Handle house approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $houseId = (int)($_POST['house_id'] ?? 0);
    
    if ($houseId > 0 && in_array($action, ['approve', 'reject'], true)) {
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $db->prepare("UPDATE boarding_houses SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $houseId);
        if ($stmt->execute()) {
            $msg = "House status updated to " . ucfirst($newStatus) . " successfully!";
        } else {
            $msg = "Failed to update house status: " . $db->error;
            $msgType = 'danger';
        }
    }
}

// Fetch boarding houses with owner details and room counts
$query = "
    SELECT bh.*, 
           CONCAT(u.first_name, ' ', u.last_name) as owner_name, 
           u.email as owner_email,
           COUNT(r.id) as total_rooms,
           COALESCE(SUM(r.capacity), 0) as total_capacity
    FROM boarding_houses bh
    LEFT JOIN users u ON bh.owner_id = u.id
    LEFT JOIN rooms r ON bh.id = r.boarding_house_id
    GROUP BY bh.id
    ORDER BY bh.id DESC
";
$housesRes = $db->query($query);
$housesList = $housesRes ? $housesRes->fetch_all(MYSQLI_ASSOC) : [];

$totalHouses = count($housesList);
$pendingCount = count(array_filter($housesList, fn($h) => ($h['status'] ?? '') === 'pending'));
$approvedCount = count(array_filter($housesList, fn($h) => ($h['status'] ?? '') === 'approved'));
$rejectedCount = count(array_filter($housesList, fn($h) => ($h['status'] ?? '') === 'rejected'));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Boarding Houses – <?= htmlspecialchars($siteName) ?> Admin</title>
  <?php include __DIR__ . '/components/links.php'; ?>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
     data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
  
  <?php include __DIR__ . '/components/sidebar.php'; ?>
  
  <div class="body-wrapper">
    <?php include __DIR__ . '/components/header.php'; ?>
    
    <div class="container-fluid">
      
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h4 class="fw-bold mb-1 text-dark">Boarding Houses</h4>
          <p class="text-muted small mb-0">Review, approve, and manage registered properties</p>
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
        <div class="col-md-3 col-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-primary text-primary"><i class="ti ti-home-2"></i></div>
              <div>
                <small class="text-muted d-block">Total Properties</small>
                <h5 class="fw-bold mb-0"><?= $totalHouses ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-warning text-warning"><i class="ti ti-clock-pause"></i></div>
              <div>
                <small class="text-muted d-block">Pending Approval</small>
                <h5 class="fw-bold mb-0 text-warning"><?= $pendingCount ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-success text-success"><i class="ti ti-circle-check"></i></div>
              <div>
                <small class="text-muted d-block">Approved</small>
                <h5 class="fw-bold mb-0 text-success"><?= $approvedCount ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-danger text-danger"><i class="ti ti-circle-x"></i></div>
              <div>
                <small class="text-muted d-block">Rejected</small>
                <h5 class="fw-bold mb-0"><?= $rejectedCount ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Properties Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="housesTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Property</th>
                  <th class="border-0">Owner</th>
                  <th class="border-0">Rooms / Slots</th>
                  <th class="border-0">Status</th>
                  <th class="border-0 text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($housesList as $h): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-light-primary text-primary" style="width:40px;height:40px;font-size:1.1rem;">
                          <i class="ti ti-building"></i>
                        </div>
                        <div>
                          <span class="fw-bold text-dark d-block"><?= htmlspecialchars($h['name']) ?></span>
                          <small class="text-muted"><i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($h['address'] ?? 'No address provided') ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="fw-medium text-dark"><?= htmlspecialchars($h['owner_name'] ?? 'Unknown') ?></div>
                      <small class="text-muted"><?= htmlspecialchars($h['owner_email'] ?? '') ?></small>
                    </td>
                    <td>
                      <span class="badge bg-light-info text-info stat-badge">
                        <?= (int)$h['total_rooms'] ?> Rooms (<?= (int)$h['total_capacity'] ?> cap)
                      </span>
                    </td>
                    <td>
                      <?php if ($h['status'] === 'approved'): ?>
                        <span class="badge bg-light-success text-success stat-badge">Approved</span>
                      <?php elseif ($h['status'] === 'pending'): ?>
                        <span class="badge bg-light-warning text-warning stat-badge">Pending Review</span>
                      <?php else: ?>
                        <span class="badge bg-light-danger text-danger stat-badge">Rejected</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <?php if ($h['status'] === 'pending'): ?>
                        <div class="d-inline-flex gap-1">
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="house_id" value="<?= $h['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                              <i class="ti ti-check me-1"></i> Approve
                            </button>
                          </form>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="house_id" value="<?= $h['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                              <i class="ti ti-x me-1"></i> Reject
                            </button>
                          </form>
                        </div>
                      <?php else: ?>
                        <form method="POST" class="d-inline">
                          <input type="hidden" name="action" value="<?= ($h['status'] === 'approved') ? 'reject' : 'approve' ?>">
                          <input type="hidden" name="house_id" value="<?= $h['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-light rounded-pill px-3 text-muted">
                            <?= ($h['status'] === 'approved') ? 'Revoke Approval' : 'Approve House' ?>
                          </button>
                        </form>
                      <?php endif; ?>
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
    $('#housesTable').DataTable({
        pageLength: 10,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search properties..."
        }
    });
});
</script>
</body>
</html>
