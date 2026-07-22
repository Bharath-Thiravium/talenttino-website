<?php
require_once 'auth_check.php';
$success = '';
$error = '';

$conn->query("CREATE TABLE IF NOT EXISTS review_showcase (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    icon VARCHAR(60) NOT NULL DEFAULT 'fa-image',
    image VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$countRes = $conn->query("SELECT COUNT(*) AS total FROM review_showcase");
if ((int)(($countRes ? $countRes->fetch_assoc() : ['total' => 0])['total'] ?? 0) === 0) {
    $defaults = [
        ['Full Stack Development', 'fa-code', 'uploads/media/full-stack-development-20260703-133158-761383.png', 1],
        ['AI & Machine Learning', 'fa-brain', 'uploads/media/data-science-ai-20260703-133112-527863.png', 2],
        ['Cyber Security', 'fa-shield-halved', 'uploads/media/cyber-security-20260703-133329-242125.png', 3],
        ['Data Analyst', 'fa-chart-line', 'uploads/media/data-analyst-20260703-133130-702998.png', 4],
        ['Digital Marketing', 'fa-bullhorn', 'uploads/media/digital-marketing-20260703-133146-981935.png', 5],
        ['Programming Languages', 'fa-terminal', 'uploads/media/programming-languages-20260703-133210-630417.png', 6],
    ];
    $stmt = $conn->prepare("INSERT INTO review_showcase (title, icon, image, sort_order, is_active) VALUES (?, ?, ?, ?, 1)");
    foreach ($defaults as [$title, $icon, $image, $order]) {
        $stmt->bind_param('sssi', $title, $icon, $image, $order);
        $stmt->execute();
    }
}

function tt_admin_review_image_url(?string $image): string
{
    $image = ltrim(trim((string)$image), '/');
    if ($image === '') return '';
    if (preg_match('/^https?:\/\//i', $image)) return $image;
    return '../../frontend/' . $image;
}

function tt_admin_review_upload(string &$error): string
{
    $file = $_FILES['image_file'] ?? null;
    if (!$file || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if ((int)$file['error'] !== UPLOAD_ERR_OK) { $error = 'Upload failed.'; return ''; }
    $mime = mime_content_type((string)$file['tmp_name']) ?: '';
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? '';
    if ($ext === '') { $error = 'Please upload JPG, PNG, WebP or GIF.'; return ''; }
    $uploadDir = __DIR__ . '/../../frontend/uploads/media/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
    $slug = trim((string)preg_replace('/[^a-z0-9]+/i', '-', strtolower(pathinfo((string)$file['name'], PATHINFO_FILENAME))), '-') ?: 'review';
    $fileName = $slug . '-' . date('Ymd-His') . '-' . random_int(100000, 999999) . '.' . $ext;
    if (!move_uploaded_file((string)$file['tmp_name'], $uploadDir . $fileName)) { $error = 'Could not save image.'; return ''; }
    return 'uploads/media/' . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $icon = $conn->real_escape_string(trim($_POST['icon'] ?? 'fa-image'));
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = (int)($_POST['is_active'] ?? 1);
    $imageValue = $conn->real_escape_string(trim($_POST['image_existing'] ?? ''));
    $uploaded = tt_admin_review_upload($error);
    if ($uploaded !== '') $imageValue = $conn->real_escape_string($uploaded);
    if ($error === '') {
        if ($id > 0) {
            $conn->query("UPDATE review_showcase SET title='$title',icon='$icon',image='$imageValue',sort_order=$sort_order,is_active=$is_active WHERE id=$id");
            $success = 'Updated!';
        } else {
            $conn->query("INSERT INTO review_showcase (title,icon,image,sort_order,is_active) VALUES ('$title','$icon','$imageValue',$sort_order,$is_active)");
            $success = 'Added!';
        }
    }
}

if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM review_showcase WHERE id=" . (int)$_GET['delete']);
    header('Location: review_showcase.php'); exit;
}
if (isset($_GET['toggle'])) {
    $conn->query("UPDATE review_showcase SET is_active = 1 - is_active WHERE id=" . (int)$_GET['toggle']);
    header('Location: review_showcase.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) $edit = $conn->query("SELECT * FROM review_showcase WHERE id=" . (int)$_GET['edit'])->fetch_assoc();
$items = $conn->query("SELECT * FROM review_showcase ORDER BY sort_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);

$iconOptions = [
    'fa-code' => 'Code', 'fa-brain' => 'Brain / AI', 'fa-shield-halved' => 'Shield / Security',
    'fa-chart-line' => 'Chart / Data', 'fa-bullhorn' => 'Bullhorn / Marketing', 'fa-terminal' => 'Terminal',
    'fa-cloud' => 'Cloud', 'fa-pen-ruler' => 'Design', 'fa-database' => 'Database',
    'fa-laptop-code' => 'Laptop Code', 'fa-calculator' => 'Calculator', 'fa-image' => 'Image',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Review Showcase — Talentteno Admin</title>
    <link rel="icon" type="image/png" href="../../frontend/assets/images/logot-transparent.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css?v=20260717-reviewadmin1">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-images"></i> Review Showcase Images</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:start;">
            <div class="admin-card">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;"><?= $edit ? 'Edit' : 'Add' ?> Showcase Image</h3>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
                    <input type="hidden" name="image_existing" value="<?= htmlspecialchars($edit['image'] ?? '') ?>">
                    <div class="form-group"><label>Title *</label><input type="text" name="title" required value="<?= htmlspecialchars($edit['title'] ?? '') ?>" placeholder="e.g. Full Stack Development"></div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select name="icon">
                            <?php foreach ($iconOptions as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($edit['icon'] ?? 'fa-image') === $val ? 'selected' : '' ?>><i class="fa-solid <?= $val ?>"></i> <?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
                        <small class="field-help">JPG, PNG, WebP or GIF.</small>
                        <?php if (!empty($edit['image'])): ?>
                        <div class="content-current-image"><img src="<?= htmlspecialchars(tt_admin_review_image_url($edit['image'])) ?>" alt=""><span>Current image</span></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $edit['sort_order'] ?? 0 ?>"></div>
                        <div class="form-group"><label>Status</label><select name="is_active"><option value="1" <?= ($edit['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= ($edit['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option></select></div>
                    </div>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> <?= $edit ? 'Update' : 'Add' ?></button>
                    <?php if ($edit): ?><a href="review_showcase.php" style="margin-left:12px;color:#64748B;font-size:13px;">Cancel</a><?php endif; ?>
                </form>
            </div>
            <div class="admin-card">
                <div class="card-header"><h3><i class="fas fa-list"></i> All Showcase Images (<?= count($items) ?>)</h3></div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Image</th><th>Title / Icon</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php if (!empty($item['image'])): ?><img class="content-admin-thumb" src="<?= htmlspecialchars(tt_admin_review_image_url($item['image'])) ?>" alt=""><?php else: ?><span class="content-admin-placeholder"><i class="fas fa-image"></i></span><?php endif; ?></td>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong><br><small><i class="fa-solid <?= htmlspecialchars($item['icon']) ?>"></i> <?= htmlspecialchars($item['icon']) ?></small></td>
                                <td><?= (int)$item['sort_order'] ?></td>
                                <td><span class="badge badge-<?= $item['is_active'] ? 'green' : 'gray' ?>"><?= $item['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                                <td>
                                    <a href="?edit=<?= $item['id'] ?>" class="btn-xs btn-blue"><i class="fas fa-edit"></i></a>
                                    <a href="?toggle=<?= $item['id'] ?>" class="btn-xs btn-<?= $item['is_active'] ? 'orange' : 'green' ?>"><i class="fas fa-<?= $item['is_active'] ? 'eye-slash' : 'eye' ?>"></i></a>
                                    <a href="?delete=<?= $item['id'] ?>" class="btn-xs btn-red" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
