<?php
declare(strict_types=1);

function tt_admin_asset_url(string $relativePath, string $fallbackVersion = '20260727'): string
{
    $relativePath = trim($relativePath);
    if ($relativePath === '') {
        return '';
    }

    if (preg_match('#^(?:https?:)?//#i', $relativePath) || str_starts_with($relativePath, 'data:')) {
        return $relativePath;
    }

    $parts = parse_url($relativePath);
    $path = isset($parts['path']) ? str_replace('\\', '/', (string)$parts['path']) : '';
    if ($path === '' || str_contains($path, '..')) {
        return $relativePath;
    }

    $fullPath = realpath(__DIR__ . '/' . $path);
    $version = $fallbackVersion;
    if ($fullPath && is_file($fullPath)) {
        $mtime = @filemtime($fullPath);
        if ($mtime) {
            $version = (string)$mtime;
        }
    }

    $query = [];
    if (!empty($parts['query'])) {
        parse_str((string)$parts['query'], $query);
    }
    $query['v'] = $version;

    return $path . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
}
