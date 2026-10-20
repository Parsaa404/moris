<?php
/**
 * Admin — Site settings.
 * Contact details, social links, tagline, default language, hero image.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

// Settings we manage here (key => label).
$fields = [
    'site_name'        => 'Site name',
    'tagline_en'       => 'Tagline (English)',
    'tagline_fa'       => 'Tagline (Persian)',
    'contact_email'    => 'Contact email (receives enquiries)',
    'contact_phone'    => 'Phone',
    'contact_whatsapp' => 'WhatsApp number',
    'instagram_url'    => 'Instagram URL',
    'youtube_url'      => 'YouTube URL',
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

        // Hero image: upload new, or remove.
        if (isset($_FILES['hero_image']) && ($_FILES['hero_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $imgErr = null;
            $path = handle_image_upload($_FILES['hero_image'], $imgErr);
            if ($path === null) {
                $errors[] = $imgErr ?? 'Hero image upload failed.';
            } else {
                delete_upload(setting('hero_image', ''));
                set_setting('hero_image', $path);
            }
        } elseif (isset($_POST['remove_hero'])) {
            delete_upload(setting('hero_image', ''));
            set_setting('hero_image', '');
        }

        if (!$errors) {
            flash_set('Settings saved.');
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
$heroImage = setting('hero_image', '');

$pageHeading = 'Settings';
require __DIR__ . '/_header.php';
?>

<p class="muted">These control your contact details, social links, and homepage hero. They update the public site instantly.</p>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel form">
    <?= csrf_field() ?>

    <h2 class="lang-title">Brand</h2>
    <label>Site name
        <input type="text" name="site_name" value="<?= e($val['site_name']) ?>">
    </label>
    <div class="lang-cols">
        <label>Tagline (English)
            <input type="text" name="tagline_en" value="<?= e($val['tagline_en']) ?>">
        </label>
        <label>Tagline (Persian)
            <input type="text" name="tagline_fa" value="<?= e($val['tagline_fa']) ?>" dir="rtl">
        </label>
    </div>
    <label>Default language (first visit)
        <select name="default_language">
            <option value="en" <?= $defaultLang === 'en' ? 'selected' : '' ?>>English</option>
            <option value="fa" <?= $defaultLang === 'fa' ? 'selected' : '' ?>>Persian</option>
        </select>
    </label>

    <h2 class="lang-title">Contact</h2>
    <div class="field-grid">
        <label>Contact email <span class="muted">(receives enquiries)</span>
            <input type="text" name="contact_email" value="<?= e($val['contact_email']) ?>" placeholder="you@yourdomain.com">
        </label>
        <label>Phone
            <input type="text" name="contact_phone" value="<?= e($val['contact_phone']) ?>" placeholder="+60...">
        </label>
        <label>WhatsApp number
            <input type="text" name="contact_whatsapp" value="<?= e($val['contact_whatsapp']) ?>" placeholder="+60...">
        </label>
    </div>

    <h2 class="lang-title">Social links</h2>
    <div class="field-grid">
        <label>Instagram URL
            <input type="text" name="instagram_url" value="<?= e($val['instagram_url']) ?>" placeholder="https://instagram.com/...">
        </label>
        <label>YouTube URL
            <input type="text" name="youtube_url" value="<?= e($val['youtube_url']) ?>" placeholder="https://youtube.com/...">
        </label>
    </div>

    <h2 class="lang-title">Homepage hero image</h2>
    <p class="muted note">Optional. A strong training/boxing photo shows behind the homepage headline (with a dark overlay for readable text). Leave empty to use the clean gradient.</p>
    <?php if ($heroImage !== ''): ?>
        <div class="current-image">
            <img src="<?= e(url($heroImage)) ?>" alt="">
            <label class="checkbox"><input type="checkbox" name="remove_hero" value="1"> Remove hero image</label>
        </div>
    <?php endif; ?>
    <label>Upload hero image <span class="muted">(JPG/PNG/WEBP, max 6 MB — wide/landscape works best)</span>
        <input type="file" name="hero_image" accept="image/*">
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-inline">Save settings</button>
    </div>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
