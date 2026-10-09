<?php
declare(strict_types=1);

abstract class BaseController
{
    public function __construct()
    {
        $db = $this->db();
        
        // Ensure SystemSetting class is loaded before using it
        require_once __DIR__ . '/../models/SystemSetting.php';
        
        $settingModel = new SystemSetting($db);
        $maintenance = $settingModel->get('maintenance_mode', '0');

        if ($maintenance === '1') {
            $role = $_SESSION['role'] ?? null;
            $url = $_GET['url'] ?? 'home/index';

            // Admins bypass maintenance mode
            // Allow auth routes so admins can still log in
            if ($role !== 'admin' && strpos($url, 'auth/') !== 0) {
                http_response_code(503); // Service Unavailable
                $this->render('home/maintenance');
                exit;
            }
        }
    }

    protected function db(): mysqli
    {
        return Database::get();
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            echo "View not found: " . htmlspecialchars($viewFile, ENT_QUOTES, 'UTF-8');
            return;
        }

        require $viewFile;
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    protected function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /tenant/?url=auth/login');
            exit;
        }

        // Ensure deactivated/inactive users cannot browse the system
        $stmt = $this->db()->prepare('SELECT u.status, u.role_id, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.is_deleted = 0 LIMIT 1');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();
        if (!$userRow || (int)$userRow['status'] !== 1
            || !App\Modules\Identity\Services\SessionGuard::valid($this->db(), (int)$_SESSION['user_id'])) {
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            $reason = $userRow && (int)$userRow['status'] === 1 ? 'session_expired' : 'deactivated';
            header('Location: /tenant/?url=auth/login&' . $reason . '=1');
            exit;
        }
        $_SESSION['role_id'] = (int)$userRow['role_id'];
        $_SESSION['role'] = strtolower($userRow['role_slug']);
    }

    /**
     * @param string[] $roles
     */
    protected function requireRole(array $roles): void
    {
        $this->requireLogin();

        $current = $_SESSION['role'] ?? null;
        if ($current === null || !in_array($current, $roles, true)) {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }
    }

    protected function redirect(string $path, string $type = '', string $message = ''): void
    {
        if ($type && $message) {
            $_SESSION[$type] = $message;
        }
        header('Location: /tenant/?url=' . $path);
        exit;
    }

    protected function requirePermission(string $slug): void
    {
        $this->requireLogin();
        
        if (!$this->hasPermission($slug)) {
            http_response_code(403);
            $this->render('errors/403', ['permission' => $slug]);
            exit;
        }
    }

    protected function hasPermission(string $slug): bool
    {
        if (!isset($_SESSION['role_id'])) return false;
        
        $db = $this->db();
        $stmt = $db->prepare("
            SELECT 1 FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.slug = ?
        ");
        $roleId = (int)$_SESSION['role_id'];
        $stmt->bind_param("is", $roleId, $slug);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0;
    }

    /**
     * Returns the owner's active subscription status.
     * Returns an array with: subscription row + 'is_expired' (bool) + 'expires_on' (date string)
     * Returns null if no subscription found.
     */
    protected function getOwnerSubscriptionStatus(int $ownerId): ?array
    {
        $stmt = $this->db()->prepare("
            SELECT s.*, p.name AS plan_name, p.price_monthly, p.price_yearly
            FROM subscriptions s
            JOIN plans p ON p.id = s.plan_id
            WHERE s.owner_id = ? AND s.status = 'active'
            ORDER BY s.created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param('i', $ownerId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return null;

        // Determine start date: use start_date if set, otherwise created_at
        $startRaw = (!empty($row['start_date']) && $row['start_date'] !== '0000-00-00')
            ? $row['start_date']
            : substr((string)$row['created_at'], 0, 10);

        $start = new DateTime($startRaw);
        $today = new DateTime('today');

        // advance expiry by each billing period until we exceed today
        $expires = clone $start;
        $interval = ($row['billing_cycle'] === 'yearly') ? new DateInterval('P1Y') : new DateInterval('P1M');
        while ($expires <= $today) {
            $expires->add($interval);
        }
        // $expires is now the next due date; go back one period to get current cycle end
        $cycleEnd = clone $expires;
        $cycleEnd->sub($interval);

        // Check if there is a paid plan_payment for this cycle
        $cycleEndStr  = $cycleEnd->format('Y-m-d 23:59:59');
        $cycleStartStr = $start->format('Y-m-d 00:00:00');
        // Recalculate the current cycle start
        $currentCycleStart = clone $expires;
        $currentCycleStart->sub($interval);
        $currentCycleStartStr = $currentCycleStart->format('Y-m-d');

        $paidStmt = $this->db()->prepare("
            SELECT id FROM plan_payments
            WHERE owner_id = ? AND subscription_id = ? AND status = 'paid'
              AND paid_at >= ?
            LIMIT 1
        ");
        $subId = (int)$row['id'];
        $paidStmt->bind_param('iis', $ownerId, $subId, $currentCycleStartStr);
        $paidStmt->execute();
        $hasPaid = (bool)$paidStmt->get_result()->fetch_assoc();

        $isExpired = ($today >= $cycleEnd) && !$hasPaid;

        return array_merge($row, [
            'expires_on'  => $cycleEnd->format('Y-m-d'),
            'is_expired'  => $isExpired,
            'has_paid'    => $hasPaid,
            'cycle_start' => $currentCycleStartStr,
        ]);
    }

    /**
     * Enforces subscription payment for owners.
     * Call this in any page controller to block overdue owners.
     * Pages in $exempt are allowed through (subscriptions page, logout, etc.)
     */
    public function checkSubscriptionPaywall(): void
    {
        try {
            $role   = strtolower($_SESSION['role'] ?? '');
            $userId = (int)($_SESSION['user_id'] ?? 0);

            if ($role !== 'owner' || $userId <= 0) return;

            $url    = strtolower($_GET['url'] ?? '');
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

            // Never block these pages
            $exempt = ['admin/subscriptions', 'auth/logout', 'organization/', 'property/browse', 'owner/subscription_simulation', 'admin/upgrade_plan'];
            foreach ($exempt as $ex) {
                if (strpos($url, $ex) === 0) return;
            }

            // Skip _json endpoints (DataTables AJAX) and all POST requests
            if (strpos($url, '_json') !== false || $method === 'POST') return;

            $subModel = new Subscription($this->db());
            $status  = $subModel->getOwnerSubscriptionStatus($userId);
            $blocked = ($status === null) || !empty($status['is_expired']);
            if (!$blocked) return;

            $reason = ($status === null) ? 'no_subscription' : 'overdue';

            // AJAX detection — PHP 7 compatible (no str_contains)
            $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
            $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
                   || (strpos($acceptHeader, 'application/json') !== false);

            if ($isAjax) {
                http_response_code(402);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'paywall' => true,
                    'reason'  => $reason,
                    'message' => 'Subscription payment required. Please renew your plan.',
                ]);
                exit;
            }

            // Full-page paywall for regular GET requests
            $plansRes = $this->db()->query("SELECT * FROM plans WHERE is_deleted = 0 ORDER BY price_monthly ASC");
            $availablePlans = $plansRes ? $plansRes->fetch_all(MYSQLI_ASSOC) : [];

            require_once __DIR__ . '/../models/SystemSetting.php';
            $psModel = new SystemSetting($this->db());
            $paymentSettings = $psModel->getAll();

            $this->render('admin/paywall', [
                'paywallReason'    => $reason,
                'subscriptionData' => $status,
                'availablePlans'   => $availablePlans,
                'paymentSettings'  => $paymentSettings
            ]);
            exit;

        } catch (\Throwable $e) {
            // Silently ignore any errors in the paywall check — never break the app
            return;
        }
    }
}


