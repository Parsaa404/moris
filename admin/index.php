<?php
/**
 * Admin dashboard — protected landing page.
 * Shows quick stats and direct links to Homepage Builder and CRUD sections.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

/**
 * Small helper to count rows safely (returns 0 if a table is missing).
 */
function count_rows(string $sql): int
{
    try {
        return (int) db()->query($sql)->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

$stats = [
    'active_sections' => count_rows('SELECT COUNT(*) FROM homepage_sections WHERE is_active = 1'),
    'unread_messages' => count_rows('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'),
    'total_messages'  => count_rows('SELECT COUNT(*) FROM contact_messages'),
    'published_posts' => count_rows('SELECT COUNT(*) FROM blog_posts WHERE is_published = 1'),
    'total_posts'     => count_rows('SELECT COUNT(*) FROM blog_posts'),
    'packages'        => count_rows('SELECT COUNT(*) FROM packages WHERE is_active = 1'),
    'gallery'         => count_rows('SELECT COUNT(*) FROM gallery_items'),
];

$pageHeading = 'Dashboard';
require __DIR__ . '/_header.php';
?>

<p class="muted">Welcome back, <?= e($admin['username']) ?>. Here's a quick overview.</p>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-num"><?= (int) $stats['active_sections'] ?></span>
        <span class="stat-label">Homepage sections</span>
        <span class="stat-sub"><a href="<?= e(url('admin/homepage.php')) ?>">Customize layout &rarr;</a></span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $stats['unread_messages'] ?></span>
        <span class="stat-label">Unread messages</span>
        <span class="stat-sub"><?= (int) $stats['total_messages'] ?> total</span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $stats['published_posts'] ?></span>
        <span class="stat-label">Published posts</span>
        <span class="stat-sub"><?= (int) $stats['total_posts'] ?> total</span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $stats['packages'] ?></span>
        <span class="stat-label">Active packages</span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $stats['gallery'] ?></span>
        <span class="stat-label">Gallery images</span>
    </div>
</div>

<div class="panel">
    <h2>Manage your site</h2>
    <p class="muted">Jump to any section:</p>
    <div class="quick-links">
        <a class="btn btn-primary btn-inline" style="width: auto;" href="<?= e(url('admin/homepage.php')) ?>">Homepage Builder</a>
        <a class="btn btn-ghost btn-inline" href="<?= e(url('admin/pages.php')) ?>">Pages</a>
        <a class="btn btn-ghost btn-inline" href="<?= e(url('admin/packages.php')) ?>">Packages</a>
        <a class="btn btn-ghost btn-inline" href="<?= e(url('admin/gallery.php')) ?>">Gallery</a>
        <a class="btn btn-ghost btn-inline" href="<?= e(url('admin/blog.php')) ?>">Blog</a>
        <a class="btn btn-ghost btn-inline" href="<?= e(url('admin/messages.php')) ?>">Messages</a>
        <a class="btn btn-ghost btn-inline" href="<?= e(url('admin/settings.php')) ?>">Settings</a>
        <a class="btn btn-ghost btn-inline" href="<?= e(url('en/')) ?>" target="_blank">View site (EN) ↗</a>
        <a class="btn btn-ghost btn-inline" href="<?= e(url('fa/')) ?>" target="_blank">View site (FA) ↗</a>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
