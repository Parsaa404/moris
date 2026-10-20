<?php
/**
 * Admin — create or edit a blog post.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$isNew = ($id === 0);

$post = [
    'id' => 0,
    'category_id' => null,
    'slug' => '',
    'title_en' => '', 'title_fa' => '',
    'excerpt_en' => '', 'excerpt_fa' => '',
    'content_en' => '', 'content_fa' => '',
    'featured_image' => null,
    'is_published' => 0,
];

if (!$isNew) {
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('That post was not found.', 'error');
        redirect('admin/blog.php');
    }
    $post = $found;
}

$categories = get_blog_categories();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Security token invalid. Please try again.';
    } else {
        $post['category_id'] = ($_POST['category_id'] ?? '') === '' ? null : (int) $_POST['category_id'];
        $post['title_en']    = trim((string) ($_POST['title_en'] ?? ''));
        $post['title_fa']    = trim((string) ($_POST['title_fa'] ?? ''));
        $post['excerpt_en']  = trim((string) ($_POST['excerpt_en'] ?? ''));
        $post['excerpt_fa']  = trim((string) ($_POST['excerpt_fa'] ?? ''));
        $post['content_en']  = sanitize_html((string) ($_POST['content_en'] ?? ''));
        $post['content_fa']  = sanitize_html((string) ($_POST['content_fa'] ?? ''));
        $post['is_published'] = isset($_POST['is_published']) ? 1 : 0;
        $slugInput = trim((string) ($_POST['slug'] ?? ''));

        if ($post['title_en'] === '') {
            $errors[] = 'English title is required.';
        }

        // Featured image: optional upload; optional remove.
        $newImage = $post['featured_image'];
        if (isset($_FILES['featured_image']) && ($_FILES['featured_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $imgErr = null;
            $uploaded = handle_image_upload($_FILES['featured_image'], $imgErr);
            if ($uploaded === null) {
                $errors[] = $imgErr ?? 'Image upload failed.';
            } else {
                delete_upload($post['featured_image']); // remove old
                $newImage = $uploaded;
            }
        } elseif (isset($_POST['remove_image'])) {
            delete_upload($post['featured_image']);
            $newImage = null;
        }

        if (!$errors) {
            // Build a unique slug (from input or title).
            $slugSource = $slugInput !== '' ? $slugInput : $post['title_en'];
            $slug = unique_slug('blog_posts', slugify($slugSource), $isNew ? 0 : $id);

            if ($isNew) {
                $publishedAt = $post['is_published'] ? date('Y-m-d H:i:s') : null;
                $sql = 'INSERT INTO blog_posts
                        (category_id, slug, title_en, title_fa, excerpt_en, excerpt_fa,
                         content_en, content_fa, featured_image, is_published, published_at)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?)';
                db()->prepare($sql)->execute([
                    $post['category_id'], $slug, $post['title_en'], $post['title_fa'],
                    $post['excerpt_en'], $post['excerpt_fa'], $post['content_en'], $post['content_fa'],
                    $newImage, $post['is_published'], $publishedAt,
                ]);
                flash_set('Post created.');
            } else {
                // Set published_at when publishing for the first time.
                $publishedClause = '';
                if ($post['is_published'] && empty($post['published_at'])) {
                    $publishedClause = ', published_at = NOW()';
                }
                $sql = "UPDATE blog_posts SET
                        category_id=?, slug=?, title_en=?, title_fa=?, excerpt_en=?, excerpt_fa=?,
                        content_en=?, content_fa=?, featured_image=?, is_published=? {$publishedClause}
                        WHERE id=?";
                db()->prepare($sql)->execute([
                    $post['category_id'], $slug, $post['title_en'], $post['title_fa'],
                    $post['excerpt_en'], $post['excerpt_fa'], $post['content_en'], $post['content_fa'],
                    $newImage, $post['is_published'], $id,
                ]);
                flash_set('Post saved.');
            }
            redirect('admin/blog.php');
        }

        $post['featured_image'] = $newImage;
        $post['slug'] = $slugInput;
    }
}

$pageHeading = $isNew ? 'New post' : 'Edit post';
require __DIR__ . '/_header.php';
?>

<p class="muted"><a href="<?= e(url('admin/blog.php')) ?>">← Back to blog</a></p>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">

    <div class="field-grid">
        <label>Category
            <select name="category_id">
                <option value="">— None —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $post['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name_en']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>URL slug <span class="muted">(optional, auto from title)</span>
            <input type="text" name="slug" value="<?= e($post['slug']) ?>" placeholder="my-post-title">
        </label>
    </div>

    <div class="lang-cols">
        <div class="lang-col">
            <h2 class="lang-title">English</h2>
            <label>Title
                <input type="text" name="title_en" value="<?= e($post['title_en']) ?>" required>
            </label>
            <label>Excerpt <span class="muted">(short summary for the list)</span>
                <textarea name="excerpt_en" rows="3"><?= e($post['excerpt_en']) ?></textarea>
            </label>
            <label>Content
                <textarea name="content_en" class="rich-ltr" rows="12"><?= e($post['content_en']) ?></textarea>
            </label>
        </div>
        <div class="lang-col">
            <h2 class="lang-title">Persian (فارسی)</h2>
            <label>Title
                <input type="text" name="title_fa" value="<?= e($post['title_fa']) ?>" dir="rtl">
            </label>
            <label>Excerpt <span class="muted">(خلاصه کوتاه)</span>
                <textarea name="excerpt_fa" rows="3" dir="rtl"><?= e($post['excerpt_fa']) ?></textarea>
            </label>
            <label>Content
                <textarea name="content_fa" class="rich-rtl" rows="12" dir="rtl"><?= e($post['content_fa']) ?></textarea>
            </label>
        </div>
    </div>

    <h2 class="lang-title">Featured image</h2>
    <?php if (!empty($post['featured_image'])): ?>
        <div class="current-image">
            <img src="<?= e(url($post['featured_image'])) ?>" alt="">
            <label class="checkbox"><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
        </div>
    <?php endif; ?>
    <label>Upload image <span class="muted">(optional, JPG/PNG/WEBP/GIF, max 6 MB)</span>
        <input type="file" name="featured_image" accept="image/*">
    </label>

    <label class="checkbox">
        <input type="checkbox" name="is_published" value="1" <?= (int) $post['is_published'] ? 'checked' : '' ?>>
        Publish this post (visible on the website)
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-inline"><?= $isNew ? 'Create post' : 'Save post' ?></button>
        <a class="btn btn-ghost" href="<?= e(url('admin/blog.php')) ?>">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/_editor.php'; ?>
<?php require __DIR__ . '/_footer.php'; ?>
