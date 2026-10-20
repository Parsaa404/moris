<?php
/**
 * Admin — Pages list.
 * Lists the CMS pages (Home, About, Services) with edit links.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

// Fetch pages in a friendly order (home, about, services, then anything else).
$pages = db()->query(
    "SELECT id, slug, title_en, title_fa, is_active, updated_at
     FROM pages
     ORDER BY FIELD(slug, 'home', 'about', 'services'), id"
)->fetchAll();

// Human-friendly labels for known slugs.
$labels = [
    'home'     => 'Home',
    'about'    => 'About',
    'services' => 'Services',
];

$pageHeading = 'Pages';
require __DIR__ . '/_header.php';
?>

<p class="muted">Edit the text shown on your public pages. Changes are saved to the database and appear on the site.</p>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>Page</th>
                <th>Title (EN)</th>
                <th>Status</th>
                <th>Last updated</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages as $p): ?>
                <tr>
                    <td><strong><?= e($labels[$p['slug']] ?? ucfirst($p['slug'])) ?></strong></td>
                    <td><?= e($p['title_en'] !== '' ? $p['title_en'] : '—') ?></td>
                    <td>
                        <?php if ((int) $p['is_active'] === 1): ?>
                            <span class="badge badge-on">Visible</span>
                        <?php else: ?>
                            <span class="badge badge-off">Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td class="muted"><?= e(date('d M Y, H:i', strtotime($p['updated_at']))) ?></td>
                    <td class="ta-right">
                        <a class="btn btn-ghost" href="<?= e(url('admin/page-edit.php?slug=' . urlencode($p['slug']))) ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$pages): ?>
                <tr><td colspan="5" class="muted">No pages found. Did you import <code>sql/schema.sql</code>?</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
