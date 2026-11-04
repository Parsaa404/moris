<?php
/**
 * Admin — Site settings & Global SEO Configuration.
 * Organized into clearly separated visual section containers.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

// Settings we manage here (key => label).
$fields = [
    'site_name'           => 'Site name',
    'tagline_en'          => 'Tagline (English)',
    'tagline_fa'          => 'Tagline (Persian)',
    'seo_default_desc_en' => 'Default Meta Description (EN)',
    'seo_default_desc_fa' => 'Default Meta Description (FA)',
    'seo_keywords_en'     => 'Default Meta Keywords (EN)',
    'seo_keywords_fa'     => 'Default Meta Keywords (FA)',
    'seo_og_image'        => 'Default OpenGraph Share Image',
    'seo_google_verify'   => 'Google Search Console Verification Code',
    'contact_email'       => 'Contact email (receives enquiries)',
    'contact_phone'       => 'Phone',
    'contact_whatsapp'    => 'WhatsApp number',
    'instagram_url'       => 'Instagram URL',
    'youtube_url'         => 'YouTube URL',
    'wa_label_en'         => 'WhatsApp Button Label (EN)',
    'wa_label_fa'         => 'WhatsApp Button Label (FA)',
    'wa_msg_en'           => 'WhatsApp Default Message (EN)',
    'wa_msg_fa'           => 'WhatsApp Default Message (FA)',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Security token invalid. Please try again.';
    } else {
        foreach ($fields as $key => $label) {
            $value = trim((string) ($_POST[$key] ?? ''));
            if ($key === 'contact_email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Contact email is not a valid email address.';
                continue;
            }
            if (in_array($key, ['instagram_url', 'youtube_url'], true)) {
                $value = sanitize_http_url($value);
            }
            set_setting($key, $value);
        }

        // Default language.
        $lang = ($_POST['default_language'] ?? 'en') === 'fa' ? 'fa' : 'en';
        set_setting('default_language', $lang);

        // WhatsApp widget style options.
        set_setting('wa_enabled', isset($_POST['wa_enabled']) ? '1' : '0');
        set_setting('wa_pulse', isset($_POST['wa_pulse']) ? '1' : '0');
        $waStyle = in_array($_POST['wa_style'] ?? '', ['pill', 'circle', 'badge'], true) ? $_POST['wa_style'] : 'pill';
        $waColor = in_array($_POST['wa_color'] ?? '', ['emerald', 'vibrant', 'cyan', 'obsidian'], true) ? $_POST['wa_color'] : 'emerald';
        $waPos = in_array($_POST['wa_position'] ?? '', ['auto', 'right', 'left'], true) ? $_POST['wa_position'] : 'auto';
        set_setting('wa_style', $waStyle);
        set_setting('wa_color', $waColor);
        set_setting('wa_position', $waPos);

        if (!$errors) {
            flash_set('Settings and Global SEO configuration saved.');
            redirect('admin/settings.php');
        }
    }
}

// Current values.
$val = [];
foreach ($fields as $key => $label) {
    $val[$key] = setting($key, '');
}
$defaultLang = setting('default_language', 'en');
$waEnabled = setting('wa_enabled', '1');
$waPulse = setting('wa_pulse', '1');
$waStyle = setting('wa_style', 'pill');
$waColor = setting('wa_color', 'emerald');
$waPos = setting('wa_position', 'auto');

if ($val['wa_label_en'] === '') {
    $val['wa_label_en'] = 'Chat on WhatsApp';
}
if ($val['wa_label_fa'] === '') {
    $val['wa_label_fa'] = 'چت در واتساپ';
}
if ($val['wa_msg_en'] === '') {
    $val['wa_msg_en'] = 'Hi Coach Mori, I would like to inquire about personal training.';
}
if ($val['wa_msg_fa'] === '') {
    $val['wa_msg_fa'] = 'سلام مربی موری، برای مشاوره و شروع تمرینات پیام می‌دهم.';
}

$pageHeading = 'Site Settings & Global SEO';
require __DIR__ . '/_header.php';
?>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" class="form">
    <?= csrf_field() ?>

    <!-- SECTION 01: BRAND & IDENTITY -->
    <section class="adm-section-group accent-blue">
        <div class="adm-group-header">
            <div class="adm-group-title-wrap">
                <span class="adm-group-num">Section 01</span>
                <div class="adm-group-title">
                    <h2>Brand Identity &amp; Default Language</h2>
                    <p>Primary site name, bilingual taglines, and default language on first visitor load</p>
                </div>
            </div>
            <div class="adm-group-actions">
                <button type="submit" class="btn btn-primary btn-inline">Save All Settings</button>
            </div>
        </div>
        <div class="adm-group-body">
            <div class="field-grid">
                <label>Site Name
                    <input type="text" name="site_name" value="<?= e($val['site_name']) ?>">
                </label>
                <label>Default Language (First Visit)
                    <select name="default_language">
                        <option value="en" <?= $defaultLang === 'en' ? 'selected' : '' ?>>English (EN)</option>
                        <option value="fa" <?= $defaultLang === 'fa' ? 'selected' : '' ?>>Persian / فارسی (FA)</option>
                    </select>
                </label>
            </div>
            <div class="lang-cols">
                <label>Tagline (English)
                    <input type="text" name="tagline_en" value="<?= e($val['tagline_en']) ?>">
                </label>
                <label>Tagline (Persian / شعار سایت)
                    <input type="text" name="tagline_fa" value="<?= e($val['tagline_fa']) ?>" dir="rtl">
                </label>
            </div>
        </div>
    </section>

    <!-- SECTION 02: GLOBAL SEO & SEARCH ENGINE SETTINGS -->
    <section class="adm-section-group accent-indigo" id="seo-settings">
        <div class="adm-group-header">
            <div class="adm-group-title-wrap">
                <span class="adm-group-num">Section 02</span>
                <div class="adm-group-title">
                    <h2>Global SEO &amp; Search Engine Configuration</h2>
                    <p>Default meta descriptions, keywords, OpenGraph share image, and Google Search Console verification</p>
                </div>
            </div>
            <div class="adm-group-actions">
                <a class="btn btn-ghost btn-sm" href="<?= e(url('sitemap.xml')) ?>" target="_blank">View Sitemap.xml ↗</a>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('robots.txt')) ?>" target="_blank">View Robots.txt ↗</a>
                <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/pages.php')) ?>">Per-Page SEO →</a>
            </div>
        </div>
        <div class="adm-group-body">
            <div class="lang-cols">
                <div>
                    <label>Default Meta Description (English)
                        <textarea name="seo_default_desc_en" rows="3" placeholder="Certified bodybuilding &amp; fitness coach in Kuala Lumpur..."><?= e($val['seo_default_desc_en']) ?></textarea>
                    </label>
                    <label>Default Meta Keywords (English — comma separated)
                        <input type="text" name="seo_keywords_en" value="<?= e($val['seo_keywords_en']) ?>" placeholder="personal trainer kuala lumpur, bodybuilding coach, online fitness">
                    </label>
                </div>
                <div>
                    <label>توضیحات متا پیش‌فرض سئو (Persian Meta Description)
                        <textarea name="seo_default_desc_fa" rows="3" dir="rtl" placeholder="مربی رسمی بدنسازی و تناسب اندام در کوالالامپور..."><?= e($val['seo_default_desc_fa']) ?></textarea>
                    </label>
                    <label>کلمات کلیدی پیش‌فرض (Persian Meta Keywords)
                        <input type="text" name="seo_keywords_fa" value="<?= e($val['seo_keywords_fa']) ?>" dir="rtl" placeholder="مربی بدنسازی, برنامه تمرینی, مربی خصوصی کوالالامپور">
                    </label>
                </div>
            </div>
            <div class="field-grid" style="margin-top:0.75rem;">
                <label>Default Social Share Image (OpenGraph URL or Path)
                    <input type="text" name="seo_og_image" value="<?= e($val['seo_og_image']) ?>" placeholder="uploads/hero/default-share.jpg">
                </label>
                <label>Google Search Console Verification Code <span class="muted">(meta content value)</span>
                    <input type="text" name="seo_google_verify" value="<?= e($val['seo_google_verify']) ?>" placeholder="e.g. google-site-verification token">
                </label>
            </div>
        </div>
    </section>

    <!-- SECTION 03: CONTACT DETAILS -->
    <section class="adm-section-group accent-emerald">
        <div class="adm-group-header">
            <div class="adm-group-title-wrap">
                <span class="adm-group-num">Section 03</span>
                <div class="adm-group-title">
                    <h2>Contact Channels &amp; Social Media</h2>
                    <p>Direct email for enquiries, phone number, WhatsApp number, and social links</p>
                </div>
            </div>
        </div>
        <div class="adm-group-body">
            <div class="field-grid">
                <label>Contact Email <span class="muted">(receives enquiries)</span>
                    <input type="text" name="contact_email" value="<?= e($val['contact_email']) ?>" placeholder="you@yourdomain.com">
                </label>
                <label>Phone Number
                    <input type="text" name="contact_phone" value="<?= e($val['contact_phone']) ?>" placeholder="+60...">
                </label>
                <label>WhatsApp Number
                    <input type="text" name="contact_whatsapp" value="<?= e($val['contact_whatsapp']) ?>" placeholder="+60...">
                </label>
            </div>
            <div class="field-grid" style="margin-top:0.5rem;">
                <label>Instagram URL
                    <input type="text" name="instagram_url" value="<?= e($val['instagram_url']) ?>" placeholder="https://instagram.com/...">
                </label>
                <label>YouTube URL
                    <input type="text" name="youtube_url" value="<?= e($val['youtube_url']) ?>" placeholder="https://youtube.com/...">
                </label>
            </div>
        </div>
    </section>

    <!-- SECTION 04: WHATSAPP FLOATING BUTTON -->
    <section class="adm-section-group accent-amber">
        <div class="adm-group-header">
            <div class="adm-group-title-wrap">
                <span class="adm-group-num">Section 04</span>
                <div class="adm-group-title">
                    <h2>Floating WhatsApp Button Appearance</h2>
                    <p>Customize the floating WhatsApp widget shown on public pages</p>
                </div>
            </div>
        </div>
        <div class="adm-group-body">
            <div class="check-row" style="margin-top:0; margin-bottom:1rem;">
                <label class="checkbox">
                    <input type="checkbox" name="wa_enabled" value="1" <?= $waEnabled === '1' ? 'checked' : '' ?>>
                    Enable Floating WhatsApp Button
                </label>
                <label class="checkbox">
                    <input type="checkbox" name="wa_pulse" value="1" <?= $waPulse === '1' ? 'checked' : '' ?>>
                    Enable Pulse Ring Animation
                </label>
            </div>
            <div class="field-grid">
                <label>Button Shape / Style
                    <select name="wa_style">
                        <option value="pill" <?= $waStyle === 'pill' ? 'selected' : '' ?>>Pill Button (Icon + Label)</option>
                        <option value="badge" <?= $waStyle === 'badge' ? 'selected' : '' ?>>Coach Online Badge (Live Dot + Subtitle)</option>
                        <option value="circle" <?= $waStyle === 'circle' ? 'selected' : '' ?>>Minimal Circle (Icon Only)</option>
                    </select>
                </label>
                <label>Color Theme
                    <select name="wa_color">
                        <option value="emerald" <?= $waColor === 'emerald' ? 'selected' : '' ?>>WhatsApp Emerald (#128c7e)</option>
                        <option value="vibrant" <?= $waColor === 'vibrant' ? 'selected' : '' ?>>Vibrant Green (#25d366)</option>
                        <option value="cyan" <?= $waColor === 'cyan' ? 'selected' : '' ?>>Moris Brand Cyan (#00856f)</option>
                        <option value="obsidian" <?= $waColor === 'obsidian' ? 'selected' : '' ?>>Stealth Obsidian Dark (#141414)</option>
                    </select>
                </label>
                <label>Screen Position
                    <select name="wa_position">
                        <option value="auto" <?= $waPos === 'auto' ? 'selected' : '' ?>>Auto (Right in EN / Left in FA)</option>
                        <option value="right" <?= $waPos === 'right' ? 'selected' : '' ?>>Always Bottom-Right</option>
                        <option value="left" <?= $waPos === 'left' ? 'selected' : '' ?>>Always Bottom-Left</option>
                    </select>
                </label>
            </div>
            <div class="lang-cols">
                <div>
                    <label>Button Text (English)
                        <input type="text" name="wa_label_en" value="<?= e($val['wa_label_en']) ?>">
                    </label>
                    <label>Default Message (English)
                        <input type="text" name="wa_msg_en" value="<?= e($val['wa_msg_en']) ?>">
                    </label>
                </div>
                <div>
                    <label>Button Text (Persian / متن دکمه)
                        <input type="text" name="wa_label_fa" value="<?= e($val['wa_label_fa']) ?>" dir="rtl">
                    </label>
                    <label>Default Message (Persian / پیام پیش‌فرض)
                        <input type="text" name="wa_msg_fa" value="<?= e($val['wa_msg_fa']) ?>" dir="rtl">
                    </label>
                </div>
            </div>
        </div>
    </section>

    <div class="form-actions" style="display:flex; justify-content:flex-end; margin-top:1rem;">
        <button type="submit" class="btn btn-primary btn-inline">Save All Settings</button>
    </div>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
