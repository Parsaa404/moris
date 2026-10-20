<?php
/**
 * Authentication & security helpers for the Moris admin panel.
 * - Session handling
 * - CSRF token generation/checking
 * - Login / logout
 * - Access guard (require_admin)
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php';

/**
 * Start the admin session once (safe to call multiple times).
 */
function auth_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        secure_session_cookie_params();
        session_name(SESSION_NAME);
        session_start();
    }
    send_security_headers();
}

/**
 * Return the current CSRF token (creates one if needed).
 */
function csrf_token(): string
{
    auth_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Hidden input field with the CSRF token — put inside every admin form.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Validate the CSRF token from a submitted form.
 */
function csrf_check(): bool
{
    auth_start();
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * How many admin users exist (used by the installer).
 * On DB error, returns -1 (treat as locked — do NOT reopen install).
 */
function admin_count(): int
{
    try {
        return (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    } catch (Throwable) {
        return -1;
    }
}

/**
 * Is someone logged in?
 */
function is_logged_in(): bool
{
    auth_start();
    return !empty($_SESSION['admin_id']);
}

/**
 * Return the logged-in admin record, or null.
 */
function current_admin(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, username, email FROM admin_users WHERE id = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([(int) $_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    return $admin ?: null;
}

/**
 * Guard a page: redirect to login if not authenticated / inactive.
 */
function require_admin(): void
{
    auth_start();
    if (!is_logged_in() || current_admin() === null) {
        logout();
        redirect('admin/login.php');
    }
}

/**
 * Attempt login with username-or-email + password.
 */
function login_attempt(string $identifier, string $password): bool
{
    $stmt = db()->prepare(
        'SELECT id, password_hash FROM admin_users
         WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1'
    );
    $stmt->execute([$identifier, $identifier]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        auth_start();
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        // Rotate CSRF after privilege change.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }
    return false;
}

/**
 * Store a one-time flash message (shown on the next admin page load).
 */
function flash_set(string $message, string $type = 'success'): void
{
    auth_start();
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Retrieve and clear the flash message, if any.
 */
function flash_get(): ?array
{
    auth_start();
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Log out and clear the session.
 */
function logout(): void
{
    auth_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'] ?: '/',
            'domain'   => $p['domain'] ?? '',
            'secure'   => (bool) ($p['secure'] ?? false),
            'httponly' => (bool) ($p['httponly'] ?? true),
            'samesite' => $p['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}
