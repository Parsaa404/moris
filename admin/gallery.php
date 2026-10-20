<?php
/**
 * Admin — Gallery images (upload, edit, delete).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('Security token invalid. Please try again.', 'error');
        redirect('admin/gallery.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        $error = null;
        $path = handle_image_upload($_FILES['image'] ?? [], $error);
        if ($path === null) {
            flash_set($error ?? 'Upload failed.', 'error');
        } else {
            $categoryId = ($_POST['category_id'] ?? '') === '' ? null : (int) $_POST['category_id'];
            $stmt = db()->prepare(
                'INSERT INTO gallery_items (category_id, title_en, title_fa, image_path, sort_order, is_active)
                 VALUES (?,?,?,?,?,1)'
            );
            $stmt->execute([
                $categoryId,
                trim((string) ($_POST['title_en'] ?? '')),
                trim((string) ($_POST['title_fa'] ?? '')),
                $path,
                (int) ($_POST['sort_order'] ?? 0),
            ]);
            flash_set('Image uploaded.');
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $categoryId = ($_POST['category_id'] ?? '') === '' ? null : (int) $_POST['category_id'];
        $stmt = db()->prepare(
            'UPDATE gallery_items SET category_id=?, title_en=?, title_fa=?, sort_order=?, is_active=? WHERE id=?'
        );
        $stmt->execute([
            $categoryId,
            trim((string) ($_POST['title_en'] ?? '')),
            trim((string) ($_POST['title_fa'] ?? '')),
            (int) ($_POST['sort_order'] ?? 0),
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);
        flash_set('Image updated.');
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $row = db()->prepare('SELECT image_path FROM gallery_items WHERE id = ?');
        $row->execute([$id]);
        $item = $row->fetch();
        db()->prepare('DELETE FROM gallery_items WHERE id = ?')->execute([$id]);
        if ($item) {
            delete_upload($item['image_path']);
        }
        flash_set('Image deleted.');
    }
    redirect('admin/gallery.php');
}

$categories = get_gallery_categories();
$catName = [];
foreach ($categories as $c) {
    $catName[(int) $c['id']] = $c['name_en'];
}
$items = get_gallery_items();

$pageHeading = 'Gallery';
require __DIR__ . '/_header.php';
?>

<div class="page-actions">
    <p class="muted">Upload photos for your public gallery. Organize them with categories.</p>
    <a class="btn btn-ghost btn-inline" href="<?= e(url('admin/gallery-categories.php')) ?>">Manage categories</a>
</div>

<div class="panel">
    <h2>Upload image</h2>
    <form method="post" enctype="multipart/form-data" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="upload">
        <div class="field-grid">
            <label>Image file
                <input type="file" name="image" accept="image/*" required>
            </label>
            <label>Category
                <select name="category_id">
                    <option value="">— Uncategorized —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['name_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Title (English)
                <input type="text" name="title_en" placeholder="optional">
            </label>
            <label>Title (Persian)
                <input type="text" name="title_fa" dir="rtl" placeholder="اختیاری">
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="0">
            </label>
        </div>
        <p class="muted note">Allowed: JPG, PNG, WEBP, GIF · Max 6 MB.</p>
        <button type="submit" class="btn btn-primary btn-inline">Upload</button>
    </form>
</div>

<div class="panel">
    <h2>Images (<?= count($items) ?>)</h2>
    <?php if (!$items): ?>
        <p class="muted">No images yet. Upload your first one above.</p>
    <?php else: ?>
        <div class="gallery-admin-grid">
            <?php foreach ($items as $it): ?>
                <div class="gallery-admin-card">
                    <img src="<?= e(url($it['image_path'])) ?>" alt="<?= e($it['title_en']) ?>" loading="lazy">
                    <form method="post" class="form gallery-admin-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                        <label>Category
                            <select name="category_id">
                                <option value="">— Uncategorized —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>" <?= (int) $it['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name_en']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Title (EN)
                            <input type="text" name="title_en" value="<?= e($it['title_en']) ?>">
                        </label>
                        <label>Title (FA)
                            <input type="text" name="title_fa" value="<?= e($it['title_fa']) ?>" dir="rtl">
                        </label>
                        <div class="gallery-admin-row">
                            <label class="sort-inline">Sort
                                <input type="number" name="sort_order" value="<?= (int) $it['sort_order'] ?>">
                            </label>
                            <label class="checkbox"><input type="checkbox" name="is_active" value="1" <?= (int) $it['is_active'] ? 'checked' : '' ?>> Show</label>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-ghost btn-inline">Save</button>
                    </form>
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this image?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                        <button type="submit" class="btn btn-ghost btn-danger btn-inline">Delete</button>
                    </form>
                        </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
