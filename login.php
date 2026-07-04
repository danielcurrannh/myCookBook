<?php
require_once __DIR__ . '/config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('SELECT * FROM tbl_users WHERE fld_userName = ? OR fld_email = ?');
    $login = trim($_POST['login'] ?? '');
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['fld_passwordHash'])) {
        $_SESSION['user_id'] = (int)$user['fld_id'];
        redirect('index.php');
    }
    $error = 'Invalid login.';
}
include __DIR__ . '/header.php';
?>
<form class="auth-form" method="post">
    <h1>Log in</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <label>Username or email <input name="login" required></label>
    <label>Password <input name="password" type="password" required></label>
    <button>Log in</button>
</form>
<?php include __DIR__ . '/footer.php'; ?>

