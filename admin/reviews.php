<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $revId = (int)($_POST['review_id'] ?? 0);
        if ($revId > 0) {
            $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->bind_param("i", $revId);
            if ($stmt->execute()) {
                $msg = "Review deleted successfully!";
            } else {
                $msg = "Error deleting review: " . $db->error;
                $msgType = 'danger';
            }
        }
    }
}

$reviewModel = new Review($db);
$reviewsList = $reviewModel->getAll();

$totalReviews = count($reviewsList);
$avgRating = 0.0;
if ($totalReviews > 0) {
    $sum = array_reduce($reviewsList, fn($c, $r) => $c + (float)($r['rating'] ?? 0), 0.0);
    $avgRating = round($sum / $totalReviews, 1);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reviews & Ratings – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Tenant Reviews & Ratings</h4>
          <p class="text-muted small mb-0">Moderate tenant feedback and ratings for properties and rooms</p>
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
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-primary text-primary"><i class="ti ti-messages"></i></div>
              <div>
                <small class="text-muted d-block">Total Reviews</small>
                <h5 class="fw-bold mb-0"><?= $totalReviews ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-warning text-warning"><i class="ti ti-star"></i></div>
              <div>
                <small class="text-muted d-block">Average Platform Rating</small>
                <h5 class="fw-bold mb-0 text-warning"><?= $avgRating ?> / 5.0</h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="reviewsTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Reviewer</th>
                  <th class="border-0">Property</th>
                  <th class="border-0">Rating</th>
                  <th class="border-0">Feedback</th>
                  <th class="border-0">Date</th>
                  <th class="border-0 text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($reviewsList as $rev): ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($rev['reviewer_name'] ?? 'Anonymous') ?></div>
                    </td>
                    <td>
                      <span class="badge bg-light-primary text-primary fw-semibold px-3 py-1 rounded-pill">
                        <?= htmlspecialchars($rev['house_name'] ?? 'Boarding House') ?>
                      </span>
                    </td>
                    <td>
                      <div class="text-warning d-flex align-items-center gap-1">
                        <i class="ti ti-star-filled"></i>
                        <span class="fw-bold text-dark"><?= (float)($rev['rating'] ?? 5) ?></span>
                      </div>
                    </td>
                    <td>
                      <p class="mb-0 small text-muted" style="max-width: 320px;"><?= htmlspecialchars($rev['comment'] ?? 'No written comment') ?></p>
                    </td>
                    <td>
                      <small class="text-muted"><?= date('M j, Y', strtotime($rev['created_at'] ?? 'now')) ?></small>
                    </td>
                    <td class="text-end">
                      <form method="POST" class="d-inline" onsubmit="return confirm('Delete this review?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                          <i class="ti ti-trash"></i>
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
    $('#reviewsTable').DataTable({
        pageLength: 10,
        language: { search: "_INPUT_", searchPlaceholder: "Search reviews..." }
    });
});
</script>
</body>
</html>
