<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Mailer Service
 * Delivers email via SMTP or fallback logging to `email_log` table and `storage/mail.log`.
 * Allows the entire internship lifecycle to be demonstrated and tested offline.
 */
class Mailer
{
    private static ?array $config = null;

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function send(string $toEmail, string $subject, string $bodyHtml, string $bodyText = ''): bool
    {
        if (self::$config === null) {
            $fullConfig = require dirname(__DIR__) . '/config/config.php';
            self::$config = $fullConfig['mail'];
        }

        $useSmtp = self::$config['use_smtp'] ?? false;
        $status = 'logged';

        if ($useSmtp && !empty(self::$config['host'])) {
            // Attempt standard mail or SMTP if configured
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . self::$config['from_name'] . ' <' . self::$config['from_email'] . '>',
                'Reply-To: ' . self::$config['from_email'],
                'X-Mailer: PHP/' . phpversion()
            ];
            $sent = @mail($toEmail, $subject, $bodyHtml, implode("\r\n", $headers));
            $status = $sent ? 'sent' : 'failed';
        }

        // Always log to database `email_log` table for demonstration (FR-COM-02, Assumption A1)
        try {
            Database::execute(
                "INSERT INTO email_log (to_email, subject, body, status, created_at)
                 VALUES (:to_email, :subject, :body, :status, NOW())",
                [
                    'to_email' => $toEmail,
                    'subject' => $subject,
                    'body' => $bodyHtml,
                    'status' => $status
                ]
            );
        } catch (\Throwable $e) {
            error_log("Failed to write to email_log table: " . $e->getMessage());
        }

        // Always append to storage/mail.log
        $logPath = self::$config['log_path'] ?? dirname(__DIR__, 2) . '/storage/mail.log';
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logEntry = sprintf(
            "[%s] TO: %s | SUBJECT: %s | STATUS: %s\nBODY:\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $toEmail,
            $subject,
            $status,
            strip_tags($bodyHtml),
            str_repeat('-', 70)
        );
        @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);

        return true;
    }
}
