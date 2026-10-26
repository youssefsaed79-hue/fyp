<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session Handler with Automatic Inactivity Timeout (NFR-SEC-02)
 */
class Session
{
    private static bool $started = false;

    public static function start(array $config = []): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $sessionName = $config['name'] ?? 'INTERNFLOW_SESS';
        $lifetime = $config['lifetime'] ?? 1800; // 30 minutes
        $secure = $config['cookie_secure'] ?? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $httponly = $config['cookie_httponly'] ?? true;
        $samesite = $config['cookie_samesite'] ?? 'Lax';

        session_name($sessionName);

        session_set_cookie_params([
            'lifetime' => 0, // session cookie expires when browser closes
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ]);

        session_start();
        self::$started = true;

        // Check for 30-minute inactivity timeout (NFR-SEC-02)
        self::checkInactivityTimeout($lifetime);
    }

    private static function checkInactivityTimeout(int $lifetime): void
    {
        $now = time();
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            if (($now - $_SESSION['LAST_ACTIVITY']) > $lifetime) {
                // Session expired due to inactivity
                self::destroy();
                session_start();
                $_SESSION['flash_warning'] = 'Your session has expired due to 30 minutes of inactivity. Please log in again.';
                return;
            }
        }
        $_SESSION['LAST_ACTIVITY'] = $now;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
            self::$started = false;
        }
    }
}
