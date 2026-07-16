<?php
require_once __DIR__ . '/config.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = '';
$reset = null;

if ($token !== '') {
    $stmt = db()->prepare('SELECT tbl_passwordResets.*, tbl_users.fld_email
                           FROM tbl_passwordResets
                           JOIN tbl_users ON tbl_users.fld_id = tbl_passwordResets.fld_userId
                           WHERE tbl_passwordResets.fld_tokenHash = ?
                             AND tbl_passwordResets.fld_expiresAt >= NOW()
                           LIMIT 1');
    $stmt->execute([hash('sha256', $token)]);
    $reset = $stmt->fetch();
}

if (!$reset) {
    $error = 'This password reset link is invalid or has expired.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $update = db()->prepare('UPDATE tbl_users SET fld_passwordHash = ? WHERE fld_id = ?');
        $update->execute([password_hash($password, PASSWORD_DEFAULT), (int)$reset['fld_userId']]);

        db()->prepare('DELETE FROM tbl_passwordResets WHERE fld_userId = ?')->execute([(int)$reset['fld_userId']]);

        $success = 'Your password has been reset. You can now log in with your new password.';
        $reset = null;
    }
}

include __DIR__ . '/header.php';
?>
<form class="auth-form" method="post">
    <h1>Choose a new password</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= e($success) ?></p>
        <a class="button" href="login.php">Log in</a>
    <?php elseif ($reset): ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <p>Resetting password for <?= e($reset['fld_email']) ?>.</p>
        <label>New password <input name="password" type="password" required minlength="8"></label>
        <label>Confirm new password <input name="confirm_password" type="password" required minlength="8"></label>
        <button>Reset password</button>
    <?php else: ?>
        <a class="button" href="forgot_password.php">Request a new reset link</a>
    <?php endif; ?>
</form>
<?php include __DIR__ . '/footer.php'; ?>

