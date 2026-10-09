<?php require __DIR__ . '/../layouts/admin_header.php'; ?>

<style>
/* Elite Transactions Design */
.transactions-container {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.premium-card {
    border: none;
    border-radius: 32px;
    background: #ffffff;
    box-shadow: 0 4px 30px rgba(0,0,0,0.02);
    border: 1px solid rgba(241, 245, 249, 0.8);
    overflow: hidden;
}

.table thead th {
    background: #f8fafc;
    border-bottom: 2px solid #f1f5f9;
    color: #475569;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 1px;
    padding: 20px 15px;
}

.table tbody td {
    padding: 18px 15px;
    vertical-align: middle;
    border-bottom: 1px solid #f8fafc;
}

.status-pill {
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 0.65rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.pill-paid { background: #ecfdf5; color: #047857; border: 1px solid #d1fae5; }
.pill-pending { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }

.ref-box {
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 0.75rem;
    color: #2563eb;
    background: #eff6ff;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 700;
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
.dataTables_length select:hover { border-color: #2563eb !important; box-shadow: 0 0 10px rgba(37, 99, 235, 0.1); }

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

<div class="container-fluid px-4 py-5 transactions-container">
    <div class="d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between mb-5 gap-4">
        <div class="w-100">
            <h1 class="display-4 fw-900 text-dark mb-2 letter-spacing--2">Portfolio Transactions</h1>
            <?php if (isset($houseId) && $houseId): ?>
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 mb-2">
                    <span class="badge bg-soft-indigo text-primary px-3 py-2 rounded-pill fw-800 text-wrap text-start">
                        <i class="fa-solid fa-filter me-2"></i>FILTER ACTIVE: <?= htmlspecialchars($houseName) ?>
                    </span>
                    <a href="/tenant/?url=owner/payments/transactions" class="text-muted small fw-bold text-decoration-none hover-primary mt-2 mt-sm-0">
                        <i class="fa-solid fa-circle-xmark me-1"></i>Clear Filter
                    </a>
                </div>
            <?php else: ?>
                <p class="text-muted fs-5 mb-0 fw-500">Global ledger of all financial events across your properties.</p>
            <?php endif; ?>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-3 w-100 w-xl-auto align-items-stretch">
            <select id="statusFilter" class="form-select rounded-pill px-4 fw-800 shadow-sm border-2 text-uppercase ls-1 w-100 flex-grow-1" style="height: 45px; cursor: pointer; border-color: #cbd5e1; color: #1e293b;">
                <option value="">ALL STATUSES</option>
                <option value="paid">PAID ONLY</option>
                <option value="pending">PENDING ONLY</option>
                <option value="failed">FAILED ONLY</option>
            </select>
            <form action="/tenant/?url=owner/payments/print_transactions" method="POST" target="_blank" class="d-inline flex-grow-1 w-100 m-0">
                <?php if (isset($houseId)): ?>
                    <input type="hidden" name="house_id" value="<?= (int)$houseId ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-800 d-flex justify-content-center align-items-center gap-2 shadow-sm w-100" style="height: 45px; border-width: 2px;" data-elite-tooltip="Generate Statement Transcript">
                    <i class="fa-solid fa-print"></i>
                    <span>PRINT</span>
                </button>
            </form>
            <a href="/tenant/?url=owner/payments/earnings" class="btn btn-primary rounded-pill px-4 py-2 fw-800 d-flex justify-content-center align-items-center gap-2 shadow-sm border-0 flex-grow-1 w-100" style="height: 45px; background: #2563eb; transition: all 0.3s;" data-elite-tooltip="View Financial Insights">
                <i class="fa-solid fa-chart-pie"></i>
                <span>ANALYTICS</span>
            </a>
        </div>
    </div>

    <div class="premium-card p-4">
        <div id="txn-top-controls" class="mb-4"></div>
        <div class="table-responsive d-none d-lg-block">
            <table class="table mb-0 w-100" id="ownerTransactionsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Tenant</th>
                        <th>Property</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        
        <div id="transactionsGrid" class="row g-4 d-lg-none mt-2"></div>
        
        <div id="txn-bottom-controls" class="mt-4"></div>
    </div>
</div>

<!-- Add hidden inputs for filtering context -->
<input type="hidden" id="houseIdFilter" value="<?= $houseId ?? '' ?>">

<script>
$(document).ready(function() {
    const table = $('#ownerTransactionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/tenant/?url=owner/transactions_data',
            type: 'POST',
            data: function(d) {
                d.house_id = $('#houseIdFilter').val();
                d.status = $('#statusFilter').val();
            }
        },
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search lifecycle events..."
        },
        dom: '<"top">rt<"bottom"ip><"clear">',
        columns: [
            { data: 'date', orderable: true },
            { 
                data: 'tenant',
                render: function(data) {
                    return `
                        <div class="fw-900 text-dark text-wrap text-break">${data.name}</div>
                        <div class="small text-muted fw-bold opacity-75 text-wrap text-break">${data.email}</div>
                    `;
                }
            },
            { 
                data: 'property',
                render: function(data) {
                    return `<div class="text-wrap text-break">${data}</div>`;
                }
            },
            { 
                data: 'method',
                render: function(data) {
                    return `<span class="badge bg-light text-dark fw-800 py-2 px-3 border text-wrap">${data}</span>`;
                }
            },
            { 
                data: 'reference',
                render: function(data) {
                    return `<span class="ref-box text-break">${data}</span>`;
                }
            },
            { data: 'amount', className: 'text-end fw-900 text-dark' },
            { 
                data: 'status',
                className: 'text-center',
                render: function(data, type, row) {
                    return `<span class="status-pill ${row.status_class}">${data}</span>`;
                }
            }
        ],
        drawCallback: function() {
            const api = this.api();
            const rows = api.rows({page:'current'}).data();
            const container = $('#transactionsGrid');
            container.empty();

            if (rows.length === 0) {
                container.html(`
                    <div class="col-12 text-center py-5">
                        <div class="text-muted"><i class="fa-solid fa-file-invoice mb-3 fs-1 opacity-50"></i><br><h5 class="fw-800">No transactions recorded.</h5></div>
                    </div>
                `);
                return;
            }

            rows.each(function(t) {
                const card = `
                    <div class="col-12">
                        <div class="premium-card p-4 border shadow-sm h-100 d-flex flex-column animate-fade-up" style="background: #ffffff; border-radius: 20px;">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-3 border-bottom pb-3 gap-3">
                                <div class="w-100">
                                    <div class="fw-900 text-dark fs-3 text-primary">${t.amount}</div>
                                    <div class="text-muted small fw-800 text-uppercase mt-1 text-wrap"><i class="fa-solid fa-wallet me-1"></i> ${t.method}</div>
                                </div>
                                <div class="text-start text-md-end w-100 w-md-auto">
                                    <span class="status-pill ${t.status_class}">${t.status}</span>
                                </div>
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">
                                    <div class="fw-800 text-muted small text-uppercase mb-1">REFERENCE #</div>
                                    <div class="fw-800 text-dark ref-box d-inline-block text-break">${t.reference}</div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="fw-800 text-muted small text-uppercase mb-1">POSTED DATE</div>
                                    <div class="fw-700 text-dark">${t.date}</div>
                                </div>
                            </div>

                            <div class="mb-2 bg-light p-3 rounded-4 border">
                                <div class="fw-800 text-muted small text-uppercase mb-1">TENANT</div>
                                <div class="fw-900 text-dark mb-0 text-wrap text-break">${t.tenant.name}</div>
                                <div class="text-muted small fw-700 text-wrap text-break">${t.tenant.email}</div>
                                <div class="fw-800 text-muted small text-uppercase mt-3 mb-1">PROPERTY</div>
                                <div class="fw-700 text-dark mb-0 text-wrap text-break">${t.property}</div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });

            // Re-apply tooltips if needed
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-elite-tooltip]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        }
    });

    // Custom Datatables Top Controls
    const filterInput = $('#ownerTransactionsTable_filter').detach();
    const lengthInput = $('#ownerTransactionsTable_length').detach();

    filterInput.find('input').attr('placeholder', 'Search lifecycle events...').addClass('form-control rounded-pill px-4 py-2 border shadow-sm').css({'min-width': '250px', 'background-color': '#f1f5f9'});
    lengthInput.find('select').addClass('form-select rounded-pill px-3 py-2 border shadow-sm').css({'min-width': '80px', 'background-color': '#f1f5f9'});

    filterInput.addClass('d-flex justify-content-md-end w-100 w-md-auto');
    lengthInput.addClass('d-flex align-items-center gap-2');

    $('#txn-top-controls').html(`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div id="ctrl-len" class="w-100 w-md-auto"></div>
            <div id="ctrl-search" class="w-100 w-md-auto"></div>
        </div>
    `);
    $('#ctrl-len').append(lengthInput);
    $('#ctrl-search').append(filterInput);

    // Custom Datatables Bottom Controls
    const infoText = $('#ownerTransactionsTable_info').detach();
    const paginateCtrl = $('#ownerTransactionsTable_paginate').detach();

    infoText.addClass('small fw-600 text-muted');
    $('#txn-bottom-controls').html(`
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 p-3 bg-white border rounded-4 shadow-sm w-100 mt-2">
            <div id="ctrl-info" class="text-center text-md-start"></div>
            <div id="ctrl-page" class="d-flex justify-content-center"></div>
        </div>
    `);
    $('#ctrl-info').append(infoText);
    $('#ctrl-page').append(paginateCtrl);

    // Custom Filter Logic
    $('#statusFilter').on('change', function() {
        table.ajax.reload();
    });
});
</script>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
