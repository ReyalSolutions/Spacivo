<?php
declare(strict_types=1);
require_once __DIR__ . '/../layouts/management_header.php';
?>

<style>
    
/* Premium Stat Card Styling */
.premium-stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
}
.premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1) !important; }

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

#reviews-top-controls, #reviews-bottom-controls {
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
        justify-content: flex-end !important;
        gap: 10px !important;
    }
    #reviews-top-controls {
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

/* Global Spin Animation */
@keyframes fa-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.fa-spin { animation: fa-spin 1s infinite linear !important; }
.hover-rotate i { transition: transform 0.5s ease; }
.hover-rotate:hover i { transform: rotate(180deg); }

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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-4 p-4 bg-white rounded-4 shadow-sm border animate-fade-down">
        <div>
            <h3 class="m-0 fw-900 fs-5 text-gradient-primary">Platform Reviews</h3>
            <p class="text-muted small mt-1 mb-0 fw-600">Audit and moderate tenant feedback for properties.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-end justify-content-md-end gap-2 header-actions w-100 w-md-auto">
            <button id="refreshBtn" class="btn btn-white border shadow-sm rounded-pill px-4 fw-bold transition-all text-primary action-btn d-inline-flex align-items-center justify-content-center gap-2 btn-sm hover-rotate" style="height: 40px;" onclick="refreshData()">
                <i class="fa-solid fa-rotate-right" id="refreshIcon" style="font-size: 0.9rem;"></i>
                <span id="refreshText">REFRESH</span>
            </button>
        </div>
    </div>

    <!-- DataTable Controls Container (Top) -->
    <div id="reviews-top-controls" class="mb-4"></div>

    <!-- Stats Overview (Quick Count) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="premium-stat-card p-4 shadow-sm border-0 bg-white rounded-4 d-flex align-items-center gap-3">
                <div class="stat-icon-wrap bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 1.6rem; flex-shrink: 0; border: 1px solid rgba(99, 102, 241, 0.2);">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted fw-bold text-uppercase mb-0" style="font-size: 0.7rem; letter-spacing: 0.08em; opacity: 0.8;">Total Reviews</div>
                    <h4 class="fw-bold m-0 text-primary" id="totalReviewsCount" style="font-size: 1.5rem; letter-spacing: -0.02em;">0</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="premium-stat-card d-block p-0 overflow-hidden shadow-sm border rounded-4 bg-white mb-4 animate-fade-up">
        <div class="table-responsive p-3 d-none d-md-block">
            <table id="reviewsTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr class="text-uppercase small fw-bold text-muted border-0">
                        <th class="ps-4">#</th>
                        <th>Property</th>
                        <th>Tenant</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables dynamic -->
                </tbody>
            </table>
        </div>

        <!-- Mobile Grid -->
        <div id="reviews-grid" class="d-md-none p-3 row g-3">
             <div class="col-12 text-center py-5 text-muted opacity-50">
                <i class="fa-solid ui-skeleton ui-skeleton-line mb-2"></i>
                <p class="small">Loading Reviews...</p>
            </div>
        </div>
    </div>

    <!-- DataTable Controls Container (Bottom) -->
    <div id="reviews-bottom-controls" class="mt-4"></div>
</div>

<!-- View Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-primary py-4 px-4 rounded-top-4 text-white">
                <div>
                    <h5 class="modal-title fw-bold" id="commentModalLabel">Review Content</h5>
                    <p class="text-white-50 mb-0 small" id="mUserSub">Posted by User Name</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="mb-3 d-flex align-items-center justify-content-between">
                    <div id="mRatingStars" class="text-warning"></div>
                    <span class="text-muted small" id="mDate">Date</span>
                </div>
                <div class="p-3 bg-light rounded-4 border">
                    <p class="mb-0 text-dark" id="mCommentText" style="line-height: 1.6;"></p>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4 border shadow-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.transition-all { transition: all 0.2s ease-in-out; }
.action-btn { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.action-btn:hover { box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
#reviewsTable_wrapper .dataTables_filter input {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 0.5rem 1rem;
    width: 250px;
}
#reviewsTable_wrapper .dataTables_length select {
    border-radius: 10px;
}
</style>

<script>
let reviewsTable;
let currentReviewsData = {};

$(document).ready(function() {
    reviewsTable = $('#reviewsTable').DataTable({
        ajax: {
            url: '/tenant/?url=admin/get_reviews_json',
            type: 'POST',
            data: { csrf_token: '<?= htmlspecialchars(Csrf::token()) ?>' },
            dataType: 'json',
            dataSrc: function(json) {
                $('#totalReviewsCount').text(json.data.length);
                return json.data;
            }
        },
        autoWidth: false,
        pageLength: 10,
        dom: 'rtip', // Controls moved manually
        language: {
            lengthMenu: "Show _MENU_ reviews",
            search: "",
            searchPlaceholder: "Search reviews...",
            info: "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ reviews</span>",
            paginate: { 
                previous: "<i class='fa-solid fa-chevron-left'></i>", 
                next: "<i class='fa-solid fa-chevron-right'></i>" 
            }
        },
        order: [[0, 'desc']],
        columns: [
            { 
                data: 'id',
                className: 'px-4 text-center text-muted small',
                render: (data) => `#${data}`
            },
            { 
                data: 'boarding_house_name',
                className: 'px-4 fw-bold text-dark'
            },
            { 
                data: 'user_name',
                className: 'px-4'
            },
            { 
                data: 'rating',
                className: 'px-4',
                render: function(data) {
                    let stars = '';
                    for(let i=1; i<=5; i++) {
                        stars += `<i class="fa-solid fa-star ${i <= data ? 'text-warning' : 'text-secondary opacity-25'} small"></i>`;
                    }
                    return `<div class="d-flex gap-1">${stars}</div>`;
                }
            },
            { 
                data: 'comment',
                className: 'px-4',
                render: function(data, type, row) {
                    currentReviewsData[row.id] = row;
                    const truncated = data.length > 50 ? data.substring(0, 50) + '...' : data;
                    return `
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary small cursor-pointer" onclick="viewComment(${row.id})">${truncated}</span>
                            ${data.length > 50 ? `<i class="fa-solid fa-circle-info text-primary opacity-50 cursor-pointer" onclick="viewComment(${row.id})"></i>` : ''}
                        </div>
                    `;
                }
            },
            { 
                data: 'status',
                className: 'px-4',
                render: function(data) {
                    let badgeClass = 'bg-warning';
                    if (data === 'approved') badgeClass = 'bg-success';
                    if (data === 'rejected') badgeClass = 'bg-danger';
                    return `<span class="badge ${badgeClass} bg-opacity-10 text-${data === 'pending' ? 'warning text-dark' : (data === 'approved' ? 'success' : 'danger')} rounded-pill px-3 py-2 text-capitalize badge-fixed-width" style="font-size: 0.7rem;"><i class="fa-solid ${data === 'approved' ? 'fa-circle-check' : (data === 'rejected' ? 'fa-circle-xmark' : 'fa-clock')} me-1"></i>${data.toUpperCase()}</span>`;
                }
            },
            { 
                data: null,
                className: 'px-4 text-end',
                orderable: false,
                render: function(data, type, row) {
                    let actionHtml = '';
                    if (row.status === 'pending') {
                        actionHtml += `
                            <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all action-btn" 
                                    style="background: #f0fdf4; color: #15803d; border: 1.5px solid #22c55e; font-size: 0.7rem;"
                                    onclick="updateStatus(${row.id}, 'approved')" title="Approve">
                                <i class="fa-solid fa-check me-1"></i>APPROVE
                            </button>
                            <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all action-btn" 
                                    style="background: #fff1f2; color: #be123c; border: 1.5px solid #f43f5e; font-size: 0.7rem;"
                                    onclick="updateStatus(${row.id}, 'rejected')" title="Reject">
                                <i class="fa-solid fa-xmark me-1"></i>REJECT
                            </button>
                        `;
                    } else if (row.status === 'approved') {
                        actionHtml += `
                            <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all action-btn" 
                                    style="background: #fff1f2; color: #be123c; border: 1.5px solid #f43f5e; font-size: 0.7rem;"
                                    onclick="updateStatus(${row.id}, 'rejected')" title="Reject">
                                <i class="fa-solid fa-xmark me-1"></i>REJECT
                            </button>
                        `;
                    } else {
                        actionHtml += `
                            <button class="btn btn-sm rounded-pill px-3 fw-bold transition-all action-btn" 
                                    style="background: #f0fdf4; color: #15803d; border: 1.5px solid #22c55e; font-size: 0.7rem;"
                                    onclick="updateStatus(${row.id}, 'approved')" title="Approve">
                                <i class="fa-solid fa-check me-1"></i>APPROVE
                            </button>
                        `;
                    }
                    actionHtml += `
                        <button class="btn btn-sm rounded-pill px-2 fw-bold transition-all action-btn ms-1" 
                                style="background: #fef2f2; color: #b91c1c; border: 1.5px solid #ef4444; font-size: 0.7rem;"
                                onclick="deleteReview(${row.id})" title="Delete">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    `;
                    return `<div class="d-flex justify-content-end gap-1">${actionHtml}</div>`;
                }
            }
        ],
        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const $grid = $('#reviews-grid');
            
            // Generate Mobile Cards
            $grid.empty();
            if (data.length === 0) {
                $grid.html('<div class="col-12 text-center py-5 text-muted opacity-50"><i class="fa-solid fa-comments fa-2x mb-2 d-block"></i>No reviews found.</div>');
            } else {
                data.each(function(row) {
                    const initials = row.user_name ? row.user_name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase() : 'U';
                    const status = (row.status || 'pending').toLowerCase();
                    const truncated = row.comment && row.comment.length > 50 ? row.comment.substring(0, 50) + '...' : (row.comment || '');
                    
                    let badgeClass = 'bg-secondary';
                    let borderColor = '#e2e8f0';
                    let icon = 'fa-clock';
                    if (status === 'approved') { badgeClass = 'bg-success'; borderColor = '#10b981'; icon = 'fa-circle-check'; } 
                    else if (status === 'pending') { badgeClass = 'bg-warning text-dark'; borderColor = '#f59e0b'; }
                    else if (status === 'rejected') { badgeClass = 'bg-danger'; borderColor = '#ef4444'; icon = 'fa-circle-xmark'; }

                    let stars = '';
                    for(let i=1; i<=5; i++) {
                        stars += `<i class="fa-solid fa-star ${i <= row.rating ? 'text-warning' : 'text-secondary opacity-25'} small"></i>`;
                    }

                    let actionHtml = '';
                    if (status === 'pending') {
                        actionHtml = `
                            <div class="mt-3 pt-3 border-top d-flex gap-2">
                                <button class="btn btn-sm btn-outline-success fw-bold flex-grow-1 rounded-pill" onclick="updateStatus(${row.id}, 'approved')"><i class="fa-solid fa-check me-1"></i>APPROVE</button>
                                <button class="btn btn-sm btn-outline-danger fw-bold flex-grow-1 rounded-pill" onclick="updateStatus(${row.id}, 'rejected')"><i class="fa-solid fa-xmark me-1"></i>REJECT</button>
                            </div>
                        `;
                    } else if (status === 'approved') {
                        actionHtml = `
                            <div class="mt-3 pt-3 border-top d-flex gap-2">
                                <button class="btn btn-sm btn-outline-danger fw-bold flex-grow-1 rounded-pill" onclick="updateStatus(${row.id}, 'rejected')"><i class="fa-solid fa-xmark me-1"></i>REJECT</button>
                            </div>
                        `;
                    } else {
                        actionHtml = `
                            <div class="mt-3 pt-3 border-top d-flex gap-2">
                                <button class="btn btn-sm btn-outline-success fw-bold flex-grow-1 rounded-pill" onclick="updateStatus(${row.id}, 'approved')"><i class="fa-solid fa-check me-1"></i>APPROVE</button>
                            </div>
                        `;
                    }

                    const card = `
                        <div class="col-12">
                            <div class="premium-stat-card p-4 shadow-sm border-0 border-start border-4 position-relative bg-white rounded-4 overflow-hidden text-start" style="border-left-color: ${borderColor} !important;">
                                <!-- Top Row: Property & Status -->
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fw-900 text-dark fs-6 text-truncate pe-2"><i class="fa-solid fa-house-chimney text-primary opacity-50 me-2"></i>${row.boarding_house_name}</div>
                                    <span class="badge rounded-pill ${badgeClass} px-3 py-2 fw-800 shadow-sm badge-fixed-width" style="font-size: 0.65rem;">
                                        <i class="fa-solid ${icon} me-1"></i>
                                        ${status.toUpperCase()}
                                    </span>
                                </div>

                                <!-- User & Rating -->
                                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-4 bg-light bg-opacity-50 border">
                                    <div class="owner-avatar-sm">${initials}</div>
                                    <div class="overflow-hidden w-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-800 text-dark text-truncate">${row.user_name}</div>
                                            <div class="d-flex gap-1">${stars}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Comment -->
                                <div class="text-secondary small cursor-pointer p-2 rounded-3 bg-light border opacity-75" onclick="viewComment(${row.id})" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-quote-left opacity-25 me-1"></i>
                                    ${truncated}
                                </div>

                                ${actionHtml}
                                
                                <div class="mt-2 text-end">
                                    <button class="btn btn-sm btn-link text-danger p-0 fw-bold text-decoration-none" style="font-size: 0.7rem;" onclick="deleteReview(${row.id})">
                                        <i class="fa-solid fa-trash-can"></i> DELETE
                                    </button>
                                </div>
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

            if ($length.length) $('#reviews-top-controls').append($length);
            if ($filter.length) $('#reviews-top-controls').append($filter);
            if ($info.length) $('#reviews-bottom-controls').append($info);
            if ($paginate.length) $('#reviews-bottom-controls').append($paginate);

            // Pagination Styling
            $('.pagination').addClass('pagination-rounded gap-1');
            $('.page-link').addClass('rounded-3 border-0 shadow-none');
            if ($(window).width() < 576) {
                $('.pagination').addClass('justify-content-center mt-3');
            }
        }
    });
});

function refreshData() {
    const btn = $('#refreshBtn');
    const icon = $('#refreshIcon');
    const text = $('#refreshText');
    
    // UI Feedback
    btn.addClass('disabled').css('opacity', '0.7');
    icon.addClass('fa-spin');
    text.text('SYNCING...');
    
    // Reload Table
    if (reviewsTable) {
        reviewsTable.ajax.reload(function() {
            // Restore UI after slight delay for visual effect
            setTimeout(() => {
                btn.removeClass('disabled').css('opacity', '1');
                icon.removeClass('fa-spin');
                text.text('REFRESH');
                
                // Optional Toast
                Feedback.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Data Synchronized',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
            }, 500);
        }, false);
    }
}

function viewComment(id) {
    const data = currentReviewsData[id];
    if (!data) return;
    
    $('#mUserSub').text(`Posted by ${data.user_name} for ${data.boarding_house_name}`);
    $('#mDate').text(new Date(data.created_at).toLocaleDateString(undefined, { dateStyle: 'long', timeStyle: 'short' }));
    $('#mCommentText').text(data.comment);
    
    let stars = '';
    for(let i=1; i<=5; i++) {
        stars += `<i class="fa-solid fa-star ${i <= data.rating ? 'text-warning' : 'text-secondary opacity-25'}"></i> `;
    }
    $('#mRatingStars').html(stars);
    
    new bootstrap.Modal(document.getElementById('commentModal')).show();
}

function updateStatus(id, status) {
    const title = status === 'approved' ? 'Approve Review?' : 'Reject Review?';
    const confirmText = status === 'approved' ? 'Yes, approve it' : 'Yes, reject it';
    
    Feedback.fire({
        title: title,
        text: `Are you sure you want to set this review status to ${status}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'approved' ? '#22c55e' : '#f43f5e',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        rounded: 'pill'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/tenant/?url=admin/update_review_status', {
                review_id: id,
                status: status,
                csrf_token: '<?= htmlspecialchars(Csrf::token()) ?>'
            }, function(res) {
                if (res.success) {
                    Feedback.fire({ icon: 'success', title: 'Updated!', text: res.message, timer: 1500, showConfirmButton: false });
                    reviewsTable.ajax.reload(null, false);
                } else {
                    Feedback.fire('Error', res.message, 'error');
                }
            }, 'json');
        }
    });
}

function deleteReview(id) {
    Feedback.fire({
        title: 'Delete Review?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/tenant/?url=admin/delete_review', {
                review_id: id,
                csrf_token: '<?= htmlspecialchars(Csrf::token()) ?>'
            }, function(res) {
                if (res.success) {
                    Feedback.fire({ icon: 'success', title: 'Deleted!', text: res.message, timer: 1500, showConfirmButton: false });
                    reviewsTable.ajax.reload(null, false);
                } else {
                    Feedback.fire('Error', res.message, 'error');
                }
            }, 'json');
        }
    });
}
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
