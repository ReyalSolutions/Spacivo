<div class="col-xl-4 col-md-6 mb-4">
    <div class="house-card h-100">
        <div class="house-img-container" id="card-img-<?= $h['id'] ?>" data-house-id="<?= $h['id'] ?>">
            <span class="status-badge status-<?= strtolower($h['status']) ?>">
                <i class="fa-solid <?= $h['status'] === 'approved' ? 'fa-check' : ($h['status'] === 'pending' ? 'fa-clock' : 'fa-xmark') ?> me-2"></i>
                <?= strtoupper($h['status']) ?>
            </span>
            <?php if (!empty($h['images'])): ?>
                <div id="carousel-<?= $h['id'] ?>" class="carousel slide h-100" data-bs-ride="carousel" style="border-radius:24px 24px 0 0;overflow:hidden;">
                    <div class="carousel-inner h-100">
                        <?php foreach ($h['images'] as $idx => $img): ?>
                            <div class="carousel-item h-100 <?= $idx === 0 ? 'active' : '' ?>" style="background-color: #0f172a;">
                                <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($h['name']) ?>"
                                    style="width:100%;height:220px;object-fit:contain;display:block;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($h['images']) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $h['id'] ?>" data-bs-slide="prev" style="opacity:1;">
                        <i class="fa-solid fa-chevron-left text-primary fs-2" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.8));"></i>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $h['id'] ?>" data-bs-slide="next" style="opacity:1;">
                        <i class="fa-solid fa-chevron-right text-primary fs-2" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.8));"></i>
                    </button>
                    <div style="position:absolute;bottom:10px;right:14px;background:rgba(0,0,0,0.55);color:#fff;font-size:0.68rem;padding:3px 9px;border-radius:20px;font-weight:700;backdrop-filter:blur(6px);z-index:5;">
                        <i class="fa-solid fa-images me-1"></i><?= count($h['images']) ?> Photos
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="house-img-placeholder">
                    <i class="fa-solid fa-house-chimney fa-4x mb-2 opacity-50"></i>
                    <span class="small fw-800 text-uppercase letter-spacing-2 opacity-50">Boarding House</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h3 class="h4 fw-900 text-dark mb-0"><?= htmlspecialchars($h['name']) ?></h3>
                <div class="dropdown">
                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2">
                        <li><a class="dropdown-item rounded-3 fw-600" href="#" onclick='openEditModal(<?= json_encode($h) ?>)'><i class="fa-solid fa-pen-to-square me-2"></i>Edit Details</a></li>
                        <li><a class="dropdown-item rounded-3 fw-600 text-danger" href="#" onclick="confirmDelete(<?= $h['id'] ?>, '<?= addslashes($h['name']) ?>')"><i class="fa-solid fa-trash-can me-2"></i>Delete Asset</a></li>
                    </ul>
                </div>
            </div>
            
            <p class="text-muted small fw-600 mb-4 d-flex align-items-center gap-2">
                <span class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary" style="width:28px; height:28px; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-location-dot" style="font-size: 0.7rem;"></i>
                </span>
                <span><?= htmlspecialchars($h['address']) ?></span>
            </p>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="p-3 bg-light rounded-4 text-center border">
                        <div class="text-muted small fw-800 uppercase mb-1" style="font-size: 0.55rem;">Rooms Provisioned</div>
                        <div class="h5 mb-0 fw-900"><?= count($h['rooms'] ?? []) ?></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded-4 text-center border">
                        <div class="text-muted small fw-800 uppercase mb-1" style="font-size: 0.55rem;">Live Occupancy</div>
                        <div class="h5 mb-0 fw-900 text-primary">
                            <?php 
                                $hCap = array_reduce($h['rooms'] ?? [], fn($cc, $rr) => $cc + $rr['capacity'], 0);
                                $hOcc = array_reduce($h['rooms'] ?? [], fn($cc, $rr) => $cc + ($rr['capacity'] - $rr['available_slots']), 0);
                                echo $hCap > 0 ? round(($hOcc / $hCap) * 100) . '%' : '0%';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-cluster d-flex justify-content-between align-items-center mt-auto pt-4 border-top">
                <a href="/tenant/?url=owner/bookings&house_id=<?= $h['id'] ?>" class="tool-icon text-primary bg-primary-subtle border-primary-subtle" data-tooltip="Manage Tenants">
                    <i class="fa-solid fa-user-group"></i>
                </a>
                <button class="tool-icon text-info bg-info-subtle border-info-subtle" data-tooltip="Manage Photos" onclick="openImageModal(<?= $h['id'] ?>, '<?= addslashes($h['name']) ?>')">
                    <i class="fa-solid fa-images"></i>
                </button>
                <button class="tool-icon text-warning bg-warning-subtle border-warning-subtle" data-tooltip="Manage Amenities" onclick="openAmenitiesModal(<?= $h['id'] ?>, '<?= addslashes($h['name']) ?>')">
                    <i class="fa-solid fa-list-check"></i>
                </button>
                <button class="tool-icon text-dark bg-secondary-subtle border-secondary-subtle" data-tooltip="Configure Asset" onclick='openEditModal(<?= json_encode($h) ?>)'>
                    <i class="fa-solid fa-gear"></i>
                </button>
                <button class="tool-icon delete text-danger bg-danger-subtle border-danger-subtle" data-tooltip="Delete Asset" onclick="confirmDelete(<?= $h['id'] ?>, '<?= addslashes($h['name']) ?>')">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>
    </div>
</div>
