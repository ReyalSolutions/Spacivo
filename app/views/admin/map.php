<?php require __DIR__ . '/../layouts/management_header.php'; ?>

<style>
.map-wrapper {
    height: calc(100vh - 200px);
    min-height: 500px;
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    position: relative;
}
#adminMap { height: 100%; width: 100%; }

.house-panel {
    background: #fff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    overflow-y: auto;
    max-height: calc(100vh - 200px);
    min-height: 500px;
}
.house-item {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.house-item:last-child { border-bottom: none; }
.house-item:hover { background: #f8fafc; }
.house-item.active { background: #eff6ff; border-left: 3px solid #2563eb; }

.house-icon {
    width: 38px; height: 38px;
    background: #eff6ff;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    color: #2563eb;
    font-size: 1rem;
}
.house-icon.has-coords { background: #ecfdf5; color: #059669; }
.house-icon.no-coords  { background: #fff7ed; color: #d97706; }

.coord-badge {
    font-size: 0.7rem; font-weight: 700;
    padding: 3px 8px; border-radius: 100px;
    display: inline-block; margin-top: 4px;
}
.coord-badge.set   { background: #ecfdf5; color: #059669; }
.coord-badge.unset { background: #fff7ed; color: #d97706; }

/* Edit Panel */
#editPanel {
    position: absolute; bottom: 20px; left: 20px; right: 20px;
    z-index: 1000;
    background: white;
    border-radius: 18px;
    padding: 20px 24px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    border: 1px solid #e2e8f0;
    display: none;
    max-width: 460px;
}

.map-instructions {
    position: absolute; top: 20px; left: 20px; transform: none;
    z-index: 1000;
    background: rgba(15,23,42,0.85);
    color: white;
    padding: 8px 18px;
    border-radius: 100px;
    font-size: 0.78rem; font-weight: 700;
    white-space: nowrap;
    backdrop-filter: blur(8px);
    pointer-events: none;
}

/* Leaflet popup override */
.leaflet-popup-content-wrapper { border-radius: 14px !important; box-shadow: 0 8px 30px rgba(0,0,0,0.12) !important; }
.leaflet-popup-content { font-family: 'Outfit', sans-serif; font-size: 0.85rem; min-width: 200px; }
</style>

<!-- Leaflet & Geosearch Dependencies -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.css">

<style>
/* Geosearch Custom Premium Styling - Floating Bar */
.custom-map-search {
    position: absolute;
    top: 20px;
    right: 20px;
    left: auto;
    z-index: 1100;
    width: 360px;
    max-width: calc(100% - 40px);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-input-wrapper {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px) saturate(180%);
    -webkit-backdrop-filter: blur(12px) saturate(180%);
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    display: flex;
    align-items: center;
    padding: 4px 8px;
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    transition: all 0.3s;
}

.search-input-wrapper:focus-within {
    background: #ffffff;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: translateY(-1px);
}

.search-input-wrapper i.search-icon {
    color: #6366f1;
    font-size: 1.1rem;
    margin-left: 12px;
}

.search-input-wrapper input {
    border: none;
    background: transparent;
    padding: 10px 14px;
    width: 100%;
    font-family: 'Outfit', sans-serif;
    font-weight: 600;
    font-size: 0.95rem;
    color: #1e293b;
    outline: none;
}

.search-input-wrapper input::placeholder {
    color: #94a3b8;
    font-weight: 500;
}

.search-results-box {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(16px);
    border-radius: 18px;
    margin-top: 10px;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.5);
    display: none;
}

.search-result-item {
    padding: 14px 18px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.5);
}

.search-result-item:last-child { border-bottom: none; }

.search-result-item:hover {
    background: #f8fafc;
    padding-left: 22px;
}

.search-result-item .loc-icon {
    width: 32px;
    height: 32px;
    background: #eff6ff;
    color: #2563eb;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.search-result-item .loc-details {
    overflow: hidden;
}

.search-result-item .loc-name {
    font-weight: 700;
    font-size: 0.88rem;
    color: #0f172a;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.search-result-item .loc-addr {
    font-size: 0.75rem;
    color: #64748b;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#clearPlaceSearch {
    padding: 8px;
    border-radius: 50%;
    border: none;
    background: #f1f5f9;
    color: #94a3b8;
    cursor: pointer;
    display: none;
    transition: all 0.2s;
    margin-right: 4px;
}

#clearPlaceSearch:hover {
    background: #e2e8f0;
    color: #475569;
}

@media (max-width: 576px) {
    .custom-map-search {
        width: calc(100% - 24px);
        left: 12px;
        top: 12px;
    }
}
</style>

<div class="animate-fade-up">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 p-4 bg-white rounded-4 shadow-sm border">
        <div>
            <h2 class="fw-bold m-0 text-dark fs-4 d-flex align-items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-primary"></i> Boarding House Map
            </h2>
            <p class="text-muted mb-0 small fw-600 mt-1">Click any house in the list or drop a pin on the map to set its coordinates</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-light text-dark border px-3 py-2 fw-700">
                <i class="fa-solid fa-circle-check text-success me-1"></i>
                <?= count(array_filter($houses, fn($h) => !empty($h['latitude']))) ?> / <?= count($houses) ?> Pinned
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: House List -->
        <div class="col-lg-3 col-md-4">
            <div class="house-panel p-2">
                <div class="px-2 py-2 mb-1">
                    <input type="text" id="houseSearch" class="form-control rounded-pill px-3 border-2" placeholder="Search properties...">
                </div>
                <div id="houseList">
                    <?php if (empty($houses)): ?>
                        <div class="text-center p-4 mt-4">
                            <div class="mb-3 text-muted">
                                <i class="fa-solid fa-map-location-dot fs-1"></i>
                            </div>
                            <h6 class="fw-bold text-dark">No boarding house yet</h6>
                            <p class="text-muted small mb-3">You need to add a boarding house before you can pin its location on the map.</p>
                            <a href="/tenant/?url=owner/houses" class="btn btn-primary rounded-pill fw-bold text-white px-4">
                                <i class="fa-solid fa-plus me-2"></i>Add It Now
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($houses as $h): ?>
                        <div class="house-item" 
                             data-id="<?= $h['id'] ?>"
                             data-name="<?= htmlspecialchars($h['name']) ?>"
                             data-lat="<?= $h['latitude'] ?? '' ?>"
                             data-lng="<?= $h['longitude'] ?? '' ?>"
                             data-addr="<?= htmlspecialchars($h['address']) ?>">
                            <div class="house-icon <?= !empty($h['latitude']) ? 'has-coords' : 'no-coords' ?>">
                                <i class="fa-solid fa-<?= !empty($h['latitude']) ? 'location-dot' : 'location-question' ?>"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-800 text-dark text-truncate small"><?= htmlspecialchars($h['name']) ?></div>
                                <div class="text-muted text-truncate" style="font-size: 0.7rem;"><?= htmlspecialchars($h['address']) ?></div>
                                <span class="coord-badge <?= !empty($h['latitude']) ? 'set' : 'unset' ?>">
                                    <?= !empty($h['latitude']) ? '✓ Pinned' : '⚠ Not set' ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Map -->
        <div class="col-lg-9 col-md-8">
            <div class="map-wrapper">
                <div class="map-instructions" id="mapInstructions">
                    <i class="fa-solid fa-hand-pointer me-2"></i>Select a boarding house, then click on the map
                </div>
                
                <!-- Custom Floating Search Bar -->
                <div class="custom-map-search">
                    <div class="search-input-wrapper">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" id="mapPlaceSearch" placeholder="Search for places (e.g. Cebu City)" autocomplete="off">
                        <button type="button" id="clearPlaceSearch" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div id="placeSearchResults" class="search-results-box"></div>
                </div>

                <div id="adminMap"></div>

                <!-- Edit Panel (shows after clicking map) -->
                <div id="editPanel">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-900 text-dark mb-0" id="editHouseName">House Name</h6>
                            <div class="text-muted small fw-600" id="editCoords">—</div>
                        </div>
                        <button class="btn-close btn-sm" onclick="closeEditPanel()"></button>
                    </div>
                    <input type="hidden" id="editHouseId">
                    <input type="hidden" id="editLat">
                    <input type="hidden" id="editLng">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary rounded-pill px-4 fw-700 flex-grow-1" id="saveCoordBtn" onclick="saveCoordinates()">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Save Location
                        </button>
                        <button class="btn btn-outline-secondary rounded-pill px-3 fw-700" onclick="closeEditPanel()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet & Geosearch JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-geosearch@3.11.0/dist/bundle.min.js"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const houses = <?= json_encode($houses) ?>;

// --- Init Map ---
const map = L.map('adminMap', { zoomControl: false }).setView([10.3157, 123.8854], 13); // Fallback: Cebu City

// Move zoom controls to bottom right to avoid overlap
L.control.zoom({ position: 'bottomright' }).addTo(map);

// Attempt to geolocate user and center map using device GPS
map.locate({setView: true, maxZoom: 15});

// Show a small blue dot where the user is
map.on('locationfound', function(e) {
    const radius = e.accuracy / 2;
    L.circleMarker(e.latlng, {
        radius: 8,
        fillColor: "#3b82f6",
        color: "#ffffff",
        weight: 3,
        opacity: 1,
        fillOpacity: 1
    }).addTo(map).bindPopup("You are here (GPS Location)");
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(map);

// --- Custom Premium Geosearch Logic ---
const provider = new window.GeoSearch.OpenStreetMapProvider();
const searchInput = document.getElementById('mapPlaceSearch');
const resultsBox = document.getElementById('placeSearchResults');
const clearBtn = document.getElementById('clearPlaceSearch');
let searchTimeout;

searchInput.addEventListener('input', function(e) {
    const query = e.target.value.trim();
    clearBtn.style.display = query.length > 0 ? 'block' : 'none';
    
    clearTimeout(searchTimeout);
    if (query.length < 3) {
        resultsBox.style.display = 'none';
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const results = await provider.search({ query: query });
            renderSearchResults(results);
        } catch (err) {
            console.error('Search failed:', err);
        }
    }, 400);
});

function renderSearchResults(results) {
    if (!results || results.length === 0) {
        resultsBox.innerHTML = '<div class="p-3 text-center text-muted small fw-bold">No locations found.</div>';
    } else {
        let html = '';
        results.forEach(res => {
            const labelParts = res.label.split(',');
            const primary = labelParts[0];
            const secondary = labelParts.slice(1).join(',').trim();
            html += `
                <div class="search-result-item" onclick="onPlaceSelected(${res.y}, ${res.x}, '${primary.replace(/'/g, "\\'")}')">
                    <div class="loc-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="loc-details">
                        <span class="loc-name">${primary}</span>
                        <span class="loc-addr text-truncate">${secondary}</span>
                    </div>
                </div>
            `;
        });
        resultsBox.innerHTML = html;
    }
    resultsBox.style.display = 'block';
}

function onPlaceSelected(lat, lon, label) {
    map.flyTo([lat, lon], 16, { animate: true, duration: 1.5 });
    
    if (tempMarker) map.removeLayer(tempMarker);
    closeEditPanel();
    
    resultsBox.style.display = 'none';
    searchInput.value = label;

    // Pulse effect on map center
    const pulse = L.circleMarker([lat, lon], {
        radius: 0,
        fillColor: "#2563eb",
        color: "#2563eb",
        weight: 1,
        opacity: 0.8,
        fillOpacity: 0.2
    }).addTo(map);

    let r = 0;
    const interval = setInterval(() => {
        r += 5;
        pulse.setRadius(r);
        pulse.setStyle({ opacity: 1 - (r/100) });
        if (r > 100) { map.removeLayer(pulse); clearInterval(interval); }
    }, 15);
}

clearBtn.addEventListener('click', () => {
    searchInput.value = '';
    clearBtn.style.display = 'none';
    resultsBox.style.display = 'none';
    searchInput.focus();
});

// Close dropdown on click outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.custom-map-search')) {
        resultsBox.style.display = 'none';
    }
});

// --- Marker state ---
let selectedHouseId = null;
let selectedHouseName = '';
let tempMarker = null;
let houseMarkers = {};

// --- Draw existing pins ---
function pinIcon(pinned) {
    return L.divIcon({
        className: '',
        html: `<div style="
            background: ${pinned ? '#2563eb' : '#f59e0b'}; 
            color: white; width: 36px; height: 36px; border-radius: 50% 50% 50% 0; 
            transform: rotate(-45deg); border: 3px solid white;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: center;
        "><i class="fa-solid fa-house" style="transform: rotate(45deg); font-size: 14px;"></i></div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -40]
    });
}

houses.forEach(h => {
    if (h.latitude && h.longitude) {
        const marker = L.marker([h.latitude, h.longitude], { icon: pinIcon(true) })
            .addTo(map)
            .bindPopup(`<strong>${h.name}</strong><br><small class="text-muted">${h.address}</small><br><code style="font-size:0.7rem;">${h.latitude}, ${h.longitude}</code>`);
        houseMarkers[h.id] = marker;
    }
});

// --- Click on house list ---
document.querySelectorAll('.house-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.house-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        selectedHouseId = item.dataset.id;
        selectedHouseName = item.dataset.name;

        document.getElementById('mapInstructions').innerHTML = 
            `<i class="fa-solid fa-crosshairs me-2 text-primary"></i>Click on the map to pin <strong>${selectedHouseName}</strong>`;
        document.getElementById('mapInstructions').style.background = 'rgba(37,99,235,0.9)';

        // If it already has coords, fly to it and open its popup
        if (item.dataset.lat && item.dataset.lng) {
            map.flyTo([item.dataset.lat, item.dataset.lng], 16, { duration: 1.2 });
            // Open the popup after flight animation completes
            const hid = item.dataset.id;
            map.once('moveend', function() {
                if (houseMarkers[hid]) {
                    houseMarkers[hid].openPopup();
                }
            });
        }

        closeEditPanel();
    });
});

// --- Map click to drop temp pin ---
map.on('click', function(e) {
    if (!selectedHouseId) {
        Swal.fire({ icon: 'info', title: 'Select a House First', text: 'Please click on a boarding house from the list on the left first.', confirmButtonColor: '#2563eb' });
        return;
    }

    const { lat, lng } = e.latlng;

    if (tempMarker) map.removeLayer(tempMarker);

    tempMarker = L.marker([lat, lng], { 
        icon: pinIcon(false),
        draggable: true 
    }).addTo(map);

    // Allow dragging
    tempMarker.on('dragend', function(ev) {
        const pos = ev.target.getLatLng();
        updateEditPanel(pos.lat, pos.lng);
    });

    updateEditPanel(lat, lng);
});

function updateEditPanel(lat, lng) {
    document.getElementById('editHouseId').value = selectedHouseId;
    document.getElementById('editLat').value = lat.toFixed(7);
    document.getElementById('editLng').value = lng.toFixed(7);
    document.getElementById('editHouseName').textContent = selectedHouseName;
    document.getElementById('editCoords').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('editPanel').style.display = 'block';
}

function closeEditPanel() {
    document.getElementById('editPanel').style.display = 'none';
    if (tempMarker) { map.removeLayer(tempMarker); tempMarker = null; }
}

// --- Save coordinates via AJAX ---
function saveCoordinates() {
    const houseId = document.getElementById('editHouseId').value;
    const lat = document.getElementById('editLat').value;
    const lng = document.getElementById('editLng').value;
    const btn = document.getElementById('saveCoordBtn');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...';

    fetch('/tenant/?url=admin/map_update_coords', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ house_id: houseId, latitude: lat, longitude: lng, csrf_token: csrfToken })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Save Location';

        if (data.success) {
            // Remove old marker and place confirmed one
            if (houseMarkers[houseId]) map.removeLayer(houseMarkers[houseId]);
            if (tempMarker) { map.removeLayer(tempMarker); tempMarker = null; }

            const newMarker = L.marker([lat, lng], { icon: pinIcon(true) })
                .addTo(map)
                .bindPopup(`<strong>${selectedHouseName}</strong><br><code style="font-size:0.7rem;">${parseFloat(lat).toFixed(6)}, ${parseFloat(lng).toFixed(6)}</code>`);
            houseMarkers[houseId] = newMarker;

            // Update sidebar item
            const item = document.querySelector(`.house-item[data-id="${houseId}"]`);
            if (item) {
                item.dataset.lat = lat;
                item.dataset.lng = lng;
                item.querySelector('.house-icon').className = 'house-icon has-coords';
                item.querySelector('.house-icon i').className = 'fa-solid fa-location-dot';
                item.querySelector('.coord-badge').className = 'coord-badge set';
                item.querySelector('.coord-badge').textContent = '✓ Pinned';
            }

            document.getElementById('editPanel').style.display = 'none';
            document.getElementById('mapInstructions').innerHTML = '<i class="fa-solid fa-hand-pointer me-2"></i>Select a boarding house, then click on the map';
            document.getElementById('mapInstructions').style.background = 'rgba(15,23,42,0.85)';
            selectedHouseId = null;
            document.querySelectorAll('.house-item').forEach(i => i.classList.remove('active'));

            Swal.fire({ icon: 'success', title: 'Coordinates Saved!', text: `Location pinned for "${selectedHouseName || 'the property'}".`, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Could not save. Try again.' });
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Save Location';
        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Please check your connection and try again.' });
    });
}

// --- Search filter ---
document.getElementById('houseSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.house-item').forEach(item => {
        const name = item.dataset.name.toLowerCase();
        const addr = item.dataset.addr.toLowerCase();
        item.style.display = (name.includes(q) || addr.includes(q)) ? '' : 'none';
    });
});
</script>

<?php require __DIR__ . '/../layouts/management_footer.php'; ?>
