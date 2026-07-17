<?php
require_once 'auth_check.php';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = $conn->real_escape_string(trim($_POST['student_name'] ?? ''));
    $company = $conn->real_escape_string(trim($_POST['company'] ?? ''));
    $course = $conn->real_escape_string(trim($_POST['course'] ?? ''));
    $review = $conn->real_escape_string(trim($_POST['review'] ?? ''));
    $rating = (int)($_POST['rating'] ?? 5);
    $is_active = (int)($_POST['is_active'] ?? 1);
    if ($id > 0) {
        $conn->query("UPDATE testimonials SET student_name='$name',company='$company',course='$course',review='$review',rating=$rating,is_active=$is_active WHERE id=$id");
        $success = 'Testimonial updated!';
    } else {
        $conn->query("INSERT INTO testimonials (student_name,company,course,review,rating,is_active) VALUES ('$name','$company','$course','$review',$rating,$is_active)");
        $success = 'Testimonial added!';
    }
}

if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM testimonials WHERE id=" . (int)$_GET['delete']);
    header('Location: testimonials.php'); exit;
}

if (isset($_GET['toggle'])) {
    $conn->query("UPDATE testimonials SET is_active = 1 - is_active WHERE id=" . (int)$_GET['toggle']);
    header('Location: testimonials.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) $edit = $conn->query("SELECT * FROM testimonials WHERE id=" . (int)$_GET['edit'])->fetch_assoc();
$testimonials = $conn->query("SELECT * FROM testimonials ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Testimonials — Talentteno Admin</title>
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
        <h1 class="page-title"><i class="fas fa-star"></i> Testimonials</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:24px;align-items:start;">
            <div class="admin-card">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;"><?= $edit?'Edit':'Add' ?> Testimonial</h3>
                <form method="POST">
                    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
                    <div class="form-group"><label>Student Name *</label><input type="text" name="student_name" required value="<?= htmlspecialchars($edit['student_name'] ?? '') ?>"></div>
                    <div class="form-group"><label>Company</label><input type="text" name="company" value="<?= htmlspecialchars($edit['company'] ?? '') ?>"></div>
                    <div class="form-group"><label>Course</label><input type="text" name="course" value="<?= htmlspecialchars($edit['course'] ?? '') ?>"></div>
                    <div class="form-group"><label>Review *</label><textarea name="review" rows="4" required><?= htmlspecialchars($edit['review'] ?? '') ?></textarea></div>
                    <div class="form-row">
                        <div class="form-group"><label>Rating (1-5)</label><input type="number" name="rating" min="1" max="5" value="<?= $edit['rating'] ?? 5 ?>"></div>
                        <div class="form-group"><label>Status</label><select name="is_active"><option value="1" <?= ($edit['is_active']??1)==1?'selected':'' ?>>Active</option><option value="0" <?= ($edit['is_active']??1)==0?'selected':'' ?>>Inactive</option></select></div>
                    </div>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> <?= $edit?'Update':'Add' ?> Testimonial</button>
                    <?php if ($edit): ?><a href="testimonials.php" style="margin-left:12px;color:#64748B;font-size:13px;">Cancel</a><?php endif; ?>
                </form>
            </div>
            <div class="admin-card">
                <div class="card-header"><h3><i class="fas fa-list"></i> All Testimonials (<?= count($testimonials) ?>)</h3></div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Student</th><th>Company</th><th>Course</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($testimonials as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['student_name']) ?></strong></td>
                                <td><?= htmlspecialchars($t['company'] ?: '—') ?></td>
                                <td><?= htmlspecialchars($t['course'] ?: '—') ?></td>
                                <td><?= str_repeat('★', $t['rating']) ?></td>
                                <td><span class="badge badge-<?= $t['is_active']?'green':'gray' ?>"><?= $t['is_active']?'Active':'Hidden' ?></span></td>
                                <td>
                                    <a href="?edit=<?= $t['id'] ?>" class="btn-xs btn-blue"><i class="fas fa-edit"></i></a>
                                    <a href="?toggle=<?= $t['id'] ?>" class="btn-xs btn-<?= $t['is_active']?'orange':'green' ?>">
                                        <i class="fas fa-<?= $t['is_active']?'eye-slash':'eye' ?>"></i>
                                    </a>
                                    <a href="?delete=<?= $t['id'] ?>" class="btn-xs btn-red" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
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
