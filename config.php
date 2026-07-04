<?php
declare(strict_types=1);

session_start();

$configPath = __DIR__ . '/config.ini';
$config = parse_ini_file($configPath, true);

if ($config === false) {
    throw new RuntimeException('Unable to load application configuration from config.ini.');
}

$databaseConfig = $config['database'] ?? [];
$uploadsConfig = $config['uploads'] ?? [];

define('DB_HOST', $databaseConfig['host'] ?? 'localhost');
define('DB_NAME', $databaseConfig['name'] ?? 'db_myCookBook');
define('DB_USER', $databaseConfig['user'] ?? 'usr_myCookBook');
define('DB_PASS', $databaseConfig['password'] ?? '');
define('UPLOAD_DIR', __DIR__ . '/' . rtrim($uploadsConfig['dir'] ?? 'uploads', '/\\') . '/');
define('UPLOAD_URL', rtrim($uploadsConfig['url'] ?? 'uploads', '/\\') . '/');

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT fld_id, fld_userName, fld_email, fld_profilePublic FROM tbl_users WHERE fld_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function hot_score_sql(): string
{
    return '(tbl_recipes.fld_viewCount / POW(TIMESTAMPDIFF(HOUR, tbl_recipes.fld_createdAt, NOW()) + 2, 1.25))';
}

