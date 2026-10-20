<?php
/**
 * Public site header (shared by EN + FA pages).
 * Expects:
 *   $lang, $dir, $pageTitle, $activeNav, $currentPage
 */

declare(strict_types=1);

require_once __DIR__ . '/security.php';
send_security_headers();

$lang = $lang ?? DEFAULT_LANG;
$dir = $dir ?? ($lang === 'fa' ? 'rtl' : 'ltr');
$pageTitle = $pageTitle ?? SITE_NAME;
$activeNav = $activeNav ?? '';
$currentPage = $currentPage ?? '';
$otherLang = $lang === 'en' ? 'fa' : 'en';
$brand = setting('site_name', SITE_NAME);

$isFa = ($lang === 'fa');
$menu = [
    'home'     => ['file' => '',             'label' => $isFa ? 'خانه'    : 'Home'],
    'about'    => ['file' => 'about.php',     'label' => $isFa ? 'درباره'  : 'About'],
    'services' => ['file' => 'services.php',  'label' => $isFa ? 'خدمات'   : 'Services'],
    'packages' => ['file' => 'packages.php',  'label' => $isFa ? 'پکیج‌ها' : 'Packages'],
    'gallery'  => ['file' => 'gallery.php',   'label' => $isFa ? 'گالری'    : 'Gallery'],
    'blog'     => ['file' => 'blog.php',      'label' => $isFa ? 'وبلاگ'    : 'Blog'],
];
$switchLabel = $isFa ? 'EN' : 'فا';
$ctaLabel = $isFa ? 'شروع تمرین' : 'Start training';
$brandInitial = mb_strtoupper(mb_substr($brand, 0, 1));
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" dir="<?= e($dir) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Serif+Display:ital@0;1&family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="site">
<header class="site-header">
    <div class="site-header-inner">
        <a class="brand" href="<?= e(url($lang . '/')) ?>">
            <span class="brand-mark"><?= e($brandInitial) ?></span>
            <span class="brand-text"><?= e($brand) ?><span class="accent">.</span></span>
        </a>

        <input type="checkbox" id="nav-toggle" class="nav-toggle" hidden>
        <label for="nav-toggle" class="nav-burger" aria-label="Menu">
            <span></span><span></span><span></span>
        </label>

        <nav class="site-nav">
            <?php foreach ($menu as $slug => $item): ?>
                <a href="<?= e(url($lang . '/' . $item['file'])) ?>"
                   class="nav-link <?= $activeNav === $slug ? 'is-active' : '' ?>"><?= e($item['label']) ?></a>
            <?php endforeach; ?>
            <a class="nav-cta <?= $activeNav === 'contact' ? 'is-active' : '' ?>" href="<?= e(url($lang . '/contact.php')) ?>"><?= e($ctaLabel) ?></a>
            <a class="lang-switch" href="<?= e(url($otherLang . '/' . $currentPage)) ?>"><?= e($switchLabel) ?></a>
        </nav>
    </div>
</header>
<main class="site-main">
