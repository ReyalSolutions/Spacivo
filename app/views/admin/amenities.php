<?php 
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/admin_header.php'; 
?>
<style>
/* Premium Stat Card Styling */
.premium-stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
}
.premium-stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.1) !important; }

/* DataTable Standardized Controls */
.dataTables_length select, .dataTables_filter input {
    border: 1px solid #cbd5e1 !important;
    border-radius: 14px !important;
    padding: 10px 16px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    background-color: #f1f5f9 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
}

.dataTables_length label, .dataTables_filter label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    white-space: nowrap !important;
    color: #0f172a !important; /* Premium dark slate */
    font-weight: 800 !important;
    margin-bottom: 0 !important;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.025em;
}

#amenities-top-controls, #amenities-bottom-controls {
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
    #amenities-top-controls {
        flex-direction: column !important;
        gap: 15px !important;
    }
    .dataTables_filter { order: 1 !important; width: 100% !important; text-align: left !important; }
    .dataTables_filter input { width: 100% !important; margin-left: 0 !important; }
    .dataTables_length { order: 2 !important; width: 100% !important; justify-content: center !important; }
}

.pagination-rounded .page-link { border-radius: 8px !important; margin: 0 3px; border: none !important; background: #f1f5f9; color: #64748b; padding: 8px 14px; }
.pagination-rounded .page-item.active .page-link { background: #2563eb !important; color: white !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }

.transition-all { transition: all 0.2s ease-in-out; }
.icon-preview { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #dbeafe; color: #2563eb; border-radius: 10px; font-size: 1.25rem; }

.text-gradient-primary {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Icon Selector Styles */
.icon-selector-grid .icon-option {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    color: #64748b;
    font-size: 1.1rem;
}
.icon-selector-grid .icon-option:hover {
    border-color: #2563eb;
    color: #2563eb;
    background: #eff6ff;
    transform: translateY(-3px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.icon-selector-grid .icon-option.active {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}
.icon-selector-grid {
    max-height: 220px;
    overflow-y: auto;
    scrollbar-width: thin;
}
.icon-selector-grid::-webkit-scrollbar {
    width: 6px;
}
.icon-selector-grid::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border">
        <div>
            <h3 class="m-0 fw-bold fs-5 text-gradient-primary">Amenities Dictionary</h3>
            <p class="text-muted small mt-1 mb-0">Manage global amenities available for boarding houses.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 header-actions">
            <button class="btn btn-primary rounded-pill px-3 shadow-sm fw-bold d-inline-flex align-items-center gap-2 btn-sm" onclick="openAmenityModal()">
                <i class="fa-solid fa-plus"></i>
                <span>ADD AMENITY</span>
            </button>
        </div>
    </div>

    <!-- DataTable Controls Container (Top) -->
    <div id="amenities-top-controls" class="mb-4"></div>

    <!-- Table Wrapper -->
    <div class="premium-stat-card d-block p-0 overflow-hidden shadow-sm border rounded-4 bg-white mb-4 animate-fade-up">
        <div class="table-responsive p-3">
            <table id="amenitiesTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr class="text-uppercase small fw-bold text-muted">
                        <th class="ps-4">Icon</th>
                        <th>Amenity Name</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- DataTable Controls Container (Bottom) -->
    <div id="amenities-bottom-controls" class="mt-4"></div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="amenityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-light py-4 px-4 rounded-top-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="modalTitle">Add Amenity</h5>
                    <p class="text-muted mb-0 small" id="modalDesc">Enter the amenity details below.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="amenityForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <input type="hidden" id="amenityId" name="id" value="">
                
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small">Amenity Name</label>
                        <input type="text" class="form-control rounded-3 py-2" id="amenityName" name="name" required placeholder="e.g. WiFi, Full Aircon, Private Pool">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small">Choose an Icon</label>
                        <div class="icon-selector-grid d-flex flex-wrap gap-2 p-3 bg-light rounded-4 border mb-3">
                            <?php 
                            $commonIcons = [
                                'fa-wifi', 'fa-tv', 'fa-wind', 'fa-snowflake', 'fa-fan', 
                                'fa-car', 'fa-square-parking', 'fa-water', 'fa-bolt', 'fa-plug',
                                'fa-utensils', 'fa-mug-hot', 'fa-fire-burner', 'fa-kitchen-set',
                                'fa-sink', 'fa-shower', 'fa-faucet', 'fa-soap', 'fa-toilet',
                                'fa-bed', 'fa-couch', 'fa-door-open', 'fa-house-lock', 'fa-key',
                                'fa-shield-halved', 'fa-camera', 'fa-vault', 'fa-briefcase-medical',
                                'fa-shirt', 'fa-tshirt', 'fa-broom', 'fa-trash-can',
                                'fa-dumbbell', 'fa-swimming-pool', 'fa-bicycle', 'fa-elevator',
                                'fa-stairs', 'fa-building', 'fa-computer', 'fa-print', 'fa-phone',
                                'fa-clock', 'fa-calendar-days', 'fa-map-location-dot', 'fa-bullhorn'
                            ];
                            foreach($commonIcons as $icon): ?>
                                <div class="icon-option" data-icon="<?= $icon ?>" onclick="selectIcon('<?= $icon ?>')" title="<?= str_replace('fa-', '', $icon) ?>">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <label class="form-label text-secondary fw-semibold small">Custom FontAwesome Class</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">fa-solid</span>
                            <input type="text" class="form-control rounded-end-3 py-2 border-start-0 ps-0" id="amenityIcon" name="icon" required placeholder="fa-wifi">
                        </div>
                        <div class="form-text small mt-2">
                            Click an icon above or type a custom class (e.g. <code>fa-car</code>).
                        </div>
                        <div class="mt-3 text-center border rounded-3 p-3 bg-light d-none" id="iconPreviewContainer">
                            <p class="small text-muted mb-2 fw-bold">Selection Preview</p>
                            <div class="icon-preview mx-auto shadow-sm border" style="width: 60px; height: 60px; font-size: 2rem;">
                                <i id="liveIconPreview" class="fa-solid fa-check text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 px-4 pb-4 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveBtn">Save Amenity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let amenityModal;
let form;
let table;

document.addEventListener('DOMContentLoaded', function() {
    amenityModal = new bootstrap.Modal(document.getElementById('amenityModal'));
    form = document.getElementById('amenityForm');

    table = $('#amenitiesTable').DataTable({
        "ajax": "/tenant/?url=admin/amenities_data",
        "columns": [
            { 
                "data": "icon",
                "width": "80px",
                "render": function(data) {
                    return `<div class="icon-preview shadow-sm border"><i class="fa-solid ${data || 'fa-check'}"></i></div>`;
                }
            },
            { 
                "data": "name",
                "render": function(data, type, row) {
                    return `<div><div class="fw-bold text-dark fs-6">${data}</div><div class="text-muted small" style="font-family: monospace;">fa-solid ${row.icon}</div></div>`;
                }
            },
            { 
                "data": "created_at",
                "render": function(data) {
                    const date = new Date(data);
                    const formattedDate = date.toLocaleDateString('en-US', {month: 'short', day: '2-digit', year: 'numeric'});
                    return `<span class="badge bg-light text-secondary border px-2 py-1"><i class="fa-regular fa-calendar me-1"></i> ${formattedDate}</span>`;
                }
            },
            {
                "data": null,
                "className": "text-end pe-4",
                "orderable": false,
                "render": function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-light border text-primary rounded-circle shadow-sm me-1 action-edit" data-id="${row.id}" title="Edit">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-light border text-danger rounded-circle shadow-sm action-delete" data-id="${row.id}" data-name="${row.name}" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        "order": [[1, "asc"]],
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "search": "",
            "searchPlaceholder": "Search amenities...",
            "lengthMenu": "Show _MENU_",
            "info": "Showing _START_ to _END_ of _TOTAL_ amenities",
            "paginate": {
                "previous": "<i class='fa-solid fa-chevron-left'></i>",
                "next": "<i class='fa-solid fa-chevron-right'></i>"
            }
        },
        "dom": '<"top"fl>rt<"bottom"ip><"clear">',
        "drawCallback": function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded justify-content-center mt-3');
        }
    });

    $('#amenitiesTable_length').appendTo('#amenities-top-controls');
    $('#amenitiesTable_filter').appendTo('#amenities-top-controls');
    $('#amenitiesTable_info, #amenitiesTable_paginate').appendTo('#amenities-bottom-controls');

    // Handle Form Submission via AJAX
    $(form).on('submit', function(e) {
        e.preventDefault();
        const id = $('#amenityId').val();
        const url = id ? '/tenant/?url=admin/update_amenity' : '/tenant/?url=admin/store_amenity';
        const formData = $(this).serialize();

        $('#saveBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    amenityModal.hide();
                    table.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            },
            complete: function() {
                $('#saveBtn').prop('disabled', false).text('Save Amenity');
            }
        });
    });

    // Delegate Edit/Delete buttons (since rows are dynamic)
    $('#amenitiesTable').on('click', '.action-edit', function() {
        const id = $(this).data('id');
        const rowData = table.row($(this).parents('tr')).data();
        editAmenity(rowData);
    });

    $('#amenitiesTable').on('click', '.action-delete', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        deleteAmenity(id, name);
    });

    const iconInput = document.getElementById('amenityIcon');
    if (iconInput) {
        iconInput.addEventListener('keyup', previewIcon);
    }
});

function openAmenityModal() {
    form.reset();
    document.getElementById('amenityId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Amenity';
    document.getElementById('modalDesc').textContent = 'Enter the details for the new global amenity.';
    document.getElementById('iconPreviewContainer').classList.add('d-none');
    amenityModal.show();
}

function editAmenity(data) {
    document.getElementById('amenityId').value = data.id;
    document.getElementById('amenityName').value = data.name;
    document.getElementById('amenityIcon').value = data.icon;
    
    document.getElementById('modalTitle').textContent = 'Edit Amenity';
    document.getElementById('modalDesc').textContent = 'Update the global amenity details.';
    
    previewIcon();
    amenityModal.show();
}

function deleteAmenity(id, name) {
    Swal.fire({
        title: 'Delete Amenity?',
        text: `Are you sure you want to delete "${name}"? This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/tenant/?url=admin/delete_amenity',
                type: 'POST',
                data: {
                    id: id,
                    csrf_token: $('input[name="csrf_token"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        Swal.fire('Failed', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                }
            });
        }
    });
}

function selectIcon(iconClass) {
    document.getElementById('amenityIcon').value = iconClass;
    
    // Update active state in grid
    document.querySelectorAll('.icon-option').forEach(el => {
        el.classList.remove('active');
        if (el.dataset.icon === iconClass) el.classList.add('active');
    });
    
    previewIcon();
}

function previewIcon() {
    const iconClass = document.getElementById('amenityIcon').value.trim() || 'fa-check';
    const previewContainer = document.getElementById('iconPreviewContainer');
    const liveIcon = document.getElementById('liveIconPreview');
    
    liveIcon.className = 'fa-solid ' + iconClass + ' text-primary';
    previewContainer.classList.remove('d-none');

    // Update grid selection if typed manually
    document.querySelectorAll('.icon-option').forEach(el => {
        el.classList.toggle('active', el.dataset.icon === iconClass);
    });
}
</script>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
