<?php 
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

#plans-top-controls, #plans-bottom-controls {
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
    .header-actions .btn { flex: 1 !important; }
    #plans-top-controls {
        flex-direction: column !important;
        gap: 15px !important;
    }
    .dataTables_filter { order: 1 !important; width: 100% !important; text-align: left !important; }
    .dataTables_filter input { width: 100% !important; margin-left: 0 !important; }
    .dataTables_length { order: 2 !important; width: 100% !important; justify-content: center !important; opacity: 0.8 !important; }
}

.pagination-rounded .page-link { border-radius: 8px !important; margin: 0 3px; border: none !important; background: #f1f5f9; color: #64748b; padding: 8px 14px; }
.pagination-rounded .page-item.active .page-link { background: #6366f1 !important; color: white !important; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }

.transition-all { transition: all 0.2s ease-in-out; }
.action-btn { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.action-btn:hover { box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
</style>
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border">
        <div>
            <h3 class="m-0 fw-bold fs-5 text-gradient-primary">Service Plans</h3>
            <p class="text-muted small mt-1 mb-0">Manage system billing tiers and features.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 header-actions">
            <a href="/tenant/?url=admin/print_plans" target="_blank" class="btn btn-white border shadow-sm rounded-pill px-3 fw-bold transition-all text-primary d-inline-flex align-items-center gap-2 btn-sm">
                <i class="fa-solid fa-print"></i>
                <span>EXPORT</span>
            </a>
            <a href="/tenant/?url=admin/payment_settings" class="btn btn-white border shadow-sm rounded-pill px-3 fw-bold transition-all text-muted d-inline-flex align-items-center gap-2 btn-sm">
                <i class="fa-solid fa-credit-card"></i>
                <span>GATEWAYS</span>
            </a>
            <button class="btn btn-primary rounded-pill px-3 shadow-sm fw-bold d-inline-flex align-items-center gap-2 btn-sm" onclick="openPlanModal()">
                <i class="fa-solid fa-plus"></i>
                <span>ADD PLAN</span>
            </button>
        </div>
    </div>

    <!-- DataTable Controls Container (Top) -->
    <div id="plans-top-controls" class="mb-4"></div>

    <!-- Table / Card Wrapper -->
    <div class="premium-stat-card d-block p-0 overflow-hidden shadow-sm border rounded-4 bg-white mb-4 animate-fade-up">
        <div class="table-responsive p-3 d-none d-md-block">
            <table id="plansTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr class="text-uppercase small fw-bold text-muted">
                        <th class="ps-4">ID</th>
                        <th>Plan Name</th>
                        <th class="text-end">Monthly</th>
                        <th class="text-end">Yearly</th>
                        <th class="text-center">Prop Limit</th>
                        <th class="text-center">Room Limit</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Mobile Grid -->
        <div id="plans-grid" class="d-md-none p-3 row g-3">
             <div class="col-12 text-center py-5 text-muted opacity-50">
                <i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i>
                <p class="small">Loading Plans...</p>
            </div>
        </div>
    </div>

    <!-- DataTable Controls Container (Bottom) -->
    <div id="plans-bottom-controls" class="mt-4"></div>
</div>

<!-- Add/Edit Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light py-4 px-4 rounded-top-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="planModalLabel">Add New Plan</h5>
                    <p class="text-muted mb-0 small" id="planModalDesc">Configure the billing plan details below.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="planForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <input type="hidden" id="planId" name="plan_id" value="">
                
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small">Plan Name</label>
                        <input type="text" class="form-control rounded-3 py-2" id="planName" name="name" required placeholder="e.g. Starter">
                    </div>
                    
                    <div class="row gx-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary fw-semibold small">Monthly Price (â‚±)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3 py-2" id="planPriceMonthly" name="price_monthly" required placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary fw-semibold small">Yearly Price (â‚±)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3 py-2" id="planPriceYearly" name="price_yearly" required placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="row gx-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary fw-semibold small">Property Limit</label>
                            <input type="number" min="1" class="form-control rounded-3 py-2" id="planBHouseLimit" name="bhouse_limit" required placeholder="e.g. 2">
                            <div class="form-text small">Enter 9999 for unlimited.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary fw-semibold small">Room Limit</label>
                            <input type="number" min="1" class="form-control rounded-3 py-2" id="planRoomLimit" name="room_limit" required placeholder="e.g. 5">
                            <div class="form-text small">Enter 9999 for unlimited.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary fw-semibold small">Free Trial (Days)</label>
                            <input type="number" min="0" class="form-control rounded-3 py-2" id="planLengthFree" name="length_free" required value="0">
                            <div class="form-text small">Start billing after X days.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small">Features</label>
                        <textarea class="form-control rounded-3 py-2" id="planFeatures" name="features" rows="4" placeholder="Enter one feature per line..."></textarea>
                        <div class="form-text small">These features will be displayed as bullet points.</div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 px-4 pb-4 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="savePlanBtn">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Plan Modal -->
<div class="modal fade" id="viewPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-primary py-4 px-4 rounded-top-4 text-white">
                <div>
                    <h5 class="modal-title fw-bold" id="viewPlanModalLabel">Plan Details</h5>
                    <p class="text-white-50 mb-0 small">Overview of the selected service tier.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-white">
                <!-- Plan Summary -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                        <i class="fa-solid fa-gem fa-2x"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1" id="vPlanName">--</h3>
                    <div class="mt-2 text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 m-1 border border-primary border-opacity-25" id="vPlanBHouseLimit" style="font-weight: 700;">-- Properties</span>
                        <span class="badge bg-primary rounded-pill px-3 m-1 shadow-sm" id="vPlanLimit" style="font-weight: 700;">-- Rooms</span>
                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 m-1 border border-info border-opacity-25" id="vPlanFreeTrial" style="font-weight: 700;">-- Days Free</span>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <span class="text-secondary small d-block mb-1">Monthly Billing</span>
                            <h4 class="fw-bold text-dark mb-0" id="vPlanPriceMonthly">â‚±0.00</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-4 text-center border border-primary border-opacity-10">
                            <span class="text-secondary small d-block mb-1">Yearly Billing</span>
                            <h4 class="fw-bold text-primary mb-0" id="vPlanPriceYearly">â‚±0.00</h4>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-secondary fw-semibold small text-uppercase mb-3 d-block border-bottom pb-2">Included Features</label>
                    <div id="vPlanFeaturesList" class="d-flex flex-column gap-2">
                        <!-- Features dynamic -->
                    </div>
                </div>

                <div class="text-center">
                    <span class="text-muted small">Created on <span id="vPlanCreatedAt">--</span></span>
                </div>
            </div>
            
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-pill px-5 border shadow-sm fw-semibold" data-bs-dismiss="modal">Close View</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Add a clean look to the actions container */
.plan-actions .btn { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
.transition-all { transition: all 0.2s ease-in-out; }
.action-btn { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.action-btn:hover { box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
</style>

<script>
let plansTable;
let currentPlansData = {}; // To easily fetch plan data by ID for the edit modal

$(document).ready(function() {
    plansTable = $('#plansTable').DataTable({
        ajax: {
            url: '/tenant/?url=admin/get_plans_json',
            type: 'POST',
            data: { csrf_token: '<?= htmlspecialchars(Csrf::token()) ?>' },
            dataType: 'json',
            dataSrc: 'data',
            error: function (xhr, error, code) {
                console.error("DataTables Ajax Error:", xhr.responseText);
                Swal.fire('Table Error', 'Failed to load plans. Check console for details.', 'error');
            }
        },
        autoWidth: false,
        pageLength: 25,
        dom: 'rtip', // Controls moved manually
        language: {
            lengthMenu: "Show _MENU_ entries",
            search: "_INPUT_",
            searchPlaceholder: "Search plans...",
            info: "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ entries</span>",
            paginate: { previous: "<i class='fa-solid fa-chevron-left'></i>", next: "<i class='fa-solid fa-chevron-right'></i>" }
        },
        order: [[0, 'asc']], // Ordered by ID ascending
        columns: [
            { 
                data: 'id',
                className: 'px-4 text-secondary small',
                render: (data) => `<span class="fw-bold">#${data}</span>`
            },
            { 
                data: 'name',
                className: 'px-4 fw-bold text-dark',
                render: function(data, type, row) {
                    // Stash raw data for editing
                    currentPlansData[row.id] = row;
                    return `<span class="d-flex align-items-center"><i class="fa-solid fa-gem text-primary me-2 opacity-50"></i>${data}</span>`;
                }
            },
            { 
                data: 'price_monthly',
                className: 'px-4 text-end',
                render: (data) => `â‚±${parseFloat(data).toFixed(2)}`
            },
            { 
                data: 'price_yearly',
                className: 'px-4 text-end',
                render: (data) => `â‚±${parseFloat(data).toFixed(2)}`
            },
            { 
                data: 'bhouse_limit',
                className: 'px-4 text-center',
                render: (data) => {
                    return data >= 9999 
                        ? `<span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill fw-bold"><i class="fa-solid fa-infinity me-1"></i>Unlimited</span>` 
                        : `<span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill fw-bold border border-primary border-opacity-25">${data} Props</span>`;
                }
            },
            { 
                data: 'room_limit',
                className: 'px-4 text-center',
                render: (data) => {
                    return data >= 9999 
                        ? `<span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill fw-bold"><i class="fa-solid fa-infinity me-1"></i>Unlimited</span>` 
                        : `<span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill fw-bold">${data} Rooms</span>`;
                }
            },
            { 
                data: null,
                className: 'px-4 text-end plan-actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex justify-content-end gap-2 px-2">
                            <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all action-btn" 
                                    style="background: #f0f9ff; color: #0369a1; border: 1.5px solid #0ea5e9; font-size: 0.75rem;"
                                    onmouseover="this.style.background='#0ea5e9'; this.style.color='#fff'; this.style.transform='translateY(-2px)';" 
                                    onmouseout="this.style.background='#f0f9ff'; this.style.color='#0369a1'; this.style.transform='translateY(0)';"
                                    onclick="viewPlan(${row.id})" title="View Details">
                                <i class="fa-solid fa-eye me-1"></i>VIEW
                            </button>
                            <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all action-btn" 
                                    style="background: #f0fdf4; color: #15803d; border: 1.5px solid #22c55e; font-size: 0.75rem;"
                                    onmouseover="this.style.background='#22c55e'; this.style.color='#fff'; this.style.transform='translateY(-2px)';" 
                                    onmouseout="this.style.background='#f0fdf4'; this.style.color='#15803d'; this.style.transform='translateY(0)';"
                                    onclick="editPlan(${row.id})" title="Edit Plan">
                                <i class="fa-solid fa-pen-to-square me-1"></i>EDIT
                            </button>
                            <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all action-btn" 
                                    style="background: #fef2f2; color: #b91c1c; border: 1.5px solid #ef4444; font-size: 0.75rem;"
                                    onmouseover="this.style.background='#ef4444'; this.style.color='#fff'; this.style.transform='translateY(-2px)';" 
                                    onmouseout="this.style.background='#fef2f2'; this.style.color='#b91c1c'; this.style.transform='translateY(0)';"
                                    onclick="deletePlan(${row.id}, '${row.name}')" title="Delete Plan">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const $grid = $('#plans-grid');
            
            // Stash raw data for editing (Refresh the map on each draw)
            data.each(row => { currentPlansData[row.id] = row; });

            // Generate Mobile Cards
            $grid.empty();
            if (data.length === 0) {
                $grid.html('<div class="col-12 text-center py-5 text-muted opacity-50"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>No plans found.</div>');
            } else {
                data.each(function(row) {
                    const name = row.name;
                    const monthly = parseFloat(row.price_monthly).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    const yearly = parseFloat(row.price_yearly).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    const props = row.bhouse_limit >= 9999 ? 'Unlimited' : `${row.bhouse_limit}`;
                    const rooms = row.room_limit >= 9999 ? 'Unlimited' : `${row.room_limit}`;
                    const trial = row.length_free > 0 ? `${row.length_free} Days Free` : 'No Trial';

                    const card = `
                        <div class="col-12">
                            <div class="premium-stat-card p-4 shadow-sm border-0 border-top border-4 border-primary position-relative bg-white rounded-4 overflow-hidden text-start">
                                <!-- Top Row: Icon & Name -->
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-pill d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                                            <i class="fa-solid fa-gem fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-900 text-dark fs-5 mb-0">${name}</div>
                                            <div class="text-xs text-muted fw-700 letter-spacing-1 opacity-75">SERVICE PLAN</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button onclick="viewPlan(${row.id})" class="btn btn-sm btn-light border-0 shadow-none rounded-circle p-2" title="View"><i class="fa-solid fa-eye text-primary"></i></button>
                                        <button onclick="editPlan(${row.id})" class="btn btn-sm btn-light border-0 shadow-none rounded-circle p-2" title="Edit"><i class="fa-solid fa-pen-to-square text-success"></i></button>
                                        <button onclick="deletePlan(${row.id}, '${name}')" class="btn btn-sm btn-light border-0 shadow-none rounded-circle p-2" title="Delete"><i class="fa-solid fa-trash-can text-danger"></i></button>
                                    </div>
                                </div>

                                <!-- Pricing Row -->
                                <div class="row g-2 mb-4">
                                    <div class="col-6">
                                        <div class="p-3 rounded-4 bg-light border text-center transition-all">
                                            <div class="text-xs text-muted fw-800 text-uppercase mb-1 opacity-75">Monthly</div>
                                            <div class="fw-900 text-dark">â‚±${monthly}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-4 bg-primary text-center transition-all shadow-sm">
                                            <div class="text-white text-opacity-75 fw-800 text-uppercase mb-1" style="font-size: 0.65rem;">Yearly (Save)</div>
                                            <div class="fw-900 text-white">â‚±${yearly}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vertical Data Rows (Limits) -->
                                <div class="mb-4 d-flex flex-column gap-2">
                                    <div class="p-3 rounded-3 bg-light d-flex align-items-center justify-content-between">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50"><i class="fa-solid fa-house-chimney me-2"></i>Properties</div>
                                        <div class="small fw-800 text-dark">${props}</div>
                                    </div>
                                    <div class="p-3 rounded-3 bg-light d-flex align-items-center justify-content-between">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50"><i class="fa-solid fa-key me-2"></i>Room Limit</div>
                                        <div class="small fw-800 text-dark">${rooms}</div>
                                    </div>
                                    <div class="p-3 rounded-3 bg-light d-flex align-items-center justify-content-between">
                                        <div class="text-xs text-muted text-uppercase fw-800 opacity-50"><i class="fa-solid fa-clock me-2"></i>Free Trial</div>
                                        <div class="small fw-800 text-info">${trial}</div>
                                    </div>
                                </div>

                                <!-- Primary Action -->
                                <button onclick="viewPlan(${row.id})" class="btn btn-primary w-100 rounded-pill py-3 fw-900 shadow-sm letter-spacing-1 d-flex align-items-center justify-content-center gap-2">
                                    <i class="fa-solid fa-circle-info mt-1"></i> VIEW FULL FEATURES
                                </button>
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

            if ($length.length) $('#plans-top-controls').append($length);
            if ($filter.length) $('#plans-top-controls').append($filter);
            if ($info.length) $('#plans-bottom-controls').append($info);
            if ($paginate.length) $('#plans-bottom-controls').append($paginate);

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

    // Handle Form Submission (Create or Update)
    $('#planForm').on('submit', function(e) {
        e.preventDefault();
        
        // Convert multiline text to JSON array
        const rawFeatures = $('#planFeatures').val().split('\n').map(f => f.trim()).filter(f => f.length > 0);
        const featuresJson = JSON.stringify(rawFeatures);
        
        const isEdit = $('#planId').val() !== '';
        const endpoint = isEdit ? '/tenant/?url=admin/update_plan' : '/tenant/?url=admin/create_plan';
        
        const btn = $('#savePlanBtn');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');

        // Override the features field dynamically before Serialize
        const formData = $(this).serializeArray();
        const featuresIndex = formData.findIndex(item => item.name === 'features');
        if (featuresIndex !== -1) {
            formData[featuresIndex].value = featuresJson;
        }

        $.ajax({
            url: endpoint,
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(res) {
                console.log("Plan Save Response:", res);
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Success!', text: res.message, timer: 1500, showConfirmButton: false });
                    $('#planModal').modal('hide');
                    plansTable.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.message || 'Failed to save plan.', 'error');
                }
            },
            error: function(xhr) {
                console.error("Plan Save Ajax Failed:", xhr.responseText);
                let msg = 'Failed to save plan.';
                try { 
                    const parsed = JSON.parse(xhr.responseText);
                    msg = parsed.message || msg;
                } catch(e) {}
                Swal.fire('Error', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('Save Plan');
            }
        });
    });
    // Automatic Yearly Price Calculation (12 months with 20% discount)
    $('#planPriceMonthly').on('input', function() {
        const monthly = parseFloat($(this).val()) || 0;
        const yearly = monthly * 12 * 0.8;
        $('#planPriceYearly').val(yearly.toFixed(2));
    });
});

function openPlanModal() {
    $('#planForm')[0].reset();
    $('#planId').val('');
    $('#planModalLabel').text('Add New Plan');
    $('#planModalDesc').text('Create a new billing plan for your tenants.');
    new bootstrap.Modal(document.getElementById('planModal')).show();
}

function editPlan(id) {
    const plan = currentPlansData[id];
    if (!plan) return;

    $('#planForm')[0].reset();
    $('#planId').val(id);
    
    $('#planName').val(plan.name);
    $('#planPriceMonthly').val(plan.price_monthly);
    $('#planPriceYearly').val(plan.price_yearly);
    $('#planBHouseLimit').val(plan.bhouse_limit);
    $('#planRoomLimit').val(plan.room_limit);
    $('#planLengthFree').val(plan.length_free);
    
    // Parse features back to newline separated string
    try {
        const feats = JSON.parse(plan.features || '[]');
        if (Array.isArray(feats)) {
            $('#planFeatures').val(feats.join('\n'));
        }
    } catch (e) {
        $('#planFeatures').val('');
    }

    $('#planModalLabel').text('Edit Plan');
    $('#planModalDesc').text(`Updating details for ${plan.name}.`);
    
    new bootstrap.Modal(document.getElementById('planModal')).show();
}

function deletePlan(id, name) {
    Swal.fire({
        title: 'Delete Plan?',
        html: `Are you sure you want to permanently delete the <strong>${name}</strong> plan?<br><br><small class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Active subscriptions will prevent deletion.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/tenant/?url=admin/delete_plan',
                type: 'POST',
                data: {
                    plan_id: id,
                    csrf_token: '<?= Csrf::token() ?>'
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message, timer: 1500, showConfirmButton: false });
                        plansTable.ajax.reload(null, false);
                    } else {
                        Swal.fire('Action Blocked', res.message || 'Failed to delete plan.', 'error');
                    }
                },
                error: function(xhr) {
                    console.error("Plan Delete Ajax Failed:", xhr.responseText);
                    let msg = 'Failed to delete plan.';
                    try { 
                        const parsed = JSON.parse(xhr.responseText);
                        msg = parsed.message || msg;
                    } catch(e) {}
                    Swal.fire('Error', msg, 'error');
                }
            });
        }
    });
}

function viewPlan(id) {
    const plan = currentPlansData[id];
    if (!plan) return;

    $('#vPlanName').text(plan.name);
    $('#vPlanPriceMonthly').text(`â‚±${parseFloat(plan.price_monthly).toFixed(2)}`);
    $('#vPlanPriceYearly').text(`â‚±${parseFloat(plan.price_yearly).toFixed(2)}`);
    
    // Property Limit
    const bhouseText = plan.bhouse_limit >= 9999 ? 'Unlimited Properties' : `${plan.bhouse_limit} Properties`;
    $('#vPlanBHouseLimit').text(bhouseText).removeClass('text-primary text-success border-primary border-success bg-primary bg-success').addClass(plan.bhouse_limit >= 9999 ? 'text-success border-success bg-success' : 'text-primary border-primary bg-primary');

    // Room Limit
    const limitText = plan.room_limit >= 9999 ? 'Unlimited Rooms' : `${plan.room_limit} Rooms`;
    $('#vPlanLimit').text(limitText).removeClass('bg-primary bg-success').addClass(plan.room_limit >= 9999 ? 'bg-success' : 'bg-primary');

    // Free Trial
    const trialText = plan.length_free > 0 ? `${plan.length_free} Days Free` : 'No Free Trial';
    $('#vPlanFreeTrial').text(trialText)
        .removeClass('text-info border-info bg-info text-muted border-secondary bg-light bg-opacity-10')
        .addClass(plan.length_free > 0 ? 'text-info border-info bg-info bg-opacity-10' : 'text-muted border-secondary bg-light');

    // Features List
    const featContainer = $('#vPlanFeaturesList');
    featContainer.empty();
    try {
        const feats = JSON.parse(plan.features || '[]');
        if (Array.isArray(feats) && feats.length > 0) {
            feats.forEach(f => {
                featContainer.append(`
                    <div class="d-flex align-items-center bg-light p-2 px-3 rounded-pill border">
                        <i class="fa-solid fa-circle-check text-success me-2 small"></i>
                        <span class="text-dark small fw-medium">${f}</span>
                    </div>
                `);
            });
        } else {
            featContainer.append('<div class="text-muted small text-center py-2 italic">No features listed.</div>');
        }
    } catch (e) {
        featContainer.append('<div class="text-danger small text-center py-2">Error loading features.</div>');
    }

    // Created At
    const date = new Date(plan.created_at);
    $('#vPlanCreatedAt').text(date.toLocaleDateString('en-US', { 
        month: 'long', 
        day: 'numeric', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }));

    new bootstrap.Modal(document.getElementById('viewPlanModal')).show();
}
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
