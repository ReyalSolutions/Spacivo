<?php
declare(strict_types=1);

namespace App\Modules\Organizations\Controllers\Api;

use App\Modules\Organizations\Services\OrganizationService;
use App\Shared\Exceptions\AuthorizationException;

final class OrganizationController
{
    private OrganizationService $service;
    public function __construct(OrganizationService $service) { $this->service = $service; }

    public function handle(string $action): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (getenv('ORGANIZATIONS_ENABLED') !== 'true') {
            http_response_code(503);
            echo json_encode(['success' => false, 'message' => 'Organization setup is not enabled.']);
            return;
        }
        $actor = (int)($_SESSION['user_id'] ?? 0);
        if ($actor <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required.']);
            return;
        }
        $method = $action === 'show' ? 'GET' : 'POST';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $method) {
            http_response_code(405);
            header('Allow: ' . $method);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }
        if ($method === 'POST' && !\Csrf::verify($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null))) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }
        try {
            $data = null;
            $organizationId = filter_var(($method === 'GET' ? $_GET : $_POST)['organization_id'] ?? null, FILTER_VALIDATE_INT);
            if ($action === 'create') {
                if (!is_string($_POST['name'] ?? null)) {
                    throw new \InvalidArgumentException('Organization name is required.');
                }
                $data = $this->service->onboard($actor, $_POST['name']);
                http_response_code(201);
            } elseif ($organizationId === false || $organizationId <= 0) {
                throw new \InvalidArgumentException('A valid organization ID is required.');
            } elseif ($action === 'show') {
                $data = $this->service->show($actor, $organizationId);
            } elseif ($action === 'add_member') {
                $permissions = $_POST['permissions'] ?? [];
                $memberId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
                $email = $_POST['email'] ?? null;
                if (!is_array($permissions) || count(array_filter($permissions, 'is_string')) !== count($permissions)
                    || !is_string($_POST['role'] ?? null)) {
                    throw new \InvalidArgumentException('Invalid member assignment.');
                }
                if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->service->addMemberByEmail($actor, $organizationId, $email, $_POST['role'], $permissions);
                } elseif ($memberId !== false && $memberId > 0) {
                    $this->service->addMember($actor, $organizationId, $memberId, $_POST['role'], $permissions);
                } else {
                    throw new \InvalidArgumentException('A valid member account is required.');
                }
            } elseif ($action === 'verify') {
                if (!is_string($_POST['decision'] ?? null)) {
                    throw new \InvalidArgumentException('Verification decision is required.');
                }
                $this->service->verify($actor, $organizationId, $_POST['decision']);
            }
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (AuthorizationException $error) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $error->getMessage()]);
        }
    }
}
