<?php
/**
 * Admin layout header — for logged-in pages only.
 * Expects: $admin (from current_admin()), optional $pageHeading.
 */

declare(strict_types=1);

if (!isset($admin) || !$admin) {
    // Safety: never render the panel chrome without an authenticated admin.
    require_once dirname(__DIR__) . '/includes/auth.php';
    require_admin();
    $admin = current_admin();
}

$pageHeading = $pageHeading ?? 'Dashboard';
$current = basename($_SERVER['PHP_SELF']);

$nav = [
    'index.php'    => 'Dashboard',
    'pages.php'    => 'Pages',
    'packages.php' => 'Packages',
    'gallery.php'  => 'Gallery',
    'blog.php'     => 'Blog',
    'messages.php' => 'Messages',
    'settings.php' => 'Settings',
];

// Keep the correct nav item highlighted while on a sub-page (edit forms).
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
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="admin">
<header class="admin-topbar">
    <a class="admin-brand" href="<?= e(url('admin/index.php')) ?>"><?= e(SITE_NAME) ?> <span>Admin</span></a>
    <div class="admin-user">
        <span><?= e($admin['username']) ?></span>
        <a class="btn btn-ghost" href="<?= e(url('admin/logout.php')) ?>">Log out</a>
    </div>
</header>
<div class="admin-shell">
    <nav class="admin-nav">
        <?php foreach ($nav as $file => $label): ?>
            <a href="<?= e(url('admin/' . $file)) ?>"
               class="<?= $current === $file ? 'is-active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <main class="admin-main">
        <h1 class="admin-h1"><?= e($pageHeading) ?></h1>
        <?php $flash = flash_get(); ?>
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type'] === 'error' ? 'error' : 'success') ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
