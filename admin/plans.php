<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $priceMonthly = (float)($_POST['price_monthly'] ?? 0);
        $priceYearly = (float)($_POST['price_yearly'] ?? 0);
        $roomLimit = (int)($_POST['room_limit'] ?? 5);
        $features = json_encode(array_filter(array_map('trim', explode("\n", $_POST['features'] ?? ''))));

        if ($name) {
            $stmt = $db->prepare("INSERT INTO plans (name, price_monthly, price_yearly, room_limit, features) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sddis", $name, $priceMonthly, $priceYearly, $roomLimit, $features);
            if ($stmt->execute()) {
                $msg = "Plan '{$name}' created successfully!";
            } else {
                $msg = "Failed to create plan: " . $db->error;
                $msgType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $planId = (int)($_POST['plan_id'] ?? 0);
        if ($planId > 0) {
            $stmt = $db->prepare("DELETE FROM plans WHERE id = ?");
            $stmt->bind_param("i", $planId);
            if ($stmt->execute()) {
                $msg = "Plan deleted successfully!";
            } else {
                $msg = "Cannot delete plan with active subscribers.";
                $msgType = 'danger';
            }
        }
    }
}

$plansRes = $db->query("SELECT p.*, COUNT(s.id) as subscriber_count FROM plans p LEFT JOIN subscriptions s ON p.id = s.plan_id AND s.status = 'active' GROUP BY p.id ORDER BY p.id ASC");
$plansList = $plansRes ? $plansRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Subscription Plans – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Subscription Plans</h4>
          <p class="text-muted small mb-0">Configure owner tier pricing and room capacity packages</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addPlanModal">
          <i class="ti ti-plus me-1"></i> New Plan
        </button>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Plans Cards Grid -->
      <div class="row g-4 mb-4">
        <?php foreach ($plansList as $p): ?>
          <?php 
            $featuresArr = json_decode($p['features'] ?? '[]', true) ?: [];
          ?>
          <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 position-relative" style="border-radius:18px;">
              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="badge bg-light-primary text-primary fw-bold px-3 py-2 rounded-pill"><?= htmlspecialchars($p['name']) ?></span>
                  <span class="badge bg-light-info text-info stat-badge"><?= (int)$p['subscriber_count'] ?> active</span>
                </div>
                
                <div class="mb-3">
                  <h3 class="fw-bold text-dark mb-0">₱<?= number_format((float)$p['price_monthly'], 2) ?></h3>
                  <small class="text-muted">Per month &bull; ₱<?= number_format((float)$p['price_yearly'], 2) ?>/yr</small>
                </div>

                <div class="p-3 bg-light rounded-3 mb-3">
                  <div class="d-flex align-items-center gap-2 small text-dark fw-semibold">
                    <i class="ti ti-door text-primary fs-5"></i>
                    <span>Room Capacity: <?= (int)$p['room_limit'] ?> rooms</span>
                  </div>
                </div>

                <ul class="list-unstyled d-flex flex-column gap-2 mb-4 flex-grow-1 small text-muted">
                  <?php if (!empty($featuresArr)): ?>
                    <?php foreach ($featuresArr as $feat): ?>
                      <li class="d-flex align-items-center gap-2">
                        <i class="ti ti-check text-success fs-5"></i>
                        <span><?= htmlspecialchars($feat) ?></span>
                      </li>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <li class="text-muted fst-italic">Standard platform features</li>
                  <?php endif; ?>
                </ul>

                <div class="border-top pt-3 d-flex justify-content-end">
                  <form method="POST" onsubmit="return confirm('Delete this plan?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                      <i class="ti ti-trash me-1"></i> Delete Plan
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
    
    <?php include __DIR__ . '/components/footer.php'; ?>
  </div>
</div>

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius:16px;">
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Create Subscription Plan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label small fw-semibold">Plan Name *</label>
              <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Standard, Premium" required>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Price Monthly (₱) *</label>
              <input type="number" step="0.01" name="price_monthly" class="form-control rounded-3" value="499.00" required>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Price Yearly (₱) *</label>
              <input type="number" step="0.01" name="price_yearly" class="form-control rounded-3" value="4990.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Room Limit *</label>
              <input type="number" name="room_limit" class="form-control rounded-3" value="10" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Features (1 per line)</label>
              <textarea name="features" class="form-control rounded-3" rows="4" placeholder="Unlimited tenant bookings&#10;Map listing&#10;24/7 Support"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top p-3">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Save Plan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>
</body>
</html>
