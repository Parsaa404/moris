<?php
/**
 * Custom Bilingual 404 Not Found Page
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

http_response_code(404);

$reqUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
$isFa = str_starts_with($reqUri, '/fa') || (!str_starts_with($reqUri, '/en') && setting('default_language', DEFAULT_LANG) === 'fa');
$lang = $isFa ? 'fa' : 'en';
$dir = $isFa ? 'rtl' : 'ltr';
$activeNav = '';
$currentPage = '';

$pageTitle = ($isFa ? 'صفحه پیدا نشد (۴۰۴)' : 'Page Not Found (404)') . ' — ' . SITE_NAME;

require __DIR__ . '/includes/public_header.php';
?>
<section class="page page-404">
    <div class="error-404-badge">404</div>
    <h1><?= $isFa ? 'صفحه مورد نظر پیدا نشد.' : 'Page not found.' ?></h1>
    <p class="tagline">
        <?= $isFa
            ? 'آدرسی که وارد کرده‌اید وجود ندارد یا جابه‌جا شده است.'
            : 'The page you are looking for might have been moved or no longer exists.' ?>
    </p>
    <div class="error-404-actions">
        <a href="<?= e(url($lang . '/')) ?>" class="btn-cta">
            <?= $isFa ? 'بازگشت به صفحه اصلی' : 'Back to Homepage' ?>
        </a>
        <a href="<?= e(url($lang . '/packages.php')) ?>" class="package-btn-wa">
            <?= $isFa ? 'مشاهده پکیج‌ها' : 'View Packages' ?>
        </a>
        <a href="<?= e(url($lang . '/contact.php')) ?>" class="package-btn-wa">
            <?= $isFa ? 'تماس با ما' : 'Contact Us' ?>
        </a>
    </div>
</section>
<?php
require __DIR__ . '/includes/public_footer.php';

