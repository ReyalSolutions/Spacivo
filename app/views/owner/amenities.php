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
    color: #475569 !important;
    font-weight: 700 !important;
    margin-bottom: 0 !important;
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
.pagination-rounded .page-item.active .page-link { background: #6366f1 !important; color: white !important; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

.transition-all { transition: all 0.2s ease-in-out; }
.icon-preview { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #e0e7ff; color: #4f46e5; border-radius: 10px; font-size: 1.25rem; }
</style>
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border">
        <div>
            <h3 class="m-0 fw-bold fs-5 text-gradient-primary">Amenities Directory</h3>
            <p class="text-muted small mt-1 mb-0">Browse and manage the global property amenities list.</p>
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
                        <th>Added On</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($amenities)): ?>
                        <?php foreach($amenities as $amenity): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="icon-preview shadow-sm border">
                                    <i class="fa-solid <?= htmlspecialchars($amenity['icon'] ?: 'fa-check') ?>"></i>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($amenity['name']) ?></div>
                                <div class="text-muted small" style="font-family: monospace;">fa-solid <?= htmlspecialchars($amenity['icon']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border px-2 py-1">
                                    <i class="fa-regular fa-calendar me-1"></i> <?= date('M d, Y', strtotime($amenity['created_at'])) ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border text-primary rounded-circle shadow-sm me-1" 
                                        onclick="editAmenity(<?= htmlspecialchars(json_encode($amenity)) ?>)" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-light border text-danger rounded-circle shadow-sm" 
                                        onclick="deleteAmenity(<?= $amenity['id'] ?>)" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-couch fs-1 mb-3 opacity-50"></i>
                                <h5>No amenities found</h5>
                                <p class="small mb-0">Add standard amenities like WiFi, Aircon, or Parking.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
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
            
            <form id="amenityForm" method="POST" action="/tenant/?url=owner/store_amenity">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <input type="hidden" id="amenityId" name="id" value="">
                
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small">Amenity Name</label>
                        <input type="text" class="form-control rounded-3 py-2" id="amenityName" name="name" required placeholder="e.g. WiFi, Full Aircon, Private Pool">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-semibold small">FontAwesome Icon Class</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">fa-solid</span>
                            <input type="text" class="form-control rounded-end-3 py-2 border-start-0 ps-0" id="amenityIcon" name="icon" required placeholder="fa-wifi">
                            <button class="btn btn-outline-secondary px-3" type="button" onclick="previewIcon()">
                                <i class="fa-solid fa-eye"></i> Preview
                            </button>
                        </div>
                        <div class="form-text small mt-2">
                            Type a FontAwesome v6 solid icon class. Ex: <code>fa-wifi</code>, <code>fa-tv</code>, <code>fa-car</code>.
                        </div>
                        <div class="mt-3 text-center border rounded-3 p-3 bg-light d-none" id="iconPreviewContainer">
                            <p class="small text-muted mb-2 fw-bold">Live Preview</p>
                            <i id="liveIconPreview" class="fa-solid fa-check fs-1 text-primary"></i>
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

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="/tenant/?url=owner/delete_amenity" class="d-none">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($amenities)): ?>
    const table = $('#amenitiesTable').DataTable({
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
        "dom": '<"top">rt<"bottom"ilp><"clear">',
        "drawCallback": function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded justify-content-center mt-3');
        }
    });

    // Move controls perfectly
    $('#amenitiesTable_length, #amenitiesTable_filter').appendTo('#amenities-top-controls');
    $('#amenitiesTable_info, #amenitiesTable_paginate').appendTo('#amenities-bottom-controls');
    <?php endif; ?>

    // Live preview icon on type
    document.getElementById('amenityIcon').addEventListener('keyup', previewIcon);
});

const amenityModal = new bootstrap.Modal(document.getElementById('amenityModal'));
const form = document.getElementById('amenityForm');

function openAmenityModal() {
    form.reset();
    document.getElementById('amenityId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Amenity';
    document.getElementById('modalDesc').textContent = 'Enter the details for the new global amenity.';
    form.action = '/tenant/?url=owner/store_amenity';
    document.getElementById('iconPreviewContainer').classList.add('d-none');
    amenityModal.show();
}

function editAmenity(data) {
    document.getElementById('amenityId').value = data.id;
    document.getElementById('amenityName').value = data.name;
    document.getElementById('amenityIcon').value = data.icon;
    
    document.getElementById('modalTitle').textContent = 'Edit Amenity';
    document.getElementById('modalDesc').textContent = 'Update the global amenity details.';
    form.action = '/tenant/?url=owner/update_amenity';
    
    previewIcon();
    amenityModal.show();
}

function deleteAmenity(id) {
    Feedback.fire({
        title: 'Delete Amenity?',
        text: "Are you sure? This cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}

function previewIcon() {
    const iconClass = document.getElementById('amenityIcon').value.trim() || 'fa-check';
    const previewContainer = document.getElementById('iconPreviewContainer');
    const liveIcon = document.getElementById('liveIconPreview');
    
    liveIcon.className = 'fa-solid ' + iconClass + ' fs-1 text-primary';
    previewContainer.classList.remove('d-none');
}
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
