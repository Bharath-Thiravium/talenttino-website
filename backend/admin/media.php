<?php
require_once 'auth_check.php';

$success = '';
$error = '';
$galleryDir = __DIR__ . '/../../frontend/uploads/gallery/';
$galleryUrl = '../../frontend/uploads/gallery/';
$galleryFrontendPrefix = 'uploads/gallery/';
$mediaDir = __DIR__ . '/../../frontend/uploads/media/';
$mediaUrl = '../../frontend/uploads/media/';
$mediaFrontendPrefix = 'uploads/media/';
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    'video/mp4' => 'mp4',
    'video/webm' => 'webm',
    'video/ogg' => 'ogv',
    'application/pdf' => 'pdf',
];
$appMaxBytes = 100 * 1024 * 1024;
$maxBatchFiles = 10;
$isAjaxUpload = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && isset($_POST['upload_media'])
    && strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'fetch';

function tt_media_ensure_dir(string $dir): bool
{
    return is_dir($dir) || mkdir($dir, 0775, true);
}

function tt_media_safe_name(string $name): string
{
    $base = pathinfo($name, PATHINFO_FILENAME);
    $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $base), '-'));
    return $base !== '' ? $base : 'media';
}

function tt_media_ini_bytes(string $value): int
{
    $value = trim($value);
    if ($value === '') {
        return 0;
    }

    $unit = strtolower($value[strlen($value) - 1]);
    $bytes = (float) $value;
    if ($bytes < 0) {
        return PHP_INT_MAX;
    }

    switch ($unit) {
        case 'g':
            $bytes *= 1024;
            // no break
        case 'm':
            $bytes *= 1024;
            // no break
        case 'k':
            $bytes *= 1024;
            break;
    }

    return (int) $bytes;
}

function tt_media_format_bytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        return rtrim(rtrim(number_format($bytes / 1024 / 1024, 1), '0'), '.') . ' MB';
    }

    return rtrim(rtrim(number_format($bytes / 1024, 1), '0'), '.') . ' KB';
}

function tt_media_upload_error_message(int $errorCode, int $maxBytes): string
{
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Media file is too large. Maximum allowed size is ' . tt_media_format_bytes($maxBytes) . '.';
        case UPLOAD_ERR_PARTIAL:
            return 'Upload was interrupted. Please choose the file and try again.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Upload failed because the server temporary folder is missing.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Upload failed because the server could not write the temporary file.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload was blocked by a server extension.';
        default:
            return 'Upload failed. Please try again.';
    }
}

function tt_media_uploaded_files(array $files): array
{
    if (!isset($files['name'])) {
        return [];
    }

    if (!is_array($files['name'])) {
        return [[
            'name' => (string)($files['name'] ?? ''),
            'type' => (string)($files['type'] ?? ''),
            'tmp_name' => (string)($files['tmp_name'] ?? ''),
            'error' => (int)($files['error'] ?? UPLOAD_ERR_NO_FILE),
            'size' => (int)($files['size'] ?? 0),
        ]];
    }

    $normalized = [];
    foreach ($files['name'] as $index => $name) {
        $normalized[] = [
            'name' => (string)$name,
            'type' => (string)($files['type'][$index] ?? ''),
            'tmp_name' => (string)($files['tmp_name'][$index] ?? ''),
            'error' => (int)($files['error'][$index] ?? UPLOAD_ERR_NO_FILE),
            'size' => (int)($files['size'][$index] ?? 0),
        ];
    }

    return $normalized;
}

$serverUploadMax = tt_media_ini_bytes((string) ini_get('upload_max_filesize'));
$serverPostMax = tt_media_ini_bytes((string) ini_get('post_max_size'));
$serverPostFileMax = $serverPostMax > 1024 * 1024 ? $serverPostMax - (512 * 1024) : $serverPostMax;
$maxUploadBytes = min($appMaxBytes, $serverUploadMax ?: $appMaxBytes, $serverPostFileMax ?: $appMaxBytes);
$maxUploadLabel = tt_media_format_bytes($maxUploadBytes);
$serverPostLabel = tt_media_format_bytes($serverPostMax ?: $maxUploadBytes);

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && isset($_SERVER['CONTENT_LENGTH'])
    && $serverPostMax > 0
    && (int)$_SERVER['CONTENT_LENGTH'] > $serverPostMax
) {
    $error = 'Upload is too large for this server. Please upload smaller files. Maximum total request size is ' . $serverPostLabel . '.';
}

if ($error === '' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_media'])) {
    $galleryUpload = ($_POST['media_kind'] ?? '') === 'gallery_item';
    $targetDir = $galleryUpload ? $galleryDir : $mediaDir;
    $targetLabel = $galleryUpload ? 'gallery upload folder' : 'media upload folder';
    if (!tt_media_ensure_dir($targetDir)) {
        $error = 'Unable to create ' . $targetLabel . '.';
    } elseif (!isset($_FILES['media_file'])) {
        $error = 'Please choose 1 to ' . $maxBatchFiles . ' file(s).';
    } else {
        $uploads = array_values(array_filter(
            tt_media_uploaded_files($_FILES['media_file']),
            static fn(array $file): bool => (int)$file['error'] !== UPLOAD_ERR_NO_FILE || trim((string)$file['name']) !== ''
        ));

        if (!$uploads) {
            $error = 'Please choose 1 to ' . $maxBatchFiles . ' file(s).';
        } elseif (count($uploads) > $maxBatchFiles) {
            $error = 'Upload maximum ' . $maxBatchFiles . ' files at one time.';
        } else {
            $saved = 0;
            $failed = [];

            foreach ($uploads as $index => $file) {
                $displayName = trim((string)$file['name']) !== '' ? (string)$file['name'] : 'File ' . ($index + 1);

                if ((int)$file['error'] !== UPLOAD_ERR_OK) {
                    $failed[] = $displayName . ': ' . tt_media_upload_error_message((int)$file['error'], $maxUploadBytes);
                    continue;
                }

                if ((int)$file['size'] > $maxUploadBytes) {
                    $failed[] = $displayName . ': File must be ' . $maxUploadLabel . ' or smaller.';
                    continue;
                }

                $tmpName = (string)$file['tmp_name'];
                $mime = is_file($tmpName) ? (mime_content_type($tmpName) ?: '') : '';
                if (!isset($allowedMimes[$mime])) {
                    $failed[] = $displayName . ': Only JPG, PNG, WebP, GIF, or PDF files are allowed.';
                    continue;
                }

                if ($galleryUpload && !str_starts_with($mime, 'image/') && !str_starts_with($mime, 'video/')) {
                    $failed[] = $displayName . ': Gallery upload accepts only images or videos.';
                    continue;
                }

                $name = tt_media_safe_name($displayName);
                $filename = $name . '-' . date('Ymd-His') . '-' . substr(uniqid('', true), -6) . '.' . $allowedMimes[$mime];
                if (move_uploaded_file($tmpName, $targetDir . $filename)) {
                    $saved++;
                } else {
                    $failed[] = $displayName . ': Unable to save the uploaded file.';
                }
            }

            if ($saved > 0) {
                $label = $galleryUpload ? 'gallery item' : 'media file';
                $success = $saved . ' ' . $label . ($saved === 1 ? '' : 's') . ' uploaded successfully.';
            }

            if ($failed) {
                $error = implode(' ', array_slice($failed, 0, 4));
                if (count($failed) > 4) {
                    $error .= ' +' . (count($failed) - 4) . ' more failed.';
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_media'])) {
    $file = basename((string)($_POST['file'] ?? ''));
    $library = ($_POST['library'] ?? '') === 'gallery' ? 'gallery' : 'media';
    $path = ($library === 'gallery' ? $galleryDir : $mediaDir) . $file;
    if ($file !== '' && is_file($path)) {
        unlink($path);
        $success = $library === 'gallery' ? 'Gallery item deleted successfully.' : 'Media deleted successfully.';
    } else {
        $error = $library === 'gallery' ? 'Gallery item not found.' : 'Media file not found.';
    }
}

if ($isAjaxUpload) {
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => $error === '',
        'success' => $success,
        'error' => $error,
    ]);
    exit;
}

tt_media_ensure_dir($galleryDir);
tt_media_ensure_dir($mediaDir);

function tt_media_extension_mime(string $file): string
{
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogv' => 'video/ogg',
        'ogg' => 'video/ogg',
        'pdf' => 'application/pdf',
    ];

    return $mimes[$extension] ?? '';
}

function tt_media_page_number(string $key): int
{
    return max(1, (int)($_GET[$key] ?? 1));
}

function tt_media_page_url(string $key, int $page): string
{
    $query = $_GET;
    $query[$key] = max(1, $page);
    return 'media.php?' . http_build_query($query);
}

function tt_media_collect_files(string $dir, string $adminUrl, string $frontendPrefix, bool $galleryOnly = false, int $page = 1, int $perPage = 24): array
{
    $files = [];
    foreach (glob($dir . '*') ?: [] as $path) {
        if (!is_file($path)) continue;
        $file = basename($path);
        if (str_starts_with($file, '.')) continue;
        $mime = tt_media_extension_mime($file);
        if ($mime === '') {
            $mime = mime_content_type($path) ?: '';
        }
        if ($galleryOnly && !str_starts_with($mime, 'image/') && !str_starts_with($mime, 'video/')) continue;
        $files[] = [
            'name' => $file,
            'mime' => $mime,
            'size' => filesize($path),
            'modified' => filemtime($path),
            'frontend_path' => $frontendPrefix . rawurlencode($file),
            'admin_url' => $adminUrl . rawurlencode($file),
            'is_image' => str_starts_with($mime, 'image/'),
            'is_video' => str_starts_with($mime, 'video/'),
        ];
    }
    usort($files, static fn($a, $b) => $b['modified'] <=> $a['modified']);

    $total = count($files);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min(max(1, $page), $totalPages);

    return [
        'items' => array_slice($files, ($page - 1) * $perPage, $perPage),
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
    ];
}

function tt_media_render_pagination(array $pagination, string $pageKey): void
{
    if (($pagination['total_pages'] ?? 1) <= 1) {
        return;
    }

    $page = (int)$pagination['page'];
    $totalPages = (int)$pagination['total_pages'];
    ?>
    <div class="pagination">
        <a class="<?= $page <= 1 ? 'disabled' : '' ?>" href="<?= htmlspecialchars(tt_media_page_url($pageKey, $page - 1)) ?>"><i class="fas fa-chevron-left"></i> Prev</a>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <a class="<?= $page >= $totalPages ? 'disabled' : '' ?>" href="<?= htmlspecialchars(tt_media_page_url($pageKey, $page + 1)) ?>">Next <i class="fas fa-chevron-right"></i></a>
    </div>
    <?php
}

$mediaPerPage = 24;
$galleryLibrary = tt_media_collect_files($galleryDir, $galleryUrl, $galleryFrontendPrefix, true, tt_media_page_number('gallery_page'), $mediaPerPage);
$mediaLibrary = tt_media_collect_files($mediaDir, $mediaUrl, $mediaFrontendPrefix, false, tt_media_page_number('media_page'), $mediaPerPage);
$galleryFiles = $galleryLibrary['items'];
$files = $mediaLibrary['items'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library — Talentteno Admin</title>
    <link rel="icon" type="image/png" href="../../frontend/assets/images/logot-transparent.png">
    <link rel="apple-touch-icon" href="../../frontend/assets/images/logot-transparent.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css?v=20260722-adminmobile3">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-photo-film"></i> Media Library</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="media-upload-options">
            <div class="admin-card media-upload-intro">
                <div>
                    <h3><i class="fas fa-photo-film"></i> Gallery Images & Videos</h3>
                    <p>Upload public gallery images and videos. These items are shown automatically on the frontend Gallery page.</p>
                </div>
                <button type="button" class="course-add-btn course" data-open-media-upload data-media-kind="gallery_item">
                    <i class="fas fa-plus"></i> Add Gallery Items
                </button>
            </div>
            <div class="admin-card media-upload-intro">
                <div>
                    <h3><i class="fas fa-folder-plus"></i> Media Files</h3>
                    <p>Upload reusable files for course images, page content, brochures, or admin copy-paste paths.</p>
                </div>
                <button type="button" class="course-add-btn course" data-open-media-upload data-media-kind="media_file">
                    <i class="fas fa-plus"></i> Add Media Files
                </button>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h3><i class="fas fa-photo-film"></i> Gallery Images & Videos</h3>
                <span style="font-size:13px;color:#64748B;"><strong><?= (int)$galleryLibrary['total'] ?></strong> files</span>
            </div>
            <?php if ($galleryFiles): ?>
            <div class="media-grid">
                <?php foreach ($galleryFiles as $file): ?>
                <article class="media-card">
                    <a class="media-preview" href="<?= htmlspecialchars($file['admin_url']) ?>" target="_blank">
                        <?php if ($file['is_video']): ?>
                        <video src="<?= htmlspecialchars($file['admin_url']) ?>" muted preload="none"></video>
                        <span class="media-video-badge"><i class="fas fa-play"></i></span>
                        <?php else: ?>
                        <img src="<?= htmlspecialchars($file['admin_url']) ?>" alt="<?= htmlspecialchars($file['name']) ?>" loading="lazy" decoding="async">
                        <?php endif; ?>
                    </a>
                    <div class="media-body">
                        <h3 title="<?= htmlspecialchars($file['name']) ?>"><?= htmlspecialchars($file['name']) ?></h3>
                        <p><?= htmlspecialchars($file['mime']) ?> · <?= number_format($file['size'] / 1024, 1) ?> KB</p>
                        <label>Gallery path</label>
                        <input type="text" value="<?= htmlspecialchars($file['frontend_path']) ?>" readonly onclick="this.select();">
                        <div class="media-actions">
                            <a class="btn-xs btn-blue" href="<?= htmlspecialchars($file['admin_url']) ?>" target="_blank"><i class="fas fa-eye"></i> View</a>
                            <form method="POST" onsubmit="return confirm('Delete this gallery item?');">
                                <input type="hidden" name="delete_media" value="1">
                                <input type="hidden" name="library" value="gallery">
                                <input type="hidden" name="file" value="<?= htmlspecialchars($file['name']) ?>">
                                <button class="btn-xs btn-red" type="submit"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php tt_media_render_pagination($galleryLibrary, 'gallery_page'); ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <p>No gallery images or videos uploaded yet.</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h3><i class="fas fa-folder-open"></i> Uploaded Media</h3>
                <span style="font-size:13px;color:#64748B;"><strong><?= (int)$mediaLibrary['total'] ?></strong> files</span>
            </div>
            <?php if ($files): ?>
            <div class="media-grid">
                <?php foreach ($files as $file): ?>
                <article class="media-card">
                    <a class="media-preview" href="<?= htmlspecialchars($file['admin_url']) ?>" target="_blank">
                        <?php if ($file['is_image']): ?>
                        <img src="<?= htmlspecialchars($file['admin_url']) ?>" alt="<?= htmlspecialchars($file['name']) ?>" loading="lazy" decoding="async">
                        <?php elseif ($file['is_video']): ?>
                        <video src="<?= htmlspecialchars($file['admin_url']) ?>" muted preload="none"></video>
                        <span class="media-video-badge"><i class="fas fa-play"></i></span>
                        <?php else: ?>
                        <i class="fas fa-file-pdf"></i>
                        <?php endif; ?>
                    </a>
                    <div class="media-body">
                        <h3 title="<?= htmlspecialchars($file['name']) ?>"><?= htmlspecialchars($file['name']) ?></h3>
                        <p><?= htmlspecialchars($file['mime']) ?> · <?= number_format($file['size'] / 1024, 1) ?> KB</p>
                        <label>Project path</label>
                        <input type="text" value="<?= htmlspecialchars($file['frontend_path']) ?>" readonly onclick="this.select();">
                        <div class="media-actions">
                            <a class="btn-xs btn-blue" href="<?= htmlspecialchars($file['admin_url']) ?>" target="_blank"><i class="fas fa-eye"></i> View</a>
                            <form method="POST" onsubmit="return confirm('Delete this media file?');">
                                <input type="hidden" name="delete_media" value="1">
                                <input type="hidden" name="library" value="media">
                                <input type="hidden" name="file" value="<?= htmlspecialchars($file['name']) ?>">
                                <button class="btn-xs btn-red" type="submit"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php tt_media_render_pagination($mediaLibrary, 'media_page'); ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-photo-film"></i>
                <p>No media uploaded yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="admin-modal" id="mediaUploadModal" aria-hidden="true">
    <div class="admin-modal-backdrop" data-close-media-upload></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="mediaUploadTitle">
        <div class="admin-modal-header">
            <div>
                <h2 id="mediaUploadTitle"><i class="fas fa-cloud-upload-alt"></i> Add Gallery Items</h2>
                <p id="mediaUploadDescription">Choose 1 to 10 images or videos. After upload, they will appear in the public gallery.</p>
            </div>
            <button type="button" class="modal-close" data-close-media-upload aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="media-upload-form">
            <input type="hidden" name="upload_media" value="1">
            <input type="hidden" name="media_kind" value="gallery_item" data-media-kind-input>
            <div class="admin-modal-body">
                <div class="form-group">
                    <label data-media-file-label>Gallery Files</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxUploadBytes ?>">
                    <input type="file" name="media_file[]" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/ogg" multiple required data-media-file-input>
                    <small class="field-help" data-media-file-help>Choose 1 to <?= (int)$maxBatchFiles ?> files. Allowed: JPG, PNG, WebP, GIF, MP4, WebM, OGV. Maximum <?= htmlspecialchars($maxUploadLabel) ?> per file.</small>
                    <small class="field-help" data-media-file-count></small>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-cancel" data-close-media-upload>Cancel</button>
                <button class="btn-save" type="submit" data-media-submit><i class="fas fa-cloud-upload-alt"></i> Upload Gallery Items</button>
            </div>
        </form>
    </div>
</div>
<script>
const mediaUploadModal = document.getElementById('mediaUploadModal');
const mediaUploadTitle = document.getElementById('mediaUploadTitle');
const mediaUploadDescription = document.getElementById('mediaUploadDescription');
const mediaKindInput = document.querySelector('[data-media-kind-input]');
const mediaFileLabel = document.querySelector('[data-media-file-label]');
const mediaFileInput = document.querySelector('[data-media-file-input]');
const mediaFileHelp = document.querySelector('[data-media-file-help]');
const mediaFileCount = document.querySelector('[data-media-file-count]');
const mediaSubmit = document.querySelector('[data-media-submit]');
const maxBatchFiles = <?= (int)$maxBatchFiles ?>;
const maxUploadBytes = <?= (int)$maxUploadBytes ?>;
const maxUploadLabel = <?= json_encode($maxUploadLabel) ?>;
const uploadModalModes = {
    gallery_item: {
        title: '<i class="fas fa-cloud-upload-alt"></i> Add Gallery Items',
        description: 'Choose 1 to 10 images or videos. After upload, they will appear in the public gallery.',
        label: 'Gallery Files',
        accept: 'image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/ogg',
        help: 'Choose 1 to <?= (int)$maxBatchFiles ?> files. Allowed: JPG, PNG, WebP, GIF, MP4, WebM, OGV. Maximum <?= htmlspecialchars($maxUploadLabel) ?> per file.',
        submit: '<i class="fas fa-cloud-upload-alt"></i> Upload Gallery Items'
    },
    media_file: {
        title: '<i class="fas fa-cloud-upload-alt"></i> Add Media Files',
        description: 'Choose 1 to 10 images, videos, or PDFs for reusable admin media paths. These files do not automatically appear in Gallery.',
        label: 'Media Files',
        accept: 'image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/ogg,application/pdf',
        help: 'Choose 1 to <?= (int)$maxBatchFiles ?> files. Allowed: JPG, PNG, WebP, GIF, MP4, WebM, OGV, PDF. Maximum <?= htmlspecialchars($maxUploadLabel) ?> per file.',
        submit: '<i class="fas fa-cloud-upload-alt"></i> Upload Media'
    }
};
function openMediaUploadModal(kind = 'gallery_item') {
    const mode = uploadModalModes[kind] || uploadModalModes.gallery_item;
    mediaUploadTitle.innerHTML = mode.title;
    mediaUploadDescription.textContent = mode.description;
    mediaKindInput.value = kind;
    mediaFileLabel.textContent = mode.label;
    mediaFileInput.value = '';
    mediaFileInput.setAttribute('accept', mode.accept);
    mediaFileInput.setAttribute('multiple', 'multiple');
    mediaFileCount.textContent = '';
    mediaFileCount.classList.remove('text-danger');
    mediaFileHelp.textContent = mode.help;
    mediaSubmit.innerHTML = mode.submit;
    mediaSubmit.disabled = false;
    mediaSubmit.removeAttribute('aria-busy');
    mediaUploadModal.classList.add('is-open');
    mediaUploadModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
}
function closeMediaUploadModal() {
    mediaUploadModal.classList.remove('is-open');
    mediaUploadModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
}
document.querySelectorAll('[data-open-media-upload]').forEach(button => {
    button.addEventListener('click', () => openMediaUploadModal(button.dataset.mediaKind || 'gallery_image'));
});
document.querySelectorAll('[data-close-media-upload]').forEach(button => button.addEventListener('click', closeMediaUploadModal));
mediaFileInput.addEventListener('change', () => {
    const files = mediaFileInput.files ? [...mediaFileInput.files] : [];
    const count = files.length;
    if (count > maxBatchFiles) {
        mediaFileInput.value = '';
        mediaFileCount.textContent = `Please select only ${maxBatchFiles} files at one time.`;
        mediaFileCount.classList.add('text-danger');
        mediaSubmit.disabled = true;
        return;
    }

    const oversized = files.filter(file => file.size > maxUploadBytes);
    if (oversized.length) {
        mediaFileInput.value = '';
        mediaFileCount.textContent = `${oversized[0].name} is too large. Maximum ${maxUploadLabel} per file.`;
        mediaFileCount.classList.add('text-danger');
        mediaSubmit.disabled = true;
        return;
    }

    mediaFileCount.classList.remove('text-danger');
    mediaFileCount.textContent = count ? `${count} file${count === 1 ? '' : 's'} selected.` : '';
    mediaSubmit.disabled = false;
});
document.querySelector('.media-upload-form').addEventListener('submit', async event => {
    event.preventDefault();
    const files = mediaFileInput.files ? [...mediaFileInput.files] : [];
    const oversized = files.find(file => file.size > maxUploadBytes);
    if (!files.length || files.length > maxBatchFiles || oversized) {
        mediaFileCount.classList.add('text-danger');
        mediaFileCount.textContent = !files.length
            ? 'Please choose at least one file.'
            : oversized
            ? `${oversized.name} is too large. Maximum ${maxUploadLabel} per file.`
            : `Please select only ${maxBatchFiles} files at one time.`;
        return;
    }

    mediaSubmit.disabled = true;
    mediaSubmit.setAttribute('aria-busy', 'true');
    mediaFileInput.disabled = true;
    mediaFileCount.classList.remove('text-danger');

    let uploaded = 0;
    const failed = [];
    for (const [index, file] of files.entries()) {
        mediaFileCount.textContent = `Uploading ${index + 1} of ${files.length}: ${file.name}`;
        const formData = new FormData();
        formData.append('upload_media', '1');
        formData.append('media_kind', mediaKindInput.value || 'gallery_item');
        formData.append('MAX_FILE_SIZE', String(maxUploadBytes));
        formData.append('media_file', file, file.name);

        try {
            const response = await fetch('media.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'fetch' }
            });
            const result = await response.json();
            if (!response.ok || !result.ok) {
                failed.push(result.error || `${file.name}: Upload failed.`);
            } else {
                uploaded += 1;
            }
        } catch (error) {
            failed.push(`${file.name}: Upload failed. Please try again.`);
        }
    }

    if (failed.length) {
        mediaFileInput.disabled = false;
        mediaSubmit.disabled = false;
        mediaSubmit.removeAttribute('aria-busy');
        mediaFileCount.classList.add('text-danger');
        mediaFileCount.textContent = `${uploaded} uploaded. ${failed.slice(0, 2).join(' ')}`;
        return;
    }

    mediaFileCount.textContent = `${uploaded} file${uploaded === 1 ? '' : 's'} uploaded. Refreshing...`;
    window.location.href = 'media.php';
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && mediaUploadModal.classList.contains('is-open')) {
        closeMediaUploadModal();
    }
});
</script>
</body>
</html>
