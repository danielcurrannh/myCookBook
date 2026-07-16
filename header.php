<?php
require_once __DIR__ . '/functions.php';
$currentUser = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>myCookBook</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="index.php"><img src="assets/logo.png" alt="myCookBook logo" onerror="this.src='assets/placeholder-logo.svg'"></a>
    <div class="header-actions">
        <nav class="user-menu">
            <button class="cog" aria-label="User menu">⚙</button>
            <div class="dropdown">
                <a href="https://github.com/danielcurrannh/myCookBook" title="Source code"><i class="fa-brands fa-square-git" aria-hidden="true"></i> Source code</a>
                <?php if ($currentUser): ?>
                    <a href="user.php?id=<?= (int)$currentUser['fld_id'] ?>">My Page</a>
                    <a href="settings.php">Settings</a>
                    <a href="logout.php">Log out</a>
                <?php else: ?>
                    <a href="login.php">Log in</a>
                    <a href="register.php">Register</a>
                    <a href="forgot_password.php">Reset password</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<main class="page <?= e($pageClass ?? '') ?>">
