<?php
declare(strict_types=1);

final class AuthController extends BaseController
{
    public function login(): void
    {
        if (!empty($_SESSION['user_id']) && !App\Modules\Identity\Services\SessionGuard::valid($this->db(), (int)$_SESSION['user_id'])) {
            $_SESSION = [];
        }
        // Redirect already authenticated users to their corresponding dashboard
        if (!empty($_SESSION['user_id'])) {
            $this->redirectAfterLogin();
            return;
        }

        $error = null;
        if (isset($_GET['deactivated'])) {
            $error = 'Your account has been deactivated. Please contact an administrator.';
        }
        if (isset($_GET['session_expired'])) {
            $error = 'Your session has expired. Please sign in again.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf = $_POST['csrf_token'] ?? null;
            if (!Csrf::verify($csrf)) {
                $error = 'Your session has expired. Please refresh the page and try again.';
            } else {
                $username = trim(Security::sanitize($_POST['username'] ?? ''));
                $password = App\Modules\Identity\Services\RegistrationPolicy::passwordFromInput($_POST['password'] ?? '');

                if ($username === '' || $password === '') {
                    $error = 'Please enter your username or email, and password.';
                } else {
                    $authentication = new App\Modules\Identity\Services\AuthenticationService(
                        new App\Modules\Identity\Repositories\AuthenticationRepository($this->db())
                    );
                    $user = $authentication->authenticate($username, $password);
                    if (!$user) {
                        $error = 'The username/email or password you entered is incorrect.';
                    } elseif (isset($user['status']) && (int)$user['status'] !== 1) {
                        $error = 'Your account has been deactivated. Please contact an administrator.';
                    } else {
                        session_regenerate_id(true);
                        $roleId = (int)$user['role_id'];
                        
                        $roleSlug = strtolower($user['role_slug'] ?? '');
                        if (!$roleSlug) {
                            $roleMap = [1 => 'admin', 2 => 'owner', 3 => 'tenant'];
                            $roleSlug = $roleMap[$roleId] ?? 'tenant';
                        }

                        $_SESSION['user_id'] = (int)$user['id'];
                        $_SESSION['role_id'] = $roleId;
                        $_SESSION['role'] = $roleSlug;
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['name'] = trim($user['first_name'] . ' ' . ($user['last_name'] ?? ''));
                        $_SESSION['email'] = $user['email'] ?? '';
                        $_SESSION['username'] = $user['username'] ?? '';
                        App\Modules\Identity\Services\SessionGuard::recordVersion($this->db(), (int)$user['id']);

                        $roleLabels = [
                            1 => 'System Administrator',
                            2 => 'Property Owner',
                            3 => 'Tenant'
                        ];
                        $roleLabel = $roleLabels[$roleId] ?? (!empty($user['role_name']) ? $user['role_name'] : ucfirst($roleSlug));

                        $welcomeMessage = "Welcome back, {$user['first_name']}! Redirecting to your dashboard...";
                        if ($roleId === 1) {
                            $welcomeMessage = 'Welcome back, Administrator! Redirecting to Admin Panel...';
                        } elseif ($roleId === 2) {
                            $welcomeMessage = 'Welcome back, Property Owner! Redirecting to Owner Portal...';
                        } elseif ($roleId === 3) {
                            $welcomeMessage = 'Welcome back! Redirecting to your Tenant Dashboard...';
                        }

                        $redirectUrl = $this->getDashboardUrl($roleId, $roleSlug);

                        if ($this->isAjax()) {
                            header('Content-Type: application/json');
                            echo json_encode([
                                'success' => true,
                                'role' => $roleSlug,
                                'role_id' => $roleId,
                                'role_label' => $roleLabel,
                                'first_name' => $user['first_name'],
                                'message' => $welcomeMessage,
                                'redirect' => $redirectUrl
                            ]);
                            return;
                        }

                        header('Location: ' . $redirectUrl);
                        exit;
                    }
                }
            }

            if ($this->isAjax() && $error) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                return;
            }
        }

        $this->render('auth/login', ['error' => $error]);
    }

    public function register(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf = $_POST['csrf_token'] ?? null;
            if (!Csrf::verify($csrf)) {
                $error = 'Invalid CSRF token.';
            } else {
                $firstName = Security::sanitize($_POST['first_name'] ?? '');
                $middleName = Security::sanitize($_POST['middle_name'] ?? '');
                $lastName = Security::sanitize($_POST['last_name'] ?? '');
                $email = Security::sanitize($_POST['email'] ?? '');
                $phone = Security::sanitize($_POST['phone'] ?? '');
                $roleId = (int)($_POST['role_id'] ?? 3);

                // Backend Validation
                $role = (new Role($this->db()))->findById($roleId);
                if (!App\Modules\Identity\Services\RegistrationPolicy::publicRoleAllowed($role)) {
                    $error = 'Please choose an owner or tenant account.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please provide a valid email address.';
                } elseif ($firstName === '' || $lastName === '' || $email === '' || $phone === '') {
                    $error = 'Please fill out all required fields marked with (*).';
                } else {
                    $userModel = new User($this->db());

                    // Precise Uniqueness Checks
                    if ($userModel->isEmailTaken($email)) {
                        $error = 'This email is already registered. Please use a different one or log in.';
                    } elseif ($userModel->findByPhoneAndRole($phone, $roleId)) {
                        $error = 'This phone number is already registered for this role.';
                    } else {
                        try {
                            $id = $userModel->create($firstName, $middleName, $lastName, $email, $phone, $roleId);
                            
                            if (!$id) {
                                throw new Exception('System failed to create user record.');
                            }

                            $_SESSION['reg_step'] = 2;
                            $_SESSION['reg_user_id'] = $id;
                            $_SESSION['reg_role_id'] = $roleId;

                            if ($this->isAjax()) {
                                header('Content-Type: application/json');
                                echo json_encode(['success' => true, 'redirect' => '/tenant/?url=auth/register_step2']);
                                return;
                            }

                            header('Location: /tenant/?url=auth/register_step2'); 
                            return;
                        } catch (Throwable $e) {
                            // Detailed error for better UX feedback during debugging
                            $msg = $e->getMessage();
                            if (strpos($msg, 'Duplicate entry') !== false) {
                                if (strpos($msg, 'email') !== false) {
                                    $error = 'The email address is already taken.';
                                } else if (strpos($msg, 'phone') !== false) {
                                    $error = 'The phone number is already taken.';
                                } else {
                                    $error = 'Duplicate information provided. Please check your details.';
                                }
                            } else {
                                $error = 'Registration could not be completed. Please try again.';
                            }
                        }
                    }
                }
            }

            if ($this->isAjax() && $error) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                return;
            }
        }

        $this->render('auth/register', ['error' => $error]);
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo 'Method Not Allowed';
            return;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo 'Invalid CSRF token';
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();

        header('Location: /tenant/?url=home/index');
        exit;
    }

    public function portal(): void
    {
        // Single unified login page: redirect to auth/login
        header('Location: /tenant/?url=auth/login');
        exit;
    }

    private function recoveryService(): App\Modules\Identity\Services\PasswordResetService
    {
        $outbox = getenv('PASSWORD_RESET_OUTBOX') ?: __DIR__ . '/../../storage/private/password-reset-outbox';
        return new App\Modules\Identity\Services\PasswordResetService(
            new App\Modules\Identity\Repositories\PasswordResetRepository($this->db()),
            new App\Integrations\Email\LocalPasswordResetDelivery($outbox),
            getenv('APP_URL') ?: 'http://localhost/tenant'
        );
    }

    public function forgot_password(): void
    {
        if (getenv('PASSWORD_RECOVERY_ENABLED') !== 'true' || getenv('APP_ENV') !== 'local' && getenv('APP_ENV') !== 'testing') {
            http_response_code(503);
            echo 'Account recovery is temporarily unavailable.';
            return;
        }
        $message = null;
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                $error = 'Your session has expired. Please refresh the page.';
            } elseif (!is_string($_POST['email'] ?? null) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(422);
                $error = 'Please enter a valid email address.';
            } else {
                $this->recoveryService()->request($_POST['email']);
                $message = 'If an eligible account exists, recovery instructions have been queued.';
            }
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }
        $this->render('auth/recovery', ['mode' => 'request', 'message' => $message, 'error' => $error]);
    }

    public function reset_password(): void
    {
        if (getenv('PASSWORD_RECOVERY_ENABLED') !== 'true' || getenv('APP_ENV') !== 'local' && getenv('APP_ENV') !== 'testing') {
            http_response_code(503);
            echo 'Account recovery is temporarily unavailable.';
            return;
        }
        header('Referrer-Policy: no-referrer');
        header('Cache-Control: no-store');
        $token = $_POST['token'] ?? $_GET['token'] ?? '';
        $token = is_string($token) ? $token : '';
        $message = null;
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = App\Modules\Identity\Services\RegistrationPolicy::passwordFromInput($_POST['password'] ?? '');
            $confirmation = App\Modules\Identity\Services\RegistrationPolicy::passwordFromInput($_POST['confirm_password'] ?? '');
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                $error = 'Your session has expired. Please refresh the page.';
            } elseif ($password !== $confirmation || !$this->recoveryService()->reset($token, $password)) {
                http_response_code(422);
                $error = 'The link is invalid or expired, or the passwords do not match the requirements.';
            } else {
                $message = 'Your password has been changed. Please sign in again.';
            }
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }
        $this->render('auth/recovery', ['mode' => 'reset', 'token' => $token, 'message' => $message, 'error' => $error]);
    }

    public function portal_login(): void
    {
        // Forward directly to unified login
        $this->login();
    }

    public function register_step2(): void
    {
        // Must have started registration
        $userId = $_SESSION['reg_user_id'] ?? null;
        if (!$userId) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session expired.', 'redirect' => '/tenant/?url=auth/register']);
                return;
            }
            header('Location: /tenant/?url=auth/register');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf = $_POST['csrf_token'] ?? null;
            if (!Csrf::verify($csrf)) {
                $error = 'Invalid CSRF token.';
            } else {
                $username = Security::sanitize($_POST['username'] ?? '');
                $password = App\Modules\Identity\Services\RegistrationPolicy::passwordFromInput($_POST['password'] ?? '');
                $confirm = App\Modules\Identity\Services\RegistrationPolicy::passwordFromInput($_POST['confirm_password'] ?? '');

                if ($username === '' || !App\Modules\Identity\Services\RegistrationPolicy::validNewPassword($password)) {
                    $error = 'Please provide a username and a password of 8 to 72 bytes.';
                } elseif ($password !== $confirm) {
                    $error = 'Passwords do not match.';
                } else {
                    $userModel = new User($this->db());
                    
                    // Check if username taken
                    if ($userModel->findByUsername($username)) {
                        $error = 'Username is already taken.';
                    } elseif ($userModel->updateRegistration($userId, $username, $password)) {
                        $roleId = $_SESSION['reg_role_id'] ?? null;
                        
                        // Cleanup registration session
                        unset($_SESSION['reg_user_id'], $_SESSION['reg_step'], $_SESSION['reg_role_id']);
                        
                        if ($roleId == 2) {
                            $_SESSION['pending_plan_user_id'] = $userId;
                            if ($this->isAjax()) {
                                header('Content-Type: application/json');
                                echo json_encode(['success' => true, 'redirect' => '/tenant/?url=auth/select_plan']);
                                return;
                            }
                            header('Location: /tenant/?url=auth/select_plan');
                            return;
                        }

                        if ($this->isAjax()) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'redirect' => '/tenant/?url=auth/login']);
                            return;
                        }

                        header('Location: /tenant/?url=auth/login');
                        return;
                    } else {
                        $error = 'Failed to save account details. Please try again.';
                    }
                }
            }

            if ($this->isAjax() && $error) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                return;
            }
        }

        $this->render('auth/register_step2', ['error' => $error]);
    }

    public function select_plan(): void
    {
        if (empty($_SESSION['pending_plan_user_id'])) {
            header('Location: /tenant/?url=auth/login');
            exit;
        }

        $res = $this->db()->query("SELECT * FROM plans WHERE is_deleted = 0 ORDER BY price_monthly ASC");
        $plans = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        require_once __DIR__ . '/../models/SystemSetting.php';
        $settingModel = new SystemSetting($this->db());
        $paymentSettings = $settingModel->getAll();

        $this->render('auth/select_plan', [
            'plans' => $plans,
            'paymentSettings' => $paymentSettings
        ]);
    }

    public function create_plan_payment(): void
    {
        header('Content-Type: application/json');
        try {
            $userId = $_SESSION['pending_plan_user_id'] ?? null;
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Session expired. Please log in and subscribe from the dashboard.']);
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
                exit;
            }

            $csrf = $_POST['csrf_token'] ?? null;
            if (!Csrf::verify($csrf)) {
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                exit;
            }

            $planId = (int)($_POST['plan_id'] ?? $_POST['new_plan_id'] ?? 0);
            $billingCycle = $_POST['billing_cycle'] ?? 'monthly';
            if (!in_array($billingCycle, ['monthly', 'yearly'])) {
                $billingCycle = 'monthly';
            }

            if ($planId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid plan selected.']);
                exit;
            }

            require_once __DIR__ . '/../models/Plan.php';
            $planModel = new Plan($this->db());
            $plan = $planModel->getById($planId);
            
            if (!$plan) {
                echo json_encode(['success' => false, 'message' => 'Plan not found.']);
                exit;
            }

            $amount = ($billingCycle === 'yearly') ? (float)$plan['price_yearly'] : (float)$plan['price_monthly'];
            
            require_once __DIR__ . '/../models/SystemSetting.php';
            $settingModel = new SystemSetting($this->db());
            $taxEnabled = ($settingModel->get('payment_tax_enabled', '0') === '1');
            $taxPercent = (float)$settingModel->get('payment_tax_percent', 0);
            $taxAmount = $taxEnabled ? ($amount * ($taxPercent / 100)) : 0;
            $grandTotal = $amount + $taxAmount;

            $amountInCentavos = (int)(round($grandTotal * 100));

            // Create a pending subscription for this user
            // We use CURRENT_DATE instead of NULL as start_date to prevent possible strict mode null value error
            $subInsert = $this->db()->prepare("INSERT INTO subscriptions (owner_id, plan_id, billing_cycle, start_date, status) VALUES (?, ?, ?, CURRENT_DATE, 'pending')");
            if (!$subInsert) {
                echo json_encode(['success' => false, 'message' => 'DB Error on subscriptions: ' . $this->db()->error]);
                exit;
            }
            $subInsert->bind_param('iis', $userId, $planId, $billingCycle);
            if (!$subInsert->execute()) {
                echo json_encode(['success' => false, 'message' => 'Insert error on subscriptions: ' . $subInsert->error]);
                exit;
            }
            $subId = (int)$this->db()->insert_id;

            $gateway = 'paymongo';
            $method = 'card';
            $ppStatus = 'pending';

            $ppInsert = $this->db()->prepare(
                "INSERT INTO plan_payments
                 (owner_id, subscription_id, plan_id, billing_cycle, amount, gateway, payment_method, status, session_id, transaction_ref, paid_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL)"
            );
            if (!$ppInsert) {
                echo json_encode(['success' => false, 'message' => 'DB Error on plan_payments: ' . $this->db()->error]);
                exit;
            }
            $ppInsert->bind_param('iiisdsss', $userId, $subId, $planId, $billingCycle, $grandTotal, $gateway, $method, $ppStatus);
            if (!$ppInsert->execute()) {
                echo json_encode(['success' => false, 'message' => 'Insert error on plan_payments: ' . $ppInsert->error]);
                exit;
            }
            $planPaymentId = (int)$this->db()->insert_id;

            $secretKey = trim($settingModel->get('paymongo_sec', '') ?? '');
            if (!empty($secretKey) && strpos($secretKey, 'sk_') === 0) {
                $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $domainName = $_SERVER['HTTP_HOST'];
                $baseUrl    = $protocol . $domainName . '/tenant/';
                
                $payload = [
                    'data' => [
                        'attributes' => [
                            'send_email_receipt' => true,
                            'show_description' => true,
                            'show_line_items' => true,
                            'description' => "StayHub " . $plan['name'] . " Subscription (" . ucfirst($billingCycle) . ")" . ($taxEnabled ? " (Incl. $taxPercent% Service Fee)" : ""),
                            'success_url' => $baseUrl . '?url=auth/plan_payment_success&payment_id=' . $planPaymentId . '&session={CHECKOUT_SESSION_ID}',
                            'cancel_url' => $baseUrl . '?url=auth/plan_payment_cancel',
                            'line_items' => [
                                [
                                    'currency' => 'PHP',
                                    'amount' => $amountInCentavos,
                                    'description' => "StayHub " . $plan['name'] . " Subscription",
                                    'name' => $plan['name'],
                                    'quantity' => 1
                                ]
                            ],
                            'payment_method_types' => [$_POST['payment_method'] ?? 'card']
                        ]
                    ]
                ];

                $ch = curl_init('https://api.paymongo.com/v1/checkout_sessions');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ':');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $data = json_decode((string)$response, true);
                    $redirectUrl = $data['data']['attributes']['checkout_url'] ?? null;
                    $sessionId = $data['data']['id'] ?? null;
                    
                    if ($sessionId) {
                        $upd = $this->db()->prepare("UPDATE plan_payments SET session_id = ? WHERE id = ?");
                        $upd->bind_param('si', $sessionId, $planPaymentId);
                        $upd->execute();
                    }

                    echo json_encode(['success' => true, 'redirect_url' => $redirectUrl]);
                    exit;
                }
            }
            
            echo json_encode(['success' => false, 'message' => 'Failed to initialize PayMongo session. Ensure your API keys are correct.']);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Server Configuration Error: ' . $e->getMessage()]);
            exit;
        }
    }

    public function plan_payment_success(): void
    {
        $userId = $_SESSION['pending_plan_user_id'] ?? null;
        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $sessionId = $_GET['session'] ?? null;

        if ($userId && $paymentId > 0 && $sessionId) {
            require_once __DIR__ . '/../models/SystemSetting.php';
            $settingModel = new SystemSetting($this->db());
            $secretKey = trim($settingModel->get('paymongo_sec', '') ?? '');
            
            $methodPrefix = 'PAYMONGO';
            if (!empty($secretKey)) {
                $ch = curl_init('https://api.paymongo.com/v1/checkout_sessions/' . $sessionId);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ':');
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $session = json_decode((string)$response, true)['data'] ?? null;
                    if ($session && !empty($session['attributes']['payments'])) {
                        $firstPayment = $session['attributes']['payments'][0];
                        $methodPrefix = $firstPayment['attributes']['source']['type'] ?? 'PAYMONGO';
                    }
                }
            }

            // Generate transaction ref based on the method
            $types = ['card' => 'CRD', 'paymaya' => 'PAY', 'gcash' => 'GCS', 'grab_pay' => 'GRB'];
            $prefix = $types[$methodPrefix] ?? 'PMN';
            $transactionRef = $prefix . date('ymd') . strtoupper(substr(uniqid(), -5));
            
            // Mark payment as paid
            $upd = $this->db()->prepare("UPDATE plan_payments SET status = 'paid', transaction_ref = ?, paid_at = NOW() WHERE id = ? AND owner_id = ?");
            $upd->bind_param('sii', $transactionRef, $paymentId, $userId);
            $upd->execute();

            // Activate subscription
            $subReq = $this->db()->prepare("SELECT subscription_id FROM plan_payments WHERE id = ? LIMIT 1");
            $subReq->bind_param('i', $paymentId);
            $subReq->execute();
            $subRow = $subReq->get_result()->fetch_assoc();
            if ($subRow && $subRow['subscription_id']) {
                $actSub = $this->db()->prepare("UPDATE subscriptions SET status = 'active', start_date = CURRENT_DATE WHERE id = ?");
                $actSub->bind_param('i', $subRow['subscription_id']);
                $actSub->execute();
            }

            $_SESSION['flash_success'] = "You successfully subscribed to this plan! Please log in to your account.";
            unset($_SESSION['pending_plan_user_id']);
        }

        header('Location: /tenant/?url=auth/login');
        exit;
    }

    public function plan_payment_cancel(): void
    {
        $_SESSION['flash_error'] = "Subscription payment was cancelled via PayMongo checkout.";
        header('Location: /tenant/?url=auth/select_plan');
        exit;
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function getDashboardUrl(int $roleId, string $roleSlug = ''): string
    {
        $roleSlug = strtolower($roleSlug);
        if ($roleId === 1 || $roleSlug === 'admin' || $roleSlug === 'administrator') {
            return '/tenant/admin/index.php';
        } elseif ($roleId === 2 || $roleSlug === 'owner' || $roleSlug === 'property owner') {
            return '/tenant/?url=admin/index';
        } else {
            return '/tenant/?url=tenant/dashboard';
        }
    }

    private function redirectAfterLogin(): void
    {
        $roleId = (int)($_SESSION['role_id'] ?? 3);
        $roleSlug = (string)($_SESSION['role'] ?? '');
        header('Location: ' . $this->getDashboardUrl($roleId, $roleSlug));
        exit;
    }
}

