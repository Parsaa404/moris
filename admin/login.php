<?php
/**
 * Admin login page.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

auth_start();

// Already logged in? Go to dashboard.
if (current_admin() !== null) {
    redirect('admin/index.php');
}

// No admin yet? Send to installer. (-1 = DB error → do not reopen install)
$count = admin_count();
if ($count === 0) {
    redirect('admin/install.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Security token invalid. Please try again.';
    } else {
        $identifier = trim((string) ($_POST['identifier'] ?? ''));
        $password   = (string) ($_POST['password'] ?? '');
        $rlKey = 'login_' . md5(client_ip() . '|' . strtolower($identifier));

        if (!rate_limit_allow($rlKey, 5, 900)) {
            $error = 'Too many login attempts. Please wait 15 minutes and try again.';
        } elseif ($identifier === '' || $password === '') {
            $error = 'Please enter your username/email and password.';
        } elseif (login_attempt($identifier, $password)) {
            redirect('admin/index.php');
        } else {
            $error = 'Incorrect credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in — <?= e(SITE_NAME) ?> Admin</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="admin admin-auth">
<div class="auth-card">
    <h1><?= e(SITE_NAME) ?> <span class="muted">Admin</span></h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label>
            Username or email
            <input type="text" name="identifier" value="<?= e($_POST['identifier'] ?? '') ?>" required autofocus>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-primary">Log in</button>
    </form>
</div>
</body>
</html>
