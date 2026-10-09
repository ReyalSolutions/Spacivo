<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<style>
:root {
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.5);
    --accent-indigo: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    --accent-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.ledger-container {
    animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.premium-card {
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    border: 1px solid var(--glass-border);
    border-radius: 32px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.04);
    overflow: hidden;
}

.tenant-hero {
    background: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.95)), url('https://www.transparenttextures.com/patterns/cubes.png');
    padding: 40px;
    border-bottom: 1px solid #f1f5f9;
}

.hero-avatar {
    width: 80px;
    height: 80px;
    border-radius: 24px;
    background: var(--accent-indigo);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: 850;
    box-shadow: 0 15px 35px rgba(79, 70, 229, 0.25);
}

.stat-pill {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    padding: 24px;
    border-radius: 28px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    min-width: 220px;
    flex: 1;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}
.stat-pill:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
    border-color: #e2e8f0;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.bg-soft-indigo { background: #eef2ff; color: #6366f1; }
.bg-soft-emerald { background: #ecfdf5; color: #10b981; }
.bg-soft-rose { background: #fff1f2; color: #f43f5e; }
.bg-soft-amber { background: #fffbeb; color: #f59e0b; }
.bg-soft-sky { background: #f0f9ff; color: #0ea5e9; }

.table-glass {
    border-collapse: separate;
    border-spacing: 0 12px;
}
.table-glass thead th {
    background: #f8fafc;
    border: none;
    padding: 18px 24px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 800;
    color: #64748b;
    border-radius: 12px;
}
.table-glass tbody tr {
    transition: all 0.3s;
}
.table-glass tbody tr td {
    background: #ffffff;
    border: none;
    padding: 20px 24px;
    font-weight: 600;
    vertical-align: middle;
}
.table-glass tbody tr td:first-child { border-radius: 20px 0 0 20px; border-left: 1px solid #f1f5f9; }
.table-glass tbody tr td:last-child { border-radius: 0 20px 20px 0; border-right: 1px solid #f1f5f9; }
.table-glass tbody tr:hover td {
    background: #f1f5f9;
    transform: scale(1.005);
}

.status-indicator {
    padding: 8px 16px;
    border-radius: 40px;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
}
.indicator-paid { background: #dcfce7; color: #166534; }
.indicator-pending { background: #fef9c3; color: #854d0e; }
.indicator-failed { background: #fee2e2; color: #991b1b; }

.btn-back {
    padding: 12px 28px;
    border-radius: 18px;
    background: white;
    border: 1px solid #e2e8f0;
    font-weight: 700;
    color: #475569;
    transition: all 0.3s;
}
.btn-back:hover {
    background: #f8fafc;
    transform: translateX(-5px);
}

/* DataTables Modernization */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 25px;
    font-weight: 700;
    color: #475569;
}
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #e2e8f0 !important;
    border-radius: 14px !important;
    padding: 10px 20px !important;
    background: #f8fafc !important;
    margin-left: 15px !important;
    width: 300px !important;
    transition: all 0.3s !important;
}
.dataTables_wrapper .dataTables_filter input:focus {
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
    border-color: #6366f1 !important;
}
.dataTables_wrapper .dataTables_info {
    font-weight: 700;
    color: #64748b;
    margin-top: 20px;
}
.dataTables_wrapper .dataTables_paginate {
    margin-top: 20px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 12px !important;
    border: 1px solid #e2e8f0 !important;
    background: white !important;
    margin: 0 4px !important;
    font-weight: 800 !important;
    color: #475569 !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--accent-indigo) !important;
    color: white !important;
    border: none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
    background: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
}

.dataTables_length label, .dataTables_filter label { color: #1e293b !important; font-weight: 700 !important; }

.btn-approve-ledger {
    background: var(--accent-emerald) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border: none !important;
}
.btn-approve-ledger:hover {
    transform: scale(1.05) translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
}

.animate-pulse-slow { animation: pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse-slow { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }

.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }
</style>

<div class="container-fluid py-5 ledger-container">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-5 gap-4">
                <div class="w-100">
                    <h1 class="fw-900 text-dark mb-1 letter-spacing--1">Account Statement</h1>
                    <p class="text-muted fw-600 mb-0">Detailed transaction history for <?= htmlspecialchars($booking['tenant_name']) ?></p>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto align-items-stretch">
                    <button type="button" class="btn btn-primary px-4 py-3 py-sm-2 fw-800 rounded-pill shadow-sm w-100 flex-grow-1" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                        <i class="fa-solid fa-plus-circle me-2"></i>RECORD PAYMENT
                    </button>
                    <form action="/tenant/?url=owner/print_statement" method="POST" target="_blank" class="d-inline flex-grow-1 w-100">
                        <input type="hidden" name="tenancy_id" value="<?= (int)$booking['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                        <button type="submit" class="btn btn-outline-primary px-4 py-3 py-sm-2 fw-800 rounded-pill w-100">
                            <i class="fa-solid fa-print me-2"></i>PRINT SOA
                        </button>
                    </form>
                    <a href="/tenant/?url=owner/bookings/approved" class="btn btn-back d-flex justify-content-center align-items-center gap-2 flex-grow-1 w-100 py-3 py-sm-2 m-0">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>BACK</span>
                    </a>
                </div>
            </div>

            <!-- Tenant Identification Hero -->
            <div class="premium-card mb-5">
                <div class="tenant-hero d-flex flex-column align-items-start justify-content-between gap-4">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="hero-avatar flex-shrink-0">
                            <?= substr($booking['tenant_name'], 0, 1) ?>
                        </div>
                        <div class="text-wrap text-break">
                            <h2 class="fw-900 text-dark mb-1 fs-4 fs-md-2 text-wrap text-break"><?= htmlspecialchars($booking['tenant_name']) ?></h2>
                            <div class="d-flex flex-column flex-sm-row gap-1 gap-sm-3 text-muted small fw-700">
                                <span class="text-wrap text-break"><i class="fa-solid fa-building me-1"></i> <?= htmlspecialchars($booking['boarding_house_name']) ?></span>
                                <span class="d-none d-sm-inline opacity-25">|</span>
                                <span class="text-wrap"><i class="fa-solid fa-door-closed me-1"></i> Room <?= htmlspecialchars($booking['room_name']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-column flex-xl-row gap-3 w-100 mt-2 pb-2">
                        <!-- Contract Total -->
                        <div class="stat-pill border-0 bg-light shadow-sm w-100">
                            <div class="stat-icon bg-soft-indigo">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted small fw-800 text-uppercase mb-1">Contract Total</span>
                                <span class="h5 fw-900 text-dark mb-0">₱<?= number_format((float)$booking['total_amount'], 2) ?></span>
                            </div>
                        </div>

                        <!-- Total Paid (Emerald) -->
                        <div class="stat-pill border-0 bg-light shadow-sm w-100">
                            <div class="stat-icon bg-soft-emerald">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted small fw-800 text-uppercase mb-1">Total Paid</span>
                                <span class="h5 fw-900 text-success mb-0">₱<?= number_format((float)$totalPaid, 2) ?></span>
                            </div>
                        </div>

                        <!-- Outstanding (Rose/Amber) -->
                        <?php 
                        $balanceRaw = (float)$booking['total_amount'] - (float)$totalPaid;
                        $isExceed = $balanceRaw < 0;
                        ?>
                        <div class="stat-pill border-0 shadow-sm w-100 <?= $balanceRaw > 0 ? 'bg-soft-rose border-start border-4 border-danger' : 'bg-light opacity-50' ?>">
                            <div class="stat-icon bg-soft-rose">
                                <i class="fa-solid fa-circle-exclamation"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted small fw-800 text-uppercase mb-1">Outstanding Balance</span>
                                <span class="h5 fw-900 text-danger mb-0">₱<?= number_format(max(0, $balanceRaw), 2) ?></span>
                            </div>
                        </div>

                        <!-- Exceed Balance (Sky/Indigo) -->
                        <div class="stat-pill border-0 shadow-sm w-100 <?= $isExceed ? 'bg-soft-sky border-start border-4 border-primary animate-pulse' : 'bg-light opacity-50' ?>">
                            <div class="stat-icon bg-soft-sky text-primary">
                                <i class="fa-solid fa-hand-holding-heart"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted small fw-800 text-uppercase mb-1">Exceed Balance / Credit</span>
                                <span class="h5 fw-900 text-primary mb-0">₱<?= number_format($isExceed ? abs($balanceRaw) : 0, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Table -->
                <div class="p-4 bg-white overflow-hidden rounded-4 shadow-sm border">
                    <div id="payments-top-controls" class="mb-4"></div>
                    
                    <div class="table-responsive d-none d-lg-block" style="overflow-x: hidden !important;">
                        <table id="residencyPaymentsTable" class="table table-glass w-100 m-0">
                            <thead>
                                <tr>
                                    <th>Ref # / Date</th>
                                    <th>Description</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables dynamic content -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="paymentsGrid" class="row g-4 d-lg-none mt-2"></div>
                    
                    <div id="payments-bottom-controls" class="mt-4"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="premium-card p-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-900 text-dark mb-0">Record Manual Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/tenant/?url=owner/record_manual_payment" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="tenancy_id" value="<?= (int)$booking['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    
                    <div class="modal-body py-4">
                            <div class="d-flex justify-content-between align-items-end mb-2">
                                <label class="form-label fw-800 text-muted small text-uppercase mb-0">Payment Amount (₱)</label>
                                <button type="button" class="btn btn-link p-0 text-decoration-none extra-small fw-800 text-primary" onclick="autoFillRent(<?= (float)$booking['room_price'] ?>)">
                                    <i class="fa-solid fa-magic-wand-sparkles me-1"></i>SET TO MONTHLY RENT (₱<?= number_format((float)$booking['room_price'], 2) ?>)
                                </button>
                            </div>
                            <input type="number" step="0.01" name="amount" id="manualPaymentAmount" class="form-control form-control-lg border-0 bg-light rounded-4 px-4 py-3 fw-900" placeholder="0.00" required>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-muted small text-uppercase">Payment Date</label>
                                <input type="datetime-local" name="date" class="form-control border-0 bg-light rounded-4 px-3 py-3 fw-700" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-muted small text-uppercase">Method</label>
                                <select name="method" class="form-select border-0 bg-light rounded-4 px-3 py-3 fw-700">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="GCash (Manual)">GCash (Manual)</option>
                                    <option value="Check">Check</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-800 text-muted small text-uppercase">Payment Type</label>
                                <select name="payment_type" class="form-select border-0 bg-light rounded-4 px-3 py-3 fw-700">
                                    <option value="rent">Monthly Rent</option>
                                    <option value="utilities">Utilities (Water/Electric)</option>
                                    <option value="penalty">Penalty Fees</option>
                                    <option value="advance">Advance Payment</option>
                                    <option value="others">Others/Misc</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-800 text-muted small text-uppercase">Months</label>
                                <input type="number" name="months_covered" class="form-control border-0 bg-light rounded-4 px-3 py-3 fw-700" value="1" min="1" required>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-800 text-muted small text-uppercase">Internal Note / Description</label>
                            <textarea name="description" class="form-control border-0 bg-light rounded-4 px-4 py-3 fw-600" rows="2" placeholder="e.g. Paid for March 2026 rent"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 pt-0 d-flex gap-2">
                        <button type="button" class="btn btn-light fw-800 rounded-pill px-4 flex-grow-1" data-bs-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary fw-800 rounded-pill px-4 flex-grow-1">SAVE PAYMENT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Action Confirmation Modal -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="premium-card p-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-900 text-dark mb-0" id="confirmModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="fw-600 text-muted mb-0" id="confirmModalMessage">Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light fw-800 rounded-pill px-4 flex-grow-1" data-bs-dismiss="modal">CANCEL</button>
                    <button type="button" id="confirmActionButton" class="btn fw-800 rounded-pill px-4 flex-grow-1">CONFIRM</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#residencyPaymentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/tenant/?url=owner/get_residency_payments_json&tenancy_id=<?= (int)$booking['id'] ?>",
        columns: [
            { 
                data: 'ref_date',
                render: function(data) {
                    return `
                        <div class="fw-800 text-dark">${data.ref}</div>
                        <div class="text-muted small">${data.date}</div>
                    `;
                }
            },
            { 
                data: 'description',
                render: function(data) {
                    return `
                        <div class="fw-700">${data.type}</div>
                        <div class="text-muted small">${data.desc}</div>
                    `;
                }
            },
            { 
                data: 'method',
                render: function(data) {
                    return `<span class="fw-800 text-uppercase small text-muted">${data}</span>`;
                }
            },
            { 
                data: 'amount',
                render: function(data) {
                    return `<span class="h5 fw-900 text-dark mb-0">${data}</span>`;
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    let icon = data === 'paid' ? 'fa-check-circle' : (data === 'pending' ? 'fa-clock' : 'fa-circle-xmark');
                    return `
                        <span class="status-indicator indicator-${data}">
                            <i class="fa-solid ${icon} me-1"></i>
                            ${data}
                        </span>
                    `;
                }
            },
            {
                data: 'actions',
                orderable: false,
                render: function(data) {
                    let html = '<div class="text-end d-flex justify-content-end gap-1">';
                    if (data.can_void) {
                        html += `
                            <button onclick="voidPayment(${data.id})" class="btn btn-sm btn-outline-danger border-0 rounded-pill px-3 fw-800">
                                <i class="fa-solid fa-trash-can me-1"></i> VOID
                            </button>
                        `;
                    }
                    if (data.can_approve) {
                        html += `
                            <button onclick="approvePayment(${data.id})" class="btn btn-sm btn-approve-ledger animate-pulse-slow rounded-pill px-3 fw-900">
                                <i class="fa-solid fa-check-double me-1"></i> MARK AS PAID
                            </button>
                        `;
                    }
                    if (!data.can_void && !data.can_approve) {
                        html += '<span class="text-muted">—</span>';
                    }
                    html += '</div>';
                    return html;
                }
            }
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        dom: '<"top">rt<"bottom"ip><"clear">',
        language: {
            search: "SEARCH RECORDS",
            searchPlaceholder: "Ref#, Type, or Description...",
            processing: '<div class="ui-skeleton ui-skeleton-line text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        drawCallback: function() {
            $('.dataTables_paginate .paginate_button').addClass('btn btn-sm');
            
            const api = this.api();
            const rows = api.rows({page:'current'}).data();
            const container = $('#paymentsGrid');
            container.empty();

            if (rows.length === 0) {
                container.html(`
                    <div class="col-12 text-center py-5">
                        <div class="text-muted"><i class="fa-solid fa-receipt mb-3 fs-1 opacity-50"></i><br><h5 class="fw-800">No transactions recorded.</h5></div>
                    </div>
                `);
                return;
            }

            rows.each(function(t) {
                let statusBadge = '';
                if (t.status === 'paid') statusBadge = '<span class="status-indicator indicator-paid"><i class="fa-solid fa-check-circle me-1"></i>PAID</span>';
                else if (t.status === 'pending') statusBadge = '<span class="status-indicator indicator-pending"><i class="fa-solid fa-clock me-1"></i>PENDING</span>';
                else statusBadge = '<span class="status-indicator indicator-failed"><i class="fa-solid fa-circle-xmark me-1"></i>FAILED</span>';

                let actionHtml = '';
                if (t.actions.can_void) {
                    actionHtml += `
                        <button onclick="voidPayment(${t.actions.id})" class="btn btn-sm w-100 btn-outline-danger border-0 rounded-pill px-3 fw-800 py-2">
                            <i class="fa-solid fa-trash-can me-1"></i> VOID PAYMENT
                        </button>
                    `;
                }
                if (t.actions.can_approve) {
                    actionHtml += `
                        <button onclick="approvePayment(${t.actions.id})" class="btn btn-sm w-100 btn-approve-ledger animate-pulse-slow rounded-pill px-3 fw-900 py-2 ${actionHtml ? 'mt-2' : ''}">
                            <i class="fa-solid fa-check-double me-1"></i> MARK AS PAID
                        </button>
                    `;
                }
                
                if (!actionHtml) actionHtml = '<div class="text-muted small text-center w-100 fw-600">— No Actions Available —</div>';

                const rawAmount = typeof t.raw_amount !== 'undefined' ? parseFloat(t.raw_amount).toLocaleString('en-PH', {minimumFractionDigits: 2}) : t.amount.replace('₱', '').trim();

                const card = `
                    <div class="col-12">
                        <div class="premium-card p-4 border shadow-sm h-100 d-flex flex-column animate-fade-up" style="background: #ffffff; border-radius: 20px;">
                            <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                                <div>
                                    <div class="fw-900 text-dark fs-3 text-primary">₱${rawAmount}</div>
                                    <div class="text-muted small fw-800 text-uppercase mt-1"><i class="fa-solid fa-wallet me-1"></i> ${t.method}</div>
                                </div>
                                <div class="text-end">
                                    ${statusBadge}
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">
                                    <div class="fw-800 text-muted small text-uppercase mb-1">REFERENCE #</div>
                                    <div class="fw-800 text-dark text-break">${t.ref_date.ref}</div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="fw-800 text-muted small text-uppercase mb-1">POSTED DATE</div>
                                    <div class="fw-700 text-dark">${t.ref_date.date}</div>
                                </div>
                            </div>

                            <div class="mb-4 bg-light p-3 rounded-4 border">
                                <div class="fw-800 text-muted small text-uppercase mb-1">DESCRIPTION (${t.description.type})</div>
                                <div class="fw-700 text-dark mb-0">${t.description.desc}</div>
                            </div>

                            <div class="mt-auto d-flex flex-column gap-2 border-top pt-3 w-100">
                                ${actionHtml}
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }
    });

    // Custom Datatables Top Controls
    const filterInput = $('#residencyPaymentsTable_filter').detach();
    const lengthInput = $('#residencyPaymentsTable_length').detach();

    filterInput.find('input').attr('placeholder', 'Search transactions...').addClass('form-control rounded-pill px-4 py-2 border shadow-sm').css({'min-width': '250px', 'background-color': '#f1f5f9'});
    lengthInput.find('select').addClass('form-select rounded-pill px-3 py-2 border shadow-sm').css({'min-width': '80px', 'background-color': '#f1f5f9'});

    filterInput.addClass('d-flex justify-content-md-end w-100 w-md-auto');
    lengthInput.addClass('d-flex align-items-center gap-2');

    $('#payments-top-controls').html(`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div id="ctrl-len" class="w-100 w-md-auto"></div>
            <div id="ctrl-search" class="w-100 w-md-auto"></div>
        </div>
    `);
    $('#ctrl-len').append(lengthInput);
    $('#ctrl-search').append(filterInput);

    // Custom Datatables Bottom Controls
    const infoText = $('#residencyPaymentsTable_info').detach();
    const paginateCtrl = $('#residencyPaymentsTable_paginate').detach();

    infoText.addClass('small fw-600 text-muted');
    $('#payments-bottom-controls').html(`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 p-3 bg-white border rounded-4 shadow-sm w-100">
            <div id="ctrl-info" class="text-center text-md-start"></div>
            <div id="ctrl-page" class="d-flex justify-content-center"></div>
        </div>
    `);
    $('#ctrl-info').append(infoText);
    $('#ctrl-page').append(paginateCtrl);
});

let pendingActionId = null;
let pendingActionType = null;

function approvePayment(paymentId) {
    pendingActionId = paymentId;
    pendingActionType = 'approve';
    
    document.getElementById('confirmModalTitle').innerText = 'Confirm Payment Approval';
    document.getElementById('confirmModalMessage').innerText = 'Are you sure you want to MARK THIS AS PAID? Only do this if you have manually verified the payment from the tenant.';
    
    const confirmBtn = document.getElementById('confirmActionButton');
    confirmBtn.className = 'btn btn-approve-ledger fw-800 rounded-pill px-4 flex-grow-1';
    confirmBtn.innerText = 'YES, MARK AS PAID';
    
    const modal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
    modal.show();
}

function voidPayment(paymentId) {
    pendingActionId = paymentId;
    pendingActionType = 'void';
    
    document.getElementById('confirmModalTitle').innerText = 'Confirm Payment Void';
    document.getElementById('confirmModalMessage').innerText = 'Are you sure you want to VOID this manual payment? This action will permanently remove it from the ledger.';
    
    const confirmBtn = document.getElementById('confirmActionButton');
    confirmBtn.className = 'btn btn-danger fw-800 rounded-pill px-4 flex-grow-1';
    confirmBtn.innerText = 'YES, VOID PAYMENT';
    
    const modal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
    modal.show();
}

document.getElementById('confirmActionButton').addEventListener('click', function() {
    if (!pendingActionId || !pendingActionType) return;
    
    const url = pendingActionType === 'approve' ? '/tenant/?url=owner/approve_pending_payment' : '/tenant/?url=owner/void_payment';
    const payload = {
        payment_id: pendingActionId,
        tenancy_id: <?= (int)$booking['id'] ?>,
        csrf_token: '<?= Csrf::token() ?>'
    };

    $.post(url, payload, function(response) {
        let res = JSON.parse(response);
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Action Successful',
                text: res.message,
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                background: '#ffffff',
                color: '#0f172a',
                iconColor: '#10b981'
            }).then(() => {
                location.reload(); 
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Action Failed',
                text: res.message,
                confirmButtonColor: '#6366f1'
            });
        }
    });
});
function autoFillRent(amount) {
    document.getElementById('manualPaymentAmount').value = amount.toFixed(2);
}
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
