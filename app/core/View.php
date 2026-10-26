<?php
declare(strict_types=1);

namespace App\Core;

/**
 * View Renderer with Layout & Partial support, escaping and URL helpers
 */
class View
{
    private static string $baseViewsPath = '';
    private static string $baseUrl = '';

    public static function init(string $viewsPath, string $baseUrl = ''): void
    {
        self::$baseViewsPath = rtrim($viewsPath, '/\\');
        self::$baseUrl = rtrim($baseUrl, '/');
    }

    public static function getBaseUrl(): string
    {
        if (!empty(self::$baseUrl)) {
            return self::$baseUrl;
        }

        // Auto-detect script base URL
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = dirname($scriptName);
        if ($dir === '/' || $dir === '\\') {
            return '';
        }
        return rtrim(str_replace('\\', '/', $dir), '/');
    }

    /**
     * Render a view within a layout
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewFile = self::$baseViewsPath . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        // Extract variables to local scope
        extract($data, EXTR_SKIP);

        // Buffer view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render layout
        if ($layout) {
            $layoutFile = self::$baseViewsPath . '/' . str_replace('.', '/', $layout) . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout file not found: {$layoutFile}");
            }
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Include a partial view template
     */
    public static function partial(string $partial, array $data = []): void
    {
        $partialFile = self::$baseViewsPath . '/' . str_replace('.', '/', $partial) . '.php';
        if (!file_exists($partialFile)) {
            echo "<!-- Partial not found: {$partialFile} -->";
            return;
        }
        extract($data, EXTR_SKIP);
        require $partialFile;
    }

    /**
     * Render JSON response
     */
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Global helper: Escape HTML
 */
if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Global helper: Generate application URL
 */
if (!function_exists('url')) {
    function url(string $path = '', array $params = []): string
    {
        $base = \App\Core\View::getBaseUrl();
        $path = '/' . ltrim($path, '/');
        $url = $base . $path;
        if (!empty($params)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
        }
        return $url ?: '/';
    }
}

/**
 * Global helper: Asset URL
 */
if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}
