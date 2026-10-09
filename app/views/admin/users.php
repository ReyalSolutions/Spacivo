<?php 
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/admin_header.php'; 
?>

<div class="animate-fade-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= ucfirst($filter ?? 'User') ?> Management</h2>
            <p class="text-muted small">Manage all registered <?= htmlspecialchars($filter ?? 'platform') ?> with advanced administrative filters</p>
        </div>
        <div class="d-flex gap-2 header-actions">
            <button class="btn btn-light-info rounded-pill px-4 shadow-sm" onclick="window.open('/tenant/?url=admin/print_users&role=<?= $filter ?>', '_blank', 'width=1000,height=800')">
                <i class="fa-solid fa-print me-2"></i>Export Manifest
            </button>
            <?php if ($this->hasPermission('manage_admins') || $this->hasPermission('manage_owners') || $this->hasPermission('manage_tenants')): ?>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm animate-pulse" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fa-solid fa-user-plus me-2"></i>Add New User
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="users-container pb-5">
        <!-- New Dedicated Control Containers -->
        <div id="users-controls-top" class="mb-4"></div>

        <!-- Grid Container for User Cards -->
        <div id="usersGrid" class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-3 animate-fade-up">
            <!-- Cards injected via drawCallback -->
        </div>

        <div id="users-controls-bottom" class="mt-4"></div>

        <!-- Hidden Table but visible container for DataTables controls -->
        <table id="usersTable" class="table w-100" style="display: none;">
            <thead>
                <tr>
                    <th>User Details</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registration</th>
                    <th>Management</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // Session Notifications
    <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({ icon: 'success', title: 'Action Successful', text: '<?= $_SESSION['success'] ?>', timer: 3000, showConfirmButton: false, background: '#f8fafc' });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({ icon: 'error', title: 'Action Failed', text: '<?= $_SESSION['error'] ?>', background: '#f8fafc' });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    if ($.fn.DataTable.isDataTable('#usersTable')) {
        $('#usersTable').DataTable().destroy();
    }
    
    const table = $('#usersTable').DataTable({
        ajax: '/tenant/?url=admin/get_users_json&role=<?= $filter ?>',
        columns: [
            { data: 'first_name' },
            { data: 'last_name' },
            { data: 'email' },
            { data: 'role' },
            { data: 'username' }
        ],
        pageLength: 9,
        lengthMenu: [6, 9, 12, 24],
        order: [[0, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Instant intelligence search...",
            paginate: { next: '<i class="fa-solid fa-chevron-right"></i>', previous: '<i class="fa-solid fa-chevron-left"></i>' }
        },
        dom: '<"d-flex justify-content-between align-items-center mb-4"lf>rt<"d-flex justify-content-between align-items-center mt-4"ip>',
        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const $grid = $('#usersGrid');
            const canEdit = <?= json_encode($this->hasPermission('manage_admins') || $this->hasPermission('manage_owners') || $this->hasPermission('manage_tenants')) ?>;

            $grid.empty();

            if (data.length === 0) {
                $grid.append('<div class="col-12 text-center py-5 text-muted bg-white rounded-4 border shadow-sm">No users found matching your search.</div>');
            } else {
                data.each(function(user) {
                    const status = user.status == 1;
                    const char = (user.first_name || 'U').charAt(0).toUpperCase();
                    const joined = new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    
                    let roleClass = 'badge-blue';
                    let icon = 'fa-people-roof';
                    const role = user.role.toLowerCase();
                    if (role === 'admin') { roleClass = 'badge-rose'; icon = 'fa-shield-halved'; }
                    else if (role === 'owner') { roleClass = 'badge-purple'; icon = 'fa-user-tie'; }

                    const card = `
                        <div class="col">
                            <div class="premium-stat-card d-block p-4 h-100 border-0 shadow-sm transition-hover position-relative overflow-hidden">
                                <div class="card-glow"></div>
                                <div class="d-flex justify-content-between align-items-start mb-4 position-relative">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar-lg bg-indigo-subtle text-indigo animate-scale-in">
                                            ${char}
                                        </div>
                                        <div>
                                            <h5 class="fw-bold m-0 text-dark">${user.first_name} ${user.last_name}</h5>
                                            <span class="badge rounded-pill fw-bold px-3 py-1 mt-1 ${roleClass} shadow-sm" style="font-size: 0.7rem;">
                                                <i class="fa-solid ${icon} me-1"></i>
                                                ${role.toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="status-indicator ${status ? 'bg-success' : 'bg-danger'} animate-pulse"></span>
                                        <span class="small fw-bold ${status ? 'text-success' : 'text-danger'} text-uppercase" style="font-size: 0.65rem;">${status ? 'Active' : 'Restricted'}</span>
                                    </div>
                                </div>

                                <div class="user-details mb-4 position-relative">
                                    <div class="mb-2 text-muted truncate" title="${user.email}">
                                        <i class="fa-solid fa-envelope me-2 text-indigo opacity-50"></i>
                                        <span class="small font-monospace">${user.email}</span>
                                    </div>
                                    <div class="mb-2 text-muted">
                                        <i class="fa-solid fa-phone me-2 text-indigo opacity-50"></i>
                                        <span class="small">${user.phone || 'No phone'}</span>
                                    </div>
                                    <div class="text-muted">
                                        <i class="fa-solid fa-calendar-check me-2 text-indigo opacity-50"></i>
                                        <span class="small">Joined: ${joined}</span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-auto pt-3 border-top position-relative">
                                    <button class="btn btn-sm btn-light-info flex-grow-1 view-user-btn" 
                                        data-bs-toggle="modal" data-bs-target="#viewUserModal"
                                        data-id="${user.id}" data-first="${user.first_name}" data-middle="${user.middle_name || ''}" 
                                        data-last="${user.last_name}" data-username="${user.username}" data-email="${user.email}" 
                                        data-phone="${user.phone}" data-role="${user.role.toUpperCase()}" 
                                        data-status="${status ? 'ACTIVE' : 'RESTRICTED'}"
                                        data-joined="${joined}"
                                    >
                                        <i class="fa-solid fa-eye me-1"></i> View
                                    </button>
                                    
                                    ${canEdit ? `
                                    <button class="btn btn-sm btn-light-primary flex-grow-1 edit-user-btn" 
                                        data-bs-toggle="modal" data-bs-target="#editUserModal"
                                        data-id="${user.id}" data-first="${user.first_name}" data-middle="${user.middle_name || ''}" 
                                        data-last="${user.last_name}" data-username="${user.username}" data-email="${user.email}" 
                                        data-phone="${user.phone}" data-role="${user.role.toLowerCase()}"
                                    >
                                        <i class="fa-solid fa-pen-nib me-1"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-icon-only ${status ? 'btn-light-danger' : 'btn-light-success'} restrict-user-btn" 
                                        data-id="${user.id}" data-username="${user.username}" data-status="${user.status}"
                                        title="${status ? 'Restrict Account' : 'Activate Account'}">
                                        <i class="fa-solid ${status ? 'fa-user-slash' : 'fa-user-check'}"></i>
                                    </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                    $grid.append(card);
                });
            }

            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');

            // Move controls to their dedicated containers (Explicit order: Length, then Filter)
            const $length = $('.dataTables_length');
            const $filter = $('.dataTables_filter');
            $('#users-controls-top').empty().append($length).append($filter);
            $('#users-controls-bottom').append($('.dataTables_info, .dataTables_paginate'));
            
            // Re-style shifted elements for fluid layout
            $('#users-controls-top').addClass('d-flex justify-content-between align-items-center mb-4 gap-3');
            $('#users-controls-bottom').addClass('d-flex justify-content-between align-items-center mt-4 gap-3');
        }
    });

    // Edit User Data Bridging
    $(document).on('click', '.edit-user-btn', function() {
        const btn = $(this);
        $('#editUserId').val(btn.data('id'));
        $('#editFirst').val(btn.data('first'));
        $('#editMiddle').val(btn.data('middle'));
        $('#editLast').val(btn.data('last'));
        $('#editUser').val(btn.data('username'));
        $('#editEmail').val(btn.data('email'));
        $('#editPhone').val(btn.data('phone'));
        $('#editRole').val(btn.data('role'));
        
        // Reset validation state
        $('#editUserForm').removeClass('was-validated');
        $('#editPass, #editConfirm').val('');
        $('#editStrengthMeter').css('width', '0%');
        $('#editStrengthText').text('System entropy analysis...').css('color', '#64748b');
    });

    // View User Data Bridging
    $(document).on('click', '.view-user-btn', function() {
        const btn = $(this);
        const st = btn.data('status');
        $('#viewFullName').text(btn.data('first') + ' ' + (btn.data('middle') ? btn.data('middle') + ' ' : '') + btn.data('last'));
        $('#viewUsername').text('@' + btn.data('username'));
        $('#viewEmail').text(btn.data('email'));
        $('#viewPhone').text(btn.data('phone'));
        $('#viewRole').text(btn.data('role'));
        $('#viewJoined').text(btn.data('joined'));
        $('#viewAvatarChar').text(btn.data('first').charAt(0).toUpperCase());
        
        const badge = $('#viewStatusBadge');
        badge.removeClass('badge-active badge-restricted').addClass(st === 'ACTIVE' ? 'badge-active' : 'badge-restricted');
        badge.html('<i class="fa-solid ' + (st === 'ACTIVE' ? 'fa-circle-check' : 'fa-circle-xmark') + ' me-1"></i> ' + st);
    });

    // Restrict/Activate Identity
    $(document).on('click', '.restrict-user-btn', function() {
        const btn = $(this);
        const userId = btn.data('id');
        const username = btn.data('username');
        const currentStatus = btn.data('status');
        const action = currentStatus == 1 ? 'Restrict' : 'Activate';
        const icon = currentStatus == 1 ? 'warning' : 'info';
        const color = currentStatus == 1 ? '#ef4444' : '#22c55e';

        Swal.fire({
            title: action + ' Identity?',
            text: "Are you sure you want to " + action.toLowerCase() + " access for @" + username + "?",
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: color,
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, ' + action + ' Access'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('<form>', {
                    'action': '/tenant/?url=admin/toggle_user_status',
                    'method': 'POST'
                }).append($('<input>', {
                    'type': 'hidden',
                    'name': 'csrf_token',
                    'value': $('meta[name="csrf-token"]').attr('content')
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'user_id',
                    'value': userId
                }));
                $('body').append(form);
                form.submit();
            }
        });
    });
});
</script>

<style>
/* USER CARD PREMIUM STYLES */
.user-avatar-lg {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 950;
    font-size: 1.5rem;
    box-shadow: 0 8px 16px rgba(99, 102, 241, 0.15);
}

.transition-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.transition-hover:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
}

.card-glow {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at center, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
    pointer-events: none;
}

.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#paginationNav .pagination {
    margin-bottom: 0;
}

#paginationNav .page-item.active .page-link {
    background: #6366f1 !important;
    border-color: #6366f1 !important;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.user-details i {
    width: 20px;
    text-align: center;
}

/* HIGH CONTRAST BADGES */
.badge-rose { background: #fee2e2 !important; color: #991b1b !important; border: 1px solid #f87171 !important; }
.badge-purple { background: #f3e8ff !important; color: #6b21a8 !important; border: 1px solid #a855f7 !important; }
.badge-blue { background: #dbeafe !important; color: #1e40af !important; border: 1px solid #3b82f6 !important; }

.bg-indigo-subtle { background: #e0e7ff !important; }
.text-indigo { color: #4338ca !important; }

.status-indicator { width: 10px; height: 10px; border-radius: 50%; box-shadow: 0 0 8px rgba(16, 185, 129, 0.5); }

.btn-icon-only { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; transition: all 0.2s; border: none; }
.btn-light-primary { background: #f1f5f9; color: #2563eb; }
.btn-light-primary:hover { background: #2563eb; color: white; transform: translateY(-2px); }
.btn-light-danger { background: #fff1f2; color: #f43f5e; }
.btn-light-danger:hover { background: #f43f5e; color: white; transform: translateY(-2px); }
.btn-light-info { background: #f0f9ff; color: #0ea5e9; }
.btn-light-info:hover { background: #0ea5e9; color: white; transform: translateY(-2px); }
.btn-light-success { background: #f0fdf4; color: #22c55e; }
.btn-light-success:hover { background: #22c55e; color: white; transform: translateY(-2px); }

/* DataTables Premium Polish */
.dataTables_length select, .dataTables_filter input {
    border: 1px solid #cbd5e1 !important;
    border-radius: 14px !important;
    padding: 10px 16px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    background-color: #f1f5f9 !important; /* Not White Background */
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
}

.dataTables_length label, .dataTables_filter label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    white-space: nowrap !important;
    color: #475569 !important; /* Dark text for readability */
    font-weight: 700 !important;
    margin-bottom: 0 !important;
}

/* Custom Search Icon */
.dataTables_filter input {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' /%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: 14px center !important;
    background-size: 18px !important;
    padding-left: 42px !important;
    width: 100% !important;
    max-width: 450px;
    margin-left: 0 !important;
}

/* Custom Select Arrow */
.dataTables_length select {
    min-width: 80px !important;
    padding-right: 35px !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E") !important;
    background-size: 14px !important;
    background-position: right 14px center !important;
    background-repeat: no-repeat !important;
    cursor: pointer;
}

.dataTables_length select:hover, .dataTables_filter input:hover {
    background-color: #f1f5f9 !important;
    border-color: #94a3b8 !important;
}

.dataTables_length select:focus, .dataTables_filter input:focus {
    outline: none !important;
    border-color: #6366f1 !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
}

.dataTables_filter {
    flex-grow: 1;
}

.dataTables_length label, .dataTables_filter label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    white-space: nowrap !important;
    font-weight: 700 !important;
    color: #475569 !important;
    margin-bottom: 0;
}

.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }

#users-controls-top, #users-controls-bottom {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
}

.dataTables_length {
    order: 1 !important; /* Left on desktop */
}

.dataTables_filter {
    order: 2 !important; /* Right on desktop */
    text-align: right !important;
}

@media (max-width: 576px) {
    .d-flex.justify-content-between.align-items-center.mb-4 {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 15px !important;
    }
    .header-actions {
        flex-direction: column !important;
        width: 100% !important;
    }
    .header-actions .btn {
        width: 100% !important;
    }
    #users-controls-top {
        display: flex !important;
        flex-direction: column !important; /* Simple vertical stack */
        gap: 15px !important;
    }
    .dataTables_filter {
        order: 1 !important; /* Search on TOP for mobile */
        width: 100% !important;
        margin: 0 !important;
        text-align: left !important;
    }
    .dataTables_filter input {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }
    .dataTables_length {
        order: 2 !important; /* Entries BELOW search */
        width: 100% !important;
        justify-content: center !important;
        opacity: 0.8 !important;
    }
    .dataTables_length label {
        width: auto !important;
        font-size: 0.75rem !important;
        gap: 5px !important;
    }
    .dataTables_length select {
        padding: 4px 8px !important;
        font-size: 0.75rem !important;
        height: auto !important;
    }
}
</style>

<?php 
require_once __DIR__ . '/modals/add_user.php'; 
require_once __DIR__ . '/modals/edit_user.php';
require_once __DIR__ . '/modals/view_user.php';
require_once __DIR__ . '/../layouts/admin_footer.php'; 
?>
