<?php
declare(strict_types=1);

final class FavoritesController extends BaseController
{
    public function toggle(): void
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

        $boardingHouseIdRaw = $_POST['boarding_house_id'] ?? null;
        $boardingHouseId = is_numeric($boardingHouseIdRaw) ? (int)$boardingHouseIdRaw : 0;
        if ($boardingHouseId <= 0) {
            $this->json(['ok' => false, 'message' => 'Invalid boarding_house_id'], 400);
        }

        $model = new Favorite($this->db());

        $userId = (int)$_SESSION['user_id'];
        $res = $model->toggle($userId, $boardingHouseId);

        $this->json(['ok' => true] + $res);
    }
}

