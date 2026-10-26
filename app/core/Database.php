<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Database Singleton using PDO
 * Connects to standalone MySQL 8.0+ database using prepared statements only.
 */
class Database
{
    private static ?PDO $instance = null;
    private static ?array $config = null;

    private function __construct() {}
    private function __clone() {}

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            if (self::$config === null) {
                $configFile = dirname(__DIR__) . '/config/config.php';
                if (!file_exists($configFile)) {
                    throw new RuntimeException("Database configuration file not found.");
                }
                $fullConfig = require $configFile;
                self::$config = $fullConfig['db'];
            }

            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                self::$config['host'],
                self::$config['port'] ?? 3306,
                self::$config['dbname'],
                self::$config['charset'] ?? 'utf8mb4'
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    self::$config['user'],
                    self::$config['pass'],
                    self::$config['options'] ?? [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                error_log("Database connection failure: " . $e->getMessage());
                throw new RuntimeException("Could not connect to the database. Please ensure MySQL is running.");
            }
        }

        return self::$instance;
    }

    /**
     * Helper to run a prepared query and return PDOStatement
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all records
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single row
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $res = self::query($sql, $params)->fetch();
        return $res ?: null;
    }

    /**
     * Fetch a single column value
     */
    public static function fetchValue(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    /**
     * Insert / Update / Delete returning affected rows
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Get last inserted ID
     */
    public static function lastInsertId(): int
    {
        return (int) self::getConnection()->lastInsertId();
    }

    /**
     * Transaction helper
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::getConnection();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
