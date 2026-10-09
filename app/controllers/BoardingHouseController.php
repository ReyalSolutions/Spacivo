<?php
declare(strict_types=1);

final class BoardingHouseController extends BaseController
{
    public function index(): void
    {
        $model = new BoardingHouse($this->db());

        $filters = [
            'q' => isset($_GET['q']) ? trim((string)$_GET['q']) : null,
            'min_price' => isset($_GET['min_price']) ? trim((string)$_GET['min_price']) : null,
            'max_price' => isset($_GET['max_price']) ? trim((string)$_GET['max_price']) : null,
        ];

        $houses = $model->getAllApproved($filters);

        $this->render('boarding/index', [
            'houses' => $houses,
            'filters' => $filters,
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

        $this->render('boarding/show', [
            'house' => $house,
            'rooms' => $rooms,
        ]);
    }
}

