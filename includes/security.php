<?php
/**
 * Security helpers for Moris (rate limits, headers, sanitization).
 */

declare(strict_types=1);

/**
 * Apply secure session cookie parameters before session_start().
 */
function secure_session_cookie_params(): void
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https, // true on HTTPS (production); false on local HTTP
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Send baseline security headers (idempotent).
 */
function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com data:; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
    if ($https) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/**
 * Directory for rate-limit files (created on demand).
 */
function rate_limit_dir(): string
{
    $dir = __DIR__ . '/cache/ratelimit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    return $dir;
}

/**
 * Simple file-based rate limiter.
 * Returns true if the action is allowed; false if over the limit.
 */
function rate_limit_allow(string $key, int $maxAttempts, int $windowSeconds): bool
{
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $key) ?: 'key';
    $file = rate_limit_dir() . '/' . $safe . '.json';
    $now = time();
    $data = ['attempts' => []];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        $decoded = $raw ? json_decode($raw, true) : null;
        if (is_array($decoded) && isset($decoded['attempts']) && is_array($decoded['attempts'])) {
            $data = $decoded;
        }
    }

    // Keep only attempts inside the window.
    $data['attempts'] = array_values(array_filter(
        $data['attempts'],
        static fn ($t) => is_int($t) && ($now - $t) < $windowSeconds
    ));

    if (count($data['attempts']) >= $maxAttempts) {
        return false;
    }

    $data['attempts'][] = $now;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

/**
 * Client IP for rate limiting (best-effort behind shared hosting).
 */
function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return is_string($ip) ? $ip : '0.0.0.0';
}

/**
 * Strip CR/LF to prevent mail / header injection.
 */
function strip_crlf(string $value): string
{
    return str_replace(["\r", "\n", "%0a", "%0d", "%0A", "%0D"], '', $value);
}

/**
 * Allow only http/https URLs (blocks javascript:, data:, etc.).
 */
function sanitize_http_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return '';
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return '';
    }
    return $url;
}

/**
 * Allowlist HTML for CMS content (pages, posts, package descriptions).
 * Strips scripts, event handlers, and dangerous tags without needing Composer.
 */
function sanitize_html(?string $html): string
{
    if ($html === null || $html === '') {
        return '';
    }

    // Remove null bytes and script/style blocks early.
    $html = str_replace("\0", '', $html);
    $html = preg_replace('#<(script|iframe|object|embed|link|meta|form|input|button|textarea|select)[^>]*>.*?</\1>#is', '', $html) ?? '';
    $html = preg_replace('#<(script|iframe|object|embed|link|meta|form|input|button|textarea|select)[^>]*/?>#is', '', $html) ?? '';

    // Strip on* event handlers and javascript: URLs.
    $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
    $html = preg_replace('/(href|src)\s*=\s*([\'"]?)\s*javascript:[^\'"\s>]*/i', '$1=$2#', $html) ?? '';

    $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><blockquote><a><span>';
    $clean = strip_tags($html, $allowed);

    // Keep only safe <a href="https?://..."> attributes.
    $clean = preg_replace_callback(
        '#<a\s+[^>]*href\s*=\s*([\'"])(.*?)\1[^>]*>#i',
        static function (array $m): string {
            $href = sanitize_http_url(html_entity_decode($m[2], ENT_QUOTES, 'UTF-8'));
            if ($href === '') {
                return '<a>';
            }
            return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" rel="noopener noreferrer">';
        },
        $clean
    ) ?? $clean;

    return $clean;
}
