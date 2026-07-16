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
$appConfig = $config['app'] ?? [];
$mailConfig = $config['mail'] ?? [];

define('DB_HOST', $databaseConfig['host'] ?? 'localhost');
define('DB_NAME', $databaseConfig['name'] ?? 'db_myCookBook');
define('DB_USER', $databaseConfig['user'] ?? 'usr_myCookBook');
define('DB_PASS', $databaseConfig['password'] ?? '');
define('UPLOAD_DIR', __DIR__ . '/' . rtrim($uploadsConfig['dir'] ?? 'uploads', '/\\') . '/');
define('UPLOAD_URL', rtrim($uploadsConfig['url'] ?? 'uploads', '/\\') . '/');
define('APP_BASE_URL', rtrim($appConfig['base_url'] ?? '', '/'));
define('MAIL_FROM', $mailConfig['from'] ?? $appConfig['mail_from'] ?? 'no-reply@mycookbook.local');
define('SMTP_HOST', $mailConfig['host'] ?? '');
define('SMTP_PORT', (int)($mailConfig['port'] ?? 587));
define('SMTP_USERNAME', $mailConfig['username'] ?? '');
define('SMTP_PASSWORD', $mailConfig['password'] ?? '');
define('SMTP_ENCRYPTION', strtolower($mailConfig['encryption'] ?? 'tls'));

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

function absolute_url(string $path): string
{
    if (APP_BASE_URL !== '') {
        return APP_BASE_URL . '/' . ltrim($path, '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    return $scheme . '://' . $host . ($basePath === '' ? '' : $basePath) . '/' . ltrim($path, '/');
}

function send_password_reset_email(string $email, string $resetUrl): bool
{
    $subject = 'Reset your myCookBook password';
    $message = "We received a request to reset your myCookBook password.\n\n" .
        "Open this link to choose a new password. This link expires in 1 hour:\n" .
        $resetUrl . "\n\n" .
        "If you did not request a password reset, you can ignore this email.";
    if (SMTP_HOST !== '' && SMTP_USERNAME !== '' && SMTP_PASSWORD !== '') {
        return smtp_mail($email, $subject, $message);
    }

    $headers = 'From: ' . MAIL_FROM . "\r\n" .
        'Reply-To: ' . MAIL_FROM . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    return @mail($email, $subject, $message, $headers);
}

function smtp_mail(string $to, string $subject, string $body): bool
{
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $remoteHost = SMTP_ENCRYPTION === 'ssl' ? 'ssl://' . $host : $host;
    $socket = @fsockopen($remoteHost, $port, $errno, $errstr, 20);

    if (!$socket) {
        error_log("SMTP connection failed: {$errno} {$errstr}");
        return false;
    }

    stream_set_timeout($socket, 20);

    $read = static function () use ($socket): string {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        return $response;
    };

    $write = static function (string $command) use ($socket): void {
        fwrite($socket, $command . "\r\n");
    };

    $expect = static function (array $codes) use ($read): bool {
        $response = $read();
        $code = substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            error_log('SMTP error: ' . trim($response));
            return false;
        }

        return true;
    };

    $localhost = $_SERVER['SERVER_NAME'] ?? 'localhost';

    if (!$expect(['220'])) {
        fclose($socket);
        return false;
    }

    $write('EHLO ' . $localhost);
    if (!$expect(['250'])) {
        fclose($socket);
        return false;
    }

    if (SMTP_ENCRYPTION === 'tls') {
        $write('STARTTLS');
        if (!$expect(['220']) || !stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return false;
        }

        $write('EHLO ' . $localhost);
        if (!$expect(['250'])) {
            fclose($socket);
            return false;
        }
    }

    $write('AUTH LOGIN');
    if (!$expect(['334'])) {
        fclose($socket);
        return false;
    }

    $write(base64_encode(SMTP_USERNAME));
    if (!$expect(['334'])) {
        fclose($socket);
        return false;
    }

    $write(base64_encode(SMTP_PASSWORD));
    if (!$expect(['235'])) {
        fclose($socket);
        return false;
    }

    $write('MAIL FROM:<' . MAIL_FROM . '>');
    if (!$expect(['250'])) {
        fclose($socket);
        return false;
    }

    $write('RCPT TO:<' . $to . '>');
    if (!$expect(['250', '251'])) {
        fclose($socket);
        return false;
    }

    $write('DATA');
    if (!$expect(['354'])) {
        fclose($socket);
        return false;
    }

    $headers = [
        'From: myCookBook <' . MAIL_FROM . '>',
        'To: ' . $to,
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    $data = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n.", "\n..", $body) . "\r\n.";
    $write($data);

    if (!$expect(['250'])) {
        fclose($socket);
        return false;
    }

    $write('QUIT');
    fclose($socket);

    return true;
}

function hot_score_sql(): string
{
    return '(tbl_recipes.fld_viewCount / POW(TIMESTAMPDIFF(HOUR, tbl_recipes.fld_createdAt, NOW()) + 2, 1.25))';
}

