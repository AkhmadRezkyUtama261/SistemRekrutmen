<?php
/*
 * @Module:      Database Configuration
 * @Author:      BE-01 (Database Core & Security)
 * @Date:        2026-05-24
 * @Description: PDO connection singleton with secure defaults.
 *               Uses utf8mb4, native prepared statements, and
 *               exception-based error handling.
 * @Ownership:   BE-01
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/app.php';

class Database
{
    /** @var PDO|null Singleton PDO instance */
    private static ?PDO $instance = null;

    /**
     * PDO connection options — security-hardened defaults.
     * - ERRMODE_EXCEPTION: throws on query errors (never silent failures)
     * - FETCH_ASSOC: returns associative arrays by default
     * - EMULATE_PREPARES false: uses native MySQL prepared statements
     *   (prevents type-juggling attacks and ensures proper parameterization)
     */
    private static array $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ];

    /**
     * Get the singleton PDO connection.
     *
     * Uses constants defined in config/app.php:
     *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, DB_CHARSET
     *
     * @return PDO Active database connection
     * @throws RuntimeException If connection fails (safe message for end-users)
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, self::$options);
            } catch (PDOException $e) {
                // Log the actual error for developers — NEVER expose to users
                error_log(sprintf(
                    '[RecruitPro DB Error] %s | File: %s | Line: %d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));

                // Return a safe, generic message to the user
                throw new RuntimeException(
                    'Sistem sedang mengalami gangguan. Silakan coba beberapa saat lagi.'
                );
            }
        }

        return self::$instance;
    }

    /**
     * Execute a prepared statement with parameters.
     *
     * Convenience wrapper that prepares, executes, and returns
     * the PDOStatement for further processing (fetch, fetchAll, rowCount).
     *
     * @param  string $sql    SQL query with named placeholders (:param)
     * @param  array  $params Associative array of parameter values
     * @return PDOStatement   Executed statement ready for fetching
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Begin a database transaction.
     *
     * Use for multi-step operations (e.g., creating application + status log).
     * Always pair with commit() or rollback() in a try/catch block.
     */
    public static function beginTransaction(): void
    {
        self::getConnection()->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    public static function commit(): void
    {
        self::getConnection()->commit();
    }

    /**
     * Roll back the current transaction.
     */
    public static function rollback(): void
    {
        self::getConnection()->rollBack();
    }

    /**
     * Get the last inserted auto-increment ID.
     *
     * @return string Last insert ID
     */
    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    // ── Prevent instantiation, cloning, and unserialization ──

    private function __construct() {}

    private function __clone() {}

    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize a singleton.');
    }
}
