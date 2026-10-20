<?php
/**
 * Router for PHP built-in server (local Mac development only).
 * Usage: php -S localhost:8000 router.php
 *
 * Ensures /en/, /fa/, and /admin/ work when the server runs from the project root.
 */
declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode((string) $uri);
if ($uri === false || $uri === '') {
    $uri = '/';
}

$root = __DIR__;
$fsPath = $root . $uri;

if ($uri !== '/' && is_dir($fsPath)) {
    $index = rtrim($fsPath, '/\\') . '/index.php';
    if (is_file($index)) {
        chdir(dirname($index));
        require $index;
        return true;
    }
}

if ($uri !== '/' && is_file($fsPath)) {
    return false;
}

if ($uri === '/' && is_file($root . '/index.php')) {
    require $root . '/index.php';
    return true;
}

return false;
