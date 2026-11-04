<?php
/**
 * Admin — Account & Password Management
 * Allows the logged-in administrator to update username, email, and password.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Security token invalid. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $currentPass = (string) ($_POST['current_password'] ?? '');

            if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) {
                $errors[] = 'Username must be 3–40 characters (letters, numbers, . _ -).';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }

            $stmt = db()->prepare('SELECT password_hash FROM admin_users WHERE id = ? LIMIT 1');
            $stmt->execute([(int) $admin['id']]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($currentPass, $row['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            }

            if (!$errors) {
                $dup = db()->prepare('SELECT COUNT(*) FROM admin_users WHERE (username = ? OR email = ?) AND id <> ?');
                $dup->execute([$username, $email, (int) $admin['id']]);
                if ((int) $dup->fetchColumn() > 0) {
                    $errors[] = 'That username or email is already in use by another admin.';
                } else {
                    $upd = db()->prepare('UPDATE admin_users SET username = ?, email = ? WHERE id = ?');
                    $upd->execute([$username, $email, (int) $admin['id']]);
                    flash_set('Account details updated.');
                    redirect('admin/account.php');
                }
            }
        } elseif ($action === 'change_password') {
            $currentPass = (string) ($_POST['current_password'] ?? '');
            $newPass = (string) ($_POST['new_password'] ?? '');
            $confirmPass = (string) ($_POST['confirm_password'] ?? '');

            $stmt = db()->prepare('SELECT password_hash FROM admin_users WHERE id = ? LIMIT 1');
            $stmt->execute([(int) $admin['id']]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($currentPass, $row['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            }
            if (strlen($newPass) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            }
            if ($newPass !== $confirmPass) {
                $errors[] = 'New password and confirmation do not match.';
            }

            if (!$errors) {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $upd = db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
                $upd->execute([$hash, (int) $admin['id']]);
                session_regenerate_id(true);
                flash_set('Password changed successfully.');
                redirect('admin/account.php');
            }
        }
    }
}

$pageHeading = 'Account & Security';
require __DIR__ . '/_header.php';
?>

<p class="muted">Manage your administrator login credentials and password.</p>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="lang-cols">
    <form method="post" class="panel form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_profile">
        <h2 class="lang-title">Profile Details</h2>

        <label>Username
            <input type="text" name="username" value="<?= e($admin['username']) ?>" required autocomplete="username">
        </label>

        <label>Email address
            <input type="email" name="email" value="<?= e($admin['email']) ?>" required autocomplete="email">
        </label>

        <label>Current password <span class="muted">(required to confirm changes)</span>
            <input type="password" name="current_password" required autocomplete="current-password">
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-inline">Update Profile</button>
        </div>
    </form>

    <form method="post" class="panel form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">
        <h2 class="lang-title">Change Password</h2>

        <label>Current password
            <input type="password" name="current_password" required autocomplete="current-password">
        </label>

        <label>New password <span class="muted">(at least 8 characters)</span>
            <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
        </label>

        <label>Confirm new password
            <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-inline">Update Password</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/_footer.php'; ?>

