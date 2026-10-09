<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'ti ti-star');
        $category = trim($_POST['category'] ?? 'General');
        
        if ($name) {
            $stmt = $db->prepare("INSERT INTO amenities (name, icon, category) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $icon, $category);
            if ($stmt->execute()) {
                $msg = "Amenity '{$name}' created successfully!";
            } else {
                $msg = "Error creating amenity: " . $db->error;
                $msgType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $amenityId = (int)($_POST['amenity_id'] ?? 0);
        if ($amenityId > 0) {
            $stmt = $db->prepare("DELETE FROM amenities WHERE id = ?");
            $stmt->bind_param("i", $amenityId);
            if ($stmt->execute()) {
                $msg = "Amenity deleted successfully!";
            } else {
                $msg = "Error deleting amenity: " . $db->error;
                $msgType = 'danger';
            }
        }
    }
}

$amenitiesRes = $db->query("SELECT * FROM amenities ORDER BY id DESC");
$amenitiesList = $amenitiesRes ? $amenitiesRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Room Amenities – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Amenities Catalog</h4>
          <p class="text-muted small mb-0">Manage features and conveniences available to assign across properties</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAmenityModal">
          <i class="ti ti-plus me-1"></i> Add Amenity
        </button>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Amenities Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="amenitiesTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Icon & Name</th>
                  <th class="border-0">Category</th>
                  <th class="border-0 text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($amenitiesList as $a): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-light-primary text-primary" style="width:38px;height:38px;font-size:1.1rem;">
                          <i class="<?= htmlspecialchars($a['icon'] ?? 'ti ti-sparkles') ?>"></i>
                        </div>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($a['name']) ?></span>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light-info text-info stat-badge">
                        <?= htmlspecialchars($a['category'] ?? 'General') ?>
                      </span>
                    </td>
                    <td class="text-end">
                      <form method="POST" class="d-inline" onsubmit="return confirm('Delete this amenity?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="amenity_id" value="<?= $a['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                          <i class="ti ti-trash me-1"></i> Delete
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

<!-- Add Amenity Modal -->
<div class="modal fade" id="addAmenityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius:16px;">
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Add New Amenity</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label small fw-semibold">Amenity Name *</label>
              <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. High-Speed Wi-Fi" required>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Icon Class</label>
              <input type="text" name="icon" class="form-control rounded-3" value="ti ti-wifi" placeholder="e.g. ti ti-wifi">
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Category</label>
              <select name="category" class="form-select rounded-3">
                <option value="General">General</option>
                <option value="Room Features">Room Features</option>
                <option value="Utilities">Utilities</option>
                <option value="Security">Security</option>
                <option value="Shared Space">Shared Space</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top p-3">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Save Amenity</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>
<script>
$(document).ready(function() {
    $('#amenitiesTable').DataTable({
        pageLength: 10,
        language: { search: "_INPUT_", searchPlaceholder: "Search amenities..." }
    });
});
</script>
</body>
</html>
