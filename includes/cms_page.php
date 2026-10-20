<?php
/**
 * Generic public CMS page renderer (About, Services).
 * The calling file must define BEFORE including this:
 *   $lang, $dir, $slug, $activeNav, $currentPage
 */

declare(strict_types=1);

$page = get_page($slug);
$title = $page ? lang_field($page, 'title', $lang) : '';
$content = $page ? lang_field($page, 'content', $lang) : '';

$pageTitle = ($title !== '' ? $title : SITE_NAME) . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<article class="page">
    <?php if ($title !== ''): ?>
        <h1><?= e($title) ?></h1>
    <?php endif; ?>

    <?php if ($content !== ''): ?>
        <div class="content"><?= $content /* HTML from admin editor */ ?></div>
    <?php elseif (!$page): ?>
        <p class="muted">
            <?= $lang === 'fa'
                ? 'این صفحه هنوز فعال نیست.'
                : 'This page is not available yet.' ?>
        </p>
    <?php endif; ?>
</article>
<?php
require __DIR__ . '/public_footer.php';
