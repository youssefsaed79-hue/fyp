<?php
declare(strict_types=1);

/**
 * InternFlow Global Helper Functions
 */

require_once __DIR__ . '/View.php';

use App\Core\View;

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = '', array $params = []): string
    {
        $base = View::getBaseUrl();
        $path = '/' . ltrim($path, '/');
        $url = $base . $path;
        if (!empty($params)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
        }
        return $url ?: '/';
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}
