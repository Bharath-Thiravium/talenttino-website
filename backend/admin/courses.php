<?php
require_once 'auth_check.php';

$success = '';
$error = '';

if (isset($_GET['saved'])) {
    $success = match ($_GET['saved']) {
        'added' => 'Course added successfully!',
        'deleted' => 'Course deleted successfully!',
        default => 'Course updated successfully!',
    };
}

if (isset($_GET['delete_error'])) {
    $error = 'Unable to delete course: ' . trim((string)$_GET['delete_error']);
}

// Add the catalog placement fields to databases created before this feature.
$conn->query("ALTER TABLE courses ADD COLUMN IF NOT EXISTS course_type ENUM('course', 'short', 'popular', 'advanced', 'designing', 'cyber') DEFAULT 'course' AFTER category");
$conn->query("ALTER TABLE courses MODIFY course_type ENUM('course', 'short', 'popular', 'advanced', 'designing', 'cyber') DEFAULT 'course'");
$conn->query("ALTER TABLE courses ADD COLUMN IF NOT EXISTS highlights TEXT AFTER description");
$conn->query("CREATE TABLE IF NOT EXISTS course_seed_log (
    course_type VARCHAR(30) PRIMARY KEY,
    seeded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Import the original frontend catalogs once so all three groups can be
// managed from this page. Existing database records are never overwritten.
define('TT_CATALOG_DATA_ONLY', true);
$mainCourseDefaults = [
    ['icon' => 'fa-code', 'name' => 'Programming Languages', 'desc' => 'Learn C, C++, Java, Python, PHP, .NET and SQL with practical coding.', 'items' => ['Live Coding', 'Projects', 'Interview Training']],
    ['icon' => 'fa-laptop-code', 'name' => 'Full Stack Development', 'desc' => 'HTML, CSS, JavaScript, Bootstrap, React, Django, Node.js and MySQL.', 'items' => ['Live Website', 'Internship', 'Placement Support']],
    ['icon' => 'fa-bullhorn', 'name' => 'Digital Marketing', 'desc' => 'SEO, Google Ads, Social Media Marketing, Email Marketing and Analytics.', 'items' => ['Google Ads', 'SEO', 'Live Campaigns']],
    ['icon' => 'fa-chart-line', 'name' => 'Data Analyst', 'desc' => 'Excel, SQL, Power BI, Tableau and Python for business analytics.', 'items' => ['Dashboards', 'Reports', 'Case Studies']],
    ['icon' => 'fa-database', 'name' => 'Data Science & AI', 'desc' => 'Machine Learning, Artificial Intelligence and Deep Learning.', 'items' => ['Python', 'Machine Learning', 'AI Projects']],
    ['icon' => 'fa-shield-halved', 'name' => 'Cyber Security', 'desc' => 'Ethical Hacking, Networking, Penetration Testing and Security Tools.', 'items' => ['Kali Linux', 'Live Labs', 'Certification']],
    ['icon' => 'fa-cloud', 'name' => 'Cloud Computing', 'desc' => 'Learn AWS, Microsoft Azure, DevOps, Docker and Kubernetes.', 'items' => ['AWS', 'Docker', 'Kubernetes']],
];
$catalogSources = [
    'short' => __DIR__ . '/../../frontend/shorttermcourse.php',
    'popular' => __DIR__ . '/../../frontend/popularcourse.php',
    'advanced' => __DIR__ . '/../../frontend/advancecourse.php',
    'designing' => __DIR__ . '/../../frontend/designingcourse.php',
    'cyber' => __DIR__ . '/../../frontend/cybersecuritycourse.php',
];
$catalogGroups = [];
$catalogGroups['course'] = $mainCourseDefaults;
foreach ($catalogSources as $type => $source) {
    $coursePage = [];
    require $source;
    $catalogGroups[$type] = $coursePage['courses'] ?? [];
}
unset($coursePage);

$courseTypes = [
    'course' => [
        'label' => 'Course Page',
        'add_label' => 'Add Course',
        'icon' => 'fa-book-open',
        'description' => 'Add this course only to the main Course frontend page.',
    ],
    'short' => [
        'label' => 'Short Term Course',
        'add_label' => 'Add Short Term',
        'icon' => 'fa-bolt',
        'description' => 'Add this course only to the Short Term Course frontend page.',
    ],
    'popular' => [
        'label' => 'Popular Course',
        'add_label' => 'Add Popular',
        'icon' => 'fa-fire',
        'description' => 'Add this course only to the Popular Course frontend page.',
    ],
    'advanced' => [
        'label' => 'Advanced Course',
        'add_label' => 'Add Advanced',
        'icon' => 'fa-rocket',
        'description' => 'Add this course only to the Advanced Course frontend page.',
    ],
    'designing' => [
        'label' => 'Designing Course',
        'add_label' => 'Add Designing',
        'icon' => 'fa-palette',
        'description' => 'Add this course only to the Designing Courses frontend page.',
    ],
    'cyber' => [
        'label' => 'Cyber Security',
        'add_label' => 'Add Cyber Security',
        'icon' => 'fa-shield-halved',
        'description' => 'Add this course only to the Cyber Security frontend page.',
    ],
];
$categories = ['Data & AI','Development','Marketing','Security','Design','Designing','Business'];

function tt_admin_course_slug(string $title): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
    return trim($slug, '-') ?: 'course';
}

function tt_admin_unique_course_slug(mysqli $conn, string $title, int $currentId = 0): string
{
    $baseSlug = tt_admin_course_slug($title);
    $slug = $baseSlug;
    $counter = 2;

    $stmt = $conn->prepare('SELECT id FROM courses WHERE slug = ? AND id <> ? LIMIT 1');
    if (!$stmt) {
        return $currentId > 0 ? $baseSlug . '-' . $currentId : $baseSlug . '-' . uniqid();
    }

    do {
        $stmt->bind_param('si', $slug, $currentId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        if ($exists) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    } while ($exists);

    return $slug;
}

function tt_admin_ensure_upload_dir(string $dir): bool
{
    return is_dir($dir) || mkdir($dir, 0775, true);
}

function tt_admin_media_images(): array
{
    $dir = __DIR__ . '/../../frontend/uploads/media/';
    $images = [];
    foreach (glob($dir . '*') ?: [] as $path) {
        if (!is_file($path)) continue;
        if (str_starts_with(basename($path), '.')) continue;
        $mime = mime_content_type($path) ?: '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) continue;
        $images[] = [
            'file' => basename($path),
            'label' => basename($path),
            'url' => '../../frontend/uploads/media/' . rawurlencode(basename($path)),
            'modified' => filemtime($path),
        ];
    }
    usort($images, static fn($a, $b) => $b['modified'] <=> $a['modified']);
    return $images;
}

$mediaImages = tt_admin_media_images();

$catalogExists = $conn->prepare("SELECT id FROM courses WHERE title = ? AND course_type = ? LIMIT 1");
$catalogInsert = $conn->prepare("INSERT INTO courses (title, slug, category, course_type, description, highlights, duration, fee, original_fee, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, '', 0, 0, 0, 1)");
$seedExists = $conn->prepare("SELECT course_type FROM course_seed_log WHERE course_type = ? LIMIT 1");
$seedMark = $conn->prepare("INSERT IGNORE INTO course_seed_log (course_type) VALUES (?)");
$courseCountByType = $conn->prepare("SELECT COUNT(*) AS total FROM courses WHERE course_type = ?");
if ($catalogExists && $catalogInsert && $seedExists && $seedMark && $courseCountByType) {
    foreach ($catalogGroups as $type => $catalogCourses) {
        $seedExists->bind_param('s', $type);
        $seedExists->execute();
        if ($seedExists->get_result()->num_rows > 0) {
            continue;
        }

        $courseCountByType->bind_param('s', $type);
        $courseCountByType->execute();
        $existingCount = (int)($courseCountByType->get_result()->fetch_assoc()['total'] ?? 0);
        if ($existingCount > 0) {
            $seedMark->bind_param('s', $type);
            $seedMark->execute();
            continue;
        }

        foreach ($catalogCourses as $catalogCourse) {
            $title = trim((string)($catalogCourse['name'] ?? ''));
            if ($title === '') continue;

            $catalogExists->bind_param('ss', $title, $type);
            $catalogExists->execute();
            if ($catalogExists->get_result()->num_rows > 0) continue;

            $baseSlug = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title)), '-');
            $slug = $type === 'course' ? 'course-' . $baseSlug : $baseSlug;
            $description = trim((string)($catalogCourse['desc'] ?? ''));
            $highlights = implode("\n", array_slice(array_filter(array_map('trim', $catalogCourse['items'] ?? [])), 0, 5));
            $catalogCategory = trim((string)($catalogCourse['category'] ?? ''));
            $name = strtolower($title);
            $category = in_array($catalogCategory, $categories, true)
                ? $catalogCategory
                : (str_contains($name, 'data') || str_contains($name, 'machine') || str_contains($name, 'artificial')
                    ? 'Data & AI'
                    : (str_contains($name, 'cyber') || str_contains($name, 'hack') || str_contains($name, 'ccna') || str_contains($name, 'ccnp')
                        ? 'Security'
                        : (str_contains($name, 'design') || str_contains($name, 'graphic') || str_contains($name, 'animation')
                            ? 'Design'
                            : (str_contains($name, 'tally') || str_contains($name, 'office') || str_contains($name, 'account')
                                ? 'Business'
                                : 'Development'))));

            $catalogInsert->bind_param('ssssss', $title, $slug, $category, $type, $description, $highlights);
            @$catalogInsert->execute();
        }
        $seedMark->bind_param('s', $type);
        $seedMark->execute();
    }
}

// Handle form submission - Add / Edit course
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = tt_admin_unique_course_slug($conn, $title, $id);
    $category = trim($_POST['category'] ?? '');
    $course_type = isset($courseTypes[$_POST['course_type'] ?? ''])
        ? $_POST['course_type']
        : 'course';
    $description = trim($_POST['description'] ?? '');
    $highlights = trim($_POST['highlights'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $fee = (float)($_POST['fee'] ?? 0);
    $original_fee = (float)($_POST['original_fee'] ?? 0);
    $is_featured = (int)($_POST['is_featured'] ?? 0);
    $is_active = (int)($_POST['is_active'] ?? 1);
    $upload_valid = true;

    // Course images must be chosen from the Media Library.
    $remove_course_image = (int)($_POST['remove_course_image'] ?? 0) === 1;
    $course_image = $remove_course_image ? '' : trim($_POST['existing_image'] ?? '');
    $selected_media = basename(trim($_POST['media_image'] ?? ''));

    if ($upload_valid && $selected_media !== '') {
        $source = __DIR__ . '/../../frontend/uploads/media/' . $selected_media;
        $image_dir = __DIR__ . '/../../frontend/uploads/course-images/';
        $image_mimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!is_file($source)) {
            $error = 'Selected media image was not found.';
            $upload_valid = false;
        } elseif (!tt_admin_ensure_upload_dir($image_dir)) {
            $error = 'Unable to create the course image upload folder.';
            $upload_valid = false;
        } else {
            $image_mime = mime_content_type($source);
            if (!isset($image_mimes[$image_mime])) {
                $error = 'Selected media must be JPG, PNG, or WebP.';
                $upload_valid = false;
            } else {
                $new_image = 'media_' . time() . '_' . uniqid() . '.' . $image_mimes[$image_mime];
                if (copy($source, $image_dir . $new_image)) {
                    $course_image = $new_image;
                } else {
                    $error = 'Unable to use the selected media image.';
                    $upload_valid = false;
                }
            }
        }
    }

    // Handle brochure upload
    $brochure_file = trim($_POST['existing_brochure'] ?? '');
    if (isset($_FILES['brochure_file']) && $_FILES['brochure_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = __DIR__ . '/../../frontend/uploads/brochures/';
        $ext = strtolower(pathinfo($_FILES['brochure_file']['name'], PATHINFO_EXTENSION));
        if (!tt_admin_ensure_upload_dir($upload_dir)) {
            $error = 'Unable to create the brochure upload folder.';
            $upload_valid = false;
        } elseif ($_FILES['brochure_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'The brochure upload failed. Please try again.';
            $upload_valid = false;
        } elseif ($_FILES['brochure_file']['size'] > 10 * 1024 * 1024) {
            $error = 'The brochure PDF must be 10 MB or smaller.';
            $upload_valid = false;
        } elseif ($ext !== 'pdf' || mime_content_type($_FILES['brochure_file']['tmp_name']) !== 'application/pdf') {
            $error = 'Only valid PDF brochure files are allowed.';
            $upload_valid = false;
        } else {
            $new_filename = 'brochure_' . time() . '_' . uniqid() . '.pdf';
            if (move_uploaded_file($_FILES['brochure_file']['tmp_name'], $upload_dir . $new_filename)) {
                $brochure_file = $new_filename;
            } else {
                $error = 'Unable to save the brochure PDF.';
                $upload_valid = false;
            }
        }
    }

    if ($title && $category && $upload_valid) {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE courses SET title=?, slug=?, category=?, course_type=?, description=?, highlights=?, duration=?, fee=?, original_fee=?, brochure_file=?, image=?, is_featured=?, is_active=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param('sssssssddssiii', $title, $slug, $category, $course_type, $description, $highlights, $duration, $fee, $original_fee, $brochure_file, $course_image, $is_featured, $is_active, $id);
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO courses (title, slug, category, course_type, description, highlights, duration, fee, original_fee, brochure_file, image, is_featured, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            if ($stmt) {
                $stmt->bind_param('sssssssddssii', $title, $slug, $category, $course_type, $description, $highlights, $duration, $fee, $original_fee, $brochure_file, $course_image, $is_featured, $is_active);
            }
        }

        if (!$stmt) {
            $error = 'Database error: ' . $conn->error;
        } elseif ($stmt->execute()) {
            header('Location: courses.php?saved=' . ($id > 0 ? 'updated' : 'added'));
            exit;
        } else {
            $error = 'Database error: ' . $stmt->error;
        }
    } elseif (!$title || !$category) {
        $error = 'Course title and category are required.';
    }
}

// Delete course
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM courses WHERE id = ?');
    if (!$stmt) {
        header('Location: courses.php?delete_error=' . urlencode($conn->error ?: 'Database prepare failed.'));
        exit;
    }
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        header('Location: courses.php?saved=deleted');
    } else {
        header('Location: courses.php?delete_error=' . urlencode($stmt->error ?: 'Database delete failed.'));
    }
    exit;
}

// Toggle active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE courses SET is_active = 1 - is_active WHERE id = $id");
    header('Location: courses.php');
    exit;
}

// Fetch course for edit
$edit_course = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_course = $conn->query("SELECT * FROM courses WHERE id = $id")->fetch_assoc();
}

$courses = $conn->query("SELECT * FROM courses ORDER BY is_featured DESC, id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses — Talentteno Admin</title>
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
        <h1 class="page-title"><i class="fas fa-book"></i> Manage Courses</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> All Courses (<?= count($courses) ?>)</h3>
                    <div class="course-add-actions">
                        <?php foreach ($courseTypes as $type => $meta): ?>
                        <button type="button" class="course-add-btn <?= htmlspecialchars($type) ?>" data-open-course-modal="<?= htmlspecialchars($type) ?>"><i class="fas <?= htmlspecialchars($meta['icon']) ?>"></i> <?= htmlspecialchars($meta['add_label']) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="course-filter-tabs" role="group" aria-label="Filter courses">
                    <button type="button" class="active" data-course-filter="all">All <span><?= count($courses) ?></span></button>
                    <button type="button" data-course-filter="course">Course Page <span><?= count(array_filter($courses, static fn($c) => ($c['course_type'] ?? 'course') === 'course')) ?></span></button>
                    <button type="button" data-course-filter="short">Short Term <span><?= count(array_filter($courses, static fn($c) => ($c['course_type'] ?? '') === 'short')) ?></span></button>
                    <button type="button" data-course-filter="popular">Popular <span><?= count(array_filter($courses, static fn($c) => ($c['course_type'] ?? '') === 'popular')) ?></span></button>
                    <button type="button" data-course-filter="advanced">Advanced <span><?= count(array_filter($courses, static fn($c) => ($c['course_type'] ?? '') === 'advanced')) ?></span></button>
                    <button type="button" data-course-filter="designing">Designing <span><?= count(array_filter($courses, static fn($c) => ($c['course_type'] ?? '') === 'designing')) ?></span></button>
                    <button type="button" data-course-filter="cyber">Cyber Security <span><?= count(array_filter($courses, static fn($c) => ($c['course_type'] ?? '') === 'cyber')) ?></span></button>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Image</th><th>Title</th><th>Category</th><th>Course Page</th><th>Fee</th><th>Downloads</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $c): ?>
                            <tr data-course-row="<?= htmlspecialchars($c['course_type'] ?? 'course') ?>">
                                <td>
                                    <?php if (!empty($c['image'])): ?>
                                    <img class="course-admin-thumb" src="../../frontend/uploads/course-images/<?= rawurlencode($c['image']) ?>" alt="">
                                    <?php else: ?>
                                    <span class="course-admin-placeholder"><i class="fas fa-image"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($c['title']) ?></strong>
                                    <?php if ($c['is_featured']): ?><span class="badge badge-orange" style="margin-left:4px;">Featured</span><?php endif; ?>
                                </td>
                                <td><span class="badge badge-blue"><?= htmlspecialchars($c['category']) ?></span></td>
                                <td><span class="badge badge-gray"><?= htmlspecialchars($courseTypes[$c['course_type'] ?? 'course']['label'] ?? ucfirst($c['course_type'] ?? 'course')) ?></span></td>
                                <td>₹<?= number_format($c['fee']) ?></td>
                                <td><?= $c['download_count'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $c['is_active']?'green':'gray' ?>">
                                        <?= $c['is_active']?'Active':'Inactive' ?>
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="?edit=<?= $c['id'] ?>" class="btn-xs btn-blue"><i class="fas fa-edit"></i></a>
                                    <a href="?toggle=<?= $c['id'] ?>" class="btn-xs btn-<?= $c['is_active']?'orange':'green' ?>">
                                        <i class="fas fa-<?= $c['is_active']?'eye-slash':'eye' ?>"></i>
                                    </a>
                                    <a href="?delete=<?= $c['id'] ?>" class="btn-xs btn-red" onclick="return confirm('Delete this course?')"><i class="fas fa-trash"></i></a>
                                    <?php if (!empty($c['brochure_file'])): ?>
                                    <a href="../../frontend/uploads/brochures/<?= $c['brochure_file'] ?>" target="_blank" class="btn-xs btn-green"><i class="fas fa-file-pdf"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
</div>

<?php
function tt_admin_render_course_modal(?array $course, ?string $fixedType, array $courseTypes, array $categories, bool $isOpen = false): void
{
    global $mediaImages;
    $isEdit = $course !== null;
    $isGeneralAdd = !$isEdit && $fixedType === null;
    $type = $fixedType ?: ($course['course_type'] ?? 'course');
    $meta = $courseTypes[$type] ?? $courseTypes['course'];
    $modalId = $isEdit ? 'courseModalEdit' : ($isGeneralAdd ? 'courseModalCourse' : 'courseModal' . ucfirst($type));
    $title = $isEdit ? 'Edit Course' : ($isGeneralAdd ? 'Add Course' : $meta['add_label']);
    $description = $isEdit || $isGeneralAdd ? 'Choose the frontend course page where this course should appear.' : $meta['description'];
?>
<div class="admin-modal <?= $isOpen ? 'is-open' : '' ?>" id="<?= htmlspecialchars($modalId) ?>" role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($modalId) ?>Title" aria-hidden="<?= $isOpen ? 'false' : 'true' ?>">
    <div class="admin-modal-backdrop" data-close-course-modal></div>
    <div class="admin-modal-panel">
        <div class="admin-modal-header">
            <div>
                <h2 id="<?= htmlspecialchars($modalId) ?>Title"><?= htmlspecialchars($title) ?></h2>
                <p><?= htmlspecialchars($description) ?></p>
            </div>
            <button type="button" class="modal-close" data-close-course-modal aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)($course['id'] ?? 0) ?>">
            <input type="hidden" name="existing_brochure" value="<?= htmlspecialchars($course['brochure_file'] ?? '') ?>">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($course['image'] ?? '') ?>">
            <?php if (!$isEdit && !$isGeneralAdd): ?>
            <input type="hidden" name="course_type" value="<?= htmlspecialchars($type) ?>">
            <?php endif; ?>
            <div class="admin-modal-body">
                <?php if (!$isEdit && !$isGeneralAdd): ?>
                <div class="course-form-banner <?= htmlspecialchars($type) ?>">
                    <i class="fas <?= htmlspecialchars($meta['icon']) ?>"></i>
                    <span><?= htmlspecialchars($meta['label']) ?></span>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Course Title *</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($course['title'] ?? '') ?>" placeholder="e.g. Data Science & Analytics">
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($course['category'] ?? '') === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($isEdit || $isGeneralAdd): ?>
                <div class="form-group">
                    <label>Show On Course Page *</label>
                    <select name="course_type" required>
                        <?php foreach ($courseTypes as $value => $typeMeta): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= ($course['course_type'] ?? 'course') === $value ? 'selected' : '' ?>><?= htmlspecialchars($typeMeta['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="field-help">Changing this moves the course to another frontend course page.</small>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Brief course description"><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Course Details</label>
                    <textarea name="highlights" rows="4" placeholder="Add one detail per line, e.g.&#10;Live Website&#10;Internship&#10;Placement Support"><?= htmlspecialchars($course['highlights'] ?? '') ?></textarea>
                    <small class="field-help">These lines appear as the tick-mark details on the course cards and detail popup.</small>
                </div>
                <div class="form-group">
                    <label>Course Image</label>
                    <input type="hidden" name="remove_course_image" value="0">
                    <?php if (!empty($mediaImages)): ?>
                    <input type="hidden" name="media_image" value="">
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
                    <small class="field-help">Image will change only after you choose one from the popup and click Use Selected Image.</small>
                    <div class="course-media-modal" data-media-picker-modal hidden>
                        <div class="course-media-modal-backdrop" data-close-media-picker></div>
                        <div class="course-media-modal-panel">
                            <div class="course-media-modal-header">
                                <h3>Choose Course Image</h3>
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
                    <small class="field-help">No media images found. Add images from the Media page first.</small>
                    <?php endif; ?>
                    <?php if (!empty($course['image'])): ?>
                    <div class="course-current-image" data-current-image-preview>
                        <img class="course-image-preview" src="../../frontend/uploads/course-images/<?= rawurlencode($course['image']) ?>" alt="Current course image">
                        <button type="button" class="media-remove-btn" data-remove-current-image aria-label="Remove current image">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" name="duration" value="<?= htmlspecialchars($course['duration'] ?? '') ?>" placeholder="e.g. 4 Months">
                    </div>
                    <div class="form-group">
                        <label>Course Fee (₹)</label>
                        <input type="number" name="fee" min="0" value="<?= htmlspecialchars((string)($course['fee'] ?? '')) ?>" placeholder="18000">
                    </div>
                </div>
                <div class="form-group">
                    <label>Original Fee (₹) <small>(for strikethrough)</small></label>
                    <input type="number" name="original_fee" min="0" value="<?= htmlspecialchars((string)($course['original_fee'] ?? '')) ?>" placeholder="25000">
                </div>
                <div class="form-group">
                    <label>Download Brochure PDF</label>
                    <input type="file" name="brochure_file" accept="application/pdf,.pdf">
                    <small class="field-help">PDF only. This file is connected to the course Download button.</small>
                    <?php if (!empty($course['brochure_file'])): ?>
                    <a class="current-file" href="../../frontend/uploads/brochures/<?= rawurlencode($course['brochure_file']) ?>" target="_blank">
                        <i class="fas fa-file-pdf"></i> <?= htmlspecialchars($course['brochure_file']) ?>
                    </a>
                    <?php endif; ?>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Featured</label>
                        <select name="is_featured">
                            <option value="0" <?= ($course['is_featured'] ?? 0) == 0 ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= ($course['is_featured'] ?? 0) == 1 ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active">
                            <option value="1" <?= ($course['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($course['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <a href="courses.php" class="btn-cancel" data-close-course-modal>Cancel</a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Update Course' : ($isGeneralAdd ? 'Add Course' : $meta['add_label']) ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php
}

foreach (array_keys($courseTypes) as $type) {
    tt_admin_render_course_modal(null, $type, $courseTypes, $categories);
}
if ($edit_course) {
    tt_admin_render_course_modal($edit_course, null, $courseTypes, $categories, true);
}
?>
<script>
const openCourseModal = (type = 'popular') => {
    const modal = document.getElementById(`courseModal${type.charAt(0).toUpperCase()}${type.slice(1)}`);
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    const titleInput = modal.querySelector('input[name="title"]');
    if (titleInput) titleInput.focus();
};
const closeCourseModal = () => {
    window.location.href = 'courses.php';
};

document.querySelectorAll('[data-open-course-modal]').forEach((button) => {
    button.addEventListener('click', () => openCourseModal(button.dataset.openCourseModal));
});
document.querySelectorAll('[data-close-course-modal]').forEach((button) => {
    button.addEventListener('click', (event) => {
        event.preventDefault();
        closeCourseModal();
    });
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const mediaModal = document.querySelector('.course-media-modal:not([hidden])');
    if (mediaModal) {
        mediaModal.hidden = true;
        return;
    }
    if (document.querySelector('.admin-modal.is-open')) closeCourseModal();
});
<?php if ($edit_course): ?>document.body.classList.add('modal-open');<?php endif; ?>

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

        const input = group.querySelector('input[name="media_image"]');
        const removeInput = group.querySelector('input[name="remove_course_image"]');
        const preview = group.querySelector('[data-selected-media-preview]');
        const currentPreview = group.querySelector('[data-current-image-preview]');
        if (input) input.value = modal.dataset.pendingFile;
        if (removeInput) removeInput.value = '0';
        if (preview) {
            const image = preview.querySelector('img');
            const label = preview.querySelector('span');
            if (image) image.src = modal.dataset.pendingUrl || '';
            if (label) label.textContent = modal.dataset.pendingFile;
            preview.hidden = false;
        }
        if (currentPreview) currentPreview.hidden = true;
        modal.hidden = true;
    });
});

document.addEventListener('click', (event) => {
    const clearSelected = event.target.closest('[data-clear-selected-media]');
    const removeCurrent = event.target.closest('[data-remove-current-image]');
    if (!clearSelected && !removeCurrent) return;

    event.preventDefault();
    event.stopPropagation();

    const button = clearSelected || removeCurrent;
    const group = button.closest('.form-group');
    const removeInput = group?.querySelector('input[name="remove_course_image"]');
    const mediaInput = group?.querySelector('input[name="media_image"]');
    const currentPreview = group?.querySelector('[data-current-image-preview]');
    const selectedPreview = group?.querySelector('[data-selected-media-preview]');

    if (mediaInput) mediaInput.value = '';
    if (selectedPreview) selectedPreview.hidden = true;

    if (removeCurrent) {
        if (removeInput) removeInput.value = '1';
        if (currentPreview) currentPreview.hidden = true;
        return;
    }

    if (removeInput) removeInput.value = '0';
    if (currentPreview) currentPreview.hidden = false;
});

document.querySelectorAll('[data-course-filter]').forEach((button) => {
    button.addEventListener('click', () => {
        const filter = button.dataset.courseFilter;
        document.querySelectorAll('[data-course-filter]').forEach(item => item.classList.toggle('active', item === button));
        document.querySelectorAll('[data-course-row]').forEach(row => {
            row.hidden = filter !== 'all' && row.dataset.courseRow !== filter;
        });
    });
});
</script>
</body>
</html>
