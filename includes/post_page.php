<?php
/**
 * Public single blog post (shared by EN + FA).
 * Clean, light article layout.
 */

declare(strict_types=1);

require_once __DIR__ . '/page_cms.php';

$activeNav = 'blog';
$isFa = ($lang === 'fa');

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = $slug !== '' ? get_post_by_slug($slug) : null;
$sp = get_subpage_fields('blog');

// Language switch should return to the same post.
$currentPage = 'post.php?slug=' . urlencode($slug);

$backLabel = $isFa ? '→ بازگشت به مقالات وبلاگ' : '← Back to All Articles';

if (!$post) {
    http_response_code(404);
    $pageTitle = ($isFa ? 'یافت نشد' : 'Not found') . ' — ' . SITE_NAME;
    require __DIR__ . '/public_header.php';
    echo '<link rel="stylesheet" href="' . e(url('assets/css/subpages.css')) . '">';
    echo '<div class="sp-page-wrap">';
    render_subpage_hero($lang, $sp);
    echo '<section class="sp-section"><div class="sp-container" style="text-align:center;"><a class="sp-btn sp-btn-primary" href="' . e(url($lang . '/blog.php')) . '">' . e($backLabel) . '</a></div></section>';
    echo '</div>';
    require __DIR__ . '/public_footer.php';
    return;
}

$title = lang_field($post, 'title', $lang);
$content = lang_field($post, 'content', $lang);
$metaDescription = lang_field($post, 'excerpt', $lang);
$ogImage = !empty($post['featured_image']) ? $post['featured_image'] : 'assets/images/hero-earn-it.webp';
$ogType = 'article';
$cat = $isFa ? ($post['cat_fa'] ?? '') : ($post['cat_en'] ?? '');
$date = date('d M Y', strtotime($post['published_at'] ?? $post['created_at']));

$pageTitle = $title . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/subpages.css')) ?>">

<div class="sp-page-wrap">
    <section class="sp-hero">
        <div class="sp-container">
            <div class="sp-hero-grid is-centered">
                <div class="sp-hero-copy">
                    <span class="sp-eyebrow"><?= e($cat !== '' ? $cat : ($isFa ? 'مقاله تخصصی' : 'ARTICLE')) ?> · <?= e($date) ?></span>
                    <h1 class="sp-hero-title"><?= e($title) ?></h1>
                    <?php if ($metaDescription !== ''): ?>
                        <p class="sp-hero-sub"><?= e($metaDescription) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="sp-section">
        <div class="sp-container" style="max-width: 760px;">
            <div style="margin-bottom: 1.25rem;">
                <a href="<?= e(url($lang . '/blog.php')) ?>" class="sp-blog-link"><?= e($backLabel) ?></a>
            </div>

            <?php if (!empty($post['featured_image'])): ?>
                <div style="margin-bottom: 1.5rem; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                    <img src="<?= e(url($post['featured_image'])) ?>" alt="<?= e($title) ?>" style="width:100%; max-height:400px; object-fit:cover; display:block;">
                </div>
            <?php endif; ?>

            <?php if ($content !== ''): ?>
                <div class="content post-content" style="font-size:0.95rem; line-height:1.8; color:#1e293b;">
                    <?= $content /* HTML from admin editor */ ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php render_subpage_cta($lang, $sp); ?>
</div>

<?php
require __DIR__ . '/public_footer.php';
