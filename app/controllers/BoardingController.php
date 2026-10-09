<?php
declare(strict_types=1);

// Router expects `BoardingController` for `?url=boarding/*`.
// This controller delegates to the boarding-house listing/show behavior.
final class BoardingController extends BaseController
{
    public function index(): void
    {
        $planModel = new Plan($this->db());
        $plans = $planModel->getAll();

        // Fetch most popular plan ID based on paid payments
        $popQuery = $this->db()->query("
            SELECT plan_id, COUNT(id) as cnt 
            FROM plan_payments 
            WHERE status = 'paid' 
            GROUP BY plan_id 
            ORDER BY cnt DESC 
            LIMIT 1
        ");
        $popularPlanId = $popQuery ? (int)($popQuery->fetch_assoc()['plan_id'] ?? 0) : 0;

        $this->render('boarding/index', [
            'plans' => $plans,
            'popularPlanId' => $popularPlanId
        ]);
    }

    public function explore(): void
    {
        $model = new BoardingHouse($this->db());

        $filters = [
            'q' => isset($_POST['q']) ? trim((string)$_POST['q']) : (isset($_GET['q']) ? trim((string)$_GET['q']) : null),
            'min_price' => isset($_POST['min_price']) ? trim((string)$_POST['min_price']) : null,
            'max_price' => isset($_POST['max_price']) ? trim((string)$_POST['max_price']) : null,
        ];

        $houses = $model->getAllApproved($filters, 10, 0);

        $this->render('boarding/explore', [
            'houses' => $houses,
            'filters' => $filters,
        ]);
    }

    public function api_explore(): void
    {
        header('Content-Type: application/json');
        $model = new BoardingHouse($this->db());

        $q = $_GET['q'] ?? $_POST['q'] ?? '';
        $max_price = $_GET['max_price'] ?? $_POST['max_price'] ?? '';

        $filters = [
            'q' => trim((string)$q),
            'max_price' => trim((string)$max_price),
        ];

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $houses = $model->getAllApproved($filters, $limit, $offset);

        echo json_encode([
            'success' => true,
            'houses' => $houses,
            'count' => count($houses),
            'offset' => $offset,
            'limit' => $limit
        ]);
    }

    public function show(): void
    {
        $idRaw = $_GET['id'] ?? null;
        $id = is_numeric($idRaw) ? (int)$idRaw : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo 'Invalid boarding house id';
            return;
        }

        $model = new BoardingHouse($this->db());

        $house = $model->getById($id);
        if (!$house) {
            http_response_code(404);
            echo 'Boarding house not found';
            return;
        }

        $rooms = $model->getRoomsByBoardingHouseId($id);
        $amenities = $model->getAmenitiesForBoardingHouse($id);

        $isFavorited = false;
        if (!empty($_SESSION['user_id'])) {
            $favModel = new Favorite($this->db());
            $isFavorited = $favModel->exists((int)$_SESSION['user_id'], $id);
        }

        $this->render('boarding/show', [
            'house' => $house,
            'rooms' => $rooms,
            'amenities' => $amenities,
            'isFavorited' => $isFavorited,
            'today' => date('Y-m-d')
        ]);
    }

    public function api_show(): void
    {
        header('Content-Type: application/json');
        
        $idRaw = $_GET['id'] ?? null;
        $id = is_numeric($idRaw) ? (int)$idRaw : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid boarding house id']);
            return;
        }

        $model = new BoardingHouse($this->db());
        $house = $model->getById($id);
        
        if (!$house) {
            echo json_encode(['success' => false, 'message' => 'Boarding house not found']);
            return;
        }

        $rooms = $model->getRoomsByBoardingHouseId($id);
        $amenities = $model->getAmenitiesForBoardingHouse($id);

        echo json_encode([
            'success' => true,
            'house' => $house,
            'rooms' => $rooms,
            'amenities' => $amenities
        ]);
    }

    public function toggle_favorite(): void
    {
        header('Content-Type: application/json');
        
        if (empty($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $houseId = isset($_POST['house_id']) ? (int)$_POST['house_id'] : 0;
        if ($houseId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid house id']);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $model = new Favorite($this->db());
        $res = $model->toggle($userId, $houseId);

        echo json_encode([
            'status' => 'success', 
            'action' => $res['favorited'] ? 'added' : 'removed',
            'favorited' => $res['favorited']
        ]);
    }
}

