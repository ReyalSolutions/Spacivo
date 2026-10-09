<?php 
/**
 * Plan Payments Audit Ledger
 * Purely AJAX-driven interface for monitoring owner subscription payments.
 */
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/management_header.php';
?>
<style>
/* Premium Stat Card Styling */
.premium-stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
}
.premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1) !important; }

.hover-rotate:hover i { transform: rotate(180deg); }
.hover-rotate i { transition: transform 0.5s ease; }
.cursor-pointer { cursor: pointer !important; }

/* Premium Selection Styling (SVG Arrows) */
.premium-select {
    appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E") !important;
    background-size: 12px !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    padding-right: 32px !important;
    border: 1px solid #cbd5e1 !important;
    background-color: #f1f5f9 !important; /* Not White */
    color: #334155 !important;
    transition: all 0.3s ease !important;
}
.premium-select:hover { border-color: #94a3b8 !important; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; }

/* Elevated Action Buttons */
.btn-premium {
    border-radius: 100px !important;
    font-weight: 700 !important;
    letter-spacing: 0.3px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}
.btn-premium:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
}

/* DataTable Standardized Controls (Not White) */
.dataTables_length select, .dataTables_filter input {
    border: 1px solid #cbd5e1 !important;
    border-radius: 14px !important;
    padding: 10px 16px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    background-color: #f1f5f9 !important; /* Not White */
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
}

.dataTables_length label, .dataTables_filter label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    white-space: nowrap !important;
    color: #475569 !important;
    font-weight: 700 !important;
    margin-bottom: 0 !important;
}

#plan-payments-top-controls, #plan-payments-bottom-controls {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    min-height: 60px;
    padding: 0 1rem;
}

.dataTables_length { order: 1 !important; }
.dataTables_filter { order: 2 !important; flex-grow: 1; text-align: right !important; }

@media (max-width: 576px) {
    .header-actions {
        width: 100% !important;
        justify-content: stretch !important;
        gap: 10px !important;
    }
    .header-actions .btn, .header-actions .form-select { flex: 1 !important; min-width: 0 !important; }
    #plan-payments-top-controls {
        flex-direction: column !important;
        gap: 15px !important;
    }
    .dataTables_filter { order: 1 !important; width: 100% !important; text-align: left !important; }
    .dataTables_filter input { width: 100% !important; margin-left: 0 !important; }
    .dataTables_length { order: 2 !important; width: 100% !important; justify-content: center !important; opacity: 0.8 !important; }
}

.pagination-rounded .page-link { border-radius: 8px !important; margin: 0 3px; border: none !important; background: #f1f5f9; color: #64748b; padding: 8px 14px; }
.pagination-rounded .page-item.active .page-link { background: #2563eb !important; color: white !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }

.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }

/* Mobile Card Specifics */
.owner-avatar-sm {
    width: 38px;
    height: 38px;
    background: #f1f5f9;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
}
</style>
<div class="container-fluid px-0 animate-fade-up">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4 p-4 bg-white rounded-4 shadow-sm border animate-fade-down">
        <div>
            <h3 class="m-0 fw-900 fs-5 text-gradient-primary">Subscription Revenue Audit</h3>
            <p class="text-muted small mt-1 mb-0 fw-600">Track and manage administrative income from owner renewals</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 header-actions w-100 w-md-auto">
            <!-- Billing Cycle Filter -->
            <div class="position-relative w-100 w-md-auto">
                <select id="cycleFilter" class="form-select rounded-pill px-3 shadow-sm premium-select fw-800 text-secondary btn-sm w-100 w-md-auto" style="min-width: 140px; font-size: 0.75rem; height: 40px;">
                    <option value="">All Cycles</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="position-relative w-100 w-md-auto">
                <select id="stateFilter" class="form-select rounded-pill px-3 shadow-sm premium-select fw-800 text-primary btn-sm w-100 w-md-auto" style="min-width: 130px; font-size: 0.75rem; height: 40px;">
                    <option value="">All Statuses</option>
                    <option value="paid">PAID</option>
                    <option value="pending">PENDING</option>
                    <option value="failed">FAILED</option>
                </select>
            </div>

            <a href="/tenant/?url=admin/print_plan_payments" id="printBtn" target="_blank" class="btn btn-primary btn-premium px-4 py-2 fw-800 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm border-0 flex-grow-1 flex-md-grow-0 btn-sm" style="font-size: 0.75rem; background: linear-gradient(135deg, #2563eb, #3b82f6); height: 40px;">
                <i class="fa-solid fa-print"></i>
                <span>PRINT LEDGER</span>
            </a>

            <button id="refreshTable" class="btn btn-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center transition-all hover-rotate btn-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="fa-solid fa-rotate-right text-primary" style="font-size: 0.9rem;"></i>
            </button>
        </div>
    </div>

    <!-- DataTable Controls Container (Top) -->
    <div id="plan-payments-top-controls" class="mb-4"></div>

    <!-- Stats Summary Row (AJAX Populated) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="premium-stat-card p-4 shadow-sm border-0 bg-white rounded-4 d-flex align-items-center gap-3">
                <div class="stat-icon-wrap bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 1.6rem; flex-shrink: 0; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted fw-bold text-uppercase mb-0" style="font-size: 0.7rem; letter-spacing: 0.08em; opacity: 0.8;">Total Collected</div>
                    <h4 id="statTotalCollected" class="fw-bold m-0 text-success" style="font-size: 1.5rem; letter-spacing: -0.02em;">â‚±0.00</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="premium-stat-card p-4 shadow-sm border-0 bg-white rounded-4 d-flex align-items-center gap-3">
                <div class="stat-icon-wrap bg-warning bg-opacity-10 text-warning rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 1.6rem; flex-shrink: 0; border: 1px solid rgba(245, 158, 11, 0.2);">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted fw-bold text-uppercase mb-0" style="font-size: 0.7rem; letter-spacing: 0.08em; opacity: 0.8;">Pending Volume</div>
                    <h4 id="statPendingVolume" class="fw-bold m-0 text-warning" style="font-size: 1.5rem; letter-spacing: -0.02em;">â‚±0.00</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="premium-stat-card p-4 shadow-sm border-0 bg-white rounded-4 d-flex align-items-center gap-3">
                <div class="stat-icon-wrap bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 1.6rem; flex-shrink: 0; border: 1px solid rgba(99, 102, 241, 0.2);">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted fw-bold text-uppercase mb-0" style="font-size: 0.7rem; letter-spacing: 0.08em; opacity: 0.8;">Paid Transactions</div>
                    <h4 id="statPaidCount" class="fw-bold m-0 text-primary" style="font-size: 1.5rem; letter-spacing: -0.02em;">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="premium-stat-card p-4 shadow-sm border-0 bg-white rounded-4 d-flex align-items-center gap-3">
                <div class="stat-icon-wrap bg-info bg-opacity-10 text-info rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 1.6rem; flex-shrink: 0; border: 1px solid rgba(14, 165, 233, 0.2);">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted fw-bold text-uppercase mb-0" style="font-size: 0.7rem; letter-spacing: 0.08em; opacity: 0.8;">Success Rate</div>
                    <h4 id="statSuccessRate" class="fw-bold m-0 text-info" style="font-size: 1.5rem; letter-spacing: -0.02em;">0%</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="premium-stat-card d-block p-0 overflow-hidden shadow-sm border rounded-4 bg-white mb-4 animate-fade-up">
        <div class="table-responsive p-3 d-none d-md-block">
            <table id="planPaymentsTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr class="text-uppercase small fw-bold text-muted border-0">
                        <th class="ps-4">Reference / Session</th>
                        <th>Owner</th>
                        <th>Plan & Cycle</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Status</th>
                        <th>Completed</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Mobile Grid -->
        <div id="plan-payments-grid" class="d-md-none p-3 row g-3">
             <div class="col-12 text-center py-5 text-muted opacity-50">
                <i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i>
                <p class="small">Loading Ledger...</p>
            </div>
        </div>
    </div>

    <!-- DataTable Controls Container (Bottom) -->
    <div id="plan-payments-bottom-controls" class="mt-4"></div>
</div>


<script>
$(document).ready(function() {
    const table = $('#planPaymentsTable').DataTable({
        ajax: {
            url: '/tenant/?url=admin/get_plan_payments_json',
            data: function (d) {
                d.status = $('#stateFilter').val();
                d.billing_cycle = $('#cycleFilter').val();
            },
            dataSrc: function(json) {
                updateStats(json.data);
                return json.data;
            }
        },
        order: [[5, 'desc']],
        pageLength: 10,
        dom: 'rtip', // Controls moved manually
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search ledger...",
            lengthMenu: "Show _MENU_ entries",
            info: "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ entries</span>",
            paginate: {
                previous: '<i class="fa-solid fa-chevron-left"></i>',
                next: '<i class="fa-solid fa-chevron-right"></i>'
            }
        },
        columns: [
            { 
                data: null,
                className: 'ps-4 py-3',
                render: function(data) {
                    const ref = data.transaction_ref || 'PENDING';
                    const gateway = (data.gateway || 'manual').toUpperCase();
                    let sessionInfo = '';
                    
                    if (gateway === 'PAYMONGO' && data.session_id) {
                        sessionInfo = `<div class="text-primary fw-bold" style="font-size: 0.65rem;">PAYMONGO ${data.session_id}</div>`;
                    } else if (data.session_id) {
                        sessionInfo = `<div class="text-muted small" style="font-size: 0.65rem;">${data.session_id}</div>`;
                    }
                    
                    return `
                        <div class="fw-bold text-dark">${ref}</div>
                        <div class="d-flex align-items-center gap-1 mt-1">
                            <span class="badge bg-light text-dark border" style="font-size: 0.6rem;">${gateway}</span>
                            ${sessionInfo}
                        </div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    const name = (data.first_name || 'N/A') + ' ' + (data.last_name || '');
                    return `
                        <div class="fw-bold text-dark">${name}</div>
                        <div class="small text-muted font-monospace" style="font-size: 0.7rem;">${data.email || 'no-email'}</div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    const plan = data.plan_name || 'System Plan';
                    const cycle = (data.billing_cycle || 'monthly').toUpperCase();
                    const cycleClass = cycle === 'YEARLY' ? 'text-primary' : 'text-info';
                    return `
                        <div class="fw-bold text-dark">${plan}</div>
                        <div class="small ${cycleClass} fw-bold" style="font-size: 0.7rem;">${cycle}</div>
                    `;
                }
            },
            {
                data: 'amount',
                className: 'text-center',
                render: function(amt) {
                    return `<div class="fw-bold text-dark fs-5">â‚±${parseFloat(amt).toLocaleString(undefined, {minimumFractionDigits: 2})}</div>`;
                }
            },
            {
                data: 'status',
                className: 'text-center',
                render: function(status) {
                    status = (status || 'pending').toLowerCase();
                    let badgeClass = 'bg-secondary';
                    let icon = 'fa-circle-question';
                    if (status === 'paid') { badgeClass = 'bg-success'; icon = 'fa-circle-check'; }
                    else if (status === 'pending') { badgeClass = 'bg-warning text-dark'; icon = 'fa-clock'; }
                    else if (status === 'failed') { badgeClass = 'bg-danger'; icon = 'fa-circle-xmark'; }
                    
                    return `
                        <span class="badge rounded-pill ${badgeClass} px-3 py-2 fw-bold shadow-sm badge-fixed-width" style="font-size: 0.7rem;">
                            <i class="fa-solid ${icon} me-1"></i>
                            ${status.toUpperCase()}
                        </span>
                    `;
                }
            },
            {
                data: 'paid_at',
                render: function(date, type, row) {
                    const displayDate = date || row.created_at;
                    const d = new Date(displayDate);
                    return `
                        <div class="text-dark small fw-bold">${d.toLocaleDateString()}</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">${d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                    `;
                }
            },
            {
                data: null,
                className: 'text-center pe-4',
                render: function(data) {
                    const isManual = (data.gateway || 'manual').toLowerCase() === 'manual';
                    const isPending = (data.status || 'pending').toLowerCase() === 'pending';
                    
                    if (isPending) {
                        return `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary border dropdown-toggle fw-bold px-3 rounded-pill" type="button" data-bs-toggle="dropdown" style="font-size: 0.75rem; letter-spacing: 0.02em;">
                                    <i class="fa-solid fa-pen-nib me-1"></i> Update Status
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2" style="font-size: 0.85rem; min-width: 180px;">
                                    <li><h6 class="dropdown-header text-uppercase text-muted extra-small fw-bold pb-2" style="font-size: 0.65rem;">Action Required</h6></li>
                                    <li><a class="dropdown-item text-success fw-bold update-status-btn rounded-3 py-2" href="#" data-id="${data.id}" data-status="paid" data-ref="${data.transaction_ref || 'PENDING'}"><i class="fa-solid fa-circle-check me-2"></i>Mark as Paid</a></li>
                                    <li><a class="dropdown-item text-danger fw-bold update-status-btn rounded-3 py-2" href="#" data-id="${data.id}" data-status="failed" data-ref="${data.transaction_ref || 'PENDING'}"><i class="fa-solid fa-circle-xmark me-2"></i>Mark as Failed</a></li>
                                </ul>
                            </div>
                        `;
                    }
                    return `<span class="text-muted small">---</span>`;
                }
            }
        ],
        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const $grid = $('#plan-payments-grid');
            
            // Generate Mobile Cards
            $grid.empty();
            if (data.length === 0) {
                $grid.html('<div class="col-12 text-center py-5 text-muted opacity-50"><i class="fa-solid fa-receipt fa-2x mb-2 d-block"></i>No transactions found.</div>');
            } else {
                data.each(function(row) {
                    const initials = (row.first_name ? row.first_name[0] : '') + (row.last_name ? row.last_name[0] : '');
                    const ref = row.transaction_ref || 'PENDING';
                    const gateway = (row.gateway || 'manual').toUpperCase();
                    const status = (row.status || 'pending').toLowerCase();
                    const cycle = (row.billing_cycle || 'monthly').toUpperCase();
                    
                    let badgeClass = 'bg-secondary';
                    let borderColor = '#e2e8f0';
                    if (status === 'paid') { badgeClass = 'bg-success'; borderColor = '#10b981'; } 
                    else if (status === 'pending') { badgeClass = 'bg-warning text-dark'; borderColor = '#f59e0b'; }
                    else if (status === 'failed') { badgeClass = 'bg-danger'; borderColor = '#ef4444'; }

                    const d = new Date(row.paid_at || row.created_at);
                    const formattedDate = d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                    let actionHtml = '';
                    if (status === 'pending') {
                        actionHtml = `
                            <div class="dropdown mt-4">
                                <button class="btn btn-primary w-100 rounded-pill py-3 fw-900 shadow-sm dropdown-toggle d-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-pen-nib"></i> UPDATE STATUS
                                </button>
                                <ul class="dropdown-menu w-100 shadow-lg border-0 rounded-4 p-2">
                                     <li><a class="dropdown-item text-success fw-bold update-status-btn py-2" href="#" data-id="${row.id}" data-status="paid" data-ref="${ref}"><i class="fa-solid fa-circle-check me-2"></i>Mark as Paid</a></li>
                                     <li><a class="dropdown-item text-danger fw-bold update-status-btn py-2" href="#" data-id="${row.id}" data-status="failed" data-ref="${ref}"><i class="fa-solid fa-circle-xmark me-2"></i>Mark as Failed</a></li>
                                </ul>
                            </div>
                        `;
                    }

                    const card = `
                        <div class="col-12">
                            <div class="premium-stat-card p-4 shadow-sm border-0 border-start border-4 position-relative bg-white rounded-4 overflow-hidden text-start" style="border-left-color: ${borderColor} !important;">
                                <!-- Top Row: Ref & Status -->
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <div class="fw-900 text-dark fs-6 mb-0">${ref}</div>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="badge bg-light text-dark border extra-small fw-800" style="font-size: 0.6rem;">${gateway}</span>
                                            ${row.session_id ? `<span class="text-primary fw-800 extra-small" style="font-size: 0.6rem;">${row.session_id.substring(0,12)}...</span>` : ''}
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill ${badgeClass} px-3 py-2 fw-800 shadow-sm" style="font-size: 0.65rem;">
                                        ${status.toUpperCase()}
                                    </span>
                                </div>

                                <!-- Owner Row -->
                                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-4 bg-light bg-opacity-50 border">
                                    <div class="owner-avatar-sm">${initials.toUpperCase()}</div>
                                    <div class="overflow-hidden">
                                        <div class="fw-800 text-dark text-truncate">${row.first_name} ${row.last_name}</div>
                                        <div class="text-xs text-muted text-truncate fw-600">${row.email || 'No Email'}</div>
                                    </div>
                                </div>

                                <!-- Metadata Grid -->
                                <div class="row g-3 mb-2">
                                    <div class="col-6">
                                        <div class="text-xs text-muted text-uppercase fw-800 mb-1 opacity-50">Plan / Cycle</div>
                                        <div class="fw-800 text-dark small">${row.plan_name}</div>
                                        <div class="text-xs fw-900 text-primary mt-1">${cycle}</div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="text-xs text-muted text-uppercase fw-800 mb-1 opacity-50">Amount</div>
                                        <div class="fw-900 text-dark fs-5">â‚±${parseFloat(row.amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between">
                                    <div class="text-xs text-muted fw-700">
                                        <i class="fa-solid fa-clock-rotate-left me-1"></i> ${formattedDate}
                                    </div>
                                </div>

                                ${actionHtml}
                            </div>
                        </div>
                    `;
                    $grid.append(card);
                });
            }

            // Move controls to their dedicated containers
            const $length = $('.dataTables_length');
            const $filter = $('.dataTables_filter');
            const $info = $('.dataTables_info');
            const $paginate = $('.dataTables_paginate');

            if ($length.length) $('#plan-payments-top-controls').append($length);
            if ($filter.length) $('#plan-payments-top-controls').append($filter);
            if ($info.length) $('#plan-payments-bottom-controls').append($info);
            if ($paginate.length) $('#plan-payments-bottom-controls').append($paginate);

            // Pagination Styling
            $('.pagination').addClass('pagination-rounded gap-1');
            $('.page-link').addClass('rounded-3 border-0 shadow-none');
            if ($(window).width() < 576) {
                $('.pagination').addClass('justify-content-center mt-3');
            }
        }
    });

    function updateStats(data) {
        let totalPaid = 0;
        let totalPending = 0;
        let paidCount = 0;
        let totalCount = data.length;

        data.forEach(item => {
            const amt = parseFloat(item.amount || 0);
            if (item.status === 'paid') {
                totalPaid += amt;
                paidCount++;
            } else if (item.status === 'pending') {
                totalPending += amt;
            }
        });

        $('#statTotalCollected').text('â‚±' + totalPaid.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#statPendingVolume').text('â‚±' + totalPending.toLocaleString(undefined, {minimumFractionDigits: 2}));
        $('#statPaidCount').text(paidCount);
        $('#statSuccessRate').text(totalCount > 0 ? Math.round((paidCount / totalCount) * 100) + '%' : '0%');
    }

    $('#stateFilter, #cycleFilter').on('change', () => {
        table.ajax.reload();
        updatePrintLink();
    });
    $('#refreshTable').on('click', () => table.ajax.reload());

    function updatePrintLink() {
        let url = '/tenant/?url=admin/print_plan_payments';
        const status = $('#stateFilter').val();
        const cycle = $('#cycleFilter').val();
        
        if (status) url += '&status=' + encodeURIComponent(status);
        if (cycle) url += '&billing_cycle=' + encodeURIComponent(cycle);
        
        $('#printBtn').attr('href', url);
    }

    $(document).on('click', '.update-status-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const status = $(this).data('status');
        const ref = $(this).data('ref');
        const csrf = '<?= Csrf::token() ?>';

        Swal.fire({
            title: 'Verify Transaction Status',
            html: `Are you sure you want to mark transaction <strong class="text-primary">${ref}</strong> as <span class="badge ${status === 'paid' ? 'bg-success' : 'bg-danger'}">${status.toUpperCase()}</span>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel',
            confirmButtonColor: status === 'paid' ? '#10b981' : '#ef4444',
            cancelButtonColor: '#64748b',
            backdrop: `rgba(15, 23, 42, 0.4)`,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '/tenant/?url=admin/update_plan_payment_status',
                    method: 'POST',
                    data: { payment_id: id, status: status, csrf_token: csrf }
                }).then(resp => {
                    if (!resp.success) {
                        throw new Error(resp.message || 'Update failed');
                    }
                    return resp;
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Status Updated!',
                    text: result.value.message,
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                table.ajax.reload(null, false);
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
