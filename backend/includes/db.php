<?php
// Database Configuration
// Override these without editing code by setting DB_HOST, DB_USER, DB_PASS
// or DB_PASSWORD, DB_NAME, DB_PORT, DB_SOCKET, and DB_OPTIONAL in your server environment.
$loadEnvFile = static function (string $file): void {
    if (!is_file($file) || !is_readable($file)) {
        return;
    }

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        $value = trim($value, "\"'");
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
};

$loadEnvFile(dirname(__DIR__, 2) . '/.env');
$loadEnvFile(dirname(__DIR__) . '/node/.env');
$loadEnvFile(dirname(__DIR__, 2) . '/frontend/.env');

$env = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    return $value === false ? $default : $value;
};

if (!defined('TT_TIMEZONE')) {
    define('TT_TIMEZONE', $env('TT_TIMEZONE', 'Asia/Kolkata'));
}
if (!defined('TT_DB_TIME_ZONE')) {
    define('TT_DB_TIME_ZONE', $env('TT_DB_TIME_ZONE', '+05:30'));
}

date_default_timezone_set(TT_TIMEZONE);

if (!defined('DB_HOST')) {
    define('DB_HOST', $env('DB_HOST', '127.0.0.1'));
}
if (!defined('DB_USER')) {
    define('DB_USER', $env('DB_USER', 'u494785662_talentwebsite'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $env('DB_PASS', $env('DB_PASSWORD', 'Talenttino@2026')));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $env('DB_NAME', 'u494785662_talentwebsite'));
}
if (!defined('DB_PORT')) {
    define('DB_PORT', (int)$env('DB_PORT', '3306'));
}
if (!defined('DB_SOCKET')) {
    define('DB_SOCKET', $env('DB_SOCKET', ''));
}
if (!defined('DB_OPTIONAL')) {
    define('DB_OPTIONAL', filter_var($env('DB_OPTIONAL', '0'), FILTER_VALIDATE_BOOL));
}

mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;
$socket = DB_SOCKET !== '' && is_readable(DB_SOCKET) ? DB_SOCKET : null;
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, $socket);

if ($conn->connect_error && $socket === null && in_array(DB_HOST, ['localhost', '::1'], true)) {
    $conn = @new mysqli('127.0.0.1', DB_USER, DB_PASS, DB_NAME, DB_PORT);
}

if ($conn->connect_error) {
    error_log('MySQL connection failed: ' . $conn->connect_error);

    if (DB_OPTIONAL) {
        $conn = null;
        return;
    }

    http_response_code(500);
    die('Database connection failed. Start MySQL, run database/setup_local_mysql.sh, and check the DB_* settings in backend/includes/db.php.');
}

$conn->set_charset('utf8mb4');
$conn->query("SET time_zone = '" . $conn->real_escape_string(TT_DB_TIME_ZONE) . "'");
?>
