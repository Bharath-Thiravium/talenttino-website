<?php
declare(strict_types=1);

$root = __DIR__;
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . ltrim(rawurldecode($path), '/');

$serveFile = static function (string $file): bool {
    if (!is_file($file)) {
        return false;
    }

    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'php') {
        $cwd = getcwd();
        chdir(dirname($file));
        require basename($file);
        if ($cwd !== false) {
            chdir($cwd);
        }
        return true;
    }

    return false;
};

$resolve = static function (string $base, string $requestPath): string {
    $relative = ltrim($requestPath, '/');
    if ($relative === '') {
        $relative = 'index.php';
    }

    $file = rtrim($base, '/') . '/' . $relative;
    if (is_dir($file)) {
        $file = rtrim($file, '/') . '/index.php';
    }

    return $file;
};

if (str_starts_with($path, '/admin')) {
    $adminPath = substr($path, strlen('/admin')) ?: '/';
    $file = $resolve($root . '/backend/admin', $adminPath);
    if ($serveFile($file)) {
        return true;
    }
    if (is_file($file)) {
        return false;
    }
    http_response_code(404);
    echo 'Admin file not found.';
    return true;
}

$frontendFile = $resolve($root . '/frontend', $path);
if ($serveFile($frontendFile)) {
    return true;
}
if (is_file($frontendFile)) {
    $_SERVER['SCRIPT_FILENAME'] = $frontendFile;
    return false;
}

http_response_code(404);
echo 'Page not found.';
return true;
