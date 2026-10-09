<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="dash-container">
    <div class="welcome-header">
        <h1>Hi, <?= htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['name'] ?? 'Guest', ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p>Your StayHub dashboard is your primary hub for managing your residence.</p>
    </div>

    <?php if (!empty($activeRentals)): ?>
        <h2 class="section-title">
            <i class="fa-solid fa-house-user" style="color: var(--dash-primary);"></i>
            Current Residence<?= count($activeRentals) > 1 ? 's' : '' ?>
        </h2>
        
        <?php 
        // Group rentals by boarding house
        $groupedRentals = [];
        foreach ($activeRentals as $rental) {
            $groupedRentals[$rental['boarding_house_id']]['info'] = $rental;
            $groupedRentals[$rental['boarding_house_id']]['rooms'][] = $rental;
        }
        ?>

        <?php foreach ($groupedRentals as $houseId => $houseData): 
            $rental = $houseData['info'];
            $houseRooms = $houseData['rooms'];
        ?>
            <div class="residence-card" style="margin-bottom: 24px;">
                <div class="residence-cover">
                    <div class="residence-avatar">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="residence-body">
                    <div class="residence-header">
                        <div>
                            <h3 class="residence-title">
                                <?= htmlspecialchars($rental['boarding_house_name'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <p class="residence-address">
                                <i class="fas fa-location-dot" style="color: #ef4444;"></i>
                                <?= htmlspecialchars($rental['boarding_house_address'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <div class="residence-tags">
                                <?php foreach ($houseRooms as $hr): ?>
                                    <span class="residence-tag">
                                        <i class="fas fa-door-open"></i> Room: <?= htmlspecialchars($hr['room_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endforeach; ?>
                                <span class="residence-tag verified">
                                    <i class="fa-solid fa-shield-halved"></i> Verified Stay
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if ($houseId === $activeRentals[0]['boarding_house_id']): ?>
                    <div class="residence-amenities">
                        <div class="residence-amenities-title">Amenities Included</div>
                        <div class="amenities-grid">
                            <?php if (!empty($amenities)): ?>
                                <?php foreach ($amenities as $amenity): ?>
                                    <div class="amenity-tag">
                                        <i class="fas <?= htmlspecialchars($amenity['icon'], ENT_QUOTES, 'UTF-8') ?>" style="color: #10b981;"></i>
                                        <?= htmlspecialchars($amenity['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="color: var(--dash-text-muted); font-size: 0.9rem; font-style: italic;">No amenities listed yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="residence-actions">
                        <a href="javascript:void(0)" class="btn-res-action btn-res-primary view-details-trigger" data-id="<?= (int)$rental['boarding_house_id'] ?>">
                            <i class="fas fa-circle-info"></i> View Details
                        </a>
                        <a href="#" class="btn-res-action btn-res-secondary">
                            <i class="fas fa-comment-dots"></i> Message Landlord
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="stats-grid">
            <a href="/tenant/?url=tenant/dues" class="premium-stat-card" style="text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 20px -5px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                    <div class="stat-info" style="display: flex; flex-direction: column;">
                        <span class="stat-label" style="text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.8rem; margin-bottom: 4px; color: var(--dash-text-muted);">
                            <?php if (count($activeRentals) > 1): ?>
                                Earliest Payment Due
                            <?php else: ?>
                                Next Payment Due
                            <?php endif; ?>
                        </span>
                        <span class="stat-value" style="font-size: 1.8rem; font-weight: 900; color: var(--dash-text-main); line-height: 1.2;"><?= $nextPayment ?></span>
                    </div>
                    <div class="stat-icon" style="background: #f0f9ff; color: #0ea5e9; flex-shrink: 0; width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
                <div class="due-progress-container" style="margin-top: auto; padding-top: 16px;">
                    <div class="progress-label" style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; font-weight: 600; color: var(--dash-text-muted);">
                        <span>Payment Cycle</span>
                        <span style="color: var(--dash-primary); font-weight: 800;"><?= $daysRemaining ?> days left</span>
                    </div>
                    <div class="progress-bar-bg" style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div class="progress-bar-fill" style="width: <?= $progressPercent ?>%; height: 100%; background: linear-gradient(90deg, #0ea5e9, #3b82f6); border-radius: 4px; transition: width 0.5s ease;"></div>
                    </div>
                    <?php if (count($activeRentals) > 1): ?>
                        <div style="margin-top: 10px; font-size: 0.75rem; color: var(--dash-primary); font-weight: 700; text-align: center;">
                            <i class="fas fa-layer-group"></i> Manage <?= count($activeRentals) ?> active rooms
                        </div>
                    <?php endif; ?>
                </div>
            </a>
            
            <div class="premium-stat-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                    <div class="stat-info" style="display: flex; flex-direction: column;">
                        <span class="stat-label" style="text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.8rem; margin-bottom: 4px;">Total Amount Paid</span>
                        <span class="stat-value" style="font-size: 2rem; font-weight: 900; color: #10b981; line-height: 1.2;">₱<?= number_format((float)$totalPaid, 2) ?></span>
                    </div>
                    <div class="stat-icon" style="background: #ecfdf5; color: #10b981; flex-shrink: 0; width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <div style="margin-top: auto; padding-top: 16px;">
                    <a href="/tenant/?url=tenant/payments" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.95rem; color: #10b981; font-weight: 800; text-decoration: none; padding: 12px 20px; background: #ecfdf5; border-radius: 14px; transition: all 0.2s; width: 100%; justify-content: center; border: 1px solid rgba(16, 185, 129, 0.1);">
                        Detailed Ledger <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                    </a>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 24px;">
            <!-- Recent Transactions -->
            <div>
                <div class="section-title">
                    <i class="fa-solid fa-receipt" style="color: #6366f1;"></i>
                    Recent Transactions
                </div>
                <div class="premium-transaction-list">
                    <?php if (empty($recentPayments)): ?>
                        <div style="padding: 24px; text-align: center; color: var(--dash-text-muted); font-size: 0.9rem; font-weight: 600;">
                            No payment records found.
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentPayments as $pay): ?>
                            <div class="premium-transaction-item">
                                <div class="trans-icon <?= $pay['status'] === 'paid' ? 'paid' : 'pending' ?>" style="width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; <?= $pay['status'] === 'paid' ? 'background: #ecfdf5; color: #10b981;' : 'background: #fff7ed; color: #f97316;' ?>">
                                    <i class="fas <?= $pay['status'] === 'paid' ? 'fa-check' : 'fa-clock' ?>"></i>
                                </div>
                                <div class="trans-info" style="flex: 1;">
                                    <span class="trans-title" style="display: block; font-weight: 800; font-size: 0.95rem; color: var(--dash-text-main); margin-bottom: 2px;">
                                        <?= htmlspecialchars($pay['payment_method'], ENT_QUOTES, 'UTF-8') ?> Payment
                                    </span>
                                    <span class="trans-date" style="font-size: 0.8rem; color: var(--dash-text-muted); font-weight: 600;">
                                        <?= date('M d, Y', strtotime($pay['created_at'])) ?>
                                    </span>
                                </div>
                                <div class="trans-amount" style="font-weight: 900; font-size: 1.05rem; <?= $pay['status'] === 'paid' ? 'color: #10b981;' : 'color: var(--dash-text-main);' ?>">
                                    ₱<?= number_format((float)$pay['amount'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Maintenance & Support -->
            <div>
                <div class="section-title">
                    <i class="fa-solid fa-headset" style="color: #ec4899;"></i>
                    Support & Help
                </div>
                <div class="support-card">
                    <h3>Maintenance Request</h3>
                    <p>Encountering issues with your room? We're here to help you quickly.</p>
                    <a href="#" class="btn-support">Report an Issue</a>
                    <div style="margin-top: 20px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-circle-info"></i>
                        Average response: 2-4 hours
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Premium Empty State -->
        <div class="stat-card" style="padding: 60px 40px; text-align: center; border: 2px dashed #e2e8f0; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); box-shadow: none;">
            <div class="stat-icon" style="margin: 0 auto 24px; width: 100px; height: 100px; font-size: 2.5rem; background: #f1f5f9; color: #94a3b8; border-radius: 30px;">
                <i class="fas fa-house-chimney-user"></i>
            </div>
            <h2 style="margin-bottom: 12px; font-weight: 800; color: var(--dash-text-main);">Welcome to StayHub!</h2>
            <p style="color: var(--dash-text-muted); margin: 0 auto 32px; max-width: 400px; font-size: 1.1rem; line-height: 1.6;">
                You don't have an active rental yet. Find your perfect boarding house and start your journey with us today.
            </p>
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="/tenant/?url=boarding/explore" class="btn-primary-lg">
                    <i class="fas fa-search" style="font-size: 1.2rem;"></i>
                    Browse Houses
                </a>
                <a href="/tenant/?url=tenant/bookings" class="btn-outline-lg">
                    <i class="fas fa-clipboard-list" style="font-size: 1.2rem; color: var(--dash-primary);"></i>
                     My Requests
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 class="section-title" style="margin-bottom: 0;">
            <i class="fas fa-rocket" style="color: #f59e0b;"></i>
            Quick Links
        </h2>
        <button id="manage-shortcuts-btn" style="background: none; border: none; color: var(--dash-primary); font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i class="fas fa-sliders"></i>
            Manage
        </button>
    </div>

    <div class="action-menu" id="shortcuts-container">
        <!-- Shortcuts will be dynamically managed by JS using LocalStorage -->
        <a href="/tenant/?url=boarding/explore" class="action-item" data-shortcut="browse">
            <div class="icon-box browse">
                <i class="fas fa-magnifying-glass-location"></i>
            </div>
            <span class="action-title">Browse</span>
        </a>
        <a href="/tenant/?url=tenant/bookings" class="action-item" data-shortcut="bookings">
            <div class="icon-box bookings">
                <i class="fas fa-layer-group"></i>
            </div>
            <span class="action-title">History</span>
        </a>
        <a href="/tenant/?url=tenant/favorites" class="action-item" data-shortcut="favorites">
            <div class="icon-box favorites">
                <i class="fas fa-star"></i>
            </div>
            <span class="action-title">Favorites</span>
        </a>
        <a href="#" class="action-item" data-shortcut="support">
            <div class="icon-box support">
                <i class="fas fa-headset"></i>
            </div>
            <span class="action-title">Support</span>
        </a>
        <a href="/tenant/?url=tenant/profile" class="action-item" data-shortcut="profile">
            <div class="icon-box profile">
                <i class="fas fa-user-gear"></i>
            </div>
            <span class="action-title">Profile</span>
        </a>
        
        <button class="action-item add-shortcut-trigger" id="add-shortcut-btn" style="display: none;">
            <div class="icon-box add-shortcut">
                <i class="fas fa-plus"></i>
            </div>
            <span class="action-title">Add</span>
        </button>
    </div>

    <!-- Shortcut Customization Modal -->
    <div class="shortcut-modal" id="shortcutModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Customize Dashboard</h3>
                <button id="close-modal" style="background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 12px; cursor: pointer; color: #64748b;">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <p style="color: var(--dash-text-muted); font-size: 0.9rem; margin-bottom: 24px;">Select the shortcuts you want to see on your primary dashboard.</p>
            
            <div class="toggle-list">
                <div class="toggle-item" data-id="browse">
                    <div class="toggle-icon"><i class="fas fa-magnifying-glass-location"></i></div>
                    <div class="toggle-info">
                        <span class="toggle-title">Browse Places</span>
                    </div>
                    <div class="toggle-input"></div>
                </div>
                <div class="toggle-item" data-id="bookings">
                    <div class="toggle-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="toggle-info">
                        <span class="toggle-title">Booking History</span>
                    </div>
                    <div class="toggle-input"></div>
                </div>
                <div class="toggle-item" data-id="favorites">
                    <div class="toggle-icon"><i class="fas fa-star"></i></div>
                    <div class="toggle-info">
                        <span class="toggle-title">My Favorites</span>
                    </div>
                    <div class="toggle-input"></div>
                </div>
                <div class="toggle-item" data-id="support">
                    <div class="toggle-icon"><i class="fas fa-headset"></i></div>
                    <div class="toggle-info">
                        <span class="toggle-title">Help & Support</span>
                    </div>
                    <div class="toggle-input"></div>
                </div>
                <div class="toggle-item" data-id="profile">
                    <div class="toggle-icon"><i class="fas fa-user-gear"></i></div>
                    <div class="toggle-info">
                        <span class="toggle-title">Profile Settings</span>
                    </div>
                    <div class="toggle-input"></div>
                </div>
            </div>

            <button id="save-shortcuts" style="width: 100%; background: var(--dash-primary); color: white; border: none; padding: 16px; border-radius: 16px; font-weight: 800; font-size: 1rem; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);">
                Save Preferences
            </button>
        </div>
    </div>

    <!-- Announcement Section -->
    <div class="announcement-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 class="section-title" style="margin-bottom: 0;">
                <i class="fa-solid fa-bullhorn" style="color: #ef4444;"></i>
                Latest Announcements
            </h2>
            <a href="/tenant/?url=tenant/announcements" style="color: var(--dash-primary); font-weight: 700; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--dash-primary-light); border-radius: 10px; transition: background 0.2s;">
                See All
                <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
            </a>
        </div>
        
        <div class="announcement-card">
            <div class="ann-icon">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div class="ann-content">
                <div class="ann-meta">Update • Today</div>
                <span class="ann-title">Enhanced Security Protocol</span>
                <p class="ann-body">We've updated our guest policy to ensure maximum safety. Please remind all visitors to log in at the main gate.</p>
            </div>
        </div>

        <div class="announcement-card" style="border-left-color: #6366f1;">
            <div class="ann-icon" style="background: #eef2ff; color: #6366f1;">
                <i class="fas fa-faucet-drip"></i>
            </div>
            <div class="ann-content">
                <div class="ann-meta">Notice • Mar 22</div>
                <span class="ann-title">Scheduled Water Maintenance</span>
                <p class="ann-body">There will be a brief water service interruption this Sunday from 2:00 PM to 4:00 PM for tank cleaning.</p>
            </div>
        </div>
    </div>
</div>


<?php 
    $footerVariant = 'desktop-only';
    require __DIR__ . '/../layouts/landing_footer.php'; 
?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

<!-- BHouse Details Modal (also used on bookings page) -->
<div class="shortcut-modal" id="bhouseModal">
    <div class="modal-card" style="max-width: 800px; width: 95%; max-height: 90vh; overflow-y: auto; padding: 0; display: flex; flex-direction: column;">
        
        <!-- Header Gradient Cover -->
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

                <!-- Amenities -->
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">Amenities Included</h3>
                    <div id="modal-amenities" style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Description -->
                <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05);">
                    <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">About this Residence</h3>
                    <p id="modal-description" style="margin: 0; color: var(--dash-text-muted); line-height: 1.6; font-size: 0.95rem;"></p>
                </div>

                <!-- Map -->
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--dash-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">Location Map</h3>
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

function openBhouseModal(houseId) {
    $('#bhouseModal').addClass('active');
    $('#modal-loading').css('display', 'block');
    $('#modal-content').css('display', 'none');
    
    $.ajax({
        url: '/tenant/?url=boarding/api_show&id=' + houseId,
        method: 'GET',
        dataType: 'json',
        success: function(resp) {
            if (resp.success && resp.house) {
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
                
                // Map
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

                    if (detailsMarker) detailsMap.removeLayer(detailsMarker);
                    if (hasCoords) {
                        detailsMarker = L.marker([lat, lng]).addTo(detailsMap).bindPopup(resp.house.name);
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
}

$(document).ready(function() {
    $(document).on('click', '.view-details-trigger', function(e) {
        e.preventDefault();
        openBhouseModal($(this).data('id'));
    });

    $('#close-bhouse-modal').on('click', function() {
        $('#bhouseModal').removeClass('active');
    });
});
</script>

