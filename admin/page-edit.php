<?php
/**
 * Admin — Subpage Studio Builder (About, Services, Packages, Gallery, Blog, Contact).
 * Provides full bilingual editing of Hero Banners, Page-Specific Repeaters & Sections,
 * Bottom Call-to-Action Banners, Media Library Picker, and SEO Meta Settings.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/page_cms.php';
require_once dirname(__DIR__) . '/includes/homepage_cms.php';

require_admin();
$admin = current_admin();

ensure_subpages_seeded();

$slug = trim((string) ($_GET['slug'] ?? $_POST['slug'] ?? ''));
if ($slug === 'home') {
    redirect('admin/homepage.php');
}

$labels = [
    'about'    => 'About Coach Mori',
    'services' => 'Services',
    'packages' => 'Packages & Pricing',
    'gallery'  => 'Gallery & Transformations',
    'blog'     => 'Blog & Articles',
    'contact'  => 'Contact & Booking',
];

$stmt = db()->prepare('SELECT * FROM pages WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    flash_set('That page was not found.', 'error');
    redirect('admin/pages.php');
}

$sp = get_subpage_fields($slug);
$errors = [];

/**
 * Helper to upload or keep existing image path.
 */
function handle_sp_image(string $fileKey, string $fallbackPath, array &$errors): string
{
    if (!empty($_FILES[$fileKey]['name']) && ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $up = upload_image($_FILES[$fileKey], 'homepage');
        if ($up['ok']) {
            return $up['path'];
        }
        $errors[] = $up['error'] ?? 'Image upload failed.';
    }
    return trim($fallbackPath);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Security token invalid. Please try again.';
    } else {
        $title_en   = trim((string) ($_POST['title_en'] ?? ''));
        $title_fa   = trim((string) ($_POST['title_fa'] ?? ''));
        $content_en = sanitize_html((string) ($_POST['content_en'] ?? ''));
        $content_fa = sanitize_html((string) ($_POST['content_fa'] ?? ''));
        $meta_en    = trim((string) ($_POST['meta_description_en'] ?? ''));
        $meta_fa    = trim((string) ($_POST['meta_description_fa'] ?? ''));
        $is_active  = isset($_POST['is_active']) ? 1 : 0;

        if ($title_en === '') {
            $errors[] = 'English page title is required.';
        }

        $fieldsEn = [];
        $fieldsFa = [];

        $heroBg = handle_sp_image('hero_bg_file', (string) ($_POST['hero_bg'] ?? sp_field($sp, 'hero_bg', 'en', 'assets/images/hero-earn-it.webp')), $errors);
        $fieldsEn['hero_bg'] = $heroBg;
        $fieldsFa['hero_bg'] = $heroBg;

        $fieldsEn['hero_eyebrow']    = trim((string) ($_POST['hero_eyebrow_en'] ?? ''));
        $fieldsFa['hero_eyebrow']    = trim((string) ($_POST['hero_eyebrow_fa'] ?? ''));
        $fieldsEn['hero_title']      = trim((string) ($_POST['hero_title_en'] ?? ''));
        $fieldsFa['hero_title']      = trim((string) ($_POST['hero_title_fa'] ?? ''));
        $fieldsEn['hero_subtitle']   = trim((string) ($_POST['hero_subtitle_en'] ?? ''));
        $fieldsFa['hero_subtitle']   = trim((string) ($_POST['hero_subtitle_fa'] ?? ''));
        $fieldsEn['hero_chips']      = trim((string) ($_POST['hero_chips_en'] ?? ''));
        $fieldsFa['hero_chips']      = trim((string) ($_POST['hero_chips_fa'] ?? ''));
        $fieldsEn['hero_badge_num']  = trim((string) ($_POST['hero_badge_num_en'] ?? ''));
        $fieldsFa['hero_badge_num']  = trim((string) ($_POST['hero_badge_num_fa'] ?? ''));
        $fieldsEn['hero_badge_text'] = trim((string) ($_POST['hero_badge_text_en'] ?? ''));
        $fieldsFa['hero_badge_text'] = trim((string) ($_POST['hero_badge_text_fa'] ?? ''));
        $fieldsEn['hero_corner_tag'] = trim((string) ($_POST['hero_corner_tag_en'] ?? ''));
        $fieldsFa['hero_corner_tag'] = trim((string) ($_POST['hero_corner_tag_fa'] ?? ''));
        $fieldsEn['hero_btn1_text']  = trim((string) ($_POST['hero_btn1_text_en'] ?? ''));
        $fieldsFa['hero_btn1_text']  = trim((string) ($_POST['hero_btn1_text_fa'] ?? ''));
        $heroBtn1Url = trim((string) ($_POST['hero_btn1_url'] ?? 'packages.php'));
        $fieldsEn['hero_btn1_url']   = $heroBtn1Url;
        $fieldsFa['hero_btn1_url']   = $heroBtn1Url;
        $fieldsEn['hero_btn2_text']  = trim((string) ($_POST['hero_btn2_text_en'] ?? ''));
        $fieldsFa['hero_btn2_text']  = trim((string) ($_POST['hero_btn2_text_fa'] ?? ''));
        $heroBtn2Url = trim((string) ($_POST['hero_btn2_url'] ?? 'contact.php'));
        $fieldsEn['hero_btn2_url']   = $heroBtn2Url;
        $fieldsFa['hero_btn2_url']   = $heroBtn2Url;
        $fieldsEn['hero_index']      = trim((string) ($_POST['hero_index_en'] ?? ''));
        $fieldsFa['hero_index']      = trim((string) ($_POST['hero_index_fa'] ?? ''));
        $heroWatermark = trim((string) ($_POST['hero_watermark'] ?? strtoupper($slug)));
        $fieldsEn['hero_watermark']  = $heroWatermark;
        $fieldsFa['hero_watermark']  = $heroWatermark;

        $fieldsEn['cta_title']    = trim((string) ($_POST['cta_title_en'] ?? ''));
        $fieldsFa['cta_title']    = trim((string) ($_POST['cta_title_fa'] ?? ''));
        $fieldsEn['cta_sub']      = trim((string) ($_POST['cta_sub_en'] ?? ''));
        $fieldsFa['cta_sub']      = trim((string) ($_POST['cta_sub_fa'] ?? ''));
        $fieldsEn['cta_btn_text'] = trim((string) ($_POST['cta_btn_text_en'] ?? ''));
        $fieldsFa['cta_btn_text'] = trim((string) ($_POST['cta_btn_text_fa'] ?? ''));
        $ctaUrl = trim((string) ($_POST['cta_btn_url'] ?? 'contact.php'));
        $fieldsEn['cta_btn_url']  = $ctaUrl;
        $fieldsFa['cta_btn_url']  = $ctaUrl;

        if ($slug === 'about') {
            $storyImg = handle_sp_image('story_image_file', (string) ($_POST['story_image'] ?? sp_field($sp, 'story_image', 'en', 'assets/images/coach-mori.jpg')), $errors);
            $fieldsEn['story_image']   = $storyImg;
            $fieldsFa['story_image']   = $storyImg;
            $fieldsEn['story_eyebrow'] = trim((string) ($_POST['story_eyebrow_en'] ?? ''));
            $fieldsFa['story_eyebrow'] = trim((string) ($_POST['story_eyebrow_fa'] ?? ''));
            $fieldsEn['story_title']   = trim((string) ($_POST['story_title_en'] ?? ''));
            $fieldsFa['story_title']   = trim((string) ($_POST['story_title_fa'] ?? ''));
            $fieldsEn['story_lead']    = trim((string) ($_POST['story_lead_en'] ?? ''));
            $fieldsFa['story_lead']    = trim((string) ($_POST['story_lead_fa'] ?? ''));
            $fieldsEn['story_body']    = trim((string) ($_POST['story_body_en'] ?? ''));
            $fieldsFa['story_body']    = trim((string) ($_POST['story_body_fa'] ?? ''));

            $fieldsEn['pillars_title'] = trim((string) ($_POST['pillars_title_en'] ?? ''));
            $fieldsFa['pillars_title'] = trim((string) ($_POST['pillars_title_fa'] ?? ''));
            $fieldsEn['pillars_sub']   = trim((string) ($_POST['pillars_sub_en'] ?? ''));
            $fieldsFa['pillars_sub']   = trim((string) ($_POST['pillars_sub_fa'] ?? ''));

            $stats = [];
            $rawStats = $_POST['stats'] ?? [];
            if (is_array($rawStats)) {
                foreach ($rawStats as $st) {
                    $nEn = trim((string) ($st['num_en'] ?? ''));
                    $lEn = trim((string) ($st['label_en'] ?? ''));
                    if ($nEn !== '' || $lEn !== '') {
                        $stats[] = [
                            'num_en'   => $nEn,
                            'num_fa'   => trim((string) ($st['num_fa'] ?? $nEn)),
                            'label_en' => $lEn,
                            'label_fa' => trim((string) ($st['label_fa'] ?? $lEn)),
                        ];
                    }
                }
            }
            $statsJson = json_encode($stats, JSON_UNESCAPED_UNICODE);
            $fieldsEn['stats_json'] = $statsJson;
            $fieldsFa['stats_json'] = $statsJson;

            $pillars = [];
            $rawPillars = $_POST['pillars'] ?? [];
            if (is_array($rawPillars)) {
                foreach ($rawPillars as $pl) {
                    $tEn = trim((string) ($pl['title_en'] ?? ''));
                    $dEn = trim((string) ($pl['desc_en'] ?? ''));
                    if ($tEn !== '' || $dEn !== '') {
                        $pillars[] = [
                            'icon'     => trim((string) ($pl['icon'] ?? 'award')),
                            'title_en' => $tEn,
                            'title_fa' => trim((string) ($pl['title_fa'] ?? $tEn)),
                            'desc_en'  => $dEn,
                            'desc_fa'  => trim((string) ($pl['desc_fa'] ?? $dEn)),
                        ];
                    }
                }
            }
            $pillarsJson = json_encode($pillars, JSON_UNESCAPED_UNICODE);
            $fieldsEn['pillars_json'] = $pillarsJson;
            $fieldsFa['pillars_json'] = $pillarsJson;

        } elseif ($slug === 'services') {
            $fieldsEn['intro_eyebrow'] = trim((string) ($_POST['intro_eyebrow_en'] ?? ''));
            $fieldsFa['intro_eyebrow'] = trim((string) ($_POST['intro_eyebrow_fa'] ?? ''));
            $fieldsEn['intro_title']   = trim((string) ($_POST['intro_title_en'] ?? ''));
            $fieldsFa['intro_title']   = trim((string) ($_POST['intro_title_fa'] ?? ''));
            $fieldsEn['intro_sub']     = trim((string) ($_POST['intro_sub_en'] ?? ''));
            $fieldsFa['intro_sub']     = trim((string) ($_POST['intro_sub_fa'] ?? ''));

            $fieldsEn['steps_title']   = trim((string) ($_POST['steps_title_en'] ?? ''));
            $fieldsFa['steps_title']   = trim((string) ($_POST['steps_title_fa'] ?? ''));
            $fieldsEn['steps_sub']     = trim((string) ($_POST['steps_sub_en'] ?? ''));
            $fieldsFa['steps_sub']     = trim((string) ($_POST['steps_sub_fa'] ?? ''));

            $services = [];
            $rawServices = $_POST['services'] ?? [];
            if (is_array($rawServices)) {
                foreach ($rawServices as $idx => $srv) {
                    $tEn = trim((string) ($srv['title_en'] ?? ''));
                    $tFa = trim((string) ($srv['title_fa'] ?? ''));
                    if ($tEn !== '' || $tFa !== '') {
                        $img = handle_sp_image('service_img_file_' . $idx, (string) ($srv['image'] ?? 'assets/images/basics-gym.jpg'), $errors);
                        $services[] = [
                            'icon'       => trim((string) ($srv['icon'] ?? 'dumbbell')),
                            'image'      => $img,
                            'tag_en'     => trim((string) ($srv['tag_en'] ?? '')),
                            'tag_fa'     => trim((string) ($srv['tag_fa'] ?? '')),
                            'title_en'   => $tEn,
                            'title_fa'   => $tFa,
                            'desc_en'    => trim((string) ($srv['desc_en'] ?? '')),
                            'desc_fa'    => trim((string) ($srv['desc_fa'] ?? '')),
                            'bullets_en' => trim((string) ($srv['bullets_en'] ?? '')),
                            'bullets_fa' => trim((string) ($srv['bullets_fa'] ?? '')),
                        ];
                    }
                }
            }
            $svcJson = json_encode($services, JSON_UNESCAPED_UNICODE);
            $fieldsEn['services_json'] = $svcJson;
            $fieldsFa['services_json'] = $svcJson;

            $steps = [];
            $rawSteps = $_POST['steps'] ?? [];
            if (is_array($rawSteps)) {
                foreach ($rawSteps as $idx => $st) {
                    $tEn = trim((string) ($st['title_en'] ?? ''));
                    if ($tEn !== '') {
                        $steps[] = [
                            'step'     => sprintf('%02d', $idx + 1),
                            'title_en' => $tEn,
                            'title_fa' => trim((string) ($st['title_fa'] ?? $tEn)),
                            'desc_en'  => trim((string) ($st['desc_en'] ?? '')),
                            'desc_fa'  => trim((string) ($st['desc_fa'] ?? '')),
                        ];
                    }
                }
            }
            $stepsJson = json_encode($steps, JSON_UNESCAPED_UNICODE);
            $fieldsEn['steps_json'] = $stepsJson;
            $fieldsFa['steps_json'] = $stepsJson;

        } elseif ($slug === 'packages') {
            $fieldsEn['faq_title'] = trim((string) ($_POST['faq_title_en'] ?? ''));
            $fieldsFa['faq_title'] = trim((string) ($_POST['faq_title_fa'] ?? ''));
            $fieldsEn['faq_sub']   = trim((string) ($_POST['faq_sub_en'] ?? ''));
            $fieldsFa['faq_sub']   = trim((string) ($_POST['faq_sub_fa'] ?? ''));

            $faqs = [];
            $rawFaqs = $_POST['faqs'] ?? [];
            if (is_array($rawFaqs)) {
                foreach ($rawFaqs as $fq) {
                    $qEn = trim((string) ($fq['q_en'] ?? ''));
                    $qFa = trim((string) ($fq['q_fa'] ?? ''));
                    if ($qEn !== '' || $qFa !== '') {
                        $faqs[] = [
                            'q_en' => $qEn,
                            'q_fa' => $qFa,
                            'a_en' => trim((string) ($fq['a_en'] ?? '')),
                            'a_fa' => trim((string) ($fq['a_fa'] ?? '')),
                        ];
                    }
                }
            }
            $faqJson = json_encode($faqs, JSON_UNESCAPED_UNICODE);
            $fieldsEn['faq_json'] = $faqJson;
            $fieldsFa['faq_json'] = $faqJson;

        } elseif ($slug === 'gallery') {
            $fieldsEn['archive_eyebrow'] = trim((string) ($_POST['archive_eyebrow_en'] ?? ''));
            $fieldsFa['archive_eyebrow'] = trim((string) ($_POST['archive_eyebrow_fa'] ?? ''));
            $fieldsEn['archive_title']   = trim((string) ($_POST['archive_title_en'] ?? ''));
            $fieldsFa['archive_title']   = trim((string) ($_POST['archive_title_fa'] ?? ''));
            $fieldsEn['archive_sub']     = trim((string) ($_POST['archive_sub_en'] ?? ''));
            $fieldsFa['archive_sub']     = trim((string) ($_POST['archive_sub_fa'] ?? ''));

            $fieldsEn['stories_eyebrow'] = trim((string) ($_POST['stories_eyebrow_en'] ?? ''));
            $fieldsFa['stories_eyebrow'] = trim((string) ($_POST['stories_eyebrow_fa'] ?? ''));
            $fieldsEn['stories_title']   = trim((string) ($_POST['stories_title_en'] ?? ''));
            $fieldsFa['stories_title']   = trim((string) ($_POST['stories_title_fa'] ?? ''));
            $fieldsEn['stories_sub']     = trim((string) ($_POST['stories_sub_en'] ?? ''));
            $fieldsFa['stories_sub']     = trim((string) ($_POST['stories_sub_fa'] ?? ''));

            $stories = [];
            $rawStories = $_POST['stories'] ?? [];
            if (is_array($rawStories)) {
                foreach ($rawStories as $idx => $st) {
                    $nEn = trim((string) ($st['name_en'] ?? ''));
                    $nFa = trim((string) ($st['name_fa'] ?? ''));
                    $qEn = trim((string) ($st['quote_en'] ?? ''));
                    $qFa = trim((string) ($st['quote_fa'] ?? ''));
                    if ($nEn !== '' || $nFa !== '' || $qEn !== '' || $qFa !== '') {
                        $img = handle_sp_image('story_img_file_' . $idx, (string) ($st['image'] ?? ''), $errors);
                        $stories[] = [
                            'image'    => $img,
                            'name_en'  => $nEn,
                            'name_fa'  => $nFa !== '' ? $nFa : $nEn,
                            'tag_en'   => trim((string) ($st['tag_en'] ?? '')),
                            'tag_fa'   => trim((string) ($st['tag_fa'] ?? '')),
                            'quote_en' => $qEn,
                            'quote_fa' => $qFa !== '' ? $qFa : $qEn,
                        ];
                    }
                }
            }
            $storiesJson = json_encode($stories, JSON_UNESCAPED_UNICODE);
            $fieldsEn['stories_json'] = $storiesJson;
            $fieldsFa['stories_json'] = $storiesJson;

        } elseif ($slug === 'contact') {
            $fieldsEn['info_title']  = trim((string) ($_POST['info_title_en'] ?? ''));
            $fieldsFa['info_title']  = trim((string) ($_POST['info_title_fa'] ?? ''));
            $fieldsEn['info_sub']    = trim((string) ($_POST['info_sub_en'] ?? ''));
            $fieldsFa['info_sub']    = trim((string) ($_POST['info_sub_fa'] ?? ''));
            $fieldsEn['location_en'] = trim((string) ($_POST['location_en'] ?? ''));
            $fieldsFa['location_en'] = trim((string) ($_POST['location_fa'] ?? ''));
            $fieldsEn['hours_en']    = trim((string) ($_POST['hours_en'] ?? ''));
            $fieldsFa['hours_en']    = trim((string) ($_POST['hours_fa'] ?? ''));
        }

        // Per-page SEO fields
        $fieldsEn['seo_keywords'] = trim((string) ($_POST['seo_keywords_en'] ?? ''));
        $fieldsFa['seo_keywords'] = trim((string) ($_POST['seo_keywords_fa'] ?? ''));
        $ogImg = handle_sp_image('seo_og_file', (string) ($_POST['seo_og_image'] ?? sp_field($sp, 'seo_og_image', 'en', '')), $errors);
        $fieldsEn['seo_og_image'] = $ogImg;
        $fieldsFa['seo_og_image'] = $ogImg;
        $noIndexVal = isset($_POST['seo_noindex']) ? '1' : '0';
        $fieldsEn['seo_noindex']  = $noIndexVal;
        $fieldsFa['seo_noindex']  = $noIndexVal;

        if (!$errors) {
            $upd = db()->prepare(
                'UPDATE pages
                 SET title_en = ?, title_fa = ?, content_en = ?, content_fa = ?,
                     meta_description_en = ?, meta_description_fa = ?, is_active = ?
                 WHERE id = ?'
            );
            $upd->execute([
                $title_en, $title_fa, $content_en, $content_fa,
                $meta_en, $meta_fa, $is_active, $page['id'],
            ]);

            save_subpage_fields($slug, $fieldsEn, $fieldsFa);

            $activeTab = trim((string) ($_POST['active_tab'] ?? ''));
            $tabSuffix = $activeTab !== '' ? '&tab=' . urlencode($activeTab) : '';
            flash_set('“' . ($labels[$slug] ?? ucfirst($slug)) . '” Page Builder & SEO saved successfully!');
            redirect('admin/page-edit.php?slug=' . urlencode($slug) . $tabSuffix);
        }

        $page = array_merge($page, [
            'title_en' => $title_en, 'title_fa' => $title_fa,
            'content_en' => $content_en, 'content_fa' => $content_fa,
            'meta_description_en' => $meta_en, 'meta_description_fa' => $meta_fa,
            'is_active' => $is_active,
        ]);
        $sp = get_subpage_fields($slug);
    }
}

$initialTab = trim((string) ($_GET['tab'] ?? 'hero'));
if (!in_array($initialTab, ['hero', 'sections', 'cta', 'seo'], true)) {
    $initialTab = 'hero';
}

$mediaLibrary = get_media_library_images();
$pageLabel = $labels[$slug] ?? ucfirst($slug);
$pageHeading = 'Page Builder: ' . $pageLabel;
require __DIR__ . '/_header.php';
?>

<style>
.sp-builder-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-top: 3px solid #0d9488;
    color: #0f172a;
    padding: 1rem 1.35rem;
    border-radius: 12px;
    margin-bottom: 1.15rem;
}
.sp-tabs {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
    margin-bottom: 1.15rem;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 0.5rem;
}
.sp-tab-btn {
    padding: 0.5rem 1rem;
    border-radius: 7px;
    border: 1px solid transparent;
    background: transparent;
    color: #475569;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
}
.sp-tab-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.sp-tab-btn.is-active {
    background: #0d9488;
    color: #fff;
    border-color: #0d9488;
}
.sp-pane { display: none; }
.sp-pane.is-active { display: block; }
.sp-repeater-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 0.85rem;
}
.sp-img-preview-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.85rem;
    margin-bottom: 1rem;
}
.sp-img-thumb {
    width: 110px;
    height: 72px;
    object-fit: cover;
    border-radius: 8px;
    background: #0f172a;
    border: 1px solid #cbd5e1;
}
.sp-media-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.72);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}
.sp-media-modal.is-open { display: flex; }
.sp-media-box {
    background: #fff;
    border-radius: 14px;
    max-width: 820px;
    width: 100%;
    max-height: 82vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.sp-media-grid {
    padding: 1.25rem;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
    gap: 0.85rem;
}
.sp-media-item {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    background: #0f172a;
    transition: border-color 0.15s ease;
}
.sp-media-item:hover { border-color: #0abab5; }
.sp-media-item img { width: 100%; height: 95px; object-fit: cover; display: block; }
.sp-media-item span {
    display: block;
    background: #fff;
    font-size: 0.72rem;
    padding: 0.3rem 0.45rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<div class="sp-builder-top">
    <div>
        <a href="<?= e(url('admin/pages.php')) ?>" style="color:#0d9488; font-weight:600; text-decoration:none; font-size:0.82rem;">← Back to Pages &amp; Builders</a>
        <h2 style="margin:0.2rem 0 0; font-size:1.12rem; color:#0f172a;">Page Builder: <?= e($pageLabel) ?></h2>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('en/' . $slug . '.php')) ?>" target="_blank">View EN Live ↗</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('fa/' . $slug . '.php')) ?>" target="_blank">View FA Live ↗</a>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="sp-tabs">
    <button type="button" class="sp-tab-btn <?= $initialTab === 'hero' ? 'is-active' : '' ?>" data-tab="tab-hero" data-key="hero">1. Header Banner</button>
    <button type="button" class="sp-tab-btn <?= $initialTab === 'sections' ? 'is-active' : '' ?>" data-tab="tab-sections" data-key="sections">2. Page Sections &amp; Content</button>
    <button type="button" class="sp-tab-btn <?= $initialTab === 'cta' ? 'is-active' : '' ?>" data-tab="tab-cta" data-key="cta">3. Bottom Call-to-Action</button>
    <button type="button" class="sp-tab-btn <?= $initialTab === 'seo' ? 'is-active' : '' ?>" data-tab="tab-seo" data-key="seo">4. SEO &amp; Google Preview</button>
</div>

<form method="post" enctype="multipart/form-data" class="adm-section-group form" style="margin-top:0;">
    <?= csrf_field() ?>
    <input type="hidden" name="slug" value="<?= e($slug) ?>">
    <input type="hidden" name="active_tab" id="active_tab_input" value="<?= e($initialTab) ?>">

    <!-- ===================================================================
         TAB 1: HEADER BANNER (HERO SHOWCASE)
         =================================================================== -->
    <div class="sp-pane <?= $initialTab === 'hero' ? 'is-active' : '' ?>" id="tab-hero">
        <h3 style="margin-top:0;">Architectural Showcase Hero Banner</h3>
        <p class="muted">Customize every element of the Hero Section on the <strong><?= e($pageLabel) ?></strong> page: image, headline, checkmark chips, floating glass badge, and action buttons.</p>

        <?php $heroBgPath = sp_field($sp, 'hero_bg', 'en', 'assets/images/hero-earn-it.webp'); ?>
        <div class="sp-img-preview-row">
            <img src="<?= e(url($heroBgPath)) ?>" id="preview_hero_bg" class="sp-img-thumb" alt="Hero BG">
            <div style="flex:1; min-width:220px;">
                <label style="margin-bottom:0.35rem;">Hero Showcase Image Path
                    <input type="text" name="hero_bg" id="input_hero_bg" value="<?= e($heroBgPath) ?>">
                </label>
                <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                    <button type="button" class="btn btn-ghost btn-sm sp-open-media" data-target-input="input_hero_bg" data-target-preview="preview_hero_bg">Choose from Media Library</button>
                    <input type="file" name="hero_bg_file" accept="image/*" style="font-size:0.82rem;">
                </div>
            </div>
        </div>

        <div class="lang-cols">
            <div class="lang-col">
                <h2 class="lang-title">English Hero Content</h2>
                <label>Eyebrow Tag (Small uppercase pill)
                    <input type="text" name="hero_eyebrow_en" value="<?= e(sp_field($sp, 'hero_eyebrow', 'en')) ?>">
                </label>
                <label>Hero Main Headline
                    <input type="text" name="hero_title_en" value="<?= e(sp_field($sp, 'hero_title', 'en')) ?>">
                </label>
                <label>Hero Subtitle
                    <textarea name="hero_subtitle_en" rows="3"><?= e(sp_field($sp, 'hero_subtitle', 'en')) ?></textarea>
                </label>
                <label>Feature Highlight Chips (1 item per line)
                    <textarea name="hero_chips_en" rows="4" placeholder="13+ Years Ring &amp; Gym Experience&#10;EFT Coaching Methodology&#10;1-on-1 &amp; Worldwide Online"><?= e(sp_field($sp, 'hero_chips', 'en')) ?></textarea>
                </label>
            </div>
            <div class="lang-col">
                <h2 class="lang-title">محتوای هیرو فارسی (FA)</h2>
                <label>روتیتر بالای عنوان
                    <input type="text" name="hero_eyebrow_fa" value="<?= e(sp_field($sp, 'hero_eyebrow', 'fa')) ?>" dir="rtl">
                </label>
                <label>عنوان اصلی هدر
                    <input type="text" name="hero_title_fa" value="<?= e(sp_field($sp, 'hero_title', 'fa')) ?>" dir="rtl">
                </label>
                <label>زیرعنوان هدر
                    <textarea name="hero_subtitle_fa" rows="3" dir="rtl"><?= e(sp_field($sp, 'hero_subtitle', 'fa')) ?></textarea>
                </label>
                <label>تگ‌های ویژگی با آیکون تیک (هر خط یک مورد)
                    <textarea name="hero_chips_fa" rows="4" dir="rtl" placeholder="+۱۳ سال سابقه در رینگ و باشگاه&#10;متد اختصاصی EFT&#10;آموزش حضوری و کوچینگ آنلاین"><?= e(sp_field($sp, 'hero_chips', 'fa')) ?></textarea>
                </label>
            </div>
        </div>

        <hr style="margin:1.75rem 0; border:0; border-top:1px solid #e2e8f0;">
        <h4>Floating Glass Badge &amp; Image Corner Tag</h4>
        <p class="muted" style="margin-top:0;">This floating badge overlaps the bottom corner of the Hero photo (e.g. <strong>13+ Years — Pro Boxing &amp; Strength Coaching Experience</strong>).</p>

        <div class="lang-cols">
            <div class="lang-col">
                <label>Floating Badge Bold Title / Number (EN)
                    <input type="text" name="hero_badge_num_en" value="<?= e(sp_field($sp, 'hero_badge_num', 'en')) ?>" placeholder="e.g. 13+ Years or 4 Core Programs">
                </label>
                <label>Floating Badge Subtitle (EN)
                    <input type="text" name="hero_badge_text_en" value="<?= e(sp_field($sp, 'hero_badge_text', 'en')) ?>" placeholder="e.g. Pro Boxing &amp; Strength Coaching Experience">
                </label>
                <label>Top-Right Image Corner Tag (EN)
                    <input type="text" name="hero_corner_tag_en" value="<?= e(sp_field($sp, 'hero_corner_tag', 'en')) ?>" placeholder="e.g. KUALA LUMPUR &amp; ONLINE">
                </label>
            </div>
            <div class="lang-col" dir="rtl">
                <label>عنوان درشت کارت شناور روی عکس (FA)
                    <input type="text" name="hero_badge_num_fa" value="<?= e(sp_field($sp, 'hero_badge_num', 'fa')) ?>" placeholder="مثلاً: +۱۳ سال یا ۴ دوره تخصصی">
                </label>
                <label>توضیح زیر کارت شناور (FA)
                    <input type="text" name="hero_badge_text_fa" value="<?= e(sp_field($sp, 'hero_badge_text', 'fa')) ?>" placeholder="مثلاً: سابقه حرفه‌ای در رینگ بوکس و مربی‌گری بدنسازی">
                </label>
                <label>برچسب گوشه بالای عکس (FA)
                    <input type="text" name="hero_corner_tag_fa" value="<?= e(sp_field($sp, 'hero_corner_tag', 'fa')) ?>" placeholder="مثلاً: کوالالامپور و آنلاین">
                </label>
            </div>
        </div>

        <hr style="margin:1.75rem 0; border:0; border-top:1px solid #e2e8f0;">
        <h4>Hero Action Buttons &amp; Index Badge</h4>
        <div class="lang-cols">
            <div class="lang-col">
                <label>Primary Button Label (EN)
                    <input type="text" name="hero_btn1_text_en" value="<?= e(sp_field($sp, 'hero_btn1_text', 'en')) ?>">
                </label>
                <label>Secondary Button Label (EN)
                    <input type="text" name="hero_btn2_text_en" value="<?= e(sp_field($sp, 'hero_btn2_text', 'en')) ?>">
                </label>
                <label>Top-Right Section Index Label (EN)
                    <input type="text" name="hero_index_en" value="<?= e(sp_field($sp, 'hero_index', 'en')) ?>" placeholder="01 // COACH PROFILE">
                </label>
            </div>
            <div class="lang-col" dir="rtl">
                <label>متن دکمه اول هیرو (FA)
                    <input type="text" name="hero_btn1_text_fa" value="<?= e(sp_field($sp, 'hero_btn1_text', 'fa')) ?>">
                </label>
                <label>متن دکمه دوم هیرو (FA)
                    <input type="text" name="hero_btn2_text_fa" value="<?= e(sp_field($sp, 'hero_btn2_text', 'fa')) ?>">
                </label>
                <label>شماره و عنوان بخش در نوار بالای هیرو (FA)
                    <input type="text" name="hero_index_fa" value="<?= e(sp_field($sp, 'hero_index', 'fa')) ?>" placeholder="۰۱ // پروفایل مربی">
                </label>
            </div>
        </div>
        <div class="field-grid" style="margin-top:0.5rem;">
            <label>Primary Button Link URL
                <input type="text" name="hero_btn1_url" value="<?= e(sp_field($sp, 'hero_btn1_url', 'en', 'packages.php')) ?>">
            </label>
            <label>Secondary Button Link URL
                <input type="text" name="hero_btn2_url" value="<?= e(sp_field($sp, 'hero_btn2_url', 'en', 'contact.php')) ?>">
            </label>
            <label>Background Watermark Word (English)
                <input type="text" name="hero_watermark" value="<?= e(sp_field($sp, 'hero_watermark', 'en', strtoupper($slug))) ?>">
            </label>
        </div>
    </div>

    <!-- ===================================================================
         TAB 2: PAGE-SPECIFIC SECTIONS & CONTENT
         =================================================================== -->
    <div class="sp-pane <?= $initialTab === 'sections' ? 'is-active' : '' ?>" id="tab-sections">
        <?php if ($slug === 'about'): ?>
            <h3 style="margin-top:0;">Coach Portrait & Biography Section</h3>
            <?php $storyImgPath = sp_field($sp, 'story_image', 'en', 'assets/images/coach-mori.jpg'); ?>
            <div class="sp-img-preview-row">
                <img src="<?= e(url($storyImgPath)) ?>" id="preview_story_img" class="sp-img-thumb" alt="Story Portrait">
                <div style="flex:1; min-width:220px;">
                    <label style="margin-bottom:0.35rem;">Coach Portrait Image
                        <input type="text" name="story_image" id="input_story_img" value="<?= e($storyImgPath) ?>">
                    </label>
                    <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                        <button type="button" class="btn btn-ghost btn-sm sp-open-media" data-target-input="input_story_img" data-target-preview="preview_story_img">Choose from Media Library</button>
                        <input type="file" name="story_image_file" accept="image/*" style="font-size:0.82rem;">
                    </div>
                </div>
            </div>

            <div class="lang-cols">
                <div class="lang-col">
                    <h2 class="lang-title">English Story</h2>
                    <label>Story Eyebrow
                        <input type="text" name="story_eyebrow_en" value="<?= e(sp_field($sp, 'story_eyebrow', 'en')) ?>">
                    </label>
                    <label>Story Section Headline
                        <input type="text" name="story_title_en" value="<?= e(sp_field($sp, 'story_title', 'en')) ?>">
                    </label>
                    <label>Lead Paragraph
                        <textarea name="story_lead_en" rows="3"><?= e(sp_field($sp, 'story_lead', 'en')) ?></textarea>
                    </label>
                    <label>Main Narrative Body
                        <textarea name="story_body_en" rows="6"><?= e(sp_field($sp, 'story_body', 'en')) ?></textarea>
                    </label>
                    <label>Additional Rich Content (Optional)
                        <textarea name="content_en" class="rich-ltr" rows="5"><?= e($page['content_en']) ?></textarea>
                    </label>
                </div>
                <div class="lang-col">
                    <h2 class="lang-title">Persian Story (فارسی)</h2>
                    <label>روتیتر داستان مربی
                        <input type="text" name="story_eyebrow_fa" value="<?= e(sp_field($sp, 'story_eyebrow', 'fa')) ?>" dir="rtl">
                    </label>
                    <label>عنوان بخش بیوگرافی
                        <input type="text" name="story_title_fa" value="<?= e(sp_field($sp, 'story_title', 'fa')) ?>" dir="rtl">
                    </label>
                    <label>پاراگراف مقدمه
                        <textarea name="story_lead_fa" rows="3" dir="rtl"><?= e(sp_field($sp, 'story_lead', 'fa')) ?></textarea>
                    </label>
                    <label>متن اصلی داستان مربی
                        <textarea name="story_body_fa" rows="6" dir="rtl"><?= e(sp_field($sp, 'story_body', 'fa')) ?></textarea>
                    </label>
                    <label>محتوای تکمیلی (اختیاری)
                        <textarea name="content_fa" class="rich-rtl" rows="5" dir="rtl"><?= e($page['content_fa']) ?></textarea>
                    </label>
                </div>
            </div>

            <hr style="margin:2rem 0; border:0; border-top:1px solid #e2e8f0;">
            <h3>Stats Strip (4 Key Numbers)</h3>
            <?php $stats = sp_json($sp, 'stats_json'); ?>
            <?php for ($i = 0; $i < 4; $i++):
                $st = $stats[$i] ?? ['num_en' => '', 'num_fa' => '', 'label_en' => '', 'label_fa' => ''];
            ?>
                <div class="sp-repeater-card">
                    <div class="lang-cols">
                        <div class="lang-col" style="display:grid; grid-template-columns:120px 1fr; gap:0.75rem;">
                            <label>Number (EN)
                                <input type="text" name="stats[<?= $i ?>][num_en]" value="<?= e($st['num_en'] ?? '') ?>">
                            </label>
                            <label>Label (EN)
                                <input type="text" name="stats[<?= $i ?>][label_en]" value="<?= e($st['label_en'] ?? '') ?>">
                            </label>
                        </div>
                        <div class="lang-col" style="display:grid; grid-template-columns:120px 1fr; gap:0.75rem;" dir="rtl">
                            <label>عدد (FA)
                                <input type="text" name="stats[<?= $i ?>][num_fa]" value="<?= e($st['num_fa'] ?? '') ?>">
                            </label>
                            <label>عنوان آمار (FA)
                                <input type="text" name="stats[<?= $i ?>][label_fa]" value="<?= e($st['label_fa'] ?? '') ?>">
                            </label>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

            <hr style="margin:2rem 0; border:0; border-top:1px solid #e2e8f0;">
            <h3>3 EFT Philosophy Pillars</h3>
            <?php $pillars = sp_json($sp, 'pillars_json'); ?>
            <div class="lang-cols" style="margin-bottom:1rem;">
                <div class="lang-col">
                    <label>Pillars Section Heading (EN)
                        <input type="text" name="pillars_title_en" value="<?= e(sp_field($sp, 'pillars_title', 'en')) ?>">
                    </label>
                    <label>Pillars Subtitle (EN)
                        <input type="text" name="pillars_sub_en" value="<?= e(sp_field($sp, 'pillars_sub', 'en')) ?>">
                    </label>
                </div>
                <div class="lang-col" dir="rtl">
                    <label>عنوان بخش متدولوژی (FA)
                        <input type="text" name="pillars_title_fa" value="<?= e(sp_field($sp, 'pillars_title', 'fa')) ?>">
                    </label>
                    <label>زیرعنوان بخش متدولوژی (FA)
                        <input type="text" name="pillars_sub_fa" value="<?= e(sp_field($sp, 'pillars_sub', 'fa')) ?>">
                    </label>
                </div>
            </div>
            <?php for ($i = 0; $i < 3; $i++):
                $pl = $pillars[$i] ?? ['icon' => 'award', 'title_en' => '', 'title_fa' => '', 'desc_en' => '', 'desc_fa' => ''];
            ?>
                <div class="sp-repeater-card">
                    <input type="hidden" name="pillars[<?= $i ?>][icon]" value="<?= e($pl['icon'] ?? 'award') ?>">
                    <div class="lang-cols">
                        <div class="lang-col">
                            <label>Pillar #<?= $i + 1 ?> Title (EN)
                                <input type="text" name="pillars[<?= $i ?>][title_en]" value="<?= e($pl['title_en'] ?? '') ?>">
                            </label>
                            <label>Pillar #<?= $i + 1 ?> Description (EN)
                                <textarea name="pillars[<?= $i ?>][desc_en]" rows="2"><?= e($pl['desc_en'] ?? '') ?></textarea>
                            </label>
                        </div>
                        <div class="lang-col" dir="rtl">
                            <label>عنوان اصل #<?= $i + 1 ?> (FA)
                                <input type="text" name="pillars[<?= $i ?>][title_fa]" value="<?= e($pl['title_fa'] ?? '') ?>">
                            </label>
                            <label>توضیحات اصل #<?= $i + 1 ?> (FA)
                                <textarea name="pillars[<?= $i ?>][desc_fa]" rows="2"><?= e($pl['desc_fa'] ?? '') ?></textarea>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

        <?php elseif ($slug === 'services'): ?>
            <h3 style="margin-top:0;">Services Intro Header</h3>
            <div class="lang-cols" style="margin-bottom:1.5rem;">
                <div class="lang-col">
                    <label>Intro Eyebrow (EN)
                        <input type="text" name="intro_eyebrow_en" value="<?= e(sp_field($sp, 'intro_eyebrow', 'en')) ?>">
                    </label>
                    <label>Intro Heading (EN)
                        <input type="text" name="intro_title_en" value="<?= e(sp_field($sp, 'intro_title', 'en')) ?>">
                    </label>
                    <label>Intro Subtitle (EN)
                        <input type="text" name="intro_sub_en" value="<?= e(sp_field($sp, 'intro_sub', 'en')) ?>">
                    </label>
                </div>
                <div class="lang-col" dir="rtl">
                    <label>روتیتر بالای خدمات (FA)
                        <input type="text" name="intro_eyebrow_fa" value="<?= e(sp_field($sp, 'intro_eyebrow', 'fa')) ?>">
                    </label>
                    <label>عنوان بخش خدمات (FA)
                        <input type="text" name="intro_title_fa" value="<?= e(sp_field($sp, 'intro_title', 'fa')) ?>">
                    </label>
                    <label>زیرعنوان بخش خدمات (FA)
                        <input type="text" name="intro_sub_fa" value="<?= e(sp_field($sp, 'intro_sub', 'fa')) ?>">
                    </label>
                </div>
            </div>

            <h3>Service Showcase Blocks (Alternating Cards)</h3>
            <p class="muted">Edit each coaching service block below. Write one checklist feature per line.</p>
            <?php
            $servicesList = sp_json($sp, 'services_json');
            $svcCount = max(count($servicesList), 4);
            for ($i = 0; $i < $svcCount; $i++):
                $srv = $servicesList[$i] ?? [];
                $sImg = $srv['image'] ?? 'assets/images/basics-gym.jpg';
            ?>
                <div class="sp-repeater-card" style="margin-bottom:1.5rem;">
                    <h4 style="margin:0 0 0.75rem;">Service Block #<?= $i + 1 ?></h4>
                    <input type="hidden" name="services[<?= $i ?>][icon]" value="<?= e($srv['icon'] ?? 'dumbbell') ?>">
                    <div class="sp-img-preview-row">
                        <img src="<?= e(url($sImg)) ?>" id="preview_svc_<?= $i ?>" class="sp-img-thumb" alt="Service">
                        <div style="flex:1; min-width:220px;">
                            <label style="margin-bottom:0.35rem;">Service Image
                                <input type="text" name="services[<?= $i ?>][image]" id="input_svc_<?= $i ?>" value="<?= e($sImg) ?>">
                            </label>
                            <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                                <button type="button" class="btn btn-ghost btn-sm sp-open-media" data-target-input="input_svc_<?= $i ?>" data-target-preview="preview_svc_<?= $i ?>">Choose from Media Library</button>
                                <input type="file" name="service_img_file_<?= $i ?>" accept="image/*" style="font-size:0.82rem;">
                            </div>
                        </div>
                    </div>

                    <div class="lang-cols">
                        <div class="lang-col">
                            <label>Badge / Tag (EN)
                                <input type="text" name="services[<?= $i ?>][tag_en]" value="<?= e($srv['tag_en'] ?? '') ?>">
                            </label>
                            <label>Service Title (EN)
                                <input type="text" name="services[<?= $i ?>][title_en]" value="<?= e($srv['title_en'] ?? '') ?>">
                            </label>
                            <label>Description (EN)
                                <textarea name="services[<?= $i ?>][desc_en]" rows="3"><?= e($srv['desc_en'] ?? '') ?></textarea>
                            </label>
                            <label>Checklist Bullet Points (EN — 1 per line)
                                <textarea name="services[<?= $i ?>][bullets_en]" rows="4"><?= e($srv['bullets_en'] ?? '') ?></textarea>
                            </label>
                        </div>
                        <div class="lang-col" dir="rtl">
                            <label>برچسب بالای سرویس (FA)
                                <input type="text" name="services[<?= $i ?>][tag_fa]" value="<?= e($srv['tag_fa'] ?? '') ?>">
                            </label>
                            <label>عنوان سرویس (FA)
                                <input type="text" name="services[<?= $i ?>][title_fa]" value="<?= e($srv['title_fa'] ?? '') ?>">
                            </label>
                            <label>توضیحات سرویس (FA)
                                <textarea name="services[<?= $i ?>][desc_fa]" rows="3"><?= e($srv['desc_fa'] ?? '') ?></textarea>
                            </label>
                            <label>ویژگی‌ها (FA — هر خط یک مورد)
                                <textarea name="services[<?= $i ?>][bullets_fa]" rows="4"><?= e($srv['bullets_fa'] ?? '') ?></textarea>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

            <hr style="margin:2rem 0; border:0; border-top:1px solid #e2e8f0;">
            <h3>3-Step Coaching Process</h3>
            <?php $stepsList = sp_json($sp, 'steps_json'); ?>
            <div class="lang-cols" style="margin-bottom:1rem;">
                <div class="lang-col">
                    <label>Process Section Heading (EN)
                        <input type="text" name="steps_title_en" value="<?= e(sp_field($sp, 'steps_title', 'en')) ?>">
                    </label>
                    <label>Process Subtitle (EN)
                        <input type="text" name="steps_sub_en" value="<?= e(sp_field($sp, 'steps_sub', 'en')) ?>">
                    </label>
                </div>
                <div class="lang-col" dir="rtl">
                    <label>عنوان بخش مراحل کار (FA)
                        <input type="text" name="steps_title_fa" value="<?= e(sp_field($sp, 'steps_title', 'fa')) ?>">
                    </label>
                    <label>زیرعنوان بخش مراحل کار (FA)
                        <input type="text" name="steps_sub_fa" value="<?= e(sp_field($sp, 'steps_sub', 'fa')) ?>">
                    </label>
                </div>
            </div>
            <?php for ($i = 0; $i < 3; $i++):
                $st = $stepsList[$i] ?? [];
            ?>
                <div class="sp-repeater-card">
                    <div class="lang-cols">
                        <div class="lang-col">
                            <label>Step 0<?= $i + 1 ?> Title (EN)
                                <input type="text" name="steps[<?= $i ?>][title_en]" value="<?= e($st['title_en'] ?? '') ?>">
                            </label>
                            <label>Step 0<?= $i + 1 ?> Description (EN)
                                <textarea name="steps[<?= $i ?>][desc_en]" rows="2"><?= e($st['desc_en'] ?? '') ?></textarea>
                            </label>
                        </div>
                        <div class="lang-col" dir="rtl">
                            <label>عنوان مرحله ۰<?= $i + 1 ?> (FA)
                                <input type="text" name="steps[<?= $i ?>][title_fa]" value="<?= e($st['title_fa'] ?? '') ?>">
                            </label>
                            <label>توضیحات مرحله ۰<?= $i + 1 ?> (FA)
                                <textarea name="steps[<?= $i ?>][desc_fa]" rows="2"><?= e($st['desc_fa'] ?? '') ?></textarea>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

            <input type="hidden" name="content_en" value="<?= e($page['content_en']) ?>">
            <input type="hidden" name="content_fa" value="<?= e($page['content_fa']) ?>">

        <?php elseif ($slug === 'packages'): ?>
            <div style="display:flex; align-items:center; justify-content:space-between; background:#f0fdf4; border:1px solid #bbf7d0; padding:0.9rem 1.2rem; border-radius:10px; margin-bottom:1.5rem;">
                <div>
                    <strong>Individual Package Pricing Cards</strong>
                    <div class="muted" style="font-size:0.85rem;">To add, edit, or reorder your training packages and prices, use the Packages Manager.</div>
                </div>
                <a class="btn btn-primary btn-sm" href="<?= e(url('admin/packages.php')) ?>">Manage Package Cards →</a>
            </div>

            <h3>Intro Text (Optional)</h3>
            <div class="lang-cols">
                <div class="lang-col">
                    <label>Intro Text Above Packages (EN)
                        <textarea name="content_en" class="rich-ltr" rows="4"><?= e($page['content_en']) ?></textarea>
                    </label>
                </div>
                <div class="lang-col">
                    <label>متن بالای پکیج‌ها (FA)
                        <textarea name="content_fa" class="rich-rtl" rows="4" dir="rtl"><?= e($page['content_fa']) ?></textarea>
                    </label>
                </div>
            </div>

            <hr style="margin:2rem 0; border:0; border-top:1px solid #e2e8f0;">
            <h3>Frequently Asked Questions (FAQ Accordion)</h3>
            <?php
            $faqsList = sp_json($sp, 'faq_json');
            $faqCount = max(count($faqsList), 4);
            ?>
            <div class="lang-cols" style="margin-bottom:1rem;">
                <div class="lang-col">
                    <label>FAQ Section Title (EN)
                        <input type="text" name="faq_title_en" value="<?= e(sp_field($sp, 'faq_title', 'en')) ?>">
                    </label>
                    <label>FAQ Subtitle (EN)
                        <input type="text" name="faq_sub_en" value="<?= e(sp_field($sp, 'faq_sub', 'en')) ?>">
                    </label>
                </div>
                <div class="lang-col" dir="rtl">
                    <label>عنوان بخش سوالات متداول (FA)
                        <input type="text" name="faq_title_fa" value="<?= e(sp_field($sp, 'faq_title', 'fa')) ?>">
                    </label>
                    <label>زیرعنوان سوالات متداول (FA)
                        <input type="text" name="faq_sub_fa" value="<?= e(sp_field($sp, 'faq_sub', 'fa')) ?>">
                    </label>
                </div>
            </div>
            <?php for ($i = 0; $i < $faqCount; $i++):
                $fq = $faqsList[$i] ?? [];
            ?>
                <div class="sp-repeater-card">
                    <div class="lang-cols">
                        <div class="lang-col">
                            <label>Question #<?= $i + 1 ?> (EN)
                                <input type="text" name="faqs[<?= $i ?>][q_en]" value="<?= e($fq['q_en'] ?? '') ?>">
                            </label>
                            <label>Answer #<?= $i + 1 ?> (EN)
                                <textarea name="faqs[<?= $i ?>][a_en]" rows="2"><?= e($fq['a_en'] ?? '') ?></textarea>
                            </label>
                        </div>
                        <div class="lang-col" dir="rtl">
                            <label>سوال #<?= $i + 1 ?> (FA)
                                <input type="text" name="faqs[<?= $i ?>][q_fa]" value="<?= e($fq['q_fa'] ?? '') ?>">
                            </label>
                            <label>پاسخ #<?= $i + 1 ?> (FA)
                                <textarea name="faqs[<?= $i ?>][a_fa]" rows="2"><?= e($fq['a_fa'] ?? '') ?></textarea>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

        <?php elseif ($slug === 'contact'): ?>
            <div style="display:flex; align-items:center; justify-content:space-between; background:#f0fdf4; border:1px solid #bbf7d0; padding:0.9rem 1.2rem; border-radius:10px; margin-bottom:1.5rem;">
                <div>
                    <strong>Phone, Email & WhatsApp Number</strong>
                    <div class="muted" style="font-size:0.85rem;">Your phone number, email, and WhatsApp settings are synced automatically from Settings / Dashboard.</div>
                </div>
                <a class="btn btn-primary btn-sm" href="<?= e(url('admin/settings.php')) ?>">Contact Settings →</a>
            </div>

            <div class="lang-cols">
                <div class="lang-col">
                    <h2 class="lang-title">English Contact Details</h2>
                    <label>Left Column Heading
                        <input type="text" name="info_title_en" value="<?= e(sp_field($sp, 'info_title', 'en')) ?>">
                    </label>
                    <label>Left Column Intro Text
                        <textarea name="info_sub_en" rows="3"><?= e(sp_field($sp, 'info_sub', 'en')) ?></textarea>
                    </label>
                    <label>Location Text
                        <input type="text" name="location_en" value="<?= e(sp_field($sp, 'location_en', 'en')) ?>">
                    </label>
                    <label>Coaching Hours Text
                        <input type="text" name="hours_en" value="<?= e(sp_field($sp, 'hours_en', 'en')) ?>">
                    </label>
                    <label>Additional Notes (Optional)
                        <textarea name="content_en" class="rich-ltr" rows="4"><?= e($page['content_en']) ?></textarea>
                    </label>
                </div>
                <div class="lang-col" dir="rtl">
                    <h2 class="lang-title">اطلاعات تماس فارسی</h2>
                    <label>عنوان ستون اطلاعات تماس
                        <input type="text" name="info_title_fa" value="<?= e(sp_field($sp, 'info_title', 'fa')) ?>">
                    </label>
                    <label>توضیحات ستون اطلاعات تماس
                        <textarea name="info_sub_fa" rows="3"><?= e(sp_field($sp, 'info_sub', 'fa')) ?></textarea>
                    </label>
                    <label>متن موقعیت مکانی
                        <input type="text" name="location_fa" value="<?= e(sp_field($sp, 'location_en', 'fa')) ?>">
                    </label>
                    <label>متن ساعات کاری
                        <input type="text" name="hours_fa" value="<?= e(sp_field($sp, 'hours_en', 'fa')) ?>">
                    </label>
                    <label>توضیحات تکمیلی (اختیاری)
                        <textarea name="content_fa" class="rich-rtl" rows="4"><?= e($page['content_fa']) ?></textarea>
                    </label>
                </div>
            </div>

        <?php elseif ($slug === 'gallery'): ?>
            <div style="display:flex; align-items:center; justify-content:space-between; background:#f0fdf4; border:1px solid #bbf7d0; padding:0.9rem 1.2rem; border-radius:10px; margin-bottom:1.5rem;">
                <div>
                    <strong>Photo Archive (Masonry Grid)</strong>
                    <div class="muted" style="font-size:0.85rem;">Upload, categorize, or delete individual gallery photos in the Gallery Manager.</div>
                </div>
                <a class="btn btn-primary btn-sm" href="<?= e(url('admin/gallery.php')) ?>">Open Photo Manager →</a>
            </div>

            <h3 style="margin-top:0;">1. Gallery Photo Archive Heading</h3>
            <div class="lang-cols" style="margin-bottom:1.5rem;">
                <div class="lang-col">
                    <label>Archive Eyebrow (EN)
                        <input type="text" name="archive_eyebrow_en" value="<?= e(sp_field($sp, 'archive_eyebrow', 'en')) ?>">
                    </label>
                    <label>Archive Section Title (EN)
                        <input type="text" name="archive_title_en" value="<?= e(sp_field($sp, 'archive_title', 'en')) ?>">
                    </label>
                    <label>Archive Subtitle (EN)
                        <input type="text" name="archive_sub_en" value="<?= e(sp_field($sp, 'archive_sub', 'en')) ?>">
                    </label>
                    <label>Additional Intro Text (EN — Optional)
                        <textarea name="content_en" class="rich-ltr" rows="4"><?= e($page['content_en']) ?></textarea>
                    </label>
                </div>
                <div class="lang-col" dir="rtl">
                    <label>روتیتر بالای آرشیو تصاویر (FA)
                        <input type="text" name="archive_eyebrow_fa" value="<?= e(sp_field($sp, 'archive_eyebrow', 'fa')) ?>">
                    </label>
                    <label>عنوان بخش آرشیو تصاویر (FA)
                        <input type="text" name="archive_title_fa" value="<?= e(sp_field($sp, 'archive_title', 'fa')) ?>">
                    </label>
                    <label>زیرعنوان آرشیو تصاویر (FA)
                        <input type="text" name="archive_sub_fa" value="<?= e(sp_field($sp, 'archive_sub', 'fa')) ?>">
                    </label>
                    <label>متن معرفی تکمیلی (FA — اختیاری)
                        <textarea name="content_fa" class="rich-rtl" rows="4" dir="rtl"><?= e($page['content_fa']) ?></textarea>
                    </label>
                </div>
            </div>

            <hr style="margin:2rem 0; border:0; border-top:1px solid #e2e8f0;">
            <h3>2. Featured Client Transformations (CLIENT STORIES)</h3>
            <p class="muted">Edit the section headings and client transformation cards shown under <strong>Featured Client Transformations</strong> on <code>gallery.php</code>. Leave a card's Name and Quote empty to hide that card.</p>

            <div class="lang-cols" style="margin-bottom:1.25rem;">
                <div class="lang-col">
                    <label>Transformations Eyebrow (EN)
                        <input type="text" name="stories_eyebrow_en" value="<?= e(sp_field($sp, 'stories_eyebrow', 'en')) ?>" placeholder="CLIENT STORIES">
                    </label>
                    <label>Transformations Section Title (EN)
                        <input type="text" name="stories_title_en" value="<?= e(sp_field($sp, 'stories_title', 'en')) ?>" placeholder="Featured Client Transformations">
                    </label>
                    <label>Transformations Subtitle (EN — Optional)
                        <input type="text" name="stories_sub_en" value="<?= e(sp_field($sp, 'stories_sub', 'en')) ?>">
                    </label>
                </div>
                <div class="lang-col" dir="rtl">
                    <label>روتیتر بخش داستان موفقیت شاگردان (FA)
                        <input type="text" name="stories_eyebrow_fa" value="<?= e(sp_field($sp, 'stories_eyebrow', 'fa')) ?>" placeholder="داستان موفقیت شاگردان">
                    </label>
                    <label>عنوان بخش تغییرات شاگردان (FA)
                        <input type="text" name="stories_title_fa" value="<?= e(sp_field($sp, 'stories_title', 'fa')) ?>" placeholder="تجربه و تغییرات شاگردان">
                    </label>
                    <label>زیرعنوان بخش تغییرات شاگردان (FA — اختیاری)
                        <input type="text" name="stories_sub_fa" value="<?= e(sp_field($sp, 'stories_sub', 'fa')) ?>">
                    </label>
                </div>
            </div>

            <?php
            $storiesList = sp_json($sp, 'stories_json');
            $storySlots  = max(count($storiesList) + 1, 4);
            for ($i = 0; $i < $storySlots; $i++):
                $st   = $storiesList[$i] ?? [];
                $sImg = $st['image'] ?? '';
            ?>
                <div class="sp-repeater-card" style="margin-bottom:1.25rem;">
                    <h4 style="margin:0 0 0.75rem;">Client Transformation Card #<?= $i + 1 ?> <?= $i >= count($storiesList) ? '<span class="muted" style="font-weight:400; font-size:0.8rem;">(Empty slot — fill to add a new card)</span>' : '' ?></h4>
                    <div class="sp-img-preview-row">
                        <img src="<?= e($sImg !== '' ? url($sImg) : url('assets/images/gallery-sara.jpg')) ?>" id="preview_story_card_<?= $i ?>" class="sp-img-thumb" alt="Client Photo">
                        <div style="flex:1; min-width:220px;">
                            <label style="margin-bottom:0.35rem;">Client Transformation Photo Path
                                <input type="text" name="stories[<?= $i ?>][image]" id="input_story_card_<?= $i ?>" value="<?= e($sImg) ?>" placeholder="assets/images/gallery-sara.jpg">
                            </label>
                            <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                                <button type="button" class="btn btn-ghost btn-sm sp-open-media" data-target-input="input_story_card_<?= $i ?>" data-target-preview="preview_story_card_<?= $i ?>">Choose from Media Library</button>
                                <input type="file" name="story_img_file_<?= $i ?>" accept="image/*" style="font-size:0.82rem;">
                            </div>
                        </div>
                    </div>

                    <div class="lang-cols">
                        <div class="lang-col">
                            <label>Client Name (EN)
                                <input type="text" name="stories[<?= $i ?>][name_en]" value="<?= e($st['name_en'] ?? '') ?>" placeholder="e.g. Sara">
                            </label>
                            <label>Program / Goal Tag (EN)
                                <input type="text" name="stories[<?= $i ?>][tag_en]" value="<?= e($st['tag_en'] ?? '') ?>" placeholder="e.g. Boxing &amp; Conditioning">
                            </label>
                            <label>Client Story / Quote (EN)
                                <textarea name="stories[<?= $i ?>][quote_en]" rows="3"><?= e($st['quote_en'] ?? '') ?></textarea>
                            </label>
                        </div>
                        <div class="lang-col" dir="rtl">
                            <label>نام شاگرد (FA)
                                <input type="text" name="stories[<?= $i ?>][name_fa]" value="<?= e($st['name_fa'] ?? '') ?>" placeholder="مثلاً: سارا">
                            </label>
                            <label>برچسب دوره یا هدف (FA)
                                <input type="text" name="stories[<?= $i ?>][tag_fa]" value="<?= e($st['tag_fa'] ?? '') ?>" placeholder="مثلاً: بوکس و آمادگی جسمانی">
                            </label>
                            <label>متن تجربه و نتیجه شاگرد (FA)
                                <textarea name="stories[<?= $i ?>][quote_fa]" rows="3"><?= e($st['quote_fa'] ?? '') ?></textarea>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

        <?php else: /* blog */ ?>
            <div style="display:flex; align-items:center; justify-content:space-between; background:#f0fdf4; border:1px solid #bbf7d0; padding:0.9rem 1.2rem; border-radius:10px; margin-bottom:1.5rem;">
                <div>
                    <strong>Manage Blog Articles & Categories</strong>
                    <div class="muted" style="font-size:0.85rem;">Write, edit, and publish bilingual articles in the Blog Manager.</div>
                </div>
                <a class="btn btn-primary btn-sm" href="<?= e(url('admin/posts.php')) ?>">Blog Posts Manager →</a>
            </div>

            <div class="lang-cols">
                <div class="lang-col">
                    <h2 class="lang-title">English Intro Content (Optional)</h2>
                    <textarea name="content_en" class="rich-ltr" rows="6"><?= e($page['content_en']) ?></textarea>
                </div>
                <div class="lang-col">
                    <h2 class="lang-title">متن معرفی فارسی (اختیاری)</h2>
                    <textarea name="content_fa" class="rich-rtl" rows="6" dir="rtl"><?= e($page['content_fa']) ?></textarea>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===================================================================
         TAB 3: BOTTOM CTA BANNER
         =================================================================== -->
    <div class="sp-pane <?= $initialTab === 'cta' ? 'is-active' : '' ?>" id="tab-cta">
        <h3 style="margin-top:0;">Bottom Call-to-Action Banner</h3>
        <p class="muted">Appears in a clean light card at the bottom of the page.</p>
        <div class="lang-cols">
            <div class="lang-col">
                <h2 class="lang-title">English CTA</h2>
                <label>CTA Headline
                    <input type="text" name="cta_title_en" value="<?= e(sp_field($sp, 'cta_title', 'en')) ?>">
                </label>
                <label>CTA Subtitle
                    <textarea name="cta_sub_en" rows="2"><?= e(sp_field($sp, 'cta_sub', 'en')) ?></textarea>
                </label>
                <label>CTA Primary Button Label
                    <input type="text" name="cta_btn_text_en" value="<?= e(sp_field($sp, 'cta_btn_text', 'en')) ?>">
                </label>
            </div>
            <div class="lang-col" dir="rtl">
                <h2 class="lang-title">بخش دعوت به اقدام پایین صفحه (FA)</h2>
                <label>عنوان فراخوان
                    <input type="text" name="cta_title_fa" value="<?= e(sp_field($sp, 'cta_title', 'fa')) ?>">
                </label>
                <label>زیرعنوان فراخوان
                    <textarea name="cta_sub_fa" rows="2"><?= e(sp_field($sp, 'cta_sub', 'fa')) ?></textarea>
                </label>
                <label>متن دکمه اصلی
                    <input type="text" name="cta_btn_text_fa" value="<?= e(sp_field($sp, 'cta_btn_text', 'fa')) ?>">
                </label>
            </div>
        </div>
        <label style="max-width:420px;">CTA Button Target Link (e.g. <code>contact.php</code> or <code>packages.php</code>)
            <input type="text" name="cta_btn_url" value="<?= e(sp_field($sp, 'cta_btn_url', 'en', 'contact.php')) ?>">
        </label>
    </div>

    <!-- ===================================================================
         TAB 4: SEO & GOOGLE SEARCH PREVIEW
         =================================================================== -->
    <div class="sp-pane <?= $initialTab === 'seo' ? 'is-active' : '' ?>" id="tab-seo">
        <h3 style="margin-top:0;">Search Engine Optimization (SEO) &amp; Social Sharing</h3>
        <p class="muted">Configure how this page appears in Google search results and social media previews (WhatsApp, Telegram, Instagram, X).</p>

        <!-- Live Google Search Snippet Previews -->
        <div class="lang-cols" style="margin-bottom:1.25rem;">
            <div>
                <div style="font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:0.35rem; text-transform:uppercase;">Google Search Preview (English)</div>
                <div class="adm-google-preview">
                    <div class="adm-google-url">morispt.com › en › <?= e($slug) ?>.php</div>
                    <div class="adm-google-title" id="gprev_title_en"><?= e(($page['title_en'] ?: $pageLabel) . ' — ' . SITE_NAME) ?></div>
                    <div class="adm-google-desc" id="gprev_desc_en"><?= e($page['meta_description_en'] ?: sp_field($sp, 'hero_subtitle', 'en')) ?></div>
                </div>
            </div>
            <div dir="rtl">
                <div style="font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:0.35rem;">پیش‌نمایش نتیجه جستجو در گوگل (فارسی)</div>
                <div class="adm-google-preview">
                    <div class="adm-google-url" dir="ltr" style="text-align:right;">morispt.com › fa › <?= e($slug) ?>.php</div>
                    <div class="adm-google-title" id="gprev_title_fa"><?= e(($page['title_fa'] ?: $pageLabel) . ' — ' . SITE_NAME) ?></div>
                    <div class="adm-google-desc" id="gprev_desc_fa"><?= e($page['meta_description_fa'] ?: sp_field($sp, 'hero_subtitle', 'fa')) ?></div>
                </div>
            </div>
        </div>

        <div class="lang-cols">
            <div class="lang-col">
                <h2 class="lang-title">English SEO</h2>
                <label>Browser &amp; Google Title (EN) *
                    <input type="text" name="title_en" id="seo_title_en" value="<?= e($page['title_en']) ?>" required>
                </label>
                <label>Meta Description (EN) <span class="muted">(Recommended: 120–160 chars)</span>
                    <textarea name="meta_description_en" id="seo_desc_en" rows="3" maxlength="300" placeholder="Clear summary of this page for Google search results..."><?= e($page['meta_description_en'] ?? '') ?></textarea>
                </label>
                <label>Focus Keywords (EN) <span class="muted">(comma-separated)</span>
                    <input type="text" name="seo_keywords_en" value="<?= e(sp_field($sp, 'seo_keywords', 'en')) ?>" placeholder="personal trainer kuala lumpur, boxing coach...">
                </label>
            </div>
            <div class="lang-col" dir="rtl">
                <h2 class="lang-title">تنظیمات سئو فارسی (FA)</h2>
                <label>عنوان صفحه در گوگل و مرورگر (FA)
                    <input type="text" name="title_fa" id="seo_title_fa" value="<?= e($page['title_fa']) ?>">
                </label>
                <label>توضیحات متا برای گوگل (Meta Description FA)
                    <textarea name="meta_description_fa" id="seo_desc_fa" rows="3" maxlength="300" placeholder="توضیح کوتاه و جذاب درباره این صفحه برای نمایش در نتایج گوگل..."><?= e($page['meta_description_fa'] ?? '') ?></textarea>
                </label>
                <label>کلمات کلیدی هدف (Keywords FA) <span class="muted">(با ویرگول جدا کنید)</span>
                    <input type="text" name="seo_keywords_fa" value="<?= e(sp_field($sp, 'seo_keywords', 'fa')) ?>" placeholder="مربی خصوصی کوالالامپور, آموزش بوکس...">
                </label>
            </div>
        </div>

        <hr style="margin:1.5rem 0; border:0; border-top:1px solid #e2e8f0;">
        <h4>Social Share Image (OpenGraph) &amp; Indexing</h4>
        <?php $ogImgPath = sp_field($sp, 'seo_og_image', 'en', sp_field($sp, 'hero_bg', 'en', 'assets/images/hero-earn-it.webp')); ?>
        <div class="sp-img-preview-row">
            <img src="<?= e(url($ogImgPath)) ?>" id="preview_seo_og" class="sp-img-thumb" alt="OG Image">
            <div style="flex:1; min-width:220px;">
                <label style="margin-bottom:0.35rem;">OpenGraph Image Path (shown when link is shared on WhatsApp / Telegram)
                    <input type="text" name="seo_og_image" id="input_seo_og" value="<?= e(sp_field($sp, 'seo_og_image', 'en', '')) ?>" placeholder="Leave empty to use Header image automatically">
                </label>
                <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                    <button type="button" class="btn btn-ghost btn-sm sp-open-media" data-target-input="input_seo_og" data-target-preview="preview_seo_og">Choose from Media Library</button>
                    <input type="file" name="seo_og_file" accept="image/*" style="font-size:0.82rem;">
                </div>
            </div>
        </div>

        <div class="check-row" style="margin-top:1rem;">
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= (int) $page['is_active'] === 1 ? 'checked' : '' ?>>
                <strong>Show this page on the public website</strong>
            </label>
            <label class="checkbox">
                <input type="checkbox" name="seo_noindex" value="1" <?= sp_field($sp, 'seo_noindex', 'en', '0') === '1' ? 'checked' : '' ?>>
                Hide this page from search engines (<code>noindex, nofollow</code>)
            </label>
        </div>
    </div>

    <div class="form-actions" style="margin-top:1.75rem; padding-top:1.25rem; border-top:1px solid #e2e8f0;">
        <button type="submit" class="btn btn-primary btn-inline">Save Page Builder &amp; SEO Changes</button>
        <a class="btn btn-ghost" href="<?= e(url('admin/pages.php')) ?>">Cancel</a>
    </div>
</form>

<!-- Media Library Modal -->
<div class="sp-media-modal" id="spMediaModal">
    <div class="sp-media-box">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid #e2e8f0;">
            <strong>Select Image from Website Media Library</strong>
            <button type="button" class="btn btn-ghost btn-sm" id="spMediaClose">Close ✕</button>
        </div>
        <div class="sp-media-grid">
            <?php foreach ($mediaLibrary as $m): ?>
                <div class="sp-media-item" data-path="<?= e($m['path']) ?>" data-url="<?= e($m['url']) ?>">
                    <img src="<?= e($m['url']) ?>" alt="<?= e($m['name'] ?? '') ?>" loading="lazy">
                    <span><?= e($m['name'] ?? $m['path']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tabs
    const tabBtns = document.querySelectorAll('.sp-tab-btn');
    const panes = document.querySelectorAll('.sp-pane');
    const activeTabInp = document.getElementById('active_tab_input');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('is-active'));
            panes.forEach(p => p.classList.remove('is-active'));
            btn.classList.add('is-active');
            const target = document.getElementById(btn.getAttribute('data-tab'));
            if (target) target.classList.add('is-active');
            if (activeTabInp) activeTabInp.value = btn.getAttribute('data-key') || 'hero';
        });
    });

    // Live Google Search Snippet Preview
    const siteName = <?= json_encode(SITE_NAME) ?>;
    const tEn = document.getElementById('seo_title_en');
    const dEn = document.getElementById('seo_desc_en');
    const tFa = document.getElementById('seo_title_fa');
    const dFa = document.getElementById('seo_desc_fa');
    const gpTitleEn = document.getElementById('gprev_title_en');
    const gpDescEn  = document.getElementById('gprev_desc_en');
    const gpTitleFa = document.getElementById('gprev_title_fa');
    const gpDescFa  = document.getElementById('gprev_desc_fa');

    function syncGooglePreview() {
        if (tEn && gpTitleEn) gpTitleEn.textContent = (tEn.value.trim() || 'Page Title') + ' — ' + siteName;
        if (dEn && gpDescEn)  gpDescEn.textContent  = dEn.value.trim() || 'Enter a meta description to preview how this page looks on Google...';
        if (tFa && gpTitleFa) gpTitleFa.textContent = (tFa.value.trim() || 'عنوان صفحه') + ' — ' + siteName;
        if (dFa && gpDescFa)  gpDescFa.textContent  = dFa.value.trim() || 'توضیحات متا را وارد کنید تا پیش‌نمایش گوگل را ببینید...';
    }
    [tEn, dEn, tFa, dFa].forEach(el => {
        if (el) el.addEventListener('input', syncGooglePreview);
    });

    // Media Picker Modal
    const modal = document.getElementById('spMediaModal');
    const closeBtn = document.getElementById('spMediaClose');
    let activeInputId = null;
    let activePreviewId = null;

    document.querySelectorAll('.sp-open-media').forEach(btn => {
        btn.addEventListener('click', () => {
            activeInputId = btn.getAttribute('data-target-input');
            activePreviewId = btn.getAttribute('data-target-preview');
            modal.classList.add('is-open');
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => modal.classList.remove('is-open'));
    }
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('is-open');
    });

    document.querySelectorAll('.sp-media-item').forEach(item => {
        item.addEventListener('click', () => {
            const path = item.getAttribute('data-path');
            const url = item.getAttribute('data-url');
            if (activeInputId) {
                const inp = document.getElementById(activeInputId);
                if (inp) inp.value = path;
            }
            if (activePreviewId) {
                const img = document.getElementById(activePreviewId);
                if (img) img.src = url;
            }
            modal.classList.remove('is-open');
        });
    });
});
</script>

<?php require __DIR__ . '/_editor.php'; ?>
<?php require __DIR__ . '/_footer.php'; ?>
