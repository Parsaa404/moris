<?php
/**
 * Public Gallery page (shared by EN + FA).
 * Calling file must define: $lang, $dir.
 * Masonry grid + category filter + lightbox.
 */

declare(strict_types=1);

$activeNav = 'gallery';
$currentPage = 'gallery.php';
$isFa = ($lang === 'fa');

$categories = get_gallery_categories(true);
$items = get_gallery_items(true);

// Map category id => localized name.
$catName = [];
foreach ($categories as $c) {
    $catName[(int) $c['id']] = lang_field($c, 'name', $lang);
}

$heading = $isFa ? 'گالری' : 'Gallery';
$allLabel = $isFa ? 'همه' : 'All';
$emptyMsg = $isFa ? 'هنوز تصویری اضافه نشده است.' : 'No photos yet.';

$pageTitle = $heading . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<section class="page">
    <h1><?= e($heading) ?></h1>

    <?php if (!$items): ?>
        <p class="muted"><?= e($emptyMsg) ?></p>
    <?php else: ?>
        <?php if ($categories): ?>
            <div class="gallery-filters">
                <button type="button" class="filter-btn is-active" data-filter="all"><?= e($allLabel) ?></button>
                <?php foreach ($categories as $c): ?>
                    <button type="button" class="filter-btn" data-filter="cat-<?= (int) $c['id'] ?>">
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
                    <a href="<?= e($src) ?>" class="gallery-link"
                       data-title="<?= e($title) ?>">
                        <img src="<?= e($src) ?>" alt="<?= e($title) ?>" loading="lazy">
                        <?php if ($title !== ''): ?>
                            <figcaption><?= e($title) ?></figcaption>
                        <?php endif; ?>
                    </a>
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

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
