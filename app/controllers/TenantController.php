<?php
declare(strict_types=1);

final class TenantController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireRole(['tenant']);

        $db = $this->db();
        $bookingModel = new Booking($db);
        $paymentModel = new Payment($db);
        $userId = (int)$_SESSION['user_id'];

        $activeRentals = $bookingModel->getAllActiveForTenant($userId);
        
        $totalPaid = $paymentModel->getTotalPaidForUser($userId);
        $recentPayments = [];
        if (!empty($activeRentals)) {
            // Get combined recent payments from the first rental for now, or all?
            // User likely wants historical recent payments.
            $recentPayments = $paymentModel->getFilteredForTenant($userId, null, null);
            $recentPayments = array_slice($recentPayments, 0, 3);
        }
        
        // Calculate Earliest Next Payment
        $nextPayment = 'N/A';
        $daysRemaining = 0;
        $progressPercent = 0;

        if (!empty($activeRentals)) {
            $earliestNextDate = null;
            $currentCycleStart = null;
            
            foreach ($activeRentals as $rental) {
                $startDate = new DateTime($rental['start_date']);
                $paymentCount = $paymentModel->getRentPaymentCount((int)$rental['booking_id']);
                
                // Next Due Date = Start Date + (Number of months paid)
                $nextDate = clone $startDate;
                if ($paymentCount > 0) {
                    $nextDate->modify('+' . $paymentCount . ' months');
                }
                
                if ($earliestNextDate === null || $nextDate < $earliestNextDate) {
                    $earliestNextDate = $nextDate;
                    // The cycle for this specific "earliest" rental starts 1 month before its due date
                    $currentCycleStart = clone $nextDate;
                    $currentCycleStart->modify('-1 month');
                }
            }

            if ($earliestNextDate) {
                $nextPayment = $earliestNextDate->format('M d, Y');
                
                $now = new DateTime();
                $totalDays = $currentCycleStart->diff($earliestNextDate)->days;
                
                if ($totalDays > 0) {
                    $elapsedDays = $currentCycleStart->diff($now)->days;
                    $daysRemaining = max(0, $totalDays - $elapsedDays);
                    $progressPercent = min(100, round(($elapsedDays / $totalDays) * 100));
                }
            }
        }

        // Fetch amenities for the most recent active house
        $amenities = [];
        if (!empty($activeRentals)) {
            $bhModel = new BoardingHouse($db);
            $amenities = $bhModel->getAmenitiesForBoardingHouse((int)$activeRentals[0]['boarding_house_id']);
        }

        $this->render('tenant/dashboard', [
            'activeRental' => !empty($activeRentals) ? $activeRentals[0] : null,
            'activeRentals' => $activeRentals,
            'totalPaid' => $totalPaid,
            'recentPayments' => $recentPayments,
            'nextPayment' => $nextPayment,
            'daysRemaining' => $daysRemaining,
            'progressPercent' => $progressPercent,
            'amenities' => $amenities,
        ]);
    }

    public function dues(): void
    {
        $this->requireRole(['tenant']);

        $db = $this->db();
        $bookingModel = new Booking($db);
        $userId = (int)$_SESSION['user_id'];

        $activeRentals = $bookingModel->getAllActiveForTenant($userId);
        
        $paymentModel = new Payment($db);
        $dues = [];
        foreach ($activeRentals as $rental) {
            $startDate = new DateTime($rental['start_date']);
            $paymentCount = $paymentModel->getRentPaymentCount((int)$rental['booking_id']);
            
            $nextDate = clone $startDate;
            if ($paymentCount > 0) {
                $nextDate->modify('+' . $paymentCount . ' months');
            }

            $now = new DateTime();
            $oneMonthFromNow = (clone $now)->modify('+1 month');
            $isPaidAhead = ($nextDate > $oneMonthFromNow);

            $dues[] = [
                'boarding_house_name' => $rental['boarding_house_name'],
                'room_name' => $rental['room_name'],
                'amount' => (float)$rental['room_price'],
                'due_date' => $nextDate->format('M d, Y'),
                'booking_id' => $rental['booking_id'],
                'is_paid_ahead' => $isPaidAhead
            ];
        }

        $this->render('tenant/dues', [
            'dues' => $dues
        ]);
    }

    public function payments(): void
    {
        $this->requireRole(['tenant']);

        $db = $this->db();
        $userId = (int)$_SESSION['user_id'];
        
        // Get all tenant's bookings for boarding house filter dropdown
        $bookingModel = new Booking($db);
        $bookings = $bookingModel->getForTenant($userId);
        $uniqueHouses = [];
        foreach ($bookings as $b) {
            $uniqueHouses[$b['boarding_house_id']] = $b['boarding_house_name'];
        }

        // Parse Filters
        $filterHouseId = isset($_GET['bhouse_id']) && $_GET['bhouse_id'] !== '' ? (int)$_GET['bhouse_id'] : null;
        $filterMonth = isset($_GET['month']) && $_GET['month'] !== '' ? $_GET['month'] : null;

        $paymentModel = new Payment($db);
        $payments = $paymentModel->getFilteredForTenant($userId, $filterHouseId, $filterMonth);

        $this->render('tenant/payments', [
            'payments' => $payments,
            'uniqueHouses' => $uniqueHouses,
            'filterHouseId' => $filterHouseId,
            'filterMonth' => $filterMonth
        ]);
    }

    public function bookings(): void
    {
        $this->requireRole(['tenant']);

        $bookingModel = new Booking($this->db());
        $userId = (int)$_SESSION['user_id'];
        
        $bookings = $bookingModel->getForTenant($userId);
        
        $activeRentals = $bookingModel->getAllActiveForTenant($userId);
        $activeBookingId = !empty($activeRentals) ? (int)$activeRentals[0]['booking_id'] : null;

        $this->render('tenant/bookings', [
            'bookings' => $bookings,
            'activeBookingId' => $activeBookingId
        ]);
    }

    public function favorites(): void
    {
        $this->requireRole(['tenant']);

        $favModel = new Favorite($this->db());
        $favorites = $favModel->getForUser((int)$_SESSION['user_id']);

        $this->render('tenant/favorites', [
            'favorites' => $favorites,
        ]);
    }

    public function profile(): void
    {
        $this->requireRole(['tenant']);

        $db = $this->db();
        $userId = (int)$_SESSION['user_id'];

        $userModel = new User($db);
        $user = $userModel->findById($userId);

        $bookingModel = new Booking($db);
        $activeRentals = $bookingModel->getAllActiveForTenant($userId);
        $activeRental = !empty($activeRentals) ? $activeRentals[0] : null;

        $this->render('tenant/profile', [
            'user' => $user,
            'activeRental' => $activeRental,
        ]);
    }

    public function settings(): void
    {
        $this->requireRole(['tenant']);

        $db = $this->db();
        $userId = (int)$_SESSION['user_id'];

        $userModel = new User($db);
        $user = $userModel->findById($userId);

        $this->render('tenant/settings', [
            'user' => $user,
        ]);
    }
}

