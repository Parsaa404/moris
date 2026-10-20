<?php
/**
 * Public Packages page (shared by EN + FA).
 * Calling file must define: $lang, $dir.
 */

declare(strict_types=1);

$activeNav = 'packages';
$currentPage = 'packages.php';
$isFa = ($lang === 'fa');

$packages = get_active_packages();

$heading = $isFa ? 'پکیج‌ها' : 'Packages';
$intro = $isFa
    ? 'برنامه‌های تمرینی و قیمت‌ها. برای شروع با من تماس بگیرید.'
    : 'Training programs and pricing. Get in touch to start.';
$emptyMsg = $isFa ? 'در حال حاضر پکیجی موجود نیست.' : 'No packages available right now.';
$sessionsWord = $isFa ? 'جلسه' : 'sessions';
$minWord = $isFa ? 'دقیقه' : 'min';

$pageTitle = $heading . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<section class="page">
    <h1><?= e($heading) ?></h1>
    <p class="tagline"><?= e($intro) ?></p>

    <?php if (!$packages): ?>
        <p class="muted"><?= e($emptyMsg) ?></p>
    <?php else: ?>
        <div class="package-grid">
            <?php foreach ($packages as $p): ?>
                <?php
                $name = lang_field($p, 'name', $lang);
                $desc = lang_field($p, 'description', $lang);
                $features = parse_features($lang === 'fa' && trim((string) $p['features_fa']) !== ''
                    ? $p['features_fa']
                    : $p['features_en']);
                ?>
                <article class="package-card">
                    <h2 class="package-name"><?= e($name) ?></h2>

                    <div class="package-price"><?= e(format_price((float) $p['price'], $p['currency'])) ?></div>

                    <?php if ($p['sessions_count'] !== null): ?>
                        <div class="package-meta">
                            <?= (int) $p['sessions_count'] ?> <?= e($sessionsWord) ?>
                            <?php if ($p['session_duration_min'] !== null): ?>
                                · <?= (int) $p['session_duration_min'] ?> <?= e($minWord) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($desc !== ''): ?>
                        <div class="package-desc"><?= $desc /* HTML from admin editor */ ?></div>
                    <?php endif; ?>

                    <?php if ($features): ?>
                        <ul class="package-features">
                            <?php foreach ($features as $f): ?>
                                <li><?= e($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php
require __DIR__ . '/public_footer.php';
