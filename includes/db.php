<?php
/**
 * Database access. A single lazily-created PDO connection for the request.
 */
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        config('db.host', 'localhost'),
        config('db.name', 'formule1'),
        config('db.charset', 'utf8mb4')
    );

    try {
        $pdo = new PDO($dsn, (string)config('db.user', ''), (string)config('db.pass', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        // Session datetimes are stored in UTC.
        $pdo->exec("SET time_zone = '+00:00'");
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(503);
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, "Database connection failed.\n");
            exit(1);
        }
        exit('The site is temporarily unavailable. Please try again in a few minutes.');
    }
    return $pdo;
}

/** Run a query and return all rows; logs and returns [] on failure. */
function db_all(string $sql, array $params = []): array
{
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Query failed: ' . $e->getMessage() . ' — ' . $sql);
        return [];
    }
}

/** Run a query and return the first row or null. */
function db_one(string $sql, array $params = []): ?array
{
    $rows = db_all($sql, $params);
    return $rows[0] ?? null;
}

/** Run a query and return the first column of the first row. */
function db_value(string $sql, array $params = []): mixed
{
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $v = $stmt->fetchColumn();
        return $v === false ? null : $v;
    } catch (PDOException $e) {
        error_log('Query failed: ' . $e->getMessage() . ' — ' . $sql);
        return null;
    }
}
