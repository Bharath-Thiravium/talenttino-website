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

    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimeTypes = [
        'avif' => 'image/avif',
        'css' => 'text/css; charset=UTF-8',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'application/javascript; charset=UTF-8',
        'mp4' => 'video/mp4',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    if (!headers_sent()) {
        header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Length: ' . filesize($file));
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        readfile($file);
    }

    return true;
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
