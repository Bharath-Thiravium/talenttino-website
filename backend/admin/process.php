<?php
require_once 'auth_check.php';

$success = '';
$error = '';

$icon_choices = [
    'fa-comments','fa-user-graduate','fa-project-diagram','fa-certificate',
    'fa-comments-dollar','fa-handshake','fa-flag','fa-laptop-code','fa-search',
    'fa-file-signature','fa-briefcase','fa-clipboard-check'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $step_number = (int)($_POST['step_number'] ?? 1);
    $title = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $icon = $conn->real_escape_string(trim($_POST['icon'] ?? 'fa-flag'));
    $sort_order = (int)($_POST['sort_order'] ?? $step_number);
    $is_active = (int)($_POST['is_active'] ?? 1);

    if ($title) {
        if ($id > 0) {
            $sql = "UPDATE process_steps SET step_number=$step_number, title='$title', description='$description', icon='$icon', sort_order=$sort_order, is_active=$is_active WHERE id=$id";
        } else {
            $sql = "INSERT INTO process_steps (step_number, title, description, icon, sort_order, is_active) VALUES ($step_number,'$title','$description','$icon',$sort_order,$is_active)";
        }
        if ($conn->query($sql)) {
            $success = $id > 0 ? 'Step updated successfully!' : 'Step added successfully!';
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    } else {
        $error = 'Step title is required.';
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM process_steps WHERE id = $id");
    header('Location: process.php');
    exit;
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE process_steps SET is_active = 1 - is_active WHERE id = $id");
    header('Location: process.php');
    exit;
}

$edit_step = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_step = $conn->query("SELECT * FROM process_steps WHERE id = $id")->fetch_assoc();
}

$steps = $conn->query("SELECT * FROM process_steps ORDER BY sort_order ASC, step_number ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Process Steps — Talentteno Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-route"></i> Manage Process Steps</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <p style="margin:-8px 0 18px;color:#64748B;font-size:13.5px;">
            These steps power the <strong>"How It Works"</strong> timeline on the homepage — from enquiry to job placement.
        </p>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:start;">
            <div class="admin-card">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-<?= $edit_step ? 'edit' : 'plus' ?>" style="color:var(--blue)"></i>
                    <?= $edit_step ? 'Edit Step' : 'Add New Step' ?>
                </h3>
                <form method="POST">
                    <?php if ($edit_step): ?>
                    <input type="hidden" name="id" value="<?= $edit_step['id'] ?>">
                    <?php endif; ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Step Number</label>
                            <input type="number" name="step_number" value="<?= $edit_step['step_number'] ?? (count($steps)+1) ?>" min="1">
                        </div>
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="sort_order" value="<?= $edit_step['sort_order'] ?? (count($steps)+1) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Step Title *</label>
                        <input type="text" name="title" required value="<?= htmlspecialchars($edit_step['title'] ?? '') ?>" placeholder="e.g. Free Counselling">
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select name="icon">
                            <?php foreach ($icon_choices as $ic): ?>
                            <option value="<?= $ic ?>" <?= ($edit_step['icon'] ?? '')===$ic?'selected':'' ?>><?= $ic ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Short description of this step"><?= htmlspecialchars($edit_step['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active">
                            <option value="1" <?= ($edit_step['is_active'] ?? 1)==1?'selected':'' ?>>Active (visible on site)</option>
                            <option value="0" <?= ($edit_step['is_active'] ?? 1)==0?'selected':'' ?>>Inactive (hidden)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> <?= $edit_step ? 'Update Step' : 'Add Step' ?>
                    </button>
                    <?php if ($edit_step): ?>
                    <a href="process.php" style="margin-left:12px;color:#64748B;font-size:13px;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> All Steps (<?= count($steps) ?>)</h3>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr><th>#</th><th>Icon</th><th>Title</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($steps as $s): ?>
                            <tr>
                                <td><?= (int)$s['step_number'] ?></td>
                                <td><i class="fas <?= htmlspecialchars($s['icon']) ?>" style="color:var(--blue);font-size:18px;"></i></td>
                                <td><strong><?= htmlspecialchars($s['title']) ?></strong></td>
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
                                    <a href="?delete=<?= $s['id'] ?>" class="btn-xs btn-red" onclick="return confirm('Delete this step?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($steps)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:24px;">No steps added yet.</td></tr>
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
