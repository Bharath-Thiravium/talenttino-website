<?php
require_once 'auth_check.php';

$contentTable = $contentTable ?? '';
$contentTitle = $contentTitle ?? 'Content';
$contentIcon = $contentIcon ?? 'fa-file-lines';
$contentSingular = $contentSingular ?? 'Item';

if (!in_array($contentTable, ['careers', 'blog_posts', 'projects', 'why_items', 'hiring_items', 'franchise_items'], true)) {
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

function tt_admin_content_image_url(?string $image): string
{
    $image = ltrim(trim((string)$image), '/');
    if ($image === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }
    return '../../frontend/' . $image;
}

function tt_admin_content_upload_image(string &$error): string
{
    $file = $_FILES['image_file'] ?? null;
    if (!$file || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed. Please choose another image.';
        return '';
    }

    $tmpPath = (string)$file['tmp_name'];
    $mime = mime_content_type($tmpPath) ?: '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($extensions[$mime])) {
        $error = 'Please upload a JPG, PNG, WebP, or GIF image.';
        return '';
    }

    $uploadDir = __DIR__ . '/../../frontend/uploads/media/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $baseName = pathinfo((string)$file['name'], PATHINFO_FILENAME);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($baseName));
    $slug = trim((string)$slug, '-') ?: 'content-image';
    $fileName = $slug . '-' . date('Ymd-His') . '-' . random_int(100000, 999999) . '.' . $extensions[$mime];
    $target = $uploadDir . $fileName;

    if (!move_uploaded_file($tmpPath, $target)) {
        $error = 'Could not save uploaded image. Please try again.';
        return '';
    }

    return 'uploads/media/' . $fileName;
}

function tt_admin_content_default_items(string $table): array
{
    $defaults = [
        'careers' => [
            ['fa-user-tie', 'Placement Preparation', 'assets/images/contact-counsellor-hero.png', 'Resume, mock interview and job-readiness guidance.', 'Practice interview questions, resume structure, LinkedIn profile improvement and job-readiness steps.', 1],
            ['fa-briefcase', 'Internship to Career Path', 'assets/images/home1.webp', 'Build confidence through real project practice.', 'Move from training to internship work with guided tasks, project reviews and portfolio preparation.', 2],
            ['fa-handshake', 'Hiring Support', 'assets/images/home2.webp', 'Get guided towards suitable IT career opportunities.', 'Get counselling for suitable roles, interview readiness and placement follow-up support.', 3],
        ],
        'blog_posts' => [
            ['fa-newspaper', 'How to Choose an IT Course', 'assets/images/home1.webp', 'Pick a practical track based on your career goal.', 'Compare your interest, current skill level, project goals and placement timeline before choosing a course.', 1],
            ['fa-code', 'Why Projects Matter', 'uploads/media/full-stack-development-20260703-133158-761383.png', 'Portfolio projects help prove your skills in interviews.', 'Live projects show practical problem solving, coding confidence and the ability to explain your work.', 2],
            ['fa-lightbulb', 'Learning from Basics', 'uploads/media/programming-languages-20260703-133210-630417.png', 'A strong foundation makes advanced tools easier.', 'Start with fundamentals, practice consistently, then move into tools, frameworks and real-time tasks.', 3],
        ],
        'projects' => [
            ['fa-diagram-project', 'Live Website Project', 'uploads/media/full-stack-development-20260703-133158-761383.png', 'Build a complete responsive business website.', 'Plan pages, create responsive sections, connect enquiry forms and publish portfolio-ready work.', 1],
            ['fa-database', 'Data Dashboard Project', 'uploads/media/data-analyst-20260703-133130-702998.png', 'Practice data cleaning, reporting and visualization.', 'Use real datasets to clean data, prepare charts and explain business insights clearly.', 2],
            ['fa-shield-halved', 'Cyber Lab Project', 'uploads/media/cyber-security-20260703-133329-242125.png', 'Learn practical security workflows in guided labs.', 'Practice security basics, scanning workflow, reporting and safe lab documentation.', 3],
        ],
        'why_items' => [
            ['fa-laptop-code', 'Practical IT Training', 'assets/images/home.webp', 'Learn by building tasks, labs and live projects.', 'Training focuses on real workflow, not only theory, so students can explain and apply what they learn.', 1],
            ['fa-user-tie', 'Mentor Guidance', 'assets/images/contact-counsellor-hero.png', 'Get support from trainers during practice.', 'Students receive structured guidance, doubt clarification, project review and career direction.', 2],
            ['fa-briefcase', 'Career Support', 'assets/images/home2.webp', 'Resume, interview and placement preparation.', 'The institute supports job readiness through portfolio projects, mock interviews and placement guidance.', 3],
        ],
        'hiring_items' => [
            ['fa-chart-line', 'Data Science', 'assets/images/home1.webp', 'Data science and analytics trainer needed.', 'Python, statistics, machine learning, dashboards and practical project mentoring experience preferred.', 1],
            ['fa-pen-nib', 'UI/UX Designer', 'assets/images/home2.webp', 'UI/UX design trainer and mentor needed.', 'Figma, wireframing, prototyping, design systems and portfolio guidance experience preferred.', 2],
            ['fa-vial-circle-check', 'Software Tester', 'assets/images/contact-counsellor-hero.png', 'Manual and automation testing trainer needed.', 'Testing concepts, test cases, Selenium, API testing and real-time QA workflow experience preferred.', 3],
            ['fa-users-gear', 'Staffs Needed', 'assets/images/home.webp', 'Office, counselling and support staff needed.', 'Apply for student counselling, admission support, coordination and institute operations roles.', 4],
        ],
        'franchise_items' => [
            ['fa-handshake', 'Institute Partnership', 'assets/images/contact-counsellor-hero.png', 'Discuss Talentteno training centre partnership.', 'Share your city, space and plan. Our team will explain the partnership workflow and next steps.', 1],
            ['fa-chalkboard-teacher', 'Training Model', 'assets/images/home.webp', 'Course structure, counselling and student support.', 'Understand how practical course content, counselling, trainer coordination and student support are handled.', 2],
            ['fa-bullhorn', 'Brand Support', 'assets/images/home1.webp', 'Guidance for local admissions and promotion.', 'Get clarity on brand usage, enquiry handling, admission process and local centre operations.', 3],
        ],
    ];

    return $defaults[$table] ?? [];
}

function tt_admin_content_seed_defaults(mysqli $conn, string $table): void
{
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    $count = (int)(($countResult ? $countResult->fetch_assoc() : ['total' => 0])['total'] ?? 0);
    if ($count > 0) {
        return;
    }

    $stmt = $conn->prepare("INSERT INTO `$table` (icon, title, image, short_desc, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    foreach (tt_admin_content_default_items($table) as $item) {
        [$icon, $title, $image, $shortDesc, $description, $sortOrder] = $item;
        $stmt->bind_param('sssssi', $icon, $title, $image, $shortDesc, $description, $sortOrder);
        $stmt->execute();
    }
}

tt_admin_content_seed_defaults($conn, $contentTable);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-file-lines');
    $image = trim($_POST['image_existing'] ?? '');
    $uploadedImage = tt_admin_content_upload_image($error);
    if ($uploadedImage !== '') {
        $image = $uploadedImage;
    }
    $short_desc = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = (int)($_POST['is_active'] ?? 1);

    if ($error !== '') {
        // Keep the upload error visible and skip database write.
    } elseif ($title === '') {
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
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_item): ?><input type="hidden" name="id" value="<?= (int)$edit_item['id'] ?>"><?php endif; ?>
                    <input type="hidden" name="image_existing" value="<?= htmlspecialchars($edit_item['image'] ?? '') ?>">
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
                        <label>Image</label>
                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
                        <small class="field-help">Optional. Choose a JPG, PNG, WebP, or GIF image. If empty, frontend chooses a matching default image.</small>
                        <?php if (!empty($edit_item['image'])): ?>
                        <div class="content-current-image">
                            <img src="<?= htmlspecialchars(tt_admin_content_image_url($edit_item['image'])) ?>" alt="">
                            <span>Current image</span>
                        </div>
                        <?php endif; ?>
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
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                    <img class="content-admin-thumb" src="<?= htmlspecialchars(tt_admin_content_image_url($item['image'])) ?>" alt="">
                                    <?php else: ?>
                                    <span class="content-admin-placeholder"><i class="fas fa-image"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td class="content-detail-cell">
                                    <strong><?= htmlspecialchars($item['short_desc'] ?: 'No short description') ?></strong>
                                    <?php if (!empty($item['description'])): ?>
                                    <span><?= htmlspecialchars($item['description']) ?></span>
                                    <?php endif; ?>
                                </td>
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
