<?php
require_once __DIR__ . '/components/auth_check.php';

$userModel = new User($db);

// Fetch roles for the select dropdown
$rolesRes  = $db->query("SELECT id, name FROM roles ORDER BY id");
$rolesList = $rolesRes ? $rolesRes->fetch_all(MYSQLI_ASSOC) : [];

// Handle POST actions
$msg       = null;
$msgType   = 'success';
$resetData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ── CREATE ──────────────────────────────────────────────── */
    if ($action === 'create') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name']  ?? '');
        $email     = trim($_POST['email']      ?? '');
        $username  = trim($_POST['username']   ?? '');
        $phone     = trim($_POST['phone']      ?? '');
        $roleId    = (int)($_POST['role_id']   ?? 3);
        $password  = $_POST['password']        ?? 'Password123!';

        if (!$firstName || !$lastName || !$email || !$username) {
            $msg     = 'Please fill in all required fields before submitting.';
            $msgType = 'warning';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg     = "The email address you entered doesn't look valid. Please double-check it.";
            $msgType = 'warning';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $db->prepare(
                "INSERT INTO users (username, role_id, first_name, last_name, email, phone, password)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sisssss", $username, $roleId, $firstName, $lastName, $email, $phone, $hashed);

            if ($stmt->execute()) {
                $msg = "Account created for <strong>{$firstName} {$lastName}</strong>.";
            } elseif ($db->errno === 1062) {
                if (stripos($db->error, 'email') !== false) {
                    $msg = 'That email address is already registered to another account.';
                } elseif (stripos($db->error, 'username') !== false) {
                    $msg = 'That username is already taken. Please choose a different one.';
                } else {
                    $msg = 'An account with those details already exists. Please check the email or username.';
                }
                $msgType = 'danger';
            } else {
                $msg     = 'Something went wrong while creating the account. Please try again.';
                $msgType = 'danger';
            }
        }

    /* ── EDIT ────────────────────────────────────────────────── */
    } elseif ($action === 'edit') {
        $editId     = (int)($_POST['user_id']    ?? 0);
        $firstName  = trim($_POST['first_name']  ?? '');
        $middleName = trim($_POST['middle_name']  ?? '');
        $lastName   = trim($_POST['last_name']   ?? '');
        $email      = trim($_POST['email']       ?? '');
        $username   = trim($_POST['username']    ?? '');
        $phone      = trim($_POST['phone']       ?? '');
        $roleId     = (int)($_POST['role_id']    ?? 3);
        $status     = (int)($_POST['status']     ?? 1);

        if ($editId <= 0) {
            $msg     = 'Invalid request. Please refresh the page and try again.';
            $msgType = 'warning';
        } elseif (!$firstName || !$lastName || !$email || !$username) {
            $msg     = 'Please fill in all required fields before saving.';
            $msgType = 'warning';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg     = "The email address you entered doesn't look valid. Please double-check it.";
            $msgType = 'warning';
        } else {
            // Prevent de-activating or demoting your own account
            if ($editId === (int)$_SESSION['user_id'] && $status === 0) {
                $msg     = 'You cannot deactivate your own account.';
                $msgType = 'warning';
            } else {
                $stmt = $db->prepare(
                    "UPDATE users
                     SET first_name=?, middle_name=?, last_name=?, email=?,
                         username=?, phone=?, role_id=?, status=?
                     WHERE id=?"
                );
                $stmt->bind_param(
                    "sssssssii",
                    $firstName, $middleName, $lastName, $email,
                    $username, $phone, $roleId, $status, $editId
                );

                if ($stmt->execute()) {
                    $msg = "Changes saved for <strong>{$firstName} {$lastName}</strong>.";
                } elseif ($db->errno === 1062) {
                    if (stripos($db->error, 'email') !== false) {
                        $msg = 'That email address is already in use by another account.';
                    } elseif (stripos($db->error, 'username') !== false) {
                        $msg = 'That username is already taken. Please choose a different one.';
                    } else {
                        $msg = 'Another account already uses those details. Please check the email or username.';
                    }
                    $msgType = 'danger';
                } else {
                    $msg     = 'The changes could not be saved right now. Please try again.';
                    $msgType = 'danger';
                }
            }
        }

    /* ── RESET PASSWORD ──────────────────────────────────────── */
    } elseif ($action === 'reset_password') {
        $resetId = (int)($_POST['user_id'] ?? 0);

        if ($resetId <= 0) {
            $msg     = 'Invalid request. Please refresh the page and try again.';
            $msgType = 'warning';
        } else {
            $chars    = 'abcdefghjkmnpqrstuvwxyz';
            $upper    = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            $digits   = '23456789';
            $specials = '!@#$%&*';
            $plain    = $upper[random_int(0, strlen($upper) - 1)]
                      . substr(str_shuffle($chars), 0, 4)
                      . $digits[random_int(0, strlen($digits) - 1)]
                      . $digits[random_int(0, strlen($digits) - 1)]
                      . $specials[random_int(0, strlen($specials) - 1)];
            $plain  = str_shuffle($plain);
            $hashed = password_hash($plain, PASSWORD_DEFAULT);

            $uStmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $uStmt->bind_param("i", $resetId);
            $uStmt->execute();
            $uRow = $uStmt->get_result()->fetch_assoc();

            if (!$uRow) {
                $msg     = 'That user account could not be found. It may have already been deleted.';
                $msgType = 'danger';
            } else {
                $upStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upStmt->bind_param("si", $hashed, $resetId);

                if ($upStmt->execute()) {
                    $resetData = [
                        'name'     => htmlspecialchars(trim(($uRow['first_name'] ?? '') . ' ' . ($uRow['last_name'] ?? ''))),
                        'password' => $plain,
                    ];
                    $msg     = "Password has been reset for <strong>{$resetData['name']}</strong>.";
                    $msgType = 'success';
                } else {
                    $msg     = 'The password could not be reset right now. Please try again in a moment.';
                    $msgType = 'danger';
                }
            }
        }

    /* ── DELETE ──────────────────────────────────────────────── */
    } elseif ($action === 'delete') {
        $deleteId = (int)($_POST['user_id'] ?? 0);

        if ($deleteId <= 0) {
            $msg     = 'Invalid request. Please refresh the page and try again.';
            $msgType = 'warning';
        } elseif ($deleteId === (int)$_SESSION['user_id']) {
            $msg     = 'You cannot delete your own account while you are logged in.';
            $msgType = 'warning';
        } else {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $deleteId);

            if ($stmt->execute()) {
                $msg = 'The user account has been permanently removed.';
            } elseif ($db->errno === 1451) {
                $msg     = "This account can't be deleted because it has linked bookings or properties. Remove those first, then try again.";
                $msgType = 'danger';
            } else {
                $msg     = 'The account could not be deleted right now. Please try again.';
                $msgType = 'danger';
            }
        }
    }
}

// Fetch users with role name
$usersResult = $db->query("
    SELECT u.*, r.name as role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.id DESC
");
$usersList = $usersResult ? $usersResult->fetch_all(MYSQLI_ASSOC) : [];

$totalCount  = count($usersList);
$ownerCount  = count(array_filter($usersList, fn($u) => strtolower($u['role_name'] ?? '') === 'owner'));
$tenantCount = count(array_filter($usersList, fn($u) => strtolower($u['role_name'] ?? '') === 'tenant'));
$adminCount  = count(array_filter($usersList, fn($u) => strtolower($u['role_name'] ?? '') === 'admin'));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Accounts – <?= htmlspecialchars($siteName) ?> Admin</title>
  <?php include __DIR__ . '/components/links.php'; ?>
</head>
<body>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
     data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

  <?php include __DIR__ . '/components/sidebar.php'; ?>

  <div class="body-wrapper">
    <?php include __DIR__ . '/components/header.php'; ?>

    <div class="container-fluid">

      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h4 class="fw-bold mb-1 text-dark">User Management</h4>
          <p class="text-muted small mb-0">Monitor, create, and manage system accounts across all roles</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="ti ti-user-plus me-1"></i> Add New User
          </button>
        </div>
      </div>

      <!-- KPI Counters -->
      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-primary text-primary"><i class="ti ti-users"></i></div>
              <div><small class="text-muted d-block">All Users</small><h5 class="fw-bold mb-0"><?= $totalCount ?></h5></div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-info text-info"><i class="ti ti-building"></i></div>
              <div><small class="text-muted d-block">Property Owners</small><h5 class="fw-bold mb-0"><?= $ownerCount ?></h5></div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-success text-success"><i class="ti ti-bed"></i></div>
              <div><small class="text-muted d-block">Tenants</small><h5 class="fw-bold mb-0"><?= $tenantCount ?></h5></div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-warning text-warning"><i class="ti ti-shield"></i></div>
              <div><small class="text-muted d-block">Administrators</small><h5 class="fw-bold mb-0"><?= $adminCount ?></h5></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">User</th>
                  <th class="border-0">Contact</th>
                  <th class="border-0">Role</th>
                  <th class="border-0">Status</th>
                  <th class="border-0">Joined</th>
                  <th class="border-0 text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usersList as $u): ?>
                  <?php
                    $isActive  = (int)($u['status'] ?? 1) === 1;
                    $isSelf    = (int)$u['id'] === (int)$_SESSION['user_id'];
                    $fullName  = htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                    $safeJs    = htmlspecialchars(addslashes(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')));
                  ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-initials bg-light-primary text-primary">
                          <?= strtoupper(substr($u['first_name'] ?? 'U', 0, 1) . substr($u['last_name'] ?? '', 0, 1)) ?>
                        </div>
                        <div>
                          <span class="fw-bold text-dark d-block"><?= $fullName ?></span>
                          <small class="text-muted">@<?= htmlspecialchars($u['username'] ?? '') ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="small text-dark fw-medium"><?= htmlspecialchars($u['email']) ?></div>
                      <small class="text-muted"><?= htmlspecialchars($u['phone'] ?? '—') ?></small>
                    </td>
                    <td>
                      <?php
                        $r = strtolower($u['role_name'] ?? '');
                        if ($r === 'admin')     echo '<span class="badge bg-light-warning text-warning stat-badge">Admin</span>';
                        elseif ($r === 'owner') echo '<span class="badge bg-light-info text-info stat-badge">Owner</span>';
                        else                   echo '<span class="badge bg-light-success text-success stat-badge">Tenant</span>';
                      ?>
                    </td>
                    <td>
                      <?php if ($isActive): ?>
                        <span class="badge bg-light-success text-success stat-badge"><i class="ti ti-circle-check me-1"></i>Active</span>
                      <?php else: ?>
                        <span class="badge bg-light-danger text-danger stat-badge"><i class="ti ti-ban me-1"></i>Inactive</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <small class="text-muted"><?= date('M j, Y', strtotime($u['created_at'] ?? 'now')) ?></small>
                    </td>
                    <td class="text-end">
                      <div class="dropdown">
                        <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                          <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:10px; min-width:180px;">
                          <!-- Edit -->
                          <li>
                            <a href="javascript:void(0)" class="dropdown-item small py-2"
                               onclick="openEditModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">
                              <i class="ti ti-pencil me-2 text-info"></i> Edit User
                            </a>
                          </li>
                          <!-- Reset Password -->
                          <li>
                            <a href="javascript:void(0)" class="dropdown-item small py-2"
                               onclick="openResetModal(<?= (int)$u['id'] ?>, '<?= $safeJs ?>')">
                              <i class="ti ti-lock-open me-2 text-primary"></i> Reset Password
                            </a>
                          </li>
                          <li><hr class="dropdown-divider my-1"></li>
                          <!-- Delete -->
                          <li>
                            <a href="javascript:void(0)"
                               class="dropdown-item text-danger small py-2 <?= $isSelf ? 'disabled opacity-50' : '' ?>"
                               <?= $isSelf ? '' : "onclick=\"confirmDelete({$u['id']}, '{$safeJs}')\"" ?>>
                              <i class="ti ti-trash me-2"></i> Delete User
                            </a>
                          </li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div><!-- /container-fluid -->

    <?php include __DIR__ . '/components/footer.php'; ?>
  </div>
</div>

<!-- ============================================================
     ADD USER MODAL
     ============================================================ -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow" style="border-radius:16px;">
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="modal-header border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;border-radius:10px;background:rgba(93,135,255,.12);display:flex;align-items:center;justify-content:center;">
              <i class="ti ti-user-plus text-primary"></i>
            </div>
            <h5 class="modal-title fw-bold mb-0" id="addUserModalLabel">Create New User</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" class="form-control rounded-3" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Middle Name</label>
              <input type="text" name="middle_name" class="form-control rounded-3">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
              <input type="text" name="username" class="form-control rounded-3" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Phone</label>
              <input type="text" name="phone" class="form-control rounded-3">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Role <span class="text-danger">*</span></label>
              <select name="role_id" class="form-select rounded-3">
                <?php foreach ($rolesList as $rl): ?>
                  <option value="<?= (int)$rl['id'] ?>"><?= htmlspecialchars($rl['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
              <input type="text" name="password" class="form-control rounded-3 font-monospace" value="Password123!" required>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top p-3">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="ti ti-check me-1"></i>Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================
     EDIT USER MODAL
     ============================================================ -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow" style="border-radius:16px;">
      <form method="POST" id="editUserForm">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="user_id" id="editUserId">
        <div class="modal-header border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;border-radius:10px;background:rgba(19,222,185,.12);display:flex;align-items:center;justify-content:center;">
              <i class="ti ti-pencil text-success"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold mb-0" id="editUserModalLabel">Edit User</h5>
              <small class="text-muted" id="editUserSubtitle"></small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">

          <!-- Status Banner -->
          <div class="mb-4 p-3 rounded-3 d-flex align-items-center justify-content-between gap-3" style="background:#f8f9fc; border:1px dashed #dee2e6;">
            <div>
              <div class="fw-semibold text-dark small">Account Status</div>
              <div class="text-muted" style="font-size:.78rem;">Inactive accounts cannot log in.</div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="badge bg-light-danger text-danger stat-badge" id="statusBadgeInactive" style="display:none!important;"><i class="ti ti-ban me-1"></i>Inactive</span>
              <span class="badge bg-light-success text-success stat-badge" id="statusBadgeActive"><i class="ti ti-circle-check me-1"></i>Active</span>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="editStatusToggle"
                       onchange="syncStatus(this)" style="width:42px;height:22px;cursor:pointer;">
                <input type="hidden" name="status" id="editStatusHidden" value="1">
              </div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" id="editFirstName" class="form-control rounded-3" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Middle Name</label>
              <input type="text" name="middle_name" id="editMiddleName" class="form-control rounded-3">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" id="editLastName" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" id="editEmail" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
              <input type="text" name="username" id="editUsername" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Phone</label>
              <input type="text" name="phone" id="editPhone" class="form-control rounded-3">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Role <span class="text-danger">*</span></label>
              <select name="role_id" id="editRoleId" class="form-select rounded-3">
                <?php foreach ($rolesList as $rl): ?>
                  <option value="<?= (int)$rl['id'] ?>"><?= htmlspecialchars($rl['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success rounded-pill px-4"><i class="ti ti-device-floppy me-1"></i>Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================
     RESET PASSWORD MODAL
     ============================================================ -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
    <div class="modal-content border-0 shadow" style="border-radius:16px;">
      <form method="POST">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="user_id" id="resetUserId">
        <div class="modal-header border-bottom">
          <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;border-radius:10px;background:rgba(93,135,255,.12);display:flex;align-items:center;justify-content:center;">
              <i class="ti ti-lock-open text-primary"></i>
            </div>
            <h5 class="modal-title fw-bold mb-0">Reset Password</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-muted small mb-1">You are about to reset the password for:</p>
          <p class="fw-bold text-dark mb-3" id="resetUserName" style="font-size:1rem;"></p>
          <div class="alert alert-warning d-flex gap-2 align-items-start rounded-3 py-2 px-3" style="font-size:.82rem;">
            <i class="ti ti-alert-triangle mt-1 flex-shrink-0"></i>
            <span>A new random password will be generated. <strong>Copy it immediately — it will only be shown once.</strong></span>
          </div>
        </div>
        <div class="modal-footer border-top p-3">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="ti ti-refresh me-1"></i> Generate &amp; Reset
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================
     DELETE CONFIRM (hidden form + modal)
     ============================================================ -->
<form method="POST" id="deleteForm" style="display:none;">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="user_id" id="deleteUserId">
</form>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
    <div class="modal-content border-0 shadow" style="border-radius:16px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-danger">Delete User?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body py-3 text-muted small">
        You are about to permanently delete <strong id="deleteUserName" class="text-dark"></strong>. This action cannot be undone.
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" onclick="document.getElementById('deleteForm').submit()">
          <i class="ti ti-trash me-1"></i> Delete
        </button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>
<script>
$(document).ready(function () {
  $('#usersTable').DataTable({
    pageLength: 10,
    language: { search: '_INPUT_', searchPlaceholder: 'Search users...' }
  });
});

/* ── EDIT MODAL ────────────────────────────────────────────── */
function openEditModal(u) {
  document.getElementById('editUserId').value      = u.id;
  document.getElementById('editFirstName').value   = u.first_name  || '';
  document.getElementById('editMiddleName').value  = u.middle_name || '';
  document.getElementById('editLastName').value    = u.last_name   || '';
  document.getElementById('editEmail').value       = u.email       || '';
  document.getElementById('editUsername').value    = u.username    || '';
  document.getElementById('editPhone').value       = u.phone       || '';
  document.getElementById('editRoleId').value      = u.role_id     || 3;
  document.getElementById('editUserSubtitle').textContent = '@' + (u.username || '');

  const isActive = parseInt(u.status) === 1;
  const toggle   = document.getElementById('editStatusToggle');
  toggle.checked = isActive;
  document.getElementById('editStatusHidden').value = isActive ? '1' : '0';
  document.getElementById('statusBadgeActive').style.display   = isActive ? '' : 'none';
  document.getElementById('statusBadgeInactive').style.removeProperty('display');
  document.getElementById('statusBadgeInactive').style.display = isActive ? 'none' : '';

  new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function syncStatus(toggle) {
  const active = toggle.checked;
  document.getElementById('editStatusHidden').value = active ? '1' : '0';
  document.getElementById('statusBadgeActive').style.display   = active ? '' : 'none';
  document.getElementById('statusBadgeInactive').style.display = active ? 'none' : '';
}

/* ── RESET PASSWORD MODAL ──────────────────────────────────── */
function openResetModal(userId, userName) {
  document.getElementById('resetUserId').value = userId;
  document.getElementById('resetUserName').textContent = userName;
  new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

/* ── DELETE CONFIRM MODAL ──────────────────────────────────── */
function confirmDelete(userId, userName) {
  document.getElementById('deleteUserId').value = userId;
  document.getElementById('deleteUserName').textContent = userName;
  new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

/* ── FIRE TOAST FROM PHP ───────────────────────────────────── */
<?php if ($msg): ?>
window.addEventListener('DOMContentLoaded', () => {
  <?php if ($resetData): ?>
  ToastStack.create({
    type:     'success',
    title:    'Password Reset',
    message:  'New password for <strong><?= $resetData['name'] ?></strong>. Share it securely:',
    password: '<?= addslashes($resetData['password']) ?>',
    duration: 15000
  });
  <?php else: ?>
  ToastStack.create({
    type:    '<?= $msgType ?>',
    message: '<?= addslashes(strip_tags($msg)) ?>',
    duration: 6000
  });
  <?php endif; ?>
});
<?php endif; ?>
</script>
</body>
</html>
