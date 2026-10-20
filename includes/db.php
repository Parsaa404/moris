<?php
/**
 * Database connection (PDO)
 * Used by public site and admin panel.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Returns a shared PDO instance.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // DB_PORT is optional; defaults to MySQL standard 3306 if not defined.
    $port = defined('DB_PORT') ? (int) DB_PORT : 3306;

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST,
        $port,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Force UTF-8 for Persian / special characters (critical on MAMP).
    $pdo->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');

    return $pdo;
}
