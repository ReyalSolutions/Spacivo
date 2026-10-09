<?php
require __DIR__ . '/../layouts/header.php';

$lat = isset($house['latitude']) ? (float)$house['latitude'] : null;
$lng = isset($house['longitude']) ? (float)$house['longitude'] : null;
$today = date('Y-m-d');
$defaultEnd = date('Y-m-d', strtotime('+30 days'));
?>

<div class="dash-container">
    
    <!-- Welcome Header identical to Dashboard & Bookings -->
    <div class="welcome-header">
        <h1>Boarding House Details</h1>
        <p>Explore this property, view available rooms, and secure your booking today.</p>
    </div>

    <h2 class="section-title">
        <i class="fa-solid fa-house-user" style="color: var(--dash-primary);"></i>
        Property Overview
    </h2>

    <!-- Match identical Residence Card from Dashboard -->
    <div class="residence-card">
        <div class="residence-cover">
            <div class="residence-avatar">
                <i class="fas fa-building"></i>
            </div>
        </div>
        <div class="residence-body">
            <div class="residence-header">
                <div>
                    <h3 class="residence-title">
                        <?= htmlspecialchars((string)$house['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>
                    <p class="residence-address">
                        <i class="fas fa-location-dot" style="color: #ef4444;"></i>
                        <?= htmlspecialchars((string)($house['address'] ?? 'Address not provided'), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <?php if (!empty($house['description'])): ?>
                <div class="residence-amenities">
                    <div class="residence-amenities-title">About this Residence</div>
                    <p style="margin: 0; color: var(--dash-text-muted); line-height: 1.6; font-size: 0.95rem;">
                        <?= nl2br(htmlspecialchars((string)$house['description'], ENT_QUOTES, 'UTF-8')) ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($amenities)): ?>
                <div class="residence-amenities" style="margin-top: 24px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 24px;">
                    <div class="residence-amenities-title">Amenities & Features</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px;">
                        <?php foreach ($amenities as $amen): 
                            $rawIcon = trim((string)($amen['icon'] ?? 'check'));
                            if (strpos($rawIcon, 'fa-') === false) $icon = 'fa-solid fa-' . $rawIcon;
                            elseif (strpos($rawIcon, 'fa ') === false && strpos($rawIcon, 'fas ') === false && strpos($rawIcon, 'fa-solid ') === false && strpos($rawIcon, 'fab ') === false) $icon = 'fa-solid ' . $rawIcon;
                            else $icon = $rawIcon;
                        ?>
                            <span class="amenity-badge">
                                <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
                                <?= htmlspecialchars((string)$amen['name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="residence-actions">
                <button class="btn-res-action <?= $isFavorited ? 'btn-res-secondary' : 'btn-res-primary' ?> favorite-toggle-btn" id="favorite-btn" data-house-id="<?= (int)$house['id'] ?>" style="<?= $isFavorited ? 'color: #ff4f6d; border-color: #ff4f6d;' : '' ?>">
                    <i class="<?= $isFavorited ? 'fas' : 'far' ?> fa-heart"></i> 
                    <span><?= $isFavorited ? 'Saved to Favorites' : 'Add to Favorites' ?></span>
                </button>
                <a href="/tenant/?url=boarding/index" class="btn-res-action btn-res-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Browse
                </a>
            </div>
        </div>
    </div>

    <!-- Rooms and Map identical to Dashboard Grid -->
    <div class="stats-grid">
        
        <!-- Rooms Section -->
        <div>
            <div class="section-title">
                <i class="fas fa-bed" style="color: #6366f1;"></i>
                Available Rooms
            </div>
            
            <?php if (empty($rooms)): ?>
                <div class="premium-stat-card" style="padding: 48px; text-align: center; color: var(--dash-text-muted);">
                    <i class="fa-solid fa-bed-pulse" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px; display: block;"></i>
                    <p style="font-weight: 700; font-size: 1.1rem; margin-bottom: 4px;">No rooms available</p>
                    <p style="font-size: 0.9rem; margin: 0;">This property is currently at full capacity.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach ($rooms as $room): ?>
                        <div class="premium-stat-card" style="padding: 0; overflow: hidden; border: 1px solid #f1f5f9; transition: transform 0.2s, box-shadow 0.2s;">
                            <!-- Room Header -->
                            <div style="padding: 24px; background: #fff; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 6px; font-size: 1.4rem; font-weight: 800; color: var(--dash-text-main); letter-spacing: -0.01em;">
                                        <?= htmlspecialchars((string)$room['room_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </h3>
                                    <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--dash-text-muted); display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-users" style="color: var(--dash-primary); opacity: 0.7;"></i>
                                            Max <?= htmlspecialchars((string)$room['capacity'], ENT_QUOTES, 'UTF-8') ?> guests
                                        </span>
                                        <?php if ((int)$room['available_slots'] > 0): ?>
                                            <span style="font-size: 0.85rem; font-weight: 700; color: #10b981; display: flex; align-items: center; gap: 6px; background: #ecfdf5; padding: 2px 10px; border-radius: 20px;">
                                                <i class="fas fa-circle-check"></i>
                                                <?= htmlspecialchars((string)$room['available_slots'], ENT_QUOTES, 'UTF-8') ?> slots left
                                            </span>
                                        <?php else: ?>
                                            <span style="font-size: 0.85rem; font-weight: 800; color: #ef4444; display: flex; align-items: center; gap: 6px; background: #fef2f2; padding: 2px 10px; border-radius: 20px;">
                                                <i class="fas fa-user-lock"></i>
                                                Occupied
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($room['amenities'])): ?>
                                        <div style="margin-top: 14px; display: flex; flex-wrap: wrap; gap: 6px;">
                                            <?php foreach ($room['amenities'] as $amen): 
                                                $rawIcon = trim((string)($amen['icon'] ?? 'check'));
                                                if (strpos($rawIcon, 'fa-') === false) $icon = 'fa-solid fa-' . $rawIcon;
                                                elseif (strpos($rawIcon, 'fa ') === false && strpos($rawIcon, 'fas ') === false && strpos($rawIcon, 'fa-solid ') === false && strpos($rawIcon, 'fab ') === false) $icon = 'fa-solid ' . $rawIcon;
                                                else $icon = $rawIcon;
                                            ?>
                                                <span style="font-size: 0.75rem; color: var(--dash-text-muted); background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 12px; display: flex; align-items: center; gap: 4px;">
                                                    <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>" style="color: var(--dash-primary); opacity: 0.8; font-size: 0.7rem;"></i>
                                                    <?= htmlspecialchars((string)$amen['name'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="margin-top: 14px; font-size: 0.8rem; color: #94a3b8; font-style: italic; display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-circle-info" style="font-size: 0.75rem; opacity: 0.7;"></i>
                                            No additional amenities
                                        </div>
                                    <?php endif; ?>

                                </div>
                                <div style="text-align: right;">
                                    <span style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Monthly Rent</span>
                                    <span style="font-size: 1.6rem; font-weight: 900; color: var(--dash-primary);">
                                        ₱<?= number_format((float)$room['price'], 2) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Room Booking Action Footer -->
                            <div style="padding: 20px 24px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
                                <?php if ((int)($room['available_slots'] ?? 0) <= 0): ?>
                                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; padding: 14px; border-radius: 14px; text-align: center; font-weight: 800; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <i class="fas fa-ban"></i> Fully Occupied
                                    </div>
                                <?php else: ?>
                                    <div class="booking-form" data-room-id="<?= (int)$room['id'] ?>" style="display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;">
                                        <div style="flex: 1; min-width: 180px;">
                                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                                                <i class="fas fa-calendar-star" style="margin-right: 4px; color: var(--dash-primary);"></i> When will you move in?
                                            </label>
                                            <input type="date" name="start_date" value="<?= $today ?>" required 
                                                   style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid var(--dash-border); background: white; font-family: inherit; font-size: 1rem; font-weight: 600; color: var(--dash-text-main); outline: none; transition: border-color 0.2s;">
                                            <input type="hidden" name="end_date" value=""> <!-- Optional now -->
                                        </div>
                                        <button class="btn primary book-btn" type="button" data-room-id="<?= (int)$room['id'] ?>" 
                                                style="flex: 1.5; min-width: 220px; padding: 16px; height: 52px; background: var(--dash-primary); color: white; border: none; border-radius: 14px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 12px rgba(37,99,235,0.2); transition: all 0.2s;">
                                            <i class="fas fa-door-open"></i> Rent this Room
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Map Section -->
        <div>
            <div class="section-title">
                <i class="fas fa-map-location-dot" style="color: #10b981;"></i>
                Location
            </div>
            <div class="premium-stat-card" style="padding: 0; overflow: hidden; height: 400px; border-radius: var(--dash-radius);">
                <div id="map" style="height: 100%; width: 100%; z-index: 1;"></div>
            </div>
            
            <div class="support-card" style="margin-top: 24px;">
                <h3><i class="fas fa-shield-check" style="color: #10b981;"></i> Safe & Secure Stay</h3>
                <p>All payments and bookings are strictly monitored for your protection.</p>
            </div>
        </div>
        
    </div>
</div>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* Premium Amenity Badge */
    .amenity-badge {
        background: rgba(99, 102, 241, 0.08); /* Soft indigo tint */
        color: #4f46e5;
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(99, 102, 241, 0.15);
        transition: all 0.3s ease;
    }
    .amenity-badge:hover {
        background: rgba(99, 102, 241, 0.15);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.1);
    }
    /* Custom Premium Map Marker */
    .marker-pulse-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .marker-pulse {
        position: absolute; width: 44px; height: 44px;
        background: rgba(99, 102, 241, 0.2); border-radius: 50%;
        animation: pulse-ring 2s infinite ease-in-out;
    }
    .marker-pin {
        width: 32px; height: 32px; background: #6366f1; border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg); display: flex; align-items: center; justify-content: center;
        border: 3px solid white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        transition: all 0.3s ease;
    }
    .marker-pin i { transform: rotate(45deg); color: white; font-size: 1rem; }
    .marker-pulse-wrapper:hover .marker-pin {
        transform: rotate(-45deg) scale(1.2);
        background: #4f46e5;
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.5); opacity: 0.8; }
        100% { transform: scale(1.8); opacity: 0; }
    }
</style>

<script>
    $(function () {
        const lat = <?= $lat === null ? 'null' : (float)$lat ?>;
        const lng = <?= $lng === null ? 'null' : (float)$lng ?>;

        const first = (lat !== null && lng !== null) ? [lat, lng] : [10.3157, 123.8854];
        const map = L.map('map').setView(first, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        if (lat !== null && lng !== null) {
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `
                    <div class="marker-pulse-wrapper">
                        <div class="marker-pulse"></div>
                        <div class="marker-pin">
                            <i class="fas fa-house-chimney"></i>
                        </div>
                    </div>
                `,
                iconSize: [44, 44],
                iconAnchor: [22, 44],
                popupAnchor: [0, -40]
            });

            L.marker([lat, lng], { icon: customIcon }).addTo(map)
             .bindPopup('<strong style="font-family: \'Outfit\', sans-serif; font-size: 1.1rem; color: #0f172a;"><?= htmlspecialchars((string)$house['name'], ENT_QUOTES, 'UTF-8') ?></strong>');
        }

        // Favorite Toggle Logic
        $('#favorite-btn').on('click', function() {
            if (!window.isAuthenticated) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Login Required',
                    text: 'You must log in first to add favorites.',
                    confirmButtonText: 'Go to Login',
                    confirmButtonColor: 'var(--dash-primary)'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = '/tenant/?url=auth/login';
                    }
                });
                return;
            }

            const btn = $(this);
            const houseId = btn.data('house-id');
            const icon = btn.find('i');
            const label = btn.find('span');

            $.ajax({
                url: '/tenant/?url=boarding/toggle_favorite',
                method: 'POST',
                data: { house_id: houseId },
                success: function(resp) {
                    if (resp.status === 'success') {
                        if (resp.action === 'added') {
                            btn.removeClass('btn-res-primary').addClass('btn-res-secondary');
                            btn.css({ 'color': '#ff4f6d', 'border-color': '#ff4f6d' });
                            icon.removeClass('far').addClass('fas');
                            label.text('Saved to Favorites');
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Added to favorites',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            btn.removeClass('btn-res-secondary').addClass('btn-res-primary');
                            btn.css({ 'color': '', 'border-color': '' });
                            icon.removeClass('fas').addClass('far');
                            label.text('Add to Favorites');

                            Swal.fire({
                                icon: 'info',
                                title: 'Removed from favorites',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    }
                }
            });
        });

        // Room Booking AJAX
        $('.book-btn').on('click', function() {
            const btn = $(this);
            const roomId = btn.data('room-id');
            const form = btn.closest('.booking-form');
            const startDate = form.find('input[name="start_date"]').val();
            const endDate = form.find('input[name="end_date"]').val();
            const csrf = '<?= Csrf::token() ?>';

            if (!window.isAuthenticated) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Login Required',
                    text: 'You must log in first to rent a room.',
                    confirmButtonText: 'Go to Login',
                    confirmButtonColor: 'var(--dash-primary)'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = '/tenant/?url=auth/login';
                    }
                });
                return;
            }

            if (!startDate) {
                Swal.fire('Error', 'Please select a move-in date.', 'error');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: '/tenant/?url=booking/store',
                method: 'POST',
                data: {
                    room_id: roomId,
                    start_date: startDate,
                    end_date: endDate,
                    csrf_token: csrf
                },
                success: function(resp) {
                    if (resp.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Booking request sent!',
                            text: 'Redirecting to payment...',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Use POST for redirection to hide payment_id from URL
                            const form = $('<form>', {
                                action: '/tenant/?url=payment/checkout',
                                method: 'POST'
                            }).append($('<input>', {
                                type: 'hidden',
                                name: 'payment_id',
                                value: resp.payment_id
                            }));
                            $('body').append(form);
                            form.submit();
                        });
                    } else {
                        Swal.fire('Booking Failed', resp.message || 'Error occurred', 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-wallet"></i> Rent this Room');
                    }
                },
                error: function(xhr) {
                    let msg = 'An unexpected error occurred.';
                    try {
                        const err = JSON.parse(xhr.responseText);
                        msg = err.message || msg;
                    } catch(e) {}
                    Swal.fire('Error', msg, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-wallet"></i> Rent this Room');
                }
            });
        });
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

