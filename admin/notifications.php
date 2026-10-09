<?php
require_once __DIR__ . '/components/auth_check.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notifications – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Notifications Broadcast</h4>
          <p class="text-muted small mb-0">System alerts, booking updates, and owner registration alerts</p>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="border-radius:18px;">
        <div class="card-body p-4">
          <div class="d-flex flex-column gap-3">
            <div class="p-3 bg-light rounded-3 d-flex align-items-start gap-3">
              <div class="kpi-icon bg-light-primary text-primary" style="width:40px;height:40px;font-size:1.1rem;">
                <i class="ti ti-bell-ringing"></i>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <h6 class="fw-bold text-dark mb-0">StayHub System Initialized</h6>
                  <small class="text-muted">Today</small>
                </div>
                <p class="mb-0 text-muted small">All services, database tables, and monolithic admin routes are active.</p>
              </div>
            </div>
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
