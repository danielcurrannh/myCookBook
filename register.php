<?php
require_once __DIR__ . '/config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $email === '' || strlen($password) < 8) {
        $error = 'Enter a username, valid email, and password of at least 8 characters.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO tbl_users (fld_userName, fld_email, fld_passwordHash) VALUES (?, ?, ?)');
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            redirect('index.php');
        } catch (PDOException $e) {
            $error = 'Username or email is already in use.';
        }
    }
}
include __DIR__ . '/header.php';
?>
<form class="auth-form" method="post">
    <h1>Create account</h1>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <label>Username <input name="username" required maxlength="50"></label>
    <label>Email <input name="email" type="email" required></label>
    <label>Password <input name="password" type="password" required minlength="8"></label>
    <button>Register</button>
</form>
<?php include __DIR__ . '/footer.php'; ?>

