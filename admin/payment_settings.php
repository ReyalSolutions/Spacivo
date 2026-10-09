<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';
$settingsModel = new SystemSetting($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gcashNumber = trim($_POST['gcash_number'] ?? '');
    $gcashName = trim($_POST['gcash_account_name'] ?? '');
    $paymongoKey = trim($_POST['paymongo_public_key'] ?? '');
    $paymongoSecret = trim($_POST['paymongo_secret_key'] ?? '');
    $gcashEnabled = isset($_POST['gcash_enabled']) ? '1' : '0';
    $paymongoEnabled = isset($_POST['paymongo_enabled']) ? '1' : '0';

    $settingsModel->set('gcash_number', $gcashNumber);
    $settingsModel->set('gcash_account_name', $gcashName);
    $settingsModel->set('paymongo_public_key', $paymongoKey);
    $settingsModel->set('paymongo_secret_key', $paymongoSecret);
    $settingsModel->set('gcash_enabled', $gcashEnabled);
    $settingsModel->set('paymongo_enabled', $paymongoEnabled);

    $msg = "Payment gateway settings updated successfully!";
}

$allSettings = $settingsModel->getAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment Settings – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Payment Gateways & Protocols</h4>
          <p class="text-muted small mb-0">Configure payment gateways, GCash manual credentials, and PayMongo keys</p>
        </div>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="row g-4">
          <!-- GCash Direct -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:18px;">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="kpi-icon bg-light-primary text-primary">
                    <i class="ti ti-device-mobile"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold text-dark mb-0">GCash Direct</h5>
                    <small class="text-muted">Manual verification layer with reference numbers</small>
                  </div>
                </div>

                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label small fw-semibold">GCash Mobile Number</label>
                    <input type="text" name="gcash_number" class="form-control rounded-3" value="<?= htmlspecialchars($allSettings['gcash_number'] ?? '09123456789') ?>">
                  </div>
                  <div class="col-12">
                    <label class="form-label small fw-semibold">Account Registered Name</label>
                    <input type="text" name="gcash_account_name" class="form-control rounded-3" value="<?= htmlspecialchars($allSettings['gcash_account_name'] ?? 'STAYHUB ADMIN') ?>">
                  </div>
                  <div class="col-12">
                    <div class="form-check form-switch mt-2">
                      <input class="form-check-input" type="checkbox" name="gcash_enabled" id="gcashSwitch" <?= (($allSettings['gcash_enabled'] ?? '1') === '1') ? 'checked' : '' ?>>
                      <label class="form-check-label small fw-semibold text-dark" for="gcashSwitch">Active for Subscription Payments</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PayMongo API -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:18px;">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="kpi-icon bg-light-success text-success">
                    <i class="ti ti-credit-card"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold text-dark mb-0">PayMongo Gateway</h5>
                    <small class="text-muted">Automated card and e-wallet checkout API</small>
                  </div>
                </div>

                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label small fw-semibold">Public Key</label>
                    <input type="text" name="paymongo_public_key" class="form-control rounded-3 font-monospace small" value="<?= htmlspecialchars($allSettings['paymongo_public_key'] ?? '') ?>" placeholder="pk_test_...">
                  </div>
                  <div class="col-12">
                    <label class="form-label small fw-semibold">Secret Key</label>
                    <input type="password" name="paymongo_secret_key" class="form-control rounded-3 font-monospace small" value="<?= htmlspecialchars($allSettings['paymongo_secret_key'] ?? '') ?>" placeholder="sk_test_...">
                  </div>
                  <div class="col-12">
                    <div class="form-check form-switch mt-2">
                      <input class="form-check-input" type="checkbox" name="paymongo_enabled" id="paymongoSwitch" <?= (($allSettings['paymongo_enabled'] ?? '0') === '1') ? 'checked' : '' ?>>
                      <label class="form-check-label small fw-semibold text-dark" for="paymongoSwitch">Enable PayMongo Live Processing</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
              <i class="ti ti-device-floppy me-1"></i> Save Gateway Settings
            </button>
          </div>
        </div>
      </form>

    </div>
    
    <?php include __DIR__ . '/components/footer.php'; ?>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>
</body>
</html>
