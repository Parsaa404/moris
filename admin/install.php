<?php
/**
 * One-time installer — create the FIRST admin account.
 * After an admin exists, this page locks itself.
 * Delete or protect this file on production after use.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

auth_start();

$count = admin_count();
// -1 = database error → keep installer locked (do not allow new admin).
$hasAdmin = $count !== 0;
$installLock = is_file(dirname(__DIR__) . '/includes/.installed');
if ($installLock) {
    $hasAdmin = true;
}

$errors = [];
$done = false;

if (!$hasAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Security token invalid. Please reload and try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        if (mb_strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 10) {
            $errors[] = 'Password must be at least 10 characters.';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must include letters and numbers.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            // Only create if still zero admins (race-safe enough for single hosting).
            if (admin_count() !== 0) {
                $hasAdmin = true;
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = db()->prepare(
                    'INSERT INTO admin_users (username, email, password_hash) VALUES (?, ?, ?)'
                );
                $stmt->execute([$username, $email, $hash]);
                @file_put_contents(dirname(__DIR__) . '/includes/.installed', date('c') . "\n", LOCK_EX);
                $done = true;
                $hasAdmin = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install — <?= e(SITE_NAME) ?> Admin</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="admin admin-auth">
<div class="auth-card">
    <h1><?= e(SITE_NAME) ?> — Setup</h1>

    <?php if ($hasAdmin): ?>
        <div class="alert alert-info">
            An admin account already exists. The installer is locked.
            <br><a href="<?= e(url('admin/login.php')) ?>">Go to login →</a>
        </div>
        <p class="muted">For security, delete <code>admin/install.php</code> from the server.</p>

    <?php elseif ($done): ?>
        <div class="alert alert-success">
            Admin account created successfully.
            <br><a href="<?= e(url('admin/login.php')) ?>">Log in now →</a>
        </div>
        <p class="muted">Important: delete <code>admin/install.php</code> on production.</p>

    <?php else: ?>
        <p class="muted">Create your admin account. You can change these later.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <?= csrf_field() ?>
            <label>
                Username
                <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" required minlength="3" autofocus>
            </label>
            <label>
                Email
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
            </label>
            <label>
                Password
                <input type="password" name="password" required minlength="10">
            </label>
            <label>
                Confirm password
                <input type="password" name="password_confirm" required minlength="10">
            </label>
            <button type="submit" class="btn btn-primary">Create admin account</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
