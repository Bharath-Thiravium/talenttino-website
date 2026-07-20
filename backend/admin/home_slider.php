<?php
require_once 'auth_check.php';

$conn->query("CREATE TABLE IF NOT EXISTS home_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT '',
    image VARCHAR(255) NOT NULL,
    mobile_image VARCHAR(255) DEFAULT '',
    sort_order INT DEFAULT 0,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
$displayOrderColumn = $conn->query("SHOW COLUMNS FROM home_slides LIKE 'display_order'");
if ($displayOrderColumn && $displayOrderColumn->num_rows === 0) {
    $conn->query("ALTER TABLE home_slides ADD COLUMN display_order INT DEFAULT 0 AFTER sort_order");
    $conn->query("UPDATE home_slides SET display_order = sort_order WHERE display_order = 0");
}
$mobileImageColumn = $conn->query("SHOW COLUMNS FROM home_slides LIKE 'mobile_image'");
if ($mobileImageColumn && $mobileImageColumn->num_rows === 0) {
    $conn->query("ALTER TABLE home_slides ADD COLUMN mobile_image VARCHAR(255) DEFAULT '' AFTER image");
}

$success = '';
$error = '';

function tt_home_slide_media_images(): array
{
    $sources = [
        [
            'dir' => __DIR__ . '/../../frontend/assets/images/',
            'url' => '../../frontend/assets/images/',
            'path' => 'assets/images/',
            'label' => 'Default Home',
            'files' => ['home.webp', 'home1.webp', 'home2.webp', 'home3.webp', 'home4.webp'],
        ],
        [
            'dir' => __DIR__ . '/../../frontend/assets/images/optimized/',
            'url' => '../../frontend/assets/images/optimized/',
            'path' => 'assets/images/optimized/',
            'label' => 'Optimized Home',
        ],
        [
            'dir' => __DIR__ . '/../../frontend/uploads/media/',
            'url' => '../../frontend/uploads/media/',
            'path' => 'uploads/media/',
            'label' => 'Media',
        ],
        [
            'dir' => __DIR__ . '/../../frontend/uploads/media/optimized/',
            'url' => '../../frontend/uploads/media/optimized/',
            'path' => 'uploads/media/optimized/',
            'label' => 'Optimized Media',
        ],
        [
            'dir' => __DIR__ . '/../../frontend/uploads/gallery/',
            'url' => '../../frontend/uploads/gallery/',
            'path' => 'uploads/gallery/',
            'label' => 'Gallery',
        ],
    ];

    $images = [];
    foreach ($sources as $source) {
        $filePaths = [];
        if (!empty($source['files'])) {
            foreach ($source['files'] as $file) {
                $filePaths[] = $source['dir'] . $file;
            }
        } else {
            $filePaths = glob($source['dir'] . '*') ?: [];
        }

        foreach ($filePaths as $filePath) {
            if (!is_file($filePath)) continue;
            $mime = mime_content_type($filePath) ?: '';
            if (!str_starts_with($mime, 'image/')) continue;

            $file = basename($filePath);
            $images[] = [
                'file' => $source['path'] . rawurlencode($file),
                'url' => $source['url'] . rawurlencode($file),
                'label' => $source['label'] . ' - ' . $file,
                'modified' => filemtime($filePath),
            ];
        }
    }

    usort($images, static fn($a, $b) => $b['modified'] <=> $a['modified']);
    return $images;
}

function tt_home_slide_default_images(): array
{
    return [
        ['Home classroom', 'assets/images/home.webp', 'assets/images/optimized/home-mobile.webp', 1],
        ['Practical training', 'assets/images/home1.webp', 'assets/images/optimized/home1-mobile.webp', 2],
        ['Student learning', 'assets/images/home2.webp', 'assets/images/optimized/home2-mobile.webp', 3],
        ['IT lab session', 'assets/images/home3.webp', 'assets/images/optimized/home3-mobile.webp', 4],
        ['Career training', 'assets/images/home4.webp', 'assets/images/optimized/home4-mobile.webp', 5],
    ];
}

function tt_home_slide_image_exists(string $image): bool
{
    $image = ltrim(trim($image), '/');
    if ($image === '') {
        return false;
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return true;
    }

    return is_file(__DIR__ . '/../../frontend/' . $image);
}

function tt_home_slide_admin_url(string $image): string
{
    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    return '../../frontend/' . ltrim($image, '/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim((string)($_POST['title'] ?? ''));
    $pickedImage = trim((string)($_POST['media_image'] ?? ''));
    $manualImage = trim((string)($_POST['image'] ?? ''));
    $image = $pickedImage !== '' ? $pickedImage : $manualImage;
    $pickedMobileImage = trim((string)($_POST['mobile_media_image'] ?? ''));
    $manualMobileImage = trim((string)($_POST['mobile_image'] ?? ''));
    $mobileImage = $pickedMobileImage !== '' ? $pickedMobileImage : $manualMobileImage;
    $displayOrder = (int)(($_POST['display_order'] ?? $_POST['sort_order'] ?? 0));
    $sortOrder = $displayOrder;
    $isActive = (int)($_POST['is_active'] ?? 1);

    if ($image === '') {
        $error = 'Please choose a background image from Media or enter an image path.';
    } elseif (!tt_home_slide_image_exists($image)) {
        $error = 'Selected image was not found. Upload it in Gallery / Media first.';
    } elseif ($mobileImage !== '' && !tt_home_slide_image_exists($mobileImage)) {
        $error = 'Selected mobile image was not found. Upload it in Gallery / Media first.';
    } elseif ($id > 0) {
        $stmt = $conn->prepare('UPDATE home_slides SET title=?, image=?, mobile_image=?, sort_order=?, display_order=?, is_active=? WHERE id=?');
        $stmt->bind_param('sssiiii', $title, $image, $mobileImage, $sortOrder, $displayOrder, $isActive, $id);
        if ($stmt->execute()) {
            header('Location: home_slider.php?saved=updated');
            exit;
        }
        $error = 'Database error: ' . $conn->error;
    } else {
        $existingId = 0;
        $check = $conn->prepare('SELECT id FROM home_slides WHERE image = ? LIMIT 1');
        $check->bind_param('s', $image);
        if ($check->execute()) {
            $existing = $check->get_result()->fetch_assoc();
            $existingId = (int)($existing['id'] ?? 0);
        }

        if ($existingId > 0) {
            $stmt = $conn->prepare('UPDATE home_slides SET title=?, mobile_image=?, sort_order=?, display_order=?, is_active=? WHERE id=?');
            $stmt->bind_param('ssiiii', $title, $mobileImage, $sortOrder, $displayOrder, $isActive, $existingId);
        } else {
            $stmt = $conn->prepare('INSERT INTO home_slides (title, image, mobile_image, sort_order, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssiii', $title, $image, $mobileImage, $sortOrder, $displayOrder, $isActive);
        }

        if ($stmt->execute()) {
            header('Location: home_slider.php?saved=added');
            exit;
        }
        $error = 'Database error: ' . $conn->error;
    }
}

if (isset($_GET['saved'])) {
    if ($_GET['saved'] === 'updated') {
        $success = 'Home slider image updated successfully.';
    } elseif ($_GET['saved'] === 'cleanup') {
        $success = 'Duplicate slider images cleaned successfully.';
    } else {
        $success = 'Home slider image added successfully.';
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM home_slides WHERE id = $id");
    header('Location: home_slider.php');
    exit;
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE home_slides SET is_active = 1 - is_active WHERE id = $id");
    header('Location: home_slider.php');
    exit;
}

if (isset($_GET['cleanup'])) {
    $conn->query("DELETE older FROM home_slides older
        INNER JOIN home_slides newer
            ON older.image = newer.image
            AND older.id < newer.id");
    header('Location: home_slider.php?saved=cleanup');
    exit;
}

$slideCountResult = $conn->query('SELECT COUNT(*) AS total FROM home_slides');
$slideCount = (int)(($slideCountResult ? $slideCountResult->fetch_assoc() : null)['total'] ?? 0);
if ($slideCount === 0) {
    $seed = $conn->prepare('INSERT INTO home_slides (title, image, mobile_image, sort_order, display_order, is_active) VALUES (?, ?, ?, ?, ?, 1)');
    if ($seed) {
        foreach (tt_home_slide_default_images() as [$defaultTitle, $defaultImage, $defaultMobileImage, $defaultOrder]) {
            if (!tt_home_slide_image_exists($defaultImage)) {
                continue;
            }
            $defaultMobileImage = tt_home_slide_image_exists($defaultMobileImage) ? $defaultMobileImage : '';
            $seed->bind_param('sssii', $defaultTitle, $defaultImage, $defaultMobileImage, $defaultOrder, $defaultOrder);
            $seed->execute();
        }
    }
} else {
    foreach (tt_home_slide_default_images() as [, $defaultImage, $defaultMobileImage]) {
        if (!tt_home_slide_image_exists($defaultMobileImage)) {
            continue;
        }
        $fillMobile = $conn->prepare("UPDATE home_slides SET mobile_image = ? WHERE image = ? AND (mobile_image IS NULL OR mobile_image = '')");
        if ($fillMobile) {
            $fillMobile->bind_param('ss', $defaultMobileImage, $defaultImage);
            $fillMobile->execute();
        }
    }
}

$editSlide = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editSlide = $conn->query("SELECT * FROM home_slides WHERE id = $id")->fetch_assoc();
}

$slides = $conn->query('SELECT * FROM home_slides ORDER BY display_order ASC, sort_order ASC, updated_at DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
$activeSlideCount = 0;
foreach ($slides as $slide) {
    if ((int)($slide['is_active'] ?? 0) === 1) {
        $activeSlideCount++;
    }
}
$mediaImages = tt_home_slide_media_images();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Slider - Talentteno Admin</title>
    <link rel="icon" type="image/png" href="../../frontend/assets/images/logot-transparent.png">
    <link rel="apple-touch-icon" href="../../frontend/assets/images/logot-transparent.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css?v=20260717-homepreview1">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-images"></i> Home Slider</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <p style="margin:-8px 0 18px;color:#64748B;font-size:13.5px;">
            Add background images for the homepage right-side hero. Active images scroll automatically on the live website.
        </p>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($slides && $activeSlideCount === 0): ?>
        <div class="alert alert-error"><i class="fas fa-eye-slash"></i> No active home slider images. Click the eye icon in the Status/Actions column or edit a slide and set Status to Active.</div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:start;">
            <div class="admin-card">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-<?= $editSlide ? 'edit' : 'plus' ?>" style="color:var(--blue)"></i>
                    <?= $editSlide ? 'Edit Slider Image' : 'Add Slider Image' ?>
                </h3>
                <form method="POST">
                    <?php if ($editSlide): ?><input type="hidden" name="id" value="<?= (int)$editSlide['id'] ?>"><?php endif; ?>
                    <div class="form-group">
                        <label>Title / Label</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($editSlide['title'] ?? '') ?>" placeholder="Optional admin label">
                    </div>
                    <div class="form-group">
                        <label>Background Image</label>
                        <input type="hidden" name="media_image" value="" data-media-target>
                        <?php if ($mediaImages): ?>
                        <button type="button" class="btn-media-choose" data-open-media-picker>
                            <i class="fas fa-images"></i> Choose Image from Media
                        </button>
                        <div class="course-selected-media" data-selected-media-preview hidden>
                            <img src="" alt="">
                            <span></span>
                            <button type="button" class="media-remove-btn" data-clear-selected-media aria-label="Remove selected image">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="course-media-modal" data-media-picker-modal hidden>
                            <div class="course-media-modal-backdrop" data-close-media-picker></div>
                            <div class="course-media-modal-panel">
                                <div class="course-media-modal-header">
                                    <h3>Choose Home Background Image</h3>
                                    <button type="button" class="modal-close" data-close-media-picker aria-label="Close">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="course-media-picker">
                                    <?php foreach ($mediaImages as $media): ?>
                                    <button type="button" class="course-media-option" data-pick-media="<?= htmlspecialchars($media['file']) ?>" data-media-url="<?= htmlspecialchars($media['url']) ?>" title="<?= htmlspecialchars($media['label']) ?>">
                                        <span class="course-media-thumb">
                                            <img src="<?= htmlspecialchars($media['url']) ?>" alt="<?= htmlspecialchars($media['label']) ?>">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span class="course-media-name"><?= htmlspecialchars($media['label']) ?></span>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="course-media-modal-footer">
                                    <button type="button" class="btn-cancel" data-close-media-picker>Cancel</button>
                                    <button type="button" class="btn-save" data-apply-media-picker disabled>
                                        <i class="fas fa-check"></i> Use Selected Image
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <small class="field-help">No uploaded images found. Add images in Gallery / Media first.</small>
                        <?php endif; ?>
                        <input type="text" name="image" value="<?= htmlspecialchars($editSlide['image'] ?? '') ?>" placeholder="uploads/media/example.webp or assets/images/home.webp" style="margin-top:10px;" data-image-path-input>
                        <small class="field-help">Choose from popup or paste a valid frontend image path.</small>
                        <?php if (!empty($editSlide['image'])): ?>
                        <div class="course-current-image" style="margin-top:10px;">
                            <img class="course-image-preview home-slider-preview" src="<?= htmlspecialchars(tt_home_slide_admin_url($editSlide['image'])) ?>" alt="Current home slider image">
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Mobile Background Image <small>(optional)</small></label>
                        <input type="hidden" name="mobile_media_image" value="" data-media-target>
                        <?php if ($mediaImages): ?>
                        <button type="button" class="btn-media-choose" data-open-media-picker>
                            <i class="fas fa-mobile-screen-button"></i> Choose Mobile Image from Media
                        </button>
                        <div class="course-selected-media" data-selected-media-preview hidden>
                            <img src="" alt="">
                            <span></span>
                            <button type="button" class="media-remove-btn" data-clear-selected-media aria-label="Remove selected mobile image">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="course-media-modal" data-media-picker-modal hidden>
                            <div class="course-media-modal-backdrop" data-close-media-picker></div>
                            <div class="course-media-modal-panel">
                                <div class="course-media-modal-header">
                                    <h3>Choose Mobile Background Image</h3>
                                    <button type="button" class="modal-close" data-close-media-picker aria-label="Close">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="course-media-picker">
                                    <?php foreach ($mediaImages as $media): ?>
                                    <button type="button" class="course-media-option" data-pick-media="<?= htmlspecialchars($media['file']) ?>" data-media-url="<?= htmlspecialchars($media['url']) ?>" title="<?= htmlspecialchars($media['label']) ?>">
                                        <span class="course-media-thumb">
                                            <img src="<?= htmlspecialchars($media['url']) ?>" alt="<?= htmlspecialchars($media['label']) ?>">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span class="course-media-name"><?= htmlspecialchars($media['label']) ?></span>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="course-media-modal-footer">
                                    <button type="button" class="btn-cancel" data-close-media-picker>Cancel</button>
                                    <button type="button" class="btn-save" data-apply-media-picker disabled>
                                        <i class="fas fa-check"></i> Use Selected Image
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="text" name="mobile_image" value="<?= htmlspecialchars($editSlide['mobile_image'] ?? '') ?>" placeholder="assets/images/optimized/home-mobile.webp" style="margin-top:10px;" data-image-path-input>
                        <small class="field-help">Used only on mobile view. If empty, desktop image is used.</small>
                        <?php if (!empty($editSlide['mobile_image'])): ?>
                        <div class="course-current-image" style="margin-top:10px;">
                            <img class="course-image-preview home-slider-preview" src="<?= htmlspecialchars(tt_home_slide_admin_url($editSlide['mobile_image'])) ?>" alt="Current mobile home slider image">
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="display_order" value="<?= htmlspecialchars((string)($editSlide['display_order'] ?? $editSlide['sort_order'] ?? 0)) ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="is_active">
                                <option value="1" <?= ($editSlide['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($editSlide['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Slider Image</button>
                    <?php if ($editSlide): ?><a href="home_slider.php" style="margin-left:12px;color:#64748B;font-size:13px;">Cancel</a><?php endif; ?>
                </form>
            </div>

            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Slider Images (<?= count($slides) ?>)</h3>
                    <a class="btn-xs btn-orange" href="?cleanup=1" onclick="return confirm('Remove duplicate slider image rows?');"><i class="fas fa-broom"></i> Clean Duplicates</a>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Preview</th><th>Title</th><th>Image</th><th>Mobile Image</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($slides as $slide): ?>
                            <tr>
                                <td>
                                    <img class="home-slider-table-preview" src="<?= htmlspecialchars(tt_home_slide_admin_url($slide['image'])) ?>" alt="">
                                </td>
                                <td><strong><?= htmlspecialchars($slide['title'] ?: 'Home background') ?></strong></td>
                                <td style="max-width:230px;color:#64748B;font-size:12px;"><?= htmlspecialchars($slide['image']) ?></td>
                                <td style="max-width:230px;color:#64748B;font-size:12px;"><?= htmlspecialchars($slide['mobile_image'] ?: 'Same as desktop') ?></td>
                                <td><?= (int)($slide['display_order'] ?? $slide['sort_order'] ?? 0) ?></td>
                                <td><span class="badge badge-<?= $slide['is_active'] ? 'green' : 'gray' ?>"><?= $slide['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                <td style="white-space:nowrap;">
                                    <a href="?edit=<?= (int)$slide['id'] ?>" class="btn-xs btn-blue"><i class="fas fa-edit"></i></a>
                                    <a href="?toggle=<?= (int)$slide['id'] ?>" class="btn-xs btn-<?= $slide['is_active'] ? 'orange' : 'green' ?>" title="<?= $slide['is_active'] ? 'Make inactive' : 'Activate on frontend' ?>"><i class="fas fa-<?= $slide['is_active'] ? 'eye-slash' : 'eye' ?>"></i></a>
                                    <a href="?delete=<?= (int)$slide['id'] ?>" class="btn-xs btn-red" onclick="return confirm('Delete this slider image?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$slides): ?><tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:24px;">No home slider images added yet. The frontend will use default images.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('[data-open-media-picker]').forEach((button) => {
    button.addEventListener('click', () => {
        const group = button.closest('.form-group');
        const modal = group?.querySelector('[data-media-picker-modal]');
        if (!modal) return;
        modal.hidden = false;
        modal.dataset.pendingFile = '';
        modal.dataset.pendingUrl = '';
        modal.querySelectorAll('[data-pick-media]').forEach(option => option.classList.remove('is-selected'));
        const applyButton = modal.querySelector('[data-apply-media-picker]');
        if (applyButton) applyButton.disabled = true;
    });
});
document.querySelectorAll('[data-close-media-picker]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = button.closest('[data-media-picker-modal]');
        if (modal) modal.hidden = true;
    });
});
document.querySelectorAll('[data-pick-media]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = button.closest('[data-media-picker-modal]');
        if (!modal) return;
        modal.dataset.pendingFile = button.dataset.pickMedia || '';
        modal.dataset.pendingUrl = button.dataset.mediaUrl || '';
        modal.querySelectorAll('[data-pick-media]').forEach(option => option.classList.toggle('is-selected', option === button));
        const applyButton = modal.querySelector('[data-apply-media-picker]');
        if (applyButton) applyButton.disabled = modal.dataset.pendingFile === '';
    });
});
document.querySelectorAll('[data-apply-media-picker]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = button.closest('[data-media-picker-modal]');
        const group = button.closest('.form-group');
        if (!modal || !group || !modal.dataset.pendingFile) return;
        const hiddenInput = group.querySelector('[data-media-target]');
        const pathInput = group.querySelector('[data-image-path-input]');
        const preview = group.querySelector('[data-selected-media-preview]');
        if (hiddenInput) hiddenInput.value = modal.dataset.pendingFile;
        if (pathInput) pathInput.value = modal.dataset.pendingFile;
        if (preview) {
            const image = preview.querySelector('img');
            const label = preview.querySelector('span');
            if (image) image.src = modal.dataset.pendingUrl || '';
            if (label) label.textContent = modal.dataset.pendingFile;
            preview.hidden = false;
        }
        modal.hidden = true;
    });
});
document.addEventListener('click', (event) => {
    const clearSelected = event.target.closest('[data-clear-selected-media]');
    if (!clearSelected) return;
    event.preventDefault();
    const group = clearSelected.closest('.form-group');
    const hiddenInput = group?.querySelector('[data-media-target]');
    const preview = group?.querySelector('[data-selected-media-preview]');
    if (hiddenInput) hiddenInput.value = '';
    if (preview) preview.hidden = true;
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.course-media-modal:not([hidden])').forEach((modal) => {
        modal.hidden = true;
    });
});
</script>
</body>
</html>
