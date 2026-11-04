<?php
/**
 * Admin — Website Pages & Builders Hub.
 * Organized into 3 clearly framed, balanced sections:
 *   Section 01: Main Landing Page (Homepage Studio)
 *   Section 02: Public Subpage Builders & Per-Page SEO (6 Structured Cards in a 2-Column Equal Grid)
 *   Section 03: Global SEO, Sitemap & Search Engine Configuration
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/page_cms.php';

require_admin();
$admin = current_admin();

ensure_subpages_seeded();

$pages = db()->query(
    "SELECT id, slug, title_en, title_fa, meta_description_en, meta_description_fa, is_active, updated_at
     FROM pages
     ORDER BY FIELD(slug, 'home', 'about', 'services', 'packages', 'gallery', 'blog', 'contact'), id"
)->fetchAll();

$subpageRows = [];
foreach ($pages as $p) {
    if ($p['slug'] !== 'home') {
        $subpageRows[] = $p;
    }
}

$pageMeta = [
    'about' => [
        'num'      => '01',
        'label_en' => 'About Coach Mori',
        'label_fa' => 'درباره مربی موری',
        'pills'    => ['Showcase Hero', 'Portrait & Story', '4 Stats Strip', '3 EFT Pillars', 'Bottom CTA'],
        'url_en'   => 'en/about.php',
        'url_fa'   => 'fa/about.php',
    ],
    'services' => [
        'num'      => '02',
        'label_en' => 'Coaching Services',
        'label_fa' => 'خدمات مربی‌گری',
        'pills'    => ['Showcase Hero', '4 Service Cards', '3-Step Process', 'Bottom CTA'],
        'url_en'   => 'en/services.php',
        'url_fa'   => 'fa/services.php',
    ],
    'packages' => [
        'num'      => '03',
        'label_en' => 'Packages & Pricing',
        'label_fa' => 'پکیج‌ها و تعرفه‌ها',
        'pills'    => ['Showcase Hero', 'Pricing Cards Grid', 'FAQ Accordion', 'Bottom CTA'],
        'url_en'   => 'en/packages.php',
        'url_fa'   => 'fa/packages.php',
        'extra_url'=> 'admin/packages.php',
        'extra_lbl'=> 'Pricing Cards',
    ],
    'gallery' => [
        'num'      => '04',
        'label_en' => 'Gallery & Transformations',
        'label_fa' => 'گالری و نتایج شاگردان',
        'pills'    => ['Showcase Hero', 'Transformations', 'Photo Archive', 'Bottom CTA'],
        'url_en'   => 'en/gallery.php',
        'url_fa'   => 'fa/gallery.php',
        'extra_url'=> 'admin/gallery.php',
        'extra_lbl'=> 'Photos',
    ],
    'blog' => [
        'num'      => '05',
        'label_en' => 'Blog & Articles',
        'label_fa' => 'وبلاگ و مقالات آموزشی',
        'pills'    => ['Showcase Hero', 'Topic Filter Bar', 'Articles Grid', 'Bottom CTA'],
        'url_en'   => 'en/blog.php',
        'url_fa'   => 'fa/blog.php',
        'extra_url'=> 'admin/blog.php',
        'extra_lbl'=> 'Articles',
    ],
    'contact' => [
        'num'      => '06',
        'label_en' => 'Contact & Booking',
        'label_fa' => 'تماس و ثبت‌نام تمرین',
        'pills'    => ['Showcase Hero', 'Direct Channels & WhatsApp', 'Location & Hours', 'Enquiry Form'],
        'url_en'   => 'en/contact.php',
        'url_fa'   => 'fa/contact.php',
    ],
];

$pageHeading = 'Website Pages & Builders';
require __DIR__ . '/_header.php';
?>

<!-- =========================================================================
     SECTION 01: MAIN LANDING PAGE (HOMEPAGE BUILDER)
     ========================================================================= -->
<section class="adm-section-group">
    <div class="adm-group-header">
        <div class="adm-group-title-wrap">
            <span class="adm-group-num">Section 01</span>
            <div>
                <h2 class="adm-group-title">Main Landing Page — Homepage Builder</h2>
                <p class="adm-group-desc">Your main editorial homepage (<code>/en/</code> &amp; <code>/fa/</code>) is managed in the dedicated Homepage Builder.</p>
            </div>
        </div>
        <div class="adm-group-actions">
            <a class="btn btn-ghost btn-sm" href="<?= e(url('en/')) ?>" target="_blank">EN Live ↗</a>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('fa/')) ?>" target="_blank">FA Live ↗</a>
            <a class="btn btn-primary btn-inline btn-sm" href="<?= e(url('admin/homepage.php')) ?>">Open Homepage Builder →</a>
        </div>
    </div>

    <div class="adm-home-banner-box">
        <div class="adm-home-banner-main">
            <div class="adm-home-badge-col">
                <span class="adm-home-icon-box">HOME</span>
            </div>
            <div class="adm-home-copy-col">
                <div class="adm-home-title-row">
                    <strong>Homepage — EARN IT.</strong>
                    <span class="adm-home-title-fa" dir="rtl">صفحه اصلی وب‌سایت</span>
                    <span class="badge badge-on">Primary Landing Page</span>
                </div>
                <p>Includes 6 customizable sections: Hero Banner &amp; Highlights, Back to Basics, Boxing &amp; Sparring, Free Consultation Calendar &amp; Chat, Coach Bio &amp; Quotes, and Client Transformations.</p>
                <div class="adm-quick-tabs-row">
                    <a class="adm-tab-chip" href="<?= e(url('admin/homepage.php?tab=hero')) ?>">1. Hero &amp; Highlights</a>
                    <a class="adm-tab-chip" href="<?= e(url('admin/homepage.php?tab=basics')) ?>">2. Back to Basics</a>
                    <a class="adm-tab-chip" href="<?= e(url('admin/homepage.php?tab=sparring')) ?>">3. Sparring &amp; Boxing</a>
                    <a class="adm-tab-chip" href="<?= e(url('admin/homepage.php?tab=calendar')) ?>">4. Booking &amp; Chat</a>
                    <a class="adm-tab-chip" href="<?= e(url('admin/homepage.php?tab=bio')) ?>">5. Coach Bio</a>
                    <a class="adm-tab-chip" href="<?= e(url('admin/homepage.php?tab=gallery')) ?>">6. Transformations</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 02: PUBLIC SUBPAGE BUILDERS & PER-PAGE SEO (6 PAGES)
     ========================================================================= -->
<section class="adm-section-group accent-blue">
    <div class="adm-group-header">
        <div class="adm-group-title-wrap">
            <span class="adm-group-num">Section 02</span>
            <div>
                <h2 class="adm-group-title">Public Subpage Builders &amp; Per-Page SEO (6 Pages)</h2>
                <p class="adm-group-desc">Each subpage is framed with a structured builder for its Hero Banner, Content Sections, Bottom CTA, and Google SEO.</p>
            </div>
        </div>
        <span class="badge badge-on">6 Active Subpages</span>
    </div>

    <div class="adm-subpages-grid">
        <?php foreach ($subpageRows as $idx => $p): ?>
            <?php
            $slug = (string) $p['slug'];
            $meta = $pageMeta[$slug] ?? [
                'num'      => sprintf('%02d', $idx + 1),
                'label_en' => ucfirst($slug),
                'label_fa' => $p['title_fa'] ?: ucfirst($slug),
                'pills'    => ['Hero Banner', 'Page Content', 'Bottom CTA'],
                'url_en'   => 'en/' . $slug . '.php',
                'url_fa'   => 'fa/' . $slug . '.php',
            ];
            $sp = get_subpage_fields($slug);
            $seoDescEn = trim((string) ($p['meta_description_en'] ?? '')) ?: sp_field($sp, 'hero_subtitle', 'en');
            $hasCustomSeo = trim((string) ($p['meta_description_en'] ?? '')) !== '';
            ?>
            <article class="adm-pcard">
                <!-- 1. Card Top Header Bar -->
                <div class="adm-pcard-head">
                    <div class="adm-pcard-head-left">
                        <span class="adm-pcard-num"><?= e($meta['num']) ?></span>
                        <div class="adm-pcard-titles">
                            <h3><?= e($meta['label_en']) ?></h3>
                            <span dir="rtl"><?= e($p['title_fa'] !== '' ? $p['title_fa'] : $meta['label_fa']) ?></span>
                        </div>
                    </div>
                    <div class="adm-pcard-head-right">
                        <code class="adm-pcard-slug">/<?= e($slug) ?>.php</code>
                        <?php if ((int) $p['is_active'] === 1): ?>
                            <span class="badge badge-on">Active</span>
                        <?php else: ?>
                            <span class="badge badge-off">Hidden</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Card Body: Builder Sections & SEO Snippet -->
                <div class="adm-pcard-body">
                    <div class="adm-pcard-block">
                        <div class="adm-pcard-label">Included Builder Sections</div>
                        <div class="adm-page-pills">
                            <?php foreach ($meta['pills'] as $pill): ?>
                                <span class="adm-sec-pill"><?= e($pill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="adm-pcard-seo">
                        <div class="adm-pcard-seo-top">
                            <span>Google SEO Description</span>
                            <strong class="<?= $hasCustomSeo ? 'is-custom' : 'is-auto' ?>">
                                <?= $hasCustomSeo ? '✓ Custom SEO' : '● Auto SEO' ?>
                            </strong>
                        </div>
                        <p class="adm-pcard-seo-text"><?= e($seoDescEn !== '' ? $seoDescEn : 'No meta description configured yet.') ?></p>
                    </div>
                </div>

                <!-- 3. Card Footer Toolbar -->
                <div class="adm-pcard-foot">
                    <div class="adm-pcard-foot-main">
                        <a class="btn btn-primary btn-inline btn-sm" href="<?= e(url('admin/page-edit.php?slug=' . urlencode($slug))) ?>">Page Builder</a>
                        <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/page-edit.php?slug=' . urlencode($slug) . '&tab=seo')) ?>">SEO Settings</a>
                        <?php if (!empty($meta['extra_url'])): ?>
                            <a class="btn btn-ghost btn-sm" href="<?= e(url($meta['extra_url'])) ?>"><?= e($meta['extra_lbl']) ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="adm-pcard-foot-links">
                        <a class="adm-live-pill" href="<?= e(url($meta['url_en'])) ?>" target="_blank" title="View English Page">EN ↗</a>
                        <a class="adm-live-pill" href="<?= e(url($meta['url_fa'])) ?>" target="_blank" title="View Persian Page">FA ↗</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- =========================================================================
     SECTION 03: GLOBAL SEO, SITEMAP & SEARCH ENGINE CONFIGURATION
     ========================================================================= -->
<section class="adm-section-group accent-indigo">
    <div class="adm-group-header" style="margin-bottom:0; padding-bottom:0; border-bottom:none;">
        <div class="adm-group-title-wrap">
            <span class="adm-group-num">Section 03</span>
            <div>
                <h2 class="adm-group-title">Global SEO, Sitemap &amp; Search Engine Configuration</h2>
                <p class="adm-group-desc">Default meta descriptions, keywords, OpenGraph image, Google Search Console verification, and dynamic XML sitemap.</p>
            </div>
        </div>
        <div class="adm-group-actions">
            <a class="btn btn-ghost btn-sm" href="<?= e(url('sitemap.xml')) ?>" target="_blank">Sitemap.xml ↗</a>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('robots.txt')) ?>" target="_blank">Robots.txt ↗</a>
            <a class="btn btn-primary btn-inline btn-sm" href="<?= e(url('admin/settings.php#seo-settings')) ?>">Global SEO Settings →</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
