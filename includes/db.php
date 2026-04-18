<?php
// =============================================================
//  DATABASE CONNECTION
// =============================================================
define('BASE_URL', '/');

$url = parse_url(getenv('DATABASE_URL'));
define('DB_HOST', $url['host']);
define('DB_USER', $url['user']);
define('DB_PASS', $url['pass']);
define('DB_NAME', ltrim($url['path'], '/'));
define('DB_PORT', $url['port'] ?? '3306');
error_log('DATABASE_URL = ' . getenv('DATABASE_URL'));
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}