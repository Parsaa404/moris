<?php
/**
 * Admin — Packages list + delete.
 * Add/edit happen in package-edit.php.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

// Handle delete (POST + CSRF).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_check()) {
        flash_set('Security token invalid. Please try again.', 'error');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('DELETE FROM packages WHERE id = ?');
        $stmt->execute([$id]);
        flash_set('Package deleted.');
    }
    redirect('admin/packages.php');
}

$packages = db()->query('SELECT * FROM packages ORDER BY sort_order, id')->fetchAll();

$pageHeading = 'Packages';
require __DIR__ . '/_header.php';
?>

<div class="page-actions">
    <p class="muted">Your training packages and prices. These appear on the public Packages page.</p>
    <a class="btn btn-primary btn-inline" href="<?= e(url('admin/package-edit.php')) ?>">+ Add package</a>
</div>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name (EN)</th>
                <th>Price</th>
                <th>Sessions</th>
                <th>Type</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packages as $p): ?>
                <tr>
                    <td class="muted"><?= (int) $p['sort_order'] ?></td>
                    <td><strong><?= e($p['name_en']) ?></strong></td>
                    <td><?= e(format_price((float) $p['price'], $p['currency'])) ?></td>
                    <td class="muted"><?= $p['sessions_count'] !== null ? (int) $p['sessions_count'] : '—' ?></td>
                    <td class="muted">
                        <?php
                        $types = [];
                        if ((int) $p['includes_weights']) { $types[] = 'Weights'; }
                        if ((int) $p['includes_boxing']) { $types[] = 'Boxing'; }
                        if ((int) $p['is_online']) { $types[] = 'Online'; }
                        echo e($types ? implode(', ', $types) : '—');
                        ?>
                    </td>
                    <td>
                        <?php if ((int) $p['is_active'] === 1): ?>
                            <span class="badge badge-on">Active</span>
                        <?php else: ?>
                            <span class="badge badge-off">Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td class="ta-right nowrap">
                        <a class="btn btn-ghost" href="<?= e(url('admin/package-edit.php?id=' . (int) $p['id'])) ?>">Edit</a>
                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this package? This cannot be undone.');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="btn btn-ghost btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$packages): ?>
                <tr><td colspan="7" class="muted">No packages yet. Click “Add package” to create your first one.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
