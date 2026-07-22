<?php
require_once 'auth_check.php';
$success = '';
$error = '';

$conn->query("ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS image VARCHAR(255) AFTER company");

function tt_admin_testimonial_image_url(?string $image): string
{
    $image = ltrim(trim((string)$image), '/');
    if ($image === '') return '';
    if (preg_match('/^https?:\/\//i', $image)) return $image;
    return '../../frontend/' . $image;
}

function tt_admin_testimonial_upload_image(string &$error): string
{
    $file = $_FILES['image_file'] ?? null;
    if (!$file || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed. Please choose another image.';
        return '';
    }
    $mime = mime_content_type((string)$file['tmp_name']) ?: '';
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($extensions[$mime])) {
        $error = 'Please upload a JPG, PNG, WebP, or GIF image.';
        return '';
    }
    $uploadDir = __DIR__ . '/../../frontend/uploads/media/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
    $baseName = pathinfo((string)$file['name'], PATHINFO_FILENAME);
    $slug = trim((string)preg_replace('/[^a-z0-9]+/i', '-', strtolower($baseName)), '-') ?: 'review-image';
    $fileName = $slug . '-' . date('Ymd-His') . '-' . random_int(100000, 999999) . '.' . $extensions[$mime];
    if (!move_uploaded_file((string)$file['tmp_name'], $uploadDir . $fileName)) {
        $error = 'Could not save uploaded image. Please try again.';
        return '';
    }
    return 'uploads/media/' . $fileName;
}

$countResult = $conn->query("SELECT COUNT(*) AS total FROM testimonials");
$testimonialCount = (int)(($countResult ? $countResult->fetch_assoc() : ['total' => 0])['total'] ?? 0);
if ($testimonialCount === 0) {
    $defaults = [
        ['Deepa Krishnan', '', 'assets/images/home.webp', 'AI & Machine Learning', 'Best institute in Madurai for AI/ML. Live project experience was outstanding.', 5],
        ['Arun Murugan', '', 'assets/images/home1.webp', 'Digital Marketing', 'From zero knowledge to a Digital Marketing Manager role - Talentteno made it possible!', 5],
        ['Priya Sundaram', '', 'assets/images/home2.webp', 'Data Science', 'The trainers are industry experts. Hands-on projects made the difference. Highly recommend!', 5],
    ];
    $stmt = $conn->prepare("INSERT INTO testimonials (student_name, company, image, course, review, rating, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    foreach ($defaults as $item) {
        [$name, $company, $image, $course, $review, $rating] = $item;
        $stmt->bind_param('sssssi', $name, $company, $image, $course, $review, $rating);
        $stmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = $conn->real_escape_string(trim($_POST['student_name'] ?? ''));
    $company = $conn->real_escape_string(trim($_POST['company'] ?? ''));
    $imageValue = trim($_POST['image_existing'] ?? '');
    $uploadedImage = tt_admin_testimonial_upload_image($error);
    if ($uploadedImage !== '') $imageValue = $uploadedImage;
    $image = $conn->real_escape_string($imageValue);
    $course = $conn->real_escape_string(trim($_POST['course'] ?? ''));
    $review = $conn->real_escape_string(trim($_POST['review'] ?? ''));
    $rating = (int)($_POST['rating'] ?? 5);
    $is_active = (int)($_POST['is_active'] ?? 1);
    if ($error !== '') {
        // Keep upload error visible.
    } elseif ($id > 0) {
        $conn->query("UPDATE testimonials SET student_name='$name',company='$company',image='$image',course='$course',review='$review',rating=$rating,is_active=$is_active WHERE id=$id");
        $success = 'Testimonial updated!';
    } else {
        $conn->query("INSERT INTO testimonials (student_name,company,image,course,review,rating,is_active) VALUES ('$name','$company','$image','$course','$review',$rating,$is_active)");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <h1 class="page-title"><i class="fas fa-star"></i> Testimonials</h1>
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
                <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;"><?= $edit?'Edit':'Add' ?> Testimonial</h3>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
                    <input type="hidden" name="image_existing" value="<?= htmlspecialchars($edit['image'] ?? '') ?>">
                    <div class="form-group"><label>Student Name *</label><input type="text" name="student_name" required value="<?= htmlspecialchars($edit['student_name'] ?? '') ?>"></div>
                    <div class="form-group"><label>Company</label><input type="text" name="company" value="<?= htmlspecialchars($edit['company'] ?? '') ?>"></div>
                    <div class="form-group"><label>Image</label><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif"><small class="field-help">Optional. Choose a JPG, PNG, WebP, or GIF image.</small><?php if (!empty($edit['image'])): ?><div class="content-current-image"><img src="<?= htmlspecialchars(tt_admin_testimonial_image_url($edit['image'])) ?>" alt=""><span>Current image</span></div><?php endif; ?></div>
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
                        <thead><tr><th>Student</th><th>Image</th><th>Course / Review</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($testimonials as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['student_name']) ?></strong></td>
                                <td><?php if (!empty($t['image'])): ?><img class="content-admin-thumb" src="<?= htmlspecialchars(tt_admin_testimonial_image_url($t['image'])) ?>" alt=""><?php else: ?><span class="content-admin-placeholder"><i class="fas fa-image"></i></span><?php endif; ?></td>
                                <td class="content-detail-cell"><strong><?= htmlspecialchars($t['course'] ?: '—') ?></strong><span><?= htmlspecialchars($t['review'] ?: '') ?></span></td>
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
