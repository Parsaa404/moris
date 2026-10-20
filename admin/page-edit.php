<?php
/**
 * Admin — edit a single CMS page (Home / About / Services).
 * Fields: title (EN/FA), content (EN/FA), meta description (EN/FA), visibility.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

$labels = [
    'home'     => 'Home',
    'about'    => 'About',
    'services' => 'Services',
];

// Which page? Slug comes from GET (link) or POST (form submit).
$slug = trim((string) ($_GET['slug'] ?? $_POST['slug'] ?? ''));

$stmt = db()->prepare('SELECT * FROM pages WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    flash_set('That page was not found.', 'error');
    redirect('admin/pages.php');
}

$errors = [];

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
            $errors[] = 'English title is required.';
        }

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

            flash_set('“' . ($labels[$slug] ?? ucfirst($slug)) . '” page saved.');
            redirect('admin/pages.php');
        }

        // Keep submitted values in the form if validation failed.
        $page = array_merge($page, [
            'title_en' => $title_en, 'title_fa' => $title_fa,
            'content_en' => $content_en, 'content_fa' => $content_fa,
            'meta_description_en' => $meta_en, 'meta_description_fa' => $meta_fa,
            'is_active' => $is_active,
        ]);
    }
}

$pageHeading = 'Edit page: ' . ($labels[$slug] ?? ucfirst($slug));
require __DIR__ . '/_header.php';
?>

<p class="muted">
    <a href="<?= e(url('admin/pages.php')) ?>">← Back to pages</a>
</p>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="panel form">
    <?= csrf_field() ?>
    <input type="hidden" name="slug" value="<?= e($slug) ?>">

    <div class="lang-cols">
        <!-- English -->
        <div class="lang-col">
            <h2 class="lang-title">English</h2>
            <label>
                Title
                <input type="text" name="title_en" value="<?= e($page['title_en']) ?>" required>
            </label>
            <label>
                Content
                <textarea name="content_en" class="rich-ltr" rows="10"><?= e($page['content_en']) ?></textarea>
            </label>
            <label>
                Meta description <span class="muted">(for search engines, optional)</span>
                <input type="text" name="meta_description_en" value="<?= e($page['meta_description_en'] ?? '') ?>" maxlength="300">
            </label>
        </div>

        <!-- Persian -->
        <div class="lang-col">
            <h2 class="lang-title">Persian (فارسی)</h2>
            <label>
                Title
                <input type="text" name="title_fa" value="<?= e($page['title_fa']) ?>" dir="rtl">
            </label>
            <label>
                Content
                <textarea name="content_fa" class="rich-rtl" rows="10" dir="rtl"><?= e($page['content_fa']) ?></textarea>
            </label>
            <label>
                Meta description <span class="muted">(اختیاری)</span>
                <input type="text" name="meta_description_fa" value="<?= e($page['meta_description_fa'] ?? '') ?>" maxlength="300" dir="rtl">
            </label>
        </div>
    </div>

    <label class="checkbox">
        <input type="checkbox" name="is_active" value="1" <?= (int) $page['is_active'] === 1 ? 'checked' : '' ?>>
        Show this page on the website
    </label>

    <p class="muted note">
        Use the toolbar to format text — headings, bold, lists, and links. No HTML needed.
    </p>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-inline">Save changes</button>
        <a class="btn btn-ghost" href="<?= e(url('admin/pages.php')) ?>">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/_editor.php'; ?>
<?php require __DIR__ . '/_footer.php'; ?>
