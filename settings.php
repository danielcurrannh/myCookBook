<?php
require_once __DIR__ . '/config.php';
$user = require_login();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    db()->prepare('UPDATE tbl_users SET fld_email = ?, fld_profilePublic = ? WHERE fld_id = ?')->execute([
        trim($_POST['email'] ?? ''),
        isset($_POST['profile_public']) ? 1 : 0,
        (int)$user['fld_id'],
    ]);
    $message = 'Settings saved.';
    $user = require_login();
}
include __DIR__ . '/header.php';
?>
<form class="auth-form" method="post">
    <h1>Settings</h1>
    <?php if ($message): ?><p class="success"><?= e($message) ?></p><?php endif; ?>
    <label>Email <input type="email" name="email" value="<?= e($user['fld_email']) ?>" required></label>
    <label class="check"><input type="checkbox" name="profile_public" <?= $user['fld_profilePublic'] ? 'checked' : '' ?>> Public profile</label>
    <button>Save settings</button>
</form>
<?php include __DIR__ . '/footer.php'; ?>

