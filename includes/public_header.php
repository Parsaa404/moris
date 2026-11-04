<?php
/**
 * Public site header (shared by EN + FA pages).
 * Expects:
 *   $lang, $dir, $pageTitle, $activeNav, $currentPage
 *   Optional: $metaDescription, $ogImage, $ogType
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
$defaultDesc = $isFa
    ? setting('seo_default_desc_fa', setting('tagline_fa', 'مربی‌گری خصوصی بوکس، تمرینات قدرتی و تناسب اندام در کوالالامپور و آنلاین با مربی موری.'))
    : setting('seo_default_desc_en', setting('tagline_en', '1-on-1 Personal Training, Boxing Coaching & Athletic Conditioning in Kuala Lumpur and Online with Coach Mori.'));
$metaDescription = isset($metaDescription) && trim((string) $metaDescription) !== ''
    ? trim((string) $metaDescription)
    : (string) $defaultDesc;

$defaultKeywords = $isFa
    ? setting('seo_keywords_fa', 'مربی خصوصی کوالالامپور, آموزش بوکس, بدنسازی, مربی آنلاین, کاهش وزن')
    : setting('seo_keywords_en', 'personal trainer kuala lumpur, boxing coach kl, strength training, online fitness coach, fat loss');
$metaKeywords = isset($metaKeywords) && trim((string) $metaKeywords) !== ''
    ? trim((string) $metaKeywords)
    : (string) $defaultKeywords;

$robotsMeta = isset($robotsMeta) && trim((string) $robotsMeta) !== ''
    ? trim((string) $robotsMeta)
    : 'index, follow';

$googleVerify = trim((string) setting('seo_google_verify', ''));

$scheme = is_https() ? 'https' : 'http';
$rawHost = $_SERVER['HTTP_HOST'] ?? 'morispt.com';
$safeHost = preg_replace('/[^a-zA-Z0-9.\-:]/', '', $rawHost) ?: 'morispt.com';
$siteOrigin = $scheme . '://' . $safeHost;

$enPath = url('en/' . $currentPage);
$faPath = url('fa/' . $currentPage);
$canonicalUrl = $siteOrigin . ($isFa ? $faPath : $enPath);
$enAltUrl = $siteOrigin . $enPath;
$faAltUrl = $siteOrigin . $faPath;

$defaultOg = trim((string) setting('seo_og_image', 'assets/images/hero-earn-it.webp')) ?: 'assets/images/hero-earn-it.webp';
$ogImageRel = isset($ogImage) && trim((string) $ogImage) !== ''
    ? trim((string) $ogImage)
    : $defaultOg;
$ogImageUrl = str_starts_with($ogImageRel, 'http') ? $ogImageRel : ($siteOrigin . url($ogImageRel));
$ogType = $ogType ?? 'website';

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

$schemaData = [
    '@context' => 'https://schema.org',
    '@type' => 'HealthAndBeautyBusiness',
    'name' => $brand,
    'description' => $metaDescription,
    'url' => $siteOrigin . url($lang . '/'),
    'image' => $ogImageUrl,
    'address' => [
        '@type' => 'PostalAddress',
        'addressLocality' => 'Kuala Lumpur',
        'addressCountry' => 'MY',
    ],
];
$contactPhone = setting('contact_phone', '');
if ($contactPhone !== '' && $contactPhone !== '+60') {
    $schemaData['telephone'] = $contactPhone;
}
$sameAs = array_values(array_filter([
    setting('instagram_url', ''),
    setting('youtube_url', ''),
]));
if ($sameAs) {
    $schemaData['sameAs'] = $sameAs;
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" dir="<?= e($dir) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php if ($metaKeywords !== ''): ?>
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= e($robotsMeta) ?>">
    <?php if ($googleVerify !== ''): ?>
    <meta name="google-site-verification" content="<?= e($googleVerify) ?>">
    <?php endif; ?>

    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <link rel="alternate" hreflang="en" href="<?= e($enAltUrl) ?>">
    <link rel="alternate" hreflang="fa" href="<?= e($faAltUrl) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= e($enAltUrl) ?>">

    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:site_name" content="<?= e($brand) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e($ogImageUrl) ?>">
    <meta property="og:locale" content="<?= $isFa ? 'fa_IR' : 'en_US' ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImageUrl) ?>">

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23111111'/%3E%3Ctext x='28' y='44' font-family='sans-serif' font-size='34' font-weight='800' fill='%23ffffff' text-anchor='middle'%3EM%3C/text%3E%3Ccircle cx='49' cy='40' r='5' fill='%2300d0b4'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Serif+Display:ital@0;1&family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">

    <script type="application/ld+json"><?= json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
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
            <?php foreach ($menu as $menuSlug => $item): ?>
                <a href="<?= e(url($lang . '/' . $item['file'])) ?>"
                   class="nav-link <?= $activeNav === $menuSlug ? 'is-active' : '' ?>"><?= e($item['label']) ?></a>
            <?php endforeach; ?>
            <a class="nav-cta <?= $activeNav === 'contact' ? 'is-active' : '' ?>" href="<?= e(url($lang . '/contact.php')) ?>"><?= e($ctaLabel) ?></a>
            <a class="lang-switch" href="<?= e(url($otherLang . '/' . $currentPage)) ?>"><?= e($switchLabel) ?></a>
        </nav>
    </div>
</header>
<main class="site-main">
