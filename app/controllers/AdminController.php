<?php
declare(strict_types=1);

final class AdminController extends BaseController
{
    public function index(): void
    {
        $this->requireActionPermission('index');


        $role = $_SESSION['role'] ?? '';
        $data = [];

        if ($role === 'admin') {
            $data['pageTitle'] = 'System Administrator Dashboard';
            $data['roleLabel'] = 'Admin';
            $data['firstName'] = $_SESSION['first_name'] ?? 'Admin';

            $userModel = new User($this->db());
            $houseModel = new BoardingHouse($this->db());
            $bookingModel = new Booking($this->db());
            $paymentModel = new Payment($this->db());

            // 1. Top Summary Cards
            $users = $userModel->all();
            $data['totalUsers'] = count($users);
            $data['totalOwners'] = count(array_filter($users, fn($u) => $u['role'] === 'owner'));
            $data['totalTenants'] = count(array_filter($users, fn($u) => strtolower($u['role'] ?? '') === 'tenant'));
            $data['totalStaff'] = count(array_filter($users, fn($u) => strtolower($u['role'] ?? '') === 'staff' || strtolower($u['role'] ?? '') === 'admin'));

            $houses = $houseModel->all();
            $data['totalHouses'] = count($houses);
            $data['pendingApprovals'] = count(array_filter($houses, fn($h) => ($h['status'] ?? 'pending') === 'pending'));

            $bookings = $bookingModel->all();
            $data['totalBookings'] = count($bookings);

            $subModel = new Subscription($this->db());
            $planPayments = $this->db()->query("SELECT amount FROM plan_payments WHERE status = 'paid'")->fetch_all(MYSQLI_ASSOC);
            $subRevenue = array_reduce($planPayments, fn($carry, $item) => $carry + (float)($item['amount'] ?? 0), 0.0);

            $data['totalRevenue'] = $subRevenue;
            $data['activeSubscriptions'] = $this->db()->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'")->fetch_row()[0];

            // 2. Recent Activities (Real Data)
            $activityModel = new ActivityLog($this->db());
            $data['recentActivities'] = $activityModel->getRecentLogs(5);

            // 3. Revenue Analytics (Subscriptions Only)
            $subTrend = $subModel->getSubscriptionTrend('monthly');

            $trendLabels = [];
            $trendValues = [];
            foreach ($subTrend as $s) {
                $trendLabels[] = $s['label'];
                $trendValues[] = (float)$s['total'];
            }

            $data['revenueTrend'] = [
                'labels' => $trendLabels,
                'data'   => $trendValues
            ];

            // 4. User Growth Analytics (Real Data)
            $data['userGrowth'] = $userModel->getGrowthStats(6);

            // 5. Pending Approvals list
            $data['approvalList'] = array_slice(array_reverse(array_filter($houses, fn($h) => ($h['status'] ?? 'pending') === 'pending')), 0, 5);
        } elseif ($role === 'owner') {
            $data['pageTitle'] = 'Property Owner Dashboard';
            $data['roleLabel'] = 'Owner';
            $data['firstName'] = $_SESSION['first_name'] ?? 'Owner';

            $ownerId = (int)$_SESSION['user_id'];
            $houseModel = new BoardingHouse($this->db());
            $bookingModel = new Booking($this->db());
            $paymentModel = new Payment($this->db());
            $userModel = new User($this->db());
            $subModel = new Subscription($this->db());

            // 1. My Houses (With Room Stats)
            $myHouses = $houseModel->getByOwnerIdWithStats($ownerId);
            $data['myHouses'] = $myHouses;
            $data['totalHouses'] = count($myHouses);

            // 2. Room & Occupancy Stats
            $roomStats = $houseModel->getRoomStatsByOwner($ownerId);
            $data['totalRooms'] = (int)($roomStats['total_rooms'] ?? 0);
            $data['occupiedSlots'] = (int)($roomStats['occupied_slots'] ?? 0);
            $data['availableSlots'] = (int)($roomStats['available_slots'] ?? 0);
            $data['occupancyRate'] = $data['totalRooms'] > 0 ? round(($data['occupiedSlots'] / ($data['totalRooms'] * 2)) * 100) : 0; // Assuming 2 slots/room avg

            // 3. Bookings
            $myBookings = $bookingModel->getForOwner($ownerId);
            $data['totalBookings'] = count($myBookings);
            $data['pendingRequests'] = array_filter($myBookings, fn($b) => ($b['status'] ?? '') === 'pending');
            $data['approvedBookings'] = array_filter($myBookings, fn($b) => ($b['status'] ?? '') === 'approved');

            // 4. Earnings (Real Paid Revenue)
            $data['earnings'] = $paymentModel->getTotalRevenueForOwner($ownerId);

            // 5. Recent Activity (Real Data)
            $activityModel = new ActivityLog($this->db());
            $data['recentActivities'] = $activityModel->getRecentLogs(5, $ownerId);

            // 6. Earnings Analytics (Real Data)
            $ownerTrend = $paymentModel->getRevenueTrend($ownerId, 'daily', 7);
            $trendLabels = [];
            $trendValues = [];

            // Default to last 7 days even if no data
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                $short = date('D', strtotime($d));
                $trendLabels[] = $short;
                $val = 0;
                foreach ($ownerTrend as $ot) {
                    if ($ot['label'] === $d) {
                        $val = (float)$ot['total'];
                        break;
                    }
                }
                $trendValues[] = $val;
            }

            $data['earningsTrend'] = [
                'labels' => $trendLabels,
                'data'   => $trendValues
            ];

            // 7. Subscription Data (Real Data)
            $subStatus = $subModel->getOwnerSubscriptionStatus($ownerId);
            $hasActiveSub = ($subStatus !== null && ($subStatus['status'] ?? '') === 'active');
            $limit = (int)($subStatus['room_limit'] ?? 0);
            $usage = $data['totalRooms'];
            $usageStr = ($limit > 0) ? "$usage/$limit rooms used" : "$usage rooms used";

            $data['hasActiveSub'] = $hasActiveSub;
            $data['activeSubId'] = (int)($subStatus['id'] ?? 0);
            $data['subscription'] = [
                'plan' => $subStatus['plan_name'] ?? 'No Active Plan',
                'expiry' => $subStatus['current_cycle_end'] ?? 'N/A',
                'usage' => $usageStr,
                'percent' => ($limit > 0) ? min(100, (int)round(($usage / $limit) * 100)) : 0
            ];

            require_once __DIR__ . '/../models/SystemSetting.php';
            $psModel = new SystemSetting($this->db());
            $data['paymentSettings'] = $psModel->getAll();
        }

        $this->render('admin/index', $data);
    }

    public function approve_house(): void
    {
        $this->requireActionPermission('approve_house');
        $this->requireRole(['admin']);

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $houseId = (int)($_POST['house_id'] ?? 0);
        if (!$houseId) {
            echo json_encode(['success' => false, 'message' => 'Property ID is required.']); return;
        }

        $bhModel = new BoardingHouse($this->db());
        if ($bhModel->updateStatus($houseId, 'approved')) {
            $house = $bhModel->getById($houseId);
            $logger = new ActivityLog($this->db());
            $logger->log((int)$_SESSION['user_id'], 'ADMIN_HOUSE_APPROVED', ['house_id' => $houseId, 'name' => $house['name'] ?? '']);
            echo json_encode(['success' => true, 'message' => 'Boarding house has been approved.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update property status.']);
        }
    }

    public function reject_house(): void
    {
        $this->requireActionPermission('reject_house');
        $this->requireRole(['admin']);

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $houseId = (int)($_POST['house_id'] ?? 0);
        $reason  = Security::sanitize($_POST['reason'] ?? 'Does not meet listing standards.');
        if (!$houseId) {
            echo json_encode(['success' => false, 'message' => 'Property ID is required.']); return;
        }

        $bhModel = new BoardingHouse($this->db());
        if ($bhModel->updateStatus($houseId, 'rejected')) {
            $logger = new ActivityLog($this->db());
            $logger->log((int)$_SESSION['user_id'], 'ADMIN_HOUSE_REJECTED', ['house_id' => $houseId, 'reason' => $reason]);
            echo json_encode(['success' => true, 'message' => 'Boarding house has been rejected.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update property status.']);
        }
    }

    public function bookings(): void
    {
        $this->requireActionPermission('bookings');

        $db       = $this->db();
        $role     = strtolower($_SESSION['role'] ?? 'owner');
        $ownerId  = (int)$_SESSION['user_id'];
        $bhModel  = new BoardingHouse($db);
        if ($role === 'admin') {
            $houses = $bhModel->all();
            $roleLabel = 'Admin';
        } else {
            $houses = $bhModel->getByOwnerId($ownerId);
            $roleLabel = 'Owner';
        }
        $this->render('admin/bookings', [
            'houses' => $houses, 
            'roleLabel' => $roleLabel,
            'pageTitle' => 'Tenants Management'
        ]);
    }

    public function bookings_data(): void
    {
        $this->requireActionPermission('bookings_data');


        $ownerId = (int)$_SESSION['user_id'];
        $role    = $_SESSION['role'] ?? '';

        // DataTables server-side params
        $draw   = (int)($_GET['draw'] ?? 1);
        $start  = (int)($_GET['start'] ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');
        $orderColIdx = (int)($_GET['order'][0]['column'] ?? 0);
        $orderDir    = strtolower($_GET['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        $cols = ['b.id', 'tenant_name', 'bh.name', 'r.room_name', 'b.status', 'b.start_date'];
        $orderCol = $cols[$orderColIdx] ?? 'b.id';

        $db = $this->db();

        // Base WHERE
        $where = $role === 'admin' ? '1=1' : 'bh.owner_id = ?';
        $searchSql = '';
        $params    = [];
        $types     = '';

        if ($role !== 'admin') {
            $params[] = $ownerId;
            $types   .= 'i';
        }

        // Boarding house filter
        $bhouseId = (int)($_GET['bhouse_id'] ?? 0);
        $bhouseSql = '';
        if ($bhouseId > 0) {
            $bhouseSql = ' AND bh.id = ?';
            $params[] = $bhouseId;
            $types   .= 'i';
        }

        if ($search !== '') {
            $searchSql = " AND (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR bh.name LIKE ? OR r.room_name LIKE ? OR b.status LIKE ?)";
            $like = "%{$search}%";
            $params = array_merge($params, [$like, $like, $like, $like]);
            $types .= 'ssss';
        }

        $baseSql = "
            FROM bookings b
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            INNER JOIN users u ON u.id = b.user_id
            WHERE {$where}{$bhouseSql}{$searchSql}";

        // Total records
        $countStmt = $db->prepare("SELECT COUNT(*) $baseSql");
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $totalFiltered = (int)$countStmt->get_result()->fetch_row()[0];

        // Total unfiltered
        $totalWhere = $role === 'admin' ? '1=1' : 'bh.owner_id = ?';
        $totalStmt  = $db->prepare("SELECT COUNT(*) FROM bookings b INNER JOIN rooms r ON r.id=b.room_id INNER JOIN boarding_houses bh ON bh.id=r.boarding_house_id WHERE $totalWhere");
        if ($role !== 'admin') {
            $totalStmt->bind_param('i', $ownerId);
        }
        $totalStmt->execute();
        $totalRecords = (int)$totalStmt->get_result()->fetch_row()[0];

        // Data
        $dataParams   = array_merge($params, [$length, $start]);
        $dataTypes    = $types . 'ii';
        $dataStmt = $db->prepare("
            SELECT b.id, b.status, b.is_moved_out, b.start_date, b.end_date, b.total_amount,
                   r.room_name, bh.id AS boarding_house_id, bh.name AS boarding_house_name, bh.address,
                   CONCAT(u.first_name, ' ', u.last_name) AS tenant_name
            $baseSql
            ORDER BY {$orderCol} {$orderDir}
            LIMIT ? OFFSET ?");
        $dataStmt->bind_param($dataTypes, ...$dataParams);
        $dataStmt->execute();
        $rows = $dataStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $status = $row['status'] ?? 'pending';
            if ($status === 'approved') {
                $badge = '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1"><i class="fa-solid fa-circle-check me-1"></i>Approved</span>';
            } else {
                $badge = '<span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3 py-1"><i class="fa-solid fa-clock me-1"></i>' . ucfirst(htmlspecialchars($status)) . '</span>';
            }

            $initial = strtoupper(substr($row['tenant_name'] ?? 'U', 0, 1));
            $tenantHtml = '<div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:0.8rem;">'.$initial.'</div>
                <span class="fw-semibold">'.htmlspecialchars($row['tenant_name'] ?? 'N/A').'</span></div>';

            $csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
            $bookingId = (int)$row['id'];
            $moveOutBtn = !$row['is_moved_out']
                ? '<button type="button"
                        class="btn-move-out d-inline-flex align-items-center gap-1"
                        data-id="'.$bookingId.'"
                        data-csrf="'.$csrfToken.'"
                        style="background:linear-gradient(135deg,#fee2e2,#fecaca);color:#b91c1c;border:1px solid #fca5a5;border-radius:20px;padding:5px 14px;font-size:0.8rem;font-weight:700;cursor:pointer;transition:all 0.2s;">
                        <i class="fa-solid fa-person-walking-arrow-right"></i> Move Out
                   </button>'
                : '<span class="d-inline-flex align-items-center gap-1" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#065f46;border:1px solid #6ee7b7;border-radius:20px;padding:5px 14px;font-size:0.8rem;font-weight:700;">
                        <i class="fa-solid fa-house-circle-check"></i> Moved Out
                   </span>';

            $data[] = [
                'DT_RowId' => 'row_' . $row['id'],
                $row['id'],
                $tenantHtml,
                htmlspecialchars($row['boarding_house_name'] ?? 'N/A'),
                '<span class="badge bg-light text-dark border">'.htmlspecialchars($row['room_name'] ?? 'N/A').'</span>',
                $badge,
                date('M d, Y', strtotime($row['start_date'] ?? 'now')),
                $moveOutBtn,
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['draw' => $draw, 'recordsTotal' => $totalRecords, 'recordsFiltered' => $totalFiltered, 'data' => $data]);
        exit;
    }

    public function houses(): void
    {
        $this->requireActionPermission('houses');
        $this->requireRole(['admin', 'owner']);


        $filter = explode('/', $_GET['url'] ?? '')[2] ?? null;
        $ownerFilter = isset($_GET['owner_id']) && $_GET['owner_id'] !== '' ? (int)$_GET['owner_id'] : null;

        // Granular Sub-path Enrollment
        if ($filter === 'pending') {
            $this->requirePermission('approve_houses');
        }

        // Fetch owners for the filter dropdown (if admin)
        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $owners = [];
        if ($role === 'admin') {
            $owners = $this->db()->query("SELECT id, first_name, last_name FROM users WHERE role_id = 2")->fetch_all(MYSQLI_ASSOC);
        }

        $listingModel = new BoardingHouse($this->db());
        $ownerId = (int)$_SESSION['user_id'];
        $subscription = (new Subscription($this->db()))->getOwnerSubscriptionStatus($ownerId);
        $listingCount = $role === 'admin'
            ? (int)$this->db()->query('SELECT COUNT(*) FROM boarding_houses WHERE is_deleted = 0')->fetch_row()[0]
            : $listingModel->countByOwnerId($ownerId);

        $this->render('admin/houses', [
            'houses' => [], // Now data-driven via AJAX Orchestration
            'filter' => $filter,
            'roleLabel' => $roleLabel,
            'pageTitle' => 'Properties Management',
            'owners' => $owners,
            'ownerFilter' => $ownerFilter,
            'totalHousesCount' => $listingCount,
            'activeSubId' => $subscription ? (int)$subscription['id'] : 0,
            'allPlans' => (new Plan($this->db()))->getAll(),
            'paymentSettings' => (new SystemSetting($this->db()))->getAll(),
        ]);
    }

    public function houses_data(): void
    {
        $this->requireActionPermission('houses_data');
        $this->requireRole(['admin', 'owner']);


        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';
        $userId = (int)$_SESSION['user_id'];

        // DataTables server-side params
        $draw   = (int)($_GET['draw'] ?? 1);
        $start  = (int)($_GET['start'] ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');
        $orderColIdx = (int)($_GET['order'][0]['column'] ?? 0);
        $orderDir    = strtolower($_GET['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        // Filters from URL/AJAX
        $statusFilter = $_GET['status_filter'] ?? null; // 'pending' or null
        $ownerFilter = isset($_GET['owner_id']) && $_GET['owner_id'] !== '' ? (int)$_GET['owner_id'] : null;

        $db = $this->db();
        $houseModel = new BoardingHouse($db);

        // Base WHERE
        $whereSql = "bh.is_deleted = 0";
        $params = [];
        $types = "";

        if ($role === 'owner') {
            $whereSql .= " AND bh.owner_id = ?";
            $params[] = $userId;
            $types .= "i";
        } elseif ($role === 'admin' && $ownerFilter) {
            $whereSql .= " AND bh.owner_id = ?";
            $params[] = $ownerFilter;
            $types .= "i";
        }

        if ($statusFilter === 'pending') {
            $whereSql .= " AND bh.status = 'pending'";
        }

        if ($search !== '') {
            $whereSql .= " AND (bh.name LIKE ? OR bh.address LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $like = "%{$search}%";
            $params = array_merge($params, [$like, $like, $like, $like]);
            $types .= "ssss";
        }

        $baseSql = "
            FROM boarding_houses bh
            JOIN users u ON bh.owner_id = u.id
            WHERE $whereSql";

        // Total filtered
        $countStmt = $db->prepare("SELECT COUNT(*) $baseSql");
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $totalFilteredResult = $countStmt->get_result()->fetch_row();
        $totalFiltered = (int)($totalFilteredResult[0] ?? 0);

        // Total unfiltered
        $unfilteredWhere = ($role === 'owner') ? "owner_id = ? AND is_deleted = 0" : "is_deleted = 0";
        $totalStmt = $db->prepare("SELECT COUNT(*) FROM boarding_houses WHERE $unfilteredWhere");
        if ($role === 'owner') {
            $totalStmt->bind_param("i", $userId);
        }
        $totalStmt->execute();
        $totalRecordsResult = $totalStmt->get_result()->fetch_row();
        $totalRecords = (int)($totalRecordsResult[0] ?? 0);

        // Fetch Data
        $dataParams = array_merge($params, [$length, $start]);
        $dataTypes = $types . "ii";

        // Sorting
        $cols = ['bh.id', 'bh.name', 'bh.address', 'u.first_name', 'bh.status', 'bh.created_at'];
        $orderCol = $cols[$orderColIdx] ?? 'bh.created_at';

        $dataStmt = $db->prepare("
            SELECT bh.*, u.first_name, u.last_name
            $baseSql
            ORDER BY $orderCol $orderDir
            LIMIT ? OFFSET ?");
        $dataStmt->bind_param($dataTypes, ...$dataParams);
        $dataStmt->execute();
        $houses = $dataStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $data = [];
        foreach ($houses as $house) {
            $houseId = $house['id'];
            $houseImages = $houseModel->getImages((int)$houseId);

            // Fetch starting price
            $pStmt = $db->prepare("SELECT MIN(price) as min_price FROM rooms WHERE boarding_house_id = ?");
            $pStmt->bind_param('i', $houseId);
            $pStmt->execute();
            $pRes = $pStmt->get_result()->fetch_assoc();
            $startingPrice = (float)($pRes['min_price'] ?? 0);

            $carouselId = "carouselHouse" . $houseId;
            $status = strtolower($house['status'] ?? 'pending');
            $statusClass = $status === 'approved' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning');

            // Render Card HTML
            ob_start();
            ?>
            <div class="col-12 col-md-6 col-xl-4 house-card-item mb-4">
                <div class="premium-stat-card d-block p-0 overflow-hidden h-100 shadow-sm border transition-hover">
                    <!-- Image Carousel -->
                    <div id="<?= $carouselId ?>" class="carousel slide" data-bs-ride="carousel" style="height: 220px;">
                        <input type="hidden" class="carousel-init-trigger" value="<?= $carouselId ?>">
                        <div class="carousel-inner h-100">
                            <?php if (empty($houseImages)): ?>
                                <div class="carousel-item active h-100">
                                    <div class="h-100 w-100 d-flex align-items-center justify-content-center bg-light" style="background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.3)), url('/tenant/public/assets/images/house-placeholder.jpg') center/cover no-repeat;">
                                        <i class="fa-solid fa-image text-white fs-1 opacity-25"></i>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($houseImages as $idx => $img): ?>
                                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?> h-100">
                                        <div class="h-100 w-100" style="background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.3)), url('<?= htmlspecialchars($img['image_path']) ?>') center/cover no-repeat;"></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if (count($houseImages) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true" style="width: 1.5rem; height: 1.5rem;"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true" style="width: 1.5rem; height: 1.5rem;"></span>
                            </button>
                        <?php endif; ?>

                        <div class="position-absolute top-0 start-0 p-3" style="z-index: 10;">
                            <span class="badge rounded-pill <?= $statusClass ?> px-3 py-2 shadow-sm fw-bold small text-uppercase" style="letter-spacing: 0.5px;">
                                <?= $status ?>
                            </span>
                        </div>

                        <div class="position-absolute bottom-0 end-0 p-3" style="z-index: 10;">
                            <div class="bg-white rounded-3 p-2 px-3 shadow-lg border border-primary border-opacity-10 text-center">
                                <div class="text-muted extra-small fw-bold text-uppercase mb-0" style="font-size: 0.65rem; line-height: 1;">Starts at</div>
                                <div class="text-primary fw-extrabold" style="font-size: 1.1rem; line-height: 1.1;">
                                    <span class="small">₱</span><?= number_format($startingPrice, 0) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="mb-3">
                            <h4 class="fw-extrabold m-0 text-dark text-truncate" title="<?= htmlspecialchars($house['name']) ?>">
                                <?= htmlspecialchars($house['name'] ?? 'Untitled Property') ?>
                            </h4>

                            <?php if ($roleLabel === 'Admin'): ?>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                        <i class="fa-solid fa-user-tie" style="font-size: 0.7rem;"></i>
                                    </div>
                                    <span class="text-secondary small fw-bold">
                                        Owner: <span class="text-dark"><?= htmlspecialchars($house['first_name'] . ' ' . $house['last_name']) ?></span>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-4 line-clamp-2" style="height: 38px;">
                            <i class="fa-solid fa-location-dot me-1 text-primary text-opacity-75"></i> 
                            <?= htmlspecialchars($house['address'] ?? 'No address provided') ?>
                        </p>

                        <?php if ($roleLabel === 'Admin'): ?>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div class="d-flex gap-1">
                                <?php if ($this->hasPermission('approve_houses') && $status === 'pending'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="moderateListing(<?= $houseId ?>, 'approve')">Approve</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="moderateListing(<?= $houseId ?>, 'reject')">Reject</button>
                                <?php endif; ?>
                                </div>
                                <a href="/tenant/?url=admin/bookings&bhouse_id=<?= $houseId ?>" class="btn btn-sm btn-primary rounded-pill px-4 fw-extrabold shadow-sm ripple-button">
                                    Open Dashboard <i class="fa-solid fa-arrow-right ms-1 small"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ((int)$house['owner_id'] === $userId): ?>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <?php if ($this->hasPermission('edit_houses')): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditModal(<?= htmlspecialchars(json_encode($house), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openImageModal(<?= $houseId ?>, <?= htmlspecialchars(json_encode($house['name']), ENT_QUOTES, 'UTF-8') ?>)">Photos</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAmenitiesModal(<?= $houseId ?>, <?= htmlspecialchars(json_encode($house['name']), ENT_QUOTES, 'UTF-8') ?>)">Amenities</button>
                            <?php endif; ?>
                            <?php if ($this->hasPermission('delete_houses')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $houseId ?>, <?= htmlspecialchars(json_encode($house['name']), ENT_QUOTES, 'UTF-8') ?>)">Delete</button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
            $cardHtml = ob_get_clean();

            $data[] = [
                $cardHtml // Single column for the card
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['draw' => $draw, 'recordsTotal' => $totalRecords, 'recordsFiltered' => $totalFiltered, 'data' => $data]);
        exit;
    }

    public function users(): void
    {
        $this->requireActionPermission('users');


        $filter = explode('/', $_GET['url'] ?? '')[2] ?? null;

        // Granular Sub-path Enforcements
        if ($filter === 'owners') {
            $this->requirePermission('manage_owners');
        } elseif ($filter === 'tenants') {
            $this->requirePermission('manage_tenants');
        }

        $userModel = new User($this->db());
        $users = $userModel->all();

        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        if ($filter === 'owners') {
            $users = array_filter($users, fn($u) => $u['role'] === 'owner');
        } elseif ($filter === 'tenants') {
            $users = array_filter($users, fn($u) => $u['role'] === 'tenant');
        }

        $this->render('admin/users', [
            'users' => $users, 
            'filter' => $filter,
            'roleLabel' => $roleLabel,
            'pageTitle' => 'User Management'
        ]);
    }

    public function roles(): void
    {
        $this->requireRole(['admin']);
        $this->requireActionPermission('roles');
        $roleModel = new Role($this->db());
        $data = ['roles' => $roleModel->all(), 'permissionsByCategory' => (new Permission($this->db()))->getByCategory()];
        foreach ($data['roles'] as &$role) $role['permissions'] = $roleModel->getPermissions((int)$role['id']);
        unset($role);
        $this->render('admin/roles', $data);
    }

    public function store_role(): void
    {
        $this->requireRole(['admin']);
        $this->requireActionPermission('store_role');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/roles');
        }

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $permissions = $_POST['permissions'] ?? [];
        if (!is_array($permissions)) $this->json(['success' => false, 'message' => 'Invalid permissions.'], 422);

        if (empty($name)) {
            $_SESSION['error'] = "Role name is required.";
            $this->redirect('admin/roles');
        }

        $roleModel = new Role($this->db());
        $logger = new ActivityLog($this->db());
        $slug = strtolower(str_replace(' ', '_', $name));

        $roleId = $roleModel->create($name, $slug, $description);

        if ($roleId) {
            $roleModel->syncPermissions((int)$roleId, array_map('intval', $permissions));
            $logger->log($_SESSION['user_id'], 'CREATE_ROLE', [
                'role_name' => $name,
                'slug' => $slug,
                'permissions_count' => count($permissions)
            ], (int)$roleId);
            $_SESSION['success'] = "New role '{$name}' has been defined successfully.";
        } else {
            $_SESSION['error'] = "Failed to define new role.";
        }

        $this->redirect('admin/roles');
    }

    public function update_role(): void
    {
        $this->requireRole(['admin']);
        $this->requireActionPermission('update_role');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/roles');
        }

        $roleId = (int)($_POST['role_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($roleId <= 0 || empty($name)) {
            $this->redirect('admin/roles', 'error', 'Role name and valid ID are required.');
        }

        $roleModel = new Role($this->db());
        $logger = new ActivityLog($this->db());
        if ($roleModel->update($roleId, $name, $description)) {
            $logger->log($_SESSION['user_id'], 'UPDATE_ROLE_METADATA', [
                'role_id' => $roleId,
                'new_name' => $name,
                'new_description' => $description
            ], $roleId);
            $this->redirect('admin/roles', 'success', "Role '{$name}' updated successfully.");
        } else {
            $this->redirect('admin/roles', 'error', 'Failed to update role.');
        }
    }

    public function update_role_permissions(): void
    {
        $this->requireRole(['admin']);
        $this->requireActionPermission('update_role_permissions');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/roles');
        }

        $roleId = (int)($_POST['role_id'] ?? 0);
        $permissions = $_POST['permissions'] ?? [];
        if (!is_array($permissions)) $this->json(['success' => false, 'message' => 'Invalid permissions.'], 422);

        if ($roleId <= 0) {
            $this->redirect('admin/roles', 'error', 'Invalid role target.');
        }

        $roleModel = new Role($this->db());
        $logger = new ActivityLog($this->db());
        if ($roleModel->syncPermissions($roleId, array_map('intval', $permissions))) {
            $role = $roleModel->findById($roleId);
            $roleName = $role['name'] ?? 'Role';
            $logger->log($_SESSION['user_id'], 'SYNC_ROLE_PERMISSIONS', [
                'role_name' => $roleName,
                'permissions_assigned' => count($permissions)
            ], $roleId);
            $this->redirect('admin/roles', 'success', "Permissions for '{$roleName}' synchronized successfully.");
        } else {
            $this->redirect('admin/roles', 'error', 'Failed to synchronize permissions.');
        }
    }

    public function delete_role(): void
    {
        $this->requireRole(['admin']);
        $this->requireActionPermission('delete_role');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/roles');
        }

        $roleId = (int)($_POST['role_id'] ?? 0);

        // Prevent deleting standard roles (Admin=1, Owner=2, Tenant=3)
        if ($roleId <= 3) {
            $this->redirect('admin/roles', 'error', 'Standard system roles cannot be deleted.');
        }

        // Check if users are assigned to this role
        $stmt = $this->db()->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        $count = 0;
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $this->redirect('admin/roles', 'error', 'Cannot delete role while users are still assigned to it.');
        }

        $roleModel = new Role($this->db());
        $logger = new ActivityLog($this->db());
        $role = $roleModel->findById($roleId);

        if ($roleModel->delete($roleId)) {
            $logger->log($_SESSION['user_id'], 'DELETE_ROLE', [
                'role_name' => $role['name'] ?? 'Unknown',
                'slug' => $role['slug'] ?? 'N/A'
            ]);
            $this->redirect('admin/roles', 'success', 'Role has been removed successfully.');
        } else {
            $this->redirect('admin/roles', 'error', 'Failed to remove role.');
        }
    }

    public function store_permission(): void
    {
        $this->requireRole(['admin']);
        $this->requireActionPermission('store_permission');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/roles');
        }

        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'core');
        $description = trim($_POST['description'] ?? '');
        $slug = strtolower(str_replace(' ', '_', $name));

        if (empty($name)) {
            $this->redirect('admin/roles', 'error', 'Permission name is required.');
        }

        $permModel = new Permission($this->db());
        $logger = new ActivityLog($this->db());
        if ($permModel->exists($slug)) {
            $this->redirect('admin/roles', 'error', 'Permission slug already exists.');
        }

        if ($permModel->create($name, $slug, $category, $description)) {
            $logger->log($_SESSION['user_id'], 'CREATE_PERMISSION', [
                'perm_name' => $name,
                'category' => $category,
                'slug' => $slug
            ]);
            $this->redirect('admin/roles', 'success', "Permission '{$name}' defined successfully.");
        } else {
            $this->redirect('admin/roles', 'error', 'Failed to define permission.');
        }
    }

    public function delete_permission(): void
    {
        $this->requireRole(['admin']);
        $this->requireActionPermission('delete_permission');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/roles');
        }

        $permId = (int)($_POST['permission_id'] ?? 0);
        $permModel = new Permission($this->db());
        $logger = new ActivityLog($this->db());

        $perm = $permModel->findById($permId);
        if (!$perm) {
            $this->redirect('admin/roles', 'error', 'Permission not found.');
        }

        if ($permModel->delete($permId)) {
            $logger->log($_SESSION['user_id'], 'DELETE_PERMISSION', [
                'perm_name' => $perm['name'],
                'slug' => $perm['slug']
            ]);
            $this->redirect('admin/roles', 'success', "Permission '{$perm['name']}' deleted successfully.");
        } else {
            $this->redirect('admin/roles', 'error', 'Failed to delete permission.');
        }
    }


    public function store_user(): void

    {
        $this->requireActionPermission('store_user');


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            echo 'Invalid CSRF token';
            return;
        }

        $firstName = Security::sanitize($_POST['first_name'] ?? '');
        $middleName = Security::sanitize($_POST['middle_name'] ?? '');
        $lastName = Security::sanitize($_POST['last_name'] ?? '');
        $username = Security::sanitize($_POST['username'] ?? '');
        $email = Security::sanitize($_POST['email'] ?? '');
        $phone = Security::sanitize($_POST['phone'] ?? '');
        $roleStr = strtolower(Security::sanitize($_POST['role'] ?? 'tenant'));
        $password = App\Modules\Identity\Services\RegistrationPolicy::passwordFromInput($_POST['password'] ?? '');
        $confirm = App\Modules\Identity\Services\RegistrationPolicy::passwordFromInput($_POST['confirm_password'] ?? '');

        // Role Mapping
        $roleMap = ['admin' => 1, 'owner' => 2, 'tenant' => 3];
        $roleId = $roleMap[$roleStr] ?? 3;

        $userModel = new User($this->db());
        $logger = new ActivityLog($this->db());

        // Identity Uniqueness Guardrails
        if ($userModel->isUsernameTaken($username)) {
            $_SESSION['error'] = 'The username "@' . $username . '" is already occupied.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        if ($userModel->isEmailTaken($email)) {
            $_SESSION['error'] = 'This email address is already registered to another identity.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        if ($userModel->isPhoneTaken($phone)) {
            $_SESSION['error'] = 'This contact number is already linked to a system account.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        // Validation (Basic)
        if ($email === '' || $username === '' || $firstName === '' || $lastName === '' || $password === '') {
            $_SESSION['error'] = 'Required fields missing.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Passwords do not match.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        // Provision identity with manual password
        $newId = $userModel->create($firstName, $middleName, $lastName, $email, $phone, $roleId, $username, $password);

        if ($newId) {
            $logger->log($_SESSION['user_id'], 'CREATE_USER', [
                'identity' => [
                    'full_name' => "$firstName $middleName $lastName",
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => $roleStr
                ],
                'security' => 'Manual Provisioning (Encrypted)'
            ], $newId);
        }

        $_SESSION['success'] = 'User successfully provisioned with temporary credentials.';
        header('Location: /tenant/?url=admin/users');
        exit;
    }

    public function update_user(): void
    {
        $this->requireActionPermission('update_user');


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            echo 'Invalid CSRF token';
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            $_SESSION['error'] = 'Invalid identity target.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        $firstName = Security::sanitize($_POST['first_name'] ?? '');
        $middleName = Security::sanitize($_POST['middle_name'] ?? '');
        $lastName = Security::sanitize($_POST['last_name'] ?? '');
        $username = Security::sanitize($_POST['username'] ?? '');
        $email = Security::sanitize($_POST['email'] ?? '');
        $phone = Security::sanitize($_POST['phone'] ?? '');
        $roleStr = strtolower(Security::sanitize($_POST['role'] ?? 'tenant'));
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== '' && $password !== $confirm) {
            $_SESSION['error'] = 'Override keys do not match.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        // Role Mapping
        $roleMap = ['admin' => 1, 'owner' => 2, 'tenant' => 3];
        $roleId = $roleMap[$roleStr] ?? 3;

        $userModel = new User($this->db());
        $logger = new ActivityLog($this->db());

        // Fetch Old State for Diff Analysis
        $oldUser = $userModel->findById($userId);

        // Identity Uniqueness Guardrails (Excluding current user)
        if ($userModel->isUsernameTaken($username, $userId)) {
            $_SESSION['error'] = 'Modification failed: Username "@' . $username . '" is already taken.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        if ($userModel->isEmailTaken($email, $userId)) {
            $_SESSION['error'] = 'Modification failed: Email address is already linked to another user.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        if ($userModel->isPhoneTaken($phone, $userId)) {
            $_SESSION['error'] = 'Modification failed: Contact number is already in use by another record.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        if ($userModel->update($userId, $firstName, $middleName, $lastName, $username, $email, $phone, $roleId, $password !== '' ? $password : null)) {
            // Detailed Audit Diff
            if ($oldUser) {
                $changes = [];
                $fields = [
                    'first_name' => $firstName, 'middle_name' => $middleName, 'last_name' => $lastName,
                    'username' => $username, 'email' => $email, 'phone' => $phone
                ];
                foreach ($fields as $k => $v) {
                    if ($oldUser[$k] != $v) $changes[$k] = ['old' => $oldUser[$k] ?? 'N/A', 'new' => $v];
                }
                if ($password !== '') $changes['password'] = 'Security identity credential overridden';

                if (!empty($changes)) {
                    $logger->log($_SESSION['user_id'], 'UPDATE_USER', $changes, $userId);
                }
            }
            $_SESSION['success'] = 'Identity updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update identity record.';
        }

        header('Location: /tenant/?url=admin/users');
        exit;
    }

    public function notifications(): void
    {
        $this->requireActionPermission('notifications');

        // Simple mock for now as there is no Notification model yet
        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $this->render('admin/notifications', [
            'notifications' => [],
            'roleLabel' => $roleLabel,
            'pageTitle' => 'System Notifications'
        ]);
    }

    public function payments(): void
    {
        $this->requireActionPermission('payments');


        $role = strtolower($_SESSION['role'] ?? 'owner');
        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Fetch properties for filtering
        $sql = "SELECT id, name FROM boarding_houses";
        if ($role === 'owner') {
            $sql .= " WHERE owner_id = $userId";
        }
        $properties = $this->db()->query($sql)->fetch_all(MYSQLI_ASSOC);

        $this->render('admin/payments', [
            'pageTitle' => 'Payment Transactions',
            'properties' => $properties,
            'roleLabel' => ($role === 'admin') ? 'Admin' : 'Owner'
        ]);
    }

    public function get_payments_json(): void
    {
        $this->requireActionPermission('get_payments_json');


        $status = $_GET['status'] ?? null;
        $houseId = isset($_GET['house_id']) && $_GET['house_id'] !== '' ? (int)$_GET['house_id'] : null;
        $role = strtolower($_SESSION['role'] ?? 'owner');
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $sql = "SELECT p.*, u.first_name, u.last_name, u.email, bh.name as boarding_house_name
                FROM payments p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN bookings b ON p.booking_id = b.id
                LEFT JOIN rooms r ON b.room_id = r.id
                LEFT JOIN boarding_houses bh ON r.boarding_house_id = bh.id";

        $where = [];
        if ($status) $where[] = "p.status = '" . $this->db()->real_escape_string($status) . "'";
        if ($houseId) $where[] = "bh.id = $houseId";
        if ($role === 'owner') {
            $where[] = "bh.owner_id = $userId";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.created_at DESC";

        $res = $this->db()->query($sql);
        $data = $res->fetch_all(MYSQLI_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['data' => $data]);
        exit;
    }

    public function plan_payments(): void
    {
        $this->requireActionPermission('plan_payments');


        $this->render('admin/plan_payments', [
            'roleLabel' => 'Admin',
            'pageTitle' => 'Subscription Payment Ledger'
        ]);
    }

    public function get_plan_payments_json(): void
    {
        $this->requireActionPermission('get_plan_payments_json');


        $status = $_GET['status'] ?? null;
        $billingCycle = $_GET['billing_cycle'] ?? null;

        $sql = "SELECT pp.*, u.first_name, u.last_name, u.email, p.name as plan_name
                FROM plan_payments pp
                LEFT JOIN users u ON pp.owner_id = u.id
                LEFT JOIN plans p ON pp.plan_id = p.id";

        $where = [];
        if ($status) $where[] = "pp.status = '" . $this->db()->real_escape_string($status) . "'";
        if ($billingCycle) $where[] = "pp.billing_cycle = '" . $this->db()->real_escape_string($billingCycle) . "'";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY pp.created_at DESC";

        $res = $this->db()->query($sql);
        $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        header('Content-Type: application/json');
        echo json_encode(['data' => $data]);
        exit;
    }

    public function update_plan_payment_status(): void
    {
        $this->requireActionPermission('update_plan_payment_status');
        // 1. Ensure a clean JSON environment
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        // 2. Permission Check
        if (!$this->hasPermission('audit_finances')) {
            echo json_encode(['success' => false, 'message' => 'Forbidden: You do not have permission to audit finances.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        // 3. CSRF Verification
        require_once __DIR__ . '/../helpers/Csrf.php';
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Security Error: CSRF token mismatch. Please refresh the page.']);
            exit;
        }

        // 4. Parameter Validation
        $id = (int)($_POST['payment_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($id <= 0 || !in_array($status, ['paid', 'failed', 'pending'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters provided.']);
            exit;
        }

        $db = $this->db();
        $db->begin_transaction();

        try {
            // A. Update the plan_payment record
            $stmt = $db->prepare("UPDATE plan_payments SET status = ?, paid_at = CASE WHEN ? = 'paid' THEN NOW() ELSE paid_at END WHERE id = ?");
            $stmt->bind_param('ssi', $status, $status, $id);

            if (!$stmt->execute()) {
                throw new Exception("Database update failed: " . $db->error);
            }

            // B. If PAID, sync with subscriptions table
            if ($status === 'paid') {
                $payStmt = $db->prepare("SELECT subscription_id, plan_id, billing_cycle, owner_id FROM plan_payments WHERE id = ?");
                $payStmt->bind_param('i', $id);
                $payStmt->execute();
                $paymentRow = $payStmt->get_result()->fetch_assoc();

                if ($paymentRow) {
                    require_once __DIR__ . '/../models/Subscription.php';
                    $subModel = new Subscription($db);
                    $subModel->changePlan(
                        (int)$paymentRow['subscription_id'],
                        (int)$paymentRow['plan_id'],
                        $paymentRow['billing_cycle'],
                        (int)$paymentRow['owner_id']
                    );
                }
            }

            // C. Log the audit event
            try {
                require_once __DIR__ . '/../models/ActivityLog.php';
                $logger = new ActivityLog($db);
                $logger->log((int)($_SESSION['user_id'] ?? 0), 'AUDIT_PAYMENT_STATUS', [
                    'payment_id' => $id, 
                    'new_status' => $status
                ]);
            } catch (Throwable $e) { /* Log failure shouldn't rollback */ }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Payment status successfully updated and synchronized.']);

        } catch (Throwable $e) {
            $db->rollback();
            echo json_encode(['success' => false, 'message' => 'Critical update failure: ' . $e->getMessage()]);
        }
        exit;
    }

    public function print_plan_payments(): void
    {
        $this->requireActionPermission('print_plan_payments');


        $status = $_GET['status'] ?? null;
        $billingCycle = $_GET['billing_cycle'] ?? null;

        $sql = "SELECT pp.*, u.first_name, u.last_name, u.email, p.name as plan_name
                FROM plan_payments pp
                LEFT JOIN users u ON pp.owner_id = u.id
                LEFT JOIN plans p ON pp.plan_id = p.id";

        $where = [];
        if ($status) $where[] = "pp.status = '" . $this->db()->real_escape_string($status) . "'";
        if ($billingCycle) $where[] = "pp.billing_cycle = '" . $this->db()->real_escape_string($billingCycle) . "'";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY pp.created_at DESC";

        $res = $this->db()->query($sql);
        $payments = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $totalAmount = array_reduce($payments, fn($carry, $item) => $carry + (float)($item['amount'] ?? 0), 0.0);

        $this->render('admin/print_plan_payments', [
            'payments' => $payments,
            'totalAmount' => $totalAmount,
            'filter_status' => $status,
            'filter_cycle' => $billingCycle
        ]);
    }

    public function plans(): void
    {
        $this->requireActionPermission('plans');


        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $this->render('admin/plans', [
            'plans' => [],
            'roleLabel' => $roleLabel,
            'pageTitle' => 'Service Plans'
        ]);
    }

    public function get_plans_json(): void
    {
        $this->requireActionPermission('get_plans_json');


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf = $_POST['csrf_token'] ?? null;
            if (!Csrf::verify($csrf)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                exit;
            }
        }

        $res = $this->db()->query("SELECT * FROM plans WHERE is_deleted = 0 ORDER BY price_monthly ASC");
        $plans = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        header('Content-Type: application/json');
        echo json_encode(['data' => $plans]);
        exit;
    }

    public function print_plans(): void
    {
        $this->requireActionPermission('print_plans');


        $res = $this->db()->query("SELECT * FROM plans WHERE is_deleted = 0 ORDER BY price_monthly ASC");
        $plans = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        $totalPlans = count($plans);
        $avgMonthly = $totalPlans > 0 ? array_reduce($plans, fn($c, $p) => $c + (float)$p['price_monthly'], 0) / $totalPlans : 0;

        $this->render('admin/print_plans', [
            'plans' => $plans,
            'totalPlans' => $totalPlans,
            'avgMonthly' => $avgMonthly
        ]);
    }

    public function create_plan(): void
    {
        $this->requireActionPermission('create_plan');

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo $isAjax ? json_encode(['success' => false, 'message' => 'Method Not Allowed']) : 'Method Not Allowed';
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'price_monthly' => (float)($_POST['price_monthly'] ?? 0),
            'price_yearly' => (float)($_POST['price_yearly'] ?? 0),
            'bhouse_limit' => (int)($_POST['bhouse_limit'] ?? 0),
            'room_limit' => (int)($_POST['room_limit'] ?? 0),
            'length_free' => (int)($_POST['length_free'] ?? 0),
            'features' => trim($_POST['features'] ?? '[]')
        ];

        if (empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'Plan name is required']);
            exit;
        }

        require_once __DIR__ . '/../models/Plan.php';
        $planModel = new Plan($this->db());

        if ($planModel->create($data)) {
            try {
                $logger = new ActivityLog($this->db());
                $logger->log((int)($_SESSION['user_id'] ?? 0), 'CREATE_PLAN', ['name' => $data['name']]);
            } catch (Throwable $e) {
                // Log failed but plan created
            }
            echo json_encode(['success' => true, 'message' => 'Plan created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create plan: ' . $this->db()->error]);
        }
        exit;
    }

    public function update_plan(): void
    {
        $this->requireActionPermission('update_plan');

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo $isAjax ? json_encode(['success' => false, 'message' => 'Method Not Allowed']) : 'Method Not Allowed';
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        $id = (int)($_POST['plan_id'] ?? 0);
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'price_monthly' => (float)($_POST['price_monthly'] ?? 0),
            'price_yearly' => (float)($_POST['price_yearly'] ?? 0),
            'bhouse_limit' => (int)($_POST['bhouse_limit'] ?? 0),
            'room_limit' => (int)($_POST['room_limit'] ?? 0),
            'length_free' => (int)($_POST['length_free'] ?? 0),
            'features' => trim($_POST['features'] ?? '[]')
        ];

        if ($id <= 0 || empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request data']);
            exit;
        }

        require_once __DIR__ . '/../models/Plan.php';
        $planModel = new Plan($this->db());

        if ($planModel->update($id, $data)) {
            try {
                $logger = new ActivityLog($this->db());
                $logger->log((int)($_SESSION['user_id'] ?? 0), 'UPDATE_PLAN', ['id' => $id, 'name' => $data['name']]);
            } catch (Throwable $e) {
                // Log failed but plan updated
            }
            echo json_encode(['success' => true, 'message' => 'Plan updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update plan: ' . $this->db()->error]);
        }
        exit;
    }

    public function delete_plan(): void
    {
        $this->requireActionPermission('delete_plan');


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        $id = (int)($_POST['plan_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        require_once __DIR__ . '/../models/Plan.php';
        $planModel = new Plan($this->db());

        if ($planModel->isPlanInUse($id)) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete plan: There are active subscriptions using it.']);
            exit;
        }

        if ($planModel->delete($id)) {
            $logger = new ActivityLog($this->db());
            $logger->log((int)($_SESSION['user_id'] ?? 0), 'DELETE_PLAN', ['id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Plan deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete plan']);
        }
        exit;
    }

    public function upgrade(): void
    {
        $this->requireActionPermission('upgrade');
        $ownerId = (int)$_SESSION['user_id'];
        $subscription = (new Subscription($this->db()))->getOwnerSubscriptionStatus($ownerId);
        $subId = (int)($_GET['subscription_id'] ?? ($subscription['id'] ?? 0));
        if ($subId > 0) {
            $statement = $this->db()->prepare('SELECT id, owner_id FROM subscriptions WHERE id = ?');
            $statement->bind_param('i', $subId);
            $statement->execute();
            $row = $statement->get_result()->fetch_assoc();
            if (!$row || (strtolower($_SESSION['role']) !== 'admin' && (int)$row['owner_id'] !== $ownerId)) {
                $this->json(['success' => false, 'message' => 'Subscription unavailable.'], 403);
            }
        }
        $this->render('admin/upgrade', [
            'activeSubId' => $subId,
            'allPlans' => (new Plan($this->db()))->getAll(),
            'paymentSettings' => (new SystemSetting($this->db()))->getAll(),
            'roleLabel' => ucfirst($_SESSION['role']),
            'billingCycle' => ($_GET['cycle'] ?? 'monthly') === 'yearly' ? 'yearly' : 'monthly'
        ]);
    }

    public function subscriptions(): void
    {
        $this->requireActionPermission('subscriptions');
        $this->requireLogin();
        if ($_SESSION['role'] !== 'owner') {

        }

        if (isset($_GET['status']) && $_GET['status'] === 'success') {
            $sessionId  = $_GET['session'] ?? null;
            $ppId       = (int)($_GET['pp_id'] ?? 0);   // plan_payment row ID (set by upgrade_plan)
            $callbackUserId = (int)($_SESSION['user_id'] ?? 0);
            $callbackRole   = strtolower($_SESSION['role'] ?? '');

            if (($callbackRole === 'owner' && $callbackUserId > 0) || ($callbackRole === 'admin' && $sessionId)) {
                require_once __DIR__ . '/../models/SystemSetting.php';
                require_once __DIR__ . '/../models/Payment.php';
                require_once __DIR__ . '/../models/Subscription.php';

                $settingModel = new SystemSetting($this->db());
                $secretKey    = trim($settingModel->get('paymongo_sec', '') ?? '');
                $paymentModel = new Payment($this->db());
                $methodPrefix = 'PAYMONGO';

                if (!empty($secretKey) && $sessionId) {
                    $session = $this->getPayMongoSession($secretKey, $sessionId);
                    if ($session && !empty($session['attributes']['payments'])) {
                        $firstPayment = $session['attributes']['payments'][0];
                        $methodPrefix = $firstPayment['attributes']['source']['type'] ?? 'PAYMONGO';
                    }
                }

                $transactionRef = $paymentModel->generateTransactionRef($methodPrefix);

                // Update plan_payment by PK if we have pp_id (upgrade flow) — most reliable
                if ($ppId > 0) {
                    $upd = $this->db()->prepare(
                        "UPDATE plan_payments SET status = 'paid', paid_at = NOW(), transaction_ref = ?, session_id = COALESCE(session_id, ?) WHERE id = ? AND status = 'pending' LIMIT 1"
                    );
                    $upd->bind_param('ssi', $transactionRef, $sessionId, $ppId);
                    $upd->execute();

                    // Read the plan/cycle details from plan_payments to finalise the subscription upgrade
                    $ppRow = $this->db()->prepare(
                        "SELECT subscription_id, plan_id, billing_cycle FROM plan_payments WHERE id = ? LIMIT 1"
                    );
                    $ppRow->bind_param('i', $ppId);
                    $ppRow->execute();
                    $ppData = $ppRow->get_result()->fetch_assoc();

                    if ($ppData && !empty($ppData['subscription_id']) && !empty($ppData['plan_id'])) {
                        $subscriptionModel = new Subscription($this->db());
                        $subscriptionModel->changePlan(
                            (int)$ppData['subscription_id'],
                            (int)$ppData['plan_id'],
                            (string)$ppData['billing_cycle']
                        );
                    }
                } else {
                    // Fallback: session_id matching for older payments without pp_id
                    if ($callbackRole === 'owner') {
                        $ppStmt = $this->db()->prepare(
                            "UPDATE plan_payments SET status = 'paid', paid_at = NOW(), transaction_ref = ? WHERE session_id = ? AND owner_id = ? AND status = 'pending' LIMIT 1"
                        );
                        $ppStmt->bind_param('ssi', $transactionRef, $sessionId, $callbackUserId);
                    } else {
                        $ppStmt = $this->db()->prepare(
                            "UPDATE plan_payments SET status = 'paid', paid_at = NOW(), transaction_ref = ? WHERE session_id = ? AND status = 'pending' LIMIT 1"
                        );
                        $ppStmt->bind_param('ss', $transactionRef, $sessionId);
                    }
                    $ppStmt->execute();
                }
            }

            $_SESSION['success'] = 'Payment verified. Your subscription has been successfully updated.';
            header('Location: /tenant/?url=admin/subscriptions');
            exit;
        }

        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $res = $this->db()->query("SELECT * FROM plans ORDER BY price_monthly ASC");
        $plans = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        $settingModel = new SystemSetting($this->db());
        $paymentSettings = $settingModel->getAll();

        $this->render('admin/subscriptions', [
            'subscriptions' => [],
            'plans' => $plans,
            'allPlans' => $plans, // Inject for the component
            'paymentSettings' => $paymentSettings, // Added for payment_modal.php
            'roleLabel' => $roleLabel,
            'pageTitle' => 'Plan Management'
        ]);
    }

    public function get_subscriptions_json(): void
    {
        $this->requireActionPermission('get_subscriptions_json');
        $this->requireLogin();
        if ($_SESSION['role'] !== 'owner') {

        }
        $subscriptionModel = new Subscription($this->db());

        $userRole = strtolower($_SESSION['role'] ?? '');
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $ownerId = ($userRole === 'owner') ? $userId : null;

        $subscriptions = $subscriptionModel->allWithDetails($ownerId);

        header('Content-Type: application/json');
        echo json_encode(['data' => $subscriptions]);
        exit;
    }

    public function upgrade_subscription_yearly(): void
    {
        $this->requireActionPermission('upgrade_subscription_yearly');


        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo $isAjax ? json_encode(['success' => false, 'message' => 'Method Not Allowed']) : 'Method Not Allowed';
            return;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $subIdRaw = $_POST['subscription_id'] ?? null;
        $subId = is_numeric($subIdRaw) ? (int)$subIdRaw : 0;

        if ($subId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid subscription ID']);
            return;
        }

        $subscriptionModel = new Subscription($this->db());

        $userRole = strtolower($_SESSION['role'] ?? '');
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $ownerId = ($userRole === 'owner') ? $userId : null;

        if ($subscriptionModel->upgradeToYearly($subId, $ownerId)) {
            $logger = new ActivityLog($this->db());
            $logger->log($userId, 'UPGRADE_SUBSCRIPTION', ['subscription_id' => $subId, 'cycle' => 'yearly']);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Successfully upgraded to yearly billing']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to upgrade subscription or not authorized']);
        }
        exit;
    }

    public function downgrade_subscription_monthly(): void
    {
        $this->requireActionPermission('downgrade_subscription_monthly');


        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo $isAjax ? json_encode(['success' => false, 'message' => 'Method Not Allowed']) : 'Method Not Allowed';
            return;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $subIdRaw = $_POST['subscription_id'] ?? null;
        $subId = is_numeric($subIdRaw) ? (int)$subIdRaw : 0;

        if ($subId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid subscription ID']);
            return;
        }

        $subscriptionModel = new Subscription($this->db());

        $userRole = strtolower($_SESSION['role'] ?? '');
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $ownerId = ($userRole === 'owner') ? $userId : null;

        if ($subscriptionModel->downgradeToMonthly($subId, $ownerId)) {
            $logger = new ActivityLog($this->db());
            $logger->log($userId, 'DOWNGRADE_SUBSCRIPTION', ['subscription_id' => $subId, 'cycle' => 'monthly']);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Successfully switched to monthly billing']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to switch subscription or not authorized']);
        }
        exit;
    }

    public function cancel_subscription(): void
    {
        $this->requireActionPermission('cancel_subscription');


        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo $isAjax ? json_encode(['success' => false, 'message' => 'Method Not Allowed']) : 'Method Not Allowed';
            return;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $subIdRaw = $_POST['subscription_id'] ?? null;
        $subId = is_numeric($subIdRaw) ? (int)$subIdRaw : 0;

        if ($subId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid subscription ID']);
            return;
        }

        $subscriptionModel = new Subscription($this->db());

        $userRole = strtolower($_SESSION['role'] ?? '');
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $ownerId = ($userRole === 'owner') ? $userId : null;

        if ($subscriptionModel->cancelSubscription($subId, $ownerId)) {
            $logger = new ActivityLog($this->db());
            $logger->log($userId, 'CANCEL_SUBSCRIPTION', ['subscription_id' => $subId]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Subscription cancelled successfully']);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to cancel subscription or not authorized']);
        }
        exit;
    }

    public function upgrade_plan(): void
    {
        $this->requireActionPermission('upgrade_plan');
        $this->requireRole(['owner', 'admin']);

        try {
            if (!isset($_POST['csrf_token']) || !Csrf::verify($_POST['csrf_token'])) {
                $this->json(['success' => false, 'message' => 'Security protocol breach (Invalid CSRF). Cycle aborted.'], 403);
                return;
            }

            $subIdRaw = $_POST['subscription_id'] ?? null;
            $subId = is_numeric($subIdRaw) ? (int)$subIdRaw : 0;

            $newPlanIdRaw = $_POST['new_plan_id'] ?? null;
            $newPlanId = is_numeric($newPlanIdRaw) ? (int)$newPlanIdRaw : 0;

            $billingCycle = $_POST['billing_cycle'] ?? 'monthly';
            if (!in_array($billingCycle, ['monthly', 'yearly'])) {
                $billingCycle = 'monthly';
            }

            if ($newPlanId <= 0) {
                $this->json(['success' => false, 'message' => 'Please select a valid subscription plan.'], 400);
                return;
            }

            $userRole = strtolower($_SESSION['role'] ?? '');
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $gateway = $_POST['payment_gateway'] ?? 'manual';
            $method  = $_POST['payment_method'] ?? 'card';

            if ($subId <= 0 && $userRole === 'owner' && $userId > 0) {
                // Find existing subscription or create a new one for this owner
                $findSub = $this->db()->prepare("SELECT id FROM subscriptions WHERE owner_id = ? ORDER BY id DESC LIMIT 1");
                $findSub->bind_param('i', $userId);
                $findSub->execute();
                $existing = $findSub->get_result()->fetch_assoc();
                if ($existing && !empty($existing['id'])) {
                    $subId = (int)$existing['id'];
                } else {
                    $initStatus = ($gateway === 'paymongo') ? 'pending' : 'active';
                    $newSub = $this->db()->prepare("INSERT INTO subscriptions (owner_id, plan_id, billing_cycle, start_date, status) VALUES (?, ?, ?, CURRENT_DATE, ?)");
                    $newSub->bind_param('iiss', $userId, $newPlanId, $billingCycle, $initStatus);
                    $newSub->execute();
                    $subId = (int)$this->db()->insert_id;
                }
            }

            if ($subId <= 0 || $newPlanId <= 0) {
                $this->json(['success' => false, 'message' => 'Subscription or Plan ID is invalid.'], 400);
                return;
            }

            $subscriptionModel = new Subscription($this->db());
            $planModel = new Plan($this->db());
            $plan = $planModel->getById($newPlanId);

            if (!$plan) {
                $this->json(['success' => false, 'message' => 'Target plan blueprint not found in the system repository.'], 404);
                return;
            }

            $amount = ($billingCycle === 'yearly') ? (float)$plan['price_yearly'] : (float)$plan['price_monthly'];

            $settingModel = new SystemSetting($this->db());
            $taxEnabled = ($settingModel->get('payment_tax_enabled', '0') === '1');
            $taxPercent = (float)$settingModel->get('payment_tax_percent', 0);
            $taxAmount = $taxEnabled ? ($amount * ($taxPercent / 100)) : 0;
            $grandTotal = $amount + $taxAmount;

            $amountInCentavos = (int)(round($grandTotal * 100));

            $userRole = strtolower($_SESSION['role'] ?? '');
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $ownerId = ($userRole === 'owner') ? $userId : null;

            $gateway = $_POST['payment_gateway'] ?? 'manual';
            $method  = $_POST['payment_method'] ?? 'card';

            // Determine the actual owner_id for plan_payments
            $ppOwnerId = ($userRole === 'owner')
                ? $userId
                : (int)(($this->db()->query("SELECT owner_id FROM subscriptions WHERE id = $subId LIMIT 1")->fetch_assoc())['owner_id'] ?? 0);

            require_once __DIR__ . '/../models/Payment.php';
            $paymentModel = new Payment($this->db());

            // For PayMongo: insert as 'pending' FIRST so we have an ID to put in the success URL
            // For manual/simulation: insert as 'paid' immediately
            $isPendingPaymongo = ($gateway === 'paymongo');
            $ppStatus  = $isPendingPaymongo ? 'pending' : 'paid';
            $ppPaidAt  = $isPendingPaymongo ? null : date('Y-m-d H:i:s');
            $manualRef = $isPendingPaymongo ? null : $paymentModel->generateTransactionRef($method);

            $planPaymentId = 0;
            if ($ppOwnerId > 0) {
                $ppInsert = $this->db()->prepare(
                    "INSERT INTO plan_payments
                     (owner_id, subscription_id, plan_id, billing_cycle, amount, gateway, payment_method, status, session_id, transaction_ref, paid_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?)"
                );
                $ppInsert->bind_param(
                    'iiisdsssss',
                    $ppOwnerId, $subId, $newPlanId, $billingCycle,
                    $grandTotal, $gateway, $method, $ppStatus, $manualRef, $ppPaidAt
                );
                $ppInsert->execute();
                $planPaymentId = (int)$this->db()->insert_id;
            }

            $redirectUrl = null;
            $sessionId   = null;
            if ($gateway === 'paymongo') {
                $secretKey = trim($settingModel->get('paymongo_sec', '') ?? '');

                if (!empty($secretKey) && strpos($secretKey, 'sk_') === 0) {
                    $result = $this->createPayMongoCheckoutSession($secretKey, [
                        'amount'         => $amountInCentavos,
                        'description'    => "StayHub " . $plan['name'] . " Upgrade (" . ucfirst($billingCycle) . ")" . ($taxEnabled ? " (Incl. $taxPercent% Service Fee)" : ""),
                        'plan_name'      => $plan['name'],
                        'method_types'   => [$method],
                        'plan_payment_id'=> $planPaymentId,   // embed so callback can use it
                    ]);

                    if ($result && isset($result['checkout_url'])) {
                        $redirectUrl = $result['checkout_url'];
                        $sessionId   = $result['session_id'] ?? null;

                        // Store the PayMongo session_id on the plan_payment row for audit purposes
                        if ($planPaymentId > 0 && $sessionId) {
                            $upd = $this->db()->prepare("UPDATE plan_payments SET session_id = ? WHERE id = ?");
                            $upd->bind_param('si', $sessionId, $planPaymentId);
                            $upd->execute();
                        }
                    } else {
                        // Log failure for troubleshooting
                        $errorMsg = $result['error'] ?? 'PayMongo checkout session creation failed.';
                        $this->json([
                            'success' => false,
                            'message' => 'Failed to initialize payment gateway: ' . $errorMsg,
                            'debug_info' => $result
                        ], 400);
                        return;
                    }
                } else {
                    $redirectUrl = "/tenant/?url=owner/subscription_simulation&status=simulated&plan=" . urlencode($plan['name']);
                }
            }

            // For PayMongo: plan change is deferred to the success callback to avoid premature updates
            // For manual/simulation: apply immediately
            $planChanged = (!$isPendingPaymongo)
                ? ($subscriptionModel->changePlan($subId, $newPlanId, $billingCycle, $ownerId) && $this->db()->query("UPDATE subscriptions SET status = 'active', start_date = CURRENT_DATE WHERE id = " . (int)$subId))
                : true; // confirmed by payment callback

            if ($planChanged) {
                try {
                    $logger = new ActivityLog($this->db());
                    $logger->log($userId, 'UPGRADE_SUBSCRIPTION_PLAN', [
                        'subscription_id' => $subId, 
                        'new_plan_id' => $newPlanId, 
                        'billing_cycle' => $billingCycle,
                        'gateway' => $gateway,
                        'method' => $method,
                        'redirect_url' => $redirectUrl,
                        'amount' => $amount
                    ]);
                } catch (Throwable $e) {}

                $this->json([
                    'success' => true, 
                    'message' => 'Subscription upgrade protocols successfully initialized.',
                    'redirect_url' => $redirectUrl
                ]);
            } else {
                $this->json([
                    'success' => true, 
                    'message' => 'Subscription plan synchronized (no changes required).',
                    'redirect_url' => $redirectUrl
                ]);
            }
        } catch (Throwable $e) {
            $this->json([
                'success' => false, 
                'message' => 'A systemic error occurred during plan orchestration.',
                'error_detail' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
        exit;
    }

    private function createPayMongoCheckoutSession(string $secretKey, array $params): ?array
    {
        $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        $baseUrl    = $protocol . $domainName . '/tenant/';

        // Append plan_payment_id if provided so the callback can identify the record
        $ppIdParam = isset($params['plan_payment_id']) && $params['plan_payment_id'] > 0
            ? '&pp_id=' . (int)$params['plan_payment_id']
            : '';

        $payload = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'show_line_items' => true,
                    'description' => $params['description'],
                    'success_url' => $baseUrl . '?url=admin/subscriptions&status=success&session={CHECKOUT_SESSION_ID}' . $ppIdParam,
                    'cancel_url' => $baseUrl . '?url=owner/houses&status=cancelled',
                    'line_items' => [
                        [
                            'currency' => 'PHP',
                            'amount' => $params['amount'],
                            'description' => $params['description'],
                            'name' => $params['plan_name'],
                            'quantity' => 1
                        ]
                    ],
                    'payment_method_types' => $params['method_types']
                ]
            ]
        ];

        $ch = curl_init('https://api.paymongo.com/v1/checkout_sessions');
        if (!$ch) return null;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ':');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($data['data'])) {
            return [
                'checkout_url' => $data['data']['attributes']['checkout_url'] ?? null,
                'session_id' => $data['data']['id'] ?? null
            ];
        }

        // Return error details if available
        $errorMsg = $data['errors'][0]['detail'] ?? 'Unknown PayMongo error';
        return [
            'success' => false,
            'error' => $errorMsg,
            'http_code' => $httpCode,
            'raw_response' => $data
        ];
    }

    public function reviews(): void
    {
        $this->requireActionPermission('reviews');


        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $this->render('admin/reviews', [
            'pageTitle' => 'Platform Reviews',
            'roleLabel' => $roleLabel
        ]);
    }

    public function get_reviews_json(): void
    {
        $this->requireActionPermission('get_reviews_json');

        require_once __DIR__ . '/../models/Review.php';

        $reviewModel = new Review($this->db());
        $reviews = $reviewModel->getAll();

        header('Content-Type: application/json');
        echo json_encode(['data' => $reviews]);
        exit;
    }

    public function update_review_status(): void
    {
        $this->requireActionPermission('update_review_status');

        require_once __DIR__ . '/../models/Review.php';

        $id = $_POST['review_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $csrf = $_POST['csrf_token'] ?? null;

        if (!Csrf::verify($csrf)) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        if (!$id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }

        $reviewModel = new Review($this->db());
        if ($reviewModel->updateStatus((int)$id, $status)) {
            try {
                $logger = new ActivityLog($this->db());
                $logger->log((int)($_SESSION['user_id'] ?? 0), 'UPDATE_REVIEW_STATUS', ['id' => $id, 'status' => $status]);
            } catch (Throwable $e) {}
            echo json_encode(['success' => true, 'message' => 'Review status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update review status']);
        }
        exit;
    }

    public function delete_review(): void
    {
        $this->requireActionPermission('delete_review');

        require_once __DIR__ . '/../models/Review.php';

        $id = $_POST['review_id'] ?? null;
        $csrf = $_POST['csrf_token'] ?? null;

        if (!Csrf::verify($csrf)) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing review ID']);
            exit;
        }

        $reviewModel = new Review($this->db());
        if ($reviewModel->delete((int)$id)) {
            try {
                $logger = new ActivityLog($this->db());
                $logger->log((int)($_SESSION['user_id'] ?? 0), 'DELETE_REVIEW', ['id' => $id]);
            } catch (Throwable $e) {}
            echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete review']);
        }
        exit;
    }

    public function reports(): void
    {
        $this->requireActionPermission('reports');


        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';
        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Fetch properties for filtering
        $sql = "SELECT id, name FROM boarding_houses";
        if ($role === 'owner') {
            $sql .= " WHERE owner_id = $userId";
        }
        $properties = $this->db()->query($sql)->fetch_all(MYSQLI_ASSOC);

        $this->render('admin/reports', [
            'pageTitle' => 'Analytics & Reports',
            'roleLabel' => $roleLabel,
            'properties' => $properties
        ]);
    }

    public function get_reports_stats(): void
    {
        $this->requireActionPermission('get_reports_stats');


        $role = strtolower($_SESSION['role'] ?? 'owner');
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $houseId = isset($_POST['house_id']) && $_POST['house_id'] !== '' ? (int)$_POST['house_id'] : null;

        // Base filter parts
        $wherePayments = "WHERE p.status = 'paid'";
        $whereBookings = "WHERE 1=1";
        $whereProperties = "WHERE 1=1";

        if ($role === 'owner') {
            $whereProperties .= " AND bh.owner_id = $userId";
            // For bookings/payments, we need to join through rooms and boarding_houses
            $ownerHouseIds = $this->db()->query("SELECT id FROM boarding_houses WHERE owner_id = $userId")->fetch_all(MYSQLI_NUM);
            $houseIds = array_column($ownerHouseIds, 0);
            $houseIdsStr = !empty($houseIds) ? implode(',', $houseIds) : '0';

            if ($houseId) {
                $whereBookings .= " AND r.boarding_house_id = $houseId";
                $wherePayments .= " AND bh.id = $houseId";
                $whereProperties .= " AND bh.id = $houseId";
            } else {
                $whereBookings .= " AND r.boarding_house_id IN ($houseIdsStr)";
                $wherePayments .= " AND bh.id IN ($houseIdsStr)";
                $whereProperties .= " AND bh.id IN ($houseIdsStr)";
            }
        } else if ($houseId) {
            $whereBookings .= " AND r.boarding_house_id = $houseId";
            $wherePayments .= " AND bh.id = $houseId";
            $whereProperties .= " AND bh.id = $houseId";
        }

        // 1. KPI Stats
        $totalRevenueSql = "SELECT SUM(p.amount) as total FROM payments p 
                            JOIN bookings b ON p.booking_id = b.id 
                            JOIN rooms r ON b.room_id = r.id 
                            JOIN boarding_houses bh ON r.boarding_house_id = bh.id 
                            $wherePayments";
        $totalRevenue = $this->db()->query($totalRevenueSql)->fetch_assoc()['total'] ?? 0;

        $totalBookingsSql = "SELECT COUNT(b.id) as total FROM bookings b 
                             JOIN rooms r ON b.room_id = r.id 
                             $whereBookings";
        $totalBookings = $this->db()->query($totalBookingsSql)->fetch_assoc()['total'] ?? 0;

        // User counts are global for admins, but for owners, we show their tenants
        if ($role === 'admin' && !$houseId) {
            $activeTenants = $this->db()->query("SELECT COUNT(*) as total FROM users WHERE role_id = 3 AND status = 1")->fetch_assoc()['total'] ?? 0;
            $activeOwners = $this->db()->query("SELECT COUNT(*) as total FROM users WHERE role_id = 2 AND status = 1")->fetch_assoc()['total'] ?? 0;
        } else {
            // If houseId or owner, count unique tenants who have booked those houses
            $tenantSql = "SELECT COUNT(DISTINCT b.user_id) as total FROM bookings b 
                          JOIN rooms r ON b.room_id = r.id 
                          $whereBookings";
            $activeTenants = $this->db()->query($tenantSql)->fetch_assoc()['total'] ?? 0;
            $activeOwners = ($role === 'admin') ? 1 : 1; // Simplification for filtered view
        }

        // 2. Revenue Trend (Last 6 Months)
        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $trendSql = "SELECT SUM(p.amount) as total FROM payments p 
                         JOIN bookings b ON p.booking_id = b.id 
                         JOIN rooms r ON b.room_id = r.id 
                         JOIN boarding_houses bh ON r.boarding_house_id = bh.id 
                         $wherePayments AND DATE_FORMAT(p.created_at, '%Y-%m') = '$month'";
            $val = $this->db()->query($trendSql)->fetch_assoc()['total'] ?? 0;
            $revenueTrend[] = [
                'month' => date('M Y', strtotime("-$i months")),
                'amount' => (float)$val
            ];
        }

        // 3. Booking Distribution (Statuses)
        $bookingDistSql = "SELECT b.status, COUNT(b.id) as count FROM bookings b 
                           JOIN rooms r ON b.room_id = r.id 
                           $whereBookings GROUP BY b.status";
        $bookingDist = $this->db()->query($bookingDistSql)->fetch_all(MYSQLI_ASSOC);

        // 4. Top Properties (By Bookings)
        $topPropertiesSql = "
            SELECT bh.name, COUNT(b.id) as booking_count, SUM(b.total_amount) as revenue
            FROM boarding_houses bh
            LEFT JOIN rooms r ON bh.id = r.boarding_house_id
            LEFT JOIN bookings b ON r.id = b.room_id
            $whereProperties
            GROUP BY bh.id
            ORDER BY booking_count DESC
            LIMIT 5
        ";
        $topProperties = $this->db()->query($topPropertiesSql)->fetch_all(MYSQLI_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'kpis' => [
                'revenue' => number_format((float)$totalRevenue, 2),
                'bookings' => $totalBookings,
                'tenants' => $activeTenants,
                'owners' => $activeOwners
            ],
            'revenueTrend' => $revenueTrend,
            'bookingDist' => $bookingDist,
            'topProperties' => $topProperties
        ]);
        exit;
    }

    public function map(): void
    {
        $this->requireActionPermission('map');

        $db = $this->db();

        // Auto-migrate: safe no-op if columns already exist
        $db->query("ALTER TABLE boarding_houses ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL");
        $db->query("ALTER TABLE boarding_houses ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL");

        $role   = strtolower($_SESSION['role'] ?? 'admin');
        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Admins see all approved houses; owners see only their own
        if ($role === 'admin') {
            $result = $db->query("SELECT id, owner_id, name, address, latitude, longitude FROM boarding_houses WHERE status = 'approved' AND is_deleted = 0 ORDER BY name ASC");
        } else {
            $stmt = $db->prepare("SELECT id, owner_id, name, address, latitude, longitude FROM boarding_houses WHERE status = 'approved' AND is_deleted = 0 AND owner_id = ? ORDER BY name ASC");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        $houses = [];
        while ($row = $result->fetch_assoc()) {
            $houses[] = $row;
        }

        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $this->render('admin/map', [
            'houses'    => $houses,
            'roleLabel' => $roleLabel,
            'pageTitle' => 'Boarding House Map',
            'isAdmin'   => ($role === 'admin'),
        ]);
    }

    public function map_update_coords(): void
    {
        $this->requireActionPermission('map_update_coords');
         // All with house access can reach endpoint

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $csrf = $body['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
        if (!Csrf::verify($csrf)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
        }

        $houseId   = (int)($body['house_id'] ?? 0);
        $latitude  = isset($body['latitude'])  ? (float)$body['latitude']  : null;
        $longitude = isset($body['longitude']) ? (float)$body['longitude'] : null;

        if (!$houseId || $latitude === null || $longitude === null) {
            $this->json(['success' => false, 'message' => 'Missing required fields.'], 422);
        }

        $db     = $this->db();
        $role   = strtolower($_SESSION['role'] ?? '');
        $userId = (int)($_SESSION['user_id'] ?? 0);

        // Owners can only update their own houses
        if ($role !== 'admin') {
            $chk = $db->prepare("SELECT id FROM boarding_houses WHERE id = ? AND owner_id = ? LIMIT 1");
            $chk->bind_param('ii', $houseId, $userId);
            $chk->execute();
            if (!$chk->get_result()->fetch_assoc()) {
                $this->json(['success' => false, 'message' => 'Access denied: this property does not belong to you.'], 403);
            }
        }

        $stmt = $db->prepare("UPDATE boarding_houses SET latitude = ?, longitude = ? WHERE id = ?");
        $stmt->bind_param('ddi', $latitude, $longitude, $houseId);
        $ok = $stmt->execute();

        if ($ok) {
            $logger = new ActivityLog($db);
            $logger->log($userId, 'UPDATE_HOUSE_COORDS', [
                'house_id'  => $houseId,
                'latitude'  => $latitude,
                'longitude' => $longitude,
            ]);
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Database update failed.'], 500);
        }
    }

    public function settings(): void
    {
        $this->requireActionPermission('settings');

        $model = new SystemSetting($this->db());
        $settings = $model->getAll();

        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $this->render('admin/settings', [
            'settings' => $settings,
            'roleLabel' => $roleLabel,
            'pageTitle' => 'System Settings'
        ]);
    }

    public function payment_settings(): void
    {
        $this->requireActionPermission('payment_settings');

        $model = new SystemSetting($this->db());
        $settings = $model->getAll();

        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $this->render('admin/payment_settings', [
            'settings' => $settings,
            'roleLabel' => $roleLabel,
            'pageTitle' => 'Payment Gateway Protocols'
        ]);
    }

    public function save_settings(): void
    {
        $this->requireActionPermission('save_settings');


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $db = $this->db();
        $logger = new ActivityLog($db);
        $model = new SystemSetting($db);

        $payload = $_POST;
        $context = $payload['context'] ?? 'general';
        unset($payload['csrf_token']);
        unset($payload['context']);

        $logoPath = null;
        // Handle Logo Upload
        if (!empty($_FILES['company_logo']['name'])) {
            $file = $_FILES['company_logo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            if (!in_array($ext, $allowed)) {
                $this->json(['success' => false, 'message' => 'Invalid logo format. Use JPG, PNG, WebP or SVG.']);
            }

            $newName = 'logo_' . time() . '.' . $ext;
            $uploadDir = 'public/uploads/system/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                $logoPath = $uploadDir . $newName;
                $payload['company_logo'] = $logoPath;
            }
        }

        // Handle PayMongo methods array
        if (isset($payload['paymongo_methods']) && is_array($payload['paymongo_methods'])) {
            $payload['paymongo_methods'] = json_encode($payload['paymongo_methods']);
        } else if ($context === 'payments') {
            // If it's payment context and no methods sent (all unchecked), save empty array
            $payload['paymongo_methods'] = json_encode([]);
        }

        // Handle toggles (checkboxes not sent if unchecked)
        if ($context === 'general') {
            $payload['debug_mode'] = isset($payload['debug_mode']) ? '1' : '0';
            $payload['owner_self_reg'] = isset($payload['owner_self_reg']) ? '1' : '0';
            $payload['maintenance_mode'] = isset($payload['maintenance_mode']) ? '1' : '0';
        } else if ($context === 'payments') {
            $payload['gcash_enabled'] = isset($payload['gcash_enabled']) ? '1' : '0';
            $payload['paymongo_enabled'] = isset($payload['paymongo_enabled']) ? '1' : '0';
            $payload['payment_tax_enabled'] = isset($payload['payment_tax_enabled']) ? '1' : '0';
        }

        try {
            if (!$model->saveBatch($payload)) {
                throw new Exception("Failed to persist protocol batch.");
            }

            // Log the unified configuration update
            $logger->log($userId, 'UPDATE_SYSTEM_SETTINGS', [
                'context' => $context,
                'description' => "Updated global system and payment gateway protocols via $context context",
                'keys_modified' => array_keys($payload),
                'logo_updated' => ($logoPath !== null)
            ]);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'All administrative protocols successfully persisted in the system core.',
                'logo' => $logoPath
            ]);
        } catch (Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to persist protocols: ' . $e->getMessage()]);
        }

        exit;
    }

    public function fetch_payment_methods(): void
    {
        $this->requireActionPermission('fetch_payment_methods');


        $publicKey = $_POST['public_key'] ?? '';
        $secretKey = $_POST['secret_key'] ?? '';

        if (empty($publicKey) || empty($secretKey)) {
            echo json_encode(['success' => false, 'message' => 'Both API keys are required for discovery.']);
            exit;
        }

        // In a real scenario, we would use cURL to hit:
        // https://api.paymongo.com/v1/payment_methods (or similar capability endpoint)
        // For now, we validate key format and return supported methods
        $isValidKey = (strpos($publicKey, 'pk_') === 0 && strpos($secretKey, 'sk_') === 0);

        if ($isValidKey) {
            // Simulated response based on PayMongo's standard offerings
            $methods = [
                ['id' => 'card', 'name' => 'Credit / Debit Card', 'icon' => 'fa-solid fa-credit-card', 'desc' => 'Visa, Mastercard, JCB'],
                ['id' => 'gcash', 'name' => 'GCash', 'icon' => 'fa-solid fa-mobile-screen', 'desc' => 'Digital Wallet (Philippines)'],
                ['id' => 'grab_pay', 'name' => 'GrabPay', 'icon' => 'fa-solid fa-wallet', 'desc' => 'Grab Wallet Integration'],
                ['id' => 'paymaya', 'name' => 'Maya', 'icon' => 'fa-solid fa-money-bill-wave', 'desc' => 'PayMaya / Maya Digital'],
                ['id' => 'qrph', 'name' => 'QRPh', 'icon' => 'fa-solid fa-qrcode', 'desc' => 'Universal PH QR Standard'],
                ['id' => 'billease', 'name' => 'BillEase', 'icon' => 'fa-solid fa-calendar-check', 'desc' => 'Buy Now, Pay Later']
            ];

            echo json_encode([
                'success' => true,
                'methods' => $methods,
                'message' => 'Successfully discovered active gateway capabilities.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid API key format detected. Protocols rejected.']);
        }
        exit;
    }

    public function logs(): void
    {
        $this->requireActionPermission('logs');


        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';

        $this->render('admin/logs', [
            'roleLabel' => $roleLabel,
            'pageTitle' => 'System Audit Logs'
        ]);
    }

    public function logs_data(): void
    {
        $this->requireActionPermission('logs_data');

        header('Content-Type: application/json');

        $db = $this->db();

        $draw   = (int)($_GET['draw'] ?? 1);
        $start  = (int)($_GET['start'] ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = trim($_GET['search']['value'] ?? '');

        $orderColIdx = (int)($_GET['order'][0]['column'] ?? 0);
        $orderDir    = strtolower($_GET['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        // Columns mapped to orderable fields
        $cols = ['l.id', 'u.first_name', 'l.action', 't.first_name', 'l.created_at', 'l.id'];
        $orderCol = $cols[$orderColIdx] ?? 'l.created_at';

        $where = '1=1';
        $params = [];
        $types = '';

        if ($search !== '') {
            $where .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR l.action LIKE ? OR l.details LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
            $types .= 'ssss';
        }

        // Total count
        $stmtTotal = $db->query("SELECT COUNT(id) as c FROM activity_logs");
        $recordsTotal = $stmtTotal->fetch_assoc()['c'];

        // Filtered count
        $sqlFiltered = "SELECT COUNT(l.id) as c FROM activity_logs l 
                        JOIN users u ON l.admin_id = u.id 
                        LEFT JOIN users t ON l.target_id = t.id 
                        WHERE $where";
        if ($types) {
            $stmtF = $db->prepare($sqlFiltered);
            $stmtF->bind_param($types, ...$params);
            $stmtF->execute();
            $recordsFiltered = $stmtF->get_result()->fetch_assoc()['c'];
        } else {
            $recordsFiltered = $db->query($sqlFiltered)->fetch_assoc()['c'];
        }

        // Data query
        $sqlData = "SELECT l.*, u.first_name, u.last_name, 
                           t.first_name as target_first, t.last_name as target_last
                    FROM activity_logs l
                    JOIN users u ON l.admin_id = u.id
                    LEFT JOIN users t ON l.target_id = t.id
                    WHERE $where
                    ORDER BY $orderCol $orderDir
                    LIMIT ?, ?";

        $params[] = $start;
        $params[] = $length;
        $types .= 'ii';

        $stmtData = $db->prepare($sqlData);
        $stmtData->bind_param($types, ...$params);
        $stmtData->execute();
        $data = $stmtData->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => (int)$recordsTotal,
            "recordsFiltered" => (int)$recordsFiltered,
            "data" => $data
        ]);
    }

    public function delete_logs(): void
    {
        $this->requireActionPermission('delete_logs');
        $this->requireRole(['admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $logIds = $_POST['log_ids'] ?? [];
        if (empty($logIds) || !is_array($logIds)) {
            echo json_encode(['success' => false, 'message' => 'No logs selected for deletion.']); return;
        }

        $logIds = array_map('intval', $logIds);

        $logger = new ActivityLog($this->db());
        if ($logger->deleteMany($logIds)) {
            echo json_encode(['success' => true, 'message' => count($logIds) . ' log(s) successfully deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete selected logs.']);
        }
    }

    public function profile(): void
    {
        $this->requireActionPermission('profile');
        $this->requireLogin();
        $this->render('admin/profile', ['pageTitle' => 'Account Profile']);
    }

    public function get_profile_async(): void
    {
        $this->requireActionPermission('get_profile_async');
        $this->requireLogin();
        $userModel = new User($this->db());
        $user = $userModel->findById((int)$_SESSION['user_id']);

        if (!$user) {
            $this->json(['success' => false, 'message' => 'Identity not found.'], 404);
        }

        $role = strtolower($_SESSION['role'] ?? 'owner');
        $roleLabel = ($role === 'admin') ? 'Admin' : 'Owner';
        if ($role === 'tenant' || $role === 'user') $roleLabel = 'Tenant';

        $this->json([
            'success' => true,
            'user' => $user,
            'roleLabel' => $roleLabel
        ]);
    }

    public function update_profile(): void
    {
        $this->requireActionPermission('update_profile');
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.'], 405);
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token.'], 403);
        }

        $userId = (int)$_SESSION['user_id'];
        $firstName = Security::sanitize($_POST['first_name'] ?? '');
        $middleName = Security::sanitize($_POST['middle_name'] ?? '');
        $lastName = Security::sanitize($_POST['last_name'] ?? '');
        $username = Security::sanitize($_POST['username'] ?? '');
        $email = Security::sanitize($_POST['email'] ?? '');
        $phone = Security::sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? null;
        $confirm = $_POST['confirm_password'] ?? null;
        $imagePath = null;

        // Handle Image Upload
        if (!empty($_FILES['profile_image']['name'])) {
            $file = $_FILES['profile_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $this->json(['success' => false, 'message' => 'Invalid image format. Use JPG, PNG or WebP.']);
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                $this->json(['success' => false, 'message' => 'Image too large. Maximum size is 2MB.']);
            }

            $newName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $uploadDir = 'public/uploads/avatars/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                $imagePath = $uploadDir . $newName;
            }
        }

        $userModel = new User($this->db());
        $logger = new ActivityLog($this->db());

        // Validate
        if (empty($firstName) || empty($lastName) || empty($username) || empty($email)) {
            $this->json(['success' => false, 'message' => 'Required fields missing.']);
        }

        if ($userModel->isUsernameTaken($username, $userId)) {
            $this->json(['success' => false, 'message' => 'Username is already taken.']);
        }

        if ($userModel->isEmailTaken($email, $userId)) {
            $this->json(['success' => false, 'message' => 'Email is already taken.']);
        }

        if ($password !== '' && $password !== null && $password !== $confirm) {
            $this->json(['success' => false, 'message' => 'Passwords do not match.']);
        }

        $roleId = (int)$_SESSION['role_id'];

        if ($userModel->update($userId, $firstName, $middleName, $lastName, $username, $email, $phone, $roleId, $password, $imagePath)) {
            // Update session data
            $_SESSION['name'] = $firstName . ' ' . $lastName;
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            if ($imagePath) {
                $_SESSION['image'] = $imagePath;
            }

            $logger->log($userId, 'UPDATE_OWN_PROFILE', [
                'updated_fields' => ['first_name', 'last_name', 'username', 'email', 'phone'],
                'image_updated' => ($imagePath !== null)
            ]);

            $this->json([
                'success' => true, 
                'message' => 'Profile synchronized successfully.',
                'image' => $imagePath
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Critical: Database synchronization failed.']);
        }

    }



    public function move_out(): void
    {
        $this->requireActionPermission('move_out');
        $this->requireRole(['admin', 'owner']);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo $isAjax ? json_encode(['success' => false, 'message' => 'Method Not Allowed']) : 'Method Not Allowed';
            return;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $bookingIdRaw = $_POST['booking_id'] ?? null;
        $bookingId = is_numeric($bookingIdRaw) ? (int)$bookingIdRaw : 0;
        if ($bookingId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
            return;
        }

        $db           = $this->db();
        $role         = $_SESSION['role'] ?? '';
        $bookingModel = new Booking($db);

        // Admins don't own the house, so look up the actual owner from the booking
        if ($role === 'admin') {
            $stmt = $db->prepare('SELECT bh.owner_id FROM bookings b INNER JOIN rooms r ON r.id = b.room_id INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id WHERE b.id = ? LIMIT 1');
            $stmt->bind_param('i', $bookingId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $actingOwnerId = $row ? (int)$row['owner_id'] : (int)$_SESSION['user_id'];
        } else {
            $actingOwnerId = (int)$_SESSION['user_id'];
        }

        $ok = $bookingModel->moveOut($actingOwnerId, $bookingId);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Tenant moved out successfully.' : 'Failed to process move out.']);
            exit;
        }

        header('Location: /tenant/?url=admin/bookings');
        exit;
    }

    public function print_users(): void
    {
        $this->requireActionPermission('print_users');
        $this->requireRole(['admin']);
        $userModel = new User($this->db());
        $users = $userModel->all();

        $filter = $_GET['role'] ?? null;
        if ($filter === 'owners') {
            $users = array_filter($users, fn($u) => strtolower($u['role']) === 'owner');
        } elseif ($filter === 'tenants') {
            $users = array_filter($users, fn($u) => strtolower($u['role']) === 'tenant');
        }

        $this->render('admin/print_users', ['users' => $users, 'filter' => $filter]);
    }

    public function get_users_json(): void
    {
        $this->requireActionPermission('get_users_json');

        $userModel = new User($this->db());
        $users = $userModel->all();

        $role = $_GET['role'] ?? null;
        if ($role === 'owners') {
            $users = array_filter($users, fn($u) => strtolower($u['role']) === 'owner');
        } elseif ($role === 'tenants') {
            $users = array_filter($users, fn($u) => strtolower($u['role']) === 'tenant');
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => array_values($users)]); // array_values to reset indices after filter
        exit;
    }

    public function print_payments(): void
    {
        $this->requireActionPermission('print_payments');


        $status = $_GET['status'] ?? null;
        $houseId = isset($_GET['house_id']) && $_GET['house_id'] !== '' ? (int)$_GET['house_id'] : null;
        $role = strtolower($_SESSION['role'] ?? 'owner');
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $sql = "SELECT p.*, u.first_name, u.last_name, u.email, bh.name as boarding_house_name
                FROM payments p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN bookings b ON p.booking_id = b.id
                LEFT JOIN rooms r ON b.room_id = r.id
                LEFT JOIN boarding_houses bh ON r.boarding_house_id = bh.id";

        $where = [];
        if ($status) $where[] = "p.status = '" . $this->db()->real_escape_string($status) . "'";
        if ($houseId) $where[] = "bh.id = $houseId";
        if ($role === 'owner') {
            $where[] = "bh.owner_id = $userId";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.created_at DESC";

        $res = $this->db()->query($sql);
        $payments = $res->fetch_all(MYSQLI_ASSOC);

        $totalAmount = array_reduce($payments, fn($carry, $item) => $carry + (float)$item['amount'], 0);

        $this->render('admin/print_payments', [
            'payments' => $payments,
            'totalAmount' => $totalAmount,
            'filter_status' => $status,
            'filter_house_name' => $houseId ? ($payments[0]['boarding_house_name'] ?? 'Selected Property') : null
        ]);
    }

    public function toggle_user_status(): void
    {
        $this->requireActionPermission('toggle_user_status');
        $this->requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            http_response_code(403);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            $_SESSION['error'] = 'Invalid identity target.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        $userModel = new User($this->db());
        $logger = new ActivityLog($this->db());

        $user = $userModel->findById($userId);
        if (!$user) {
            $_SESSION['error'] = 'Identity record not found.';
            header('Location: /tenant/?url=admin/users');
            exit;
        }

        if ($userModel->toggleStatus($userId)) {
            $newStatus = ($user['status'] == 1) ? 'RESTRICTED' : 'ACTIVATED';
            $logger->log($_SESSION['user_id'], 'TOGGLE_STATUS', [
                'target' => $user['username'],
                'new_status' => $newStatus,
                'event' => 'Administrative Access Lockdown/Release'
            ], $userId);

            $_SESSION['success'] = "Identity status successfully updated to $newStatus.";
        } else {
            $_SESSION['error'] = 'Failed to modify identity status.';
        }

        header('Location: /tenant/?url=admin/users');
        exit;
    }

    private function getPayMongoSession(string $secretKey, string $sessionId): ?array
    {
        $ch = curl_init('https://api.paymongo.com/v1/checkout_sessions/' . $sessionId);
        if (!$ch) return null;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ':');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode((string)$response, true);
            return $data['data'] ?? null;
        }

        return null;
    }

    public function amenities(): void
    {
        $this->requireActionPermission('amenities');

        $this->render('admin/amenities', [
            'pageTitle' => 'Amenities Management'
        ]);
    }

    public function amenities_data(): void
    {
        $this->requireActionPermission('amenities_data');

        header('Content-Type: application/json');

        $model = new Amenity($this->db());
        $amenities = $model->all();

        echo json_encode(['success' => true, 'data' => $amenities]);
        exit;
    }

    public function store_amenity(): void
    {
        $this->requireActionPermission('store_amenity');

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token configuration.']);
            exit;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'icon' => trim($_POST['icon'] ?? 'fa-check')
        ];

        if (empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'Amenity name is required.']);
            exit;
        }

        $model = new Amenity($this->db());
        if ($model->create($data)) {
            $logger = new ActivityLog($this->db());
            $logger->log((int)$_SESSION['user_id'], 'AMENITY_CREATED', [
                'name' => $data['name'],
                'icon' => $data['icon']
            ]);
            echo json_encode(['success' => true, 'message' => 'Amenity created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create amenity.']);
        }
        exit;
    }

    public function update_amenity(): void
    {
        $this->requireActionPermission('update_amenity');

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token configuration.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'icon' => trim($_POST['icon'] ?? 'fa-check')
        ];

        if ($id <= 0 || empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid amenity data provided.']);
            exit;
        }

        $model = new Amenity($this->db());
        $old_amenity = $model->getById($id);

        if ($model->update($id, $data)) {
            $logger = new ActivityLog($this->db());
            $logger->log((int)$_SESSION['user_id'], 'AMENITY_UPDATED', [
                'id' => $id,
                'old_name' => $old_amenity['name'] ?? 'Unknown',
                'new_name' => $data['name'],
                'old_icon' => $old_amenity['icon'] ?? 'None',
                'new_icon' => $data['icon']
            ]);
            echo json_encode(['success' => true, 'message' => 'Amenity updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update amenity.']);
        }
        exit;
    }

    public function delete_amenity(): void
    {
        $this->requireActionPermission('delete_amenity');

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token configuration.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid amenity identifier.']);
            exit;
        }

        $model = new Amenity($this->db());
        $amenity = $model->getById($id);
        $name = $amenity['name'] ?? 'Unknown';

        if ($model->delete($id)) {
            $logger = new ActivityLog($this->db());
            $logger->log((int)$_SESSION['user_id'], 'AMENITY_DELETED', [
                'id' => $id,
                'name' => $name
            ]);
            echo json_encode(['success' => true, 'message' => 'Amenity deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot delete this amenity because it is currently assigned to one or more properties.']);
        }
        exit;
    }

    public function store_house(): void
    {
        $this->requireActionPermission('store_house');
        (new OwnerController())->store_house();
    }

    public function update_house(): void
    {
        $this->requireActionPermission('update_house');
        (new OwnerController())->update_house();
    }

    public function delete_house(): void
    {
        $this->requireActionPermission('delete_house');
        (new OwnerController())->delete_house();
    }

    public function upload_house_images(): void
    {
        $this->requireActionPermission('upload_house_images');
        (new OwnerController())->upload_house_images();
    }

    public function delete_house_image(): void
    {
        $this->requireActionPermission('delete_house_image');
        (new OwnerController())->delete_house_image();
    }

    public function get_house_images(): void
    {
        $this->requireActionPermission('get_house_images');
        (new OwnerController())->get_house_images();
    }

    public function get_house_amenities(): void
    {
        $this->requireActionPermission('get_house_amenities');
        (new OwnerController())->get_house_amenities();
    }

    public function update_house_amenities(): void
    {
        $this->requireActionPermission('update_house_amenities');
        (new OwnerController())->update_house_amenities();
    }

    public function load_more_houses(): void
    {
        $this->requireActionPermission('load_more_houses');
        (new OwnerController())->load_more_houses();
    }

}
