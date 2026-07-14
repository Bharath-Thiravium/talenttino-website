<?php
// Database Configuration
// Override these without editing code by setting DB_HOST, DB_USER, DB_PASS,
// DB_NAME, DB_PORT, DB_SOCKET, and DB_OPTIONAL in your server environment.
$env = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    return $value === false ? $default : $value;
};

if (!defined('DB_HOST')) {
    define('DB_HOST', $env('DB_HOST', '127.0.0.1'));
}
if (!defined('DB_USER')) {
    define('DB_USER', $env('DB_USER', 'talentteno_app'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $env('DB_PASS', 'talentteno_local_password'));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $env('DB_NAME', 'talentteno_db'));
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
?>
