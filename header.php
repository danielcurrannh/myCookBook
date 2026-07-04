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
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="index.php"><img src="assets/logo.png" alt="myCookBook logo" onerror="this.src='assets/placeholder-logo.svg'"></a>
    <nav class="user-menu">
        <button class="cog" aria-label="User menu">⚙</button>
        <div class="dropdown">
            <?php if ($currentUser): ?>
                <a href="user.php?id=<?= (int)$currentUser['fld_id'] ?>">My Page</a>
                <a href="settings.php">Settings</a>
                <a href="logout.php">Log out</a>
            <?php else: ?>
                <a href="login.php">Log in</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main class="page <?= e($pageClass ?? '') ?>">
