<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php
$currentRental = null;
$historyList = [];

foreach ($bookings as $b) {
    if (isset($activeBookingId) && $activeBookingId === (int)$b['id'] && !(bool)($b['is_moved_out'] ?? 0)) {
        $currentRental = $b;
    } else {
        $historyList[] = $b;
    }
}
?>

<div class="dash-container">
    <div class="welcome-header">
        <h1>My Boarding Houses</h1>
        <p>Track your current residence and historical boarding house migrations.</p>
    </div>

    <?php if (empty($bookings)): ?>
        <div style="padding: 40px; text-align: center; color: var(--dash-text-muted); background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-top: 24px;">
            <i class="fa-solid fa-house-chimney-crack" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>No active lease found. <a href="/tenant/?url=boarding/index" style="color: var(--dash-primary); font-weight: 700; text-decoration: none;">Browse boarding houses</a> to start a lease.</p>
        </div>
    <?php endif; ?>

    <?php if ($currentRental): ?>
        <h2 class="section-title" style="margin-top: 24px;">
            <i class="fa-solid fa-house-user" style="color: var(--dash-primary);"></i>
            Current Residence
        </h2>
        
        <div class="residence-card" style="margin-bottom: 32px;">
            <div class="residence-cover">
                <div class="residence-avatar">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            <div class="residence-body">
                <div class="residence-header">
                    <div>
                        <h3 class="residence-title" style="font-size: 1.5rem; margin-bottom: 8px;">
                            <?= htmlspecialchars((string)$currentRental['boarding_house_name'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p class="residence-address" style="margin-bottom: 16px;">
                            <i class="fas fa-location-dot" style="color: #ef4444;"></i>
                            <?= htmlspecialchars((string)($currentRental['address'] ?? 'Address not provided'), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <div class="residence-tags">
                            <span class="residence-tag">
                                <i class="fas fa-door-open"></i> Room: <?= htmlspecialchars((string)$currentRental['room_name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="residence-tag verified">
                                <i class="fa-solid fa-shield-halved"></i> Active Lease
                            </span>
                        </div>
                    </div>
                </div>

                <div class="residence-actions" style="margin-top: 24px;">
                    <a href="javascript:void(0)" class="btn-res-action btn-res-primary view-details-trigger" data-id="<?= (int)$currentRental['boarding_house_id'] ?>">
                        <i class="fas fa-circle-info"></i> View Place Details
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($historyList)): ?>
        <h2 class="section-title" style="<?= $currentRental ? 'margin-top: 40px;' : 'margin-top: 24px;' ?>">
            <i class="fa-solid fa-clock-rotate-left" style="color: #64748b;"></i>
            Migration History
        </h2>
        
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($historyList as $b): ?>
                <?php 
                    // Status Logic for Past/Other Bookings
                    $statusBadgeClass = '';
                    $statusLabel = ucfirst($b['status']);
                    $statusIconColor = '#94a3b8';
                    
                    if (isset($b['is_moved_out']) && (int)$b['is_moved_out'] === 1) {
                        $statusBadgeClass = 'background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;'; // Gray
                        $statusLabel = 'Moved Out';
                    } elseif ($b['status'] === 'approved') {
                        $statusBadgeClass = 'background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;'; // Gray
                        $statusLabel = 'Past Lease';
                    } elseif ($b['status'] === 'pending') {
                        $statusBadgeClass = 'background: #fff7ed; color: #f97316; border: 1px solid #fed7aa;'; // Orange
                        $statusIconColor = '#f97316';
                    } else {
                        $statusBadgeClass = 'background: #fef2f2; color: #ef4444; border: 1px solid #fecaca;'; // Red
                        $statusIconColor = '#ef4444';
                    }
                ?>
                <div style="background: white; border-radius: 24px; border: 1px solid #f1f5f9; padding: 20px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -2px rgba(0,0,0,0.02);">
                    
                    <!-- Top Row: Icon + Meta -->
                    <div style="display: flex; gap: 16px; align-items: flex-start;">
                        <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); display: flex; align-items: center; justify-content: center; color: <?= $statusIconColor ?>; font-size: 1.25rem; flex-shrink: 0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); border: 1px solid #e2e8f0;">
                            <i class="fas fa-building"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px; gap: 12px;">
                                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--dash-text-main); line-height: 1.4; word-break: break-word;">
                                    <?= htmlspecialchars((string)$b['boarding_house_name'], ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <span style="font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; <?= $statusBadgeClass ?> flex-shrink: 0; margin-top: 2px;">
                                    <?= $statusLabel ?>
                                </span>
                            </div>
                            <div style="color: var(--dash-text-muted); font-size: 0.85rem; font-weight: 600;">
                                <i class="fas fa-door-open" style="color: var(--dash-primary); margin-right: 4px;"></i> Room: <?= htmlspecialchars((string)$b['room_name'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 2px dashed #f1f5f9; margin: 16px 0;">

                    <!-- Bottom Row: Data Points -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
                        <div>
                            <span style="display: block; font-size: 0.7rem; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 4px;">Lease Duration</span>
                            <span style="font-weight: 700; color: var(--dash-text-muted); font-size: 0.9rem;">
                                <?= date('M d, Y', strtotime($b['start_date'])) ?> &mdash; 
                                <?php 
                                if (!empty($b['end_date']) && $b['end_date'] !== '0000-00-00') {
                                    echo date('M d, Y', strtotime((string)$b['end_date']));
                                } else {
                                    echo '<span style="font-style: italic; font-weight: 500;">Not yet decided</span>';
                                }
                                ?>
                            </span>
                        </div>
                        <div style="text-align: right;">
                            <span style="display: block; font-size: 0.7rem; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 2px;">Total Deposit</span>
                            <span style="font-weight: 900; color: var(--dash-primary); font-size: 1.25rem; line-height: 1;">
                                ₱<?= number_format((float)$b['total_amount'], 2) ?>
                            </span>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


<?php 
    $footerVariant = 'desktop-only';
    require __DIR__ . '/../layouts/landing_footer.php'; 
?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

<!-- BHouse Details Modal -->
<div class="shortcut-modal" id="bhouseModal">
    <div class="modal-card" style="max-width: 800px; width: 95%; max-height: 90vh; overflow-y: auto; padding: 0; display: flex; flex-direction: column;">
        
        <!-- Header Image (Cover) -->
        <div style="height: 140px; background: linear-gradient(135deg, var(--dash-primary) 0%, var(--dash-accent) 100%); position: relative; border-radius: 24px 24px 0 0; flex-shrink: 0;">
            <button id="close-bhouse-modal" style="position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 12px; cursor: pointer; color: white; backdrop-filter: blur(4px); transition: all 0.2s;">
                <i class="fas fa-xmark"></i>
            </button>
            <div style="position: absolute; bottom: -40px; left: 32px; width: 80px; height: 80px; background: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: var(--dash-primary); box-shadow: 0 10px 25px -5px rgba(37,99,235,0.3); border: 4px solid white;">
                <i class="fas fa-building"></i>
            </div>
        </div>

        <div style="padding: 56px 32px 32px; flex: 1; display: flex; flex-direction: column; gap: 24px;">
            <!-- Loading State -->
            <div id="modal-loading" style="text-align: center; padding: 40px; color: var(--dash-text-muted);">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 16px; color: var(--dash-primary);"></i>
                <p>Loading property details...</p>
            </div>

            <!-- Content Area -->
            <div id="modal-content" style="display: none; flex-direction: column; gap: 24px;">
                
                <!-- Title & Address -->
                <div>
                    <h2 id="modal-title" style="font-size: 1.7rem; font-weight: 800; color: var(--dash-text-main); margin: 0 0 8px;">House Name</h2>
                    <p id="modal-address" style="font-size: 1rem; color: var(--dash-text-muted); display: flex; align-items: center; gap: 8px; margin: 0;">
                        <i class="fas fa-location-dot" style="color: #ef4444;"></i>
                        <span>Address</span>
                    </p>
                </div>

                <!-- Description -->
                <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05);">
                    <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">About this Residence</h3>
                    <p id="modal-description" style="margin: 0; color: var(--dash-text-muted); line-height: 1.6; font-size: 0.95rem;"></p>
                </div>

                <!-- Amenities -->
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">Amenities Included</h3>
                    <div id="modal-amenities" style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Map -->
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">Location map</h3>
                    <div style="height: 250px; background: #e2e8f0; border-radius: 16px; overflow: hidden; border: 1px solid rgba(0,0,0,0.05);">
                        <div id="modal-map" style="height: 100%; width: 100%; z-index: 1;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet Setup for Modal -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let detailsMap = null;
let detailsMarker = null;

$(document).ready(function() {
    $('.view-details-trigger').on('click', function(e) {
        e.preventDefault();
        const houseId = $(this).data('id');
        
        // Show modal and loading state
        $('#bhouseModal').addClass('active');
        $('#modal-loading').css('display', 'block');
        $('#modal-content').css('display', 'none');
        
        // Fetch data
        $.ajax({
            url: '/tenant/?url=boarding/api_show&id=' + houseId,
            method: 'GET',
            dataType: 'json',
            success: function(resp) {
                if(resp.success && resp.house) {
                    $('#modal-title').text(resp.house.name);
                    $('#modal-address span').text(resp.house.address || 'Address not provided');
                    $('#modal-description').html(resp.house.description ? resp.house.description.replace(/\n/g, '<br>') : 'No description available.');

                    // Render Amenity chips
                    const amenitiesEl = $('#modal-amenities');
                    amenitiesEl.empty();
                    if (resp.amenities && resp.amenities.length > 0) {
                        resp.amenities.forEach(function(a) {
                            amenitiesEl.append(
                                `<div class="amenity-tag" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:20px; font-size:0.85rem; font-weight:700; color:#16a34a;">
                                    <i class="fas ${a.icon}"></i> ${a.name}
                                </div>`
                            );
                        });
                    } else {
                        amenitiesEl.append('<span style="color:var(--dash-text-muted); font-style:italic; font-size:0.9rem;">No amenities listed.</span>');
                    }
                    
                    $('#modal-loading').css('display', 'none');
                    $('#modal-content').css('display', 'flex');
                    
                    // Initialize or update Map
                    setTimeout(() => {
                        const lat = parseFloat(resp.house.latitude);
                        const lng = parseFloat(resp.house.longitude);
                        const hasCoords = !isNaN(lat) && !isNaN(lng);
                        const center = hasCoords ? [lat, lng] : [10.3157, 123.8854];

                        if (!detailsMap) {
                            detailsMap = L.map('modal-map').setView(center, 14);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap',
                                maxZoom: 19
                            }).addTo(detailsMap);
                        } else {
                            detailsMap.setView(center, 14);
                            detailsMap.invalidateSize();
                        }

                        if (detailsMarker) {
                            detailsMap.removeLayer(detailsMarker);
                        }

                        if (hasCoords) {
                            detailsMarker = L.marker([lat, lng]).addTo(detailsMap)
                                .bindPopup(resp.house.name);
                        }
                    }, 250);

                } else {
                    $('#bhouseModal').removeClass('active');
                    Feedback.fire('Error', resp.message || 'Failed to load details', 'error');
                }
            },
            error: function() {
                $('#bhouseModal').removeClass('active');
                Feedback.fire('Error', 'Network error occurred.', 'error');
            }
        });
    });

    $('#close-bhouse-modal').on('click', function() {
        $('#bhouseModal').removeClass('active');
    });
});
</script>
