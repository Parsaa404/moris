<?php
/**
 * English home — content loaded from the database (admin-editable).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$lang = 'en';
$dir = 'ltr';
$activeNav = 'home';
$currentPage = '';

$page = get_page('home');
$title = $page ? lang_field($page, 'title', $lang) : SITE_NAME;
$content = $page ? lang_field($page, 'content', $lang) : '';
$tagline = setting('tagline_en', '');
$packages = get_active_packages();

$heroImage = setting('hero_image', '');
$heroClass = 'hero' . ($heroImage !== '' ? ' hero--image' : '');
$heroStyle = $heroImage !== '' ? ' style="background-image:url(\'' . e(url($heroImage)) . '\')"' : '';

$pageTitle = ($title !== '' ? $title : SITE_NAME) . ' — ' . SITE_NAME;

require dirname(__DIR__) . '/includes/public_header.php';
?>
<section class="<?= $heroClass ?>"<?= $heroStyle ?>>
    <div class="hero-inner">
        <?php if ($tagline !== ''): ?>
            <span class="eyebrow"><?= e($tagline) ?></span>
        <?php endif; ?>
        <?php if ($title !== ''): ?>
            <h1 class="hero-title"><?= e($title) ?></h1>
        <?php endif; ?>

        <?php if ($content !== ''): ?>
            <div class="content hero-content"><?= $content /* HTML from admin editor */ ?></div>
        <?php else: ?>
            <p class="muted">No content yet. Add it in the admin panel → Pages → Home.</p>
        <?php endif; ?>

        <p class="cta-row">
            <a class="btn-cta" href="<?= e(url($lang . '/packages.php')) ?>">View packages</a>
            <a class="btn-outline" href="<?= e(url($lang . '/contact.php')) ?>">Book a session</a>
        </p>
    </div>
</section>

<?php if ($packages): ?>
<section class="home-packages">
    <h2 class="section-title">Packages</h2>
    <div class="package-grid">
        <?php foreach (array_slice($packages, 0, 3) as $p): ?>
            <a class="package-card package-card-link" href="<?= e(url($lang . '/packages.php')) ?>">
                <h3 class="package-name"><?= e(lang_field($p, 'name', $lang)) ?></h3>
                <div class="package-price"><?= e(format_price((float) $p['price'], $p['currency'])) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<?php
require dirname(__DIR__) . '/includes/public_footer.php';
