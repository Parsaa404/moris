<?php
/**
 * Admin layout header — for logged-in pages only.
 * Expects: $admin (from current_admin()), optional $pageHeading.
 */

declare(strict_types=1);

if (!isset($admin) || !$admin) {
    require_once dirname(__DIR__) . '/includes/auth.php';
    require_admin();
    $admin = current_admin();
}

$pageHeading = $pageHeading ?? 'Dashboard';
$current = basename($_SERVER['PHP_SELF']);

$navGroups = [
    'Overview' => [
        'index.php'    => 'Dashboard',
        'messages.php' => 'Messages & Bookings',
    ],
    'Page Builders' => [
        'pages.php'    => 'Pages & Builders',
        'homepage.php' => 'Homepage Builder',
    ],
    'Content & Store' => [
        'packages.php' => 'Packages & Pricing',
        'gallery.php'  => 'Photo Gallery',
        'blog.php'     => 'Blog Articles',
    ],
    'Configuration' => [
        'settings.php' => 'SEO & Site Settings',
        'account.php'  => 'Admin Account',
    ],
];

$navAlias = [
    'page-edit.php'          => 'pages.php',
    'package-edit.php'       => 'packages.php',
    'message-view.php'       => 'messages.php',
    'gallery-categories.php' => 'gallery.php',
    'blog-edit.php'          => 'blog.php',
    'blog-categories.php'    => 'blog.php',
];
if (isset($navAlias[$current])) {
    $current = $navAlias[$current];
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageHeading) ?> — <?= e(SITE_NAME) ?> Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;500;600;700&family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="admin">
<header class="admin-topbar">
    <a class="admin-brand" href="<?= e(url('admin/index.php')) ?>">
        <span class="admin-brand-dot">M</span>
        <?= e(SITE_NAME) ?> <span>Admin Panel</span>
    </a>
    <div class="admin-user">
        <a class="btn btn-ghost" href="<?= e(url('en/')) ?>" target="_blank">EN Site ↗</a>
        <a class="btn btn-ghost" href="<?= e(url('fa/')) ?>" target="_blank">سایت فارسی ↗</a>
        <a class="btn btn-ghost" href="<?= e(url('admin/account.php')) ?>"><?= e($admin['username']) ?></a>
        <form method="post" action="<?= e(url('admin/logout.php')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost">Log out</button>
        </form>
    </div>
</header>
<div class="admin-shell">
    <nav class="admin-nav">
        <?php foreach ($navGroups as $groupLabel => $items): ?>
            <div class="admin-nav-group">
                <div class="admin-nav-group-title"><?= e($groupLabel) ?></div>
                <?php foreach ($items as $file => $label): ?>
                    <a href="<?= e(url('admin/' . $file)) ?>"
                       class="<?= $current === $file ? 'is-active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>
    <main class="admin-main">
        <div class="admin-page-title-bar">
            <h1 class="admin-h1"><?= e($pageHeading) ?></h1>
        </div>
        <?php $flash = flash_get(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type'] === 'error' ? 'error' : 'success') ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
