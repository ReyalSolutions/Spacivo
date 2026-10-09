<?php 
$hideAdminHeaderTitle = true;
require __DIR__ . '/../layouts/admin_header.php'; 
?>
<style>
/* Elite Dashboard Design System */
:root {
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.3);
    --accent-indigo: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    --accent-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
    --accent-amber: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.dashboard-container {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Glass Stats Cards */
.stats-card {
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.stats-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 45px rgba(31, 38, 135, 0.12);
}

.icon-box {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Professional Property Cards */
.house-card {
    border: none;
    border-radius: 28px;
    background: #ffffff;
    box-shadow: 0 4px 24px rgba(0,0,0,0.03);
    transition: all 0.4s ease;
    border: 1px solid #f1f5f9;
}
.house-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
}

.house-img-container {
    height: 220px;
    border-radius: 24px 24px 0 0;
    position: relative;
    overflow: hidden;
    background: #f8fafc;
}

.house-img-placeholder {
    height: 100%;
    background: linear-gradient(45deg, #1e293b, #334155);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.9);
    transition: transform 0.6s ease;
}
.house-card:hover .house-img-placeholder { transform: scale(1.1); }

/* Animated Badges */
.status-badge {
    position: absolute;
    top: 24px;
    right: 24px;
    padding: 8px 16px;
    border-radius: 40px;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    z-index: 5;
    animation: pulseBadge 2s infinite;
}

@keyframes pulseBadge {
    0% { transform: scale(1); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    50% { transform: scale(1.05); box-shadow: 0 4px 25px rgba(0,0,0,0.2); }
    100% { transform: scale(1); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
}

.status-approved { background: rgba(16, 185, 129, 0.9); color: white; }
.status-pending { background: rgba(245, 158, 11, 0.9); color: white; }
.status-rejected { background: rgba(239, 68, 68, 0.9); color: white; }

/* Action Clusters & Premium Tooltips */
.action-cluster {
    gap: 12px;
}

.tool-icon {
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    border: 1.5px solid transparent;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    font-size: 1.15rem;
    position: relative;
    text-decoration: none !important;
}
.tool-icon:hover {
    transform: translateY(-4px) scale(1.08) rotate(3deg);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    z-index: 10;
}
.tool-icon.delete:hover {
    transform: translateY(-4px) scale(1.08) rotate(-3deg);
}

/* Tooltip Magic */
[data-tooltip]::before,
[data-tooltip]::after {
    position: absolute;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
    z-index: 1000;
}
[data-tooltip]::after {
    content: attr(data-tooltip);
    bottom: 115%;
    left: 50%;
    transform: translateX(-50%) translateY(8px);
    background: #0f172a;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 8px;
    white-space: nowrap;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    letter-spacing: 0.5px;
}
[data-tooltip]::before {
    content: '';
    bottom: calc(115% - 5px);
    left: 50%;
    transform: translateX(-50%) translateY(8px);
    border: 6px solid transparent;
    border-top-color: #0f172a;
}
[data-tooltip]:hover::before,
[data-tooltip]:hover::after {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

/* Custom Modal Meta */
.modal-content { border-radius: 32px; border: none; }
.modal-header { border-radius: 32px 32px 0 0; }
.modal-footer { border-radius: 0 0 32px 32px; }

</style>

<div class="container-fluid px-4 py-5 dashboard-container">
    <!-- Header Hero -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-800 small uppercase letter-spacing-1">Portfolio v2.0</span>
                <span class="text-muted small">|</span>
                <span class="text-muted small fw-600">Secure Administrative Dashboard</span>
            </div>
            <h1 class="display-5 fw-900 text-dark mb-2 letter-spacing--2">Property Management</h1>
            <p class="text-muted fs-5 mb-0 fw-500">Orchestrate your boarding houses with real-time analytics and secure logistics.</p>
        </div>
        <div class="col-lg-5 text-lg-end mt-4 mt-lg-0">
            <button class="btn btn-primary rounded-pill px-5 py-3 shadow-lg fw-900 d-inline-flex align-items-center gap-3 transition-all hover-scale" onclick="openAddModal()">
                <div class="bg-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width:34px; height:34px;">
                    <i class="fa-solid fa-plus-circle text-primary"></i>
                </div>
                <span>REGISTER NEW PROPERTY</span>
            </button>
        </div>
    </div>

    <!-- Analytics Dashboard -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-cyan-50 text-cyan-600" style="background:#ecfeff; color:#0891b2;">
                        <i class="fa-solid fa-building-circle-check fa-xl"></i>
                    </div>
                    <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1 small fw-800">Verified</span>
                </div>
                <div class="text-muted small fw-800 uppercase letter-spacing-1 mb-1">Active Assets</div>
                <div class="h2 mb-0 fw-900"><?= count($houses) ?> <small class="text-muted fw-500 h6">Units</small></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-emerald-50 text-emerald-600" style="background:#ecfdf5; color:#059669;">
                        <i class="fa-solid fa-bed-pulse fa-xl"></i>
                    </div>
                    <span class="text-muted small fw-700">Total Capacity</span>
                </div>
                <div class="text-muted small fw-800 uppercase letter-spacing-1 mb-1">Human Occupancy</div>
                <div class="h2 mb-0 fw-900">
                    <?php 
                        $totalCap = array_reduce($houses, fn($c, $h) => $c + array_reduce($h['rooms'] ?? [], fn($cc, $r) => $cc + $r['capacity'], 0), 0);
                        echo $totalCap;
                    ?>
                    <small class="text-muted fw-500 h6">Slots</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box bg-amber-50 text-amber-600" style="background:#fffbeb; color:#d97706;">
                        <i class="fa-solid fa-wallet fa-xl"></i>
                    </div>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small fw-800">Verified</span>
                </div>
                <div class="text-muted small fw-800 uppercase letter-spacing-1 mb-1">Total Revenue</div>
                <div class="h2 mb-0 fw-900">
                    <small class="text-muted fw-500 h6">₱</small><?= number_format($totalRevenue, 2) ?>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card p-4 h-100" style="background: linear-gradient(135deg, #64748b 0%, #334155 100%); border: none; color: white; box-shadow: 0 10px 30px rgba(51, 65, 85, 0.3);">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="icon-box shadow-sm" style="background: rgba(255,255,255,0.15); color: #ffffff;">
                        <i class="fa-solid fa-screwdriver-wrench" style="color: #ffffff; font-size: 26px;"></i>
                    </div>
                </div>
                <div class="text-white text-opacity-70 small fw-800 uppercase letter-spacing-1 mb-1">Provisioned Amenities</div>
                <div class="h2 mb-0 fw-900">
                    <?php 
                        $totalAmenities = array_reduce($houses, fn($c, $h) => $c + count($h['amenities'] ?? []), 0);
                        echo $totalAmenities;
                    ?>
                    <small class="text-white text-opacity-50 fw-500 h6">Services</small>
                </div>
                <div class="text-white text-opacity-40 small mt-3 fw-600">Across Portfolio Asset Suite</div>
            </div>
        </div>
    </div>

    <!-- Active Property Clusters -->
    <div id="housesGrid" class="row g-4 mb-3">
        <?php if (empty($houses)): ?>
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-5 shadow-sm border border-dashed border-2">
                    <div class="mb-4">
                        <i class="fa-solid fa-house-circle-exclamation text-primary bg-primary bg-opacity-10 p-5 rounded-circle" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="fw-900 text-dark mb-3">Your Portfolio is Empty</h2>
                    <p class="text-muted fs-5 mb-4 mx-auto" style="max-width: 500px;">Begin by registering your first property to access the StayHub administrative ecosystem and start attracting verified tenants.</p>
                    <button class="btn btn-primary rounded-pill px-5 py-3 fw-900 shadow-lg d-inline-flex align-items-center gap-2" onclick="openAddModal()">
                        <i class="fa-solid fa-plus-circle fa-lg"></i>
                        <span>REGISTER FIRST PROPERTY</span>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($houses as $h): ?>
                <?php include __DIR__ . '/_house_card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($houses) && isset($totalHousesCount) && $totalHousesCount > count($houses)): ?>
        <div class="text-center mt-3 mb-5" id="loadMoreContainer">
            <button class="btn btn-outline-primary rounded-pill px-5 py-3 fw-800 shadow-sm" id="loadMoreBtn" onclick="loadMoreHouses()">
                <i class="fa-solid fa-arrow-down fa-bounce me-2"></i> LOAD MORE PROPERTIES (<span id="remainingCount"><?= $totalHousesCount - count($houses) ?></span> REMAINING)
            </button>
        </div>
    <?php endif; ?>
</div>

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
                        <div class="spinner-border spinner-border-sm text-primary"></div>
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
                    <div class="spinner-border text-warning mb-2" role="status"></div>
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
<form id="deleteForm" method="POST" action="/tenant/?url=owner/delete_house">
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
        Swal.fire({
            title: 'Invalid Address Format',
            text: 'Please follow the format: Street/Purok, Barangay, City, Province, Country (5 components separated by commas)',
            icon: 'error',
            confirmButtonColor: '#4f46e5'
        });
        return false;
    }
    
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>SUBMITTING...';
};



function openEditModal(house) {
    document.getElementById('houseForm').action = '/tenant/?url=owner/update_house';
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
    fetch('/tenant/?url=owner/get_house_images&house_id=' + currentImgHouseId)
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
    const container = document.getElementById('card-img-' + houseId);
    if (!container) return;

    const carouselId = 'carousel-' + houseId;

    // Destroy existing Bootstrap carousel instance if any
    const oldCarousel = document.getElementById(carouselId);
    if (oldCarousel) {
        const bsInstance = bootstrap.Carousel.getInstance(oldCarousel);
        if (bsInstance) bsInstance.dispose();
    }

    // Remove everything except the status badge
    Array.from(container.children).forEach(el => {
        if (!el.classList.contains('status-badge')) el.remove();
    });

    if (images.length === 0) {
        // Restore placeholder
        const ph = document.createElement('div');
        ph.className = 'house-img-placeholder';
        ph.innerHTML = '<i class="fa-solid fa-house-chimney fa-4x mb-2 opacity-50"></i><span class="small fw-800 text-uppercase letter-spacing-2 opacity-50">Boarding House</span>';
        container.appendChild(ph);
        return;
    }

    // Build carousel
    const slides = images.map((img, i) => `
        <div class="carousel-item h-100 ${i === 0 ? 'active' : ''}" style="background-color: #0f172a;">
            <img src="${img.image_path}" alt="Property Photo"
                style="width:100%;height:220px;object-fit:contain;display:block;">
        </div>
    `).join('');

    const controls = images.length > 1 ? `
        <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev" style="opacity:1;">
            <i class="fa-solid fa-chevron-left text-primary fs-2" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.8));"></i>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next" style="opacity:1;">
            <i class="fa-solid fa-chevron-right text-primary fs-2" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.8));"></i>
        </button>
        <div style="position:absolute;bottom:10px;right:14px;background:rgba(0,0,0,0.55);color:#fff;font-size:0.68rem;padding:3px 9px;border-radius:20px;font-weight:700;backdrop-filter:blur(6px);z-index:5;">
            <i class="fa-solid fa-images me-1"></i>${images.length} Photos
        </div>
    ` : '';

    const carouselEl = document.createElement('div');
    carouselEl.id = carouselId;
    carouselEl.className = 'carousel slide h-100';
    carouselEl.setAttribute('data-bs-ride', 'carousel');
    carouselEl.style.cssText = 'border-radius:24px 24px 0 0;overflow:hidden;';
    carouselEl.innerHTML = `<div class="carousel-inner h-100">${slides}</div>${controls}`;

    container.appendChild(carouselEl);

    // Boot Bootstrap carousel
    new bootstrap.Carousel(carouselEl, { ride: 'carousel', interval: 3500 });
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
    xhr.open('POST', '/tenant/?url=owner/upload_house_images');
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
                Swal.fire({ title: 'Uploaded!', text: res.uploaded + ' image(s) added.', icon: 'success', timer: 1800, showConfirmButton: false, toast: true, position: 'top-end' });
                loadExistingImages();
            } else {
                Swal.fire('Upload Failed', res.message || 'Unknown error.', 'error');
            }
        } catch(e) { Swal.fire('Error', 'Server error during upload.', 'error'); }
    };
    xhr.onerror = () => Swal.fire('Error', 'Network error. Please retry.', 'error');
    xhr.send(formData);
}

function deleteImage(imageId) {
    Swal.fire({
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
        fetch('/tenant/?url=owner/delete_house_image', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const tile = document.getElementById('imgTile_' + imageId);
                    if (tile) tile.remove();
                    loadExistingImages();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
    });
}

function confirmDelete(id, name) {
    Swal.fire({
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
    
    fetch('/tenant/?url=owner/get_house_amenities&house_id=' + houseId)
        .then(r => r.json())
        .then(data => {
            document.getElementById('amenitiesLoader').classList.add('d-none');
            document.getElementById('amenitiesForm').classList.remove('d-none');
            if (data.success) {
                const grid = document.getElementById('amenitiesGrid');
                grid.innerHTML = data.all.map(a => {
                    const checked = data.selected_ids.includes(a.id) ? 'checked' : '';
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
                Swal.fire('Error', data.message, 'error');
            }
        }).catch(err => Swal.fire('Network Error', 'Could not load amenities', 'error'));
    
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
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>SAVING...';
    
    fetch('/tenant/?url=owner/update_house_amenities', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.innerText = originalText;
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('amenitiesModal')).hide();
                Swal.fire({ title: 'Saved!', text: 'Property amenities updated.', icon: 'success', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }).catch(err => {
            btn.disabled = false;
            btn.innerText = originalText;
            Swal.fire('Error', 'Network error while saving.', 'error');
        });
}

let currentOffset = 10;
function loadMoreHouses() {
    const btn = document.getElementById('loadMoreBtn');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>LOADING...';
    btn.disabled = true;

    fetch('/tenant/?url=owner/load_more_houses&offset=' + currentOffset)
        .then(r => r.text())
        .then(html => {
            if (html.trim() !== '') {
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const loadedCount = temp.querySelectorAll('.house-card').length;
                
                document.getElementById('housesGrid').insertAdjacentHTML('beforeend', html);
                currentOffset += loadedCount;
                
                const total = <?= isset($totalHousesCount) ? $totalHousesCount : 0 ?>;
                const remaining = total - currentOffset;
                
                if (remaining > 0) {
                    btn.disabled = false;
                    btn.innerHTML = `<i class="fa-solid fa-arrow-down fa-bounce me-2"></i> LOAD MORE PROPERTIES (<span id="remainingCount">${remaining}</span> REMAINING)`;
                } else {
                    document.getElementById('loadMoreContainer').remove();
                }
            } else {
                document.getElementById('loadMoreContainer').remove();
            }
        })
        .catch(err => {
            btn.innerHTML = originalContent;
            btn.disabled = false;
            Swal.fire('Error', 'Failed to load more properties.', 'error');
        });
}
</script>

<?php if (isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    title: 'Success!',
    text: "<?= $_SESSION['success'] ?>",
    icon: 'success',
    confirmButtonColor: '#4f46e5',
    borderRadius: '24px',
    timer: 3000,
    timerProgressBar: true
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    title: 'Action Failed',
    text: "<?= $_SESSION['error'] ?>",
    icon: 'error',
    confirmButtonColor: '#ef4444',
    borderRadius: '24px'
});
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php 
include __DIR__ . '/../components/upgrade_modal.php';
include __DIR__ . '/../components/payment_modal.php';
?>

<script>
const BHOUSE_LIMIT = <?= (int)($limits['bhouse_limit'] ?? 0) ?>;
const CURRENT_BHOUSE_COUNT = <?= (int)($totalHousesCount ?? count($houses)) ?>;

function openAddModal() {
    if (BHOUSE_LIMIT > 0 && CURRENT_BHOUSE_COUNT >= BHOUSE_LIMIT) {
        new bootstrap.Modal(document.getElementById('upgradePlanModal')).show();
        return;
    }
    document.getElementById('modalTitle').innerText = 'Register New Property';
    document.getElementById('houseForm').action = '/tenant/?url=owner/store_house';
    document.getElementById('houseId').value = '';
    document.getElementById('houseName').value = '';
    document.getElementById('houseAddress').value = '';
    document.getElementById('houseDesc').value = '';
    document.getElementById('houseLat').value = '';
    document.getElementById('houseLng').value = '';
    new bootstrap.Modal(document.getElementById('houseModal')).show();
}
</script>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
