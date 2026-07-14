<?php
require_once 'auth_check.php';

$success = '';
$error = '';
$mediaDir = __DIR__ . '/../../frontend/uploads/media/';
$mediaUrl = '../../frontend/uploads/media/';
$frontendPrefix = 'uploads/media/';
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    'application/pdf' => 'pdf',
];
$appMaxBytes = 100 * 1024 * 1024;

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

$serverUploadMax = tt_media_ini_bytes((string) ini_get('upload_max_filesize'));
$serverPostMax = tt_media_ini_bytes((string) ini_get('post_max_size'));
$maxUploadBytes = min($appMaxBytes, $serverUploadMax ?: $appMaxBytes, $serverPostMax ?: $appMaxBytes);
$maxUploadLabel = tt_media_format_bytes($maxUploadBytes);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_media'])) {
    $galleryImageUpload = ($_POST['media_kind'] ?? '') === 'gallery_image';
    if (!tt_media_ensure_dir($mediaDir)) {
        $error = 'Unable to create media upload folder.';
    } elseif (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please choose an image file.';
    } elseif ($_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
        $error = tt_media_upload_error_message((int) $_FILES['media_file']['error'], $maxUploadBytes);
    } elseif ($_FILES['media_file']['size'] > $maxUploadBytes) {
        $error = 'Image file must be ' . $maxUploadLabel . ' or smaller.';
    } else {
        $mime = mime_content_type($_FILES['media_file']['tmp_name']);
        if (!isset($allowedMimes[$mime])) {
            $error = 'Only JPG, PNG, WebP, GIF, or PDF files are allowed.';
        } elseif ($galleryImageUpload && !str_starts_with($mime, 'image/')) {
            $error = 'Gallery upload accepts only JPG, PNG, WebP, or GIF images.';
        } else {
            $name = tt_media_safe_name($_FILES['media_file']['name']);
            $filename = $name . '-' . date('Ymd-His') . '-' . substr(uniqid('', true), -6) . '.' . $allowedMimes[$mime];
            if (move_uploaded_file($_FILES['media_file']['tmp_name'], $mediaDir . $filename)) {
                $success = $galleryImageUpload ? 'Gallery image uploaded successfully.' : 'Media uploaded successfully.';
            } else {
                $error = 'Unable to save the uploaded file.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_media'])) {
    $file = basename((string)($_POST['file'] ?? ''));
    $path = $mediaDir . $file;
    if ($file !== '' && is_file($path)) {
        unlink($path);
        $success = 'Media deleted successfully.';
    } else {
        $error = 'Media file not found.';
    }
}

tt_media_ensure_dir($mediaDir);
$files = [];
foreach (glob($mediaDir . '*') ?: [] as $path) {
    if (!is_file($path)) continue;
    $file = basename($path);
    if (str_starts_with($file, '.')) continue;
    $mime = mime_content_type($path) ?: '';
    $files[] = [
        'name' => $file,
        'mime' => $mime,
        'size' => filesize($path),
        'modified' => filemtime($path),
        'frontend_path' => $frontendPrefix . rawurlencode($file),
        'admin_url' => $mediaUrl . rawurlencode($file),
        'is_image' => str_starts_with($mime, 'image/'),
    ];
}
usort($files, static fn($a, $b) => $b['modified'] <=> $a['modified']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library — Talentteno Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
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

        <div class="admin-card media-upload-intro">
            <div>
                <h3><i class="fas fa-images"></i> Gallery Images</h3>
                <p>Upload images here. New images are shown automatically on the frontend Gallery page.</p>
            </div>
            <button type="button" class="course-add-btn course" data-open-media-upload>
                <i class="fas fa-plus"></i> Add Gallery Image
            </button>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h3><i class="fas fa-folder-open"></i> Uploaded Media</h3>
                <span style="font-size:13px;color:#64748B;"><strong><?= count($files) ?></strong> files</span>
            </div>
            <?php if ($files): ?>
            <div class="media-grid">
                <?php foreach ($files as $file): ?>
                <article class="media-card">
                    <a class="media-preview" href="<?= htmlspecialchars($file['admin_url']) ?>" target="_blank">
                        <?php if ($file['is_image']): ?>
                        <img src="<?= htmlspecialchars($file['admin_url']) ?>" alt="<?= htmlspecialchars($file['name']) ?>">
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
                                <input type="hidden" name="file" value="<?= htmlspecialchars($file['name']) ?>">
                                <button class="btn-xs btn-red" type="submit"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
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
                <h2 id="mediaUploadTitle"><i class="fas fa-cloud-upload-alt"></i> Add Gallery Image</h2>
                <p>Choose a JPG, PNG, WebP, or GIF image. After upload, it will appear in the public gallery.</p>
            </div>
            <button type="button" class="modal-close" data-close-media-upload aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="media-upload-form">
            <input type="hidden" name="upload_media" value="1">
            <input type="hidden" name="media_kind" value="gallery_image">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label>Image File</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxUploadBytes ?>">
                    <input type="file" name="media_file" accept="image/jpeg,image/png,image/webp,image/gif" required>
                    <small class="field-help">Allowed: JPG, PNG, WebP, GIF. Maximum <?= htmlspecialchars($maxUploadLabel) ?>.</small>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-cancel" data-close-media-upload>Cancel</button>
                <button class="btn-save" type="submit"><i class="fas fa-cloud-upload-alt"></i> Upload Image</button>
            </div>
        </form>
    </div>
</div>
<script>
const mediaUploadModal = document.getElementById('mediaUploadModal');
function openMediaUploadModal() {
    mediaUploadModal.classList.add('is-open');
    mediaUploadModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
}
function closeMediaUploadModal() {
    mediaUploadModal.classList.remove('is-open');
    mediaUploadModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
}
document.querySelectorAll('[data-open-media-upload]').forEach(button => button.addEventListener('click', openMediaUploadModal));
document.querySelectorAll('[data-close-media-upload]').forEach(button => button.addEventListener('click', closeMediaUploadModal));
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && mediaUploadModal.classList.contains('is-open')) {
        closeMediaUploadModal();
    }
});
</script>
</body>
</html>
