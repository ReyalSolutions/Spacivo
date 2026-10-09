<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/core/Autoloader.php';
Autoloader::register();

// Redirect to login if unauthenticated
if (empty($_SESSION['user_id'])) {
    header('Location: /tenant/?url=auth/login');
    exit();
}

// Ensure deactivated/inactive users cannot access admin pages
$statusChk = $db->prepare('SELECT u.status, u.role_id, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.is_deleted = 0 LIMIT 1');
$userId = (int)$_SESSION['user_id'];
$statusChk->bind_param('i', $userId);
$statusChk->execute();
$userRow = $statusChk->get_result()->fetch_assoc();
if (!$userRow || (int)$userRow['status'] !== 1
    || !App\Modules\Identity\Services\SessionGuard::valid($db, (int)$_SESSION['user_id'])) {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    $reason = $userRow && (int)$userRow['status'] === 1 ? 'session_expired' : 'deactivated';
    header('Location: /tenant/?url=auth/login&' . $reason . '=1');
    exit();
}

// Redirect if not admin
$_SESSION['role_id'] = (int)$userRow['role_id'];
$_SESSION['role'] = strtolower($userRow['role_slug']);
$currentRole = strtolower($_SESSION['role'] ?? '');
if ($currentRole !== 'admin') {
    header('Location: /tenant/?url=home/index');
    exit();
}

$userName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['first_name'] ?? 'Administrator');
$userRole = htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin'));
$userEmail = htmlspecialchars($_SESSION['email'] ?? 'admin@stayhub.com');

if (!function_exists('hasPermission')) {
    function hasPermission(string $slug): bool {
        if (!isset($_SESSION['role_id'])) return false;
        $roleId = (int)$_SESSION['role_id'];
        if ($roleId === 1) return true; // Superadmin has all permissions
        global $db;
        if (!$db) return false;
        $stmt = $db->prepare("
            SELECT 1 FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ? AND p.slug = ?
        ");
        if (!$stmt) return false;
        $stmt->bind_param("is", $roleId, $slug);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0;
    }
}

if (!function_exists('isActiveLink')) {
    function isActiveLink(string $page): string {
        $current = basename($_SERVER['PHP_SELF'] ?? '');
        return ($current === $page) ? 'active' : '';
    }
}

if (!function_exists('getSetting')) {
    function getSetting(string $key, string $default = ''): string {
        global $db;
        if (!$db) return $default;
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        if (!$stmt) return $default;
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row['setting_value'] ?? $default;
        }
        return $default;
    }
}

$siteName = getSetting('site_name', 'StayHub');
$siteLogo = getSetting('company_logo', 'public/assets/images/favicon.png');
$siteFavicon = getSetting('site_favicon', 'public/assets/images/favicon.png');
