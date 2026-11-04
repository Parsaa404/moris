<?php
/**
 * Admin — Homepage Builder
 * Clean, organized, full-width editor with drag-and-drop section ordering,
 * visual media library modal, dynamic repeaters, and bilingual support.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/homepage_cms.php';

require_admin();
$admin = current_admin();

$tab = trim((string) ($_GET['tab'] ?? 'sections'));
$validTabs = ['sections', 'hero', 'basics', 'sparring', 'booking', 'bio', 'gallery', 'media'];
if (!in_array($tab, $validTabs, true)) {
    $tab = 'sections';
}

$errors = [];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('Security token invalid. Please try again.', 'error');
        redirect('admin/homepage.php?tab=' . urlencode($tab));
    }

    $action = $_POST['action'] ?? '';
    $submitAction = $_POST['submit_action'] ?? 'save_and_publish';
    $redirectUrl = 'admin/homepage.php?tab=' . urlencode($tab);

    // 1. Publish or Discard Drafts
    if ($action === 'publish_all') {
        try {
            publish_homepage_drafts();
            flash_set('All changes have been published to the live website.');
        } catch (Throwable $e) {
            flash_set('Failed to publish changes: ' . $e->getMessage(), 'error');
        }
        redirect($redirectUrl);
    }

    if ($action === 'revert_all') {
        try {
            revert_homepage_drafts();
            flash_set('Draft changes were discarded.');
        } catch (Throwable $e) {
            flash_set('Failed to discard drafts: ' . $e->getMessage(), 'error');
        }
        redirect($redirectUrl);
    }

    // 2. Section Manager Save
    if ($action === 'save_sections') {
        $orders = $_POST['sort_order'] ?? [];
        $activeKeys = $_POST['is_active'] ?? [];

        if (is_array($orders)) {
            save_homepage_sections_order($orders, is_array($activeKeys) ? array_keys($activeKeys) : []);
            if ($submitAction === 'save_and_publish') {
                publish_homepage_drafts();
                flash_set('Section order and visibility saved and published.');
            } else {
                flash_set('Section order saved as draft.');
            }
        }
        redirect($redirectUrl);
    }

    // 3. Media Upload & Delete
    if ($action === 'upload_media') {
        if (isset($_FILES['media_file'])) {
            $uploadErr = null;
            $path = handle_image_upload($_FILES['media_file'], $uploadErr);
            if ($path !== null) {
                flash_set('Image uploaded to Media Library: ' . $path);
            } else {
                flash_set($uploadErr ?? 'Image upload failed.', 'error');
            }
        }
        redirect('admin/homepage.php?tab=media');
    }

    if ($action === 'delete_media') {
        $delPath = trim((string) ($_POST['file_path'] ?? ''));
        if ($delPath !== '') {
            delete_upload($delPath);
            flash_set('Deleted uploaded image: ' . htmlspecialchars($delPath));
        }
        redirect('admin/homepage.php?tab=media');
    }

    // 4. Hero Section Save
    if ($action === 'save_hero') {
        $bgImg = sanitize_image_path((string) ($_POST['bg_image'] ?? ''));
        $bgImgRtl = sanitize_image_path((string) ($_POST['bg_image_rtl'] ?? ''));

        $fieldsEn = [
            'headline' => trim((string) ($_POST['headline_en'] ?? '')),
            'tagline_1' => trim((string) ($_POST['tagline_1_en'] ?? '')),
            'tagline_2' => trim((string) ($_POST['tagline_2_en'] ?? '')),
            'tagline_3' => trim((string) ($_POST['tagline_3_en'] ?? '')),
            'bg_image' => $bgImg,
            'bg_image_rtl' => $bgImgRtl,
        ];

        $fieldsFa = [
            'headline' => trim((string) ($_POST['headline_fa'] ?? '')),
            'tagline_1' => trim((string) ($_POST['tagline_1_fa'] ?? '')),
            'tagline_2' => trim((string) ($_POST['tagline_2_fa'] ?? '')),
            'tagline_3' => trim((string) ($_POST['tagline_3_fa'] ?? '')),
            'bg_image' => $bgImg,
            'bg_image_rtl' => $bgImgRtl,
        ];

        if (isset($_FILES['bg_image_file']) && ($_FILES['bg_image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $imgErr = null;
            $upPath = handle_image_upload($_FILES['bg_image_file'], $imgErr);
            if ($upPath) {
                $fieldsEn['bg_image'] = $upPath;
                $fieldsFa['bg_image'] = $upPath;
            } elseif ($imgErr) {
                $errors[] = 'Hero background upload error: ' . $imgErr;
            }
        }

        if (isset($_FILES['bg_image_rtl_file']) && ($_FILES['bg_image_rtl_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $imgErr = null;
            $upPath = handle_image_upload($_FILES['bg_image_rtl_file'], $imgErr);
            if ($upPath) {
                $fieldsEn['bg_image_rtl'] = $upPath;
                $fieldsFa['bg_image_rtl'] = $upPath;
            } elseif ($imgErr) {
                $errors[] = 'Hero RTL background upload error: ' . $imgErr;
            }
        }

        $highlights = [];
        $hlIcons = $_POST['hl_icon'] ?? [];
        $hlTitleEn = $_POST['hl_title_en'] ?? [];
        $hlTitleFa = $_POST['hl_title_fa'] ?? [];
        $hlDescEn = $_POST['hl_desc_en'] ?? [];
        $hlDescFa = $_POST['hl_desc_fa'] ?? [];
        $hlLink = $_POST['hl_link'] ?? [];

        if (is_array($hlTitleEn)) {
            foreach ($hlTitleEn as $idx => $tEn) {
                $tEn = trim((string) $tEn);
                $tFa = trim((string) ($hlTitleFa[$idx] ?? ''));
                if ($tEn !== '' || $tFa !== '') {
                    $highlights[] = [
                        'id' => count($highlights) + 1,
                        'icon' => trim((string) ($hlIcons[$idx] ?? 'award')),
                        'title_en' => $tEn !== '' ? $tEn : $tFa,
                        'title_fa' => $tFa !== '' ? $tFa : $tEn,
                        'desc_en' => trim((string) ($hlDescEn[$idx] ?? '')),
                        'desc_fa' => trim((string) ($hlDescFa[$idx] ?? '')),
                        'link' => sanitize_cms_url((string) ($hlLink[$idx] ?? '#')) ?: '#',
                    ];
                }
            }
        }
        $jsonHighlights = json_encode($highlights, JSON_UNESCAPED_UNICODE);
        $fieldsEn['highlights'] = $jsonHighlights;
        $fieldsFa['highlights'] = $jsonHighlights;

        if (!$errors) {
            save_homepage_section_draft('hero', $fieldsEn, $fieldsFa);
            if ($submitAction === 'save_and_publish') {
                publish_homepage_drafts();
                flash_set('Hero Section saved and published.');
            } else {
                flash_set('Hero Section saved as draft.');
            }
            redirect($redirectUrl);
        }
    }

    // 5. Basics Section Save
    if ($action === 'save_basics') {
        $basicsImg = sanitize_image_path((string) ($_POST['image'] ?? ''));
        $btn1Url = sanitize_cms_url((string) ($_POST['btn1_url'] ?? ''));
        $btn2Url = sanitize_cms_url((string) ($_POST['btn2_url'] ?? ''));

        $fieldsEn = [
            'eyebrow' => trim((string) ($_POST['eyebrow_en'] ?? '')),
            'title' => trim((string) ($_POST['title_en'] ?? '')),
            'lead' => trim((string) ($_POST['lead_en'] ?? '')),
            'desc' => trim((string) ($_POST['desc_en'] ?? '')),
            'image' => $basicsImg,
            'btn1_text' => trim((string) ($_POST['btn1_text_en'] ?? '')),
            'btn1_url' => $btn1Url,
            'btn2_text' => trim((string) ($_POST['btn2_text_en'] ?? '')),
            'btn2_url' => $btn2Url,
        ];

        $fieldsFa = [
            'eyebrow' => trim((string) ($_POST['eyebrow_fa'] ?? '')),
            'title' => trim((string) ($_POST['title_fa'] ?? '')),
            'lead' => trim((string) ($_POST['lead_fa'] ?? '')),
            'desc' => trim((string) ($_POST['desc_fa'] ?? '')),
            'image' => $basicsImg,
            'btn1_text' => trim((string) ($_POST['btn1_text_fa'] ?? '')),
            'btn1_url' => $btn1Url,
            'btn2_text' => trim((string) ($_POST['btn2_text_fa'] ?? '')),
            'btn2_url' => $btn2Url,
        ];

        if (isset($_FILES['image_file']) && ($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $imgErr = null;
            $upPath = handle_image_upload($_FILES['image_file'], $imgErr);
            if ($upPath) {
                $fieldsEn['image'] = $upPath;
                $fieldsFa['image'] = $upPath;
            } elseif ($imgErr) {
                $errors[] = 'Basics image upload error: ' . $imgErr;
            }
        }

        if (!$errors) {
            save_homepage_section_draft('basics', $fieldsEn, $fieldsFa);
            if ($submitAction === 'save_and_publish') {
                publish_homepage_drafts();
                flash_set('Intro / Basics section saved and published.');
            } else {
                flash_set('Intro / Basics section saved as draft.');
            }
            redirect($redirectUrl);
        }
    }

    // 6. Sparring Section Save
    if ($action === 'save_sparring') {
        $sparringImg = sanitize_image_path((string) ($_POST['image'] ?? ''));
        $sparringBtnUrl = sanitize_cms_url((string) ($_POST['btn_url'] ?? ''));

        $fieldsEn = [
            'eyebrow' => trim((string) ($_POST['eyebrow_en'] ?? '')),
            'title' => trim((string) ($_POST['title_en'] ?? '')),
            'text' => trim((string) ($_POST['text_en'] ?? '')),
            'features' => trim((string) ($_POST['features_en'] ?? '')),
            'image' => $sparringImg,
            'btn_text' => trim((string) ($_POST['btn_text_en'] ?? '')),
            'btn_url' => $sparringBtnUrl,
        ];

        $fieldsFa = [
            'eyebrow' => trim((string) ($_POST['eyebrow_fa'] ?? '')),
            'title' => trim((string) ($_POST['title_fa'] ?? '')),
            'text' => trim((string) ($_POST['text_fa'] ?? '')),
            'features' => trim((string) ($_POST['features_fa'] ?? '')),
            'image' => $sparringImg,
            'btn_text' => trim((string) ($_POST['btn_text_fa'] ?? '')),
            'btn_url' => $sparringBtnUrl,
        ];

        if (isset($_FILES['image_file']) && ($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $imgErr = null;
            $upPath = handle_image_upload($_FILES['image_file'], $imgErr);
            if ($upPath) {
                $fieldsEn['image'] = $upPath;
                $fieldsFa['image'] = $upPath;
            } elseif ($imgErr) {
                $errors[] = 'Sparring artwork upload error: ' . $imgErr;
            }
        }

        if (!$errors) {
            save_homepage_section_draft('sparring', $fieldsEn, $fieldsFa);
            if ($submitAction === 'save_and_publish') {
                publish_homepage_drafts();
                flash_set('Sparring section saved and published.');
            } else {
                flash_set('Sparring section saved as draft.');
            }
            redirect($redirectUrl);
        }
    }

    // 7. Booking & Chat Section Save
    if ($action === 'save_booking') {
        $fieldsEn = [
            'chat_title' => trim((string) ($_POST['chat_title_en'] ?? '')),
            'calendar_title' => trim((string) ($_POST['calendar_title_en'] ?? '')),
            'calendar_subtitle' => trim((string) ($_POST['calendar_subtitle_en'] ?? '')),
            'time_slots' => trim((string) ($_POST['time_slots_en'] ?? '')),
            'submit_btn_text' => trim((string) ($_POST['submit_btn_text_en'] ?? '')),
        ];

        $fieldsFa = [
            'chat_title' => trim((string) ($_POST['chat_title_fa'] ?? '')),
            'calendar_title' => trim((string) ($_POST['calendar_title_fa'] ?? '')),
            'calendar_subtitle' => trim((string) ($_POST['calendar_subtitle_fa'] ?? '')),
            'time_slots' => trim((string) ($_POST['time_slots_fa'] ?? '')),
            'submit_btn_text' => trim((string) ($_POST['submit_btn_text_fa'] ?? '')),
        ];

        $chatCards = [];
        $ccIcon = $_POST['cc_icon'] ?? [];
        $ccTitleEn = $_POST['cc_title_en'] ?? [];
        $ccTitleFa = $_POST['cc_title_fa'] ?? [];
        $ccValEn = $_POST['cc_val_en'] ?? [];
        $ccValFa = $_POST['cc_val_fa'] ?? [];
        $ccBtnEn = $_POST['cc_btn_en'] ?? [];
        $ccBtnFa = $_POST['cc_btn_fa'] ?? [];
        $ccLink = $_POST['cc_link'] ?? [];

        if (is_array($ccTitleEn)) {
            foreach ($ccTitleEn as $idx => $tEn) {
                $tEn = trim((string) $tEn);
                $tFa = trim((string) ($ccTitleFa[$idx] ?? ''));
                if ($tEn !== '' || $tFa !== '') {
                    $chatCards[] = [
                        'id' => count($chatCards) + 1,
                        'icon' => trim((string) ($ccIcon[$idx] ?? 'phone')),
                        'title_en' => $tEn !== '' ? $tEn : $tFa,
                        'title_fa' => $tFa !== '' ? $tFa : $tEn,
                        'val_en' => trim((string) ($ccValEn[$idx] ?? '')),
                        'val_fa' => trim((string) ($ccValFa[$idx] ?? '')),
                        'btn_en' => trim((string) ($ccBtnEn[$idx] ?? 'Connect')),
                        'btn_fa' => trim((string) ($ccBtnFa[$idx] ?? 'ارتباط')),
                        'link' => sanitize_cms_url((string) ($ccLink[$idx] ?? '#')) ?: '#',
                    ];
                }
            }
        }
        $jsonCards = json_encode($chatCards, JSON_UNESCAPED_UNICODE);
        $fieldsEn['chat_cards'] = $jsonCards;
        $fieldsFa['chat_cards'] = $jsonCards;

        if (!$errors) {
            save_homepage_section_draft('booking', $fieldsEn, $fieldsFa);
            if ($submitAction === 'save_and_publish') {
                publish_homepage_drafts();
                flash_set('Consultation & Booking section saved and published.');
            } else {
                flash_set('Consultation & Booking section saved as draft.');
            }
            redirect($redirectUrl);
        }
    }

    // 8. Bio Section Save
    if ($action === 'save_bio') {
        $bioImg = sanitize_image_path((string) ($_POST['image'] ?? ''));
        $bioBtnUrl = sanitize_cms_url((string) ($_POST['btn_url'] ?? ''));

        $fieldsEn = [
            'eyebrow' => trim((string) ($_POST['eyebrow_en'] ?? '')),
            'title' => trim((string) ($_POST['title_en'] ?? '')),
            'subtitle' => trim((string) ($_POST['subtitle_en'] ?? '')),
            'image' => $bioImg,
            'narrative' => trim((string) ($_POST['narrative_en'] ?? '')),
            'btn_text' => trim((string) ($_POST['btn_text_en'] ?? '')),
            'btn_url' => $bioBtnUrl,
        ];

        $fieldsFa = [
            'eyebrow' => trim((string) ($_POST['eyebrow_fa'] ?? '')),
            'title' => trim((string) ($_POST['title_fa'] ?? '')),
            'subtitle' => trim((string) ($_POST['subtitle_fa'] ?? '')),
            'image' => $bioImg,
            'narrative' => trim((string) ($_POST['narrative_fa'] ?? '')),
            'btn_text' => trim((string) ($_POST['btn_text_fa'] ?? '')),
            'btn_url' => $bioBtnUrl,
        ];

        if (isset($_FILES['image_file']) && ($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $imgErr = null;
            $upPath = handle_image_upload($_FILES['image_file'], $imgErr);
            if ($upPath) {
                $fieldsEn['image'] = $upPath;
                $fieldsFa['image'] = $upPath;
            } elseif ($imgErr) {
                $errors[] = 'Coach portrait upload error: ' . $imgErr;
            }
        }

        $quotes = [];
        $qTextEn = $_POST['quote_text_en'] ?? [];
        $qTextFa = $_POST['quote_text_fa'] ?? [];
        $qAuthEn = $_POST['quote_auth_en'] ?? [];
        $qAuthFa = $_POST['quote_auth_fa'] ?? [];

        if (is_array($qTextEn)) {
            foreach ($qTextEn as $idx => $tEn) {
                $tEn = trim((string) $tEn);
                $tFa = trim((string) ($qTextFa[$idx] ?? ''));
                if ($tEn !== '' || $tFa !== '') {
                    $quotes[] = [
                        'id' => count($quotes) + 1,
                        'quote_en' => $tEn !== '' ? $tEn : $tFa,
                        'quote_fa' => $tFa !== '' ? $tFa : $tEn,
                        'author_en' => trim((string) ($qAuthEn[$idx] ?? '')),
                        'author_fa' => trim((string) ($qAuthFa[$idx] ?? '')),
                    ];
                }
            }
        }
        $jsonQuotes = json_encode($quotes, JSON_UNESCAPED_UNICODE);
        $fieldsEn['quotes'] = $jsonQuotes;
        $fieldsFa['quotes'] = $jsonQuotes;

        if (!$errors) {
            save_homepage_section_draft('bio', $fieldsEn, $fieldsFa);
            if ($submitAction === 'save_and_publish') {
                publish_homepage_drafts();
                flash_set('Coach Bio section saved and published.');
            } else {
                flash_set('Coach Bio section saved as draft.');
            }
            redirect($redirectUrl);
        }
    }

    // 9. Gallery Section Save
    if ($action === 'save_gallery') {
        $galBtnUrl = sanitize_cms_url((string) ($_POST['btn_url'] ?? ''));

        $fieldsEn = [
            'title' => trim((string) ($_POST['title_en'] ?? '')),
            'subtitle' => trim((string) ($_POST['subtitle_en'] ?? '')),
            'btn_text' => trim((string) ($_POST['btn_text_en'] ?? '')),
            'btn_url' => $galBtnUrl,
        ];

        $fieldsFa = [
            'title' => trim((string) ($_POST['title_fa'] ?? '')),
            'subtitle' => trim((string) ($_POST['subtitle_fa'] ?? '')),
            'btn_text' => trim((string) ($_POST['btn_text_fa'] ?? '')),
            'btn_url' => $galBtnUrl,
        ];

        $items = [];
        $giNameEn = $_POST['gi_name_en'] ?? [];
        $giNameFa = $_POST['gi_name_fa'] ?? [];
        $giTagEn = $_POST['gi_tag_en'] ?? [];
        $giTagFa = $_POST['gi_tag_fa'] ?? [];
        $giQuoteEn = $_POST['gi_quote_en'] ?? [];
        $giQuoteFa = $_POST['gi_quote_fa'] ?? [];
        $giImage = $_POST['gi_image'] ?? [];

        if (is_array($giNameEn)) {
            foreach ($giNameEn as $idx => $nEn) {
                $nEn = trim((string) $nEn);
                $nFa = trim((string) ($giNameFa[$idx] ?? ''));
                if ($nEn !== '' || $nFa !== '') {
                    $imgPath = sanitize_image_path((string) ($giImage[$idx] ?? '')) ?: 'assets/images/gallery-sara.jpg';

                    if (isset($_FILES['gi_image_file']['name'][$idx]) && $_FILES['gi_image_file']['error'][$idx] === UPLOAD_ERR_OK) {
                        $fileItem = [
                            'name' => $_FILES['gi_image_file']['name'][$idx],
                            'type' => $_FILES['gi_image_file']['type'][$idx],
                            'tmp_name' => $_FILES['gi_image_file']['tmp_name'][$idx],
                            'error' => $_FILES['gi_image_file']['error'][$idx],
                            'size' => $_FILES['gi_image_file']['size'][$idx],
                        ];
                        $cErr = null;
                        $uploaded = handle_image_upload($fileItem, $cErr);
                        if ($uploaded) {
                            $imgPath = $uploaded;
                        }
                    }

                    $items[] = [
                        'id' => count($items) + 1,
                        'image' => $imgPath,
                        'name_en' => $nEn !== '' ? $nEn : $nFa,
                        'name_fa' => $nFa !== '' ? $nFa : $nEn,
                        'tag_en' => trim((string) ($giTagEn[$idx] ?? '')),
                        'tag_fa' => trim((string) ($giTagFa[$idx] ?? '')),
                        'quote_en' => trim((string) ($giQuoteEn[$idx] ?? '')),
                        'quote_fa' => trim((string) ($giQuoteFa[$idx] ?? '')),
                    ];
                }
            }
        }
        $jsonGallery = json_encode($items, JSON_UNESCAPED_UNICODE);
        $fieldsEn['items'] = $jsonGallery;
        $fieldsFa['items'] = $jsonGallery;

        if (!$errors) {
            save_homepage_section_draft('gallery', $fieldsEn, $fieldsFa);
            if ($submitAction === 'save_and_publish') {
                publish_homepage_drafts();
                flash_set('Gallery showcase saved and published.');
            } else {
                flash_set('Gallery showcase saved as draft.');
            }
            redirect($redirectUrl);
        }
    }
}

$hasDrafts = has_homepage_unpublished_changes();
$allSections = get_all_homepage_sections();
$draftContent = get_homepage_content(true);
$mediaImages = get_media_library_images();

$pageHeading = 'Homepage Builder';
require __DIR__ . '/_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/admin-homepage.css')) ?>">

<div class="hp-builder">
    <!-- ======================================================================
         Clean Top Bar
         ====================================================================== -->
    <div class="hp-header-bar">
        <div class="hp-header-left">
            <?php if ($hasDrafts): ?>
                <span class="hp-status-badge is-draft">● Unpublished Drafts</span>
            <?php else: ?>
                <span class="hp-status-badge is-live">● All Changes Published</span>
            <?php endif; ?>
            <span class="muted" style="font-size: 0.85rem;">Customize sections, texts, images, and cards for both English and Persian.</span>
        </div>

        <div class="hp-header-right">
            <?php if ($hasDrafts): ?>
                <form method="post" style="display:inline;" onsubmit="return confirm('Publish all pending draft changes to the live website?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="publish_all">
                    <button type="submit" class="hp-btn hp-btn-accent">Publish All Drafts</button>
                </form>
                <form method="post" style="display:inline;" onsubmit="return confirm('Discard unpublished draft changes?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="revert_all">
                    <button type="submit" class="hp-btn hp-btn-danger">Discard Drafts</button>
                </form>
            <?php endif; ?>
            <a href="<?= e(url('en/')) ?>" target="_blank" class="hp-btn">View Site (EN) ↗</a>
            <a href="<?= e(url('fa/')) ?>" target="_blank" class="hp-btn">مشاهده سایت (FA) ↗</a>
        </div>
    </div>

    <!-- ======================================================================
         Clean Navigation Tabs & Language Toggle Bar
         ====================================================================== -->
    <div class="hp-tabs-bar">
        <nav class="hp-tabs-list">
            <a href="?tab=sections" class="hp-tab-item <?= $tab === 'sections' ? 'is-active' : '' ?>">Layout &amp; Order</a>
            <a href="?tab=hero" class="hp-tab-item <?= $tab === 'hero' ? 'is-active' : '' ?>">Hero Section</a>
            <a href="?tab=basics" class="hp-tab-item <?= $tab === 'basics' ? 'is-active' : '' ?>">Intro / Basics</a>
            <a href="?tab=sparring" class="hp-tab-item <?= $tab === 'sparring' ? 'is-active' : '' ?>">Sparring</a>
            <a href="?tab=booking" class="hp-tab-item <?= $tab === 'booking' ? 'is-active' : '' ?>">Chat &amp; Booking</a>
            <a href="?tab=bio" class="hp-tab-item <?= $tab === 'bio' ? 'is-active' : '' ?>">Coach Bio</a>
            <a href="?tab=gallery" class="hp-tab-item <?= $tab === 'gallery' ? 'is-active' : '' ?>">Gallery</a>
            <a href="?tab=media" class="hp-tab-item <?= $tab === 'media' ? 'is-active' : '' ?>">Media Library</a>
        </nav>

        <?php if (!in_array($tab, ['sections', 'media'], true)): ?>
            <div class="hp-lang-toggle">
                <button type="button" class="hp-lang-btn is-active" data-edit-lang="all">EN + FA</button>
                <button type="button" class="hp-lang-btn" data-edit-lang="en">English</button>
                <button type="button" class="hp-lang-btn" data-edit-lang="fa">فارسی</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <!-- ======================================================================
         TAB 1: LAYOUT & SECTION ORDER
         ====================================================================== -->
    <?php if ($tab === 'sections'): ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_sections">

            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">Homepage Sections &amp; Display Order</h2>
                        <div class="hp-card-subtitle">Drag rows to reorder sections, or use the toggle switch to show or hide any section on the homepage.</div>
                    </div>
                </div>

                <div class="hp-sortable-list" id="hp-sortable-sections">
                    <?php foreach ($allSections as $idx => $sec): 
                        $isOn = (int) $sec['is_draft_active'] === 1;
                        $secKey = $sec['section_key'];
                    ?>
                        <div class="hp-sortable-item <?= !$isOn ? 'is-disabled' : '' ?>" draggable="true" data-section-key="<?= e($secKey) ?>">
                            <span class="hp-drag-handle" title="Drag to reorder">⋮⋮</span>
                            <input type="hidden" name="sort_order[<?= e($secKey) ?>]" value="<?= $idx + 1 ?>" class="hp-sort-input">

                            <div class="hp-sec-info">
                                <div class="hp-sec-title"><?= e($sec['name_en']) ?></div>
                                <div class="hp-sec-meta"><?= e($sec['name_fa']) ?> &middot; <code>#<?= e($secKey) ?></code></div>
                            </div>

                            <div class="hp-repeater-controls">
                                <button type="button" class="hp-icon-btn js-move-up" title="Move Up">↑</button>
                                <button type="button" class="hp-icon-btn js-move-down" title="Move Down">↓</button>
                            </div>

                            <label class="hp-switch" title="Show / Hide Section">
                                <input type="checkbox" name="is_active[<?= e($secKey) ?>]" value="1" <?= $isOn ? 'checked' : '' ?> onchange="this.closest('.hp-sortable-item').classList.toggle('is-disabled', !this.checked)">
                                <span class="hp-switch-slider"></span>
                            </label>

                            <a href="?tab=<?= e($secKey) ?>" class="hp-btn" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                Edit Section →
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hp-save-bar">
                <button type="submit" name="submit_action" value="save_draft" class="hp-btn">Save as Draft</button>
                <button type="submit" name="submit_action" value="save_and_publish" class="hp-btn hp-btn-primary">Save &amp; Publish</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ======================================================================
         TAB 2: HERO SECTION
         ====================================================================== -->
    <?php if ($tab === 'hero'): 
        $c = $draftContent['hero'] ?? [];
        $hlItems = hp_json($draftContent, 'hero', 'highlights', []);
    ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_hero">

            <!-- Hero Background Images -->
            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">Hero Background Images</h2>
                        <div class="hp-card-subtitle">Select an image from the Media Library or upload a new background file.</div>
                    </div>
                </div>

                <div class="hp-bilingual-grid">
                    <div class="hp-field">
                        <label>English Background (LTR)</label>
                        <div class="hp-img-widget">
                            <div class="hp-img-thumb-wrap">
                                <img src="<?= e(url($c['bg_image']['draft_en'] ?? 'assets/images/hero-earn-it.webp')) ?>" id="preview_bg_image" alt="Hero LTR">
                            </div>
                            <div class="hp-img-controls">
                                <input type="text" name="bg_image" id="input_bg_image" value="<?= e($c['bg_image']['draft_en'] ?? 'assets/images/hero-earn-it.webp') ?>" style="font-family:monospace; font-size:0.8rem;">
                                <div class="hp-img-btn-row">
                                    <button type="button" class="hp-icon-btn js-open-media-modal" data-target-input="input_bg_image" data-target-preview="preview_bg_image">
                                        Choose from Library
                                    </button>
                                    <label class="hp-icon-btn" style="margin:0; cursor:pointer;">
                                        Upload File
                                        <input type="file" name="bg_image_file" accept="image/*" style="display:none;" onchange="previewUploadedFile(this, 'preview_bg_image')">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hp-field">
                        <label>Persian Background (RTL)</label>
                        <div class="hp-img-widget">
                            <div class="hp-img-thumb-wrap">
                                <img src="<?= e(url($c['bg_image_rtl']['draft_en'] ?? 'assets/images/hero-earn-it-rtl.webp')) ?>" id="preview_bg_image_rtl" alt="Hero RTL">
                            </div>
                            <div class="hp-img-controls">
                                <input type="text" name="bg_image_rtl" id="input_bg_image_rtl" value="<?= e($c['bg_image_rtl']['draft_en'] ?? 'assets/images/hero-earn-it-rtl.webp') ?>" style="font-family:monospace; font-size:0.8rem;">
                                <div class="hp-img-btn-row">
                                    <button type="button" class="hp-icon-btn js-open-media-modal" data-target-input="input_bg_image_rtl" data-target-preview="preview_bg_image_rtl">
                                        Choose from Library
                                    </button>
                                    <label class="hp-icon-btn" style="margin:0; cursor:pointer;">
                                        Upload File
                                        <input type="file" name="bg_image_rtl_file" accept="image/*" style="display:none;" onchange="previewUploadedFile(this, 'preview_bg_image_rtl')">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Headline & Taglines -->
            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">Hero Headline &amp; Tagline</h2>
                        <div class="hp-card-subtitle">Main headline ("EARN IT.") and the 3-line subtitle.</div>
                    </div>
                </div>

                <div class="hp-bilingual-grid">
                    <div class="hp-lang-box" data-lang-col="en">
                        <span class="hp-lang-badge lang-en">English (EN)</span>
                        <div class="hp-field">
                            <label>Main Headline</label>
                            <input type="text" name="headline_en" value="<?= e($c['headline']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Tagline Line 1</label>
                            <input type="text" name="tagline_1_en" value="<?= e($c['tagline_1']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Tagline Line 2</label>
                            <input type="text" name="tagline_2_en" value="<?= e($c['tagline_2']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Tagline Line 3</label>
                            <input type="text" name="tagline_3_en" value="<?= e($c['tagline_3']['draft_en'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="hp-lang-box" data-lang-col="fa">
                        <span class="hp-lang-badge lang-fa">Persian (فارسی)</span>
                        <div class="hp-field">
                            <label>تیتر اصلی هیرو</label>
                            <input type="text" name="headline_fa" value="<?= e($c['headline']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>خط اول شعار</label>
                            <input type="text" name="tagline_1_fa" value="<?= e($c['tagline_1']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>خط دوم شعار</label>
                            <input type="text" name="tagline_2_fa" value="<?= e($c['tagline_2']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>خط سوم شعار</label>
                            <input type="text" name="tagline_3_fa" value="<?= e($c['tagline_3']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Highlights -->
            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">Bottom Highlight Cards</h2>
                        <div class="hp-card-subtitle">Information badges anchored at the bottom of the Hero section.</div>
                    </div>
                    <button type="button" class="hp-btn" onclick="addHeroHighlight()">+ Add Highlight Card</button>
                </div>

                <div class="hp-repeater-container" id="hero-highlights-list">
                    <?php foreach ($hlItems as $idx => $hl): ?>
                        <div class="hp-repeater-card">
                            <div class="hp-repeater-bar">
                                <span>Highlight Card #<?= $idx + 1 ?></span>
                                <div class="hp-repeater-controls">
                                    <select name="hl_icon[]" style="font-size:0.8rem; padding:0.2rem 0.5rem; border-radius:6px; border:1px solid #d1d5db;">
                                        <option value="award" <?= ($hl['icon'] ?? '') === 'award' ? 'selected' : '' ?>>Award / Ring</option>
                                        <option value="dumbbell" <?= ($hl['icon'] ?? '') === 'dumbbell' ? 'selected' : '' ?>>Dumbbell / Gym</option>
                                        <option value="map-pin" <?= ($hl['icon'] ?? '') === 'map-pin' ? 'selected' : '' ?>>Location Pin</option>
                                        <option value="star" <?= ($hl['icon'] ?? '') === 'star' ? 'selected' : '' ?>>Star</option>
                                        <option value="calendar" <?= ($hl['icon'] ?? '') === 'calendar' ? 'selected' : '' ?>>Calendar</option>
                                    </select>
                                    <button type="button" class="hp-icon-btn js-rep-up" title="Move Up">↑</button>
                                    <button type="button" class="hp-icon-btn js-rep-down" title="Move Down">↓</button>
                                    <button type="button" class="hp-icon-btn is-delete js-rep-del" title="Remove">Delete</button>
                                </div>
                            </div>
                            <div class="hp-repeater-body">
                                <div class="hp-bilingual-grid">
                                    <div class="hp-lang-box" data-lang-col="en">
                                        <div class="hp-field">
                                            <label>Title (EN)</label>
                                            <input type="text" name="hl_title_en[]" value="<?= e($hl['title_en'] ?? '') ?>">
                                        </div>
                                        <div class="hp-field">
                                            <label>Description (EN)</label>
                                            <input type="text" name="hl_desc_en[]" value="<?= e($hl['desc_en'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="hp-lang-box" data-lang-col="fa">
                                        <div class="hp-field">
                                            <label>عنوان (فارسی)</label>
                                            <input type="text" name="hl_title_fa[]" value="<?= e($hl['title_fa'] ?? '') ?>" dir="rtl">
                                        </div>
                                        <div class="hp-field">
                                            <label>توضیح کوتاه (فارسی)</label>
                                            <input type="text" name="hl_desc_fa[]" value="<?= e($hl['desc_fa'] ?? '') ?>" dir="rtl">
                                        </div>
                                    </div>
                                </div>
                                <div class="hp-field" style="margin-top:0.75rem;">
                                    <label>Link Destination (e.g. #bio, #sparring, #consultation)</label>
                                    <input type="text" name="hl_link[]" value="<?= e($hl['link'] ?? '#') ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hp-save-bar">
                <button type="submit" name="submit_action" value="save_draft" class="hp-btn">Save as Draft</button>
                <button type="submit" name="submit_action" value="save_and_publish" class="hp-btn hp-btn-primary">Save &amp; Publish</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ======================================================================
         TAB 3: INTRO / BASICS SECTION
         ====================================================================== -->
    <?php if ($tab === 'basics'): 
        $c = $draftContent['basics'] ?? [];
    ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_basics">

            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">Section Image / Gym Photo</h2>
                        <div class="hp-card-subtitle">Displayed on the left side of the "Back to basics." section.</div>
                    </div>
                </div>
                <div class="hp-img-widget">
                    <div class="hp-img-thumb-wrap">
                        <img src="<?= e(url($c['image']['draft_en'] ?? 'assets/images/basics-gym.jpg')) ?>" id="preview_basics_img" alt="Basics Image">
                    </div>
                    <div class="hp-img-controls">
                        <input type="text" name="image" id="input_basics_img" value="<?= e($c['image']['draft_en'] ?? 'assets/images/basics-gym.jpg') ?>" style="font-family:monospace; font-size:0.8rem;">
                        <div class="hp-img-btn-row">
                            <button type="button" class="hp-icon-btn js-open-media-modal" data-target-input="input_basics_img" data-target-preview="preview_basics_img">
                                Choose from Library
                            </button>
                            <label class="hp-icon-btn" style="margin:0; cursor:pointer;">
                                Upload File
                                <input type="file" name="image_file" accept="image/*" style="display:none;" onchange="previewUploadedFile(this, 'preview_basics_img')">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hp-card">
                <div class="hp-card-head">
                    <h2 class="hp-card-title">Introductory Copy &amp; Buttons ("Back to basics.")</h2>
                </div>
                <div class="hp-bilingual-grid">
                    <div class="hp-lang-box" data-lang-col="en">
                        <span class="hp-lang-badge lang-en">English (EN)</span>
                        <div class="hp-field">
                            <label>Eyebrow Tag</label>
                            <input type="text" name="eyebrow_en" value="<?= e($c['eyebrow']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Main Heading</label>
                            <input type="text" name="title_en" value="<?= e($c['title']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Lead Paragraph</label>
                            <textarea name="lead_en" rows="3"><?= e($c['lead']['draft_en'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>Body Description</label>
                            <textarea name="desc_en" rows="4"><?= e($c['desc']['draft_en'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>Primary Button Label</label>
                            <input type="text" name="btn1_text_en" value="<?= e($c['btn1_text']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Secondary Button Label</label>
                            <input type="text" name="btn2_text_en" value="<?= e($c['btn2_text']['draft_en'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="hp-lang-box" data-lang-col="fa">
                        <span class="hp-lang-badge lang-fa">Persian (فارسی)</span>
                        <div class="hp-field">
                            <label>برچسب بالای عنوان</label>
                            <input type="text" name="eyebrow_fa" value="<?= e($c['eyebrow']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>عنوان اصلی</label>
                            <input type="text" name="title_fa" value="<?= e($c['title']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>پاراگراف اول (لید)</label>
                            <textarea name="lead_fa" rows="3" dir="rtl"><?= e($c['lead']['draft_fa'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>توضیحات تکمیلی</label>
                            <textarea name="desc_fa" rows="4" dir="rtl"><?= e($c['desc']['draft_fa'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>متن دکمه اول</label>
                            <input type="text" name="btn1_text_fa" value="<?= e($c['btn1_text']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>متن دکمه دوم</label>
                            <input type="text" name="btn2_text_fa" value="<?= e($c['btn2_text']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                    </div>
                </div>

                <div class="hp-bilingual-grid" style="margin-top:1rem;">
                    <div class="hp-field">
                        <label>Primary Button URL</label>
                        <input type="text" name="btn1_url" value="<?= e($c['btn1_url']['draft_en'] ?? 'packages.php') ?>">
                    </div>
                    <div class="hp-field">
                        <label>Secondary Button URL</label>
                        <input type="text" name="btn2_url" value="<?= e($c['btn2_url']['draft_en'] ?? '#consultation') ?>">
                    </div>
                </div>
            </div>

            <div class="hp-save-bar">
                <button type="submit" name="submit_action" value="save_draft" class="hp-btn">Save as Draft</button>
                <button type="submit" name="submit_action" value="save_and_publish" class="hp-btn hp-btn-primary">Save &amp; Publish</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ======================================================================
         TAB 4: SPARRING SECTION
         ====================================================================== -->
    <?php if ($tab === 'sparring'): 
        $c = $draftContent['sparring'] ?? [];
    ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_sparring">

            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">Sparring Section Image</h2>
                        <div class="hp-card-subtitle">Visual shown next to "Where do you want to be?".</div>
                    </div>
                </div>
                <div class="hp-img-widget">
                    <div class="hp-img-thumb-wrap">
                        <img src="<?= e(url($c['image']['draft_en'] ?? 'assets/images/sparring-boxers.jpg')) ?>" id="preview_sparring_img" alt="Sparring">
                    </div>
                    <div class="hp-img-controls">
                        <input type="text" name="image" id="input_sparring_img" value="<?= e($c['image']['draft_en'] ?? 'assets/images/sparring-boxers.jpg') ?>" style="font-family:monospace; font-size:0.8rem;">
                        <div class="hp-img-btn-row">
                            <button type="button" class="hp-icon-btn js-open-media-modal" data-target-input="input_sparring_img" data-target-preview="preview_sparring_img">
                                Choose from Library
                            </button>
                            <label class="hp-icon-btn" style="margin:0; cursor:pointer;">
                                Upload File
                                <input type="file" name="image_file" accept="image/*" style="display:none;" onchange="previewUploadedFile(this, 'preview_sparring_img')">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hp-card">
                <div class="hp-card-head">
                    <h2 class="hp-card-title">Content &amp; Feature Bullet Points</h2>
                </div>
                <div class="hp-bilingual-grid">
                    <div class="hp-lang-box" data-lang-col="en">
                        <span class="hp-lang-badge lang-en">English (EN)</span>
                        <div class="hp-field">
                            <label>Eyebrow Tag</label>
                            <input type="text" name="eyebrow_en" value="<?= e($c['eyebrow']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Heading</label>
                            <input type="text" name="title_en" value="<?= e($c['title']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Description</label>
                            <textarea name="text_en" rows="3"><?= e($c['text']['draft_en'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>Bullet Points (One per line)</label>
                            <textarea name="features_en" rows="5"><?= e($c['features']['draft_en'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>CTA Button Text</label>
                            <input type="text" name="btn_text_en" value="<?= e($c['btn_text']['draft_en'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="hp-lang-box" data-lang-col="fa">
                        <span class="hp-lang-badge lang-fa">Persian (فارسی)</span>
                        <div class="hp-field">
                            <label>برچسب بالا</label>
                            <input type="text" name="eyebrow_fa" value="<?= e($c['eyebrow']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>عنوان</label>
                            <input type="text" name="title_fa" value="<?= e($c['title']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>توضیحات</label>
                            <textarea name="text_fa" rows="3" dir="rtl"><?= e($c['text']['draft_fa'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>لیست ویژگی‌ها (هر خط یک مورد)</label>
                            <textarea name="features_fa" rows="5" dir="rtl"><?= e($c['features']['draft_fa'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>متن دکمه</label>
                            <input type="text" name="btn_text_fa" value="<?= e($c['btn_text']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                    </div>
                </div>

                <div class="hp-field" style="margin-top:1rem;">
                    <label>CTA Button URL</label>
                    <input type="text" name="btn_url" value="<?= e($c['btn_url']['draft_en'] ?? '#consultation') ?>">
                </div>
            </div>

            <div class="hp-save-bar">
                <button type="submit" name="submit_action" value="save_draft" class="hp-btn">Save as Draft</button>
                <button type="submit" name="submit_action" value="save_and_publish" class="hp-btn hp-btn-primary">Save &amp; Publish</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ======================================================================
         TAB 5: BOOKING & CHAT SECTION
         ====================================================================== -->
    <?php if ($tab === 'booking'): 
        $c = $draftContent['booking'] ?? [];
        $chatCards = hp_json($draftContent, 'booking', 'chat_cards', []);
    ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_booking">

            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">"Start a chat." Contact Cards</h2>
                        <div class="hp-card-subtitle">Manage direct contact options above the consultation calendar.</div>
                    </div>
                    <button type="button" class="hp-btn" onclick="addChatCard()">+ Add Contact Channel</button>
                </div>

                <div class="hp-bilingual-grid" style="margin-bottom:1.25rem;">
                    <div class="hp-lang-box" data-lang-col="en">
                        <div class="hp-field">
                            <label>Chat Heading (EN)</label>
                            <input type="text" name="chat_title_en" value="<?= e($c['chat_title']['draft_en'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="hp-lang-box" data-lang-col="fa">
                        <div class="hp-field">
                            <label>عنوان بخش چت (فارسی)</label>
                            <input type="text" name="chat_title_fa" value="<?= e($c['chat_title']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                    </div>
                </div>

                <div class="hp-repeater-container" id="chat-cards-list">
                    <?php foreach ($chatCards as $idx => $card): ?>
                        <div class="hp-repeater-card">
                            <div class="hp-repeater-bar">
                                <span>Channel #<?= $idx + 1 ?> — <?= e($card['title_en'] ?? '') ?></span>
                                <div class="hp-repeater-controls">
                                    <select name="cc_icon[]" style="font-size:0.8rem; padding:0.2rem 0.5rem; border-radius:6px; border:1px solid #d1d5db;">
                                        <option value="phone" <?= ($card['icon'] ?? '') === 'phone' ? 'selected' : '' ?>>Phone</option>
                                        <option value="mail" <?= ($card['icon'] ?? '') === 'mail' ? 'selected' : '' ?>>Email</option>
                                        <option value="whatsapp" <?= ($card['icon'] ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                                        <option value="instagram" <?= ($card['icon'] ?? '') === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                                    </select>
                                    <button type="button" class="hp-icon-btn js-rep-up">↑</button>
                                    <button type="button" class="hp-icon-btn js-rep-down">↓</button>
                                    <button type="button" class="hp-icon-btn is-delete js-rep-del">Delete</button>
                                </div>
                            </div>
                            <div class="hp-repeater-body">
                                <div class="hp-bilingual-grid">
                                    <div class="hp-lang-box" data-lang-col="en">
                                        <div class="hp-field">
                                            <label>Title (EN)</label>
                                            <input type="text" name="cc_title_en[]" value="<?= e($card['title_en'] ?? '') ?>">
                                        </div>
                                        <div class="hp-field">
                                            <label>Subtitle / Value (EN)</label>
                                            <input type="text" name="cc_val_en[]" value="<?= e($card['val_en'] ?? '') ?>">
                                        </div>
                                        <div class="hp-field">
                                            <label>Button Label (EN)</label>
                                            <input type="text" name="cc_btn_en[]" value="<?= e($card['btn_en'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="hp-lang-box" data-lang-col="fa">
                                        <div class="hp-field">
                                            <label>عنوان (فارسی)</label>
                                            <input type="text" name="cc_title_fa[]" value="<?= e($card['title_fa'] ?? '') ?>" dir="rtl">
                                        </div>
                                        <div class="hp-field">
                                            <label>مقدار / توضیح (فارسی)</label>
                                            <input type="text" name="cc_val_fa[]" value="<?= e($card['val_fa'] ?? '') ?>" dir="rtl">
                                        </div>
                                        <div class="hp-field">
                                            <label>متن دکمه (فارسی)</label>
                                            <input type="text" name="cc_btn_fa[]" value="<?= e($card['btn_fa'] ?? '') ?>" dir="rtl">
                                        </div>
                                    </div>
                                </div>
                                <div class="hp-field" style="margin-top:0.75rem;">
                                    <label>Action Link (tel:..., mailto:..., https://wa.me/...)</label>
                                    <input type="text" name="cc_link[]" value="<?= e($card['link'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hp-card">
                <div class="hp-card-head">
                    <h2 class="hp-card-title">Consultation Calendar &amp; Time Slots</h2>
                </div>
                <div class="hp-bilingual-grid">
                    <div class="hp-lang-box" data-lang-col="en">
                        <span class="hp-lang-badge lang-en">English (EN)</span>
                        <div class="hp-field">
                            <label>Calendar Heading</label>
                            <input type="text" name="calendar_title_en" value="<?= e($c['calendar_title']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Calendar Subtitle</label>
                            <textarea name="calendar_subtitle_en" rows="2"><?= e($c['calendar_subtitle']['draft_en'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>Time Slots (One per line)</label>
                            <textarea name="time_slots_en" rows="5"><?= e($c['time_slots']['draft_en'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>Submit Button Label</label>
                            <input type="text" name="submit_btn_text_en" value="<?= e($c['submit_btn_text']['draft_en'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="hp-lang-box" data-lang-col="fa">
                        <span class="hp-lang-badge lang-fa">Persian (فارسی)</span>
                        <div class="hp-field">
                            <label>عنوان تقویم</label>
                            <input type="text" name="calendar_title_fa" value="<?= e($c['calendar_title']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>زیرعنوان تقویم</label>
                            <textarea name="calendar_subtitle_fa" rows="2" dir="rtl"><?= e($c['calendar_subtitle']['draft_fa'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>بازه های زمانی (هر خط یک ساعت)</label>
                            <textarea name="time_slots_fa" rows="5" dir="rtl"><?= e($c['time_slots']['draft_fa'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>متن دکمه ثبت</label>
                            <input type="text" name="submit_btn_text_fa" value="<?= e($c['submit_btn_text']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                    </div>
                </div>
            </div>

            <div class="hp-save-bar">
                <button type="submit" name="submit_action" value="save_draft" class="hp-btn">Save as Draft</button>
                <button type="submit" name="submit_action" value="save_and_publish" class="hp-btn hp-btn-primary">Save &amp; Publish</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ======================================================================
         TAB 6: COACH BIO SECTION
         ====================================================================== -->
    <?php if ($tab === 'bio'): 
        $c = $draftContent['bio'] ?? [];
        $quotes = hp_json($draftContent, 'bio', 'quotes', []);
    ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_bio">

            <div class="hp-card">
                <div class="hp-card-head">
                    <h2 class="hp-card-title">Coach Mori Portrait Image</h2>
                </div>
                <div class="hp-img-widget">
                    <div class="hp-img-thumb-wrap">
                        <img src="<?= e(url($c['image']['draft_en'] ?? 'assets/images/coach-mori.jpg')) ?>" id="preview_bio_img" alt="Coach Mori">
                    </div>
                    <div class="hp-img-controls">
                        <input type="text" name="image" id="input_bio_img" value="<?= e($c['image']['draft_en'] ?? 'assets/images/coach-mori.jpg') ?>" style="font-family:monospace; font-size:0.8rem;">
                        <div class="hp-img-btn-row">
                            <button type="button" class="hp-icon-btn js-open-media-modal" data-target-input="input_bio_img" data-target-preview="preview_bio_img">
                                Choose from Library
                            </button>
                            <label class="hp-icon-btn" style="margin:0; cursor:pointer;">
                                Upload File
                                <input type="file" name="image_file" accept="image/*" style="display:none;" onchange="previewUploadedFile(this, 'preview_bio_img')">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hp-card">
                <div class="hp-card-head">
                    <h2 class="hp-card-title">Biography Narrative ("Get to know me.")</h2>
                </div>
                <div class="hp-bilingual-grid">
                    <div class="hp-lang-box" data-lang-col="en">
                        <span class="hp-lang-badge lang-en">English (EN)</span>
                        <div class="hp-field">
                            <label>Eyebrow</label>
                            <input type="text" name="eyebrow_en" value="<?= e($c['eyebrow']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Heading</label>
                            <input type="text" name="title_en" value="<?= e($c['title']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Subtitle Note</label>
                            <input type="text" name="subtitle_en" value="<?= e($c['subtitle']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Biography Paragraphs (Leave blank line between paragraphs)</label>
                            <textarea name="narrative_en" rows="8"><?= e($c['narrative']['draft_en'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>Button Text</label>
                            <input type="text" name="btn_text_en" value="<?= e($c['btn_text']['draft_en'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="hp-lang-box" data-lang-col="fa">
                        <span class="hp-lang-badge lang-fa">Persian (فارسی)</span>
                        <div class="hp-field">
                            <label>برچسب بالا</label>
                            <input type="text" name="eyebrow_fa" value="<?= e($c['eyebrow']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>عنوان</label>
                            <input type="text" name="title_fa" value="<?= e($c['title']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>زیرعنوان</label>
                            <input type="text" name="subtitle_fa" value="<?= e($c['subtitle']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>متن بیوگرافی (پاراگراف‌ها با خط خالی جدا شوند)</label>
                            <textarea name="narrative_fa" rows="8" dir="rtl"><?= e($c['narrative']['draft_fa'] ?? '') ?></textarea>
                        </div>
                        <div class="hp-field">
                            <label>متن دکمه</label>
                            <input type="text" name="btn_text_fa" value="<?= e($c['btn_text']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                    </div>
                </div>

                <div class="hp-field" style="margin-top:1rem;">
                    <label>Button Link URL</label>
                    <input type="text" name="btn_url" value="<?= e($c['btn_url']['draft_en'] ?? 'about.php') ?>">
                </div>
            </div>

            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">Rotating Testimonial Quotes</h2>
                        <div class="hp-card-subtitle">Client endorsements shown below Coach Mori's portrait.</div>
                    </div>
                    <button type="button" class="hp-btn" onclick="addBioQuote()">+ Add Quote</button>
                </div>

                <div class="hp-repeater-container" id="bio-quotes-list">
                    <?php foreach ($quotes as $idx => $q): ?>
                        <div class="hp-repeater-card">
                            <div class="hp-repeater-bar">
                                <span>Testimonial Quote #<?= $idx + 1 ?></span>
                                <div class="hp-repeater-controls">
                                    <button type="button" class="hp-icon-btn js-rep-up">↑</button>
                                    <button type="button" class="hp-icon-btn js-rep-down">↓</button>
                                    <button type="button" class="hp-icon-btn is-delete js-rep-del">Delete</button>
                                </div>
                            </div>
                            <div class="hp-repeater-body">
                                <div class="hp-bilingual-grid">
                                    <div class="hp-lang-box" data-lang-col="en">
                                        <div class="hp-field">
                                            <label>Quote Text (EN)</label>
                                            <textarea name="quote_text_en[]" rows="2"><?= e($q['quote_en'] ?? '') ?></textarea>
                                        </div>
                                        <div class="hp-field">
                                            <label>Author &amp; Title (EN)</label>
                                            <input type="text" name="quote_auth_en[]" value="<?= e($q['author_en'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="hp-lang-box" data-lang-col="fa">
                                        <div class="hp-field">
                                            <label>متن نظر (فارسی)</label>
                                            <textarea name="quote_text_fa[]" rows="2" dir="rtl"><?= e($q['quote_fa'] ?? '') ?></textarea>
                                        </div>
                                        <div class="hp-field">
                                            <label>نام گوینده (فارسی)</label>
                                            <input type="text" name="quote_auth_fa[]" value="<?= e($q['author_fa'] ?? '') ?>" dir="rtl">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hp-save-bar">
                <button type="submit" name="submit_action" value="save_draft" class="hp-btn">Save as Draft</button>
                <button type="submit" name="submit_action" value="save_and_publish" class="hp-btn hp-btn-primary">Save &amp; Publish</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ======================================================================
         TAB 7: GALLERY SECTION
         ====================================================================== -->
    <?php if ($tab === 'gallery'): 
        $c = $draftContent['gallery'] ?? [];
        $galleryItems = hp_json($draftContent, 'gallery', 'items', []);
    ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_gallery">

            <div class="hp-card">
                <div class="hp-card-head">
                    <h2 class="hp-card-title">Gallery Heading &amp; Button</h2>
                </div>
                <div class="hp-bilingual-grid">
                    <div class="hp-lang-box" data-lang-col="en">
                        <span class="hp-lang-badge lang-en">English (EN)</span>
                        <div class="hp-field">
                            <label>Section Title</label>
                            <input type="text" name="title_en" value="<?= e($c['title']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Subtitle</label>
                            <input type="text" name="subtitle_en" value="<?= e($c['subtitle']['draft_en'] ?? '') ?>">
                        </div>
                        <div class="hp-field">
                            <label>Full Gallery Button Label</label>
                            <input type="text" name="btn_text_en" value="<?= e($c['btn_text']['draft_en'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="hp-lang-box" data-lang-col="fa">
                        <span class="hp-lang-badge lang-fa">Persian (فارسی)</span>
                        <div class="hp-field">
                            <label>عنوان بخش</label>
                            <input type="text" name="title_fa" value="<?= e($c['title']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>زیرعنوان</label>
                            <input type="text" name="subtitle_fa" value="<?= e($c['subtitle']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                        <div class="hp-field">
                            <label>متن دکمه مشاهده همه</label>
                            <input type="text" name="btn_text_fa" value="<?= e($c['btn_text']['draft_fa'] ?? '') ?>" dir="rtl">
                        </div>
                    </div>
                </div>
                <div class="hp-field" style="margin-top:1rem;">
                    <label>Full Gallery URL</label>
                    <input type="text" name="btn_url" value="<?= e($c['btn_url']['draft_en'] ?? 'gallery.php') ?>">
                </div>
            </div>

            <div class="hp-card">
                <div class="hp-card-head">
                    <div>
                        <h2 class="hp-card-title">Client Transformation Cards</h2>
                        <div class="hp-card-subtitle">Manage client showcase cards displayed on the homepage.</div>
                    </div>
                    <button type="button" class="hp-btn" onclick="addGalleryCard()">+ Add Client Card</button>
                </div>

                <div class="hp-repeater-container" id="gallery-cards-list">
                    <?php foreach ($galleryItems as $idx => $item): ?>
                        <div class="hp-repeater-card">
                            <div class="hp-repeater-bar">
                                <span>Client Card #<?= $idx + 1 ?> — <?= e($item['name_en'] ?? '') ?></span>
                                <div class="hp-repeater-controls">
                                    <button type="button" class="hp-icon-btn js-rep-up">↑</button>
                                    <button type="button" class="hp-icon-btn js-rep-down">↓</button>
                                    <button type="button" class="hp-icon-btn is-delete js-rep-del">Delete</button>
                                </div>
                            </div>
                            <div class="hp-repeater-body">
                                <div class="hp-field">
                                    <label>Client Photo</label>
                                    <div class="hp-img-widget">
                                        <div class="hp-img-thumb-wrap">
                                            <img src="<?= e(url($item['image'] ?? 'assets/images/gallery-sara.jpg')) ?>" id="preview_gal_<?= $idx ?>" alt="Client">
                                        </div>
                                        <div class="hp-img-controls">
                                            <input type="text" name="gi_image[]" id="input_gal_<?= $idx ?>" value="<?= e($item['image'] ?? '') ?>" style="font-family:monospace; font-size:0.8rem;">
                                            <div class="hp-img-btn-row">
                                                <button type="button" class="hp-icon-btn js-open-media-modal" data-target-input="input_gal_<?= $idx ?>" data-target-preview="preview_gal_<?= $idx ?>">
                                                    Choose from Library
                                                </button>
                                                <label class="hp-icon-btn" style="margin:0; cursor:pointer;">
                                                    Upload Photo
                                                    <input type="file" name="gi_image_file[]" accept="image/*" style="display:none;" onchange="previewUploadedFile(this, 'preview_gal_<?= $idx ?>')">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="hp-bilingual-grid">
                                    <div class="hp-lang-box" data-lang-col="en">
                                        <div class="hp-field">
                                            <label>Client Name (EN)</label>
                                            <input type="text" name="gi_name_en[]" value="<?= e($item['name_en'] ?? '') ?>">
                                        </div>
                                        <div class="hp-field">
                                            <label>Training Tag (EN)</label>
                                            <input type="text" name="gi_tag_en[]" value="<?= e($item['tag_en'] ?? '') ?>">
                                        </div>
                                        <div class="hp-field">
                                            <label>Client Quote (EN)</label>
                                            <textarea name="gi_quote_en[]" rows="3"><?= e($item['quote_en'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="hp-lang-box" data-lang-col="fa">
                                        <div class="hp-field">
                                            <label>نام شاگرد (فارسی)</label>
                                            <input type="text" name="gi_name_fa[]" value="<?= e($item['name_fa'] ?? '') ?>" dir="rtl">
                                        </div>
                                        <div class="hp-field">
                                            <label>تگ تمرینی (فارسی)</label>
                                            <input type="text" name="gi_tag_fa[]" value="<?= e($item['tag_fa'] ?? '') ?>" dir="rtl">
                                        </div>
                                        <div class="hp-field">
                                            <label>متن تجربه شاگرد (فارسی)</label>
                                            <textarea name="gi_quote_fa[]" rows="3" dir="rtl"><?= e($item['quote_fa'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hp-save-bar">
                <button type="submit" name="submit_action" value="save_draft" class="hp-btn">Save as Draft</button>
                <button type="submit" name="submit_action" value="save_and_publish" class="hp-btn hp-btn-primary">Save &amp; Publish</button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ======================================================================
         TAB 8: MEDIA LIBRARY
         ====================================================================== -->
    <?php if ($tab === 'media'): ?>
        <div class="hp-card">
            <div class="hp-card-head">
                <div>
                    <h2 class="hp-card-title">Upload New Image</h2>
                    <div class="hp-card-subtitle">Supported formats: JPG, PNG, WebP, GIF (max 6 MB).</div>
                </div>
            </div>
            <form method="post" enctype="multipart/form-data" style="display:flex; gap:0.85rem; align-items:center; flex-wrap:wrap;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="upload_media">
                <input type="file" name="media_file" accept="image/*" required style="flex:1;">
                <button type="submit" class="hp-btn hp-btn-primary">Upload to Library</button>
            </form>
        </div>

        <div class="hp-card">
            <div class="hp-card-head">
                <h2 class="hp-card-title">Media Library (<?= count($mediaImages) ?> files)</h2>
            </div>
            <div class="hp-modal-grid">
                <?php foreach ($mediaImages as $img): ?>
                    <div class="hp-modal-img-card" style="cursor:default;">
                        <img src="<?= e(url($img['path'])) ?>" loading="lazy" alt="<?= e($img['filename']) ?>">
                        <div style="padding:0.6rem;">
                            <div class="hp-modal-img-label" style="padding:0;" title="<?= e($img['filename']) ?>"><?= e($img['filename']) ?></div>
                            <div style="font-size:0.72rem; color:#6b7280; margin:0.25rem 0;"><?= e($img['dimensions']) ?> &middot; <?= e($img['size_human']) ?></div>
                            <div style="display:flex; gap:0.4rem; margin-top:0.5rem;">
                                <button type="button" class="hp-icon-btn" style="flex:1; justify-content:center;" onclick="navigator.clipboard.writeText('<?= e($img['path']) ?>'); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy Path', 1200);">Copy Path</button>
                                <?php if ($img['is_upload']): ?>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this uploaded image?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_media">
                                        <input type="hidden" name="file_path" value="<?= e($img['path']) ?>">
                                        <button type="submit" class="hp-icon-btn is-delete">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ==========================================================================
     Visual Media Picker Modal
     ========================================================================== -->
<div class="hp-modal-backdrop" id="hp-media-modal">
    <div class="hp-modal">
        <div class="hp-modal-header">
            <h3 class="hp-modal-title">Select Image from Media Library</h3>
            <button type="button" class="hp-icon-btn" onclick="closeMediaModal()">✕ Close</button>
        </div>
        <div class="hp-modal-body">
            <div class="hp-modal-grid">
                <?php foreach ($mediaImages as $img): ?>
                    <div class="hp-modal-img-card js-select-media-item" 
                         data-path="<?= e($img['path']) ?>" 
                         data-full-url="<?= e(url($img['path'])) ?>">
                        <img src="<?= e(url($img['path'])) ?>" loading="lazy" alt="<?= e($img['filename']) ?>">
                        <div class="hp-modal-img-label"><?= e($img['filename']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Language Column Filter (EN + FA / EN Only / FA Only)
document.querySelectorAll('[data-edit-lang]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-edit-lang]').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        const lang = btn.dataset.editLang;
        document.querySelectorAll('.hp-bilingual-grid').forEach(grid => {
            grid.classList.toggle('is-single-col', lang !== 'all');
        });
        document.querySelectorAll('[data-lang-col]').forEach(col => {
            col.style.display = (lang === 'all' || col.dataset.langCol === lang) ? '' : 'none';
        });
    });
});

// 2. Drag & Drop Section Ordering + Up/Down Buttons
const sortableList = document.getElementById('hp-sortable-sections');
if (sortableList) {
    let draggedItem = null;

    function updateSortNumbers() {
        sortableList.querySelectorAll('.hp-sortable-item').forEach((item, index) => {
            const input = item.querySelector('.hp-sort-input');
            if (input) input.value = index + 1;
        });
    }

    sortableList.querySelectorAll('.hp-sortable-item').forEach(item => {
        item.addEventListener('dragstart', () => {
            draggedItem = item;
            setTimeout(() => item.classList.add('is-dragging'), 0);
        });
        item.addEventListener('dragend', () => {
            item.classList.remove('is-dragging');
            draggedItem = null;
            updateSortNumbers();
        });
        item.addEventListener('dragover', e => {
            e.preventDefault();
            const afterElement = getDragAfterElement(sortableList, e.clientY);
            if (afterElement == null) {
                sortableList.appendChild(draggedItem);
            } else {
                sortableList.insertBefore(draggedItem, afterElement);
            }
        });
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.hp-sortable-item:not(.is-dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    sortableList.addEventListener('click', e => {
        const upBtn = e.target.closest('.js-move-up');
        const downBtn = e.target.closest('.js-move-down');
        if (upBtn) {
            const row = upBtn.closest('.hp-sortable-item');
            if (row.previousElementSibling) {
                sortableList.insertBefore(row, row.previousElementSibling);
                updateSortNumbers();
            }
        }
        if (downBtn) {
            const row = downBtn.closest('.hp-sortable-item');
            if (row.nextElementSibling) {
                sortableList.insertBefore(row.nextElementSibling, row);
                updateSortNumbers();
            }
        }
    });
}

// 3. Repeater Controls (Move Up, Move Down, Delete)
document.addEventListener('click', e => {
    const repUp = e.target.closest('.js-rep-up');
    const repDown = e.target.closest('.js-rep-down');
    const repDel = e.target.closest('.js-rep-del');

    if (repUp) {
        const card = repUp.closest('.hp-repeater-card');
        if (card && card.previousElementSibling) {
            card.parentNode.insertBefore(card, card.previousElementSibling);
        }
    }
    if (repDown) {
        const card = repDown.closest('.hp-repeater-card');
        if (card && card.nextElementSibling) {
            card.parentNode.insertBefore(card.nextElementSibling, card);
        }
    }
    if (repDel) {
        const card = repDel.closest('.hp-repeater-card');
        if (card && confirm('Remove this item?')) {
            card.remove();
        }
    }
});

// 4. Dynamic Repeater Builders (+ Add Item)
function addHeroHighlight() {
    const container = document.getElementById('hero-highlights-list');
    const idx = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'hp-repeater-card';
    div.innerHTML = `
        <div class="hp-repeater-bar">
            <span>New Highlight Card #${idx}</span>
            <div class="hp-repeater-controls">
                <select name="hl_icon[]" style="font-size:0.8rem; padding:0.2rem 0.5rem; border-radius:6px; border:1px solid #d1d5db;">
                    <option value="award">Award / Ring</option>
                    <option value="dumbbell">Dumbbell / Gym</option>
                    <option value="map-pin">Location Pin</option>
                    <option value="star">Star</option>
                    <option value="calendar">Calendar</option>
                </select>
                <button type="button" class="hp-icon-btn js-rep-up">↑</button>
                <button type="button" class="hp-icon-btn js-rep-down">↓</button>
                <button type="button" class="hp-icon-btn is-delete js-rep-del">Delete</button>
            </div>
        </div>
        <div class="hp-repeater-body">
            <div class="hp-bilingual-grid">
                <div class="hp-lang-box" data-lang-col="en">
                    <div class="hp-field"><label>Title (EN)</label><input type="text" name="hl_title_en[]" placeholder="e.g. Certified Coach"></div>
                    <div class="hp-field"><label>Description (EN)</label><input type="text" name="hl_desc_en[]" placeholder="Short subtitle"></div>
                </div>
                <div class="hp-lang-box" data-lang-col="fa">
                    <div class="hp-field"><label>عنوان (فارسی)</label><input type="text" name="hl_title_fa[]" dir="rtl"></div>
                    <div class="hp-field"><label>توضیح کوتاه (فارسی)</label><input type="text" name="hl_desc_fa[]" dir="rtl"></div>
                </div>
            </div>
            <div class="hp-field" style="margin-top:0.75rem;"><label>Link Destination</label><input type="text" name="hl_link[]" value="#consultation"></div>
        </div>`;
    container.appendChild(div);
}

function addChatCard() {
    const container = document.getElementById('chat-cards-list');
    const idx = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'hp-repeater-card';
    div.innerHTML = `
        <div class="hp-repeater-bar">
            <span>New Contact Channel #${idx}</span>
            <div class="hp-repeater-controls">
                <select name="cc_icon[]" style="font-size:0.8rem; padding:0.2rem 0.5rem; border-radius:6px; border:1px solid #d1d5db;">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="phone">Phone</option>
                    <option value="mail">Email</option>
                    <option value="instagram">Instagram</option>
                </select>
                <button type="button" class="hp-icon-btn js-rep-up">↑</button>
                <button type="button" class="hp-icon-btn js-rep-down">↓</button>
                <button type="button" class="hp-icon-btn is-delete js-rep-del">Delete</button>
            </div>
        </div>
        <div class="hp-repeater-body">
            <div class="hp-bilingual-grid">
                <div class="hp-lang-box" data-lang-col="en">
                    <div class="hp-field"><label>Title (EN)</label><input type="text" name="cc_title_en[]" placeholder="e.g. Instagram DM"></div>
                    <div class="hp-field"><label>Subtitle (EN)</label><input type="text" name="cc_val_en[]" placeholder="@moris.fitness"></div>
                    <div class="hp-field"><label>Button Label (EN)</label><input type="text" name="cc_btn_en[]" value="Message"></div>
                </div>
                <div class="hp-lang-box" data-lang-col="fa">
                    <div class="hp-field"><label>عنوان (فارسی)</label><input type="text" name="cc_title_fa[]" dir="rtl"></div>
                    <div class="hp-field"><label>توضیح (فارسی)</label><input type="text" name="cc_val_fa[]" dir="rtl"></div>
                    <div class="hp-field"><label>متن دکمه (فارسی)</label><input type="text" name="cc_btn_fa[]" value="ارسال پیام" dir="rtl"></div>
                </div>
            </div>
            <div class="hp-field" style="margin-top:0.75rem;"><label>Action Link</label><input type="text" name="cc_link[]" placeholder="https://..."></div>
        </div>`;
    container.appendChild(div);
}

function addBioQuote() {
    const container = document.getElementById('bio-quotes-list');
    const idx = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'hp-repeater-card';
    div.innerHTML = `
        <div class="hp-repeater-bar">
            <span>New Testimonial Quote #${idx}</span>
            <div class="hp-repeater-controls">
                <button type="button" class="hp-icon-btn js-rep-up">↑</button>
                <button type="button" class="hp-icon-btn js-rep-down">↓</button>
                <button type="button" class="hp-icon-btn is-delete js-rep-del">Delete</button>
            </div>
        </div>
        <div class="hp-repeater-body">
            <div class="hp-bilingual-grid">
                <div class="hp-lang-box" data-lang-col="en">
                    <div class="hp-field"><label>Quote Text (EN)</label><textarea name="quote_text_en[]" rows="2"></textarea></div>
                    <div class="hp-field"><label>Author & Title (EN)</label><input type="text" name="quote_auth_en[]"></div>
                </div>
                <div class="hp-lang-box" data-lang-col="fa">
                    <div class="hp-field"><label>متن نظر (فارسی)</label><textarea name="quote_text_fa[]" rows="2" dir="rtl"></textarea></div>
                    <div class="hp-field"><label>نام گوینده (فارسی)</label><input type="text" name="quote_auth_fa[]" dir="rtl"></div>
                </div>
            </div>
        </div>`;
    container.appendChild(div);
}

function addGalleryCard() {
    const container = document.getElementById('gallery-cards-list');
    const uid = Date.now();
    const div = document.createElement('div');
    div.className = 'hp-repeater-card';
    div.innerHTML = `
        <div class="hp-repeater-bar">
            <span>New Client Card</span>
            <div class="hp-repeater-controls">
                <button type="button" class="hp-icon-btn js-rep-up">↑</button>
                <button type="button" class="hp-icon-btn js-rep-down">↓</button>
                <button type="button" class="hp-icon-btn is-delete js-rep-del">Delete</button>
            </div>
        </div>
        <div class="hp-repeater-body">
            <div class="hp-field">
                <label>Client Photo</label>
                <div class="hp-img-widget">
                    <div class="hp-img-thumb-wrap">
                        <img src="<?= e(url('assets/images/gallery-sara.jpg')) ?>" id="preview_gal_${uid}" alt="Client">
                    </div>
                    <div class="hp-img-controls">
                        <input type="text" name="gi_image[]" id="input_gal_${uid}" value="assets/images/gallery-sara.jpg" style="font-family:monospace; font-size:0.8rem;">
                        <div class="hp-img-btn-row">
                            <button type="button" class="hp-icon-btn js-open-media-modal" data-target-input="input_gal_${uid}" data-target-preview="preview_gal_${uid}">
                                Choose from Library
                            </button>
                            <label class="hp-icon-btn" style="margin:0; cursor:pointer;">
                                Upload Photo
                                <input type="file" name="gi_image_file[]" accept="image/*" style="display:none;" onchange="previewUploadedFile(this, 'preview_gal_${uid}')">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hp-bilingual-grid">
                <div class="hp-lang-box" data-lang-col="en">
                    <div class="hp-field"><label>Client Name (EN)</label><input type="text" name="gi_name_en[]" placeholder="e.g. Daniel"></div>
                    <div class="hp-field"><label>Training Tag (EN)</label><input type="text" name="gi_tag_en[]" placeholder="e.g. Boxing & Conditioning"></div>
                    <div class="hp-field"><label>Client Quote (EN)</label><textarea name="gi_quote_en[]" rows="3"></textarea></div>
                </div>
                <div class="hp-lang-box" data-lang-col="fa">
                    <div class="hp-field"><label>نام شاگرد (فارسی)</label><input type="text" name="gi_name_fa[]" dir="rtl"></div>
                    <div class="hp-field"><label>تگ تمرینی (فارسی)</label><input type="text" name="gi_tag_fa[]" dir="rtl"></div>
                    <div class="hp-field"><label>متن تجربه شاگرد (فارسی)</label><textarea name="gi_quote_fa[]" rows="3" dir="rtl"></textarea></div>
                </div>
            </div>
        </div>`;
    container.appendChild(div);
}

// 5. Visual Media Library Picker Modal
let activeTargetInputId = null;
let activeTargetPreviewId = null;
const mediaModal = document.getElementById('hp-media-modal');

document.addEventListener('click', e => {
    const trigger = e.target.closest('.js-open-media-modal');
    if (trigger) {
        activeTargetInputId = trigger.dataset.targetInput;
        activeTargetPreviewId = trigger.dataset.targetPreview;
        mediaModal.classList.add('is-open');
    }

    const mediaCard = e.target.closest('.js-select-media-item');
    if (mediaCard && activeTargetInputId) {
        const inputEl = document.getElementById(activeTargetInputId);
        const previewEl = document.getElementById(activeTargetPreviewId);
        if (inputEl) inputEl.value = mediaCard.dataset.path;
        if (previewEl) previewEl.src = mediaCard.dataset.fullUrl;
        closeMediaModal();
    }
});

function closeMediaModal() {
    mediaModal.classList.remove('is-open');
    activeTargetInputId = null;
    activeTargetPreviewId = null;
}

function previewUploadedFile(fileInput, previewImgId) {
    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(previewImgId);
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
}
</script>

<?php require __DIR__ . '/_footer.php'; ?>
