<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';

$settingsModel = new SystemSetting($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteNameInput = trim($_POST['site_name'] ?? 'StayHub');
    $contactEmail = trim($_POST['contact_email'] ?? '');
    $currency = trim($_POST['currency'] ?? 'PHP');
    $maintenance = isset($_POST['maintenance_mode']) ? '1' : '0';

    $settingsModel->set('site_name', $siteNameInput);
    $settingsModel->set('contact_email', $contactEmail);
    $settingsModel->set('currency', $currency);
    $settingsModel->set('maintenance_mode', $maintenance);

    $msg = "System settings updated successfully!";
    $siteName = $siteNameInput;
}

$allSettings = $settingsModel->getAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>System Settings – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">System Configuration</h4>
          <p class="text-muted small mb-0">Global preferences, platform branding, and operational switches</p>
        </div>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm" style="border-radius:18px;">
            <div class="card-body p-4">
              <form method="POST">
                <div class="row g-4">
                  <div class="col-12">
                    <h5 class="fw-bold text-dark border-bottom pb-2">Branding & Platform Info</h5>
                  </div>
                  
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Application Name</label>
                    <input type="text" name="site_name" class="form-control rounded-3" value="<?= htmlspecialchars($allSettings['site_name'] ?? 'StayHub') ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Support / Contact Email</label>
                    <input type="email" name="contact_email" class="form-control rounded-3" value="<?= htmlspecialchars($allSettings['contact_email'] ?? 'support@stayhub.com') ?>">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Default Currency</label>
                    <select name="currency" class="form-select rounded-3">
                      <option value="PHP" <?= (($allSettings['currency'] ?? 'PHP') === 'PHP') ? 'selected' : '' ?>>PHP (₱ - Philippine Peso)</option>
                      <option value="USD" <?= (($allSettings['currency'] ?? '') === 'USD') ? 'selected' : '' ?>>USD ($ - US Dollar)</option>
                    </select>
                  </div>

                  <div class="col-12 mt-4">
                    <h5 class="fw-bold text-dark border-bottom pb-2">Maintenance Controls</h5>
                  </div>

                  <div class="col-12">
                    <div class="form-check form-switch d-flex align-items-center gap-3">
                      <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenanceSwitch" style="width: 2.5em; height: 1.25em;" <?= (($allSettings['maintenance_mode'] ?? '0') === '1') ? 'checked' : '' ?>>
                      <label class="form-check-label fw-semibold text-dark" for="maintenanceSwitch">
                        Enable Maintenance Mode
                        <span class="d-block text-muted small fw-normal">When enabled, regular tenants and owners see a maintenance page. Administrators can still access the dashboard.</span>
                      </label>
                    </div>
                  </div>

                  <div class="col-12 text-end pt-3 border-top">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                      <i class="ti ti-device-floppy me-1"></i> Save Changes
                    </button>
                  </div>
                </div>
              </form>
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
