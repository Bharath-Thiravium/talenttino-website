<?php
require_once 'auth_check.php';

$contentTable = $contentTable ?? '';
$contentTitle = $contentTitle ?? 'Content';
$contentIcon = $contentIcon ?? 'fa-file-lines';
$contentSingular = $contentSingular ?? 'Item';

if (!in_array($contentTable, ['careers', 'blog_posts', 'projects'], true)) {
    http_response_code(500);
    die('Invalid content table.');
}

$conn->query("CREATE TABLE IF NOT EXISTS `$contentTable` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    icon VARCHAR(100) DEFAULT 'fa-file-lines',
    short_desc VARCHAR(500),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
$conn->query("ALTER TABLE `$contentTable` ADD COLUMN IF NOT EXISTS image VARCHAR(255) AFTER icon");

$success = '';
$error = '';
$icon_choices = [
    'fa-briefcase','fa-newspaper','fa-diagram-project','fa-laptop-code','fa-code',
    'fa-graduation-cap','fa-user-tie','fa-lightbulb','fa-rocket','fa-chart-line',
    'fa-database','fa-cloud','fa-shield-halved','fa-pen-nib','fa-handshake'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-file-lines');
    $image = trim($_POST['image'] ?? '');
    $short_desc = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = (int)($_POST['is_active'] ?? 1);

    if ($title === '') {
        $error = "$contentSingular title is required.";
    } elseif ($id > 0) {
        $stmt = $conn->prepare("UPDATE `$contentTable` SET title=?, icon=?, image=?, short_desc=?, description=?, sort_order=?, is_active=? WHERE id=?");
        $stmt->bind_param('sssssiii', $title, $icon, $image, $short_desc, $description, $sort_order, $is_active, $id);
        $success = $stmt->execute() ? "$contentSingular updated successfully." : 'Database error: ' . $conn->error;
    } else {
        $stmt = $conn->prepare("INSERT INTO `$contentTable` (title, icon, image, short_desc, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssii', $title, $icon, $image, $short_desc, $description, $sort_order, $is_active);
        $success = $stmt->execute() ? "$contentSingular added successfully." : 'Database error: ' . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM `$contentTable` WHERE id = $id");
    header('Location: ' . basename($_SERVER['PHP_SELF']));
    exit;
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE `$contentTable` SET is_active = 1 - is_active WHERE id = $id");
    header('Location: ' . basename($_SERVER['PHP_SELF']));
    exit;
}

$edit_item = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_item = $conn->query("SELECT * FROM `$contentTable` WHERE id = $id")->fetch_assoc();
}

$items = $conn->query("SELECT * FROM `$contentTable` ORDER BY sort_order ASC, id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($contentTitle) ?> - Talentteno Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas <?= htmlspecialchars($contentIcon) ?>"></i> <?= htmlspecialchars($contentTitle) ?></h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <p style="margin:-8px 0 18px;color:#64748B;font-size:13.5px;">
            Active <?= strtolower($contentTitle) ?> items appear instantly on the live frontend page.
        </p>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:start;">
            <div class="admin-card">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-<?= $edit_item ? 'edit' : 'plus' ?>" style="color:var(--blue)"></i>
                    <?= $edit_item ? 'Edit ' . htmlspecialchars($contentSingular) : 'Add ' . htmlspecialchars($contentSingular) ?>
                </h3>
                <form method="POST">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?= (int)$edit_item['id'] ?>"><?php endif; ?>
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required value="<?= htmlspecialchars($edit_item['title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select name="icon">
                            <?php foreach ($icon_choices as $ic): ?>
                            <option value="<?= $ic ?>" <?= ($edit_item['icon'] ?? '') === $ic ? 'selected' : '' ?>><?= $ic ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image Path</label>
                        <input type="text" name="image" value="<?= htmlspecialchars($edit_item['image'] ?? '') ?>" placeholder="uploads/media/example.png or assets/images/home1.webp">
                        <small style="color:#64748B;">Optional. Use an image from Media/Gallery uploads or assets. If empty, frontend chooses a matching default image.</small>
                    </div>
                    <div class="form-group">
                        <label>Short Description</label>
                        <input type="text" name="short_desc" maxlength="500" value="<?= htmlspecialchars($edit_item['short_desc'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Full Description</label>
                        <textarea name="description" rows="5"><?= htmlspecialchars($edit_item['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="sort_order" value="<?= htmlspecialchars((string)($edit_item['sort_order'] ?? 0)) ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="is_active">
                                <option value="1" <?= ($edit_item['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($edit_item['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save <?= htmlspecialchars($contentSingular) ?></button>
                    <?php if ($edit_item): ?><a href="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>" style="margin-left:12px;color:#64748B;font-size:13px;">Cancel</a><?php endif; ?>
                </form>
            </div>

            <div class="admin-card">
                <div class="card-header"><h3><i class="fas fa-list"></i> All Items (<?= count($items) ?>)</h3></div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Icon</th><th>Title</th><th>Image</th><th>Description</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><i class="fas <?= htmlspecialchars($item['icon']) ?>" style="color:var(--blue);font-size:18px;"></i></td>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td style="max-width:180px;color:#64748B;font-size:12px;"><?= htmlspecialchars($item['image'] ?? '') ?></td>
                                <td style="max-width:280px;"><?= htmlspecialchars($item['short_desc']) ?></td>
                                <td><?= (int)$item['sort_order'] ?></td>
                                <td><span class="badge badge-<?= $item['is_active'] ? 'green' : 'gray' ?>"><?= $item['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                <td style="white-space:nowrap;">
                                    <a href="?edit=<?= (int)$item['id'] ?>" class="btn-xs btn-blue"><i class="fas fa-edit"></i></a>
                                    <a href="?toggle=<?= (int)$item['id'] ?>" class="btn-xs btn-<?= $item['is_active'] ? 'orange' : 'green' ?>"><i class="fas fa-<?= $item['is_active'] ? 'eye-slash' : 'eye' ?>"></i></a>
                                    <a href="?delete=<?= (int)$item['id'] ?>" class="btn-xs btn-red" onclick="return confirm('Delete this item?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$items): ?><tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:24px;">No items added yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
