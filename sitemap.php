<?php
/**
 * Dynamic XML Sitemap (Bilingual EN + FA with hreflang alternates and blog posts).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

header('Content-Type: application/xml; charset=UTF-8');

$scheme = is_https() ? 'https' : 'http';
$rawHost = $_SERVER['HTTP_HOST'] ?? 'morispt.com';
$safeHost = preg_replace('/[^a-zA-Z0-9.\-:]/', '', $rawHost) ?: 'morispt.com';
$origin = $scheme . '://' . $safeHost;

$routes = [
    ''             => ['priority' => '1.0', 'changefreq' => 'weekly'],
    'about.php'    => ['priority' => '0.8', 'changefreq' => 'monthly'],
    'services.php' => ['priority' => '0.8', 'changefreq' => 'monthly'],
    'packages.php' => ['priority' => '0.9', 'changefreq' => 'weekly'],
    'gallery.php'  => ['priority' => '0.8', 'changefreq' => 'weekly'],
    'blog.php'     => ['priority' => '0.8', 'changefreq' => 'weekly'],
    'contact.php'  => ['priority' => '0.8', 'changefreq' => 'monthly'],
];

$posts = get_published_posts();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($routes as $file => $meta):
    $enUrl = $origin . url('en/' . $file);
    $faUrl = $origin . url('fa/' . $file);
?>
    <url>
        <loc><?= e($enUrl) ?></loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= e($enUrl) ?>"/>
        <xhtml:link rel="alternate" hreflang="fa" href="<?= e($faUrl) ?>"/>
        <changefreq><?= e($meta['changefreq']) ?></changefreq>
        <priority><?= e($meta['priority']) ?></priority>
    </url>
    <url>
        <loc><?= e($faUrl) ?></loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= e($enUrl) ?>"/>
        <xhtml:link rel="alternate" hreflang="fa" href="<?= e($faUrl) ?>"/>
        <changefreq><?= e($meta['changefreq']) ?></changefreq>
        <priority><?= e($meta['priority']) ?></priority>
    </url>
<?php endforeach; ?>
<?php foreach ($posts as $post):
    $slug = urlencode((string) $post['slug']);
    $enPostUrl = $origin . url('en/post.php?slug=' . $slug);
    $faPostUrl = $origin . url('fa/post.php?slug=' . $slug);
    $lastmod = date('Y-m-d', strtotime((string) ($post['updated_at'] ?? $post['published_at'] ?? $post['created_at'])));
?>
    <url>
        <loc><?= e($enPostUrl) ?></loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= e($enPostUrl) ?>"/>
        <xhtml:link rel="alternate" hreflang="fa" href="<?= e($faPostUrl) ?>"/>
        <lastmod><?= e($lastmod) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?= e($faPostUrl) ?></loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= e($enPostUrl) ?>"/>
        <xhtml:link rel="alternate" hreflang="fa" href="<?= e($faPostUrl) ?>"/>
        <lastmod><?= e($lastmod) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endforeach; ?>
</urlset>

