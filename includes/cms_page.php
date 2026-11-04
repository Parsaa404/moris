<?php
/**
 * Clean, Light Public CMS Page Renderer (About & Services).
 * Reads all structured fields and SEO settings from `page_cms.php`.
 * The calling file defines: $lang, $dir, $slug, $activeNav, $currentPage
 */

declare(strict_types=1);

require_once __DIR__ . '/page_cms.php';

$pageSlug = $slug;
$page = get_page($pageSlug);
$sp = get_subpage_fields($pageSlug);
$isFa = ($lang === 'fa');

$title = $page ? lang_field($page, 'title', $lang) : sp_field($sp, 'hero_title', $lang, ucfirst($pageSlug));
$content = $page ? lang_field($page, 'content', $lang) : '';

$seo = get_subpage_seo($page, $sp, $lang);
$metaDescription = $seo['description'];
$metaKeywords    = $seo['keywords'];
$ogImage         = $seo['og_image'];
$robotsMeta      = $seo['robots'];

$pageTitle = ($title !== '' ? $title : SITE_NAME) . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/subpages.css')) ?>">

<div class="sp-page-wrap">
    <?php render_subpage_hero($lang, $sp, $pageSlug); ?>

    <?php if ($pageSlug === 'about'):
        $storyEyebrow = sp_field($sp, 'story_eyebrow', $lang);
        $storyTitle   = sp_field($sp, 'story_title', $lang);
        $storyImg     = sp_field($sp, 'story_image', 'en');
        $storyLead    = sp_field($sp, 'story_lead', $lang);
        $storyBody    = sp_field($sp, 'story_body', $lang);
        $paragraphs   = array_values(array_filter(preg_split('/\r\n\r\n|\n\n/', trim($storyBody)) ?: [], static fn($p) => trim((string) $p) !== ''));
        $stats        = array_values(array_filter(sp_json($sp, 'stats_json'), static function (array $st) use ($isFa): bool {
            $n = trim((string) ($isFa ? ($st['num_fa'] ?? $st['num_en'] ?? '') : ($st['num_en'] ?? '')));
            $l = trim((string) ($isFa ? ($st['label_fa'] ?? $st['label_en'] ?? '') : ($st['label_en'] ?? '')));
            return $n !== '' || $l !== '';
        }));
        $pillarsTitle = sp_field($sp, 'pillars_title', $lang);
        $pillarsSub   = sp_field($sp, 'pillars_sub', $lang);
        $pillars      = array_values(array_filter(sp_json($sp, 'pillars_json'), static function (array $pl) use ($isFa): bool {
            $t = trim((string) ($isFa ? ($pl['title_fa'] ?? $pl['title_en'] ?? '') : ($pl['title_en'] ?? '')));
            $d = trim((string) ($isFa ? ($pl['desc_fa'] ?? $pl['desc_en'] ?? '') : ($pl['desc_en'] ?? '')));
            return $t !== '' || $d !== '';
        }));
        $hasStoryCopy = ($storyEyebrow !== '' || $storyTitle !== '' || $storyLead !== '' || !empty($paragraphs) || trim(strip_tags($content)) !== '');
    ?>
        <!-- Story & Bio Split Section (Light & Clean) -->
        <?php if ($hasStoryCopy || $storyImg !== ''): ?>
        <section class="sp-section">
            <div class="sp-container">
                <div class="sp-about-split"<?= ($storyImg === '' || !$hasStoryCopy) ? ' style="grid-template-columns:1fr;"' : '' ?>>
                    <?php if ($storyImg !== ''): ?>
                    <div class="sp-about-photo">
                        <img src="<?= e(url($storyImg)) ?>" alt="<?= e($title) ?>" loading="lazy">
                    </div>
                    <?php endif; ?>
                    <?php if ($hasStoryCopy): ?>
                    <div class="sp-about-copy">
                        <?php if ($storyEyebrow !== ''): ?>
                            <span class="sp-eyebrow"><?= e($storyEyebrow) ?></span>
                        <?php endif; ?>
                        <?php if ($storyTitle !== ''): ?>
                            <h2><?= e($storyTitle) ?></h2>
                        <?php endif; ?>
                        <?php if ($storyLead !== ''): ?>
                            <p class="sp-about-lead"><?= e($storyLead) ?></p>
                        <?php endif; ?>
                        <div class="sp-about-narrative">
                            <?php foreach ($paragraphs as $pText): ?>
                                <p><?= nl2br(e(trim($pText))) ?></p>
                            <?php endforeach; ?>
                            <?php if (trim(strip_tags($content)) !== ''): ?>
                                <div class="content"><?= $content ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Light Stats Strip -->
        <?php if ($stats): ?>
        <section class="sp-section-alt">
            <div class="sp-container">
                <div class="sp-stats-grid">
                    <?php foreach ($stats as $st):
                        $sNum = trim((string) ($isFa ? ($st['num_fa'] ?? $st['num_en'] ?? '') : ($st['num_en'] ?? '')));
                        $sLbl = trim((string) ($isFa ? ($st['label_fa'] ?? $st['label_en'] ?? '') : ($st['label_en'] ?? '')));
                    ?>
                        <div class="sp-stat-card">
                            <?php if ($sNum !== ''): ?><div class="sp-stat-num"><?= e($sNum) ?></div><?php endif; ?>
                            <?php if ($sLbl !== ''): ?><div class="sp-stat-lbl"><?= e($sLbl) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- EFT Pillars Section -->
        <?php if ($pillars): ?>
        <section class="sp-section">
            <div class="sp-container">
                <?php if ($pillarsTitle !== '' || $pillarsSub !== ''): ?>
                <div class="sp-sec-head">
                    <?php if ($pillarsTitle !== ''): ?>
                        <h2 class="sp-sec-title"><?= e($pillarsTitle) ?></h2>
                    <?php endif; ?>
                    <?php if ($pillarsSub !== ''): ?>
                        <p class="sp-sec-sub"><?= e($pillarsSub) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="sp-pillars-grid">
                    <?php foreach ($pillars as $pl):
                        $pIcon  = $pl['icon'] ?? 'award';
                        $pTitle = trim((string) ($isFa ? ($pl['title_fa'] ?? $pl['title_en'] ?? '') : ($pl['title_en'] ?? '')));
                        $pDesc  = trim((string) ($isFa ? ($pl['desc_fa'] ?? $pl['desc_en'] ?? '') : ($pl['desc_en'] ?? '')));
                    ?>
                        <article class="sp-pillar-card">
                            <div class="sp-pillar-icon"><?= render_highlight_icon($pIcon) ?></div>
                            <?php if ($pTitle !== ''): ?><h3><?= e($pTitle) ?></h3><?php endif; ?>
                            <?php if ($pDesc !== ''): ?><p><?= e($pDesc) ?></p><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

    <?php elseif ($pageSlug === 'services'):
        $introEyebrow = sp_field($sp, 'intro_eyebrow', $lang);
        $introTitle   = sp_field($sp, 'intro_title', $lang);
        $introSub     = sp_field($sp, 'intro_sub', $lang);
        $servicesList = array_values(array_filter(sp_json($sp, 'services_json'), static function (array $srv) use ($isFa): bool {
            $t = trim((string) ($isFa ? ($srv['title_fa'] ?? $srv['title_en'] ?? '') : ($srv['title_en'] ?? '')));
            $d = trim((string) ($isFa ? ($srv['desc_fa'] ?? $srv['desc_en'] ?? '') : ($srv['desc_en'] ?? '')));
            return $t !== '' || $d !== '';
        }));
        $stepsTitle   = sp_field($sp, 'steps_title', $lang);
        $stepsSub     = sp_field($sp, 'steps_sub', $lang);
        $stepsList    = array_values(array_filter(sp_json($sp, 'steps_json'), static function (array $step) use ($isFa): bool {
            $t = trim((string) ($isFa ? ($step['title_fa'] ?? $step['title_en'] ?? '') : ($step['title_en'] ?? '')));
            $d = trim((string) ($isFa ? ($step['desc_fa'] ?? $step['desc_en'] ?? '') : ($step['desc_en'] ?? '')));
            return $t !== '' || $d !== '';
        }));
        $bookLabel    = $isFa ? 'رزرو یا سوال درباره این دوره' : 'Inquire About This Program';
    ?>
        <!-- Services Alternating Showcase Cards -->
        <?php if ($introEyebrow !== '' || $introTitle !== '' || $introSub !== '' || !empty($servicesList) || trim(strip_tags($content)) !== ''): ?>
        <section class="sp-section">
            <div class="sp-container">
                <?php if ($introEyebrow !== '' || $introTitle !== '' || $introSub !== ''): ?>
                <div class="sp-sec-head">
                    <?php if ($introEyebrow !== ''): ?>
                        <span class="sp-eyebrow"><?= e($introEyebrow) ?></span>
                    <?php endif; ?>
                    <?php if ($introTitle !== ''): ?>
                        <h2 class="sp-sec-title"><?= e($introTitle) ?></h2>
                    <?php endif; ?>
                    <?php if ($introSub !== ''): ?>
                        <p class="sp-sec-sub"><?= e($introSub) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($servicesList): ?>
                <div class="sp-services-stack">
                    <?php foreach ($servicesList as $idx => $srv):
                        $sImg   = trim((string) ($srv['image'] ?? ''));
                        $sTag   = trim((string) ($isFa ? ($srv['tag_fa'] ?? $srv['tag_en'] ?? '') : ($srv['tag_en'] ?? '')));
                        $sTitle = trim((string) ($isFa ? ($srv['title_fa'] ?? $srv['title_en'] ?? '') : ($srv['title_en'] ?? '')));
                        $sDesc  = trim((string) ($isFa ? ($srv['desc_fa'] ?? $srv['desc_en'] ?? '') : ($srv['desc_en'] ?? '')));
                        $sBuls  = parse_features($isFa ? ($srv['bullets_fa'] ?? $srv['bullets_en'] ?? '') : ($srv['bullets_en'] ?? ''));
                        $inquireUrl = url($lang . '/contact.php?package=' . rawurlencode($sTitle));
                    ?>
                        <article class="sp-service-card <?= ($idx % 2 === 1) ? 'is-reversed' : '' ?>"<?= $sImg === '' ? ' style="grid-template-columns:1fr;"' : '' ?>>
                            <?php if ($sImg !== ''): ?>
                            <div class="sp-service-img">
                                <img src="<?= e(url($sImg)) ?>" alt="<?= e($sTitle) ?>" loading="lazy">
                            </div>
                            <?php endif; ?>
                            <div class="sp-service-body">
                                <?php if ($sTag !== ''): ?>
                                    <span class="sp-eyebrow"><?= e($sTag) ?></span>
                                <?php endif; ?>
                                <?php if ($sTitle !== ''): ?>
                                    <h3><?= e($sTitle) ?></h3>
                                <?php endif; ?>
                                <?php if ($sDesc !== ''): ?>
                                    <p class="sp-service-desc"><?= e($sDesc) ?></p>
                                <?php endif; ?>
                                <?php if ($sBuls): ?>
                                    <ul class="sp-check-list">
                                        <?php foreach ($sBuls as $b): ?>
                                            <li>
                                                <span class="sp-check-mark">✓</span>
                                                <span><?= e($b) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <a href="<?= e($inquireUrl) ?>" class="sp-btn sp-btn-primary"><?= e($bookLabel) ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (trim(strip_tags($content)) !== ''): ?>
                    <div class="sp-intro-rich content" style="margin-top:2rem;"><?= $content ?></div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- 3-Step Coaching Process (Light) -->
        <?php if ($stepsList): ?>
        <section class="sp-section-alt">
            <div class="sp-container">
                <?php if ($stepsTitle !== '' || $stepsSub !== ''): ?>
                <div class="sp-sec-head">
                    <?php if ($stepsTitle !== ''): ?>
                        <h2 class="sp-sec-title"><?= e($stepsTitle) ?></h2>
                    <?php endif; ?>
                    <?php if ($stepsSub !== ''): ?>
                        <p class="sp-sec-sub"><?= e($stepsSub) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="sp-steps-grid">
                    <?php foreach ($stepsList as $stIdx => $step):
                        $num     = $step['step'] ?? sprintf('%02d', $stIdx + 1);
                        $stTitle = trim((string) ($isFa ? ($step['title_fa'] ?? $step['title_en'] ?? '') : ($step['title_en'] ?? '')));
                        $stDesc  = trim((string) ($isFa ? ($step['desc_fa'] ?? $step['desc_en'] ?? '') : ($step['desc_en'] ?? '')));
                    ?>
                        <div class="sp-step-card">
                            <span class="sp-step-num"><?= e($num) ?></span>
                            <?php if ($stTitle !== ''): ?><h3><?= e($stTitle) ?></h3><?php endif; ?>
                            <?php if ($stDesc !== ''): ?><p><?= e($stDesc) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

    <?php else: ?>
        <section class="sp-section">
            <div class="sp-container">
                <?php if ($content !== ''): ?>
                    <div class="content"><?= $content ?></div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php render_subpage_cta($lang, $sp); ?>
</div>

<?php
require __DIR__ . '/public_footer.php';
