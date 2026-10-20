<?php
/**
 * Admin — Contact messages inbox.
 * List, mark read/unread, delete. Full text in message-view.php.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

// Handle actions (POST + CSRF): delete, mark read, mark unread.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('Security token invalid. Please try again.', 'error');
        redirect('admin/messages.php');
    }
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete') {
        db()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
        flash_set('Message deleted.');
    } elseif ($action === 'read') {
        db()->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
        flash_set('Marked as read.');
    } elseif ($action === 'unread') {
        db()->prepare('UPDATE contact_messages SET is_read = 0 WHERE id = ?')->execute([$id]);
        flash_set('Marked as unread.');
    }
    redirect('admin/messages.php');
}

$messages = db()->query(
    'SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC'
)->fetchAll();

$pageHeading = 'Messages';
require __DIR__ . '/_header.php';
?>

<p class="muted">Enquiries from your website contact form.</p>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Email</th>
                <th>Goal</th>
                <th>Received</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $m): ?>
                <tr class="<?= (int) $m['is_read'] === 0 ? 'row-unread' : '' ?>">
                    <td>
                        <?php if ((int) $m['is_read'] === 0): ?>
                            <span class="dot-unread" title="Unread"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= e(url('admin/message-view.php?id=' . (int) $m['id'])) ?>">
                            <strong><?= e($m['name']) ?></strong>
                        </a>
                    </td>
                    <td class="muted"><?= e($m['email']) ?></td>
                    <td class="muted"><?= e($m['goal'] !== '' ? $m['goal'] : '—') ?></td>
                    <td class="muted"><?= e(date('d M Y, H:i', strtotime($m['created_at']))) ?></td>
                    <td class="ta-right nowrap">
                        <a class="btn btn-ghost" href="<?= e(url('admin/message-view.php?id=' . (int) $m['id'])) ?>">View</a>
                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this message?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                            <button type="submit" class="btn btn-ghost btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$messages): ?>
                <tr><td colspan="6" class="muted">No messages yet. They'll appear here when someone uses your contact form.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
