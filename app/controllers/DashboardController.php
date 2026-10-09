<?php
declare(strict_types=1);

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireRole(['admin', 'owner']);

        $role = $_SESSION['role'] ?? '';
        $data = [];

        if ($role === 'owner') {
            $bookingModel = new Booking($this->db());
            $ownerId = (int)$_SESSION['user_id'];
            $bookings = $bookingModel->getForOwner($ownerId);

            $pendingCount = 0;
            $approvedCount = 0;
            $earnings = 0.0;

            foreach ($bookings as $b) {
                $status = (string)($b['status'] ?? '');
                if ($status === 'pending') $pendingCount++;
                if ($status === 'approved') {
                    $approvedCount++;
                    $earnings += (float)($b['total_amount'] ?? 0);
                }
            }

            $data = [
                'pendingCount' => $pendingCount,
                'approvedCount' => $approvedCount,
                'earnings' => $earnings,
            ];
        }

        $this->render('dashboard/index', $data);
    }
}
