<?php 
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/management_header.php';
?>

<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border">
        <div>
            <?php 
                $role = strtolower($_SESSION['role'] ?? 'tenant');
                $title = "Transaction Audit Ledger";
                $desc = "Monitor all system-wide financial transactions and rent payments";
                if ($role === 'owner') {
                    $title = "Property Transaction Ledger";
                    $desc = "Monitor financial transactions and rent payments for your properties";
                } elseif ($role === 'tenant' || $role === 'user') {
                    $title = "Personal Payment History";
                    $desc = "View your transaction and rent payment history";
                }
            ?>
            <h3 class="m-0 fw-bold fs-5 text-gradient-primary"><?= $title ?></h3>
            <p class="text-muted small mt-1 mb-0"><?= $desc ?></p>
        </div>
        <div class="d-flex align-items-center gap-2 header-actions">
            <!-- Property Filter -->
            <select id="houseFilter" class="form-select form-select-sm rounded-pill border-2 px-3 fw-600" style="min-width: 180px; cursor: pointer;">
                <option value="">All Properties</option>
                <?php foreach ($properties as $prop): ?>
                    <option value="<?= $prop['id'] ?>"><?= htmlspecialchars($prop['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Status Filter -->
            <select id="stateFilter" class="form-select form-select-sm rounded-pill border-2 px-3 fw-600" style="min-width: 140px; cursor: pointer;">
                <option value="">All States</option>
                <option value="paid">PAID</option>
                <option value="pending">PENDING</option>
                <option value="failed">FAILED</option>
            </select>

            <a href="/tenant/?url=admin/print_payments" id="exportPdfBtn" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center fw-bold">
                <i class="fa-solid fa-print me-2"></i>Export
            </a>
        </div>
    </div>

    <!-- DataTable Controls Container (Top) -->
    <div id="payments-top-controls" class="mb-4"></div>

    <!-- Table / Card Wrapper -->
    <div class="premium-stat-card d-block p-0 overflow-hidden shadow-sm border rounded-4 bg-white">
        <div class="table-responsive p-3 d-none d-md-block">
            <table id="paymentsTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr class="text-uppercase small fw-bold text-muted">
                        <th class="ps-4">Reference</th>
                        <th>User</th>
                        <th>Property</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Mobile Grid -->
        <div id="payments-grid" class="d-md-none p-3 row g-3">
             <div class="col-12 text-center py-5 text-muted opacity-50">
                <i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i>
                <p class="small">Loading Ledger...</p>
            </div>
        </div>
    </div>

    <!-- DataTable Controls Container (Bottom) -->
    <div id="payments-bottom-controls" class="mt-4"></div>
</div>
</div>

<style>
#paymentsTable thead th {
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
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
.status-paid { background: #ecfdf5; color: #059669; border: 1px solid #10b98133; }
.status-pending { background: #fffbeb; color: #d97706; border: 1px solid #f59e0b33; }
.status-failed { background: #fef2f2; color: #dc2626; border: 1px solid #ef444433; }

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

#payments-top-controls, #payments-bottom-controls {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    min-height: 60px;
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
    #payments-top-controls {
        flex-direction: column !important;
        gap: 15px !important;
    }
    .dataTables_filter { order: 1 !important; width: 100% !important; text-align: left !important; }
    .dataTables_length { order: 2 !important; width: 100% !important; justify-content: center !important; opacity: 0.8 !important; }
}
</style>

<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#paymentsTable')) {
        $('#paymentsTable').DataTable().destroy();
    }
    
    const table = $('#paymentsTable').DataTable({
        ajax: {
            url: '/tenant/?url=admin/get_payments_json',
            data: function (d) {
                d.status = $('#stateFilter').val();
                d.house_id = $('#houseFilter').val();
            }
        },
        order: [[5, 'desc']],
        pageLength: 10,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search ledger..."
        },
        columns: [
            { 
                data: null,
                className: 'ps-4 py-3',
                render: function(data) {
                    const ref = data.transaction_ref || `T-${String(data.id).padStart(6, '0')}`;
                    const method = (data.payment_method || 'N/A').toUpperCase();
                    return `
                        <div class="fw-bold text-dark fs-6">${ref}</div>
                        <div class="small text-muted font-monospace" style="font-size: 0.7rem;">${method}</div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="fw-bold text-dark">${data.first_name} ${data.last_name}</div>
                        <div class="small text-muted text-truncate" style="max-width: 150px;">${data.email}</div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    const house = data.boarding_house_name || 'General';
                    const type = (data.payment_type || 'Rent').charAt(0).toUpperCase() + (data.payment_type || 'Rent').slice(1);
                    return `
                        <div class="fw-bold text-primary">${house}</div>
                        <div class="small text-muted font-semibold">${type}</div>
                    `;
                }
            },
            {
                data: 'amount',
                className: 'text-center',
                render: function(amt) {
                    return `<div class="fw-bold text-dark">â‚±${parseFloat(amt).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>`;
                }
            },
            {
                data: 'status',
                className: 'text-center',
                render: function(status) {
                    status = status.toLowerCase();
                    let badgeClass = 'bg-secondary';
                    let icon = 'fa-circle-question';
                    if (status === 'paid') { badgeClass = 'bg-success'; icon = 'fa-circle-check'; }
                    else if (status === 'pending') { badgeClass = 'bg-warning text-dark'; icon = 'fa-clock'; }
                    else if (status === 'failed') { badgeClass = 'bg-danger'; icon = 'fa-circle-xmark'; }
                    
                    return `
                        <span class="badge rounded-pill ${badgeClass} px-3 py-2 fw-bold shadow-sm" style="font-size: 0.7rem; min-width: 90px;">
                            <i class="fa-solid ${icon} me-1"></i>
                            ${status.toUpperCase()}
                        </span>
                    `;
                }
            },
            {
                data: 'created_at',
                render: function(date) {
                    const d = new Date(date);
                    const formattedDate = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    const formattedTime = d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                    return `
                        <div class="text-dark small fw-bold">${formattedDate}</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">${formattedTime}</div>
                    `;
                }
            }
        ],
        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const $grid = $('#payments-grid');
            
            // Generate Mobile Cards
            $grid.empty();
            if (data.length === 0) {
                $grid.html('<div class="col-12 text-center py-5 text-muted opacity-50"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>No transactions found.</div>');
            } else {
                data.each(function(row) {
                    const id = row.transaction_ref || `T-${String(row.id).padStart(6, '0')}`;
                    const name = `${row.first_name} ${row.last_name}`;
                    const email = row.email;
                    const house = row.boarding_house_name || 'General';
                    const amount = parseFloat(row.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    const status = row.status.toLowerCase();
                    const method = (row.payment_method || 'N/A').toUpperCase();
                    const dateRaw = new Date(row.created_at);
                    const date = dateRaw.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    const time = dateRaw.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

                    const statusClass = status === 'paid' ? 'status-paid' : (status === 'pending' ? 'status-pending' : 'status-failed');
                    const statusIcon = status === 'paid' ? 'fa-circle-check' : (status === 'pending' ? 'fa-clock' : 'fa-circle-xmark');
                    const accentBorder = status === 'paid' ? 'border-success' : (status === 'pending' ? 'border-warning' : 'border-danger');

                    const card = `
                        <div class="col-12">
                            <div class="premium-stat-card p-4 shadow-sm border-0 border-top border-4 ${accentBorder} position-relative bg-white rounded-4 overflow-hidden text-start">
                                <!-- Top Row: User & Status -->
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-primary shadow-sm text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 52px; height: 52px; font-size: 1.4rem; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;">
                                        ${name.charAt(0).toUpperCase()}
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-800 text-dark fs-6 mb-0 text-truncate">${name}</div>
                                        <div class="text-xs text-muted font-monospace opacity-75 text-truncate" style="font-size: 0.65rem;">${email}</div>
                                    </div>
                                </div>

                                <!-- Status Badge (Full Row) -->
                                <div class="mb-4">
                                    <div class="status-badge-premium ${statusClass} py-2 px-3">
                                        <i class="fa-solid ${statusIcon} me-2"></i> ${status.toUpperCase()}
                                    </div>
                                </div>

                                <!-- Vertical Data Rows -->
                                <div class="mb-0">
                                    <div class="p-3 mb-2 rounded-3 bg-light d-flex align-items-center justify-content-between gap-3 overflow-hidden">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50 flex-shrink-0" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-receipt me-1"></i> REF / METHOD
                                        </div>
                                        <div class="small fw-800 text-dark text-end ms-auto">${id} / ${method}</div>
                                    </div>
                                    <div class="p-3 mb-2 rounded-3 bg-light d-flex align-items-center justify-content-between gap-3">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50 flex-shrink-0" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-house-chimney me-1"></i> PROPERTY
                                        </div>
                                        <div class="small fw-800 text-primary text-end ms-auto">${house}</div>
                                    </div>
                                    <div class="p-3 mb-2 rounded-3 bg-light d-flex align-items-center justify-content-between gap-3">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50 flex-shrink-0" style="font-size: 0.65rem;">
                                            <i class="fa-solid fa-calendar-alt me-1"></i> DATE & TIME
                                        </div>
                                        <div class="small fw-700 text-dark text-end ms-auto">${date} Â· ${time}</div>
                                    </div>
                                    <div class="p-4 rounded-4 bg-primary shadow-sm border-0 d-flex align-items-center justify-content-between">
                                        <div class="text-white text-opacity-75 fw-800 text-uppercase letter-spacing-1" style="font-size: 0.75rem;">
                                            TOTAL AMOUNT
                                        </div>
                                        <div class="fs-4 fw-900 text-white">â‚±${amount}</div>
                                    </div>
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

            if ($length.length) $('#payments-top-controls').append($length);
            if ($filter.length) $('#payments-top-controls').append($filter);
            if ($info.length) $('#payments-bottom-controls').append($info);
            if ($paginate.length) $('#payments-bottom-controls').append($paginate);

            // Pagination Styling
            $('.pagination').addClass('pagination-rounded gap-1');
            $('.page-link').addClass('rounded-3 border-0 shadow-none');
            if ($(window).width() < 576) {
                $('.pagination').addClass('justify-content-center mt-3');
            } else {
                $('.pagination').removeClass('justify-content-center mt-3');
            }
        }
    });

    // Handle Filter Changes
    $('#stateFilter, #houseFilter').on('change', function() {
        table.ajax.reload();
        updateExportLink();
    });

    function updateExportLink() {
        let url = '/tenant/?url=admin/print_payments';
        const status = $('#stateFilter').val();
        const houseId = $('#houseFilter').val();
        
        if (status) url += '&status=' + encodeURIComponent(status);
        if (houseId) url += '&house_id=' + encodeURIComponent(houseId);
        
        $('#exportPdfBtn').attr('href', url);
    }
});
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
