<?php
/**
 * Public Contact page (shared by EN + FA).
 * Calling file must define: $lang, $dir.
 *
 * Saves the message to contact_messages and emails the owner.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

public_session();

$activeNav = 'contact';
$currentPage = 'contact.php';
$isFa = ($lang === 'fa');

// Localized labels.
$t = $isFa ? [
    'heading'  => 'تماس با ما',
    'intro'    => 'برای شروع تمرین یا سوال، پیام بگذارید. به زودی پاسخ می‌دهم.',
    'name'     => 'نام',
    'email'    => 'ایمیل',
    'phone'    => 'تلفن / واتساپ',
    'goal'     => 'هدف شما',
    'message'  => 'پیام',
    'send'     => 'ارسال پیام',
    'success'  => 'پیام شما ارسال شد. به زودی با شما تماس می‌گیرم.',
    'err_name' => 'لطفاً نام خود را وارد کنید.',
    'err_mail' => 'لطفاً یک ایمیل معتبر وارد کنید.',
    'err_msg'  => 'لطفاً پیام خود را بنویسید.',
    'err_csrf' => 'نشست منقضی شده است. دوباره تلاش کنید.',
    'err_rate' => 'تعداد پیام‌ها زیاد است. کمی بعد دوباره تلاش کنید.',
    'err_len'  => 'پیام خیلی طولانی است.',
    'optional' => '(اختیاری)',
    'goals'    => ['کاهش چربی', 'عضله‌سازی و فرم‌دهی', 'بوکس', 'تمرین آنلاین', 'سایر'],
] : [
    'heading'  => 'Contact',
    'intro'    => 'Send a message to start training or ask a question. I’ll reply soon.',
    'name'     => 'Name',
    'email'    => 'Email',
    'phone'    => 'Phone / WhatsApp',
    'goal'     => 'Your goal',
    'message'  => 'Message',
    'send'     => 'Send message',
    'success'  => 'Your message has been sent. I’ll be in touch soon.',
    'err_name' => 'Please enter your name.',
    'err_mail' => 'Please enter a valid email.',
    'err_msg'  => 'Please write a message.',
    'err_csrf' => 'Your session expired. Please try again.',
    'err_rate' => 'Too many messages. Please wait a few minutes and try again.',
    'err_len'  => 'Your message is too long.',
    'optional' => '(optional)',
    'goals'    => ['Fat loss', 'Muscle & tone', 'Boxing', 'Online coaching', 'Other'],
];

$values = ['name' => '', 'email' => '', 'phone' => '', 'goal' => '', 'message' => ''];
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

$pageTitle = $t['heading'] . ' — ' . SITE_NAME;
require __DIR__ . '/public_header.php';
?>
<section class="page">
    <h1><?= e($t['heading']) ?></h1>
    <p class="tagline"><?= e($t['intro']) ?></p>

    <?php if ($success): ?>
        <div class="form-success"><?= e($t['success']) ?></div>
    <?php else: ?>
        <?php if ($errors): ?>
            <div class="form-error">
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" class="contact-form" novalidate>
            <?= form_field() ?>

            <!-- Honeypot: hidden from humans, bots tend to fill it -->
            <div class="hp-field" aria-hidden="true">
                <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <label><?= e($t['name']) ?>
                <input type="text" name="name" value="<?= e($values['name']) ?>" required>
            </label>
            <label><?= e($t['email']) ?>
                <input type="email" name="email" value="<?= e($values['email']) ?>" required>
            </label>
            <label><?= e($t['phone']) ?> <span class="muted"><?= e($t['optional']) ?></span>
                <input type="text" name="phone" value="<?= e($values['phone']) ?>">
            </label>
            <label><?= e($t['goal']) ?> <span class="muted"><?= e($t['optional']) ?></span>
                <select name="goal">
                    <option value=""></option>
                    <?php foreach ($t['goals'] as $g): ?>
                        <option value="<?= e($g) ?>" <?= $values['goal'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><?= e($t['message']) ?>
                <textarea name="message" rows="6" required><?= e($values['message']) ?></textarea>
            </label>

            <button type="submit" class="btn-cta"><?= e($t['send']) ?></button>
        </form>
    <?php endif; ?>
</section>
<?php
require __DIR__ . '/public_footer.php';
