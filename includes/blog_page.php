<?php
/**
 * Public Blog list (shared by EN + FA).
 * Clean, light, proportional layout.
 */

declare(strict_types=1);

require_once __DIR__ . '/page_cms.php';

$activeNav = 'blog';
$currentPage = 'blog.php';
$isFa = ($lang === 'fa');

$pageRow = get_page('blog');
$sp = get_subpage_fields('blog');
$posts = get_published_posts();
$categories = get_blog_categories();

$heading = $pageRow ? lang_field($pageRow, 'title', $lang) : ($isFa ? 'وبلاگ و مقالات آموزشی' : 'Blog & Articles');
$introContent = $pageRow ? lang_field($pageRow, 'content', $lang) : '';

$seo = get_subpage_seo($pageRow, $sp, $lang);
$metaDescription = $seo['description'];
$metaKeywords    = $seo['keywords'];
$ogImage         = $seo['og_image'];
$robotsMeta      = $seo['robots'];

$emptyMsg = $isFa ? 'هنوز مطلبی منتشر نشده است.' : 'No posts published yet.';
$readMore = $isFa ? 'مطالعه مقاله ←' : 'Read Article →';
$allCatsLabel = $isFa ? 'همه موضوعات' : 'All Topics';

$selectedCat = trim((string) ($_GET['cat'] ?? ''));

$pageTitle = $heading . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/subpages.css')) ?>">

<div class="sp-page-wrap">
    <?php render_subpage_hero($lang, $sp); ?>

    <section class="sp-section">
        <div class="sp-container">
            <?php if (trim(strip_tags($introContent)) !== ''): ?>
                <div class="sp-intro-rich content"><?= $introContent ?></div>
            <?php endif; ?>

            <?php if ($categories): ?>
                <div class="sp-filter-bar">
                    <a href="<?= e(url($lang . '/blog.php')) ?>" class="sp-filter-pill <?= $selectedCat === '' ? 'is-active' : '' ?>"><?= e($allCatsLabel) ?></a>
                    <?php foreach ($categories as $c): ?>
                        <?php $cSlug = (string) $c['slug']; ?>
                        <a href="<?= e(url($lang . '/blog.php?cat=' . urlencode($cSlug))) ?>" class="sp-filter-pill <?= $selectedCat === $cSlug ? 'is-active' : '' ?>">
                            <?= e(lang_field($c, 'name', $lang)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php
            $filteredPosts = $posts;
            if ($selectedCat !== '') {
                $filteredPosts = array_values(array_filter($posts, static function (array $p) use ($selectedCat, $categories): bool {
                    foreach ($categories as $c) {
                        if ($c['slug'] === $selectedCat && (int) ($p['category_id'] ?? 0) === (int) $c['id']) {
                            return true;
                        }
                    }
                    return false;
                }));
            }
            ?>

            <?php if (!$filteredPosts): ?>
                <p class="muted" style="text-align:center;"><?= e($emptyMsg) ?></p>
            <?php else: ?>
                <div class="sp-blog-grid">
                    <?php foreach ($filteredPosts as $p): ?>
                        <?php
                        $title = lang_field($p, 'title', $lang);
                        $excerpt = lang_field($p, 'excerpt', $lang);
                        $cat = $isFa ? ($p['cat_fa'] ?? '') : ($p['cat_en'] ?? '');
                        $date = date('d M Y', strtotime($p['published_at'] ?? $p['created_at']));
                        $link = url($lang . '/post.php?slug=' . urlencode($p['slug']));
                        $img = !empty($p['featured_image']) ? url($p['featured_image']) : url('assets/images/sparring-boxers.jpg');
                        ?>
                        <article class="sp-blog-card">
                            <a href="<?= e($link) ?>" class="sp-blog-thumb">
                                <img src="<?= e($img) ?>" alt="<?= e($title) ?>" loading="lazy">
                                <?php if ($cat !== ''): ?>
                                    <span class="sp-blog-badge"><?= e($cat) ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="sp-blog-body">
                                <time class="sp-blog-date"><?= e($date) ?></time>
                                <h2 class="sp-blog-title"><a href="<?= e($link) ?>"><?= e($title) ?></a></h2>
                                <?php if ($excerpt !== ''): ?>
                                    <p class="sp-blog-excerpt"><?= e($excerpt) ?></p>
                                <?php endif; ?>
                                <a href="<?= e($link) ?>" class="sp-blog-link"><?= e($readMore) ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php render_subpage_cta($lang, $sp); ?>
</div>

<?php
require __DIR__ . '/public_footer.php';
