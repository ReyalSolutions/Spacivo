<?php 
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/management_header.php';
?>

<style>
/* Premium Bookings & Control Polish */
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
}

/* Custom Select Arrow */
.dataTables_length select {
    min-width: 80px !important;
    padding-right: 35px !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E") !important;
    background-size: 14px !important;
    background-position: right 14px center !important;
    background-repeat: no-repeat !important;
    cursor: pointer;
}

.dataTables_length select:focus, .dataTables_filter input:focus {
    outline: none !important;
    border-color: #6366f1 !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
}

#bookings-top-controls, #bookings-bottom-controls {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    min-height: 60px; /* Prevent collapse */
    padding: 10px 0;
}

/* Row & Button elevation */
#bookings-table tbody tr {
    transition: all 0.2s ease;
}
#bookings-table tbody tr:hover {
    background-color: #f8fafc !important;
}
#bookings-table td {
    padding-top: 16px !important;
    padding-bottom: 16px !important;
}
.btn-premium {
    border-radius: 100px !important;
    padding: 8px 20px !important;
    font-weight: 700 !important;
    letter-spacing: 0.3px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}
.btn-premium:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}

.dataTables_length { order: 1 !important; }
.dataTables_filter { order: 2 !important; flex-grow: 1; text-align: right !important; }

@media (max-width: 576px) {
    .header-actions {
        flex-direction: column !important;
        width: 100% !important;
        align-items: stretch !important;
        gap: 10px !important;
    }
    .header-actions > span, .header-actions select {
        width: 100% !important;
    }
    #bookings-top-controls {
        flex-direction: column !important;
        gap: 15px !important;
    }
    .dataTables_filter { order: 1 !important; width: 100% !important; text-align: left !important; }
    .dataTables_filter input { max-width: none !important; }
    .dataTables_length { order: 2 !important; width: 100% !important; justify-content: center !important; opacity: 0.8 !important; }
}

.premium-stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
}
.premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1) !important; }

/* Status-based Badge Styles */
.status-badge-premium {
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.status-active { background: #ecfdf5; color: #059669; border: 1px solid #10b98133; }
.status-moved-out { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

.booking-card-metadata {
    background: #f8fafc;
    border-radius: 16px;
    padding: 12px;
    border: 1px solid #f1f5f9;
}
</style>

<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border">
        <div>
            <?php 
                $role = strtolower($_SESSION['role'] ?? 'owner');
                $title = "System-Wide Tenant Management";
                $desc = "Monitor all active residents and historical occupancy logs system-wide";
                if ($role === 'owner') {
                    $title = "Tenants & Residency Management";
                    $desc = "Manage active tenants and historical residency transitions for your properties";
                }
            ?>
            <h3 class="m-0 fw-bold fs-5 text-gradient-primary"><?= $title ?></h3>
            <p class="text-muted small mt-1 mb-0"><?= $desc ?></p>
        </div>
        <div class="d-flex align-items-center gap-2 header-actions">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold" id="total-count">...</span>
            <select id="bhouse-filter" class="form-select form-select-sm rounded-pill border-2 px-3 fw-600" style="min-width: 200px; cursor: pointer;">
                <option value="">All Boarding Houses</option>
                <?php foreach ($houses as $h): ?>
                    <option value="<?= (int)$h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- DataTable Controls Container (Top) -->
    <div id="bookings-top-controls" class="mb-4"></div>

    <!-- Responsive Layout Wrapper -->
    <div class="premium-stat-card d-block p-0 overflow-hidden shadow-sm border rounded-4 bg-white">
        <!-- Desktop Table (Hidden on Mobile) -->
        <div class="table-responsive p-3 d-none d-md-block">
            <table id="bookings-table" class="table table-hover align-middle mb-0" style="width:100%">
                <thead class="table-light">
                    <tr class="text-uppercase small fw-bold text-muted">
                        <th class="ps-4">ID</th>
                        <th>Tenant</th>
                        <th>House Name</th>
                        <th>Room</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Mobile Grid (Generated via JS) -->
        <div id="bookings-grid" class="d-md-none p-3 row g-3">
            <div class="col-12 text-center py-5 text-muted opacity-50">
                <i class="fa-solid ui-skeleton ui-skeleton-line mb-2"></i>
                <p class="small">Loading residents...</p>
            </div>
        </div>
    </div>

    <!-- DataTable Controls Container (Bottom) -->
    <div id="bookings-bottom-controls" class="mt-4"></div>
</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    var table = $('#bookings-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/tenant/?url=admin/bookings_data',
            type: 'GET',
            data: function(d) {
                d.bhouse_id = $('#bhouse-filter').val();
            },
            dataSrc: function(json) {
                $('#total-count').text(json.recordsFiltered + ' Total');
                return json.data;
            }
        },
        columns: [
            { data: 0, className: 'ps-4 fw-bold text-muted', width: '60px' },
            { data: 1, orderable: true },
            { data: 2, orderable: true },
            { data: 3, orderable: true },
            { data: 4, orderable: true },
            { data: 5, orderable: true },
            { data: 6, orderable: false, className: 'text-end pe-4' }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<div class="text-center py-4"><div class="ui-skeleton ui-skeleton-line text-primary" role="status"></div><p class="mt-2 text-muted small">Synchronizing residency data...</p></div>',
            emptyTable: '<div class="text-center py-5 text-muted"><i class="fa-solid fa-inbox fa-3x mb-3 d-block opacity-20"></i>No active residents found.</div>',
            zeroRecords: '<div class="text-center py-5 text-muted"><i class="fa-solid fa-magnifying-glass fa-3x mb-3 d-block opacity-20"></i>No matching residency data found.</div>',
            search: '',
            searchPlaceholder: 'Search tenant, house, room...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoFiltered: '(filtered from _MAX_ total)',
            paginate: {
                previous: '<i class="fa-solid fa-chevron-left"></i>',
                next: '<i class="fa-solid fa-chevron-right"></i>'
            }
        },
        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const $grid = $('#bookings-grid');
            
            // Generate Mobile Cards
            $grid.empty();
            if (data.length === 0) {
                $grid.html('<div class="col-12 text-center py-5 text-muted opacity-50"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>No data found.</div>');
            } else {
                data.each(function(row) {
                    const id = row[0];
                    const tenant = row[1];
                    const house = row[2];
                    const room = row[3];
                    const status = row[4];
                    const startDate = row[5];
                    const actions = row[6];
                    
                    const isActive = status.toLowerCase().includes('active');
                    const statusClass = isActive ? 'status-active' : 'status-moved-out';
                    const statusIcon = isActive ? 'fa-circle-check' : 'fa-circle-xmark';
                    
                    const card = `
                        <div class="col-12">
                            <div class="premium-stat-card p-4 shadow-sm border-0 border-top border-4 ${isActive ? 'border-primary' : 'border-secondary'} position-relative bg-white rounded-4 overflow-hidden text-start">
                                <!-- Row 1: Avatar, Name, ID -->
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-primary shadow-sm text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 56px; height: 56px; font-size: 1.5rem; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;">
                                        ${tenant.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <div class="fw-800 text-dark fs-5 mb-0">${tenant}</div>
                                        <div class="text-xs text-muted font-monospace opacity-75">ID: #${id.toString().padStart(5, '0')}</div>
                                    </div>
                                </div>

                                <!-- Row 2: Status (Full Row) -->
                                <div class="mb-4">
                                    <div class="status-badge-premium ${statusClass} py-2 px-3">
                                        <i class="fa-solid ${statusIcon} me-2"></i> ${status}
                                    </div>
                                </div>

                                <!-- Vertical Data Rows (Metadata) -->
                                <div class="mb-4">
                                    <div class="p-3 mb-2 rounded-3 bg-light d-flex align-items-center justify-content-between gap-3 overflow-hidden">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50 flex-shrink-0" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-house-user me-1"></i> RESIDENCE
                                        </div>
                                        <div class="small fw-800 text-dark text-truncate text-end ms-auto" style="max-width: 60%;">${house}</div>
                                    </div>
                                    <div class="p-3 mb-2 rounded-3 bg-light d-flex align-items-center justify-content-between gap-3">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50 flex-shrink-0" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-key me-1"></i> ROOM
                                        </div>
                                        <div class="small fw-800 text-primary text-end ms-auto">${room}</div>
                                    </div>
                                    <div class="p-3 rounded-3 bg-light d-flex align-items-center justify-content-between gap-3">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50 flex-shrink-0" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-calendar-check me-1"></i> COMMENCED
                                        </div>
                                        <div class="small fw-700 text-dark text-end ms-auto" style="white-space: nowrap;">${startDate}</div>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="d-grid pt-2">
                                    ${actions.split('class="').join('class="btn btn-premium ')}
                                </div>
                            </div>
                        </div>
                    `;
                    $grid.append(card);
                });
            }

            // Move controls to their dedicated containers (Optimized)
            const $length = $('.dataTables_length');
            const $filter = $('.dataTables_filter');
            const $info = $('.dataTables_info');
            const $paginate = $('.dataTables_paginate');

            if ($length.length) $('#bookings-top-controls').append($length);
            if ($filter.length) $('#bookings-top-controls').append($filter);
            if ($info.length) $('#bookings-bottom-controls').append($info);
            if ($paginate.length) $('#bookings-bottom-controls').append($paginate);

            // Pagination Styling
            $('.pagination').addClass('pagination-rounded gap-1');
            $('.page-link').addClass('rounded-3 border-0 shadow-none');
            
            // Adjust mobile alignments
            if ($(window).width() < 576) {
                $('.pagination').addClass('justify-content-center mt-3');
            } else {
                $('.pagination').removeClass('justify-content-center mt-3');
            }
        }
    });

    // Re-draw table on boarding house filter change
    $('#bhouse-filter').on('change', function() {
        table.ajax.reload();
    });

    // Move Out — SweetAlert2 + AJAX (no page reload)
    $(document).on('click', '.btn-move-out', function() {
        const btn      = $(this);
        const id       = btn.data('id');
        const csrf     = btn.data('csrf');

        Feedback.fire({
            title: 'Move Out Tenant?',
            html: 'This will <strong>release the room slot</strong> and mark the tenant as moved out. This action <u>cannot be undone</u>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-person-walking-arrow-right me-1"></i> Yes, Move Out',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
        }).then((result) => {
            if (!result.isConfirmed) return;

            btn.prop('disabled', true).html('<i class="fa-solid ui-skeleton ui-skeleton-line"></i> Processing...');

            $.ajax({
                url: '/tenant/?url=admin/move_out',
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                data: { booking_id: id, csrf_token: csrf },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Feedback.fire({
                            title: 'Done!',
                            text: 'Tenant has been marked as moved out and the room slot has been released.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(() => table.ajax.reload(null, false));
                    } else {
                        btn.prop('disabled', false).html('<i class="fa-solid fa-person-walking-arrow-right"></i> Move Out');
                        Feedback.fire('Failed', res.message || 'Could not process move out.', 'error');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-person-walking-arrow-right"></i> Move Out');
                    Feedback.fire('Error', 'Something went wrong. Please try again.', 'error');
                }
            });
        });
    });
});
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
