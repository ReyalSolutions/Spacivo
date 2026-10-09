<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<div class="container-fluid px-4 py-4">
    <!-- Header & Filter Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold m-0 text-gradient-primary">Boarding Houses</h2>
            <p class="text-muted small mb-0"><?= $roleLabel === 'Admin' ? 'Manage all boarding house listings.' : 'Manage your own boarding house listings.' ?></p>
        </div>
        
        <div class="d-flex gap-2 align-items-center header-actions">
            <?php if ($this->hasPermission('add_houses')): ?>
            <button type="button" class="btn btn-primary" onclick="openAddModal()">Add property</button>
            <?php endif; ?>
            <?php if ($roleLabel === 'Admin'): ?>
                <div style="min-width: 180px;">
                    <div class="input-group input-group-sm rounded-pill border overflow-hidden">
                        <span class="input-group-text border-0 ps-3" style="background-color: #f8fafc;">
                            <i class="fa-solid fa-filter text-primary opacity-50 small"></i>
                        </span>
                        <select id="ownerFilterSelect" class="form-select border-0 ps-1 small fw-600" style="cursor: pointer; background-color: #f8fafc;">
                            <option value="">All Owners</option>
                            <?php foreach ($owners as $owner): ?>
                                <option value="<?= $owner['id'] ?>" <?= $ownerFilter == $owner['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($owner['first_name'] . ' ' . $owner['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border">
                <button type="button" data-filter="" class="filter-btn btn <?= !$filter ? 'btn-white shadow-sm' : 'btn-light text-muted' ?> rounded-pill px-3 fw-bold border-0">
                    All
                </button>
                <button type="button" data-filter="pending" class="filter-btn btn <?= $filter === 'pending' ? 'btn-white shadow-sm' : 'btn-light text-muted' ?> rounded-pill px-3 fw-bold border-0">
                    Pending
                </button>
            </div>
        </div>
    </div>

    <!-- New Dedicated Control Containers -->
    <div id="houses-controls-top" class="mb-4"></div>

    <!-- Properties Grid -->
    <div id="houses-grid" class="row g-4">
        <!-- Dynamically architected via AJAX Orchestration -->
        <div class="col-12 py-5 text-center">
            <div class="ui-skeleton ui-skeleton-line text-primary" role="status">
                <span class="visually-hidden">Loading properties...</span>
            </div>
            <p class="mt-2 text-muted">Intitializing property portfolio...</p>
        </div>
    </div>

    <div id="houses-controls-bottom" class="mt-4"></div>

    <!-- Hidden native table for DataTables logic -->
    <div style="display: none;">
        <table id="houses-table" class="table w-100">
            <thead>
                <tr><th>Card</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Template for No Results -->
<template id="no-results-template">
    <div class="col-12 py-5 text-center">
        <div class="premium-stat-card py-5 border-dashed border-2">
            <div class="mb-4">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-house-chimney-crack fs-1 text-muted opacity-25"></i>
                </div>
            </div>
            <h4 class="text-dark fw-bold">No Properties Found</h4>
            <p class="text-secondary mx-auto" style="max-width: 400px;">
                We couldn't find any boarding houses matching your current selection. Try adjusting your search or filters.
            </p>
            <button type="button" onclick="resetFilters()" class="btn btn-outline-primary rounded-pill px-5 mt-3">
                Reset All Filters
            </button>
        </div>
    </div>
</template>

<script>
$(document).ready(function() {
    let statusFilter = '<?= $filter ?? "" ?>';
    
    // Initialize DataTable
    const table = $('#houses-table').DataTable({
        processing: false, // We'll handle loading UI manually
        serverSide: true,
        ajax: {
            url: '/tenant/?url=admin/houses_data',
            data: function(d) {
                d.status_filter = statusFilter;
                d.owner_id = $('#ownerFilterSelect').val();
            }
        },
        columns: [
            { data: 0, orderable: false }
        ],
        pageLength: 9, // Optimal for 3-column grid
        lengthMenu: [6, 9, 12, 24],
        dom: '<"d-flex justify-content-between align-items-center mb-4"lf>rt<"d-flex justify-content-between align-items-center mt-4"ip>',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search properties...",
            paginate: {
                next: '<i class="fa-solid fa-chevron-right"></i>',
                previous: '<i class="fa-solid fa-chevron-left"></i>'
            }
        },
        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const $grid = $('#houses-grid');
            
            $grid.empty();
            
            if (data.length === 0) {
                $grid.append($('#no-results-template').html());
            } else {
                data.each(function(row) {
                    $grid.append(row[0]);
                });
            }

            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');

            // Move controls to their dedicated containers (Explicit order: Length, then Filter)
            const $length = $('.dataTables_length');
            const $filter = $('.dataTables_filter');
            $('#houses-controls-top').empty().append($length).append($filter);
            $('#houses-controls-bottom').append($('.dataTables_info, .dataTables_paginate'));
            
            // Re-style shifted elements for fluid layout
            $('#houses-controls-top').addClass('d-flex justify-content-between align-items-center mb-4 gap-3');
            $('#houses-controls-bottom').addClass('d-flex justify-content-between align-items-center mt-4 gap-3');
            
            // Re-initialize carousels for new elements (Bootstrap 5)
            const carousels = [].slice.call(document.querySelectorAll('.carousel'));
            carousels.map(function(c) { 
                return new bootstrap.Carousel(c, { interval: 5000, ride: 'carousel' }); 
            });

            // Re-initialize any bootstrap components if needed
            const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltips.map(function(t) { return new bootstrap.Tooltip(t); });
        }
    });

    // Custom Pagination Click Handling
    $(document).on('click', '#pagination-container .paginate_button', function(e) {
        e.preventDefault();
        if ($(this).hasClass('disabled') || $(this).hasClass('current')) return;
        
        if ($(this).hasClass('next')) {
            table.page('next').draw('page');
        } else if ($(this).hasClass('previous')) {
            table.page('prev').draw('page');
        } else {
            table.page(parseInt($(this).text()) - 1).draw('page');
        }
    });


    // Owner Filter
    $('#ownerFilterSelect').on('change', function() {
        table.ajax.reload();
    });

    // Status Filters
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        
        statusFilter = $(this).data('filter');
        table.ajax.reload();
        
        // Update URL state without reload (optional but professional)
        const newUrl = statusFilter ? `/tenant/?url=admin/houses/${statusFilter}` : '/tenant/?url=admin/houses';
        window.history.pushState({path: newUrl}, '', newUrl);
    });
});

function resetFilters() {
    $('#customSearchInput').val('');
    $('#ownerFilterSelect').val('');
    $('.filter-btn[data-filter=""]').click();
    $('#houses-table').DataTable().search('').ajax.reload();
}
</script>

<style>
.fw-extrabold { font-weight: 800; }
.extra-small { font-size: 0.75rem; }
.transition-hover { transition: all 0.3s ease; }
.transition-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
.border-dashed { border-style: dashed !important; }
.ripple-button { position: relative; overflow: hidden; }
.carousel-control-prev-icon, .carousel-control-next-icon {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
}

#pagination-container .pagination {
    margin-bottom: 0;
    gap: 5px;
}
#pagination-container .paginate_button {
    cursor: pointer;
    padding: 8px 16px;
    border-radius: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    font-weight: 600;
    transition: all 0.2s;
    user-select: none;
}
#pagination-container .paginate_button:hover:not(.disabled) {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #cbd5e1;
}
#pagination-container .paginate_button.current {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}
#pagination-container .paginate_button.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<style>
/* PREMIUM DATATABLE CONTROLS */
/* DataTables Premium Polish */
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
    width: 100% !important;
    max-width: 450px;
    margin-left: 0 !important;
}

/* Custom Select Arrow */
.dataTables_length select {
    min-width: 60px !important; /* Honoring user change */
    padding-right: 35px !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E") !important;
    background-size: 14px !important;
    background-position: right 14px center !important;
    background-repeat: no-repeat !important;
    cursor: pointer;
}

.dataTables_length select:hover, .dataTables_filter input:hover {
    background-color: #f1f5f9 !important;
    border-color: #94a3b8 !important;
}

.dataTables_length select:focus, .dataTables_filter input:focus {
    outline: none !important;
    border-color: #6366f1 !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
}

.dataTables_filter {
    flex-grow: 1;
}

.dataTables_length label, .dataTables_filter label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    white-space: nowrap !important;
    font-weight: 700 !important;
    color: #475569 !important;
    margin-bottom: 0;
}

.dataTables_info {
    font-weight: 700 !important;
    color: #64748b !important;
    font-size: 0.85rem;
}

.pagination-rounded .page-link {
    border-radius: 8px !important;
    margin: 0 3px;
    border: none !important;
    background: #f1f5f9;
    color: #64748b;
    padding: 8px 14px;
}

.pagination-rounded .page-item.active .page-link {
    background: #6366f1 !important;
    color: white !important;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

#houses-controls-top, #houses-controls-bottom {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
}

.dataTables_length {
    order: 1 !important; /* Left on desktop */
}

.dataTables_filter {
    order: 2 !important; /* Right on desktop */
    text-align: right !important;
}

@media (max-width: 576px) {
    .d-flex.flex-column.flex-md-row {
        gap: 15px !important;
    }
    .header-actions {
        flex-direction: column !important;
        width: 100% !important;
        align-items: stretch !important;
        gap: 10px !important;
    }
    .header-actions > div, .header-actions .btn-group {
        width: 100% !important;
    }
    .header-actions select {
        width: 100% !important;
    }
    #houses-controls-top {
        display: flex !important;
        flex-direction: column !important; /* Simple vertical stack */
        gap: 15px !important;
    }
    .dataTables_filter {
        order: 1 !important; /* Search on TOP for mobile */
        width: 100% !important;
        margin: 0 !important;
        text-align: left !important;
    }
    .dataTables_filter input {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }
    .dataTables_length {
        order: 2 !important; /* Entries BELOW search */
        width: 100% !important;
        justify-content: center !important;
        opacity: 0.8 !important;
    }
    .dataTables_length label {
        width: auto !important;
        font-size: 0.75rem !important;
        gap: 5px !important;
    }
    .dataTables_length select {
        padding: 4px 8px !important;
        font-size: 0.75rem !important;
        height: auto !important;
    }
}

/* CARDS STYLING OVERRIDES */
.premium-stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.premium-stat-card:hover {
    transform: translateY(-8px);
}
</style>

<script>
function moderateListing(id, action) {
    $.ajax({url:'/tenant/?url=admin/' + action + '_house',method:'POST',dataType:'json',data:{house_id:id,csrf_token:$('meta[name="csrf-token"]').attr('content')}})
        .done(function(result){if(result.success){ToastStack.success(result.message || 'Property status updated.');$('#houses-table').DataTable().ajax.reload(null,false);}else{Feedback.fire('Unable to update',result.message,'error');}})
        .fail(function(){Feedback.fire('Unable to update','Permission or security validation failed.','error');});
}
</script>
<?php require __DIR__ . '/../components/listing_controls.php'; require __DIR__ . '/../layouts/management_footer.php'; ?>

