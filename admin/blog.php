<?php
/**
 * Admin — Blog posts list (+ publish toggle, delete).
 * Create/edit in blog-edit.php.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('Security token invalid. Please try again.', 'error');
        redirect('admin/blog.php');
    }
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete') {
        $row = db()->prepare('SELECT featured_image FROM blog_posts WHERE id = ?');
        $row->execute([$id]);
        $post = $row->fetch();
        db()->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$id]);
        if ($post) {
            delete_upload($post['featured_image']);
        }
        flash_set('Post deleted.');
    } elseif ($action === 'publish') {
        db()->prepare('UPDATE blog_posts SET is_published = 1, published_at = COALESCE(published_at, NOW()) WHERE id = ?')->execute([$id]);
        flash_set('Post published.');
    } elseif ($action === 'unpublish') {
        db()->prepare('UPDATE blog_posts SET is_published = 0 WHERE id = ?')->execute([$id]);
        flash_set('Post moved to draft.');
    }
    redirect('admin/blog.php');
}

$posts = db()->query(
    'SELECT p.*, c.name_en AS cat_en
     FROM blog_posts p
     LEFT JOIN blog_categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC, p.id DESC'
)->fetchAll();

$pageHeading = 'Blog';
require __DIR__ . '/_header.php';
?>

<div class="page-actions">
    <p class="muted">Write and manage your blog posts.</p>
    <span class="nowrap">
        <a class="btn btn-ghost btn-inline" href="<?= e(url('admin/blog-categories.php')) ?>">Manage categories</a>
        <a class="btn btn-primary btn-inline" href="<?= e(url('admin/blog-edit.php')) ?>">+ New post</a>
    </span>
</div>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr><th>Title (EN)</th><th>Category</th><th>Status</th><th>Date</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $p): ?>
                <tr>
                    <td><strong><?= e($p['title_en']) ?></strong></td>
                    <td class="muted"><?= e($p['cat_en'] ?? '—') ?></td>
                    <td>
                        <?php if ((int) $p['is_published'] === 1): ?>
                            <span class="badge badge-on">Published</span>
                        <?php else: ?>
                            <span class="badge badge-off">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="muted"><?= e(date('d M Y', strtotime($p['created_at']))) ?></td>
                    <td class="ta-right nowrap">
                        <a class="btn btn-ghost" href="<?= e(url('admin/blog-edit.php?id=' . (int) $p['id'])) ?>">Edit</a>
                        <form method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="<?= (int) $p['is_published'] === 1 ? 'unpublish' : 'publish' ?>">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="btn btn-ghost"><?= (int) $p['is_published'] === 1 ? 'Unpublish' : 'Publish' ?></button>
                        </form>
                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this post?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="btn btn-ghost btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$posts): ?>
                <tr><td colspan="5" class="muted">No posts yet. Click “New post” to write your first one.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
