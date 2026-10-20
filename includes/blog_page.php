<?php
/**
 * Public Blog list (shared by EN + FA).
 * Calling file must define: $lang, $dir.
 */

declare(strict_types=1);

$activeNav = 'blog';
$currentPage = 'blog.php';
$isFa = ($lang === 'fa');

$posts = get_published_posts();

$heading = $isFa ? 'وبلاگ' : 'Blog';
$emptyMsg = $isFa ? 'هنوز مطلبی منتشر نشده است.' : 'No posts published yet.';
$readMore = $isFa ? 'ادامه مطلب' : 'Read more';

$pageTitle = $heading . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<section class="page">
    <h1><?= e($heading) ?></h1>

    <?php if (!$posts): ?>
        <p class="muted"><?= e($emptyMsg) ?></p>
    <?php else: ?>
        <div class="blog-list">
            <?php foreach ($posts as $p): ?>
                <?php
                $title = lang_field($p, 'title', $lang);
                $excerpt = lang_field($p, 'excerpt', $lang);
                $cat = $isFa ? ($p['cat_fa'] ?? '') : ($p['cat_en'] ?? '');
                $date = date('d M Y', strtotime($p['published_at'] ?? $p['created_at']));
                $link = url($lang . '/post.php?slug=' . urlencode($p['slug']));
                ?>
                <article class="blog-card">
                    <?php if (!empty($p['featured_image'])): ?>
                        <a class="blog-card-img" href="<?= e($link) ?>">
                            <img src="<?= e(url($p['featured_image'])) ?>" alt="<?= e($title) ?>" loading="lazy">
                        </a>
                    <?php endif; ?>
                    <div class="blog-card-body">
                        <div class="blog-meta">
                            <?php if ($cat !== ''): ?><span class="blog-cat"><?= e($cat) ?></span> · <?php endif; ?>
                            <time><?= e($date) ?></time>
                        </div>
                        <h2 class="blog-card-title"><a href="<?= e($link) ?>"><?= e($title) ?></a></h2>
                        <?php if ($excerpt !== ''): ?>
                            <p class="blog-excerpt"><?= e($excerpt) ?></p>
                        <?php endif; ?>
                        <a class="blog-readmore" href="<?= e($link) ?>"><?= e($readMore) ?> →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php
require __DIR__ . '/public_footer.php';
