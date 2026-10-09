<!-- Modal Refinements -->
<div class="modal fade" id="houseModal" data-bs-backdrop="false" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-2xl border-0 overflow-hidden">
            <div class="modal-header border-0 bg-dark p-5 position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary opacity-10" style="filter: blur(40px);"></div>
                <div class="position-relative z-10 w-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h2 class="modal-title fw-900 text-white" id="modalTitle">Register New Property</h2>
                        <button type="button" class="btn btn-outline-light btn-sm rounded-circle p-2" data-bs-dismiss="modal" style="width:34px; height:34px;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <p class="text-white text-opacity-50 mb-0 fw-600">Complete the architectural meta-data for your property below.</p>
                </div>
            </div>
            <form id="houseForm" action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <input type="hidden" name="house_id" id="houseId">
                <div class="modal-body p-5 bg-white">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label text-dark fw-800 small uppercase letter-spacing-1">Property Name / Identity</label>
                            <input type="text" name="name" id="houseName" class="form-control form-control-lg border-2" placeholder="e.g. Skyline Residences Gold" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-dark fw-800 small uppercase letter-spacing-1">Narrative Geography (Address)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-2 px-3 text-muted"><i class="fa-solid fa-map-pin"></i></span>
                                <input type="text" name="address" id="houseAddress" class="form-control form-control-lg border-2 border-start-0" 
                                    placeholder="Street/Purok, Barangay, City, Province, Country" 
                                    required>
                            </div>
                            <div class="form-text small fw-600 text-muted mt-2">Format: street/purok, barangay, city, province, country (Must have 5 components)</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-dark fw-800 small uppercase letter-spacing-1">Marketing Description / Core Utility</label>
                            <textarea name="description" id="houseDesc" class="form-control border-2" rows="4" placeholder="Highlight unique utilities: CCTV, Bio-metric access, High-speed fiber, etc."></textarea>
                        </div>
                        <input type="hidden" name="latitude" id="houseLat">
                        <input type="hidden" name="longitude" id="houseLng">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-4 px-5 justify-content-between">
                    <div class="text-muted small fw-600 d-none d-md-block">
                        <i class="fa-solid fa-shield-halved me-1"></i> Data protected by encryption layer
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-800" data-bs-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-900 shadow-lg" id="submitBtn">INITIATE SUBMISSION</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Management Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" data-bs-backdrop="false" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 overflow-hidden" style="border-radius:28px;">
            <div class="modal-header border-0 p-4" style="background:linear-gradient(135deg,#0f172a,#1e40af);">
                <div>
                    <h5 class="modal-title fw-900 text-white mb-0" id="imgModalTitle">Property Photos</h5>
                    <p class="text-white text-opacity-50 small mb-0">Up to 10 images. JPG, PNG, WebP, GIF · Max 5MB each.</p>
                </div>
                <button type="button" class="btn btn-outline-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" style="width:32px;height:32px;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body p-4">

                <!-- Drop zone -->
                <div id="dropZone" onclick="document.getElementById('imageFileInput').click()"
                    style="border:2.5px dashed #c7d2fe;border-radius:20px;padding:36px;text-align:center;cursor:pointer;background:#f8faff;transition:all .2s;"
                    ondragover="this.style.borderColor='#4f46e5';this.style.background='#eef2ff';event.preventDefault();"
                    ondragleave="this.style.borderColor='#c7d2fe';this.style.background='#f8faff';"
                    ondrop="handleDrop(event)">
                    <i class="fa-solid fa-cloud-arrow-up fa-2x text-primary mb-2"></i>
                    <div class="fw-700 text-dark">Click or drag &amp; drop images here</div>
                    <div class="text-muted small mt-1">Select up to <span id="remainingSlots">10</span> more images</div>
                </div>
                <input type="file" id="imageFileInput" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;" onchange="previewFiles(this.files)">

                <!-- Upload progress -->
                <div id="uploadProgress" class="d-none mt-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="ui-skeleton ui-skeleton-line text-primary"></div>
                        <span class="small fw-600 text-primary" id="uploadProgressText">Uploading...</span>
                    </div>
                    <div class="progress" style="height:6px;border-radius:10px;">
                        <div id="uploadProgressBar" class="progress-bar bg-primary" style="width:0%;transition:width .3s;"></div>
                    </div>
                </div>

                <!-- Preview staged files -->
                <div id="stagedPreviews" class="d-none mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-800 text-dark">READY TO UPLOAD</span>
                        <button class="btn btn-primary btn-sm rounded-pill px-4 fw-700" onclick="commitUpload()">
                            <i class="fa-solid fa-upload me-1"></i>UPLOAD NOW
                        </button>
                    </div>
                    <div class="row g-2" id="stagedGrid"></div>
                </div>

                <!-- Existing images -->
                <div id="existingImagesSection" class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-800 text-dark">CURRENT PHOTOS <span id="imgCount" class="badge bg-primary rounded-pill">0</span></span>
                    </div>
                    <div class="row g-2" id="existingImagesGrid">
                        <div class="col-12 text-center text-muted py-4" id="noImagesMsg">
                            <i class="fa-solid fa-image fa-2x mb-2 d-block opacity-40"></i>
                            No photos yet. Upload some above!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Amenities Modal -->
<div class="modal fade" id="amenitiesModal" data-bs-backdrop="false" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0 overflow-hidden">
            <div class="modal-header border-0 bg-dark p-4 position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-warning opacity-10" style="filter: blur(30px);"></div>
                <div class="position-relative z-10 w-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h4 class="modal-title fw-900 text-white" id="amenitiesModalTitle">Manage Amenities</h4>
                        <button type="button" class="btn btn-outline-light btn-sm rounded-circle p-2" data-bs-dismiss="modal" style="width:34px; height:34px;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-body p-4 bg-light">
                <div id="amenitiesLoader" class="text-center py-4">
                    <div class="ui-skeleton ui-skeleton-line text-warning mb-2" role="status"></div>
                    <p class="text-muted small fw-bold">Loading amenities...</p>
                </div>
                <form id="amenitiesForm" class="d-none">
                    <input type="hidden" id="amenityHouseId">
                    <div class="row g-3" id="amenitiesGrid">
                        <!-- Checkboxes go here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 bg-white position-relative">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-800 shadow-sm" data-bs-dismiss="modal">CANCEL</button>
                <button type="button" class="btn btn-warning rounded-pill px-5 fw-900 shadow-lg text-dark" id="saveAmenitiesBtn" onclick="saveAmenities()">SAVE AMENITIES</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form id="deleteForm" method="POST" action="/tenant/?url=admin/delete_house">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <input type="hidden" name="house_id" id="deleteHouseId">
</form>

<script>
const CSRF_TOKEN = '<?= htmlspecialchars(Csrf::token()) ?>';
let currentImgHouseId = null;
let stagedFiles = [];

document.getElementById('houseForm').onsubmit = function(e) {
    const address = document.getElementById('houseAddress').value;
    const parts = address.split(',').map(p => p.trim());
    
    if (parts.length !== 5 || parts.some(p => p === '')) {
        e.preventDefault();
        Feedback.fire({
            title: 'Invalid Address Format',
            text: 'Please follow the format: Street/Purok, Barangay, City, Province, Country (5 components separated by commas)',
            icon: 'error',
            confirmButtonColor: '#4f46e5'
        });
        return false;
    }
    
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<span class="ui-skeleton ui-skeleton-line me-2"></span>SUBMITTING...';
};



function openEditModal(house) {
    document.getElementById('houseForm').action = '/tenant/?url=admin/update_house';
    document.getElementById('modalTitle').innerText = 'Refine Asset Meta-data';
    document.getElementById('houseId').value = house.id;
    document.getElementById('houseName').value = house.name;
    document.getElementById('houseAddress').value = house.address;
    document.getElementById('houseDesc').value = house.description;
    document.getElementById('houseLat').value = house.latitude;
    document.getElementById('houseLng').value = house.longitude;
    document.getElementById('submitBtn').disabled = false;
    document.getElementById('submitBtn').innerText = 'UPDATE ARCHIVE';
    new bootstrap.Modal(document.getElementById('houseModal')).show();
}

/* ─── IMAGE MANAGEMENT ─── */
function openImageModal(houseId, houseName) {
    currentImgHouseId = houseId;
    stagedFiles = [];
    document.getElementById('imgModalTitle').innerText = '📷 ' + houseName + ' — Photos';
    document.getElementById('stagedPreviews').classList.add('d-none');
    document.getElementById('stagedGrid').innerHTML = '';
    document.getElementById('uploadProgress').classList.add('d-none');
    document.getElementById('imageFileInput').value = '';
    loadExistingImages();
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

function loadExistingImages() {
    fetch('/tenant/?url=admin/get_house_images&house_id=' + currentImgHouseId)
        .then(r => r.json())
        .then(images => {
            const grid = document.getElementById('existingImagesGrid');
            const count = images.length;
            document.getElementById('imgCount').textContent = count;
            document.getElementById('remainingSlots').textContent = Math.max(0, 10 - count);

            if (count === 0) {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-4" id="noImagesMsg"><i class="fa-solid fa-image fa-2x mb-2 d-block opacity-40"></i>No photos yet. Upload some above!</div>';
            } else {
                grid.innerHTML = images.map(img => `
                    <div class="col-4" id="imgTile_${img.id}">
                        <div style="position:relative;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.08);">
                            <img src="${img.image_path}" alt="Photo" style="width:100%;height:130px;object-fit:cover;display:block;">
                            <button onclick="deleteImage(${img.id})" title="Remove"
                                style="position:absolute;top:6px;right:6px;background:#ef4444;color:white;border:none;border-radius:50%;width:28px;height:28px;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.2);">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            // Live-update the card hero image without page reload
            refreshCardHero(currentImgHouseId, images);
        });
}

/**
 * Refresh the property card hero carousel in-place (no page reload).
 * @param {number|string} houseId
 * @param {Array} images  — [{id, image_path, sort_order}, ...]
 */
function refreshCardHero(houseId, images) {
    if ($.fn.dataTable.isDataTable('#houses-table')) $('#houses-table').DataTable().ajax.reload(null, false);
}

function handleDrop(event) {
    event.preventDefault();
    const dz = document.getElementById('dropZone');
    dz.style.borderColor = '#c7d2fe'; dz.style.background = '#f8faff';
    previewFiles(event.dataTransfer.files);
}

function previewFiles(fileList) {
    const remaining = parseInt(document.getElementById('remainingSlots').textContent);
    const files = Array.from(fileList).filter(f => f.type.startsWith('image/')).slice(0, remaining);
    if (!files.length) return;

    stagedFiles = files;
    const grid = document.getElementById('stagedGrid');
    grid.innerHTML = files.map((f, i) => {
        const url = URL.createObjectURL(f);
        return `<div class="col-4">
            <div style="position:relative;border-radius:14px;overflow:hidden;border:2px solid #c7d2fe;">
                <img src="${url}" style="width:100%;height:110px;object-fit:cover;display:block;">
                <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.5);color:white;font-size:.65rem;padding:3px 6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${f.name}</div>
            </div>
        </div>`;
    }).join('');
    document.getElementById('stagedPreviews').classList.remove('d-none');
}

function commitUpload() {
    if (!stagedFiles.length) return;
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('house_id', currentImgHouseId);
    stagedFiles.forEach(f => formData.append('images[]', f));

    const prog = document.getElementById('uploadProgress');
    const bar = document.getElementById('uploadProgressBar');
    const txt = document.getElementById('uploadProgressText');
    prog.classList.remove('d-none');
    bar.style.width = '10%';
    txt.textContent = 'Uploading ' + stagedFiles.length + ' image(s)...';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/tenant/?url=admin/upload_house_images');
    xhr.upload.onprogress = e => {
        if (e.lengthComputable) bar.style.width = Math.round((e.loaded / e.total) * 90) + '%';
    };
    xhr.onload = () => {
        bar.style.width = '100%';
        try {
            const res = JSON.parse(xhr.responseText);
            setTimeout(() => {
                prog.classList.add('d-none');
                bar.style.width = '0';
            }, 600);
            if (res.success) {
                stagedFiles = [];
                document.getElementById('stagedPreviews').classList.add('d-none');
                document.getElementById('stagedGrid').innerHTML = '';
                document.getElementById('imageFileInput').value = '';
                Feedback.fire({ title: 'Uploaded!', text: res.uploaded + ' image(s) added.', icon: 'success', timer: 1800, showConfirmButton: false, toast: true, position: 'top-end' });
                loadExistingImages();
            } else {
                Feedback.fire('Upload Failed', res.message || 'Unknown error.', 'error');
            }
        } catch(e) { Feedback.fire('Error', 'Server error during upload.', 'error'); }
    };
    xhr.onerror = () => Feedback.fire('Error', 'Network error. Please retry.', 'error');
    xhr.send(formData);
}

function deleteImage(imageId) {
    Feedback.fire({
        title: 'Remove Photo?',
        text: 'This photo will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Keep'
    }).then(r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('csrf_token', CSRF_TOKEN);
        fd.append('house_id', currentImgHouseId);
        fd.append('image_id', imageId);
        fetch('/tenant/?url=admin/delete_house_image', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const tile = document.getElementById('imgTile_' + imageId);
                    if (tile) tile.remove();
                    ToastStack.success(res.message || 'Property photo deleted.');
                    loadExistingImages();
                } else {
                    Feedback.fire('Error', res.message, 'error');
                }
            });
    });
}

function confirmDelete(id, name) {
    Feedback.fire({
        title: 'Purge Asset Record?',
        text: `Are you absolutely certain you want to decommission "${name}"? This action is irrevocable and will purge all systemic associations.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'CONFIRM PURGE',
        cancelButtonText: 'ABORT'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteHouseId').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}

/* ─── AMENITIES MANAGEMENT ─── */
function openAmenitiesModal(houseId, houseName) {
    document.getElementById('amenitiesModalTitle').innerText = 'Amenities: ' + houseName;
    document.getElementById('amenityHouseId').value = houseId;
    document.getElementById('amenitiesLoader').classList.remove('d-none');
    document.getElementById('amenitiesForm').classList.add('d-none');
    
    fetch('/tenant/?url=admin/get_house_amenities&house_id=' + houseId)
        .then(r => r.json())
        .then(data => {
            document.getElementById('amenitiesLoader').classList.add('d-none');
            document.getElementById('amenitiesForm').classList.remove('d-none');
            if (data.success) {
                const grid = document.getElementById('amenitiesGrid');
                const selectedIds = new Set((data.selected_ids || []).map(String));
                grid.innerHTML = data.all.map(a => {
                    const checked = selectedIds.has(String(a.id)) ? 'checked' : '';
                    return `
                        <div class="col-6">
                            <div class="form-check custom-checkbox bg-white p-3 rounded-4 border shadow-sm h-100 d-flex align-items-center">
                                <input class="form-check-input ms-0 me-3" type="checkbox" name="amenities[]" value="${a.id}" id="am_${a.id}" ${checked} style="cursor:pointer; width:1.2rem; height:1.2rem;">
                                <label class="form-check-label fw-bold small text-dark d-flex align-items-center w-100" for="am_${a.id}" style="cursor:pointer; padding-top:2px;">
                                    <i class="${a.icon} text-warning ms-auto me-2 fs-5 opacity-75"></i> <span class="text-truncate">${a.name}</span>
                                </label>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                Feedback.fire('Error', data.message, 'error');
            }
        }).catch(err => Feedback.fire('Network Error', 'Could not load amenities', 'error'));
    
    new bootstrap.Modal(document.getElementById('amenitiesModal')).show();
}

function saveAmenities() {
    const houseId = document.getElementById('amenityHouseId').value;
    const form = document.getElementById('amenitiesForm');
    const formData = new FormData(form);
    formData.append('house_id', houseId);
    formData.append('csrf_token', CSRF_TOKEN);
    
    const btn = document.getElementById('saveAmenitiesBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<span class="ui-skeleton ui-skeleton-line me-2"></span>SAVING...';
    
    fetch('/tenant/?url=admin/update_house_amenities', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.innerText = originalText;
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('amenitiesModal')).hide();
                Feedback.fire({ title: 'Saved!', text: 'Property amenities updated.', icon: 'success', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
            } else {
                Feedback.fire('Error', res.message, 'error');
            }
        }).catch(err => {
            btn.disabled = false;
            btn.innerText = originalText;
            Feedback.fire('Error', 'Network error while saving.', 'error');
        });
}

</script>

<?php if (isset($_SESSION['success'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    ToastStack.create({type: 'success', title: 'Success!', message: <?= json_encode($_SESSION['success'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, duration: 5000});
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    ToastStack.create({type: 'error', title: 'Action Failed', message: <?= json_encode($_SESSION['error'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, duration: 5000});
});
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php
include __DIR__ . '/../components/payment_modal.php';
?>

<script>
const BHOUSE_LIMIT = <?= $roleLabel === 'Admin' ? 2147483647 : (int)((new Subscription($this->db()))->getLimitsForOwner((int)$_SESSION['user_id'])['bhouse_limit']) ?>;
const CURRENT_BHOUSE_COUNT = <?= (int)($totalHousesCount ?? count($houses)) ?>;

function openAddModal() {
    if (BHOUSE_LIMIT > 0 && CURRENT_BHOUSE_COUNT >= BHOUSE_LIMIT) {
        window.location.href = '/tenant/?url=admin/upgrade';
        return;
    }
    document.getElementById('modalTitle').innerText = 'Register New Property';
    document.getElementById('houseForm').action = '/tenant/?url=admin/store_house';
    document.getElementById('houseId').value = '';
    document.getElementById('houseName').value = '';
    document.getElementById('houseAddress').value = '';
    document.getElementById('houseDesc').value = '';
    document.getElementById('houseLat').value = '';
    document.getElementById('houseLng').value = '';
    new bootstrap.Modal(document.getElementById('houseModal')).show();
}
</script>
