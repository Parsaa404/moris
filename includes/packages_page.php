<?php
/**
 * Public Packages page (shared by EN + FA).
 * Clean, light, proportional layout.
 */

declare(strict_types=1);

require_once __DIR__ . '/page_cms.php';

$activeNav = 'packages';
$currentPage = 'packages.php';
$isFa = ($lang === 'fa');

$pageRow = get_page('packages');
$sp = get_subpage_fields('packages');
$packages = get_active_packages();

$heading = $pageRow ? lang_field($pageRow, 'title', $lang) : ($isFa ? 'پکیج‌ها و تعرفه‌ها' : 'Packages & Pricing');
$introContent = $pageRow ? lang_field($pageRow, 'content', $lang) : '';

$seo = get_subpage_seo($pageRow, $sp, $lang);
$metaDescription = $seo['description'];
$metaKeywords    = $seo['keywords'];
$ogImage         = $seo['og_image'];
$robotsMeta      = $seo['robots'];

$emptyMsg = $isFa ? 'در حال حاضر پکیجی موجود نیست.' : 'No packages available right now.';
$sessionsWord = $isFa ? 'جلسه' : 'sessions';
$minWord = $isFa ? 'دقیقه' : 'min';
$selectBtn = $isFa ? 'انتخاب و ثبت‌نام پکیج' : 'Select & Book Package';
$waBtn = $isFa ? 'مشاوره در واتساپ' : 'Ask on WhatsApp';

$rawWa = preg_replace('/[^0-9]/', '', (string) setting('contact_whatsapp', ''));
$waNum = strlen($rawWa) > 4 ? $rawWa : '60123456789';

$pageTitle = $heading . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/subpages.css')) ?>">

<div class="sp-page-wrap">
    <?php render_subpage_hero($lang, $sp); ?>

    <section class="sp-section">
        <div class="sp-container">
            <?php if (trim(strip_tags($introContent)) !== ''): ?>
                <div class="sp-intro-rich content">
                    <?= $introContent ?>
                </div>
            <?php endif; ?>

            <?php if (!$packages): ?>
                <p class="muted" style="text-align:center;"><?= e($emptyMsg) ?></p>
            <?php else: ?>
                <div class="sp-packages-grid">
                    <?php foreach ($packages as $idx => $p): ?>
                        <?php
                        $name = lang_field($p, 'name', $lang);
                        $desc = lang_field($p, 'description', $lang);
                        $priceFormatted = format_price((float) $p['price'], $p['currency']);
                        $features = parse_features($lang === 'fa' && trim((string) $p['features_fa']) !== ''
                            ? $p['features_fa']
                            : $p['features_en']);
                        $contactUrl = url($lang . '/contact.php?package=' . rawurlencode($name . ' (' . $priceFormatted . ')'));
                        $waText = rawurlencode($isFa
                            ? "سلام مربی موری، درباره پکیج «{$name}» ({$priceFormatted}) سوال داشتم."
                            : "Hi Coach Mori, I'm interested in the \"{$name}\" package ({$priceFormatted}).");

                        $nameCheck = strtolower((string) ($p['name_en'] ?? $name));
                        $catTag = $isFa ? 'کوچینگ تخصصی' : 'Personal Training';
                        if (str_contains($nameCheck, 'box')) {
                            $catTag = $isFa ? 'بوکس و کاردیو' : 'Boxing & Conditioning';
                        } elseif (str_contains($nameCheck, 'weight') || str_contains($nameCheck, 'strength')) {
                            $catTag = $isFa ? 'بدنسازی و قدرت' : 'Strength & Weights';
                        } elseif (str_contains($nameCheck, 'online')) {
                            $catTag = $isFa ? 'کوچینگ آنلاین' : 'Online Coaching';
                        }
                        $isPopular = ($idx === 1);
                        ?>
                        <article class="sp-pkg-card <?= $isPopular ? 'is-featured' : '' ?>">
                            <div class="sp-pkg-top">
                                <span class="sp-pkg-tag"><?= e($catTag) ?></span>
                                <?php if ($isPopular): ?>
                                    <span class="sp-pkg-pop"><?= $isFa ? 'پیشنهاد ویژه' : 'Popular' ?></span>
                                <?php endif; ?>
                            </div>

                            <h2 class="sp-pkg-title"><?= e($name) ?></h2>

                            <div class="sp-pkg-price-row">
                                <span class="sp-pkg-price"><?= e($priceFormatted) ?></span>
                                <?php if ($p['sessions_count'] !== null): ?>
                                    <span class="sp-pkg-meta">
                                        / <?= (int) $p['sessions_count'] ?> <?= e($sessionsWord) ?>
                                        <?php if ($p['session_duration_min'] !== null): ?>
                                            (<?= (int) $p['session_duration_min'] ?> <?= e($minWord) ?>)
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if ($desc !== ''): ?>
                                <div class="sp-pkg-desc"><?= $desc ?></div>
                            <?php endif; ?>

                            <?php if ($features): ?>
                                <ul class="sp-pkg-features">
                                    <?php foreach ($features as $f): ?>
                                        <li>
                                            <span class="sp-check-mark">✓</span>
                                            <span><?= e($f) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <div class="sp-pkg-actions">
                                <a href="<?= e($contactUrl) ?>" class="sp-btn sp-btn-primary sp-btn-block"><?= e($selectBtn) ?></a>
                                <a href="https://wa.me/<?= e($waNum) ?>?text=<?= $waText ?>" target="_blank" rel="noopener" class="sp-btn sp-btn-outline sp-btn-block"><?= e($waBtn) ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php
    $faqs = array_values(array_filter(sp_json($sp, 'faq_json'), static function (array $faq) use ($isFa): bool {
        $q = trim((string) ($isFa ? ($faq['q_fa'] ?? $faq['q_en'] ?? '') : ($faq['q_en'] ?? '')));
        $a = trim((string) ($isFa ? ($faq['a_fa'] ?? $faq['a_en'] ?? '') : ($faq['a_en'] ?? '')));
        return $q !== '' && $a !== '';
    }));
    $faqTitle = sp_field($sp, 'faq_title', $lang);
    $faqSub = sp_field($sp, 'faq_sub', $lang);
    if ($faqs):
    ?>
    <section class="sp-section-alt">
        <div class="sp-container">
            <?php if ($faqTitle !== '' || $faqSub !== ''): ?>
            <div class="sp-sec-head">
                <span class="sp-eyebrow"><?= $isFa ? 'پاسخ به سوالات' : 'FAQ' ?></span>
                <?php if ($faqTitle !== ''): ?>
                    <h2 class="sp-sec-title"><?= e($faqTitle) ?></h2>
                <?php endif; ?>
                <?php if ($faqSub !== ''): ?>
                    <p class="sp-sec-sub"><?= e($faqSub) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="sp-faq-list">
                <?php foreach ($faqs as $faq):
                    $q = trim((string) ($isFa ? ($faq['q_fa'] ?? $faq['q_en'] ?? '') : ($faq['q_en'] ?? '')));
                    $a = trim((string) ($isFa ? ($faq['a_fa'] ?? $faq['a_en'] ?? '') : ($faq['a_en'] ?? '')));
                ?>
                    <details class="sp-faq-item">
                        <summary class="sp-faq-q">
                            <span><?= e($q) ?></span>
                            <span class="sp-faq-plus">+</span>
                        </summary>
                        <div class="sp-faq-a">
                            <p><?= e($a) ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php render_subpage_cta($lang, $sp); ?>
</div>
<?php
require __DIR__ . '/public_footer.php';
