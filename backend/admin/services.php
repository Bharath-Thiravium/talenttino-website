<?php
require_once 'auth_check.php';

$success = '';
$error = '';

$conn->query("ALTER TABLE services ADD COLUMN IF NOT EXISTS image VARCHAR(255) AFTER icon");

// Available Font Awesome icons admin can pick from for a service card
$icon_choices = [
    'fa-laptop-code','fa-id-badge','fa-briefcase','fa-file-signature','fa-comments',
    'fa-building','fa-chalkboard-teacher','fa-brain','fa-code','fa-chart-bar',
    'fa-shield-alt','fa-database','fa-cloud','fa-mobile-alt','fa-network-wired',
    'fa-graduation-cap','fa-handshake','fa-project-diagram','fa-certificate','fa-headset'
];

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $icon = $conn->real_escape_string(trim($_POST['icon'] ?? 'fa-laptop-code'));
    $image = $conn->real_escape_string(trim($_POST['image'] ?? ''));
    $short_desc = $conn->real_escape_string(trim($_POST['short_desc'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = (int)($_POST['is_active'] ?? 1);

    if ($title) {
        if ($id > 0) {
            $sql = "UPDATE services SET title='$title', icon='$icon', image='$image', short_desc='$short_desc', description='$description', sort_order=$sort_order, is_active=$is_active WHERE id=$id";
        } else {
            $sql = "INSERT INTO services (title, icon, image, short_desc, description, sort_order, is_active) VALUES ('$title','$icon','$image','$short_desc','$description',$sort_order,$is_active)";
        }
        if ($conn->query($sql)) {
            $success = $id > 0 ? 'Service updated successfully!' : 'Service added successfully!';
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    } else {
        $error = 'Service title is required.';
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM services WHERE id = $id");
    header('Location: services.php');
    exit;
}

// Toggle active (this directly controls whether it appears on the live frontend)
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE services SET is_active = 1 - is_active WHERE id = $id");
    header('Location: services.php');
    exit;
}

// Fetch service for edit
$edit_service = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_service = $conn->query("SELECT * FROM services WHERE id = $id")->fetch_assoc();
}

$services = $conn->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services — Talentteno Admin</title>
    <link rel="icon" type="image/png" href="../../frontend/assets/images/logot-transparent.png">
    <link rel="apple-touch-icon" href="../../frontend/assets/images/logot-transparent.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-concierge-bell"></i> Manage Services</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <p style="margin:-8px 0 18px;color:#64748B;font-size:13.5px;">
            Services added here appear instantly in the <strong>"Our Services"</strong> section of the live website.
            Toggle a service to <em>Inactive</em> to hide it from the frontend without deleting it.
        </p>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:start;">
            <!-- Add / Edit Form -->
            <div class="admin-card">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-<?= $edit_service ? 'edit' : 'plus' ?>" style="color:var(--blue)"></i>
                    <?= $edit_service ? 'Edit Service' : 'Add New Service' ?>
                </h3>
                <form method="POST">
                    <?php if ($edit_service): ?>
                    <input type="hidden" name="id" value="<?= $edit_service['id'] ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Service Title *</label>
                        <input type="text" name="title" required value="<?= htmlspecialchars($edit_service['title'] ?? '') ?>" placeholder="e.g. Resume & Interview Preparation">
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select name="icon">
                            <?php foreach ($icon_choices as $ic): ?>
                            <option value="<?= $ic ?>" <?= ($edit_service['icon'] ?? '')===$ic?'selected':'' ?>><?= $ic ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:#64748B;">Font Awesome icon shown on the service card.</small>
                    </div>
                    <div class="form-group">
                        <label>Image Path</label>
                        <input type="text" name="image" value="<?= htmlspecialchars($edit_service['image'] ?? '') ?>" placeholder="uploads/media/example.png or assets/images/home.jpeg">
                        <small style="color:#64748B;">Optional. Use an image from Media/Gallery uploads or assets. If empty, frontend chooses a matching default image.</small>
                    </div>
                    <div class="form-group">
                        <label>Short Description (card preview)</label>
                        <input type="text" name="short_desc" maxlength="500" value="<?= htmlspecialchars($edit_service['short_desc'] ?? '') ?>" placeholder="One line shown on the service card">
                    </div>
                    <div class="form-group">
                        <label>Full Description</label>
                        <textarea name="description" rows="4" placeholder="Detailed description of this service"><?= htmlspecialchars($edit_service['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="sort_order" value="<?= $edit_service['sort_order'] ?? 0 ?>" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="is_active">
                                <option value="1" <?= ($edit_service['is_active'] ?? 1)==1?'selected':'' ?>>Active (visible on site)</option>
                                <option value="0" <?= ($edit_service['is_active'] ?? 1)==0?'selected':'' ?>>Inactive (hidden)</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> <?= $edit_service ? 'Update Service' : 'Add Service' ?>
                    </button>
                    <?php if ($edit_service): ?>
                    <a href="services.php" style="margin-left:12px;color:#64748B;font-size:13px;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Services List -->
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> All Services (<?= count($services) ?>)</h3>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Icon</th><th>Title</th><th>Image</th><th>Short Description</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                            <tr>
                                <td><i class="fas <?= htmlspecialchars($s['icon']) ?>" style="color:var(--blue);font-size:18px;"></i></td>
                                <td><strong><?= htmlspecialchars($s['title']) ?></strong></td>
                                <td style="max-width:180px;color:#64748B;font-size:12px;"><?= htmlspecialchars($s['image'] ?? '') ?></td>
                                <td style="max-width:260px;"><?= htmlspecialchars($s['short_desc']) ?></td>
                                <td><?= (int)$s['sort_order'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $s['is_active']?'green':'gray' ?>">
                                        <?= $s['is_active']?'Active':'Inactive' ?>
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="?edit=<?= $s['id'] ?>" class="btn-xs btn-blue"><i class="fas fa-edit"></i></a>
                                    <a href="?toggle=<?= $s['id'] ?>" class="btn-xs btn-<?= $s['is_active']?'orange':'green' ?>">
                                        <i class="fas fa-<?= $s['is_active']?'eye-slash':'eye' ?>"></i>
                                    </a>
                                    <a href="?delete=<?= $s['id'] ?>" class="btn-xs btn-red" onclick="return confirm('Delete this service?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($services)): ?>
                            <tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:24px;">No services added yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
