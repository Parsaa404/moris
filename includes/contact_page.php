<?php
/**
 * Public Contact page (shared by EN + FA).
 * Clean, light 2-column layout.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/page_cms.php';

public_session();

$activeNav = 'contact';
$currentPage = 'contact.php';
$isFa = ($lang === 'fa');

$pageRow = get_page('contact');
$sp = get_subpage_fields('contact');

$seo = get_subpage_seo($pageRow, $sp, $lang);
$metaDescription = $seo['description'];
$metaKeywords    = $seo['keywords'];
$ogImage         = $seo['og_image'];
$robotsMeta      = $seo['robots'];

// Localized labels.
$t = $isFa ? [
    'heading'  => $pageRow ? lang_field($pageRow, 'title', 'fa') : 'تماس و ثبت‌نام تمرین',
    'name'     => 'نام و نام خانوادگی',
    'email'    => 'آدرس ایمیل',
    'phone'    => 'شماره تماس / واتساپ',
    'goal'     => 'هدف تمرینی یا پکیج انتخابی',
    'message'  => 'توضیحات، سوابق تمرینی یا سوالات شما',
    'send'     => 'ارسال درخواست و پیام',
    'success'  => 'پیام شما با موفقیت ثبت شد! مربی موری در کوتاه‌ترین زمان با شما تماس خواهد گرفت.',
    'err_name' => 'لطفاً نام خود را وارد کنید.',
    'err_mail' => 'لطفاً یک ایمیل معتبر وارد کنید.',
    'err_msg'  => 'لطفاً پیام خود را بنویسید.',
    'err_csrf' => 'نشست منقضی شده است. دوباره تلاش کنید.',
    'err_rate' => 'تعداد پیام‌ها زیاد است. کمی بعد دوباره تلاش کنید.',
    'err_len'  => 'پیام خیلی طولانی است.',
    'optional' => '(اختیاری)',
    'goals'    => ['کاهش چربی و تناسب اندام', 'عضله‌سازی و تمرینات قدرتی', 'آموزش بوکس و کاردیو', 'کوچینگ و برنامه آنلاین', 'سایر موارد'],
] : [
    'heading'  => $pageRow ? lang_field($pageRow, 'title', 'en') : 'Contact & Book Training',
    'name'     => 'Full Name',
    'email'    => 'Email Address',
    'phone'    => 'Phone / WhatsApp Number',
    'goal'     => 'Primary Goal or Selected Package',
    'message'  => 'Your Message, Training Background, or Questions',
    'send'     => 'Send Enquiry',
    'success'  => 'Your enquiry has been received! Coach Mori will get back to you within 24 hours.',
    'err_name' => 'Please enter your name.',
    'err_mail' => 'Please enter a valid email.',
    'err_msg'  => 'Please write a message.',
    'err_csrf' => 'Your session expired. Please try again.',
    'err_rate' => 'Too many messages. Please wait a few minutes and try again.',
    'err_len'  => 'Your message is too long.',
    'optional' => '(optional)',
    'goals'    => ['Fat Loss & Conditioning', 'Muscle & Strength Building', 'Boxing & Padwork', 'Online Coaching Program', 'Other'],
];

$selectedPackage = mb_substr(trim((string) ($_GET['package'] ?? '')), 0, 150);
$defaultMsg = '';
if ($selectedPackage !== '') {
    $defaultMsg = $isFa
        ? "سلام مربی موری، تمایل دارم در پکیج «{$selectedPackage}» ثبت‌نام کنم."
        : "Hi Coach Mori, I would like to sign up for the \"{$selectedPackage}\" package.";
}

$values = ['name' => '', 'email' => '', 'phone' => '', 'goal' => $selectedPackage, 'message' => $defaultMsg];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/security.php';

    if (!form_check()) {
        $errors[] = $t['err_csrf'];
    } elseif (!rate_limit_allow('contact_' . md5(client_ip()), 5, 600)) {
        $errors[] = $t['err_rate'];
    } else {
        $values['name']    = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 120);
        $values['email']   = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 120);
        $values['phone']   = mb_substr(trim((string) ($_POST['phone'] ?? '')), 0, 30);
        $values['goal']    = mb_substr(trim((string) ($_POST['goal'] ?? '')), 0, 200);
        $values['message'] = trim((string) ($_POST['message'] ?? ''));
        $honeypot          = trim((string) ($_POST['website'] ?? '')); // spam trap

        if ($values['name'] === '') {
            $errors[] = $t['err_name'];
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = $t['err_mail'];
        }
        if ($values['message'] === '') {
            $errors[] = $t['err_msg'];
        } elseif (mb_strlen($values['message']) > 5000) {
            $errors[] = $t['err_len'];
            $values['message'] = mb_substr($values['message'], 0, 5000);
        }

        // If the honeypot is filled, silently accept without saving (bot).
        if (!$errors && $honeypot !== '') {
            $success = true;
        } elseif (!$errors) {
            $stmt = db()->prepare(
                'INSERT INTO contact_messages (name, email, phone, goal, message)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $values['name'], $values['email'], $values['phone'],
                $values['goal'], $values['message'],
            ]);

            send_contact_notification($values); // best-effort email

            $success = true;
            $values = ['name' => '', 'email' => '', 'phone' => '', 'goal' => '', 'message' => '']; // clear
        }
    }
}

$contactPhone = trim((string) setting('contact_phone', ''));
$contactEmail = trim((string) setting('contact_email', ''));
$rawWa = preg_replace('/[^0-9]/', '', (string) setting('contact_whatsapp', ''));
$waNum = strlen($rawWa) > 4 ? $rawWa : '';
$waDefaultMsg = rawurlencode($isFa
    ? (string) setting('wa_msg_fa', 'سلام مربی موری، درباره برنامه‌های تمرینی سوال داشتم.')
    : (string) setting('wa_msg_en', 'Hi Coach Mori, I would like to ask about personal training.'));

$introContent = $pageRow ? lang_field($pageRow, 'content', $lang) : '';
$infoTitle = sp_field($sp, 'info_title', $lang);
$infoSub   = sp_field($sp, 'info_sub', $lang);
$locText   = sp_field($sp, 'location_en', $lang);
$hoursText = sp_field($sp, 'hours_en', $lang);

$pageTitle = $t['heading'] . ' — ' . SITE_NAME;

require __DIR__ . '/public_header.php';
?>
<link rel="stylesheet" href="<?= e(url('assets/css/subpages.css')) ?>">

<div class="sp-page-wrap">
    <?php render_subpage_hero($lang, $sp); ?>

    <section class="sp-section">
        <div class="sp-container">
            <div class="sp-contact-grid">
                <!-- Left Column: Direct Contact Channels -->
                <div>
                    <span class="sp-eyebrow"><?= $isFa ? 'راه‌های ارتباط مستقیم' : 'DIRECT CHANNELS' ?></span>
                    <?php if ($infoTitle !== ''): ?>
                        <h2 class="sp-sec-title" style="margin-bottom:0.5rem;"><?= e($infoTitle) ?></h2>
                    <?php endif; ?>
                    <?php if ($infoSub !== ''): ?>
                        <p class="sp-sec-sub"><?= e($infoSub) ?></p>
                    <?php endif; ?>

                    <?php if (trim(strip_tags($introContent)) !== ''): ?>
                        <div class="content" style="margin-top:0.85rem; color:#475569;"><?= $introContent ?></div>
                    <?php endif; ?>

                    <div class="sp-channel-list">
                        <?php if ($waNum !== ''): ?>
                        <a href="https://wa.me/<?= e($waNum) ?>?text=<?= $waDefaultMsg ?>" target="_blank" rel="noopener" class="sp-channel-item is-wa">
                            <div class="sp-channel-icon" aria-hidden="true">
                                <?= render_chat_icon('whatsapp') ?>
                            </div>
                            <div class="sp-channel-body">
                                <div class="sp-channel-lbl"><?= $isFa ? 'واتساپ مستقیم · پاسخ سریع' : 'WhatsApp Direct · Instant Chat' ?></div>
                                <div class="sp-channel-val" dir="ltr">+<?= e($waNum) ?></div>
                            </div>
                            <span class="sp-channel-arrow" aria-hidden="true"><?= $isFa ? '←' : '↗' ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if ($contactPhone !== ''): ?>
                        <a href="tel:<?= e(preg_replace('/\s+/', '', $contactPhone)) ?>" class="sp-channel-item">
                            <div class="sp-channel-icon" aria-hidden="true">
                                <?= render_chat_icon('phone') ?>
                            </div>
                            <div class="sp-channel-body">
                                <div class="sp-channel-lbl"><?= $isFa ? 'تماس تلفنی' : 'Phone Line' ?></div>
                                <div class="sp-channel-val" dir="ltr"><?= e($contactPhone) ?></div>
                            </div>
                            <span class="sp-channel-arrow" aria-hidden="true"><?= $isFa ? '←' : '↗' ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if ($contactEmail !== ''): ?>
                        <a href="mailto:<?= e($contactEmail) ?>" class="sp-channel-item">
                            <div class="sp-channel-icon" aria-hidden="true">
                                <?= render_chat_icon('mail') ?>
                            </div>
                            <div class="sp-channel-body">
                                <div class="sp-channel-lbl"><?= $isFa ? 'پست الکترونیک (ایمیل)' : 'Email Address' ?></div>
                                <div class="sp-channel-val" dir="ltr"><?= e($contactEmail) ?></div>
                            </div>
                            <span class="sp-channel-arrow" aria-hidden="true"><?= $isFa ? '←' : '↗' ?></span>
                        </a>
                        <?php endif; ?>

                        <?php if ($locText !== ''): ?>
                        <div class="sp-channel-item">
                            <div class="sp-channel-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div class="sp-channel-body">
                                <div class="sp-channel-lbl"><?= $isFa ? 'محل برگزاری جلسات' : 'Training Location' ?></div>
                                <div class="sp-channel-val"><?= e($locText) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($hoursText !== ''): ?>
                        <div class="sp-channel-item">
                            <div class="sp-channel-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div class="sp-channel-body">
                                <div class="sp-channel-lbl"><?= $isFa ? 'ساعات مربی‌گری' : 'Coaching Hours' ?></div>
                                <div class="sp-channel-val"><?= e($hoursText) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Clean Contact Form Card -->
                <div class="sp-contact-card">
                    <h3><?= $isFa ? 'ارسال پیام یا ثبت‌نام پکیج' : 'Send a Message or Package Request' ?></h3>

                    <?php if ($success): ?>
                        <div class="form-success" style="margin-top:0.75rem;"><?= e($t['success']) ?></div>
                    <?php else: ?>
                        <?php if ($errors): ?>
                            <div class="form-error" style="margin-top:0.75rem;">
                                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="contact-form" style="max-width:100%;" novalidate>
                            <?= form_field() ?>

                            <div class="hp-field" aria-hidden="true">
                                <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                            </div>

                            <div class="sp-form-row-2">
                                <label><?= e($t['name']) ?> *
                                    <input type="text" name="name" value="<?= e($values['name']) ?>" required>
                                </label>
                                <label><?= e($t['email']) ?> *
                                    <input type="email" name="email" value="<?= e($values['email']) ?>" required>
                                </label>
                            </div>

                            <div class="sp-form-row-2">
                                <label><?= e($t['phone']) ?> <span class="muted"><?= e($t['optional']) ?></span>
                                    <input type="text" name="phone" value="<?= e($values['phone']) ?>" placeholder="+60...">
                                </label>
                                <label><?= e($t['goal']) ?> <span class="muted"><?= e($t['optional']) ?></span>
                                    <select name="goal">
                                        <option value=""><?= $isFa ? '— انتخاب کنید —' : '— Select Goal / Package —' ?></option>
                                        <?php if ($selectedPackage !== '' && !in_array($selectedPackage, $t['goals'], true)): ?>
                                            <option value="<?= e($selectedPackage) ?>" <?= $values['goal'] === $selectedPackage ? 'selected' : '' ?>><?= e($selectedPackage) ?></option>
                                        <?php endif; ?>
                                        <?php foreach ($t['goals'] as $g): ?>
                                            <option value="<?= e($g) ?>" <?= $values['goal'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>

                            <label><?= e($t['message']) ?> *
                                <textarea name="message" rows="4" required><?= e($values['message']) ?></textarea>
                            </label>

                            <button type="submit" class="sp-btn sp-btn-primary sp-btn-block" style="margin-top:0.25rem;">
                                <?= e($t['send']) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
require __DIR__ . '/public_footer.php';
