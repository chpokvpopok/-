<?php
/**
 * router.php — точка входа для встроенного сервера PHP (php -S).
 * Эмулирует mod_rewrite: статика отдаётся как есть, остальное → index.php.
 */
declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');

if ($uri !== '/' && is_file(__DIR__ . $uri)) {
    return false;
}

require __DIR__ . '/index.php';
