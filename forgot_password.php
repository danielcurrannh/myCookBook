<?php
require_once __DIR__ . '/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'If an account exists for that email address, a password reset link has been sent.';
    } else {
        $stmt = db()->prepare('SELECT fld_id, fld_email FROM tbl_users WHERE fld_email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            db()->prepare('DELETE FROM tbl_passwordResets WHERE fld_userId = ? OR fld_expiresAt < NOW()')->execute([(int)$user['fld_id']]);

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

            $insert = db()->prepare('INSERT INTO tbl_passwordResets (fld_userId, fld_tokenHash, fld_expiresAt) VALUES (?, ?, ?)');
            $insert->execute([(int)$user['fld_id'], $tokenHash, $expiresAt]);

            $resetLink = absolute_url('reset_password.php?token=' . urlencode($token));
            send_password_reset_email($user['fld_email'], $resetLink);
        }

        $message = 'If an account exists for that email address, a password reset link has been sent.';
    }
}

include __DIR__ . '/header.php';
?>
<form class="auth-form" method="post">
    <h1>Reset password</h1>
    <p>Enter the email address associated with your account and we will send a link to choose a new password.</p>
    <?php if ($message): ?><p class="success"><?= e($message) ?></p><?php endif; ?>
    <label>Email <input name="email" type="email" required></label>
    <button>Send reset link</button>
</form>
<?php include __DIR__ . '/footer.php'; ?>

