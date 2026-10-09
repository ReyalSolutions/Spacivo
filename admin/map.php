<?php
require_once __DIR__ . '/components/auth_check.php';

$housesRes = $db->query("SELECT id, name, address, latitude, longitude, status FROM boarding_houses ORDER BY name ASC");
$houses = $housesRes ? $housesRes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Property Map – <?= htmlspecialchars($siteName) ?> Admin</title>
  <?php include __DIR__ . '/components/links.php'; ?>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    #map { height: 600px; border-radius: 16px; width: 100%; z-index: 1; }
    .map-list-item { transition: all 0.2s ease; cursor: pointer; }
    .map-list-item:hover { background-color: #f8fafc; }
  </style>
</head>
<body>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
     data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
  
  <?php include __DIR__ . '/components/sidebar.php'; ?>
  
  <div class="body-wrapper">
    <?php include __DIR__ . '/components/header.php'; ?>
    
    <div class="container-fluid">
      
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h4 class="fw-bold mb-1 text-dark">Property Geographic Explorer</h4>
          <p class="text-muted small mb-0">Interactive location map of all boarding houses and properties</p>
        </div>
      </div>

      <div class="row g-4">
        <!-- Map View -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm" style="border-radius:18px;">
            <div class="card-body p-2">
              <div id="map"></div>
            </div>
          </div>
        </div>

        <!-- Properties List -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm" style="border-radius:18px; max-height:620px; overflow-y:auto;">
            <div class="card-body p-4">
              <h5 class="fw-bold text-dark mb-3">Registered Properties (<?= count($houses) ?>)</h5>
              <div class="d-flex flex-column gap-2">
                <?php foreach ($houses as $h): ?>
                  <div class="p-3 border rounded-3 map-list-item" onclick="focusProperty(<?= (float)($h['latitude'] ?? 14.5995) ?>, <?= (float)($h['longitude'] ?? 120.9842) ?>)">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="fw-bold text-dark"><?= htmlspecialchars($h['name']) ?></span>
                      <?php if (!empty($h['latitude']) && !empty($h['longitude'])): ?>
                        <span class="badge bg-light-success text-success stat-badge"><i class="ti ti-map-pin"></i> Mapped</span>
                      <?php else: ?>
                        <span class="badge bg-light-secondary text-secondary stat-badge">No Coords</span>
                      <?php endif; ?>
                    </div>
                    <small class="text-muted d-block"><?= htmlspecialchars($h['address'] ?? 'No address') ?></small>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    
    <?php include __DIR__ . '/components/footer.php'; ?>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([14.5995, 120.9842], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

var houses = <?= json_encode($houses) ?>;
var markers = [];

houses.forEach(function(h) {
    if (h.latitude && h.longitude) {
        var marker = L.marker([parseFloat(h.latitude), parseFloat(h.longitude)]).addTo(map);
        marker.bindPopup('<strong>' + h.name + '</strong><br>' + (h.address || ''));
        markers.push(marker);
    }
});

function focusProperty(lat, lng) {
    if (lat && lng) {
        map.setView([lat, lng], 16);
    }
}
</script>
</body>
</html>
