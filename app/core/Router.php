<?php
declare(strict_types=1);

final class Router
{
    private string $url;
    private string $controllersDir;

    public function __construct(string $url, string $controllersDir)
    {
        $this->url = trim($url);
        $this->controllersDir = rtrim($controllersDir, '/\\');
    }

    public function dispatch(): void
    {
        $clean = trim($this->url);
        if ($clean === '') {
            $clean = 'home/index';
        }
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(?:\/[a-zA-Z][a-zA-Z0-9_]*){0,2}$/D', $clean)) {
            http_response_code(404);
            echo 'Page not found';
            return;
        }

        // Expected format: controller/method/action (e.g. `owner/bookings/history`).
        $parts = array_values(array_filter(explode('/', $clean), static fn($p) => $p !== ''));
        $controllerToken = $parts[0] ?? 'home';
        $method = $parts[1] ?? 'index';
        $action = $parts[2] ?? null;

        $controllerClass = ucfirst(strtolower($controllerToken)) . 'Controller';
        $controllerFile = $this->controllersDir . DIRECTORY_SEPARATOR . $controllerClass . '.php';

        if (!is_file($controllerFile)) {
            http_response_code(404);
            echo "Controller not found: " . htmlspecialchars($controllerClass, ENT_QUOTES, 'UTF-8');
            return;
        }

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Controller class missing: " . htmlspecialchars($controllerClass, ENT_QUOTES, 'UTF-8');
            return;
        }

        // Dispatch logic:
        // 1. Try combined method_action (e.g., bookings_history)
        // 2. Fallback to base method (e.g., bookings)
        $targetMethod = $method;
        if ($action !== null) {
            $combined = $method . '_' . $action;
            if (method_exists($controllerClass, $combined)) {
                $targetMethod = $combined;
            }
        }

        if (!method_exists($controllerClass, $targetMethod)) {
            http_response_code(404);
            echo "Method not found: " . htmlspecialchars($controllerClass . '::' . $targetMethod, ENT_QUOTES, 'UTF-8');
            return;
        }

        $reflection = new ReflectionMethod($controllerClass, $targetMethod);
        // Only explicitly declared public actions may be dispatched, never inherited helpers.
        if (!$reflection->isPublic() || $reflection->isStatic() || strpos($targetMethod, '__') === 0
            || $reflection->getDeclaringClass()->getName() === BaseController::class
            || $reflection->getNumberOfRequiredParameters() > 0) {
            http_response_code(404);
            echo 'Page not found';
            return;
        }
        $controller = new $controllerClass();

        // Subscription paywall — block overdue owners *before* the action runs.
        // Only applies to controllers that extend BaseController.
        if ($controller instanceof BaseController) {
            $controller->checkSubscriptionPaywall();
        }

        // Call action.
        $controller->$targetMethod();
    }
}

