<?php
/**
 * Public Gallery page (shared by EN + FA).
 * Clean, light layout with Client Transformations + Masonry photo grid + lightbox.
 */

declare(strict_types=1);

require_once __DIR__ . '/page_cms.php';

$activeNav = 'gallery';
$currentPage = 'gallery.php';
$isFa = ($lang === 'fa');

$pageRow = get_page('gallery');
$sp = get_subpage_fields('gallery');
$hc = get_homepage_content();

$rawStories = sp_json($sp, 'stories_json');
if (empty($rawStories) && !isset($sp['stories_json'])) {
    $rawStories = hp_json($hc, 'gallery', 'items');
}
$transformations = array_values(array_filter($rawStories, static function (array $item) use ($isFa): bool {
    $n = trim((string) ($isFa ? ($item['name_fa'] ?? $item['name_en'] ?? '') : ($item['name_en'] ?? '')));
    $q = trim((string) ($isFa ? ($item['quote_fa'] ?? $item['quote_en'] ?? '') : ($item['quote_en'] ?? '')));
    return $n !== '' || $q !== '';
}));

$archiveEyebrow = sp_field($sp, 'archive_eyebrow', $lang);
$archiveTitle   = sp_field($sp, 'archive_title', $lang);
$archiveSub     = sp_field($sp, 'archive_sub', $lang);

$storiesEyebrow = sp_field($sp, 'stories_eyebrow', $lang);
$storiesTitle   = sp_field($sp, 'stories_title', $lang);
$storiesSub     = sp_field($sp, 'stories_sub', $lang);

$categories = get_gallery_categories(true);
$items = get_gallery_items(true);

$heading = $pageRow ? lang_field($pageRow, 'title', $lang) : ($isFa ? 'گالری و نتایج شاگردان' : 'Gallery & Transformations');
$introContent = $pageRow ? lang_field($pageRow, 'content', $lang) : '';

$seo = get_subpage_seo($pageRow, $sp, $lang);
$metaDescription = $seo['description'];
$metaKeywords    = $seo['keywords'];
$ogImage         = $seo['og_image'];
$robotsMeta      = $seo['robots'];

$allLabel = $isFa ? 'همه تصاویر' : 'All Photos';
$emptyMsg = $isFa ? 'هنوز تصویری اضافه نشده است.' : 'No photos yet.';

$pageTitle = $heading . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/subpages.css')) ?>">

<div class="sp-page-wrap">
    <?php render_subpage_hero($lang, $sp); ?>

    <section class="sp-section">
        <div class="sp-container">
            <?php if ($archiveEyebrow !== '' || $archiveTitle !== '' || $archiveSub !== '' || trim(strip_tags($introContent)) !== ''): ?>
            <div class="sp-sec-head">
                <?php if ($archiveEyebrow !== ''): ?>
                    <span class="sp-eyebrow"><?= e($archiveEyebrow) ?></span>
                <?php endif; ?>
                <?php if ($archiveTitle !== ''): ?>
                    <h2 class="sp-sec-title"><?= e($archiveTitle) ?></h2>
                <?php endif; ?>
                <?php if ($archiveSub !== ''): ?>
                    <p class="sp-sec-sub"><?= e($archiveSub) ?></p>
                <?php endif; ?>
                <?php if (trim(strip_tags($introContent)) !== ''): ?>
                    <div class="sp-intro-rich content"><?= $introContent ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!$items): ?>
                <p class="muted" style="text-align:center;"><?= e($emptyMsg) ?></p>
            <?php else: ?>
                <?php if ($categories): ?>
                    <div class="gallery-filters sp-filter-bar">
                        <button type="button" class="filter-btn sp-filter-pill is-active" data-filter="all"><?= e($allLabel) ?></button>
                        <?php foreach ($categories as $c): ?>
                            <button type="button" class="filter-btn sp-filter-pill" data-filter="cat-<?= (int) $c['id'] ?>">
                                <?= e(lang_field($c, 'name', $lang)) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="gallery-masonry" id="gallery">
                    <?php foreach ($items as $it): ?>
                        <?php
                        $title = lang_field($it, 'title', $lang);
                        $catClass = $it['category_id'] !== null ? 'cat-' . (int) $it['category_id'] : 'cat-none';
                        $src = url($it['image_path']);
                        ?>
                        <figure class="gallery-item <?= e($catClass) ?>">
                            <a href="<?= e($src) ?>" class="gallery-link" data-title="<?= e($title) ?>">
                                <img src="<?= e($src) ?>" alt="<?= e($title) ?>" loading="lazy">
                                <?php if ($title !== ''): ?>
                                    <figcaption><?= e($title) ?></figcaption>
                                <?php endif; ?>
                            </a>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($transformations): ?>
    <section class="sp-section-alt">
        <div class="sp-container">
            <?php if ($storiesEyebrow !== '' || $storiesTitle !== '' || $storiesSub !== ''): ?>
            <div class="sp-sec-head">
                <?php if ($storiesEyebrow !== ''): ?>
                    <span class="sp-eyebrow"><?= e($storiesEyebrow) ?></span>
                <?php endif; ?>
                <?php if ($storiesTitle !== ''): ?>
                    <h2 class="sp-sec-title"><?= e($storiesTitle) ?></h2>
                <?php endif; ?>
                <?php if ($storiesSub !== ''): ?>
                    <p class="sp-sec-sub"><?= e($storiesSub) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="sp-transforms-grid">
                <?php foreach ($transformations as $item):
                    $img    = !empty($item['image']) ? url($item['image']) : '';
                    $cName  = trim((string) ($isFa ? ($item['name_fa'] ?? $item['name_en'] ?? '') : ($item['name_en'] ?? '')));
                    $cTag   = trim((string) ($isFa ? ($item['tag_fa'] ?? $item['tag_en'] ?? '') : ($item['tag_en'] ?? '')));
                    $cQuote = trim((string) ($isFa ? ($item['quote_fa'] ?? $item['quote_en'] ?? '') : ($item['quote_en'] ?? '')));
                ?>
                    <article class="sp-transform-card">
                        <?php if ($img !== ''): ?>
                        <div class="sp-transform-img">
                            <img src="<?= e($img) ?>" alt="<?= e($cName) ?>" loading="lazy">
                        </div>
                        <?php endif; ?>
                        <div class="sp-transform-body">
                            <div class="sp-transform-head">
                                <?php if ($cName !== ''): ?>
                                    <h3 class="sp-transform-name"><?= e($cName) ?></h3>
                                <?php endif; ?>
                                <?php if ($cTag !== ''): ?>
                                    <span class="sp-transform-tag"><?= e($cTag) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($cQuote !== ''): ?>
                                <p class="sp-transform-quote"><?= e($cQuote) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php render_subpage_cta($lang, $sp); ?>
</div>

<!-- Lightbox overlay -->
<div class="lightbox" id="lightbox" hidden>
    <button class="lightbox-close" id="lightbox-close" aria-label="Close">&times;</button>
    <button class="lightbox-nav lightbox-prev" id="lightbox-prev" aria-label="Previous">&#10094;</button>
    <figure class="lightbox-content">
        <img src="" alt="" id="lightbox-img">
        <figcaption id="lightbox-caption"></figcaption>
    </figure>
    <button class="lightbox-nav lightbox-next" id="lightbox-next" aria-label="Next">&#10095;</button>
</div>

<script src="<?= e(url('assets/js/gallery.js')) ?>"></script>
<?php
require __DIR__ . '/public_footer.php';
