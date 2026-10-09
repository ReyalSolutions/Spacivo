<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_role') {
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $stmt = $db->prepare("INSERT INTO roles (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $msg = "Role '{$name}' created successfully!";
            } else {
                $msg = "Error creating role: " . $db->error;
                $msgType = 'danger';
            }
        }
    }
}

$rolesRes = $db->query("
    SELECT r.*, COUNT(rp.permission_id) as perm_count, COUNT(DISTINCT u.id) as user_count
    FROM roles r
    LEFT JOIN role_permissions rp ON r.id = rp.role_id
    LEFT JOIN users u ON r.id = u.role_id
    GROUP BY r.id
    ORDER BY r.id ASC
");
$rolesList = $rolesRes ? $rolesRes->fetch_all(MYSQLI_ASSOC) : [];

$permsRes = $db->query("SELECT * FROM permissions ORDER BY module ASC, name ASC");
$permsList = $permsRes ? $permsRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Roles & Permissions – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Roles & Access Control (RBAC)</h4>
          <p class="text-muted small mb-0">Manage security roles and feature permission matrix</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
          <i class="ti ti-shield-plus me-1"></i> New Role
        </button>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Roles Cards Grid -->
      <div class="row g-4 mb-4">
        <?php foreach ($rolesList as $r): ?>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:18px;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="kpi-icon bg-light-primary text-primary">
                    <i class="ti ti-shield-lock"></i>
                  </div>
                  <span class="badge bg-light-success text-success stat-badge"><?= (int)$r['user_count'] ?> Users</span>
                </div>
                
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars(ucfirst($r['name'])) ?></h5>
                <p class="text-muted small mb-3"><?= (int)$r['perm_count'] ?> system permissions assigned</p>

                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                  <span class="small fw-semibold text-muted">Role ID: #<?= $r['id'] ?></span>
                  <span class="badge bg-primary rounded-pill px-3 py-1">Active Role</span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Permissions Reference Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <h5 class="fw-bold text-dark mb-3">Registered Permissions Repository</h5>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="permsTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Permission Name</th>
                  <th class="border-0">Slug</th>
                  <th class="border-0">Module</th>
                  <th class="border-0">Description</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($permsList as $p): ?>
                  <tr>
                    <td>
                      <span class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></span>
                    </td>
                    <td>
                      <code class="text-primary fw-semibold"><?= htmlspecialchars($p['slug']) ?></code>
                    </td>
                    <td>
                      <span class="badge bg-light-info text-info stat-badge"><?= htmlspecialchars(ucfirst($p['module'] ?? 'Core')) ?></span>
                    </td>
                    <td>
                      <small class="text-muted"><?= htmlspecialchars($p['description'] ?? 'Standard privilege') ?></small>
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

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius:16px;">
      <form method="POST">
        <input type="hidden" name="action" value="create_role">
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-bold">Create Security Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <label class="form-label small fw-semibold">Role Name *</label>
          <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Inspector, Auditor" required>
        </div>
        <div class="modal-footer border-top p-3">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Save Role</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>
<script>
$(document).ready(function() {
    $('#permsTable').DataTable({
        pageLength: 10,
        language: { search: "_INPUT_", searchPlaceholder: "Search permissions..." }
    });
});
</script>
</body>
</html>
