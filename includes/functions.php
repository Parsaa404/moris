<?php
/**
 * Shared helper functions for Moris website.
 */

declare(strict_types=1);

/**
 * Escape output for HTML (XSS protection).
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Build a URL with optional BASE_PATH prefix.
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $base = rtrim(BASE_PATH, '/');

    if ($base === '') {
        return '/' . $path;
    }

    return $base . '/' . $path;
}

/**
 * Redirect and stop execution.
 */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Split a multi-line features string into a clean array (one feature per line).
 */
function parse_features(?string $raw): array
{
    if ($raw === null || trim($raw) === '') {
        return [];
    }
    $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    $lines = array_map('trim', $lines);
    return array_values(array_filter($lines, static fn ($l) => $l !== ''));
}

/**
 * Fetch active packages ordered for public display.
 */
function get_active_packages(): array
{
    try {
        require_once __DIR__ . '/db.php';
        return db()->query(
            'SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order, id'
        )->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * Format a price with its currency (e.g. "RM 1,500" or "USD 99").
 */
function format_price(float $price, string $currency): string
{
    $symbols = ['MYR' => 'RM', 'USD' => 'USD', 'EUR' => 'EUR'];
    $label = $symbols[$currency] ?? $currency;
    // Whole numbers show without decimals; otherwise show 2 decimals.
    $amount = ($price == floor($price))
        ? number_format($price, 0)
        : number_format($price, 2);
    return $label . ' ' . $amount;
}

/**
 * Fetch an active CMS page by slug (home, about, services). Null if missing.
 */
function get_page(string $slug): ?array
{
    try {
        require_once __DIR__ . '/db.php';
        $stmt = db()->prepare('SELECT * FROM pages WHERE slug = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $page = $stmt->fetch();
        return $page ?: null;
    } catch (Throwable) {
        return null;
    }
}

/**
 * Pick a language-specific field from a row (e.g. title_en / title_fa),
 * falling back to English if the chosen language is empty.
 */
function lang_field(array $row, string $base, string $lang): string
{
    $value = $row[$base . '_' . $lang] ?? '';
    if ($value === '' || $value === null) {
        $value = $row[$base . '_en'] ?? '';
    }
    return (string) $value;
}

/**
 * Ensure a slug is unique within a table (appends -2, -3, ... if needed).
 */
function unique_slug(string $table, string $slug, int $excludeId = 0): string
{
    // Whitelist table names (never interpolate user input into SQL identifiers).
    $allowed = ['blog_posts', 'blog_categories', 'gallery_categories'];
    if (!in_array($table, $allowed, true)) {
        return $slug;
    }

    try {
        require_once __DIR__ . '/db.php';
        $base = $slug;
        $i = 1;
        while (true) {
            $stmt = db()->prepare("SELECT COUNT(*) FROM {$table} WHERE slug = ? AND id <> ?");
            $stmt->execute([$slug, $excludeId]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $slug;
            }
            $i++;
            $slug = $base . '-' . $i;
        }
    } catch (Throwable) {
        return $slug;
    }
}

/**
 * Blog categories (optionally only active ones).
 */
function get_blog_categories(bool $activeOnly = false): array
{
    try {
        require_once __DIR__ . '/db.php';
        $sql = 'SELECT * FROM blog_categories';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order, id';
        return db()->query($sql)->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * Published blog posts (newest first), with category name joined.
 */
function get_published_posts(): array
{
    try {
        require_once __DIR__ . '/db.php';
        return db()->query(
            'SELECT p.*, c.name_en AS cat_en, c.name_fa AS cat_fa
             FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id
             WHERE p.is_published = 1
             ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC'
        )->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * A single published post by slug (with category name). Null if not found.
 */
function get_post_by_slug(string $slug): ?array
{
    try {
        require_once __DIR__ . '/db.php';
        $stmt = db()->prepare(
            'SELECT p.*, c.name_en AS cat_en, c.name_fa AS cat_fa
             FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id
             WHERE p.slug = ? AND p.is_published = 1 LIMIT 1'
        );
        $stmt->execute([$slug]);
        $post = $stmt->fetch();
        return $post ?: null;
    } catch (Throwable) {
        return null;
    }
}

/**
 * Convert text to a URL-friendly slug (ascii letters, numbers, hyphens).
 */
function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? '';
    $text = trim($text, '-');
    $text = strtolower($text);
    // Transliterate where possible (keeps latin; leaves other scripts as-is).
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    if ($ascii !== false && $ascii !== '') {
        $ascii = preg_replace('/[^a-z0-9\-]+/', '', strtolower($ascii)) ?? '';
        $ascii = trim($ascii, '-');
        if ($ascii !== '') {
            $text = $ascii;
        }
    }
    return $text !== '' ? $text : 'item-' . substr(bin2hex(random_bytes(3)), 0, 6);
}

/**
 * Handle an uploaded image: validate type/size and move to uploads folder.
 * Returns the stored relative path (e.g. assets/images/uploads/ab12.jpg) or null on error.
 */
function handle_image_upload(array $file, ?string &$error = null): ?string
{
    $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($err === UPLOAD_ERR_NO_FILE) {
        $error = 'Please choose an image to upload.';
        return null;
    }
    if ($err !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please try again (error code ' . $err . ').';
        return null;
    }
    if (($file['size'] ?? 0) > 6 * 1024 * 1024) {
        $error = 'Image is too large. Maximum size is 6 MB.';
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        $error = 'Only JPG, PNG, WEBP, or GIF images are allowed.';
        return null;
    }

    $dir = __DIR__ . '/../assets/images/uploads';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $error = 'Could not save the uploaded file. Check folder permissions.';
        return null;
    }

    return 'assets/images/uploads/' . $name;
}

/**
 * Delete a stored upload file (best effort). Path is relative to project root.
 */
function delete_upload(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    // Reject path tricks early.
    if (str_contains($relativePath, '..') || str_contains($relativePath, "\0")) {
        return;
    }
    if (!str_starts_with($relativePath, 'assets/images/uploads/')) {
        return;
    }

    $uploadsRoot = realpath(__DIR__ . '/../assets/images/uploads');
    $full = realpath(__DIR__ . '/../' . $relativePath);

    if ($uploadsRoot === false || $full === false || !is_file($full)) {
        return;
    }
    // Final path must remain inside the uploads directory.
    if (!str_starts_with($full, $uploadsRoot . DIRECTORY_SEPARATOR)) {
        return;
    }
    @unlink($full);
}

/**
 * Gallery categories (optionally only active ones).
 */
function get_gallery_categories(bool $activeOnly = false): array
{
    try {
        require_once __DIR__ . '/db.php';
        $sql = 'SELECT * FROM gallery_categories';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order, id';
        return db()->query($sql)->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * Gallery items (optionally only active ones), newest sort first.
 */
function get_gallery_items(bool $activeOnly = false): array
{
    try {
        require_once __DIR__ . '/db.php';
        $sql = 'SELECT * FROM gallery_items';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order, id DESC';
        return db()->query($sql)->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * Start a lightweight session for public forms (separate from the admin session).
 */
function public_session(): void
{
    require_once __DIR__ . '/security.php';
    if (session_status() !== PHP_SESSION_ACTIVE) {
        secure_session_cookie_params();
        session_name('moris_public');
        session_start();
    }
    send_security_headers();
}

/**
 * CSRF token for public forms (e.g. contact).
 */
function form_token(): string
{
    public_session();
    if (empty($_SESSION['form_token'])) {
        $_SESSION['form_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['form_token'];
}

/**
 * Hidden CSRF field for public forms.
 */
function form_field(): string
{
    return '<input type="hidden" name="form_token" value="' . e(form_token()) . '">';
}

/**
 * Validate a public form's CSRF token.
 */
function form_check(): bool
{
    public_session();
    $token = $_POST['form_token'] ?? '';
    return is_string($token)
        && !empty($_SESSION['form_token'])
        && hash_equals($_SESSION['form_token'], $token);
}

/**
 * Email a contact-form notification to the site owner.
 * Returns true if the mail was accepted for delivery.
 * (On local MAMP there may be no mail server, so this can return false —
 *  the message is still saved to the database regardless.)
 */
function send_contact_notification(array $data): bool
{
    $to = (string) setting('contact_email', '');
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    require_once __DIR__ . '/security.php';

    $name = strip_crlf((string) ($data['name'] ?? ''));
    $email = strip_crlf((string) ($data['email'] ?? ''));
    $phone = strip_crlf((string) ($data['phone'] ?? ''));
    $goal = strip_crlf((string) ($data['goal'] ?? ''));
    $message = (string) ($data['message'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subject = strip_crlf('New enquiry from ' . $name);
    $body = "You received a new message from the Moris website.\n\n"
        . 'Name: ' . $name . "\n"
        . 'Email: ' . $email . "\n"
        . 'Phone: ' . ($phone !== '' ? $phone : '-') . "\n"
        . 'Goal: ' . ($goal !== '' ? $goal : '-') . "\n\n"
        . "Message:\n" . $message . "\n";

    $headers = 'From: ' . strip_crlf(SITE_NAME) . ' <' . $to . '>' . "\r\n"
        . 'Reply-To: ' . $email . "\r\n"
        . 'Content-Type: text/plain; charset=UTF-8';

    return @mail($to, $subject, $body, $headers);
}

/**
 * Get a site setting value from database (cached per request).
 */
function setting(string $key, ?string $default = null): ?string
{
    static $cache = [];

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        require_once __DIR__ . '/db.php';
        $stmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? $row['setting_value'] : $default;
    } catch (Throwable) {
        $cache[$key] = $default;
    }

    return $cache[$key];
}

/**
 * Create or update a site setting (upsert).
 */
function set_setting(string $key, ?string $value): void
{
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$key, $value]);
}
