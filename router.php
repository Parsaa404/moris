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

if ($uri === '/sitemap.xml') {
    require $root . '/sitemap.php';
    return true;
}

// Block direct access to internal directories & non-web files in local dev server
if (preg_match('#^/(includes|sql|vendor|deploy|scratch|postman|\.git|\.postman)(/|$)#i', $uri)
    || preg_match('#\.(md|sql|env|sh|bat|command)$#i', $uri)) {
    http_response_code(403);
    echo '403 Forbidden';
    return true;
}

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

if (is_file($root . '/404.php')) {
    require $root . '/404.php';
    return true;
}

return false;
