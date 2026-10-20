<?php
/**
 * Admin — Blog categories (add / edit / delete).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('Security token invalid. Please try again.', 'error');
        redirect('admin/blog-categories.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name_en = trim((string) ($_POST['name_en'] ?? ''));
        $name_fa = trim((string) ($_POST['name_fa'] ?? ''));
        $sort    = (int) ($_POST['sort_order'] ?? 0);
        if ($name_en !== '') {
            $slug = unique_slug('blog_categories', slugify($name_en));
            db()->prepare('INSERT INTO blog_categories (name_en, name_fa, slug, sort_order) VALUES (?,?,?,?)')
                ->execute([$name_en, $name_fa, $slug, $sort]);
            flash_set('Category added.');
        } else {
            flash_set('English name is required.', 'error');
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name_en = trim((string) ($_POST['name_en'] ?? ''));
        $name_fa = trim((string) ($_POST['name_fa'] ?? ''));
        $sort    = (int) ($_POST['sort_order'] ?? 0);
        $active  = isset($_POST['is_active']) ? 1 : 0;
        if ($name_en !== '') {
            db()->prepare('UPDATE blog_categories SET name_en=?, name_fa=?, sort_order=?, is_active=? WHERE id=?')
                ->execute([$name_en, $name_fa, $sort, $active, $id]);
            flash_set('Category updated.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM blog_categories WHERE id = ?')->execute([$id]);
        flash_set('Category deleted. Its posts are kept (uncategorized).');
    }
    redirect('admin/blog-categories.php');
}

$categories = get_blog_categories();

$pageHeading = 'Blog categories';
require __DIR__ . '/_header.php';
?>

<p class="muted"><a href="<?= e(url('admin/blog.php')) ?>">← Back to blog</a></p>

<div class="panel">
    <h2>Add category</h2>
    <form method="post" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="field-grid">
            <label>Name (English)
                <input type="text" name="name_en" required placeholder="e.g. Client Stories">
            </label>
            <label>Name (Persian)
                <input type="text" name="name_fa" dir="rtl" placeholder="داستان مشتریان">
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="0">
            </label>
        </div>
        <button type="submit" class="btn btn-primary btn-inline">Add category</button>
    </form>
</div>

<div class="panel">
    <h2>Existing categories</h2>
    <?php if (!$categories): ?>
        <p class="muted">No categories yet. Add one above.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Name (EN)</th><th>Name (FA)</th><th>Sort</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <td><input type="text" name="name_en" value="<?= e($c['name_en']) ?>" required></td>
                            <td><input type="text" name="name_fa" value="<?= e($c['name_fa']) ?>" dir="rtl"></td>
                            <td class="col-narrow"><input type="number" name="sort_order" value="<?= (int) $c['sort_order'] ?>"></td>
                            <td><label class="checkbox"><input type="checkbox" name="is_active" value="1" <?= (int) $c['is_active'] ? 'checked' : '' ?>> Active</label></td>
                            <td class="ta-right nowrap">
                                <button type="submit" class="btn btn-ghost">Save</button>
                        </form>
                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this category?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button type="submit" class="btn btn-ghost btn-danger">Delete</button>
                        </form>
                            </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
