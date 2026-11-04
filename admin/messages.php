<?php
/**
 * Admin — Contact messages & Consultation Bookings inbox.
 * Filter by All / Consultations / General Enquiries / Unread.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

$filter = trim((string) ($_GET['filter'] ?? 'all'));
if (!in_array($filter, ['all', 'consultations', 'enquiries', 'unread'], true)) {
    $filter = 'all';
}

// Handle actions (POST + CSRF): delete, mark read, mark unread.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('Security token invalid. Please try again.', 'error');
        redirect('admin/messages.php?filter=' . urlencode($filter));
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
    redirect('admin/messages.php?filter=' . urlencode($filter));
}

$allMessages = db()->query(
    'SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC'
)->fetchAll();

$counts = [
    'all' => count($allMessages),
    'consultations' => 0,
    'enquiries' => 0,
    'unread' => 0,
];

$messages = [];
foreach ($allMessages as $m) {
    $isConsultation = str_starts_with((string) ($m['goal'] ?? ''), 'Free Consultation');
    if ($isConsultation) {
        $counts['consultations']++;
    } else {
        $counts['enquiries']++;
    }
    if ((int) $m['is_read'] === 0) {
        $counts['unread']++;
    }

    if ($filter === 'consultations' && !$isConsultation) {
        continue;
    }
    if ($filter === 'enquiries' && $isConsultation) {
        continue;
    }
    if ($filter === 'unread' && (int) $m['is_read'] !== 0) {
        continue;
    }
    $messages[] = $m;
}

$pageHeading = 'Messages & Consultations';
require __DIR__ . '/_header.php';
?>

<div class="page-actions">
    <p class="muted">Consultation bookings and enquiries from your website.</p>
    <div class="nowrap">
        <a class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-ghost' ?> btn-inline" href="<?= e(url('admin/messages.php?filter=all')) ?>">All (<?= $counts['all'] ?>)</a>
        <a class="btn <?= $filter === 'consultations' ? 'btn-primary' : 'btn-ghost' ?> btn-inline" href="<?= e(url('admin/messages.php?filter=consultations')) ?>">Consultations (<?= $counts['consultations'] ?>)</a>
        <a class="btn <?= $filter === 'enquiries' ? 'btn-primary' : 'btn-ghost' ?> btn-inline" href="<?= e(url('admin/messages.php?filter=enquiries')) ?>">Enquiries (<?= $counts['enquiries'] ?>)</a>
        <a class="btn <?= $filter === 'unread' ? 'btn-primary' : 'btn-ghost' ?> btn-inline" href="<?= e(url('admin/messages.php?filter=unread')) ?>">Unread (<?= $counts['unread'] ?>)</a>
    </div>
</div>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr>
                <th></th>
                <th>Type</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Goal / Slot</th>
                <th>Received</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $m): ?>
                <?php
                $isConsultation = str_starts_with((string) ($m['goal'] ?? ''), 'Free Consultation');
                $waDigits = preg_replace('/[^0-9]/', '', (string) ($m['phone'] ?? ''));
                ?>
                <tr class="<?= (int) $m['is_read'] === 0 ? 'row-unread' : '' ?>">
                    <td>
                        <?php if ((int) $m['is_read'] === 0): ?>
                            <span class="dot-unread" title="Unread"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($isConsultation): ?>
                            <span class="badge badge-on">Consultation</span>
                        <?php else: ?>
                            <span class="badge badge-off">Enquiry</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= e(url('admin/message-view.php?id=' . (int) $m['id'])) ?>">
                            <strong><?= e($m['name']) ?></strong>
                        </a>
                    </td>
                    <td class="muted">
                        <div><?= e($m['email']) ?></div>
                        <?php if (!empty($m['phone'])): ?>
                            <small><?= e($m['phone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="muted"><?= e($m['goal'] !== '' ? $m['goal'] : '—') ?></td>
                    <td class="muted"><?= e(date('d M Y, H:i', strtotime($m['created_at']))) ?></td>
                    <td class="ta-right nowrap">
                        <?php if ($waDigits !== ''): ?>
                            <a class="btn btn-ghost" href="https://wa.me/<?= e($waDigits) ?>" target="_blank" rel="noopener" title="Chat on WhatsApp">WhatsApp</a>
                        <?php endif; ?>
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
                <tr><td colspan="7" class="muted">No messages found for this filter.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
