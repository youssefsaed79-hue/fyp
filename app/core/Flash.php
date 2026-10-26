<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Flash Messages across HTTP Redirects
 */
class Flash
{
    private const SESSION_KEY = '_flash_messages';

    public static function set(string $type, string $message): void
    {
        $messages = Session::get(self::SESSION_KEY, []);
        $messages[] = [
            'type' => $type, // 'success', 'danger', 'warning', 'info'
            'message' => $message,
        ];
        Session::set(self::SESSION_KEY, $messages);
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function danger(string $message): void
    {
        self::set('danger', $message);
    }

    public static function warning(string $message): void
    {
        self::set('warning', $message);
    }

    public static function info(string $message): void
    {
        self::set('info', $message);
    }

    public static function get(): array
    {
        $messages = Session::get(self::SESSION_KEY, []);
        Session::remove(self::SESSION_KEY);
        return $messages;
    }

    public static function has(): bool
    {
        $messages = Session::get(self::SESSION_KEY, []);
        return !empty($messages);
    }
}
