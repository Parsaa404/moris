<?php
/**
 * Admin Dashboard — Executive Control Center
 * Organized into 4 cohesive, visually distinct, connected sections:
 *   Section 01: Live Website KPIs & Quick Creation Toolbar (Unified 4-Column Grid)
 *   Section 02: Website Pages & Builders Quick Matrix (All 7 Pages + SEO Status)
 *   Section 03: Recent Client Enquiries & Consultation Requests
 *   Section 04: Floating WhatsApp Button Studio
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/page_cms.php';

require_admin();
$admin = current_admin();

ensure_subpages_seeded();

// Handle Quick WhatsApp Customizer Save directly from Dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_whatsapp_widget') {
    if (!csrf_check()) {
        flash_set('Security token invalid. Please try again.', 'error');
        redirect('admin/index.php');
    }

    $waEnabled  = isset($_POST['wa_enabled']) ? '1' : '0';
    $waNumber   = trim((string) ($_POST['contact_whatsapp'] ?? ''));
    $waStyle    = in_array($_POST['wa_style'] ?? '', ['pill', 'circle', 'badge'], true) ? $_POST['wa_style'] : 'pill';
    $waColor    = in_array($_POST['wa_color'] ?? '', ['emerald', 'vibrant', 'cyan', 'obsidian'], true) ? $_POST['wa_color'] : 'emerald';
    $waPosition = in_array($_POST['wa_position'] ?? '', ['auto', 'right', 'left'], true) ? $_POST['wa_position'] : 'auto';
    $waPulse    = isset($_POST['wa_pulse']) ? '1' : '0';
    $waLabelEn  = trim((string) ($_POST['wa_label_en'] ?? 'Chat on WhatsApp'));
    $waLabelFa  = trim((string) ($_POST['wa_label_fa'] ?? 'چت در واتساپ'));
    $waMsgEn    = trim((string) ($_POST['wa_msg_en'] ?? 'Hi Coach Mori, I would like to inquire about personal training.'));
    $waMsgFa    = trim((string) ($_POST['wa_msg_fa'] ?? 'سلام مربی موری، برای مشاوره و شروع تمرینات پیام می‌دهم.'));

    set_setting('wa_enabled', $waEnabled);
    set_setting('contact_whatsapp', $waNumber);
    set_setting('wa_style', $waStyle);
    set_setting('wa_color', $waColor);
    set_setting('wa_position', $waPosition);
    set_setting('wa_pulse', $waPulse);
    set_setting('wa_label_en', $waLabelEn);
    set_setting('wa_label_fa', $waLabelFa);
    set_setting('wa_msg_en', $waMsgEn);
    set_setting('wa_msg_fa', $waMsgFa);

    flash_set('WhatsApp button settings saved and updated on the live website.');
    redirect('admin/index.php#whatsapp-studio');
}

function count_rows(string $sql): int
{
    try {
        return (int) db()->query($sql)->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

$stats = [
    'active_sections' => count_rows('SELECT COUNT(*) FROM homepage_sections WHERE is_active = 1'),
    'unread_messages' => count_rows('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'),
    'consultations'   => count_rows("SELECT COUNT(*) FROM contact_messages WHERE goal LIKE 'Free Consultation%'"),
    'total_messages'  => count_rows('SELECT COUNT(*) FROM contact_messages'),
    'published_posts' => count_rows('SELECT COUNT(*) FROM blog_posts WHERE is_published = 1'),
    'total_posts'     => count_rows('SELECT COUNT(*) FROM blog_posts'),
    'packages'        => count_rows('SELECT COUNT(*) FROM packages WHERE is_active = 1'),
    'gallery'         => count_rows('SELECT COUNT(*) FROM gallery_items'),
];

$recentMessages = [];
try {
    $recentMessages = db()->query(
        'SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC LIMIT 5'
    )->fetchAll();
} catch (Throwable) {
    $recentMessages = [];
}

$waCfg = [
    'enabled'  => setting('wa_enabled', '1'),
    'number'   => setting('contact_whatsapp', '+60 12-345 6789'),
    'style'    => setting('wa_style', 'pill'),
    'color'    => setting('wa_color', 'emerald'),
    'position' => setting('wa_position', 'auto'),
    'pulse'    => setting('wa_pulse', '1'),
    'label_en' => setting('wa_label_en', 'Chat on WhatsApp'),
    'label_fa' => setting('wa_label_fa', 'چت در واتساپ'),
    'msg_en'   => setting('wa_msg_en', 'Hi Coach Mori, I would like to inquire about personal training.'),
    'msg_fa'   => setting('wa_msg_fa', 'سلام مربی موری، برای مشاوره و شروع تمرینات پیام می‌دهم.'),
];

$dashPages = [
    ['num' => '01', 'en' => 'Homepage (Landing Page)', 'fa' => 'صفحه اصلی سایت',       'route' => '/en/ & /fa/',     'builder' => 'admin/homepage.php',               'seo' => 'admin/settings.php#seo-settings', 'live_en' => 'en/',             'live_fa' => 'fa/'],
    ['num' => '02', 'en' => 'About Coach Mori',        'fa' => 'درباره مربی موری',     'route' => '/about.php',      'builder' => 'admin/page-edit.php?slug=about',    'seo' => 'admin/page-edit.php?slug=about&tab=seo', 'live_en' => 'en/about.php',    'live_fa' => 'fa/about.php'],
    ['num' => '03', 'en' => 'Coaching Services',       'fa' => 'خدمات مربی‌گری',       'route' => '/services.php',   'builder' => 'admin/page-edit.php?slug=services', 'seo' => 'admin/page-edit.php?slug=services&tab=seo', 'live_en' => 'en/services.php', 'live_fa' => 'fa/services.php'],
    ['num' => '04', 'en' => 'Packages & Pricing',      'fa' => 'پکیج‌های تمرینی',       'route' => '/packages.php',   'builder' => 'admin/page-edit.php?slug=packages', 'seo' => 'admin/page-edit.php?slug=packages&tab=seo', 'live_en' => 'en/packages.php', 'live_fa' => 'fa/packages.php'],
    ['num' => '05', 'en' => 'Gallery & Results',       'fa' => 'گالری و نتایج شاگردان', 'route' => '/gallery.php',    'builder' => 'admin/page-edit.php?slug=gallery',  'seo' => 'admin/page-edit.php?slug=gallery&tab=seo', 'live_en' => 'en/gallery.php',  'live_fa' => 'fa/gallery.php'],
    ['num' => '06', 'en' => 'Blog & Articles',         'fa' => 'وبلاگ و مقالات آموزشی', 'route' => '/blog.php',       'builder' => 'admin/page-edit.php?slug=blog',     'seo' => 'admin/page-edit.php?slug=blog&tab=seo', 'live_en' => 'en/blog.php',     'live_fa' => 'fa/blog.php'],
    ['num' => '07', 'en' => 'Contact & Booking',       'fa' => 'تماس و رزرو مشاوره',    'route' => '/contact.php',    'builder' => 'admin/page-edit.php?slug=contact',  'seo' => 'admin/page-edit.php?slug=contact&tab=seo', 'live_en' => 'en/contact.php',  'live_fa' => 'fa/contact.php'],
];

$pageHeading = 'Dashboard Control Center';
require __DIR__ . '/_header.php';
?>

<!-- =========================================================================
     EXECUTIVE COMMAND BANNER
     ========================================================================= -->
<div class="adm-cmd-banner">
    <div class="adm-cmd-left">
        <div class="adm-cmd-status">
            <span class="adm-cmd-dot"></span>
            <span>Bilingual Website Online (EN &amp; FA)</span>
        </div>
        <h2>Welcome back, <?= e($admin['username']) ?></h2>
        <p>Manage all 7 website pages, per-page Google SEO, training packages, client enquiries, and floating WhatsApp widget from one unified workspace.</p>
    </div>
    <div class="adm-cmd-right">
        <a class="adm-cmd-btn is-primary" href="<?= e(url('admin/pages.php')) ?>">Website Pages &amp; Builders →</a>
        <a class="adm-cmd-btn" href="<?= e(url('admin/homepage.php')) ?>">Homepage Studio</a>
        <a class="adm-cmd-btn" href="<?= e(url('admin/settings.php#seo-settings')) ?>">Global SEO</a>
    </div>
</div>

<!-- =========================================================================
     SECTION 01: LIVE METRICS & QUICK CREATION TOOLBAR (4-COLUMN ALIGNED)
     ========================================================================= -->
<section class="adm-section-group">
    <div class="adm-group-header">
        <div class="adm-group-title-wrap">
            <span class="adm-group-num">Section 01</span>
            <div>
                <h2 class="adm-group-title">Website Overview, Metrics &amp; Quick Actions</h2>
                <p class="adm-group-desc">Real-time activity across client messages, consultation requests, packages, and media.</p>
            </div>
        </div>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/settings.php')) ?>">Site Settings →</a>
    </div>

    <!-- 4-Column KPI Grid -->
    <div class="adm-kpi-grid">
        <a class="adm-kpi-card" href="<?= e(url('admin/messages.php?filter=unread')) ?>">
            <div class="adm-kpi-top">
                <span class="adm-kpi-label">Client Enquiries</span>
                <?php if ($stats['unread_messages'] > 0): ?>
                    <span class="badge badge-on"><?= (int) $stats['unread_messages'] ?> New</span>
                <?php else: ?>
                    <span class="badge badge-off">All Read</span>
                <?php endif; ?>
            </div>
            <div class="adm-kpi-val"><?= (int) $stats['unread_messages'] ?> <small>/ <?= (int) $stats['total_messages'] ?> total</small></div>
            <div class="adm-kpi-foot">Open Messages Inbox →</div>
        </a>

        <a class="adm-kpi-card" href="<?= e(url('admin/messages.php?filter=consultations')) ?>">
            <div class="adm-kpi-top">
                <span class="adm-kpi-label">Consultation Bookings</span>
                <span class="badge badge-on">Calendar</span>
            </div>
            <div class="adm-kpi-val"><?= (int) $stats['consultations'] ?> <small>booked</small></div>
            <div class="adm-kpi-foot">View Consultation Requests →</div>
        </a>

        <a class="adm-kpi-card" href="<?= e(url('admin/packages.php')) ?>">
            <div class="adm-kpi-top">
                <span class="adm-kpi-label">Training Packages</span>
                <span class="badge badge-off">Pricing</span>
            </div>
            <div class="adm-kpi-val"><?= (int) $stats['packages'] ?> <small>active plans</small></div>
            <div class="adm-kpi-foot">Manage Packages &amp; Prices →</div>
        </a>

        <a class="adm-kpi-card" href="<?= e(url('admin/blog.php')) ?>">
            <div class="adm-kpi-top">
                <span class="adm-kpi-label">Blog &amp; Gallery Media</span>
                <span class="badge badge-off"><?= (int) $stats['gallery'] ?> Photos</span>
            </div>
            <div class="adm-kpi-val"><?= (int) $stats['published_posts'] ?> <small>articles live</small></div>
            <div class="adm-kpi-foot">Manage Articles &amp; Photos →</div>
        </a>
    </div>

    <!-- 4-Column Quick Action Bar (Aligned with the 4 KPI Columns) -->
    <div class="adm-quick-bar">
        <a class="adm-qaction" href="<?= e(url('admin/package-edit.php')) ?>">
            <span class="adm-qaction-plus">+</span>
            <div>
                <strong>Add Training Package</strong>
                <span>Create new pricing card</span>
            </div>
        </a>
        <a class="adm-qaction" href="<?= e(url('admin/blog-edit.php')) ?>">
            <span class="adm-qaction-plus">+</span>
            <div>
                <strong>Write Blog Article</strong>
                <span>Publish bilingual post</span>
            </div>
        </a>
        <a class="adm-qaction" href="<?= e(url('admin/gallery.php')) ?>">
            <span class="adm-qaction-plus">+</span>
            <div>
                <strong>Upload Gallery Photo</strong>
                <span>Add client transformation</span>
            </div>
        </a>
        <a class="adm-qaction" href="<?= e(url('admin/settings.php#seo-settings')) ?>">
            <span class="adm-qaction-plus">★</span>
            <div>
                <strong>Configure Global SEO</strong>
                <span>Meta tags &amp; Google verify</span>
            </div>
        </a>
    </div>
</section>

<!-- =========================================================================
     SECTION 02: WEBSITE PAGES & BUILDERS MATRIX (ALL 7 PAGES)
     ========================================================================= -->
<section class="adm-section-group accent-blue">
    <div class="adm-group-header">
        <div class="adm-group-title-wrap">
            <span class="adm-group-num">Section 02</span>
            <div>
                <h2 class="adm-group-title">Website Pages &amp; Builders Matrix (7 Pages)</h2>
                <p class="adm-group-desc">Direct access to every page builder, per-page SEO configuration, and live English/Persian previews.</p>
            </div>
        </div>
        <a class="btn btn-primary btn-inline btn-sm" href="<?= e(url('admin/pages.php')) ?>">Open Full Pages Hub →</a>
    </div>

    <div class="dash-table-wrap">
        <table class="data-table adm-matrix-table">
            <thead>
                <tr>
                    <th style="width:48px;">#</th>
                    <th>Page Name (EN / FA)</th>
                    <th>Route</th>
                    <th>Status</th>
                    <th class="ta-right">Builder, SEO &amp; Live Links</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dashPages as $dp): ?>
                    <tr>
                        <td><span class="adm-pcard-num"><?= e($dp['num']) ?></span></td>
                        <td>
                            <div class="adm-matrix-titles">
                                <strong><?= e($dp['en']) ?></strong>
                                <span dir="rtl"><?= e($dp['fa']) ?></span>
                            </div>
                        </td>
                        <td><code><?= e($dp['route']) ?></code></td>
                        <td><span class="badge badge-on">Live &amp; Indexed</span></td>
                        <td class="ta-right nowrap">
                            <a class="btn btn-primary btn-inline btn-sm" href="<?= e(url($dp['builder'])) ?>">Page Builder</a>
                            <a class="btn btn-ghost btn-sm" href="<?= e(url($dp['seo'])) ?>">SEO</a>
                            <a class="adm-live-pill" href="<?= e(url($dp['live_en'])) ?>" target="_blank">EN ↗</a>
                            <a class="adm-live-pill" href="<?= e(url($dp['live_fa'])) ?>" target="_blank">FA ↗</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- =========================================================================
     SECTION 03: RECENT CLIENT ENQUIRIES & CONSULTATIONS
     ========================================================================= -->
<section class="adm-section-group accent-indigo">
    <div class="adm-group-header">
        <div class="adm-group-title-wrap">
            <span class="adm-group-num">Section 03</span>
            <div>
                <h2 class="adm-group-title">Recent Client Enquiries &amp; Consultation Requests</h2>
                <p class="adm-group-desc">The latest 5 messages submitted through your Contact page and Homepage booking calendar.</p>
            </div>
        </div>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/messages.php')) ?>">View All Messages (<?= (int) $stats['total_messages'] ?>) →</a>
    </div>

    <?php if (!$recentMessages): ?>
        <div class="dash-empty-box">
            No messages yet. New consultation requests and contact enquiries will appear here automatically.
        </div>
    <?php else: ?>
        <div class="dash-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:24px;"></th>
                        <th>Type</th>
                        <th>Client Name</th>
                        <th>Email / Phone</th>
                        <th>Goal / Requested Slot</th>
                        <th>Date</th>
                        <th class="ta-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentMessages as $m): ?>
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
                            <td class="muted nowrap"><?= e(date('d M Y, H:i', strtotime($m['created_at']))) ?></td>
                            <td class="ta-right nowrap">
                                <?php if ($waDigits !== ''): ?>
                                    <a class="btn btn-ghost btn-sm" href="https://wa.me/<?= e($waDigits) ?>" target="_blank" rel="noopener">WhatsApp</a>
                                <?php endif; ?>
                                <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/message-view.php?id=' . (int) $m['id'])) ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- =========================================================================
     SECTION 04: WHATSAPP FLOATING BUTTON STUDIO
     ========================================================================= -->
<section class="adm-section-group accent-emerald" id="whatsapp-studio">
    <div class="adm-group-header">
        <div class="adm-group-title-wrap">
            <span class="adm-group-num">Section 04</span>
            <div>
                <h2 class="adm-group-title">WhatsApp Floating Button Studio</h2>
                <p class="adm-group-desc">Customize your WhatsApp number, button shape, color theme, screen position, and default messages.</p>
            </div>
        </div>
        <span class="badge <?= $waCfg['enabled'] === '1' ? 'badge-on' : 'badge-off' ?>">
            <?= $waCfg['enabled'] === '1' ? 'Enabled on Website' : 'Disabled' ?>
        </span>
    </div>

    <form method="post" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_whatsapp_widget">

        <div class="wa-preview-banner">
            <div class="wa-preview-banner-info">
                <strong>Live Interactive Preview</strong>
                <span>Test how the floating WhatsApp widget appears on English &amp; Persian pages.</span>
                <div class="wa-preview-lang-btns" style="margin-top:0.6rem;">
                    <button type="button" class="btn btn-ghost wa-prev-lang is-active" data-lang="en">English Preview</button>
                    <button type="button" class="btn btn-ghost wa-prev-lang" data-lang="fa">پیش‌نمایش فارسی</button>
                </div>
            </div>
            <div class="wa-preview-stage-box">
                <div id="wa-preview-btn" class="wa-prev-widget style-<?= e($waCfg['style']) ?> color-<?= e($waCfg['color']) ?> <?= $waCfg['pulse'] === '1' ? 'has-pulse' : '' ?>">
                    <span class="wa-prev-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                            <path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.8 1-.3.2-.5.1a6.5 6.5 0 0 1-3.2-2.8c-.2-.4.2-.4.6-1.2.1-.2 0-.3 0-.5s-.5-1.3-.7-1.8-.4-.4-.5-.4h-.5a1 1 0 0 0-.7.3A3 3 0 0 0 6.3 9c0 1.8 1.3 3.5 1.5 3.7s2.5 3.9 6.1 5.2c2.2.8 2.6.6 3.1.6s1.4-.6 1.6-1.1.2-1 .1-1.1-.3-.2-.5-.3z"/>
                        </svg>
                        <span class="wa-prev-online-dot"></span>
                    </span>
                    <span class="wa-prev-copy">
                        <small class="wa-prev-sub" id="wa-prev-sub">Online · Fast Reply</small>
                        <strong class="wa-prev-label" id="wa-prev-label"><?= e($waCfg['label_en']) ?></strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="wa-toggles-bar">
            <label class="checkbox" style="margin:0;">
                <input type="checkbox" name="wa_enabled" id="wa-inp-enabled" value="1" <?= $waCfg['enabled'] === '1' ? 'checked' : '' ?>>
                <strong>Show Floating WhatsApp Button on Website</strong>
            </label>
            <label class="checkbox" style="margin:0;">
                <input type="checkbox" name="wa_pulse" id="wa-inp-pulse" value="1" <?= $waCfg['pulse'] === '1' ? 'checked' : '' ?>>
                Enable Pulse Ring Animation
            </label>
        </div>

        <div class="wa-options-grid">
            <label>WhatsApp Number
                <input type="text" name="contact_whatsapp" id="wa-inp-number" value="<?= e($waCfg['number']) ?>" placeholder="+60 12-345 6789">
            </label>

            <label>Button Shape / Style
                <select name="wa_style" id="wa-inp-style">
                    <option value="pill" <?= $waCfg['style'] === 'pill' ? 'selected' : '' ?>>Pill Button (Icon + Label)</option>
                    <option value="badge" <?= $waCfg['style'] === 'badge' ? 'selected' : '' ?>>Coach Online Badge (Live Dot)</option>
                    <option value="circle" <?= $waCfg['style'] === 'circle' ? 'selected' : '' ?>>Minimal Circle (Icon Only)</option>
                </select>
            </label>

            <label>Color Theme
                <select name="wa_color" id="wa-inp-color">
                    <option value="emerald" <?= $waCfg['color'] === 'emerald' ? 'selected' : '' ?>>WhatsApp Emerald (#128c7e)</option>
                    <option value="vibrant" <?= $waCfg['color'] === 'vibrant' ? 'selected' : '' ?>>Vibrant Green (#25d366)</option>
                    <option value="cyan" <?= $waCfg['color'] === 'cyan' ? 'selected' : '' ?>>Moris Brand Cyan (#00856f)</option>
                    <option value="obsidian" <?= $waCfg['color'] === 'obsidian' ? 'selected' : '' ?>>Stealth Obsidian Dark (#141414)</option>
                </select>
            </label>

            <label>Screen Position
                <select name="wa_position" id="wa-inp-pos">
                    <option value="auto" <?= $waCfg['position'] === 'auto' ? 'selected' : '' ?>>Auto (Right in EN / Left in FA)</option>
                    <option value="right" <?= $waCfg['position'] === 'right' ? 'selected' : '' ?>>Always Bottom-Right</option>
                    <option value="left" <?= $waCfg['position'] === 'left' ? 'selected' : '' ?>>Always Bottom-Left</option>
                </select>
            </label>
        </div>

        <div class="lang-cols" style="margin-top:0.75rem;">
            <div class="lang-col">
                <h3 class="lang-title">English Texts</h3>
                <label>Button Label (EN)
                    <input type="text" name="wa_label_en" id="wa-inp-label-en" value="<?= e($waCfg['label_en']) ?>" placeholder="Chat on WhatsApp">
                </label>
                <label>Pre-filled Greeting Message (EN)
                    <input type="text" name="wa_msg_en" value="<?= e($waCfg['msg_en']) ?>">
                </label>
            </div>
            <div class="lang-col">
                <h3 class="lang-title">متن‌های دکمه واتساپ (فارسی)</h3>
                <label>متن روی دکمه (FA)
                    <input type="text" name="wa_label_fa" id="wa-inp-label-fa" value="<?= e($waCfg['label_fa']) ?>" dir="rtl" placeholder="چت در واتساپ">
                </label>
                <label>پیام پیش‌فرض واتساپ (FA)
                    <input type="text" name="wa_msg_fa" value="<?= e($waCfg['msg_fa']) ?>" dir="rtl">
                </label>
            </div>
        </div>

        <div class="form-actions" style="display:flex; justify-content:flex-end;">
            <button type="submit" class="btn btn-primary btn-inline">Save WhatsApp Settings</button>
        </div>
    </form>
</section>

<script>
(function() {
    var styleSel = document.getElementById('wa-inp-style');
    var colorSel = document.getElementById('wa-inp-color');
    var pulseChk = document.getElementById('wa-inp-pulse');
    var enableChk = document.getElementById('wa-inp-enabled');
    var labelEnInp = document.getElementById('wa-inp-label-en');
    var labelFaInp = document.getElementById('wa-inp-label-fa');
    var prevBtn = document.getElementById('wa-preview-btn');
    var prevLabel = document.getElementById('wa-prev-label');
    var prevSub = document.getElementById('wa-prev-sub');
    var langBtns = document.querySelectorAll('.wa-prev-lang');
    var currentLang = 'en';

    function updatePreview() {
        if (!prevBtn) return;
        prevBtn.className = 'wa-prev-widget style-' + styleSel.value + ' color-' + colorSel.value + (pulseChk.checked ? ' has-pulse' : '');
        prevBtn.style.opacity = enableChk.checked ? '1' : '0.35';
        prevLabel.textContent = currentLang === 'fa' ? (labelFaInp.value || 'چت در واتساپ') : (labelEnInp.value || 'Chat on WhatsApp');
        prevSub.textContent = currentLang === 'fa' ? 'آنلاین · پاسخ سریع' : 'Online · Fast Reply';
    }

    [styleSel, colorSel, pulseChk, enableChk, labelEnInp, labelFaInp].forEach(function(el) {
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });

    langBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            langBtns.forEach(function(b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            currentLang = btn.getAttribute('data-lang') || 'en';
            updatePreview();
        });
    });
})();
</script>

<?php require __DIR__ . '/_footer.php'; ?>
