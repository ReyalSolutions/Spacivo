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

#subscriptions-top-controls, #subscriptions-bottom-controls {
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
    #subscriptions-top-controls {
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

#subDetailModal, #statusGuideModal { z-index: 10005 !important; } /* Fix: Pull modal above customized backdrop */

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
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border">
        <div>
            <?php
                $role = strtolower($_SESSION['role'] ?? 'admin');
                if ($role === 'owner') {
                    $title = "My Subscription Plan";
                    $desc  = "Monitor your active and expired owner plans";
                } else {
                    $title = "Subscription Management";
                    $desc  = "View and manage all owner subscription plans system-wide";
                }
            ?>
            <h3 class="m-0 fw-bold fs-5 text-gradient-primary"><?= $title ?></h3>
            <p class="text-muted small mt-1 mb-0"><?= $desc ?></p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 header-actions">
            <button class="btn btn-white border shadow-sm rounded-pill px-3 fw-bold transition-all text-primary d-inline-flex align-items-center gap-2 btn-sm" id="openStatusGuide">
                <i class="fa-solid fa-circle-info"></i>
                <span>STATUS GUIDE</span>
            </button>
        </div>
    </div>

    <!-- DataTable Controls Container (Top) -->
    <div id="subscriptions-top-controls" class="mb-4"></div>

    <!-- Table / Card Wrapper -->
    <div class="premium-stat-card d-block p-0 overflow-hidden shadow-sm border rounded-4 bg-white mb-4 animate-fade-up">
        <div class="table-responsive p-3 d-none d-md-block">
            <table id="subscriptionsTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr class="text-uppercase small fw-bold text-muted">
                        <th class="ps-4">Plan Name</th>
                        <th>Owner</th>
                        <th>Pricing</th>
                        <th>Room Limit</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Mobile Grid -->
        <div id="subscriptions-grid" class="d-md-none p-3 row g-3">
             <div class="col-12 text-center py-5 text-muted opacity-50">
                <i class="fa-solid ui-skeleton ui-skeleton-line mb-2"></i>
                <p class="small">Loading Subscriptions...</p>
            </div>
        </div>
    </div>

    <!-- DataTable Controls Container (Bottom) -->
    <div id="subscriptions-bottom-controls" class="mt-4"></div>
</div>

<script>
$(document).ready(function() {
    <?php if (isset($_SESSION['success'])): ?>
        Feedback.fire({
            icon: 'success',
            title: 'Success',
            text: <?= json_encode($_SESSION['success'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            timer: 3000,
            showConfirmButton: false,
            background: '#f8fafc'
        });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        Feedback.fire({
            icon: 'error',
            title: 'Error',
            text: <?= json_encode($_SESSION['error'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            background: '#f8fafc'
        });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    if ($.fn.DataTable.isDataTable('#subscriptionsTable')) {
        $('#subscriptionsTable').DataTable().destroy();
    }

    window.subscriptionsTable = $('#subscriptionsTable').DataTable({
        ajax: '/tenant/?url=admin/get_subscriptions_json',
        order: [[0, 'asc']], // Order by Plan Name by default Name
        pageLength: 10,
        responsive: true,
        columns: [
            {
                data: 'plan_name',
                render: function(data, type, row) {
                    const colors = { Starter: '#2563eb', Standard: '#0ea5e9', Premium: '#f59e0b', Enterprise: '#10b981' };
                    const color = colors[data] || '#2563eb';
                    return `<div class="d-flex align-items-center gap-2">
                                <div class="plan-icon" style="background:${color}22; color:${color};">
                                    <i class="fa-solid fa-gem fs-6"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color:${color};">${data}</div>
                                    <div class="small text-muted" style="font-size:0.72rem;">SUB-${String(row.id).padStart(4,'0')}</div>
                                </div>
                            </div>`;
                }
            },
            {
                data: 'first_name',
                render: function(data, type, row) {
                    const initials = (data ? data[0] : '') + (row.last_name ? row.last_name[0] : '');
                    return `<div class="d-flex align-items-center gap-2">
                                <div class="owner-avatar">${initials.toUpperCase()}</div>
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size:0.85rem;">${data} ${row.last_name}</div>
                                    <div class="text-muted" style="font-size:0.72rem;">${row.email || '—'}</div>
                                </div>
                            </div>`;
                }
            },
            {
                data: 'price_monthly',
                render: function(data, type, row) {
                    const isYearly = row.billing_cycle === 'yearly';
                    const price    = isYearly ? parseFloat(row.price_yearly) : parseFloat(row.price_monthly);
                    const cycleLabel = isYearly
                        ? `<span class="cycle-badge cycle-yearly"><i class="fa-solid fa-calendar me-1"></i>Yearly</span>`
                        : `<span class="cycle-badge cycle-monthly"><i class="fa-solid fa-rotate me-1"></i>Monthly</span>`;
                    return `<div class="d-flex flex-column gap-1">
                                <span class="fw-bold text-dark" style="font-size:0.92rem;">₱${price.toFixed(2)}</span>
                                ${cycleLabel}
                            </div>`;
                }
            },
            {
                data: 'room_limit',
                render: function(data) {
                    const isUnlimited = parseInt(data) >= 9999;
                    return isUnlimited
                        ? `<span class="sub-badge sub-badge-purple"><i class="fa-solid fa-infinity me-1"></i>Unlimited</span>`
                        : `<span class="sub-badge sub-badge-slate"><i class="fa-solid fa-door-open me-1"></i>${data} Rooms</span>`;
                }
            },
            {
                data: 'status',
                render: function(data) { return renderStatusBadge(data); }
            },
            {
                data: null,
                orderable: false,
                className: 'text-end pe-4',
                render: function(data, type, row) {
                    return `<button class="sub-view-btn" title="View Details">
                                <i class="fa-solid fa-eye"></i>
                                <span>Details</span>
                            </button>`;
                }
            }
        ],
        dom: 'rtip', // Controls moved manually
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search subscriptions...",
            lengthMenu: "Show _MENU_ entries",
            info: "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ entries</span>",
            paginate: {
                previous: '<i class="fa-solid fa-chevron-left"></i>',
                next: '<i class="fa-solid fa-chevron-right"></i>'
            }
        },
        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const $grid = $('#subscriptions-grid');

            // Generate Mobile Cards
            $grid.empty();
            if (data.length === 0) {
                $grid.html('<div class="col-12 text-center py-5 text-muted opacity-50"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>No subscriptions found.</div>');
            } else {
                data.each(function(row) {
                    const initials = (row.first_name ? row.first_name[0] : '') + (row.last_name ? row.last_name[0] : '');
                    const isYearly = row.billing_cycle === 'yearly';
                    const price = isYearly ? parseFloat(row.price_yearly) : parseFloat(row.price_monthly);
                    const cycleLabel = isYearly ? 'YEARLY' : 'MONTHLY';
                    const cycleClass = isYearly ? 'bg-primary' : 'bg-info';

                    const colors = { Starter: '#2563eb', Standard: '#0ea5e9', Premium: '#f59e0b', Enterprise: '#10b981' };
                    const planColor = colors[row.plan_name] || '#2563eb';
                    const statusBadge = renderStatusBadge(row.status);

                    // Determine border color based on status
                    let borderColor = '#e2e8f0';
                    if (row.status === 'active') borderColor = '#10b981';
                    if (row.status === 'trial') borderColor = '#6366f1';
                    if (row.status === 'past_due' || row.status === 'suspended') borderColor = '#f59e0b';
                    if (row.status === 'cancelled' || row.status === 'expired') borderColor = '#ef4444';

                    const card = `
                        <div class="col-12">
                            <div class="premium-stat-card p-4 shadow-sm border-0 border-start border-4 position-relative bg-white rounded-4 overflow-hidden text-start" style="border-left-color: ${borderColor} !important;">
                                <!-- Top Row: Plan & Status -->
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-pill d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; background: ${planColor}11; color: ${planColor};">
                                            <i class="fa-solid fa-gem fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-900 text-dark fs-6 mb-0">${row.plan_name}</div>
                                            <div class="text-xs text-muted fw-700 opacity-75">SUB-${String(row.id).padStart(4,'0')}</div>
                                        </div>
                                    </div>
                                    <div>${statusBadge}</div>
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
                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <div class="text-xs text-muted text-uppercase fw-800 mb-1 opacity-50">Pricing</div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-900 text-dark fs-5">₱${price.toFixed(2)}</span>
                                            <span class="badge ${cycleClass} rounded-pill text-xs mt-1" style="width: fit-content; font-size: 0.65rem;">${cycleLabel}</span>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="text-xs text-muted text-uppercase fw-800 mb-1 opacity-50">Room Limit</div>
                                        <div class="fw-800 text-dark">
                                            ${parseInt(row.room_limit) >= 9999 ? '<i class="fa-solid fa-infinity me-1"></i>Unlimited' : row.room_limit + ' Rooms'}
                                        </div>
                                    </div>
                                </div>

                                <!-- View Details Action -->
                                <button class="btn btn-primary w-100 rounded-pill py-3 fw-900 shadow-sm letter-spacing-1 d-flex align-items-center justify-content-center gap-2 sub-view-btn">
                                    <i class="fa-solid fa-circle-info mt-1"></i> VIEW FULL DETAILS
                                </button>
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

            if ($length.length) $('#subscriptions-top-controls').append($length);
            if ($filter.length) $('#subscriptions-top-controls').append($filter);
            if ($info.length) $('#subscriptions-bottom-controls').append($info);
            if ($paginate.length) $('#subscriptions-bottom-controls').append($paginate);

            // Pagination Styling
            $('.pagination').addClass('pagination-rounded gap-1');
            $('.page-link').addClass('rounded-3 border-0 shadow-none');
            if ($(window).width() < 576) {
                $('.pagination').addClass('justify-content-center mt-3');
            }
        }
    });

    // Wire up Details buttons (Global delegation)
    $(document).on('click', '.sub-view-btn', function() {
        const table = $('#subscriptionsTable').DataTable();
        // Handle both table rows and grid buttons
        let row;
        if ($(this).closest('tr').length) {
            row = table.row($(this).closest('tr')).data();
        } else {
            // For grid, we need to find the data by index or ID
            // Since we're in a loop in drawCallback, we can attach the data directly or use a helper
            // However, the standard way in this file's pattern is to find the row from the table
            // Let's find the ID from the card text and lookup
            const idText = $(this).closest('.premium-stat-card').find('.text-muted').first().text();
            const id = parseInt(idText.replace('SUB-', ''));
            row = table.rows().data().toArray().find(r => r.id == id);
        }

        if (!row) return;

        const colors = { Starter: '#2563eb', Standard: '#0ea5e9', Premium: '#f59e0b', Enterprise: '#10b981' };
        const color = colors[row.plan_name] || '#2563eb';

        let start = (row.start_date && row.start_date !== '0000-00-00') ? row.start_date : 'N/A';
        let end = row.current_cycle_end || ((row.end_date && row.end_date !== '0000-00-00') ? row.end_date : 'N/A');

        // Dynamic Expiration Calculation if end_date is N/A
        if (end === 'N/A' && start !== 'N/A') {
            const sd = new Date(start);
            if (row.billing_cycle === 'yearly') {
                sd.setFullYear(sd.getFullYear() + 1);
            } else {
                sd.setMonth(sd.getMonth() + 1);
            }
            end = sd.toISOString().split('T')[0] + ' (Estimated)';
        } else if (end === 'N/A' && row.created_at) {
            const cd = new Date(row.created_at);
            if (row.billing_cycle === 'yearly') {
                cd.setFullYear(cd.getFullYear() + 1);
            } else {
                cd.setMonth(cd.getMonth() + 1);
            }
            end = cd.toISOString().split('T')[0] + ' (Estimated)';
            if (start === 'N/A') start = row.created_at.split(' ')[0];
        }

        let statusBadge = renderStatusBadge(row.status);

        const isUnlimited = parseInt(row.room_limit) >= 9999;
        const roomBadge = isUnlimited
            ? `<span class="sub-badge sub-badge-purple"><i class="fa-solid fa-infinity me-1"></i>Unlimited Rooms</span>`
            : `<span class="sub-badge sub-badge-slate"><i class="fa-solid fa-door-open me-1"></i>${row.room_limit} Rooms</span>`;

        const features = (() => {
            try { return JSON.parse(row.features || '[]'); } catch(e) { return []; }
        })();
        const featuresHtml = features.length
            ? features.map(f => `<li class="d-flex align-items-center gap-2 py-1">
                    <i class="fa-solid fa-circle-check text-success" style="font-size:0.85rem;"></i>
                    <span style="font-size:0.85rem;">${f}</span>
                </li>`).join('')
            : `<li class="text-muted" style="font-size:0.85rem;">No features listed.</li>`;

        // Populate modal
        $('#subDetailPlanColor').css('background', `linear-gradient(135deg, ${color}22, ${color}11)`);
        $('#subDetailPlanIcon').css('color', color);
        $('#subDetailPlanName').text(row.plan_name).css('color', color);
        $('#subDetailSubId').text(`SUB-${String(row.id).padStart(4,'0')}`);
        $('#subDetailOwner').text(`${row.first_name} ${row.last_name}`);
        $('#subDetailEmail').text(row.email || '—');

        // Pricing with active cycle highlight
        const isYearly   = row.billing_cycle === 'yearly';
        const cycleHtml  = isYearly
            ? `<span class="cycle-badge cycle-yearly"><i class="fa-solid fa-calendar me-1"></i>Yearly Billing</span>`
            : `<span class="cycle-badge cycle-monthly"><i class="fa-solid fa-rotate me-1"></i>Monthly Billing</span>`;
        $('#subDetailBillingCycle').html(cycleHtml);

        $('#subDetailMonthly').html(`₱${parseFloat(row.price_monthly).toFixed(2)}`);
        $('#subDetailMonthlyActive').html(!isYearly ? `<span class="cycle-badge cycle-monthly" style="font-size:0.65rem;">Active Plan</span>` : '');

        if (isYearly && row.status !== 'cancelled' && row.status !== 'expired') {
            $('#switchToMonthlyBtnContainer').html(
                `<button class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 fw-semibold" onclick="switchToMonthly(${row.id})" style="font-size:0.7rem; letter-spacing:0.02em;">Switch</button>`
            );
        } else {
            $('#switchToMonthlyBtnContainer').html('');
        }

        $('#subDetailYearly').html(`₱${parseFloat(row.price_yearly).toFixed(2)}`);
        $('#subDetailYearlyActive').html(isYearly ? `<span class="cycle-badge cycle-yearly" style="font-size:0.65rem;">Active Plan</span>` : '');

        if (!isYearly && row.status !== 'cancelled' && row.status !== 'expired') {
            $('#upgradeYearlyBtnContainer').html(
                `<button class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 fw-semibold" onclick="upgradeToYearly(${row.id}, ${row.plan_id}, '${row.plan_name}', ${row.price_yearly})" style="font-size:0.7rem; letter-spacing:0.02em;">Upgrade</button>`
            );
        } else {
            $('#upgradeYearlyBtnContainer').html('');
        }

        $('#subDetailRoomLimit').html(roomBadge);
        $('#subDetailStatus').html(statusBadge);
        $('#subDetailStart').text(start);
        $('#subDetailEnd').text(end);
        $('#subDetailFeatures').html(featuresHtml);
        $('#subDetailCreated').text(row.created_at || '—');

        // Actions in footer
        let actionsHtml = '';
        if (row.status !== 'cancelled' && row.status !== 'expired') {
            // Upgrade Plan
            actionsHtml += `<button class="btn btn-primary shadow-sm rounded-pill px-4 py-2 fw-semibold" onclick="openUpgradePlanModal(${row.id}, ${row.plan_id}, '${row.billing_cycle}')" style="font-size: 0.85rem; background: linear-gradient(135deg, #3b82f6, #0ea5e9); border: none;"><i class="fa-solid fa-arrow-up-right-dots me-2"></i>Change Plan</button>`;
            // Cancel Subscription
            actionsHtml += `<button class="btn btn-outline-danger shadow-sm rounded-pill px-3 py-2 fw-semibold" onclick="cancelSubscription(${row.id})" style="font-size: 0.85rem;"><i class="fa-solid fa-ban me-2"></i>Cancel Subscription</button>`;
        }
        $('#subDetailActions').html(actionsHtml);

        const modal = new bootstrap.Modal(document.getElementById('subDetailModal'));
        modal.show();
    });

    // Status Guide button
    $('#openStatusGuide').on('click', function() {
        new bootstrap.Modal(document.getElementById('statusGuideModal')).show();
    });
});

function upgradeToYearly(subId, planId, planName, priceYearly) {
    const yearly = parseFloat(priceYearly).toLocaleString('en-PH', { minimumFractionDigits: 2 });

    Feedback.fire({
        title: 'Upgrade to Yearly Billing?',
        html: `<div class="py-1">
                <p class="text-muted mb-2">You are about to upgrade <strong>${planName}</strong> to yearly billing.</p>
                <div class="bg-light rounded-3 p-3 text-start d-inline-block">
                    <div class="fw-semibold text-dark">Annual Payment Due</div>
                    <div class="fs-4 fw-bold text-success">₱${yearly}</div>
                    <div class="small text-muted mt-1"><i class="fa-solid fa-shield-halved me-1"></i>Processed securely via PayMongo</div>
                </div>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-arrow-right me-1"></i> Proceed to Payment',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    }).then((result) => {
        if (!result.isConfirmed) return;

        // Close the detail modal first so the payment modal isn't obscured behind it
        const detailModalEl = document.getElementById('subDetailModal');
        const detailModal = bootstrap.Modal.getInstance(detailModalEl);

        function _openPayment() {
            openPaymentSelection({
                subId: subId,
                planId: planId,
                planName: planName,
                amount: parseFloat(priceYearly),
                cycle: 'yearly',
                initiateUrl: '/tenant/?url=admin/upgrade_plan'
            });
        }

        if (detailModal) {
            detailModalEl.addEventListener('hidden.bs.modal', _openPayment, { once: true });
            detailModal.hide();
        } else {
            _openPayment();
        }
    });
}


function switchToMonthly(subId) {
    Feedback.fire({
        title: 'Switch to Monthly?',
        text: 'Are you sure you want to switch this subscription to monthly billing?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0369a1',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, switch it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/tenant/?url=admin/downgrade_subscription_monthly', {
                subscription_id: subId,
                csrf_token: '<?= Csrf::token() ?>'
            }, function(res) {
                if (res.success) {
                    Feedback.fire({ icon: 'success', title: 'Switched!', text: res.message, timer: 2000, showConfirmButton: false });
                    $('#subDetailModal').modal('hide');
                    if (typeof subscriptionsTable !== 'undefined') subscriptionsTable.ajax.reload(null, false);
                } else {
                    Feedback.fire('Error', res.message || 'Failed to switch subscription.', 'error');
                }
            }).fail(function(xhr) {
                let msg = 'Failed to switch subscription.';
                try {
                    msg = JSON.parse(xhr.responseText).message || msg;
                } catch(e) {}
                Feedback.fire('Error', msg, 'error');
            });
        }
    });
}

function cancelSubscription(subId) {
    Feedback.fire({
        title: 'Cancel Subscription?',
        text: 'Are you sure you want to cancel this plan? You will immediately lose access to premium features.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/tenant/?url=admin/cancel_subscription', {
                subscription_id: subId,
                csrf_token: '<?= Csrf::token() ?>'
            }, function(res) {
                if (res.success) {
                    Feedback.fire({ icon: 'success', title: 'Cancelled!', text: res.message, timer: 2000, showConfirmButton: false });
                    $('#subDetailModal').modal('hide');
                    if (typeof subscriptionsTable !== 'undefined') subscriptionsTable.ajax.reload(null, false);
                } else {
                    Feedback.fire('Error', res.message || 'Failed to cancel subscription.', 'error');
                }
            }).fail(function(xhr) {
                let msg = 'Failed to cancel subscription.';
                try {
                    msg = JSON.parse(xhr.responseText).message || msg;
                } catch(e) {}
                Feedback.fire('Error', msg, 'error');
            });
        }
    });
}

function openUpgradePlanModal(subId, planId, billingCycle) {
    $('#subDetailModal').modal('hide');
    showUpgradeRequired(subId, billingCycle);
}

// ── Shared status badge helper ──
function renderStatusBadge(status) {
    const map = {
        active:    { cls: 'sub-badge-active',    dot: true,  icon: '',                               label: 'Active'    },
        trial:     { cls: 'sub-badge-trial',     dot: false, icon: 'fa-solid fa-flask',              label: 'Trial'     },
        pending:   { cls: 'sub-badge-pending',   dot: false, icon: 'fa-solid fa-clock',              label: 'Pending'   },
        expired:   { cls: 'sub-badge-expired',   dot: false, icon: 'fa-solid fa-circle-xmark',       label: 'Expired'   },
        cancelled: { cls: 'sub-badge-cancelled', dot: false, icon: 'fa-solid fa-ban',                label: 'Cancelled' },
        past_due:  { cls: 'sub-badge-past-due',  dot: false, icon: 'fa-solid fa-triangle-exclamation',label:'Past Due'  },
        suspended: { cls: 'sub-badge-suspended', dot: false, icon: 'fa-solid fa-pause-circle',       label: 'Suspended' },
        failed:    { cls: 'sub-badge-failed',    dot: false, icon: 'fa-solid fa-xmark',              label: 'Failed'    },
        renewing:  { cls: 'sub-badge-renewing',  dot: false, icon: 'fa-solid fa-arrows-rotate',      label: 'Renewing'  },
        paused:    { cls: 'sub-badge-paused',    dot: false, icon: 'fa-solid fa-snowflake',           label: 'Paused'    },
    };
    const s = map[status] || { cls: 'sub-badge-slate', dot: false, icon: 'fa-solid fa-circle', label: status };
    const inner = s.dot
        ? `<span class="status-dot"></span>${s.label}`
        : `<i class="${s.icon} me-1"></i>${s.label}`;
    return `<span class="sub-badge ${s.cls}">${inner}</span>`;
}
</script>

<!-- ═══════════════ Subscription Detail Modal ═══════════════ -->
<div class="modal fade" id="subDetailModal" tabindex="-1" aria-labelledby="subDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- Coloured plan banner -->
            <div id="subDetailPlanColor" class="modal-header border-0 py-4 px-4" style="background: #f0f0ff;">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="plan-icon plan-icon-lg" id="subDetailPlanColor2" style="background:rgba(37,99,235,0.15); color:#2563eb;">
                        <i id="subDetailPlanIcon" class="fa-solid fa-gem" style="font-size:1.4rem;"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" id="subDetailPlanName">—</h5>
                        <div class="small text-muted" id="subDetailSubId"></div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">

                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="sub-detail-card">
                            <div class="sub-detail-label"><i class="fa-solid fa-user me-2 text-violet"></i>Owner Details</div>
                            <div class="fw-semibold text-dark fs-6" id="subDetailOwner">—</div>
                            <div class="small text-muted" id="subDetailEmail">—</div>
                        </div>

                        <div class="sub-detail-card mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="sub-detail-label mb-0"><i class="fa-solid fa-coins me-2 text-amber"></i>Pricing</div>
                                <div id="subDetailBillingCycle"></div>
                            </div>
                            <div class="d-flex gap-3 mt-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="fs-5 fw-bold text-dark" id="subDetailMonthly">—</div>
                                        <div id="switchToMonthlyBtnContainer"></div>
                                    </div>
                                    <div class="small text-muted">per month</div>
                                    <div id="subDetailMonthlyActive" class="mt-1"></div>
                                </div>
                                <div class="vr mx-1"></div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="fs-5 fw-bold text-dark" id="subDetailYearly">—</div>
                                        <div id="upgradeYearlyBtnContainer"></div>
                                    </div>
                                    <div class="small text-muted">per year</div>
                                    <div id="subDetailYearlyActive" class="mt-1"></div>
                                </div>
                            </div>
                        </div>

                        <div class="sub-detail-card mt-3">
                            <div class="sub-detail-label"><i class="fa-solid fa-layer-group me-2 text-sky"></i>Capacity & Status</div>
                            <div class="d-flex align-items-center gap-3 mt-1 flex-wrap">
                                <div id="subDetailRoomLimit"></div>
                                <div id="subDetailStatus"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="sub-detail-card">
                            <div class="sub-detail-label"><i class="fa-regular fa-calendar me-2 text-green"></i>Subscription Timeline</div>
                            <div class="d-flex gap-4 mt-2">
                                <div>
                                    <div class="small text-muted">Started</div>
                                    <div class="fw-semibold" id="subDetailStart">—</div>
                                </div>
                                <div>
                                    <div class="small text-muted">Expires</div>
                                    <div class="fw-semibold" id="subDetailEnd">—</div>
                                </div>
                            </div>
                            <div class="small text-muted mt-2">Registered: <span id="subDetailCreated">—</span></div>
                        </div>

                        <div class="sub-detail-card mt-3">
                            <div class="sub-detail-label"><i class="fa-solid fa-star me-2 text-amber"></i>Plan Features</div>
                            <ul class="list-unstyled mb-0 mt-1" id="subDetailFeatures">
                                <li class="text-muted small">—</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 d-flex justify-content-between align-items-center bg-light rounded-bottom-4">
                <div id="subDetailActions" class="d-flex gap-2">
                    <!-- Dynamic actions inserted here by JS -->
                </div>
                <button class="btn btn-secondary rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Close Window</button>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════ Status Guide Modal ═══════════════ -->
<div class="modal fade" id="statusGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <div class="modal-header border-0 py-4 px-4" style="background: linear-gradient(135deg, rgba(37,99,235,0.07), rgba(139,92,246,0.07));">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;border-radius:12px;background:rgba(37,99,235,0.15);display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-circle-info" style="color:#2563eb;font-size:1.3rem;"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Subscription Status Guide</h5>
                        <div class="small text-muted">What each status means for your subscription</div>
                    </div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-3">
                <div class="row g-3">
                    <!-- Active -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-active"><span class="status-dot"></span>Active</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>Subscription is currently valid and usable.</li>
                                <li>User has paid and can access all features.</li>
                                <li>Not yet reached expiration date.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Trial -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-trial"><i class="fa-solid fa-flask me-1"></i>Trial</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>User is in a free trial period.</li>
                                <li>Has access but hasn't paid yet.</li>
                                <li>Converts to Active or Expired after trial ends.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Pending -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-pending"><i class="fa-solid fa-clock me-1"></i>Pending</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>Created but payment is not yet completed.</li>
                                <li>Waiting for confirmation (GCash, bank, PayMongo).</li>
                                <li>Can become Active or Failed.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Expired -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-expired"><i class="fa-solid fa-circle-xmark me-1"></i>Expired</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>Subscription has reached its end date.</li>
                                <li>No access unless renewed.</li>
                                <li>Common for non-auto-renew plans.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Cancelled -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-cancelled"><i class="fa-solid fa-ban me-1"></i>Cancelled</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>User manually stopped the subscription.</li>
                                <li>Access usually continues until end of billing cycle.</li>
                                <li>Will eventually become Expired.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Past Due -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-past-due"><i class="fa-solid fa-triangle-exclamation me-1"></i>Past Due</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>Payment failed but subscription is still active.</li>
                                <li>System may retry payment automatically.</li>
                                <li>Risk of becoming Suspended or Cancelled.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Suspended -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-suspended"><i class="fa-solid fa-pause-circle me-1"></i>Suspended</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>Temporarily disabled (failed payments or violations).</li>
                                <li>User cannot access any features.</li>
                                <li>Can be reactivated by admin.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Failed -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-failed"><i class="fa-solid fa-xmark me-1"></i>Failed</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>Payment attempt failed completely.</li>
                                <li>Subscription never became active.</li>
                                <li>User must start a new subscription.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Renewing -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-renewing"><i class="fa-solid fa-arrows-rotate me-1"></i>Renewing</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>Subscription is set to automatically renew.</li>
                                <li>Still Active but flagged for upcoming billing.</li>
                                <li>No action needed from user.</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Paused -->
                    <div class="col-md-6">
                        <div class="status-guide-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="sub-badge sub-badge-paused"><i class="fa-solid fa-snowflake me-1"></i>Paused</span>
                            </div>
                            <ul class="status-guide-list">
                                <li>Temporarily stopped by user or admin.</li>
                                <li>No billing and no feature access during pause.</li>
                                <li>Can be resumed at any time.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ─── Table Header ─── */
#subscriptionsTable thead th {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 700;
    color: #94a3b8;
    padding: 14px 12px;
    border-bottom: 1px solid #f1f5f9;
}
#subscriptionsTable td {
    font-size: 0.85rem;
    padding: 14px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f8fafc;
}
.table-hover tbody tr:hover {
    background-color: #f8f9ff !important;
    transition: background 0.15s;
}

/* ─── Plan Icon ─── */
.plan-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}

/* ─── Owner Avatar ─── */
.owner-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    font-size: 0.72rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    letter-spacing: 0.05em;
}

/* ─── Sub Badges ─── */
.sub-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 20px;
    letter-spacing: 0.03em;
    white-space: nowrap;
}
.sub-badge-active {
    background: #dcfce7;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}
.sub-badge-expired {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
.sub-badge-pending {
    background: #fef9c3;
    color: #ca8a04;
    border: 1px solid #fde68a;
}
.sub-badge-purple {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}
.sub-badge-slate {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

/* ─── Pulsing Active Dot ─── */
.status-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #16a34a;
    margin-right: 6px;
    animation: pulse-dot 1.8s ease-in-out infinite;
}
@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 0 rgba(22,163,74,0.4); }
    50% { box-shadow: 0 0 0 5px rgba(22,163,74,0); }
}

/* ─── Action Button ─── */
.sub-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border: none;
    border-radius: 20px;
    padding: 6px 16px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
}
.sub-view-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.4);
    color: white;
}
.sub-view-btn:active {
    transform: translateY(0);
}

/* ─── Modal Detail Card ─── */
.sub-detail-card {
    background: #f8fafc;
    border: 1px solid #e8edf5;
    border-radius: 14px;
    padding: 16px 18px;
}
.sub-detail-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
    margin-bottom: 8px;
}
.plan-icon-lg {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.text-violet { color: #7c3aed; }
.text-amber  { color: #d97706; }
.text-sky    { color: #0284c7; }
.text-green  { color: #16a34a; }

/* ─── Extended Status Badges ─── */
.sub-badge-trial {
    background: #fef9c3;
    color: #92400e;
    border: 1px solid #fde68a;
}
.sub-badge-cancelled {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
}
.sub-badge-past-due {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
}
.sub-badge-suspended {
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #cbd5e1;
}
.sub-badge-failed {
    background: #1e293b;
    color: #f1f5f9;
    border: 1px solid #334155;
}
.sub-badge-renewing {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.sub-badge-paused {
    background: #ecfeff;
    color: #0e7490;
    border: 1px solid #a5f3fc;
}

/* ─── Status Guide Button ─── */
.status-guide-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 7px 18px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.status-guide-btn:hover {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
    box-shadow: 0 4px 14px rgba(37,99,235,0.3);
}

/* ─── Status Guide Cards (inside modal) ─── */
.status-guide-card {
    background: #f8fafc;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    padding: 14px 16px;
    height: 100%;
}
.status-guide-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.status-guide-list li {
    font-size: 0.8rem;
    color: #64748b;
    padding: 2px 0;
    padding-left: 14px;
    position: relative;
}
.status-guide-list li::before {
    content: '›';
    position: absolute;
    left: 0;
    color: #94a3b8;
    font-weight: bold;
}

/* ─── Cycle Badges ─── */
.cycle-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.cycle-monthly {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.cycle-yearly {
    background: #fdf4ff;
    color: #a21caf;
    border: 1px solid #fbcfe8;
}
</style>

<?php
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'owner' || $_SESSION['role'] === 'admin')) {
    include __DIR__ . '/../components/payment_modal.php';
}
?>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
