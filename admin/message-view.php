<?php
/**
 * Admin — view a single contact message (marks it as read).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM contact_messages WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$msg = $stmt->fetch();

if (!$msg) {
    flash_set('That message was not found.', 'error');
    redirect('admin/messages.php');
}

// Mark as read on open.
if ((int) $msg['is_read'] === 0) {
    db()->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    $msg['is_read'] = 1;
}

$mailtoSubject = rawurlencode('Re: your enquiry — ' . SITE_NAME);

$pageHeading = 'Message from ' . $msg['name'];
require __DIR__ . '/_header.php';
?>

<p class="muted"><a href="<?= e(url('admin/messages.php')) ?>">← Back to messages</a></p>

<div class="panel message-detail">
    <dl class="msg-meta">
        <div><dt>Name</dt><dd><?= e($msg['name']) ?></dd></div>
        <div><dt>Email</dt><dd><a href="mailto:<?= e($msg['email']) ?>?subject=<?= $mailtoSubject ?>"><?= e($msg['email']) ?></a></dd></div>
        <div><dt>Phone</dt><dd><?= e($msg['phone'] !== '' ? $msg['phone'] : '—') ?></dd></div>
        <div><dt>Goal</dt><dd><?= e($msg['goal'] !== '' ? $msg['goal'] : '—') ?></dd></div>
        <div><dt>Received</dt><dd><?= e(date('d M Y, H:i', strtotime($msg['created_at']))) ?></dd></div>
    </dl>

    <h2 class="msg-heading">Message</h2>
    <div class="msg-body"><?= nl2br(e($msg['message'])) ?></div>

    <div class="form-actions">
        <a class="btn btn-primary btn-inline" href="mailto:<?= e($msg['email']) ?>?subject=<?= $mailtoSubject ?>">Reply by email</a>
        <form method="post" action="<?= e(url('admin/messages.php')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="unread">
            <input type="hidden" name="id" value="<?= (int) $msg['id'] ?>">
            <button type="submit" class="btn btn-ghost">Mark unread</button>
        </form>
        <form method="post" action="<?= e(url('admin/messages.php')) ?>" class="inline-form" onsubmit="return confirm('Delete this message?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $msg['id'] ?>">
            <button type="submit" class="btn btn-ghost btn-danger">Delete</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
