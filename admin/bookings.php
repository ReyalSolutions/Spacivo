<?php
require_once __DIR__ . '/components/auth_check.php';

$msg = null;
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    
    if ($bookingId > 0 && in_array($action, ['approved', 'rejected', 'cancelled'], true)) {
        $stmt = $db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $bookingId);
        if ($stmt->execute()) {
            $msg = "Booking marked as " . ucfirst($action) . " successfully!";
        } else {
            $msg = "Failed to update booking: " . $db->error;
            $msgType = 'danger';
        }
    }
}

// Fetch bookings with tenant, house, and room details
$query = "
    SELECT b.*, 
           CONCAT(u.first_name, ' ', u.last_name) as tenant_name,
           u.email as tenant_email,
           u.phone as tenant_phone,
           r.room_name,
           r.price as room_price,
           bh.name as house_name
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN rooms r ON b.room_id = r.id
    LEFT JOIN boarding_houses bh ON r.boarding_house_id = bh.id
    ORDER BY b.id DESC
";
$bookingsRes = $db->query($query);
$bookingsList = $bookingsRes ? $bookingsRes->fetch_all(MYSQLI_ASSOC) : [];

$totalBookings = count($bookingsList);
$pendingBookings = count(array_filter($bookingsList, fn($b) => ($b['status'] ?? '') === 'pending'));
$approvedBookings = count(array_filter($bookingsList, fn($b) => ($b['status'] ?? '') === 'approved'));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bookings – <?= htmlspecialchars($siteName) ?> Admin</title>
  <?php include __DIR__ . '/components/links.php'; ?>
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
          <h4 class="fw-bold mb-1 text-dark">Tenant Bookings</h4>
          <p class="text-muted small mb-0">Overview of room reservations and rental periods</p>
        </div>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
          <?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Quick KPI Counters -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-primary text-primary"><i class="ti ti-calendar-event"></i></div>
              <div>
                <small class="text-muted d-block">Total Reservations</small>
                <h5 class="fw-bold mb-0"><?= $totalBookings ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-warning text-warning"><i class="ti ti-clock-pause"></i></div>
              <div>
                <small class="text-muted d-block">Pending Confirmation</small>
                <h5 class="fw-bold mb-0 text-warning"><?= $pendingBookings ?></h5>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon bg-light-success text-success"><i class="ti ti-check"></i></div>
              <div>
                <small class="text-muted d-block">Approved / Confirmed</small>
                <h5 class="fw-bold mb-0 text-success"><?= $approvedBookings ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bookings Table -->
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="bookingsTable">
              <thead class="table-light">
                <tr>
                  <th class="border-0">Tenant</th>
                  <th class="border-0">Property & Room</th>
                  <th class="border-0">Stay Dates</th>
                  <th class="border-0">Amount</th>
                  <th class="border-0">Status</th>
                  <th class="border-0 text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bookingsList as $b): ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($b['tenant_name'] ?? 'Unknown Tenant') ?></div>
                      <small class="text-muted"><?= htmlspecialchars($b['tenant_email'] ?? '') ?></small>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($b['house_name'] ?? 'House') ?></span>
                      <small class="text-muted"><i class="ti ti-door me-1"></i><?= htmlspecialchars($b['room_name'] ?? 'Room') ?></small>
                    </td>
                    <td>
                      <div class="small fw-medium text-dark"><?= date('M j, Y', strtotime($b['start_date'])) ?></div>
                      <small class="text-muted">to <?= date('M j, Y', strtotime($b['end_date'])) ?></small>
                    </td>
                    <td>
                      <span class="fw-bold text-dark">₱<?= number_format((float)($b['total_amount'] ?? 0), 2) ?></span>
                    </td>
                    <td>
                      <?php if ($b['status'] === 'approved'): ?>
                        <span class="badge bg-light-success text-success stat-badge">Approved</span>
                      <?php elseif ($b['status'] === 'pending'): ?>
                        <span class="badge bg-light-warning text-warning stat-badge">Pending</span>
                      <?php elseif ($b['status'] === 'cancelled'): ?>
                        <span class="badge bg-light-secondary text-secondary stat-badge">Cancelled</span>
                      <?php else: ?>
                        <span class="badge bg-light-danger text-danger stat-badge"><?= ucfirst($b['status']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <?php if ($b['status'] === 'pending'): ?>
                        <form method="POST" class="d-inline">
                          <input type="hidden" name="action" value="approved">
                          <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                            Confirm
                          </button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
    
    <?php include __DIR__ . '/components/footer.php'; ?>
  </div>
</div>

<?php include __DIR__ . '/components/scripts.php'; ?>
<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable({
        pageLength: 10,
        language: { search: "_INPUT_", searchPlaceholder: "Search reservations..." }
    });
});
</script>
</body>
</html>
