<?php
/**
 * Public single blog post (shared by EN + FA).
 * Calling file must define: $lang, $dir.
 * Reads ?slug= from the query string.
 */

declare(strict_types=1);

$activeNav = 'blog';
$isFa = ($lang === 'fa');

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = $slug !== '' ? get_post_by_slug($slug) : null;

// Language switch should return to the same post.
$currentPage = 'post.php?slug=' . urlencode($slug);

$backLabel = $isFa ? '→ بازگشت به وبلاگ' : '← Back to blog';

if (!$post) {
    http_response_code(404);
    $pageTitle = ($isFa ? 'یافت نشد' : 'Not found') . ' — ' . SITE_NAME;
    require __DIR__ . '/public_header.php';
    echo '<section class="page"><h1>' . ($isFa ? 'مطلب یافت نشد' : 'Post not found') . '</h1>';
    echo '<p class="muted"><a href="' . e(url($lang . '/blog.php')) . '">' . e($backLabel) . '</a></p></section>';
    require __DIR__ . '/public_footer.php';
    return;
}

$title = lang_field($post, 'title', $lang);
$content = lang_field($post, 'content', $lang);
$cat = $isFa ? ($post['cat_fa'] ?? '') : ($post['cat_en'] ?? '');
$date = date('d M Y', strtotime($post['published_at'] ?? $post['created_at']));

$pageTitle = $title . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<article class="page post">
    <p class="muted"><a href="<?= e(url($lang . '/blog.php')) ?>"><?= e($backLabel) ?></a></p>

    <div class="blog-meta">
        <?php if ($cat !== ''): ?><span class="blog-cat"><?= e($cat) ?></span> · <?php endif; ?>
        <time><?= e($date) ?></time>
    </div>

    <h1 class="post-title"><?= e($title) ?></h1>

    <?php if (!empty($post['featured_image'])): ?>
        <img class="post-hero" src="<?= e(url($post['featured_image'])) ?>" alt="<?= e($title) ?>">
    <?php endif; ?>

    <?php if ($content !== ''): ?>
        <div class="content post-content"><?= $content /* HTML from admin editor */ ?></div>
    <?php endif; ?>
</article>
<?php
require __DIR__ . '/public_footer.php';
