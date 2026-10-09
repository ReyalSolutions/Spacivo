<?php
declare(strict_types=1);

final class PaymentController extends BaseController
{
    public function initiate(): void
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tenant/?url=tenant/dashboard');
            return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Invalid CSRF token';
            return;
        }

        $db = $this->db();
        $userId = (int)$_SESSION['user_id'];
        $paymentModel = new Payment($db);

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $paymentType = $_POST['payment_type'] ?? 'rent';
        $description = $_POST['description'] ?? '';
        $numMonths = max(1, (int)($_POST['num_months'] ?? 1));

        if ($bookingId <= 0) {
            http_response_code(400);
            echo 'Invalid booking id';
            return;
        }

        // Fetch booking details
        $booking = $db->prepare('SELECT r.price, r.room_name, bh.name FROM bookings b JOIN rooms r ON r.id = b.room_id JOIN boarding_houses bh ON bh.id = r.boarding_house_id WHERE b.id = ? AND b.user_id = ? LIMIT 1');
        $booking->bind_param('ii', $bookingId, $userId);
        $booking->execute();
        $bRow = $booking->get_result()->fetch_assoc();
        
        if (!$bRow) {
            http_response_code(404);
            echo 'Booking not found.';
            return;
        }

        $pricePerMonth = (float)$bRow['price'];
        $totalAmount = $pricePerMonth * $numMonths;

        // Check for existing pending payment of the SAME type AND months
        $existing = $db->prepare('SELECT id FROM payments WHERE booking_id = ? AND user_id = ? AND status = "pending" AND payment_type = ? AND months_covered = ? LIMIT 1');
        $existing->bind_param('iisi', $bookingId, $userId, $paymentType, $numMonths);
        $existing->execute();
        $res = $existing->get_result()->fetch_assoc();
        
        if ($res) {
            $paymentId = (int)$res['id'];
        } else {
            if (empty($description)) {
                $monthName = date('F Y');
                $monthText = $numMonths > 1 ? "{$numMonths} Months" : "1 Month";
                $description = ($paymentType === 'advance' ? 'Advance Payment' : 'Monthly Rent') . " ({$monthText}) for {$bRow['room_name']} ({$bRow['name']})";
            }

            $paymentId = $paymentModel->createPending($userId, $bookingId, $totalAmount, 'E-Wallet', $paymentType, $description, $numMonths);
        }

        header('Content-Type: text/html');
        echo '<form id="postForm" action="/tenant/?url=payment/checkout" method="POST">';
        echo '<input type="hidden" name="payment_id" value="' . $paymentId . '">';
        echo '</form>';
        echo '<script>document.getElementById("postForm").submit();</script>';
        exit;
    }

    public function checkout(): void
    {
        $this->requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $db = $this->db();
        $paymentModel = new Payment($db);

        $paymentIdRaw = $_POST['payment_id'] ?? $_GET['payment_id'] ?? null;
        $paymentId = is_numeric($paymentIdRaw) ? (int)$paymentIdRaw : 0;

        if ($paymentId <= 0) {
            header('Location: /tenant/?url=tenant/dashboard');
            return;
        }

        $payment = $paymentModel->getPendingById($paymentId);
        if (!$payment || (int)$payment['user_id'] !== $userId) {
            http_response_code(404);
            echo 'Payment not found or already processed.';
            return;
        }

        $stmt = $db->prepare('
            SELECT
                b.id AS booking_id,
                b.start_date,
                b.end_date,
                b.total_amount,
                r.id AS room_id,
                r.room_name,
                bh.name AS boarding_house_name
            FROM payments p
            INNER JOIN bookings b ON b.id = p.booking_id
            INNER JOIN rooms r ON r.id = b.room_id
            INNER JOIN boarding_houses bh ON bh.id = r.boarding_house_id
            WHERE p.id = ? AND p.user_id = ?
            LIMIT 1
        ');
        $stmt->bind_param('ii', $paymentId, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            http_response_code(500);
            echo 'Unable to load booking details.';
            return;
        }

        require_once __DIR__ . '/../models/SystemSetting.php';
        $settingModel = new SystemSetting($db);
        $paymentSettings = $settingModel->getAll();

        $this->render('payment/checkout', [
            'payment' => $payment,
            'details' => $row,
            'paymentSettings' => $paymentSettings
        ]);
    }

    public function simulate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::verify($csrf)) {
            $this->json(['ok' => false, 'message' => 'Invalid CSRF token'], 403);
        }

        $this->requireRole(['tenant']);

        $paymentIdRaw = $_POST['payment_id'] ?? null;
        $paymentId = is_numeric($paymentIdRaw) ? (int)$paymentIdRaw : 0;
        if ($paymentId <= 0) {
            $this->json(['ok' => false, 'message' => 'Invalid payment id'], 400);
        }

        $paymentModel = new Payment($this->db());
        $bookingModel = new Booking($this->db());

        $payment = $paymentModel->getPendingById($paymentId);
        if (!$payment || (int)$payment['user_id'] !== (int)$_SESSION['user_id']) {
            $this->json(['ok' => false, 'message' => 'Payment not found'], 404);
        }

        $transactionRef = (string)($payment['transaction_ref'] ?? '');

        $ok = $paymentModel->markPaid($paymentId, $transactionRef);
        if (!$ok) {
            $this->json(['ok' => false, 'message' => 'Unable to mark payment as paid'], 500);
        }

        // MVP behavior: booking remains `pending` until the owner approves/rejects.
        // (This aligns with the feature requirement for owner reservation management.)
        $this->json([
            'ok' => true,
            'message' => 'Payment received. Waiting for owner approval.'
        ]);
    }

    public function initiate_paymongo(): void
    {
        $this->requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $db = $this->db();
        
        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $method = $_POST['payment_method'] ?? 'card';

        if ($paymentId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid payment ID'], 400);
        }

        require_once __DIR__ . '/../models/Payment.php';
        require_once __DIR__ . '/../models/SystemSetting.php';

        $paymentModel = new Payment($db);
        $payment = $paymentModel->getPendingById($paymentId);

        if (!$payment || (int)$payment['user_id'] !== $userId) {
            $this->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        $settingModel = new SystemSetting($db);
        $secretKey = $settingModel->get('paymongo_sec', '');
        
        if (empty($secretKey)) {
            $this->json(['success' => false, 'message' => 'Payment gateway not configured'], 500);
            return;
        }

        // Map method to PayMongo types
        $methodTypes = [$method];
        if ($method === 'card') $methodTypes = ['card'];
        elseif ($method === 'paymaya') $methodTypes = ['paymaya'];
        elseif ($method === 'gcash') $methodTypes = ['gcash'];
        elseif ($method === 'grab_pay') $methodTypes = ['grab_pay'];

        $taxEnabled = ($settingModel->get('payment_tax_enabled', '0') === '1');
        $taxPercent = (float)$settingModel->get('payment_tax_percent', 0);
        $subtotal = (float)$payment['amount'];
        $taxAmount = $taxEnabled ? ($subtotal * ($taxPercent / 100)) : 0;
        $grandTotal = $subtotal + $taxAmount;

        $newDescription = (!empty($payment['description']) ? $payment['description'] : 'Rent Payment #' . $paymentId) . ($taxEnabled ? " (Incl. $taxPercent% Service Fee)" : "");

        // Sync local record amount and description with what we push to PayMongo
        $paymentModel->updateAmountAndDescription($paymentId, $grandTotal, $newDescription);

        $params = [
            'amount' => (int)(round($grandTotal * 100)),
            'description' => $newDescription,
            'plan_name' => !empty($payment['description']) ? $payment['description'] : 'StayHub Rent Payment',
            'method_types' => $methodTypes,
            'payment_id' => $paymentId
        ];
        
        $result = $this->createPayMongoCheckoutSession($secretKey, $params);
        $redirectUrl = $result['checkout_url'] ?? null;
        $sessionId = $result['session_id'] ?? null;

        if ($redirectUrl) {
            if ($sessionId) {
                // Store the actual session ID in transaction_ref for better tracking
                $stmt = $db->prepare("UPDATE payments SET transaction_ref = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('si', $sessionId, $paymentId);
                    $stmt->execute();
                }
            }
            $this->json(['success' => true, 'redirect_url' => $redirectUrl]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to initialize PayMongo session'], 500);
        }
    }

    private function createPayMongoCheckoutSession(string $secretKey, array $params): ?array
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . $domainName . '/tenant/';

        $payload = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'show_line_items' => true,
                    'description' => $params['description'],
                    'success_url' => $baseUrl . '?url=payment/pay_success&payment_id=' . $params['payment_id'] . '&session={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $baseUrl . '?url=payment/pay_cancel',
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
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode((string)$response, true);
            return [
                'checkout_url' => $data['data']['attributes']['checkout_url'] ?? null,
                'session_id' => $data['data']['id'] ?? null
            ];
        }

        // Diagnostic information
        error_log("PayMongo Error: HTTP $httpCode | Response: $response | Curl Error: $curlError");

        return null;
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
    public function pay_success(): void
    {
        $this->requireLogin();
        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $sessionId = $_GET['session'] ?? null;
        
        if ($paymentId > 0 && $sessionId) {
            require_once __DIR__ . '/../models/SystemSetting.php';
            require_once __DIR__ . '/../models/Payment.php';
            
            $settingModel = new SystemSetting($this->db());
            $secretKey = trim($settingModel->get('paymongo_sec', '') ?? '');
            
            $paymentModel = new Payment($this->db());
            
            $methodPrefix = 'PAYMONGO';
            if (!empty($secretKey)) {
                $session = $this->getPayMongoSession($secretKey, $sessionId);
                if ($session && !empty($session['attributes']['payments'])) {
                    $firstPayment = $session['attributes']['payments'][0];
                    $methodPrefix = $firstPayment['attributes']['source']['type'] ?? 'PAYMONGO';
                }
            }

            $transactionRef = $paymentModel->generateTransactionRef($methodPrefix);
            $paymentModel->markPaid($paymentId, $transactionRef);
            
            $_SESSION['success'] = "Payment successful! Your rent has been recorded. (Ref: $transactionRef)";
            header('Location: /tenant/?url=tenant/payments');
            exit;
        }
        
        header('Location: /tenant/?url=tenant/dashboard');
    }

    public function pay_cancel(): void
    {
        $this->requireLogin();
        $_SESSION['error'] = "Payment was cancelled.";
        header('Location: /tenant/?url=tenant/payments');
        exit;
    }
}

