<?php 
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/management_header.php';
?>

<div class="animate-fade-up">
    <!-- Feedback Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 mb-4 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 mb-4 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold m-0 text-gradient-primary">RBAC Governance</h2>
            <p class="text-muted small mb-0">Dynamic orchestration of system-wide administrative authority and privilege manifests</p>
        </div>
        <div class="d-flex gap-2 header-actions">
            <button class="btn btn-outline-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                <i class="fa-solid fa-key me-2"></i>Define Permission
            </button>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="fa-solid fa-user-shield me-2"></i>Define Role
            </button>
        </div>
    </div>

    <!-- Governance Tabs -->
    <ul class="nav nav-pills custom-pills mb-4 gap-2" id="rbacTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-bold" data-bs-toggle="pill" data-bs-target="#roles-tab" type="button">
                <i class="fa-solid fa-shield-halved me-2"></i>Roles Governance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold" data-bs-toggle="pill" data-bs-target="#permissions-tab" type="button">
                <i class="fa-solid fa-vault me-2"></i>Permission Repository
            </button>
        </li>
    </ul>

    <div class="tab-content" id="rbacTabsContent">
        <!-- Roles Tab -->
        <div class="tab-pane fade show active" id="roles-tab" role="tabpanel">
            <div class="row g-4">
                <?php foreach ($roles as $role): ?>
                    <?php 
                        // Determine icon based on slug
                        $icon = 'fa-shield-halved';
                        $colorClass = 'rose';
                        if ($role['slug'] === 'owner') { $icon = 'fa-user-tie'; $colorClass = 'purple'; }
                        if ($role['slug'] === 'tenant') { $icon = 'fa-people-roof'; $colorClass = 'blue'; }
                        if (!in_array($role['slug'], ['admin', 'owner', 'tenant'])) { $icon = 'fa-user-gear'; $colorClass = 'emerald'; }
                    ?>
                    <div class="col-lg-4">
                        <div class="premium-stat-card p-4 h-100 shadow-lg border-0" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 24px;">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="role-icon bg-<?= $colorClass ?>-subtle text-<?= $colorClass ?> rounded-xl shadow-sm">
                                        <i class="fa-solid <?= $icon ?>"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-800 m-0"><?= htmlspecialchars($role['name']) ?></h5>
                                        <span class="badge bg-<?= $colorClass ?> text-white rounded-pill smaller px-2"><?= strtoupper($role['slug']) ?></span>
                                    </div>
                                </div>
                                <?php if (!in_array($role['slug'], ['admin', 'owner', 'tenant'])): ?>
                                    <form action="/tenant/?url=admin/delete_role" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                        <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                                        <button type="submit" class="btn btn-link text-danger p-0"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            
                            <div class="permission-list px-1">
                                <?php if (!empty($role['permissions'])): ?>
                                    <?php foreach (array_slice($role['permissions'], 0, 4) as $perm): ?>
                                        <div class="perm-item d-flex align-items-center gap-2 mb-2">
                                            <i class="fa-solid fa-check-circle text-success small"></i>
                                            <span class="small fw-medium"><?= htmlspecialchars($perm['name']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($role['permissions']) > 4): ?>
                                        <div class="text-muted smaller mt-1 font-italic">+ <?= count($role['permissions']) - 4 ?> more permissions</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-muted small">No permissions assigned.</div>
                                <?php endif; ?>
                            </div>
                            
                             <div class="mt-4 pt-3 border-top">
                                 <div class="d-flex gap-2">
                                     <button class="btn btn-sm btn-light flex-grow-1 rounded-pill fw-bold text-muted transition-all" data-bs-toggle="modal" data-bs-target="#viewRoleModal_<?= $role['id'] ?>">View</button>
                                     <button class="btn btn-sm btn-outline-primary rounded-circle p-0" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#editRoleModal_<?= $role['id'] ?>" title="Edit Role Metadata">
                                         <i class="fa-solid fa-pen-to-square"></i>
                                     </button>
                                     <button class="btn btn-sm btn-primary rounded-circle p-0" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#assignPermissionsModal_<?= $role['id'] ?>" title="Assign System Permissions">
                                         <i class="fa-solid fa-user-shield"></i>
                                     </button>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- View Role Details Modal -->
                    <div class="modal fade" id="viewRoleModal_<?= $role['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 32px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(20px);">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="role-icon bg-<?= $colorClass ?>-subtle text-<?= $colorClass ?> rounded-xl shadow-sm">
                                            <i class="fa-solid <?= $icon ?>"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-800 m-0"><?= htmlspecialchars($role['name']) ?></h4>
                                            <p class="text-muted small m-0">Detailed Authority Manifest</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-5">
                                            <div class="role-meta-box bg-light p-4 rounded-4 h-100">
                                                <label class="form-label fw-bold small text-muted text-uppercase mb-2">Role Overview</label>
                                                <div class="mb-4">
                                                    <div class="text-xs text-muted mb-1">System Slug</div>
                                                    <code class="bg-white px-2 py-1 rounded-pill text-<?= $colorClass ?> fw-bold small"><?= strtoupper($role['slug']) ?></code>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-muted mb-1">Description</div>
                                                    <p class="small fw-medium text-dark"><?= nl2br(htmlspecialchars($role['description'] ?: 'No formal description provided.')) ?></p>
                                                </div>
                                                <div class="mt-4 pt-3 border-top">
                                                    <div class="d-flex align-items-center justify-content-between text-muted smaller fw-bold mb-2">
                                                        <span>Active Governance</span>
                                                        <span class="text-success">Enabled</span>
                                                    </div>
                                                    <div class="progress" style="height: 6px; border-radius: 10px;">
                                                        <div class="progress-bar bg-<?= $colorClass ?>" style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label fw-bold small text-muted text-uppercase mb-3">Privilege Architecture</label>
                                            <div class="permission-grid scrollable-permissions" style="max-height: 400px; overflow-y: auto;">
                                                <?php if (!empty($role['permissions'])): ?>
                                                    <?php 
                                                    // Group role's permissions by category for the detail view
                                                    $rolePermsGrouped = [];
                                                    foreach ($role['permissions'] as $rp) {
                                                        $rolePermsGrouped[$rp['category']][] = $rp;
                                                    }
                                                    ?>
                                                    <?php foreach ($rolePermsGrouped as $cat => $ps): ?>
                                                        <div class="mb-3">
                                                            <div class="smaller fw-800 text-uppercase letter-spacing-1 mb-2 text-<?= $colorClass ?> opacity-75"><?= $cat ?> Operations</div>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <?php foreach ($ps as $p): ?>
                                                                    <div class="badge bg-white border text-dark fw-semibold rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-2">
                                                                        <i class="fa-solid fa-check-double text-success smaller"></i>
                                                                        <?= htmlspecialchars($p['name']) ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="text-center py-5 bg-light rounded-4 border-2 border-dashed">
                                                        <i class="fa-solid fa-folder-open text-muted mb-2 fs-2"></i>
                                                        <div class="text-muted small">No permissions associated with this role.</div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 p-4 pt-0">
                                    <button type="button" class="btn btn-<?= $colorClass ?> rounded-pill px-5 fw-bold shadow-sm" data-bs-dismiss="modal">Acknowledge Architecture</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Role Modal -->
                    <div class="modal fade" id="editRoleModal_<?= $role['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h4 class="fw-800 m-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Authority</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="/tenant/?url=admin/update_role" method="POST">
                                    <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                                    <div class="modal-body p-4">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold small text-dark text-uppercase mb-2">Role Identity</label>
                                            <input type="text" name="name" class="form-control rounded-pill px-4 border-2" value="<?= htmlspecialchars($role['name']) ?>" required>
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold small text-dark text-uppercase mb-2">Description</label>
                                            <textarea name="description" class="form-control rounded-4 border-2" rows="3"><?= htmlspecialchars($role['description']) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 p-4 pt-0">
                                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Update Identity</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Assign Permissions Modal -->
                    <div class="modal fade" id="assignPermissionsModal_<?= $role['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h4 class="fw-800 m-0"><i class="fa-solid fa-user-shield me-2 text-primary"></i>Assign Permissions</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="/tenant/?url=admin/update_role_permissions" method="POST">
                                    <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                                    <div class="modal-body p-4">
                                        <p class="text-muted small mb-4">Synchronize authority levels for **<?= htmlspecialchars($role['name']) ?>** across module categories.</p>
                                        <div class="permission-matrix bg-light p-4 rounded-4" style="max-height: 500px; overflow-y: auto;">
                                            <?php 
                                            $assignedIds = array_column($role['permissions'] ?? [], 'id');
                                            foreach ($permissionsByCategory as $category => $perms): 
                                            ?>
                                                <div class="mb-4 last-child-mb-0">
                                                    <h6 class="fw-800 text-primary small text-uppercase mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                                                        <span><i class="fa-solid fa-layer-group me-2"></i><?= ucfirst($category) ?> Module</span>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-link p-0 smaller text-decoration-none fw-800 text-primary opacity-75 hover-opacity-100" onclick="toggleCategory('<?= $category ?>', '<?= $role['id'] ?>', true)">SELECT ALL</button>
                                                            <span class="text-muted opacity-50">|</span>
                                                            <button type="button" class="btn btn-link p-0 smaller text-decoration-none fw-800 text-secondary opacity-75 hover-opacity-100" onclick="toggleCategory('<?= $category ?>', '<?= $role['id'] ?>', false)">DESELECT</button>
                                                        </div>
                                                    </h6>
                                                    <div class="row g-3">
                                                        <?php foreach ($perms as $p): ?>
                                                            <div class="col-md-6">
                                                                <div class="form-check form-switch custom-switch">
                                                                    <input class="form-check-input perm-check-<?= $role['id'] ?>-<?= $category ?>" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" id="perm_<?= $role['id'] ?>_<?= $p['id'] ?>" <?= in_array($p['id'], $assignedIds) ? 'checked' : '' ?>>
                                                                    <label class="form-check-label small fw-bold text-dark" for="perm_<?= $role['id'] ?>_<?= $p['id'] ?>">
                                                                        <?= htmlspecialchars($p['name']) ?>
                                                                        <div class="text-secondary smaller fw-normal opacity-75"><?= htmlspecialchars($p['description']) ?></div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 p-4 pt-0">
                                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Sync Permissions</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Permissions Tab -->
        <div class="tab-pane fade" id="permissions-tab" role="tabpanel">
            <!-- Desktop Table View -->
            <div class="premium-stat-card p-4 shadow-lg border-0 d-none d-md-block" style="background: rgba(255,255,255,0.95); border-radius: 24px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0">
                        <thead>
                            <tr class="text-muted small text-uppercase fw-bold">
                                <th class="border-0 px-4">Permission Identity</th>
                                <th class="border-0">Category</th>
                                <th class="border-0">Slug</th>
                                <th class="border-0 text-end px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permissionsByCategory as $category => $perms): ?>
                                <tr class="bg-light-subtle">
                                    <td colspan="4" class="px-4 py-3 fw-800 text-primary small text-uppercase">
                                        <i class="fa-solid fa-layer-group me-2"></i><?= ucfirst($category) ?> Module
                                    </td>
                                </tr>
                                <?php foreach ($perms as $p): ?>
                                    <tr>
                                        <td class="px-4">
                                            <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="text-muted smaller"><?= htmlspecialchars($p['description']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-indigo-subtle text-indigo rounded-pill px-3"><?= strtoupper($p['category']) ?></span>
                                        </td>
                                        <td>
                                            <code class="small text-muted"><?= $p['slug'] ?></code>
                                        </td>
                                        <td class="text-end px-4">
                                            <form action="/tenant/?url=admin/delete_permission" method="POST" onsubmit="return confirm('Delete this permission permanently? This may affect role authority.')">
                                                <input type="hidden" name="permission_id" value="<?= $p['id'] ?>">
                                                <button type="submit" class="btn btn-icon-only text-danger shadow-none">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="d-md-none">
                <?php foreach ($permissionsByCategory as $category => $perms): ?>
                    <div class="mb-4">
                        <h6 class="fw-800 text-primary small text-uppercase mb-3 ps-2">
                            <i class="fa-solid fa-layer-group me-2"></i><?= ucfirst($category) ?> Module
                        </h6>
                        <div class="row g-3">
                            <?php foreach ($perms as $p): ?>
                                <div class="col-12">
                                    <div class="premium-stat-card p-4 shadow-sm border-0 position-relative" style="background: white; border-radius: 20px;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['name']) ?></div>
                                                <div class="badge bg-indigo-subtle text-indigo rounded-pill px-2 smaller" style="font-size: 0.6rem;"><?= strtoupper($p['category']) ?></div>
                                            </div>
                                            <form action="/tenant/?url=admin/delete_permission" method="POST" onsubmit="return confirm('Delete this permission?')">
                                                <input type="hidden" name="permission_id" value="<?= $p['id'] ?>">
                                                <button type="submit" class="btn btn-link text-danger p-0"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                        <p class="text-muted smaller mb-0"><?= htmlspecialchars($p['description']) ?></p>
                                        <div class="mt-2"><code class="smaller text-muted opacity-50"><?= $p['slug'] ?></code></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Define New Permission Modal -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="fw-800 m-0"><i class="fa-solid fa-key me-2 text-primary"></i>Define Access Token</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/tenant/?url=admin/store_permission" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark text-uppercase mb-2"><i class="fa-solid fa-signature me-2 opacity-50"></i>Token Identity</label>
                            <input type="text" name="name" class="form-control rounded-pill px-4 border-2" placeholder="e.g. Audit Financials" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark text-uppercase mb-2"><i class="fa-solid fa-tags me-2 opacity-50"></i>Module Category</label>
                            <select name="category" class="form-select rounded-pill px-4 border-2">
                                <option value="core">Core System</option>
                                <option value="users">Identity Mgmt</option>
                                <option value="assets">Asset Portfolio</option>
                                <option value="finance">Financials</option>
                                <option value="tenant">Tenant Portal</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark text-uppercase mb-2"><i class="fa-solid fa-align-left me-2 opacity-50"></i>Manifest Scope</label>
                            <textarea name="description" class="form-control rounded-4 border-2" rows="2" placeholder="Describe what this privilege allows..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Define Token</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<!-- Define New Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="fw-800 m-0"><i class="fa-solid fa-user-shield me-2 text-primary"></i>Define New Authority</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/tenant/?url=admin/store_role" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark text-uppercase mb-2"><i class="fa-solid fa-id-badge me-2 opacity-50"></i>Role Identity</label>
                            <input type="text" name="name" class="form-control rounded-pill px-4 border-2" placeholder="e.g. Operations Manager" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark text-uppercase mb-2"><i class="fa-solid fa-comment-dots me-2 opacity-50"></i>Description</label>
                            <textarea name="description" class="form-control rounded-4 border-2" rows="2" placeholder="Describe the scope of this role..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark text-uppercase mb-3"><i class="fa-solid fa-table-list me-2 opacity-50"></i>Permission Matrix</label>
                            <div class="permission-matrix bg-light p-4 rounded-4">
                                <?php foreach ($permissionsByCategory as $category => $perms): ?>
                                    <div class="mb-4 last-child-mb-0">
                                        <h6 class="fw-800 text-primary small text-uppercase mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                                            <span><i class="fa-solid fa-layer-group me-2"></i><?= ucfirst($category) ?> Module</span>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-link p-0 smaller text-decoration-none fw-800 text-primary opacity-75 hover-opacity-100" onclick="toggleCategory('<?= $category ?>', 'new', true)">SELECT ALL</button>
                                                <span class="text-muted opacity-50">|</span>
                                                <button type="button" class="btn btn-link p-0 smaller text-decoration-none fw-800 text-secondary opacity-75 hover-opacity-100" onclick="toggleCategory('<?= $category ?>', 'new', false)">DESELECT</button>
                                            </div>
                                        </h6>
                                        <div class="row g-3">
                                            <?php foreach ($perms as $p): ?>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch custom-switch">
                                                        <input class="form-check-input perm-check-new-<?= $category ?>" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" id="perm_<?= $p['id'] ?>">
                                                        <label class="form-check-label small fw-bold text-dark" for="perm_<?= $p['id'] ?>">
                                                            <?= htmlspecialchars($p['name']) ?>
                                                            <div class="text-secondary smaller fw-normal opacity-75"><?= htmlspecialchars($p['description']) ?></div>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel Authority</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-shield-halved me-2"></i>Provision Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.role-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.bg-rose-subtle { background: #fff1f2; }
.text-rose { color: #f43f5e; }
.bg-rose { background: #f43f5e; }

.bg-purple-subtle { background: #f3e8ff; }
.text-purple { color: #9333ea; }
.bg-purple { background: #9333ea; }

.bg-blue-subtle { background: #eff6ff; }
.text-blue { color: #2563eb; }
.bg-blue { background: #2563eb; }

.bg-emerald-subtle { background: #ecfdf5; }
.text-emerald { color: #10b981; }
.bg-emerald { background: #10b981; }

.smaller { font-size: 0.65rem; font-weight: 800; letter-spacing: 0.05em; }

.premium-stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.premium-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
}

.custom-switch {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-left: 0 !important;
}

.custom-switch .form-check-input {
    width: 2.8em;
    height: 1.4em;
    cursor: pointer;
    margin-left: 0 !important;
    margin-top: 0 !important;
    flex-shrink: 0;
}

.custom-switch .form-check-label {
    cursor: pointer;
    user-select: none;
}

.transition-all { transition: all 0.3s ease; }

.custom-pills .nav-link {
    background: rgba(255,255,255,0.5);
    color: #64748b;
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-pills .nav-link.active {
    background: #4f46e5;
    color: white;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
    transform: translateY(-2px);
}

.text-gradient-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.bg-indigo-subtle { background: #eef2ff; }
.text-indigo { color: #4338ca; }

.btn-icon-only {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.btn-icon-only:hover {
    background: rgba(244, 63, 94, 0.1);
}

.last-child-mb-0:last-child { margin-bottom: 0 !important; }

@media (max-width: 576px) {
    .header-actions {
        flex-direction: column !important;
        width: 100% !important;
    }
    .header-actions .btn {
        width: 100% !important;
    }
    .custom-pills {
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        padding-bottom: 5px !important;
        gap: 8px !important;
    }
    .custom-pills .nav-item {
        flex: 1 0 auto !important;
    }
    .custom-pills .nav-link {
        width: 100% !important;
        white-space: nowrap !important;
        font-size: 0.75rem !important;
        padding: 8px 12px !important;
    }
}
</style>

<script>
function toggleCategory(category, roleId, state) {
    const checkboxes = document.querySelectorAll(`.perm-check-${roleId}-${category}`);
    checkboxes.forEach(cb => {
        cb.checked = state;
    });
}
</script>
<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
