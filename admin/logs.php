<?php
require_once __DIR__ . '/components/auth_check.php';

$activityModel = new ActivityLog($db);
$logsList = $activityModel->all(200);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>System Audit Logs – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">System Audit &amp; Activity Logs</h4>
          <p class="text-muted small mb-0">Immutable records of system actions, authentication events, and administrative edits</p>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="logsTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">#</th>
                  <th class="border-0">Timestamp</th>
                  <th class="border-0">Admin</th>
                  <th class="border-0">Action</th>
                  <th class="border-0">Target</th>
                  <th class="border-0">Details</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($logsList as $log): ?>
                  <tr>
                    <td><small class="text-muted">#<?= (int)$log['id'] ?></small></td>
                    <td>
                      <small class="text-dark fw-semibold"><?= date('M j, Y H:i:s', strtotime($log['created_at'])) ?></small>
                    </td>
                    <td>
                      <?php
                        $adminName = trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? ''));
                        echo htmlspecialchars($adminName ?: 'System');
                      ?>
                    </td>
                    <td>
                      <?php
                        $actionColors = [
                          'CREATE_USER'   => 'bg-light-success text-success',
                          'UPDATE_USER'   => 'bg-light-primary text-primary',
                          'DELETE_USER'   => 'bg-light-danger text-danger',
                          'TOGGLE_STATUS' => 'bg-light-warning text-warning',
                          'APPROVE_HOUSE' => 'bg-light-success text-success',
                          'REJECT_HOUSE'  => 'bg-light-danger text-danger',
                          'CREATE_BOOKING'=> 'bg-light-info text-info',
                          'PAYMENT_RECEIVED' => 'bg-light-success text-success',
                        ];
                        $action = $log['action'] ?? 'EVENT';
                        $badgeClass = $actionColors[$action] ?? 'bg-light-secondary text-secondary';
                      ?>
                      <span class="badge <?= $badgeClass ?> stat-badge"><?= htmlspecialchars($action) ?></span>
                    </td>
                    <td>
                      <?php if (!empty($log['target_first']) || !empty($log['target_last'])): ?>
                        <small class="text-muted"><?= htmlspecialchars(trim(($log['target_first'] ?? '') . ' ' . ($log['target_last'] ?? ''))) ?></small>
                      <?php else: ?>
                        <small class="text-muted">—</small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php
                        $details = $log['details'] ?? '';
                        $decoded = json_decode($details, true);
                        if (is_array($decoded)) {
                          $keys = array_keys($decoded);
                          $summary = implode(', ', array_map(fn($k) => ucfirst($k), $keys));
                          echo '<small class="text-muted font-monospace">' . htmlspecialchars('Changed: ' . $summary) . '</small>';
                        } else {
                          echo '<small class="text-muted">' . htmlspecialchars($details ?: '—') . '</small>';
                        }
                      ?>
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
    $('#logsTable').DataTable({
        pageLength: 25,
        order: [[1, 'desc']],
        language: { search: "_INPUT_", searchPlaceholder: "Search audit logs..." }
    });
});
</script>
</body>
</html>
