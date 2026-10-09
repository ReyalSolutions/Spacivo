<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($first && $last) {
            $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("sssi", $first, $last, $phone, $userId);
            if ($stmt->execute()) {
                $_SESSION['name'] = $first . ' ' . $last;
                $_SESSION['first_name'] = $first;
                $userName = htmlspecialchars($_SESSION['name']);
                $msg = "Profile updated successfully!";
            }
        }
    } elseif ($action === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $currHash = $stmt->get_result()->fetch_assoc()['password'] ?? '';

        if (password_verify($currentPass, $currHash)) {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $upd = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $newHash, $userId);
            $upd->execute();
            $msg = "Password changed successfully!";
        } else {
            $msg = "Current password was incorrect.";
            $msgType = 'danger';
        }
    }
}

$userRes = $db->query("SELECT * FROM users WHERE id = {$userId}");
$profileData = $userRes ? $userRes->fetch_assoc() : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Profile – <?= htmlspecialchars($siteName) ?> Admin</title>
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
          <h4 class="fw-bold mb-1 text-dark">Administrator Profile</h4>
          <p class="text-muted small mb-0">Personal credentials, name, and security settings</p>
        </div>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Account Details -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm" style="border-radius:18px;">
            <div class="card-body p-4">
              <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Personal Details</h5>
              <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="row g-3">
                  <div class="col-6">
                    <label class="form-label small fw-semibold">First Name</label>
                    <input type="text" name="first_name" class="form-control rounded-3" value="<?= htmlspecialchars($profileData['first_name'] ?? '') ?>" required>
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-semibold">Last Name</label>
                    <input type="text" name="last_name" class="form-control rounded-3" value="<?= htmlspecialchars($profileData['last_name'] ?? '') ?>" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label small fw-semibold">Email (Read Only)</label>
                    <input type="email" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($profileData['email'] ?? '') ?>" readonly>
                  </div>
                  <div class="col-12">
                    <label class="form-label small fw-semibold">Contact Phone</label>
                    <input type="text" name="phone" class="form-control rounded-3" value="<?= htmlspecialchars($profileData['phone'] ?? '') ?>">
                  </div>
                  <div class="col-12 text-end pt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Profile</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Security / Password -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm" style="border-radius:18px;">
            <div class="card-body p-4">
              <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Security & Password</h5>
              <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label small fw-semibold">Current Password</label>
                    <input type="password" name="current_password" class="form-control rounded-3" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label small fw-semibold">New Password</label>
                    <input type="password" name="new_password" class="form-control rounded-3" required>
                  </div>
                  <div class="col-12 text-end pt-2">
                    <button type="submit" class="btn btn-outline-danger rounded-pill px-4">Change Password</button>
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
