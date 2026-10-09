<?php require __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="/tenant/public/assets/css/boarding.css">

<!-- Background Blobs Transferred from Landing -->
<div class="bg-blobs">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<?php
    $route = 'boarding/explore';
    $count = is_array($houses) ? count($houses) : 0;
?>

<div class="explore-container">
    <!-- Mobile Search Toggle FAB -->
    <button id="mobileSearchToggle" class="mobile-search-fab" aria-label="Toggle Search">
        <i class="fas fa-search"></i>
    </button>

    <!-- Map-First Floating Search -->
    <div class="floating-search-wrap" id="floatingSearchWrap" data-aos="fade-down">
        <div class="glass-filter-card">
            <form action="/tenant/?url=boarding/explore" method="POST" class="row g-3 align-items-end" id="exploreSearchForm">
                <div class="col-lg-6">
                    <label class="search-label-premium">
                        <i class="fas fa-search-location"></i> Location & Name
                    </label>
                    <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Search city or name..." class="search-input-premium">
                </div>
                <div class="col-lg-3">
                    <label class="search-label-premium">
                        <i class="fas fa-coins"></i> Max Price
                    </label>
                    <input type="number" name="max_price" value="<?= htmlspecialchars($filters['max_price'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="₱ Any price" class="search-input-premium">
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn-filter-hero flex-grow-1">
                        <i class="fas fa-magnifying-glass"></i>
                        <span>Find</span>
                    </button>
                    <?php if (!empty($filters['q']) || !empty($filters['max_price'])): ?>
                        <a href="/tenant/?url=boarding/explore" class="btn-reset-premium" title="Reset">
                            <i class="fas fa-rotate"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Mobile Header Removal for Explore */
        @media (max-width: 991px) {
            body.explore-page .app-header,
            body.explore-page .dash-header {
                display: none !important;
            }
        }

        /* Layout Polish for Explore */
        @media (max-width: 991px) {
            body.explore-page { overflow: hidden !important; }
        }
        body.explore-page main { padding-top: 0 !important; } /* Prevents double-margin when not logged in */
        body.explore-page .app-footer { display: none !important; }

        .explore-container {
            width: 100vw;
            margin-left: calc(-50vw + 50%);
            margin-right: calc(-50vw + 50%);
            margin-top: 100px; /* Force container below fixed 100px header */
            position: relative;
            z-index: 100;
            height: calc(100vh - 100px); /* Fill entire remaining height */
            overflow: hidden;
            background: #f8fafc;
        }

        .floating-search-wrap {
            position: absolute;
            bottom: 40px; /* Anchored to Bottom */
            top: auto;
            left: 0;
            right: 0;
            margin: 0 auto;
            z-index: 1000;
            width: 94%;
            max-width: 900px;
        }

        @keyframes float {
            0% { transform: translate(-50%, 0); }
            50% { transform: translate(-50%, -10px); }
            100% { transform: translate(-50%, 0); }
        }

        .glass-filter-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            padding: 24px 30px;
            box-shadow: 0 40px 100px rgba(15, 23, 42, 0.2);
            transition: all 0.3s ease;
        }

        .glass-filter-card:hover {
            background: rgba(255, 255, 255, 0.85);
            transform: translateY(-5px);
        }

        .search-input-premium {
            width: 100%; padding: 14px 20px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05); 
            background: rgba(255,255,255,0.9); font-family: inherit; font-size: 1rem; color: #1e293b; 
            outline: none; box-sizing: border-box; transition: all 0.3s ease;
            font-weight: 600;
        }
        .search-input-premium:focus {
            background: white; border-color: #6366f1; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.1);
        }

        .search-label-premium {
            display: flex; align-items: center; gap: 8px; font-size: 0.7rem; font-weight: 800; color: #475569; 
            text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;
        }

        .btn-filter-hero {
            padding: 0 24px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; 
            border: none; border-radius: 16px; font-weight: 800; cursor: pointer; font-size: 1rem; 
            transition: all 0.3s ease; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2); 
            display: flex; align-items: center; justify-content: center; gap: 10px;
            height: 52px;
        }
        .btn-filter-hero:hover { transform: scale(1.02); box-shadow: 0 15px 30px rgba(79, 70, 229, 0.3); }

        .btn-reset-premium {
            width: 52px; height: 52px; background: white; color: #64748b; 
            border: 1px solid rgba(0,0,0,0.05); border-radius: 16px; 
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem; 
            transition: all 0.3s ease;
        }
        .btn-reset-premium:hover { color: #ef4444; transform: rotate(-90deg); }

        /* Full Screen Map Wrap */
        .map-full-wrap {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0; left: 0;
            z-index: 1;
        }
        #map { width: 100%; height: 100%; }

        /* Custom Pulse Marker Animation */
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

        .mobile-search-fab { display: none; }

        @media (max-width: 991px) {
            .explore-container { 
                height: 100vh; /* Full Screen Height on Mobile */
                margin-top: 0; /* Reset margin since header is hidden */
            }
            .mobile-search-fab {
                display: flex;
                position: absolute;
                top: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: white;
                color: #4f46e5;
                font-size: 1.2rem;
                align-items: center;
                justify-content: center;
                border: none;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
                z-index: 2000;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            .mobile-search-fab.active {
                background: #f1f5f9;
                color: #64748b;
            }
            .floating-search-wrap { 
                display: none; /* Hidden by default on mobile */
                top: 85px; /* Below FAB */
                bottom: auto;
                width: 92%; 
                animation: slideDownFade 0.3s ease forwards;
            }
            .floating-search-wrap.active {
                display: block;
            }
            .glass-filter-card { padding: 20px 24px; border-radius: 20px; background: rgba(255, 255, 255, 0.95); }
            .btn-reset-premium { display: none; }
        }

        @keyframes slideDownFade {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="map-full-wrap">
        <div id="map"></div>
    </div>
</div>

<!-- Leaflet & AOS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>

<script>
let markers = {}; // Global for selection access

$(function () {
    // Mobile Search Toggle Logic
    $('#mobileSearchToggle').on('click', function() {
        $('#floatingSearchWrap').toggleClass('active');
        $(this).toggleClass('active');
        const icon = $(this).find('i');
        if (icon.hasClass('fa-search')) {
            icon.removeClass('fa-search').addClass('fa-times');
        } else {
            icon.removeClass('fa-times').addClass('fa-search');
        }
    });

    $('#exploreSearchForm').on('submit', function() {
        if (window.innerWidth <= 991) {
            $('#floatingSearchWrap').removeClass('active');
            $('#mobileSearchToggle').removeClass('active').find('i').removeClass('fa-times').addClass('fa-search');
        }
    });

    // Initialize AOS
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });

    const houses = <?= json_encode(array_map(static function($h) {
        return [
            'id'   => (int)($h['id'] ?? 0),
            'name' => (string)($h['name'] ?? ''),
            'address' => (string)($h['address'] ?? ''),
            'lat'  => isset($h['latitude']) ? (float)$h['latitude'] : null,
            'lng'  => isset($h['longitude']) ? (float)$h['longitude'] : null
        ];
    }, (array)$houses), JSON_UNESCAPED_UNICODE) ?>;

    const valid = houses.filter(x => x.lat !== null && x.lng !== null);
    const first = valid[0] || { lat: 10.3157, lng: 123.8854 };

    const map = L.map('map', { zoomControl: false }).setView([first.lat, first.lng], 13);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // --- Automatic Geolocation for Starting Position ---
    map.locate({setView: true, maxZoom: 14});

    map.on('locationfound', function(e) {
        L.circle(e.latlng, {
            radius: e.accuracy / 2,
            color: '#6366f1',
            fillColor: '#6366f1',
            fillOpacity: 0.15,
            weight: 1
        }).addTo(map);

        L.circleMarker(e.latlng, {
            radius: 8,
            color: '#ffffff',
            fillColor: '#6366f1',
            fillOpacity: 1,
            weight: 3
        }).addTo(map).bindPopup("<b>You are here</b>").openPopup();
    });

    map.on('locationerror', function(e) {
        console.warn('Location access denied or failed:', e.message);
    });
    
    // Custom premium map style
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const addPropertyToMap = (h) => {
        if (!h.latitude || !h.longitude) return;
        
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

        const marker = L.marker([h.latitude, h.longitude], { icon: customIcon }).addTo(map);
        markers[h.id] = marker;

        marker.bindPopup(
            `<div style="font-family: 'Outfit', sans-serif; padding: 10px; min-width: 180px;">
                <b style="font-size: 1.15rem; color: #0f172a; display: block; margin-bottom: 6px; letter-spacing: -0.5px;">${h.name}</b>
                <div style="color: #64748b; font-size: 0.9rem; margin-bottom: 15px; display: flex; gap: 6px; line-height: 1.4;">
                    <i class="fas fa-location-dot" style="color: #ef4444; margin-top: 3px;"></i> ${h.address}
                </div>
                <a href="/tenant/?url=boarding/show&id=${h.id}" style="background: #4f46e5; color: white; padding: 10px; border-radius: 12px; font-weight: 800; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s ease;">
                    Details <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                </a>
            </div>`
        );
    };

    valid.forEach(h => {
        addPropertyToMap({
            id: h.id,
            name: h.name,
            address: h.address,
            latitude: h.lat,
            longitude: h.lng
        });
    });

    if (valid.length > 0) {
        document.querySelector('.map-full-wrap').insertAdjacentHTML('beforeend', `
            <div class="results-badge" id="resultsBadge" data-aos="fade-right" data-aos-duration="600" style="cursor: pointer; pointer-events: auto;">
                <i class="fas fa-list-ul"></i>
                <span>Showing <strong>${valid.length}</strong> spaces</span>
            </div>

            <div id="resultsPanel" class="results-side-panel d-none shadow-2xl">
                <div class="panel-header">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-house-circle-check text-indigo-600 fs-4"></i>
                        <h5 class="mb-0 fw-bold text-slate-900 letter-spacing--1">Property Catalog</h5>
                    </div>
                    <button class="btn btn-close-panel" onclick="$('#resultsPanel').addClass('d-none')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="resultsList" class="panel-content custom-scrollbar"></div>
            </div>
        `);

        const resultsList = document.getElementById('resultsList');
        let listHtml = '';
        valid.forEach(h => {
            listHtml += `
            <div class="results-list-item" onclick="onListPropertySelected(${h.lat}, ${h.lng}, ${h.id})">
                <div class="prop-info">
                    <span class="prop-name">${h.name}</span>
                    <span class="prop-addr"><i class="fas fa-location-dot me-1"></i> ${h.address}</span>
                </div>
                <div class="prop-action">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>`;
        });
        resultsList.innerHTML = listHtml;

        $('#resultsBadge').on('click', function() {
            $('#resultsPanel').toggleClass('d-none');
        });

        // --- Infinite Scroll Logic ---
        let offset = 10;
        let isLoading = false;
        let noMoreResults = false;

        $('#resultsList').on('scroll', function() {
            if (isLoading || noMoreResults) return;
            const container = $(this);
            if (container.scrollTop() + container.innerHeight() >= container[0].scrollHeight - 50) {
                loadMoreProps();
            }
        });

        function loadMoreProps() {
            isLoading = true;
            const loaderId = 'loader-' + Date.now();
            $('#resultsList').append(`
                <div id="${loaderId}" class="p-4 text-center">
                    <div class="spinner-border spinner-border-sm text-indigo-500" role="status"></div>
                </div>
            `);

            $.get('/tenant/?url=boarding/api_explore', {
                offset: offset,
                limit: 10,
                q: '<?= $filters['q'] ?? '' ?>',
                max_price: '<?= $filters['max_price'] ?? '' ?>'
            }, function(res) {
                $(`#${loaderId}`).remove();
                if (res.success && res.houses.length > 0) {
                    res.houses.forEach(h => {
                        // Append to list
                        $('#resultsList').append(`
                            <div class="results-list-item" onclick="onListPropertySelected(${h.latitude}, ${h.longitude}, ${h.id})">
                                <div class="prop-info">
                                    <span class="prop-name">${h.name}</span>
                                    <span class="prop-addr"><i class="fas fa-location-dot me-1"></i> ${h.address}</span>
                                </div>
                                <div class="prop-action">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        `);
                        // Add to map
                        addPropertyToMap(h);
                    });
                    offset += res.houses.length;
                    $('#resultsBadge strong').text(offset);
                    
                    if (res.houses.length < 10) {
                        noMoreResults = true;
                        $('#resultsList').append('<div class="p-4 text-center text-slate-400 small fw-bold">No more found</div>');
                    }
                } else {
                    noMoreResults = true;
                    $('#resultsList').append('<div class="p-4 text-center text-slate-400 small fw-bold">No more found</div>');
                }
                isLoading = false;
            }).fail(function() {
                $(`#${loaderId}`).remove();
                isLoading = false;
            });
        }

    } else {
        const noResultsHtml = `
            <div id="no-results-overlay" class="no-results-glass">
                <div class="no-results-content">
                    <div class="no-results-icon pulsate">
                        <i class="fas fa-search-minus"></i>
                    </div>
                    <h3>No spaces found</h3>
                    <p>We couldn't find any boarding houses matching your filters. Try widening your search or adjusting the price.</p>
                    <a href="/tenant/?url=boarding/explore" class="btn-clear-filters">
                        <i class="fas fa-rotate"></i>
                        Clear All Filters
                    </a>
                </div>
            </div>
        `;
        document.querySelector('.map-full-wrap').insertAdjacentHTML('beforeend', noResultsHtml);
    }
});

window.onListPropertySelected = function(lat, lng, id) {
    map.flyTo([lat, lng], 16, { animate: true, duration: 1.5 });
    
    // Open the popup for this property
    if (markers[id]) {
        setTimeout(() => {
            markers[id].openPopup();
        }, 1200); // Wait for flyTo to get close
    }

    // Close panel on mobile for better view
    if (window.innerWidth <= 991) {
        $('#resultsPanel').addClass('d-none');
    }
};
</script>

<style>
    .results-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.90);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        padding: 10px 20px;
        border-radius: 30px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.1);
        pointer-events: none; /* Map clicks pass through */
    }

    .results-badge i {
        color: #4f46e5;
        font-size: 1.1rem;
    }

    .results-badge strong {
        font-weight: 800;
        color: #4f46e5;
    }

    @media (max-width: 991px) {
        .results-badge {
            top: 20px;
            left: 20px;
            padding: 8px 16px;
            font-size: 0.85rem;
        }
    }

    .results-side-panel {
        position: absolute;
        top: 80px;
        left: 20px;
        width: 340px;
        max-width: calc(100% - 40px);
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(25px) saturate(180%);
        -webkit-backdrop-filter: blur(25px) saturate(180%);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.5);
        z-index: 1050;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        animation: slideInLeft 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
        max-height: calc(100% - 160px);
    }

    @keyframes slideInLeft {
        from { transform: translateX(-20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .panel-header {
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(255, 255, 255, 0.4);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .panel-content {
        overflow-y: auto;
        flex-grow: 1;
    }

    .results-list-item {
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none !important;
    }

    .results-list-item:hover {
        background: rgba(99, 102, 241, 0.08);
        padding-left: 30px;
    }

    .prop-name {
        display: block;
        font-weight: 800;
        color: #0f172a;
        font-size: 0.95rem;
        margin-bottom: 4px;
        font-family: 'Outfit', sans-serif;
    }

    .prop-addr {
        display: block;
        font-size: 0.75rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .prop-action i {
        color: #94a3b8;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .results-list-item:hover .prop-action i {
        color: #4f46e5;
        transform: translateX(3px);
    }

    .btn-close-panel {
        background: #f1f5f9;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .btn-close-panel:hover {
        background: #ef4444;
        color: white;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(0,0,0,0.2);
    }

    .no-results-glass {
        position: absolute;
        top: 30%; /* Shifted even higher up */
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2000;
        width: 90%;
        max-width: 450px;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 30px;
        padding: 40px 30px; /* Condensed for shorter screens */
        box-shadow: 0 40px 100px rgba(15, 23, 42, 0.2);
        text-align: center;
        animation: slideInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .no-results-content h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 1.8rem;
        color: #0f172a;
        margin-bottom: 15px;
        letter-spacing: -0.5px;
    }

    .no-results-content p {
        color: #475569;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .no-results-icon {
        width: 80px;
        height: 80px;
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        margin: 0 auto 25px;
    }

    .pulsate {
        animation: pulse-ring 2s infinite ease-in-out;
    }

    .btn-clear-filters {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        background: #4f46e5;
        color: white;
        text-decoration: none !important;
        border-radius: 18px;
        font-weight: 800;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
        box-shadow: 0 15px 30px rgba(79, 70, 229, 0.3);
    }

    .btn-clear-filters:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 20px 40px rgba(79, 70, 229, 0.4);
        background: #4338ca;
    }

    @keyframes slideInUp {
        from { transform: translate(-50%, -40%); opacity: 0; }
        to { transform: translate(-50%, -50%); opacity: 1; }
    }
</style>


<?php 
    $footerVariant = 'desktop-only';
    require __DIR__ . '/../layouts/landing_footer.php'; 
?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
