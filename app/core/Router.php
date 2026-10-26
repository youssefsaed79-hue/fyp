<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Front Controller Router
 * Handles both clean SEO URLs and ?r=controller/action query routing.
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function any(string $path, string|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, string|callable $handler): void
    {
        $path = '/' . trim($path, '/');
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Strip subdirectories if app is in a subfolder (e.g. /internflow/public)
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', $scriptDir);
        if ($scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        $uri = '/' . trim($uri ?: '/', '/');
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        // 1. Check if ?r=controller/action query param is present
        if (!empty($_GET['r'])) {
            $this->dispatchQueryRoute((string) $_GET['r']);
            return;
        }

        // 2. Match registered routes
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }

            $pattern = preg_replace('#:([a-zA-Z0-9_]+)#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // 3. Fallback: Controller/Action auto-resolution from clean path
        $parts = explode('/', trim($uri, '/'));
        if (!empty($parts[0])) {
            $controllerName = 'App\\Controllers\\' . str_replace(' ', '', ucwords(str_replace('-', ' ', $parts[0]))) . 'Controller';
            $actionName = isset($parts[1]) ? lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $parts[1])))) : 'index';

            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $actionName)) {
                    $this->executeHandler([$controller, $actionName], array_slice($parts, 2));
                    return;
                }
            }
        }

        // 4. Route not found: 404
        http_response_code(404);
        View::render('errors/404', [
            'title' => '404 Not Found',
            'requestedUrl' => $uri
        ], 'layouts/main');
    }

    private function dispatchQueryRoute(string $r): void
    {
        $parts = explode('/', trim($r, '/'));
        $ctrl = $parts[0] ?? 'dashboard';
        $act = $parts[1] ?? 'index';

        $controllerName = 'App\\Controllers\\' . ucfirst($ctrl) . 'Controller';
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $act)) {
                $controller->$act();
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Page Not Found', 'requestedUrl' => $r], 'layouts/main');
    }

    private function executeHandler(string|callable|array $handler, array $params = []): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$controllerClass, $method] = explode('@', $handler, 2);
            $fullClass = 'App\\Controllers\\' . $controllerClass;
            if (!class_exists($fullClass)) {
                throw new \RuntimeException("Controller {$fullClass} not found.");
            }
            $controller = new $fullClass();
            if (!method_exists($controller, $method)) {
                throw new \RuntimeException("Method {$method} not found on {$fullClass}.");
            }
            call_user_func_array([$controller, $method], $params);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            call_user_func_array($handler, $params);
            return;
        }

        throw new \RuntimeException("Invalid route handler format.");
    }
}
