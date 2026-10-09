<?php
$title = "Administrative Audit Manifest";
require_once 'app/views/layouts/admin_header.php';
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-5 align-items-end animate__animated animate__fadeIn">
        <div class="col">
            <h6 class="text-uppercase text-primary fw-800 ls-2 mb-1">System Intelligence</h6>
            <h2 class="fw-900 text-dark mb-0">Administrative Audit <span class="text-gradient-primary">Manifest</span></h2>
            <p class="text-muted mb-0">High-fidelity chronological record of system-wide administrative orchestrations.</p>
        </div>
        <div class="col-auto">
            <div class="badge bg-white shadow-sm border rounded-pill px-4 py-3 text-dark d-flex align-items-center gap-3">
                <div class="flex-shrink-0 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <div class="smaller text-muted text-uppercase fw-800">Retention Scope</div>
                    <div class="fw-900 small">Last 200 Events</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table Container -->
    <div class="card border-0 shadow-premium overflow-hidden animate__animated animate__fadeInUp" style="border-radius: 32px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-dark text-white rounded-xl shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <div>
                            <h5 class="fw-800 mb-0">Event Stream</h5>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-danger rounded-pill px-4 fw-bold transition-all me-2 d-none" id="btnDeleteSelected">
                        <i class="fa-solid fa-trash-can me-2"></i>Delete Selected
                    </button>
                    <button class="btn btn-light rounded-pill px-4 fw-bold transition-all" onclick="window.location.reload()">
                        <i class="fa-solid fa-rotate me-2"></i>Refresh Stream
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="logsTable" class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th class="py-3 text-center" style="width: 40px;">
                                <input type="checkbox" class="form-check-input border-secondary shadow-sm" id="checkAllLogs">
                            </th>
                            <th class="py-3 text-uppercase smaller fw-800 text-muted">Administrator</th>
                            <th class="py-3 text-uppercase smaller fw-800 text-muted text-center">Action Orchestration</th>
                            <th class="py-3 text-uppercase smaller fw-800 text-muted">Target Identity</th>
                            <th class="py-3 text-uppercase smaller fw-800 text-muted">Chronology</th>
                            <th class="py-3 text-uppercase smaller fw-800 text-muted text-end pe-4">Payload</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Modal Container -->
<div id="modalContainer"></div>

<!-- UI Dependencies -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<script>
$(document).ready(function() {
    const formatAction = (action) => {
        let color = 'primary', icon = 'fa-circle-info';
        
        if (action.indexOf('CREATE') === 0) { color = 'success'; icon = 'fa-plus-circle'; }
        else if (action.indexOf('UPDATE') === 0) { color = 'info'; icon = 'fa-pen-to-square'; }
        else if (action.indexOf('DELETE') === 0) { color = 'danger'; icon = 'fa-trash-can'; }
        else if (action.indexOf('SYNC') !== -1) { color = 'indigo'; icon = 'fa-arrows-rotate'; }
        else if (action === 'OWNER_HOUSE_CREATED') { color = 'success'; icon = 'fa-house-circle-check'; }
        else if (action === 'OWNER_HOUSE_UPDATED') { color = 'info'; icon = 'fa-house-circle-xmark'; }
        else if (action === 'OWNER_HOUSE_DELETED') { color = 'danger'; icon = 'fa-house-fire'; }
        else if (action === 'OWNER_HOUSE_IMAGES_UPLOADED') { color = 'primary'; icon = 'fa-cloud-arrow-up'; }
        else if (action === 'OWNER_HOUSE_IMAGE_DELETED') { color = 'warning'; icon = 'fa-image'; }
        else if (action === 'OWNER_TENANT_MOVED_OUT') { color = 'secondary'; icon = 'fa-person-walking-arrow-right'; }
        
        return { color, icon, label: action.replace(/_/g, ' ') };
    };

    const formatDate = (dateStr) => {
        const d = new Date(dateStr);
        const dateSpan = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        let hours = d.getHours(), minutes = d.getMinutes(), seconds = d.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12; hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        const timeSpan = `${hours}:${minutes}:${seconds} ${ampm} - UTC`;
        return { dateSpan, timeSpan };
    };

    const formatFullDate = (dateStr) => {
        const d = new Date(dateStr);
        const dateSpan = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        let hours = d.getHours(), minutes = d.getMinutes(), seconds = d.getSeconds();
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        return `${dateSpan} - ${hours}:${minutes}:${seconds}`;
    };

    const dt = $('#logsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/tenant/?url=admin/logs_data',
        order: [[5, 'desc']],
        columns: [
            {
                data: 'id',
                orderable: false,
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    return `<input type="checkbox" class="form-check-input border-secondary shadow-sm log-check" value="${row.id}">`;
                }
            },
            { 
                data: 'first_name',
                orderable: true,
                render: function(data, type, row) {
                    const fname = row.first_name || '';
                    const lname = row.last_name || '';
                    const initial = (fname.substring(0,1) + lname.substring(0,1)).toUpperCase();
                    const name = `${fname} ${lname}`;
                    const act = formatAction(row.action);
                    return `
                        <div class="d-flex align-items-center gap-3 ps-2">
                            <div class="avatar-sm bg-${act.color}-subtle text-${act.color} rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px;">
                                ${initial}
                            </div>
                            <div>
                                <div class="fw-800 text-dark small mb-0">${$('<div>').text(name).html()}</div>
                                <div class="smaller text-muted text-uppercase fw-800">Administrator ID: ${row.admin_id}</div>
                            </div>
                        </div>`;
                }
            },
            {
                data: 'action',
                orderable: true,
                className: 'text-center',
                render: function(data, type, row) {
                    const act = formatAction(row.action);
                    return `
                        <span class="badge bg-${act.color}-subtle text-${act.color} border border-${act.color} rounded-pill px-3 py-2 fw-800 smaller letter-spacing-1">
                            <i class="fa-solid ${act.icon} me-2"></i>${act.label}
                        </span>`;
                }
            },
            {
                data: 'target_first',
                orderable: true,
                render: function(data, type, row) {
                    if (row.target_id) {
                        const tFirst = row.target_first || '';
                        const tLast = row.target_last || '';
                        const targetName = (tFirst === '' && tLast === '') ? 'Internal Logic Segment' : `${tFirst} ${tLast}`;
                        return `
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-bullseye text-muted smaller opacity-50"></i>
                                <div class="fw-bold small">${$('<div>').text(targetName).html()}</div>
                                <span class="badge bg-light text-dark smaller rounded-pill border">#${row.target_id}</span>
                            </div>`;
                    } else {
                        return `<span class="text-muted smaller fw-bold px-2 py-1 bg-light rounded-pill border border-dashed">Global System Scope</span>`;
                    }
                }
            },
            {
                data: 'created_at',
                orderable: true,
                render: function(data, type, row) {
                    const dtInfo = formatDate(row.created_at);
                    return `
                        <div class="small fw-800 text-dark mb-0">${dtInfo.dateSpan}</div>
                        <div class="smaller text-muted fw-bold">${dtInfo.timeSpan}</div>`;
                }
            },
            {
                data: 'id',
                orderable: false,
                className: 'text-end pe-4',
                render: function(data, type, row) {
                    // Store row data in a global payload object mapped by ID for modal generation
                    window[`logData_${row.id}`] = row;
                    return `
                        <button class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm border-0 transition-all hover-scale btn-inspect" data-log-id="${row.id}">
                            <i class="fa-solid fa-terminal me-2 small"></i>Inspect
                        </button>`;
                }
            }
        ],
        language: {
            emptyTable: `
                <div class="py-5">
                    <i class="fa-solid fa-database text-muted mb-3 fs-1 opacity-25"></i>
                    <h5 class="text-muted fw-bold">No administrative events recorded yet.</h5>
                    <p class="text-muted small">System activity will bloom here as orchestrations occur.</p>
                </div>`
        }
    });

    $(document).on('click', '.btn-inspect', function() {
        const logId = $(this).data('log-id');
        const row = window[`logData_${logId}`];
        if (!row) return;

        $('#modalContainer').empty();
        const act = formatAction(row.action);
        const adminName = `${row.first_name || ''} ${row.last_name || ''}`;
        const escapedDetails = $('<div>').text(row.details).html();

        const modalHtml = `
            <div class="modal fade" id="dynamicLogModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
                        <div class="modal-header border-0 p-4 pb-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-${act.color}-subtle text-${act.color} rounded-xl shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="fa-solid ${act.icon}"></i>
                                </div>
                                <div>
                                    <h4 class="fw-800 m-0">Payload <span class="text-${act.color}">Inspection</span></h4>
                                    <p class="text-muted small m-0">Event Identifier: ${row.id}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded-4 border h-100">
                                        <label class="smaller text-uppercase fw-800 text-muted mb-2 d-block">Orchestration Origin</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-sm bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                                <i class="fa-solid fa-user-shield text-${act.color}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small">${$('<div>').text(adminName).html()}</div>
                                                <div class="smaller text-muted">Authorized Administrator</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded-4 border h-100">
                                        <label class="smaller text-uppercase fw-800 text-muted mb-2 d-block">Timeline Marker</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-sm bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                                <i class="fa-solid fa-clock text-${act.color}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small">${formatFullDate(row.created_at)}</div>
                                                <div class="smaller text-muted">Precision Chronology (UTC)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <label class="smaller text-uppercase fw-800 text-muted mb-2 d-block">Data Matrix Digest</label>
                            <div class="rounded-4 p-4 shadow-sm border overflow-auto" style="max-height: 400px; background-color: #0f172a; border-color: #1e293b !important;">
                                <pre class="m-0 font-monospace smaller" style="color: #22d3ee; line-height: 1.6; white-space: pre-wrap;">${escapedDetails}</pre>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-5 fw-bold" data-bs-dismiss="modal">Close Digest</button>
                        </div>
                    </div>
                </div>
            </div>`;

        $('#modalContainer').html(modalHtml);
        const modalInstance = new bootstrap.Modal(document.getElementById('dynamicLogModal'));
        modalInstance.show();
    });

    // Bulk Delete Logic
    const toggleDeleteBtn = () => {
        const anyChecked = $('.log-check:checked').length > 0;
        if (anyChecked) {
            $('#btnDeleteSelected').removeClass('d-none').addClass('animate__animated animate__fadeIn');
        } else {
            $('#btnDeleteSelected').addClass('d-none').removeClass('animate__animated animate__fadeIn');
            $('#checkAllLogs').prop('checked', false);
        }
    };

    $('#checkAllLogs').on('change', function() {
        $('.log-check').prop('checked', $(this).prop('checked'));
        toggleDeleteBtn();
    });

    $(document).on('change', '.log-check', function() {
        if (!$(this).prop('checked')) {
            $('#checkAllLogs').prop('checked', false);
        } else if ($('.log-check:checked').length === $('.log-check').length) {
            $('#checkAllLogs').prop('checked', true);
        }
        toggleDeleteBtn();
    });

    dt.on('draw', function() {
        $('#checkAllLogs').prop('checked', false);
        toggleDeleteBtn();
    });

    $('#btnDeleteSelected').on('click', function() {
        const selectedIds = [];
        $('.log-check:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: 'Purge selected logs?',
            text: `You are about to irreversibly delete ${selectedIds.length} administrative event(s).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#e2e8f0',
            confirmButtonText: '<i class="fa-solid fa-trash-can me-2"></i>Purge Selected',
            cancelButtonText: '<span class="text-dark fw-bold">Cancel</span>',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'btn btn-danger rounded-pill px-4 fw-bold shadow-sm',
                cancelButton: 'btn rounded-pill px-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/tenant/?url=admin/delete_logs',
                    type: 'POST',
                    data: {
                        log_ids: selectedIds,
                        csrf_token: '<?= Csrf::token() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Purged',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            dt.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Network Error', 'Could not communicate with the server.', 'error');
                    }
                });
            }
        });
    });
});
</script>
            </div>
        </div>
    </div>
</div>

<style>
.text-gradient-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.shadow-premium {
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 10px 10px -5px rgba(0,0,0,0.02);
}
.rounded-xl { border-radius: 16px; }
.hover-scale:hover { transform: scale(1.05); }
.ls-2 { letter-spacing: 0.15em; }
.shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05); }
.bg-indigo { background-color: #6366f1 !important; }
.text-indigo { color: #6366f1 !important; }
.bg-indigo-subtle { background-color: #e0e7ff !important; }
.last-child-mb-0:last-child { margin-bottom: 0 !important; }

/* DataTables Styling Overrides */
.dataTables_wrapper .dataTables_length, 
.dataTables_wrapper .dataTables_filter, 
.dataTables_wrapper .dataTables_info, 
.dataTables_wrapper .dataTables_paginate {
    padding: 1rem 1.5rem;
    color: #4b5563 !important; /* text-muted equivalent */
    font-weight: 600;
    font-size: 0.875rem;
}
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.375rem 0.75rem;
    color: #1f2937;
    background-color: #f9fafb;
    margin-left: 0.5rem;
    outline: none;
    transition: all 0.2s;
}
.dataTables_wrapper .dataTables_length select:focus,
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}
.dataTables_wrapper .pagination .page-link {
    border-radius: 0.375rem;
    margin: 0 0.125rem;
    border: none;
    color: #4b5563;
    font-weight: 600;
}
.dataTables_wrapper .pagination .page-item.active .page-link {
    background-color: #4f46e5;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1), 0 2px 4px -1px rgba(79, 70, 229, 0.06);
}

/* DataTables Custom Polish & Visibility Fixes */
.dataTables_length, 
.dataTables_filter, 
.dataTables_info, 
.dataTables_paginate,
.dataTables_length label,
.dataTables_filter label { 
    color: #1e293b !important; /* Bold Slate Dark - FORCED */
    font-size: 0.85rem; 
    font-weight: 700 !important; 
}

.dataTables_length select { 
    border-radius: 10px !important; 
    padding: 6px 12px !important; 
    border: 1px solid #cbd5e1 !important; 
    background: #ffffff !important;
    color: #1e293b !important;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 800 !important;
}
.dataTables_length select:hover { border-color: #2563eb !important; }

.dataTables_filter input {
    border-radius: 12px !important;
    padding: 8px 16px !important;
    border: 1px solid #cbd5e1 !important; 
    background: #ffffff !important;
    width: 320px !important;
    margin-left: 12px !important;
    transition: all 0.2s;
    color: #0f172a !important;
    font-weight: 500;
}

.pagination-rounded .page-link { border-radius: 8px !important; margin: 0 3px; border: none !important; background: #f1f5f9; color: #64748b; padding: 8px 14px; }
.pagination-rounded .page-item.active .page-link { background: #2563eb !important; color: white !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }

.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
