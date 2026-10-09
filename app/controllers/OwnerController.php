<?php
declare(strict_types=1);

final class OwnerController extends BaseController
{

    public function bookings(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        $houseId = isset($_GET['house_id']) ? (int)$_GET['house_id'] : null;

        $bookingModel = new Booking($this->db());
        $houseModel = new BoardingHouse($this->db());

        $bookings = $bookingModel->getForOwner($ownerId, $houseId);
        
        $house = null;
        if ($houseId) {
            $house = $houseModel->getById($houseId);
            // Verify ownership if house_id is provided
            if ($house && (int)$house['owner_id'] !== $ownerId) {
                $house = null;
                $bookings = []; // Don't show bookings if unauthorized
            }
        }

        $this->render('owner/bookings', [
            'bookings' => $bookings,
            'house' => $house,
            'pageTitle' => $house ? "Tenants: " . $house['name'] : "Global Tenant Ledger",
            'houseId' => $houseId
        ]);
    }

    public function bookings_data(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        $ownerId = (int)$_SESSION['user_id'];
        $bookingModel = new Booking($this->db());

        $draw = (int)($_POST['draw'] ?? 0);
        $start = (int)($_POST['start'] ?? 0);
        $length = (int)($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        $houseId = isset($_POST['house_id']) && $_POST['house_id'] !== '' ? (int)$_POST['house_id'] : null;
        $status = $_POST['status'] ?? null;
        $activeTab = $_POST['active_tab'] ?? ''; // 'approved' or 'pending' etc.

        // If the route was owner/bookings/approved, the JS will pass active_tab = 'approved'
        if ($activeTab === 'approved') {
            $status = 'approved';
        }

        $orderColumnIndex = (int)($_POST['order'][0]['column'] ?? 0);
        $orderDir = $_POST['order'][0]['dir'] ?? 'DESC';
        
        $columns = [
            0 => 'b.created_at',
            1 => 'tenant_name',
            2 => 'bh.name',
            3 => 'b.total_amount',
            4 => 'b.status'
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'b.created_at';

        $recordsTotal = $bookingModel->countForOwnerPaged($ownerId, '', $houseId, $status);
        $recordsFiltered = $bookingModel->countForOwnerPaged($ownerId, $search, $houseId, $status);
        $data = $bookingModel->getForOwnerPaged($ownerId, $length, $start, $search, $orderBy, $orderDir, $houseId, $status);

        // Security tokens
        $csrfToken = htmlspecialchars(Csrf::token());

        $formatted = [];
        foreach ($data as $t) {
            $isMovedOut = (bool)$t['is_moved_out'];
            $formatted[] = [
                'id' => $t['id'],
                'tenant_name' => htmlspecialchars($t['tenant_name']),
                'tenant_email' => htmlspecialchars($t['tenant_email'] ?? ''),
                'status' => $t['status'],
                'is_moved_out' => $isMovedOut,
                'total_amount' => (float)$t['total_amount'],
                'room_name' => htmlspecialchars($t['room_name']),
                'boarding_house_name' => htmlspecialchars($t['boarding_house_name']),
                'start_date' => $t['start_date'],
                'end_date' => $t['end_date'],
                'created_at' => $t['created_at'],
                'csrf_token' => $csrfToken
            ];
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formatted
        ]);
        exit;
    }

    public function bookings_history(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        $houseId = isset($_GET['house_id']) ? (int)$_GET['house_id'] : null;

        $bookingModel = new Booking($this->db());
        $houseModel = new BoardingHouse($this->db());

        $allBookings = $bookingModel->getForOwner($ownerId, $houseId);
        
        // Filter for moved out residents only
        $history = array_filter($allBookings, fn($b) => (bool)$b['is_moved_out']);
        
        $house = null;
        if ($houseId) {
            $house = $houseModel->getById($houseId);
            if ($house && (int)$house['owner_id'] !== $ownerId) {
                $house = null;
                $history = [];
            }
        }

        $allHouses = $houseModel->getByOwnerId($ownerId);

        $this->render('owner/bookings_history', [
            'history' => $history,
            'house' => $house,
            'houses' => $allHouses,
            'pageTitle' => $house ? "Residency History: " . $house['name'] : "Global Portfolio History"
        ]);
    }

    public function bookings_print_history(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        $houseId = isset($_POST['house_id']) || isset($_GET['house_id']) ? (int)($_POST['house_id'] ?? $_GET['house_id']) : null;

        $bookingModel = new Booking($this->db());
        $houseModel = new BoardingHouse($this->db());

        $allBookings = $bookingModel->getForOwner($ownerId, $houseId);
        $history = array_filter($allBookings, fn($b) => (bool)$b['is_moved_out']);

        $house = null;
        if ($houseId) {
            $house = $houseModel->getById($houseId);
            if ($house && (int)$house['owner_id'] !== $ownerId) {
                die("Unauthorized access to property records.");
            }
        }

        $this->render('owner/print_history', [
            'history' => $history,
            'house' => $house,
            'pageTitle' => 'Archival Residency Summary: ' . ($house ? $house['name'] : 'Global Portfolio')
        ]);
    }

    public function payments(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        
        $tenancyId = (int)($_POST['tenancy_id'] ?? $_GET['tenancy_id'] ?? 0);
        if ($tenancyId <= 0) {
            // If no tenancy specified, default to earnings dashboard
            $this->payments_earnings();
            return;
        }

        $bookingModel = new Booking($this->db());
        $paymentModel = new Payment($this->db());

        $booking = $bookingModel->getById($tenancyId, $ownerId);
        if (!$booking) {
            $_SESSION['error'] = 'Unauthorized access or tenancy record not found.';
            $this->redirect('owner/bookings/approved');
        }

        $payments = $paymentModel->getForBooking($tenancyId);
        $totalPaid = $paymentModel->getTotalPaidForBooking($tenancyId);

        $this->render('owner/payments', [
            'booking' => $booking,
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'pageTitle' => 'Residency Ledger: ' . $booking['tenant_name']
        ]);
    }

    public function payments_earnings(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];

        $paymentModel = new Payment($this->db());
        $houseModel = new BoardingHouse($this->db());

        $totalRevenue = $paymentModel->getTotalRevenueForOwner($ownerId);
        $allHouses = $houseModel->getByOwnerId($ownerId);

        // Calculate revenue per house
        foreach ($allHouses as &$house) {
            $house['revenue'] = 0.0;
            $stmt = $this->db()->prepare("
                SELECT SUM(p.amount) as total
                FROM payments p
                INNER JOIN bookings b ON b.id = p.booking_id
                INNER JOIN rooms r ON r.id = b.room_id
                WHERE r.boarding_house_id = ? AND p.status = 'paid'
            ");
            $stmt->bind_param('i', $house['id']);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $house['revenue'] = (float)($res['total'] ?? 0.0);
        }

        $this->render('owner/earnings', [
            'totalRevenue' => $totalRevenue,
            'houses' => $allHouses,
            'pageTitle' => 'Financial Performance Dashboard'
        ]);
    }

    public function payments_transactions(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        $houseId = isset($_POST['house_id']) || isset($_GET['house_id']) ? (int)($_POST['house_id'] ?? $_GET['house_id']) : null;
        
        $houseName = null;
        if ($houseId) {
            $bhModel = new BoardingHouse($this->db());
            $h = $bhModel->getById($houseId);
            $houseName = $h ? $h['name'] : null;
        }

        $this->render('owner/transactions', [
            'transactions' => [], // Now handled via AJAX
            'houseId' => $houseId,
            'houseName' => $houseName,
            'pageTitle' => $houseName ? "Transactions: $houseName" : 'Portfolio Transaction History'
        ]);
    }

    public function transactions_data(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        $ownerId = (int)$_SESSION['user_id'];
        $paymentModel = new Payment($this->db());

        // DataTables parameters
        $draw = (int)($_POST['draw'] ?? 0);
        $start = (int)($_POST['start'] ?? 0);
        $length = (int)($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        $houseId = isset($_POST['house_id']) && $_POST['house_id'] !== '' ? (int)$_POST['house_id'] : null;
        $status = $_POST['status'] ?? null;

        // Sorting
        $orderColumnIndex = (int)($_POST['order'][0]['column'] ?? 0);
        $orderDir = $_POST['order'][0]['dir'] ?? 'DESC';
        
        $columns = [
            0 => 'p.created_at',
            1 => 'u.first_name',
            2 => 'bh.name',
            3 => 'p.payment_method',
            4 => 'p.transaction_ref',
            5 => 'p.amount',
            6 => 'p.status'
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'p.created_at';

        $recordsTotal = $paymentModel->countAllWithDetails($ownerId, '', $houseId, $status);
        $recordsFiltered = $paymentModel->countAllWithDetails($ownerId, $search, $houseId, $status);
        $data = $paymentModel->allWithDetailsPaged($ownerId, $length, $start, $search, $orderBy, $orderDir, $houseId, $status);

        $formatted = [];
        foreach ($data as $t) {
            $formatted[] = [
                'date' => date('M d, Y', strtotime($t['created_at'])),
                'tenant' => [
                    'name' => htmlspecialchars($t['first_name'] . ' ' . $t['last_name']),
                    'email' => htmlspecialchars($t['email'])
                ],
                'property' => htmlspecialchars($t['boarding_house_name'] ?? 'N/A'),
                'method' => htmlspecialchars($t['payment_method']),
                'reference' => htmlspecialchars($t['transaction_ref'] ?: '---'),
                'amount' => '₱' . number_format((float)$t['amount'], 2),
                'status' => $t['status'],
                'status_class' => $t['status'] === 'paid' ? 'pill-paid' : 'pill-pending'
            ];
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formatted
        ]);
        exit;
    }

    public function payments_print_transactions(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        $houseId = isset($_POST['house_id']) || isset($_GET['house_id']) ? (int)($_POST['house_id'] ?? $_GET['house_id']) : null;

        $paymentModel = new Payment($this->db());
        $transactions = $paymentModel->allWithDetails($ownerId, null, $houseId);

        // Summary calculations
        $totals = [
            'paid' => 0.0,
            'pending' => 0.0,
            'failed' => 0.0,
            'count' => count($transactions)
        ];

        foreach ($transactions as $t) {
            $status = strtolower($t['status']);
            if (isset($totals[$status])) {
                $totals[$status] += (float)$t['amount'];
            }
        }

        $houseName = null;
        if ($houseId) {
            $bhModel = new BoardingHouse($this->db());
            $h = $bhModel->getById($houseId);
            $houseName = $h ? $h['name'] : null;
        }

        $this->render('owner/print_transactions', [
            'transactions' => $transactions,
            'totals' => $totals,
            'houseName' => $houseName,
            'pageTitle' => 'Portfolio Transaction Audit: ' . date('M d, Y')
        ]);
    }

    public function analytics(): void
    {
        $this->payments_earnings();
    }

    public function tenants(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        
        $bookingModel = new Booking($this->db());
        $allBookings = $bookingModel->getForOwner($ownerId);
        
        // Filter for active tenants only
        $tenants = array_filter($allBookings, fn($b) => $b['status'] === 'approved' && !(bool)$b['is_moved_out']);

        $houseModel = new BoardingHouse($this->db());
        $houses = $houseModel->getByOwnerId($ownerId);

        $this->render('owner/tenants', [
            'tenants' => $tenants,
            'houses' => $houses,
            'pageTitle' => 'Active Resident Directory'
        ]);
    }

    public function approve_booking(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $ownerId   = (int)$_SESSION['user_id'];

        if (!$bookingId) {
            echo json_encode(['success' => false, 'message' => 'Booking ID is required.']); return;
        }

        $bookingModel = new Booking($this->db());
        if ($bookingModel->approvePending($bookingId, $ownerId)) {
            $logger = new ActivityLog($this->db());
            $logger->log($ownerId, 'OWNER_BOOKING_APPROVED', ['booking_id' => $bookingId], $bookingId);
            echo json_encode(['success' => true, 'message' => 'Booking request has been approved.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve booking. It may not be pending or not belong to your properties.']);
        }
    }

    public function reject_booking(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $ownerId   = (int)$_SESSION['user_id'];

        if (!$bookingId) {
            echo json_encode(['success' => false, 'message' => 'Booking ID is required.']); return;
        }

        $bookingModel = new Booking($this->db());
        if ($bookingModel->rejectPending($bookingId, $ownerId)) {
            $logger = new ActivityLog($this->db());
            $logger->log($ownerId, 'OWNER_BOOKING_REJECTED', ['booking_id' => $bookingId], $bookingId);
            echo json_encode(['success' => true, 'message' => 'Booking request has been declined.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject booking.']);
        }
    }

    public function get_residency_payments_json(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        $ownerId = (int)$_SESSION['user_id'];
        $tenancyId = (int)($_GET['tenancy_id'] ?? 0);

        if ($tenancyId <= 0) {
            echo json_encode(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Invalid ID']);
            return;
        }

        // Verify ownership
        $bookingModel = new Booking($this->db());
        $booking = $bookingModel->getById($tenancyId, $ownerId);
        if (!$booking) {
            http_response_code(403);
            echo json_encode(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Unauthorized']);
            return;
        }

        $paymentModel = new Payment($this->db());

        // DataTables parameters
        $draw = (int)($_GET['draw'] ?? 0);
        $start = (int)($_GET['start'] ?? 0);
        $length = (int)($_GET['length'] ?? 10);
        $search = Security::sanitize($_GET['search']['value'] ?? '');

        $recordsTotal = $paymentModel->countForBooking($tenancyId);
        $recordsFiltered = $paymentModel->countForBooking($tenancyId, $search);
        $data = $paymentModel->getForBookingPaged($tenancyId, $length, $start, $search);

        // Format data for the view
        $formatted = [];
        foreach ($data as $p) {
            $formatted[] = [
                'id' => $p['id'],
                'ref_date' => [
                    'ref' => $p['transaction_ref'] ?: 'N/A',
                    'date' => date('M d, Y h:i A', strtotime($p['created_at']))
                ],
                'description' => [
                    'type' => ucfirst($p['payment_type'] ?: 'Rent Payment'),
                    'desc' => htmlspecialchars($p['description'] ?: 'Monthly residency fee')
                ],
                'method' => htmlspecialchars($p['payment_method']),
                'amount' => '₱' . number_format((float)$p['amount'], 2),
                'status' => $p['status'],
                'actions' => [
                    'can_void' => strpos($p['transaction_ref'], 'MANUAL-') === 0 && $p['status'] === 'paid',
                    'can_approve' => $p['status'] === 'pending',
                    'id' => $p['id']
                ],
                'raw_amount' => $p['amount']
            ];
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formatted
        ]);
        exit;
    }

    public function record_manual_payment(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('owner/bookings/approved');
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Invalid CSRF token';
            return;
        }

        $tenancyId = (int)($_POST['tenancy_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = Security::sanitize($_POST['method'] ?? 'Cash');
        $type = Security::sanitize($_POST['payment_type'] ?? 'rent');
        $date = Security::sanitize($_POST['date'] ?? date('Y-m-d H:i:s'));
        $desc = Security::sanitize($_POST['description'] ?? '');
        $months = (int)($_POST['months_covered'] ?? 1);

        if ($tenancyId <= 0 || $amount <= 0) {
            $_SESSION['error'] = 'Invalid payment details provided.';
            $this->redirect('owner/bookings/approved');
        }

        $bookingModel = new Booking($this->db());
        $paymentModel = new Payment($this->db());

        // Verify ownership and get tenant ID
        $booking = $bookingModel->getById($tenancyId, $ownerId);
        if (!$booking) {
            $_SESSION['error'] = 'Unauthorized or record not found.';
            $this->redirect('owner/bookings/approved');
        }

        // We need the user_id (tenant) to attribute the payment correctly
        // Booking::getById already retrieves tenant info indirectly, but let's be sure about the user_id
        $stmt = $this->db()->prepare('SELECT user_id FROM bookings WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $tenancyId);
        $stmt->execute();
        $tenantRes = $stmt->get_result()->fetch_assoc();
        $tenantId = (int)($tenantRes['user_id'] ?? 0);

        // Validation: If no balance, don't accept payment
        $totalPaid = $paymentModel->getTotalPaidForBooking($tenancyId);
        $totalContract = (float)$booking['total_amount'];
        $outstanding = $totalContract - $totalPaid;

        if ($outstanding <= 0) {
            $_SESSION['error'] = "This renter has no outstanding balance (Paid: ₱" . number_format($totalPaid, 2) . " / ₱" . number_format($totalContract, 2) . ")";
            $this->redirect('owner/bookings/approved');
        }

        if ($amount > $outstanding) {
            // Optional: You could cap the amount or allow overpayment/credit. 
            // For now, let's just warn if they exceed by a lot, or just allow it but log it.
            // But the user specifically said "if renter has no balance don't accept".
        }

        if ($paymentModel->createManual($tenantId, $tenancyId, $amount, $method, $type, $desc, $date, $months)) {
            $_SESSION['success'] = "Payment of ₱" . number_format($amount, 2) . " recorded successfully.";
            
            // Log activity
            $logger = new ActivityLog($this->db());
            $logger->log($ownerId, 'OWNER_MANUAL_PAYMENT', [
                'tenancy_id' => $tenancyId,
                'amount' => $amount,
                'method' => $method
            ], $tenancyId);
        } else {
            $_SESSION['error'] = "Failed to record manual payment.";
        }

        // Stay on the same ledger page (requires POSTing back or just redirecting with session success)
        // Since the ledger page uses POST normally, we'll need to redirect back to the ledger view
        // But the ledger view requires POST. This is a bit tricky. 
        // For now, let's redirect to approved bookings and they can re-click. 
        // Better: We can store the tenancy_id in session if we want to redirect back specifically.
        $this->redirect('owner/ledger&tenancy_id=' . $tenancyId);
    }

    public function void_payment(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $tenancyId = (int)($_POST['tenancy_id'] ?? 0);

        if ($paymentId <= 0 || $tenancyId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Request']);
            exit;
        }

        // Verify ownership of the booking
        $bookingModel = new Booking($this->db());
        $booking = $bookingModel->getById($tenancyId, $ownerId);
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $paymentModel = new Payment($this->db());
        if ($paymentModel->deleteManually($paymentId, $tenancyId)) {
            // Log activity
            $logger = new ActivityLog($this->db());
            $logger->log($ownerId, 'OWNER_VOID_PAYMENT', [
                'payment_id' => $paymentId,
                'tenancy_id' => $tenancyId
            ], $tenancyId);

            echo json_encode(['success' => true, 'message' => 'Payment voided successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to void payment or payment is not manual.']);
        }
        exit;
    }

    public function approve_pending_payment(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $tenancyId = (int)($_POST['tenancy_id'] ?? 0);

        if ($paymentId <= 0 || $tenancyId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Request']);
            exit;
        }

        // Verify ownership of the booking
        $bookingModel = new Booking($this->db());
        $booking = $bookingModel->getById($tenancyId, $ownerId);
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $paymentModel = new Payment($this->db());
        if ($paymentModel->markPaid($paymentId, 'MANUAL-OVERRIDE-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)))) {
            // Log activity
            $logger = new ActivityLog($this->db());
            $logger->log($ownerId, 'OWNER_APPROVE_PAYMENT', [
                'payment_id' => $paymentId,
                'tenancy_id' => $tenancyId
            ], $tenancyId);

            echo json_encode(['success' => true, 'message' => 'Payment approved successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve payment.']);
        }
        exit;
    }

    public function print_statement(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        
        $tenancyId = (int)($_POST['tenancy_id'] ?? $_GET['tenancy_id'] ?? 0);
        if ($tenancyId <= 0) {
            die("Invalid Request");
        }

        $bookingModel = new Booking($this->db());
        $paymentModel = new Payment($this->db());

        $booking = $bookingModel->getById($tenancyId, $ownerId);
        if (!$booking) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $payments = $paymentModel->getForBooking($tenancyId);
        $totalPaid = $paymentModel->getTotalPaidForBooking($tenancyId);

        $this->render('owner/print_statement', [
            'booking' => $booking,
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'pageTitle' => 'Statement of Account: ' . $booking['tenant_name']
        ]); 
    }

    public function houses(): void
    {
        $this->requireRole(['owner', 'admin']);

        if (isset($_GET['status']) && $_GET['status'] === 'cancelled') {
            $_SESSION['error'] = 'Payment process was cancelled. No changes were made.';
            header('Location: /tenant/?url=owner/houses');
            exit;
        }
        $ownerId = (int)$_SESSION['user_id'];
        $model = new BoardingHouse($this->db());
        
        $limit = 10;
        $totalHousesCount = $model->countByOwnerId($ownerId);
        $houses = $model->getByOwnerId($ownerId, $limit, 0);
        $paymentModel = new Payment($this->db());
        $totalRevenue = $paymentModel->getTotalRevenueForOwner($ownerId);
        
        // Enrich houses with room stats
        foreach ($houses as &$house) {
            $house['rooms'] = $model->getRoomsByBoardingHouseId((int)$house['id']);
            $house['amenities'] = $model->getAmenitiesForBoardingHouse((int)$house['id']);
            $house['images'] = $model->getImages((int)$house['id']);
        }
        
        $subModel = new Subscription($this->db());
        $limits = $subModel->getLimitsForOwner($ownerId);
        
        // Fix: Fetch owner's active subscription ID to allow direct plan upgrades from the modal
        $activeSubQuery = $this->db()->prepare("SELECT id FROM subscriptions WHERE owner_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
        $activeSubQuery->bind_param('i', $ownerId);
        $activeSubQuery->execute();
        $activeSubRes = $activeSubQuery->get_result()->fetch_assoc();
        $activeSubId = $activeSubRes ? (int)$activeSubRes['id'] : 0;

        $planModel = new Plan($this->db());
        $allPlans = $planModel->getAll();

        $settingModel = new SystemSetting($this->db());
        $paymentSettings = $settingModel->getAll();
        
        $this->render('owner/houses', [
            'houses' => $houses,
            'totalHousesCount' => $totalHousesCount,
            'totalRevenue' => $totalRevenue,
            'limits' => $limits,
            'activeSubId' => $activeSubId,
            'allPlans' => $allPlans,
            'paymentSettings' => $paymentSettings,
            'pageTitle' => 'Manage My Properties'
        ]);
    }

    public function store_house(): void
    {
        $this->requireRole(['owner', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('owner/houses');
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token configuration.';
            $this->redirect('owner/houses');
        }

        $address = trim($_POST['address'] ?? '');
        if (!$this->validateAddress($address)) {
            $_SESSION['error'] = 'Invalid address format. Required: street/purok, barangay, city, province, country';
            $this->redirect('owner/houses');
        }

        $ownerId = (int)$_SESSION['user_id'];
        $subModel = new Subscription($this->db());
        $limits = $subModel->getLimitsForOwner($ownerId);
        $model = new BoardingHouse($this->db());
        $currentCount = $model->countByOwnerId($ownerId);

        if ($limits['bhouse_limit'] > 0 && $currentCount >= $limits['bhouse_limit']) {
            $_SESSION['error'] = 'Property limit reached. Please upgrade your plan to add more boarding houses.';
            $this->redirect('owner/houses');
        }
        $lat = $_POST['latitude'] ?? '';
        $lng = $_POST['longitude'] ?? '';

        $data = [
            'owner_id' => (int)$_SESSION['user_id'],
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'address' => $address,
            'latitude' => $lat !== '' ? (float)$lat : null,
            'longitude' => $lng !== '' ? (float)$lng : null
        ];

        if ($model->create($data)) {
            $newId = $this->db()->insert_id;
            $logger = new ActivityLog($this->db());
            $logger->log(
                (int)$_SESSION['user_id'],
                'OWNER_HOUSE_CREATED',
                [
                    'house_id'    => $newId,
                    'name'        => $data['name'],
                    'address'     => $data['address'],
                    'description' => $data['description'],
                    'status'      => 'pending',
                    'ip'          => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ],
                $newId
            );
            $_SESSION['success'] = 'Property successfully submitted for administrative review.';
        } else {
            $_SESSION['error'] = 'Failed to register property. Please check your inputs.';
        }

        $this->redirect('owner/houses');
    }

    public function update_house(): void
    {
        $this->requireRole(['owner', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('owner/houses');
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Security validation failed.';
            $this->redirect('owner/houses');
        }

        $houseId = (int)($_POST['house_id'] ?? 0);
        $model = new BoardingHouse($this->db());
        
        // Ownership Verification
        $existing = $model->getById($houseId);
        if (!$existing || (int)$existing['owner_id'] !== (int)$_SESSION['user_id']) {
            $_SESSION['error'] = 'Unauthorized access to property identity.';
            $this->redirect('owner/houses');
        }

        $address = trim($_POST['address'] ?? '');
        if (!$this->validateAddress($address)) {
            $_SESSION['error'] = 'Format error: street/purok, barangay, city, province, country required.';
            $this->redirect('owner/houses');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'address' => $address,
            'latitude' => (float)($_POST['latitude'] ?? 0),
            'longitude' => (float)($_POST['longitude'] ?? 0)
        ];

        if ($model->update($houseId, $data)) {
            $logger = new ActivityLog($this->db());
            $logger->log(
                (int)$_SESSION['user_id'],
                'OWNER_HOUSE_UPDATED',
                [
                    'house_id'    => $houseId,
                    'name'        => $data['name'],
                    'address'     => $data['address'],
                    'description' => $data['description'],
                    'latitude'    => $data['latitude'],
                    'longitude'   => $data['longitude'],
                    'ip'          => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ],
                $houseId
            );
            $_SESSION['success'] = 'Property details successfully updated.';
        } else {
            $_SESSION['error'] = 'Failed to update property meta-data.';
        }

        $this->redirect('owner/houses');
    }

    public function delete_house(): void
    {
        $this->requireRole(['owner', 'admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('owner/houses');
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid cryptographic token.';
            $this->redirect('owner/houses');
        }

        $houseId = (int)($_POST['house_id'] ?? 0);
        $model = new BoardingHouse($this->db());

        // Ownership Verification
        $existing = $model->getById($houseId);
        if (!$existing || (int)$existing['owner_id'] !== (int)$_SESSION['user_id']) {
            $_SESSION['error'] = 'Destructive action blocked: Ownership mismatch.';
            $this->redirect('owner/houses');
        }

        if ($model->delete($houseId)) {
            $logger = new ActivityLog($this->db());
            $logger->log(
                (int)$_SESSION['user_id'],
                'OWNER_HOUSE_DELETED',
                [
                    'house_id'     => $houseId,
                    'house_name'   => $existing['name'],
                    'house_address'=> $existing['address'],
                    'soft_delete'  => true,
                    'ip'           => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ],
                $houseId
            );
            $_SESSION['success'] = 'Property has been permanently removed from the system.';
        } else {
            $_SESSION['error'] = 'Failed to decommission property record.';
        }

        $this->redirect('owner/houses');
    }

    public function move_out(): void
    {
        $this->requireRole(['owner', 'admin']);
        
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

        $tenancyIdRaw = $_POST['tenancy_id'] ?? null;
        $tenancyId = is_numeric($tenancyIdRaw) ? (int)$tenancyIdRaw : 0;
        
        $endDate = $_POST['end_date'] ?? null;

        if ($tenancyId <= 0) {
            $_SESSION['error'] = 'Invalid tenancy identity provided.';
            header('Location: /tenant/?url=owner/bookings/approved');
            return;
        }

        $bookingModel = new Booking($this->db());
        if ($bookingModel->moveOut((int)$_SESSION['user_id'], $tenancyId, $endDate)) {
            $logger = new ActivityLog($this->db());
            $logger->log(
                (int)$_SESSION['user_id'],
                'OWNER_TENANT_MOVED_OUT',
                [
                    'tenancy_id' => $tenancyId,
                    'end_date'   => $endDate ?: date('Y-m-d'),
                    'actor_role' => $_SESSION['role'] ?? 'owner',
                    'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ],
                $tenancyId
            );
            $_SESSION['success'] = 'Tenant has been successfully moved out and the room is now available.';
        } else {
            $_SESSION['error'] = 'Failed to process move-out. The residency might already be closed or unauthorized.';
        }

        header('Location: /tenant/?url=owner/bookings/approved');
        exit;
    }

    public function upload_house_images(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $houseId = (int)($_POST['house_id'] ?? 0);
        $model = new BoardingHouse($this->db());
        $house = $model->getById($houseId);

        if (!$house || (int)$house['owner_id'] !== (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']); return;
        }

        $existing = $model->countImages($houseId);
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $uploadDir = __DIR__ . '/../../public/assets/uploads/bhouse_images/';
        $webPath = '/tenant/public/assets/uploads/bhouse_images/';
        $maxSize = 5 * 1024 * 1024; // 5MB per file

        $files = $_FILES['images'] ?? null;
        if (!$files || empty($files['name'][0])) {
            echo json_encode(['success' => false, 'message' => 'No files provided.']); return;
        }

        $count = count($files['name']);
        if (($existing + $count) > 10) {
            echo json_encode(['success' => false, 'message' => "Upload limit exceeded. Max 10 images per property. Currently has $existing."]); return;
        }

        $saved = [];
        for ($i = 0; $i < $count; $i++) {
            $tmpName = $files['tmp_name'][$i];
            $origName = $files['name'][$i];
            $size = $files['size'][$i];
            $error = $files['error'][$i];

            if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($tmpName)) continue;
            if ($size > $maxSize) continue;

            $mime = mime_content_type($tmpName);
            if (!in_array($mime, $allowed, true)) continue;

            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;

            $filename = 'bh_' . $houseId . '_' . uniqid('', true) . '.' . $ext;
            if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                $path = $webPath . $filename;
                $model->addImage($houseId, $path, $existing + count($saved));
                $saved[] = ['path' => $path, 'original_name' => $origName, 'size_bytes' => $size];
            }
        }

        if (!empty($saved)) {
            $logger = new ActivityLog($this->db());
            $logger->log(
                (int)$_SESSION['user_id'],
                'OWNER_HOUSE_IMAGES_UPLOADED',
                [
                    'house_id'   => $houseId,
                    'house_name' => $house['name'],
                    'count'      => count($saved),
                    'files'      => array_map(fn($s) => ['name' => $s['original_name'], 'size' => $s['size_bytes'], 'path' => $s['path']], $saved),
                    'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ],
                $houseId
            );
        }

        echo json_encode(['success' => true, 'uploaded' => count($saved), 'images' => array_map(fn($s) => ['path' => $s['path']], $saved)]);
    }

    public function delete_house_image(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $imageId = (int)($_POST['image_id'] ?? 0);
        $houseId = (int)($_POST['house_id'] ?? 0);
        $model = new BoardingHouse($this->db());

        $house = $model->getById($houseId);
        if (!$house || (int)$house['owner_id'] !== (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']); return;
        }

        $path = $model->deleteImage($imageId, $houseId);
        if ($path !== null) {
            $physPath = __DIR__ . '/../../public/assets/uploads/bhouse_images/' . basename($path);
            if (file_exists($physPath)) @unlink($physPath);

            $logger = new ActivityLog($this->db());
            $logger->log(
                (int)$_SESSION['user_id'],
                'OWNER_HOUSE_IMAGE_DELETED',
                [
                    'house_id'   => $houseId,
                    'house_name' => $house['name'],
                    'image_id'   => $imageId,
                    'image_path' => $path,
                    'file_name'  => basename($path),
                    'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ],
                $houseId
            );

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Image not found.']);
        }
    }

    public function get_house_images(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        $houseId = (int)($_GET['house_id'] ?? 0);
        $model = new BoardingHouse($this->db());
        $house = $model->getById($houseId);

        if (!$house || (int)$house['owner_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode([]); return;
        }

        echo json_encode($model->getImages($houseId));
    }

    public function get_house_amenities(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        $houseId = (int)($_GET['house_id'] ?? 0);
        $model = new BoardingHouse($this->db());
        $house = $model->getById($houseId);

        if (!$house || (int)$house['owner_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); return;
        }

        $all = $model->getAllAmenities();
        $selected = $model->getAmenitiesForBoardingHouse($houseId);

        $selectedIds = array_column($selected, 'id');
        
        echo json_encode(['success' => true, 'all' => $all, 'selected_ids' => $selectedIds]);
    }

    public function update_house_amenities(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $houseId = (int)($_POST['house_id'] ?? 0);
        $amenityIds = $_POST['amenities'] ?? [];
        if (!is_array($amenityIds)) $amenityIds = [];

        $model = new BoardingHouse($this->db());
        $house = $model->getById($houseId);

        if (!$house || (int)$house['owner_id'] !== (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']); return;
        }

        if ($model->syncAmenitiesForBoardingHouse($houseId, $amenityIds)) {
            $logger = new ActivityLog($this->db());
            $logger->log(
                (int)$_SESSION['user_id'],
                'OWNER_HOUSE_AMENITIES_UPDATED',
                [
                    'house_id'   => $houseId,
                    'house_name' => $house['name'],
                    'amenity_count' => count($amenityIds),
                    'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ],
                $houseId
            );
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed.']);
        }
    }

    private function validateAddress(string $address): bool
    {
        $parts = array_map('trim', explode(',', $address));
        return count($parts) === 5 && !empty($parts[0]) && !empty($parts[1]) && !empty($parts[2]) && !empty($parts[3]) && !empty($parts[4]);
    }

    public function load_more_houses(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = 10;
        
        $model = new BoardingHouse($this->db());
        $houses = $model->getByOwnerId($ownerId, $limit, $offset);
        
        foreach ($houses as &$house) {
            $house['rooms'] = $model->getRoomsByBoardingHouseId((int)$house['id']);
            $house['amenities'] = $model->getAmenitiesForBoardingHouse((int)$house['id']);
            $house['images'] = $model->getImages((int)$house['id']);
        }
        
        foreach ($houses as $h) {
            include __DIR__ . '/../views/owner/_house_card.php';
        }
    }

    public function rooms(): void
    {
        $this->requireRole(['owner', 'admin']);
        $ownerId = (int)$_SESSION['user_id'];
        
        $houseModel = new BoardingHouse($this->db());
        $houses = $houseModel->getByOwnerId($ownerId, 100, 0); // Get all active houses for the dropdown
        
        // Enrich houses with room stats
        foreach ($houses as &$house) {
            $house['rooms'] = $houseModel->getRoomsByBoardingHouseId((int)$house['id']);
            $house['images'] = $houseModel->getImages((int)$house['id']);
        }
        
        $subModel = new Subscription($this->db());
        $limits = $subModel->getLimitsForOwner($ownerId);
        
        // Fetch active sub ID for upgrade modal
        $activeSubQuery = $this->db()->prepare("SELECT id FROM subscriptions WHERE owner_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
        $activeSubQuery->bind_param('i', $ownerId);
        $activeSubQuery->execute();
        $activeSubRes = $activeSubQuery->get_result()->fetch_assoc();
        $activeSubId = $activeSubRes ? (int)$activeSubRes['id'] : 0;

        $planModel = new Plan($this->db());
        $allPlans = $planModel->getAll();

        $settingModel = new SystemSetting($this->db());
        $paymentSettings = $settingModel->getAll();

        $this->render('owner/rooms', [
            'houses' => $houses,
            'limits' => $limits,
            'activeSubId' => $activeSubId,
            'allPlans' => $allPlans,
            'paymentSettings' => $paymentSettings,
            'pageTitle' => 'My Rooms Management'
        ]);
    }

    public function get_all_rooms_json(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');
        
        $ownerId = (int)$_SESSION['user_id'];
        $houseModel = new BoardingHouse($this->db());
        $houses = $houseModel->getByOwnerId($ownerId, 100, 0);
        
        foreach ($houses as &$house) {
            $house['rooms'] = $houseModel->getRoomsByBoardingHouseId((int)$house['id']);
        }
        
        echo json_encode(['success' => true, 'data' => $houses]);
    }

    public function get_room(): void
    {
        $this->requireRole(['owner', 'admin']);
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'Room ID required.']);
            return;
        }
        $roomId = (int)$_GET['id'];
        $bhModel = new BoardingHouse($this->db());
        $room = $bhModel->getRoomById($roomId);
        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Room not found.']);
            return;
        }
        // Verify ownership
        $house = $bhModel->getById((int)$room['boarding_house_id']);
        if (!$house || (int)$house['owner_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            return;
        }
        echo json_encode(['success' => true, 'room' => $room]);
    }

    public function store_room(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $ownerId = (int)$_SESSION['user_id'];
        $bhModel = new BoardingHouse($this->db());
        
        // Verify property ownership
        $houseId = (int)($_POST['boarding_house_id'] ?? 0);
        $house = $bhModel->getById($houseId);
        if (!$house || (int)$house['owner_id'] !== $ownerId) {
            echo json_encode(['success' => false, 'message' => 'Invalid property selected or unauthorized.']); return;
        }

        // Check limits
        $subModel = new Subscription($this->db());
        $limits = $subModel->getLimitsForOwner($ownerId);
        $stats = $bhModel->getRoomStatsByOwner($ownerId);
        
        if ($limits['room_limit'] > 0 && $stats['total_rooms'] >= $limits['room_limit']) {
            echo json_encode(['success' => false, 'message' => 'LIMIT_REACHED']); return;
        }

        $data = [
            'boarding_house_id' => $houseId,
            'room_name' => trim($_POST['room_name'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'capacity' => (int)($_POST['capacity'] ?? 1),
            'available_slots' => (int)($_POST['capacity'] ?? 1)
        ];

        if (empty($data['room_name']) || $data['price'] <= 0 || $data['capacity'] < 1) {
            echo json_encode(['success' => false, 'message' => 'Please provide valid room details.']); return;
        }

        if ($bhModel->createRoom($data)) {
            echo json_encode(['success' => true, 'message' => 'Room successfully registered.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create room.']);
        }
    }

    public function update_room(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $roomId = (int)($_POST['room_id'] ?? 0);
        $bhModel = new BoardingHouse($this->db());
        $room = $bhModel->getRoomById($roomId);
        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Room not found.']); return;
        }

        $house = $bhModel->getById((int)$room['boarding_house_id']);
        if (!$house || (int)$house['owner_id'] !== (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']); return;
        }

        $newCapacity = (int)($_POST['capacity'] ?? 1);
        $capacityDiff = $newCapacity - (int)$room['capacity'];
        $newAvailableSlots = (int)$room['available_slots'] + $capacityDiff;
        if ($newAvailableSlots < 0) $newAvailableSlots = 0;
        if ($newAvailableSlots > $newCapacity) $newAvailableSlots = $newCapacity;

        $data = [
            'room_name' => trim($_POST['room_name'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'capacity' => $newCapacity,
            'available_slots' => $newAvailableSlots
        ];

        if (empty($data['room_name']) || $data['price'] <= 0 || $data['capacity'] < 1) {
            echo json_encode(['success' => false, 'message' => 'Please provide valid room details.']); return;
        }

        if ($bhModel->updateRoom($roomId, $data)) {
            echo json_encode(['success' => true, 'message' => 'Room perfectly updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update room.']);
        }
    }

    public function delete_room(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $roomId = (int)($_POST['room_id'] ?? 0);
        $bhModel = new BoardingHouse($this->db());
        $room = $bhModel->getRoomById($roomId);
        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Room not found.']); return;
        }

        $house = $bhModel->getById((int)$room['boarding_house_id']);
        if (!$house || (int)$house['owner_id'] !== (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']); return;
        }

        // Ensure room is completely empty before deleting? (Assuming simple delete here based on strict DB rules)
        if ($bhModel->deleteRoom($roomId)) {
            echo json_encode(['success' => true, 'message' => 'Room has been securely removed.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete room. Could be tied to active tenants.']);
        }
    }

    public function subscription_simulation(): void
    {
        $planName = $_GET['plan'] ?? 'Pro';
        $status = $_GET['status'] ?? 'simulated';

        $this->render('owner/subscription_simulation', [
            'planName' => $planName,
            'status' => $status,
            'pageTitle' => 'Subscription Verification'
        ]);
    }
    public function get_available_rooms_json(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        $houseId = (int)($_GET['house_id'] ?? 0);
        $ownerId = (int)$_SESSION['user_id'];

        $bhModel = new BoardingHouse($this->db());
        $house = $bhModel->getById($houseId);

        if (!$house || (int)$house['owner_id'] !== $ownerId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or property not found.']);
            return;
        }

        $rooms = $bhModel->getRoomsByBoardingHouseId($houseId);
        // Filter for rooms with available slots
        $availableRooms = array_filter($rooms, fn($r) => (int)$r['available_slots'] > 0);
        // Re-index array
        $availableRooms = array_values($availableRooms);

        echo json_encode(['success' => true, 'data' => $availableRooms]);
    }

    public function store_tenant(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $ownerId   = (int)$_SESSION['user_id'];
        $firstName = Security::sanitize($_POST['first_name'] ?? '');
        $middleName= Security::sanitize($_POST['middle_name'] ?? null);
        $lastName  = Security::sanitize($_POST['last_name'] ?? '');
        $email     = Security::sanitize($_POST['email'] ?? '');
        $phone     = Security::sanitize($_POST['phone'] ?? '');
        $username  = Security::sanitize($_POST['username'] ?? null) ?: null;
        $password  = $_POST['password'] ?? null;
        $roomId    = (int)($_POST['room_id'] ?? 0);
        $startDate = Security::sanitize($_POST['start_date'] ?? date('Y-m-d'));
        $endDate   = Security::sanitize($_POST['end_date'] ?? null) ?: null;

        if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || $roomId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please complete all required fields.']); return;
        }
        if ($username !== null && $password !== null && strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']); return;
        }

        $bhModel = new BoardingHouse($this->db());
        $room = $bhModel->getRoomById($roomId);
        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Selected room not found.']); return;
        }

        $house = $bhModel->getById((int)$room['boarding_house_id']);
        if (!$house || (int)$house['owner_id'] !== $ownerId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized property selection.']); return;
        }

        if ((int)$room['available_slots'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'This room is already at full capacity.']); return;
        }

        $userModel = new User($this->db());
        $bookingModel = new Booking($this->db());

        try {
            // Link by email or create new
            $user = $userModel->findByEmail($email);
            $tenantId = 0;

            if ($user) {
                $tenantId = (int)$user['id'];
                // Update credentials if provided
                if ($username !== null && $password !== null) {
                    $userModel->updateRegistration($tenantId, $username, $password);
                }
            } else {
                // Validate username uniqueness
                if ($username !== null && $userModel->isUsernameTaken($username)) {
                    echo json_encode(['success' => false, 'message' => 'This username is already taken.']); return;
                }
                $finalPassword = ($password !== null && $password !== '') ? $password : bin2hex(random_bytes(4));
                $tenantId = $userModel->create($firstName, $middleName, $lastName, $email, $phone, 3, $username, $finalPassword);
            }

            if (!$tenantId) {
                echo json_encode(['success' => false, 'message' => 'Failed to provision tenant account.']); return;
            }

            $bookingId = $bookingModel->createApproved($tenantId, $roomId, $startDate, $endDate, (float)$room['price']);
            if (!$bookingId) {
                echo json_encode(['success' => false, 'message' => 'Failed to establish residency record.']); return;
            }

            $logger = new ActivityLog($this->db());
            $logger->log($ownerId, 'OWNER_TENANT_ADDED', [
                'tenant_id'  => $tenantId,
                'booking_id' => $bookingId,
                'room_name'  => $room['room_name'],
                'property'   => $house['name']
            ], $bookingId);

            echo json_encode(['success' => true, 'message' => 'Tenant successfully registered and room assigned.']);

        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Process failed: ' . $e->getMessage()]);
        }
    }

    public function get_tenant_json(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        $bookingId = (int)($_GET['booking_id'] ?? 0);
        $ownerId   = (int)$_SESSION['user_id'];

        $stmt = $this->db()->prepare('
            SELECT
                b.id AS booking_id,
                b.user_id AS tenant_id,
                b.room_id,
                b.start_date,
                b.end_date,
                b.total_amount,
                r.boarding_house_id,
                r.room_name,
                r.price,
                bh.name AS boarding_house_name,
                bh.owner_id,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.email,
                u.phone,
                u.username
            FROM bookings b
            JOIN rooms r ON r.id = b.room_id
            JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            JOIN users u ON u.id = b.user_id
            WHERE b.id = ? AND bh.owner_id = ?
            LIMIT 1
        ');
        $stmt->bind_param('ii', $bookingId, $ownerId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Booking not found or not authorised.']); return;
        }

        echo json_encode(['success' => true, 'data' => $row]);
    }

    public function update_tenant(): void
    {
        $this->requireRole(['owner', 'admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']); return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']); return;
        }

        $ownerId   = (int)$_SESSION['user_id'];
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $firstName = Security::sanitize($_POST['first_name'] ?? '');
        $middleName= Security::sanitize($_POST['middle_name'] ?? null);
        $lastName  = Security::sanitize($_POST['last_name'] ?? '');
        $email     = Security::sanitize($_POST['email'] ?? '');
        $phone     = Security::sanitize($_POST['phone'] ?? '');
        $username  = Security::sanitize($_POST['username'] ?? '') ?: null;
        $password  = $_POST['password'] ?? null;
        $startDate = Security::sanitize($_POST['start_date'] ?? '');
        $endDate   = Security::sanitize($_POST['end_date'] ?? null) ?: null;

        if (!$bookingId || empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Please complete all required fields.']); return;
        }
        if ($password !== null && $password !== '' && strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']); return;
        }

        // Verify ownership
        $stmtCheck = $this->db()->prepare('
            SELECT b.user_id, b.room_id FROM bookings b
            JOIN rooms r ON r.id = b.room_id
            JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            WHERE b.id = ? AND bh.owner_id = ? LIMIT 1
        ');
        $stmtCheck->bind_param('ii', $bookingId, $ownerId);
        $stmtCheck->execute();
        $booking = $stmtCheck->get_result()->fetch_assoc();

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found or not authorised.']); return;
        }

        $tenantId  = (int)$booking['user_id'];
        $userModel = new User($this->db());

        // Username uniqueness check (exclude current user)
        if ($username !== null && $userModel->isUsernameTaken($username, $tenantId)) {
            echo json_encode(['success' => false, 'message' => 'This username is already taken by another user.']); return;
        }

        // Update user profile
        $userModel->update($tenantId, $firstName, $middleName, $lastName, $username ?? '', $email, $phone, 3,
            ($password !== null && $password !== '') ? $password : null
        );

        // Update booking dates
        $stmtBooking = $this->db()->prepare('UPDATE bookings SET start_date = ?, end_date = ? WHERE id = ?');
        $stmtBooking->bind_param('ssi', $startDate, $endDate, $bookingId);
        $stmtBooking->execute();

        $logger = new ActivityLog($this->db());
        $logger->log($ownerId, 'OWNER_TENANT_UPDATED', [
            'tenant_id'  => $tenantId,
            'booking_id' => $bookingId
        ], $bookingId);

        echo json_encode(['success' => true, 'message' => 'Resident record updated successfully.']);
    }
}

