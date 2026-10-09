<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<style>
/* Elite Tenant Management Design System */
:root {
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.3);
    --accent-indigo: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    --accent-rose: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    --accent-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.tenant-container {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Glass Stats Cards */
.stats-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.stats-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 45px rgba(31, 38, 135, 0.12);
}

.icon-box {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Tenant Management Cards */
.tenant-card {
    border: none;
    border-radius: 20px;
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(226, 232, 240, 0.8);
    position: relative;
    overflow: hidden;
}
.tenant-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    background: var(--accent-indigo);
    opacity: 0;
    transition: opacity 0.3s;
}
.tenant-card:hover {
    transform: translateY(-10px) scale(1.01);
    box-shadow: 0 25px 60px rgba(79, 70, 229, 0.08);
    border-color: rgba(99, 102, 241, 0.2);
}
.tenant-card:hover::before {
    opacity: 1;
}

.tenant-avatar {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    background: var(--accent-indigo);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 850;
    font-size: 1.5rem;
    box-shadow: 0 8px 20px rgba(79, 70, 229, 0.2);
    border: 2px solid #fff;
}

.status-badge {
    padding: 8px 18px;
    border-radius: 100px;
    font-size: 0.65rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.badge-approved { background: #f0fdf4; color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
.badge-pending { background: #fffbeb; color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
.badge-moved-out { background: #fff1f2; color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.2); }

.tenant-detail-item {
    padding: 12px;
    background: #f8fafc;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
    transition: background 0.3s;
}
.tenant-card:hover .tenant-detail-item {
    background: #fff;
}

.action-btn {
    padding: 12px 24px;
    border-radius: 16px;
    font-weight: 800;
    font-size: 0.8rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-ledger {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.btn-ledger:hover {
    background: #e2e8f0;
    color: #1e293b;
    transform: translateY(-2px);
}

.btn-move-out {
    background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
    color: #e11d48;
    border: 1px solid rgba(225, 29, 72, 0.15);
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.08);
}
.btn-move-out:hover {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: white;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 10px 25px rgba(225, 29, 72, 0.3);
    border-color: transparent;
}

.breadcrumb-custom {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #64748b;
}

</style>

<div class="container-fluid px-4 py-5 tenant-container">
    <!-- Header Hero -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-8">
            <nav class="breadcrumb-custom mb-3 d-flex align-items-center gap-2">
                <a href="/tenant/?url=owner/houses" class="text-decoration-none text-muted hover-primary">Portfolio</a>
                <i class="fa-solid fa-chevron-right small opacity-50"></i>
                <?php if ($house): ?>
                    <span class="text-muted"><?= htmlspecialchars($house['name']) ?></span>
                    <i class="fa-solid fa-chevron-right small opacity-50"></i>
                    <span class="text-primary">Tenants</span>
                <?php else: ?>
                    <span class="text-primary">Global Tenant Ledger</span>
                <?php endif; ?>
            </nav>
            <h1 class="display-5 fw-900 text-dark mb-2 letter-spacing--2">
                <?= $house ? 'Property Occupancy' : 'Global Tenant Ledger' ?>
            </h1>
            <p class="text-muted fs-5 mb-0 fw-500">
                <?= $house ? 'Managing active tenants and residency logs for ' . htmlspecialchars($house['name']) : 'Comprehensive overview of all active and historical residencies across your portfolio.' ?>
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
            <a href="/tenant/?url=owner/houses" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-800 d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>BACK TO ASSETS</span>
            </a>
        </div>
    </div>

    <!-- Analytics Dashboard -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-sky-50 text-sky-600" style="background:#f0f9ff; color:#0284c7;">
                        <i class="fa-solid fa-users-viewfinder fa-xl"></i>
                    </div>
                </div>
                <div class="text-muted small fw-800 uppercase letter-spacing-1 mb-1">Total Residents</div>
                <div class="h2 mb-0 fw-900"><?= count($bookings) ?> <small class="text-muted fw-500 h6">Contracts</small></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-emerald-50 text-emerald-600" style="background:#ecfdf5; color:#059669;">
                        <i class="fa-solid fa-circle-check fa-xl"></i>
                    </div>
                </div>
                <div class="text-muted small fw-800 uppercase letter-spacing-1 mb-1">Active Tenancy</div>
                <div class="h2 mb-0 fw-900">
                    <?php 
                        $active = count(array_filter($bookings, fn($b) => !$b['is_moved_out'] && $b['status'] === 'approved'));
                        echo $active;
                    ?>
                    <small class="text-muted fw-500 h6">Verified</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-amber-50 text-amber-600" style="background:#fff7ed; color:#d97706;">
                        <i class="fa-solid fa-sack-dollar fa-xl"></i>
                    </div>
                </div>
                <div class="text-muted small fw-800 uppercase letter-spacing-1 mb-1">Financial Volume</div>
                <div class="h2 mb-0 fw-900">
                    <?php 
                        $totalVal = array_reduce($bookings, fn($c, $b) => $c + (float)$b['total_amount'], 0);
                        echo '₱' . number_format($totalVal, 0);
                    ?>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); border: none; color: white;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-white bg-opacity-20 text-white shadow-none">
                        <i class="fa-solid fa-door-open fa-xl"></i>
                    </div>
                </div>
                <div class="text-white text-opacity-70 small fw-800 uppercase letter-spacing-1 mb-1">Moved Out Residents</div>
                <div class="h2 mb-0 fw-900">
                    <?php 
                        $movedOutCount = count(array_filter($bookings, fn($b) => (bool)$b['is_moved_out']));
                        echo $movedOutCount;
                    ?>
                    <small class="text-white text-opacity-50 fw-500 h6">Historical</small>
                </div>
                <div class="text-white text-opacity-40 small mt-3 fw-600">Total Residency Transitions</div>
            </div>
        </div>
    </div>

    <!-- DataTable Controls Container (Top) -->
    <div id="tenant-top-controls" class="mb-4"></div>

    <!-- Hidden Table for DataTables Core -->
    <table id="tenantTable" class="d-none w-100">
        <thead>
            <tr>
                <th>Date</th>
                <th>Tenant</th>
                <th>Property</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Tenant Ledger Mobile Grid -->
    <div id="tenantGrid" class="row g-4 mb-5">
        <!-- Cards injected via drawCallback -->
    </div>

    <!-- DataTable Controls Container (Bottom) -->
    <div id="tenant-bottom-controls" class="mt-4"></div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    const table = $('#tenantTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: "/tenant/?url=owner/bookings_data",
            type: "POST",
            data: function(d) {
                d.house_id = "<?= isset($houseId) && $houseId ? (int)$houseId : '' ?>";
                // Capture active_tab from URL for 'owner/bookings/approved' handling
                d.active_tab = (window.location.href.indexOf('bookings/approved') !== -1) ? 'approved' : '';
            }
        },
        columns: [
            { data: 'created_at' },
            { data: 'tenant_name' },
            { data: 'boarding_house_name' },
            { data: 'total_amount' },
            { data: 'status' }
        ],
        order: [[0, "desc"]],
        dom: '<"top">rt<"bottom"ip><"clear">',
        pageLength: 10,
        drawCallback: function(settings) {
            const api = this.api();
            const rows = api.rows({page:'current'}).data();
            const container = $('#tenantGrid');
            container.empty();

            if (rows.length === 0) {
                container.html(`
                    <div class="col-12 text-center py-5">
                        <div class="p-5 bg-white rounded-5 shadow-sm border border-dashed border-2">
                            <div class="mb-4">
                                <i class="fa-solid fa-folder-open text-primary bg-primary bg-opacity-10 p-5 rounded-circle" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="fw-900 text-dark mb-3">No Residency Records</h2>
                            <p class="text-muted fs-5 mb-0 mx-auto" style="max-width: 500px;">When residents book units in this property, their management profiles and payment contexts will appear here.</p>
                        </div>
                    </div>
                `);
                return;
            }

            rows.each(function(t) {
                const isMovedOut = t.is_moved_out === true || t.is_moved_out === 1;
                const isApproved = t.status === 'approved';
                let badgeHTML = '';
                if (isMovedOut) {
                    badgeHTML = '<div class="status-badge badge-moved-out mt-1"><i class="fa-solid fa-door-open"></i> FORMER RESIDENT</div>';
                } else if (isApproved) {
                    badgeHTML = '<div class="status-badge badge-approved mt-1"><i class="fa-solid fa-user-check"></i> ACTIVE TENANT</div>';
                } else {
                    badgeHTML = '<div class="status-badge badge-pending mt-1"><i class="fa-solid fa-clock"></i> PENDING ('+t.status+')</div>';
                }

                const initial = t.tenant_name ? t.tenant_name.charAt(0).toUpperCase() : '?';
                const amountFormatted = parseFloat(t.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2});
                
                const startDateObj = new Date(t.start_date);
                const startDateFormatted = startDateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                
                let endDateFormatted = '<span class="text-primary">Rolling</span>';
                if (t.end_date && t.end_date !== '0000-00-00') {
                    endDateFormatted = new Date(t.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                }

                let actionHtml = `
                    <form method="POST" action="/tenant/?url=owner/payments" class="flex-grow-1 m-0">
                        <input type="hidden" name="tenancy_id" value="${t.id}">
                        <input type="hidden" name="csrf_token" value="${t.csrf_token}">
                        <button type="submit" class="action-btn btn-ledger w-100 d-flex justify-content-center align-items-center">
                            <i class="fa-solid fa-receipt me-2"></i>LEDGER
                        </button>
                    </form>
                `;

                if (!isMovedOut && isApproved) {
                    const safeName = t.tenant_name.replace(/'/g, "\\'");
                    actionHtml += `
                        <form method="POST" action="/tenant/?url=owner/move_out" id="moveOutForm_${t.id}" class="flex-grow-1 m-0">
                            <input type="hidden" name="tenancy_id" value="${t.id}">
                            <input type="hidden" name="end_date" id="moveOutDate_${t.id}">
                            <input type="hidden" name="csrf_token" value="${t.csrf_token}">
                            <button type="button" class="action-btn btn-move-out w-100 d-flex justify-content-center align-items-center gap-2" onclick="confirmMoveOut(${t.id}, '${safeName}')">
                                <i class="fa-solid fa-person-walking-arrow-right"></i>
                                <span>MOVE OUT</span>
                            </button>
                        </form>
                    `;
                }

                const card = `
                    <div class="col-xl-6 col-lg-6">
                        <div class="tenant-card p-3 h-100 d-flex flex-column animate-fade-up">
                            <div class="d-flex flex-column align-items-start mb-3 gap-2">
                                <div class="d-flex align-items-center gap-2 overflow-hidden w-100">
                                    <div class="tenant-avatar flex-shrink-0">${initial}</div>
                                    <div class="tenant-meta text-wrap text-break">
                                        <h3 class="h6 fw-800 text-dark mb-0 text-wrap text-break">${t.tenant_name}</h3>
                                        ${badgeHTML}
                                    </div>
                                </div>
                                <div class="text-start w-100 pt-2 border-top mt-1">
                                    <div class="text-muted smaller fw-800 uppercase mb-0">Contract Value</div>
                                    <div class="h5 fw-900 text-primary mb-0">₱${amountFormatted}</div>
                                </div>
                            </div>

                             <div class="row g-2 mb-3 mt-auto">
                                <div class="col-12">
                                    <div class="tenant-detail-item">
                                        <div class="mb-2">
                                            <div class="text-muted smaller fw-800 mb-1"><i class="fa-solid fa-door-open me-2"></i>UNIT ASSIGNED</div>
                                            <div class="fw-700 text-dark text-wrap small">${t.room_name}</div>
                                        </div>
                                        <div class="pt-2 border-top">
                                            <div class="text-muted smaller fw-800 mb-1"><i class="fa-solid fa-calendar-days me-2"></i>TENANCY PERIOD</div>
                                            <div class="fw-700 text-dark small">${startDateFormatted} - ${endDateFormatted}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-column pt-2 border-top mt-auto gap-2">
                                <div class="d-flex flex-column justify-content-end gap-2 w-100">
                                    ${actionHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }
    });

    // Custom Top Controls Implementation
    const filterInput = $('#tenantTable_filter').detach();
    const lengthInput = $('#tenantTable_length').detach();

    filterInput.find('input').attr('placeholder', 'Search tenants, units...').addClass('form-control rounded-pill px-4 py-2 border shadow-sm').css({'min-width': '250px', 'background-color': '#f1f5f9'});
    lengthInput.find('select').addClass('form-select rounded-pill px-3 py-2 border shadow-sm').css({'min-width': '80px', 'background-color': '#f1f5f9'});

    filterInput.addClass('d-flex justify-content-md-end w-100 w-md-auto');
    lengthInput.addClass('d-flex align-items-center gap-2');

    $('#tenant-top-controls').html(`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div id="control-length" class="w-100 w-md-auto"></div>
            <div id="control-search" class="w-100 w-md-auto"></div>
        </div>
    `);
    $('#control-length').append(lengthInput);
    $('#control-search').append(filterInput);

    // Custom Bottom Controls Implementation
    const infoText = $('#tenantTable_info').detach();
    const paginateCtrl = $('#tenantTable_paginate').detach();

    infoText.addClass('small fw-600 text-muted');
    $('#tenant-bottom-controls').html(`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 p-3 bg-white border rounded-4 shadow-sm w-100">
            <div id="control-info" class="text-center text-md-start"></div>
            <div id="control-paginate" class="d-flex justify-content-center"></div>
        </div>
    `);
    $('#control-info').append(infoText);
    $('#control-paginate').append(paginateCtrl);
});

function confirmMoveOut(id, name) {
    const today = new Date().toISOString().split('T')[0];
    
    Feedback.fire({
        title: 'Confirm Move-out',
        html: `
            <div class="text-start mb-3">
                <p class="text-muted fw-500 mb-4">Are you sure you want to process the move-out for <strong>${name}</strong>? This will free up the room slot for new tenants immediately.</p>
                <label class="form-label fw-800 small text-uppercase ls-1 text-dark">Departure Date</label>
                <input type="date" id="swalMoveOutDate" class="form-control rounded-3" value="${today}" max="${today}">
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'CONFIRM MOVE-OUT',
        cancelButtonText: 'CANCEL',
        borderRadius: '32px',
        padding: '30px',
        background: '#ffffff',
        preConfirm: () => {
            const date = document.getElementById('swalMoveOutDate').value;
            if (!date) {
                Swal.showValidationMessage('Please select a move-out date');
                return false;
            }
            return date;
        },
        customClass: {
            title: 'fw-900 text-dark letter-spacing--1',
            text: 'fw-500 text-muted'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('moveOutDate_' + id).value = result.value;
            document.getElementById('moveOutForm_' + id).submit();
        }
    });
}
</script>

<?php if (isset($_SESSION['success'])): ?>
<script>
Feedback.fire({
    title: 'Success!',
    text: <?= json_encode($_SESSION['success'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    icon: 'success',
    confirmButtonColor: '#4f46e5',
    borderRadius: '24px',
    timer: 3000,
    timerProgressBar: true
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
Feedback.fire({
    title: 'Action Failed',
    text: <?= json_encode($_SESSION['error'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    icon: 'error',
    confirmButtonColor: '#ef4444',
    borderRadius: '24px'
});
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>


