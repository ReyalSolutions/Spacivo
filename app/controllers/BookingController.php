<?php
declare(strict_types=1);

final class BookingController extends BaseController
{
    public function store(): void
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

        $roomIdRaw = $_POST['room_id'] ?? null;
        $startDate = trim((string)($_POST['start_date'] ?? ''));
        $endDate = trim((string)($_POST['end_date'] ?? ''));

        $roomId = is_numeric($roomIdRaw) ? (int)$roomIdRaw : 0;
        if ($roomId <= 0 || $startDate === '') {
            $this->json(['ok' => false, 'message' => 'Missing required fields.'], 400);
        }

        $startTs = strtotime($startDate);
        if ($startTs === false) {
            $this->json(['ok' => false, 'message' => 'Invalid start date.'], 400);
        }

        // Fetch room info
        $stmtRoom = $this->db()->prepare('SELECT price, available_slots FROM rooms WHERE id = ? LIMIT 1');
        $stmtRoom->bind_param('i', $roomId);
        $stmtRoom->execute();
        $room = $stmtRoom->get_result()->fetch_assoc();
        if (!$room) {
            $this->json(['ok' => false, 'message' => 'Room not found.'], 404);
        }

        if ((int)$room['available_slots'] <= 0) {
            $this->json(['ok' => false, 'message' => 'Room is not available.'], 409);
        }

        $monthlyPrice = (float)$room['price'];
        $totalAmount = $monthlyPrice; // Default to 1 month

        // If end_date is provided, use prorated logic. Otherwise, it's open-ended.
        $dbEndDate = null;
        if ($endDate !== '') {
            $endTs = strtotime($endDate);
            if ($endTs === false || $endTs < $startTs) {
                $this->json(['ok' => false, 'message' => 'Invalid date range.'], 400);
            }
            $days = (int)floor(($endTs - $startTs) / 86400) + 1;
            $totalAmount = round(($monthlyPrice / 30.0) * max(1, $days), 2);
            $dbEndDate = $endDate;
        }

        $bookingModel = new Booking($this->db());
        $paymentModel = new Payment($this->db());

        try {
            $bookingId = $bookingModel->createApproved((int)$_SESSION['user_id'], $roomId, $startDate, $dbEndDate, $totalAmount);
            $paymentId = $paymentModel->createPending((int)$_SESSION['user_id'], $bookingId, $totalAmount, 'gcash');

            $this->json([
                'ok' => true,
                'booking_id' => $bookingId,
                'payment_id' => $paymentId
            ]);
        } catch (Throwable $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 400);
        }
    }
}

