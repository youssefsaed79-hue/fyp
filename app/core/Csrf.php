<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Cross-Site Request Forgery (CSRF) Protection (NFR-SEC-02)
 */
class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }
        return $token;
    }

    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token): bool
    {
        $sessionToken = Session::get(self::SESSION_KEY);
        if (!$sessionToken || !$token) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    public static function checkOrAbort(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!self::validate($token)) {
                http_response_code(403);
                Flash::set('danger', 'Invalid or expired CSRF security token. Please try again.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
                exit;
            }
        }
    }
}
