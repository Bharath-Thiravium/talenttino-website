<?php
require_once 'auth_check.php';

$success = '';
$error = '';

$conn->query("CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    course_name VARCHAR(255) NOT NULL DEFAULT '',
    category VARCHAR(100) NOT NULL DEFAULT '',
    badge_text VARCHAR(100) NOT NULL DEFAULT 'This Week Only',
    short_description VARCHAR(500) NOT NULL DEFAULT '',
    full_description TEXT,
    poster_image VARCHAR(255) NOT NULL DEFAULT '',
    poster_alt VARCHAR(255) NOT NULL DEFAULT '',
    original_fee DECIMAL(10,2) DEFAULT 0,
    offer_fee DECIMAL(10,2) DEFAULT 0,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'INR',
    emi_available TINYINT(1) NOT NULL DEFAULT 0,
    emi_description VARCHAR(255) NOT NULL DEFAULT '',
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    registration_end_date DATE DEFAULT NULL,
    available_seats INT DEFAULT NULL,
    seats_filled INT NOT NULL DEFAULT 0,
    show_remaining_seats TINYINT(1) NOT NULL DEFAULT 1,
    course_duration VARCHAR(100) NOT NULL DEFAULT '',
    training_mode ENUM('Offline','Online','Hybrid') NOT NULL DEFAULT 'Offline',
    batch_timing VARCHAR(150) NOT NULL DEFAULT '',
    course_level ENUM('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
    certificate_available TINYINT(1) NOT NULL DEFAULT 1,
    internship_available TINYINT(1) NOT NULL DEFAULT 0,
    placement_available TINYINT(1) NOT NULL DEFAULT 0,
    highlights TEXT,
    learning_outcomes TEXT,
    eligibility TEXT,
    career_opportunities TEXT,
    terms_conditions TEXT,
    cta_label VARCHAR(100) NOT NULL DEFAULT 'Reserve Your Seat',
    cta_url VARCHAR(255) NOT NULL DEFAULT 'contact.php',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    show_on_offers_page TINYINT(1) NOT NULL DEFAULT 1,
    show_on_home_page TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft','active','inactive') NOT NULL DEFAULT 'draft',
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_display_order (display_order),
    UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (isset($_GET['saved'])) {
    $success = match ($_GET['saved']) {
        'added' => 'Offer added successfully.',
        'deleted' => 'Offer deleted successfully.',
        default => 'Offer updated successfully.',
    };
}

function tt_admin_offer_slug(string $title): string
{
    $slug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
    return $slug !== '' ? $slug : 'course-offer';
}

function tt_admin_offer_unique_slug(mysqli $conn, string $title, int $currentId = 0): string
{
    $base = tt_admin_offer_slug($title);
    $slug = $base;
    $i = 2;
    $stmt = $conn->prepare('SELECT id FROM offers WHERE slug = ? AND id <> ? LIMIT 1');
    if (!$stmt) return $base . '-' . uniqid();
    do {
        $stmt->bind_param('si', $slug, $currentId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        if ($exists) {
            $slug = $base . '-' . $i++;
        }
    } while ($exists);
    return $slug;
}

function tt_admin_offer_upload_dir(): string
{
    return __DIR__ . '/../../frontend/uploads/offer-posters/';
}

function tt_admin_offer_image_url(?string $image): string
{
    $image = ltrim(trim((string)$image), '/');
    if ($image === '') return '';
    if (preg_match('/^https?:\/\//i', $image)) return $image;
    return '../../frontend/' . $image;
}

function tt_admin_offer_safe_name(string $name): string
{
    $base = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', pathinfo($name, PATHINFO_FILENAME)), '-'));
    return $base !== '' ? $base : 'offer-poster';
}

function tt_admin_offer_make_webp(string $source, string $target, string $mime, int $maxWidth = 1000): bool
{
    $create = match ($mime) {
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
        default => '',
    };
    if ($create === '' || !function_exists($create) || !function_exists('imagewebp')) {
        return false;
    }

    $info = @getimagesize($source);
    if (!$info) return false;
    $src = @$create($source);
    if (!$src) return false;

    $sourceWidth = (int)$info[0];
    $sourceHeight = (int)$info[1];
    $scale = min(1, $maxWidth / max(1, $sourceWidth));
    $targetWidth = max(1, (int)round($sourceWidth * $scale));
    $targetHeight = max(1, (int)round($sourceHeight * $scale));
    $dst = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($dst, true);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
    $ok = imagewebp($dst, $target, 72);
    imagedestroy($dst);
    imagedestroy($src);
    return $ok && is_file($target);
}

function tt_admin_offer_save_image(string &$error): string
{
    $uploadDir = tt_admin_offer_upload_dir();
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
        $error = 'Unable to create offer poster folder.';
        return '';
    }

    $selected = basename(trim((string)($_POST['media_image'] ?? '')));
    if ($selected !== '') {
        $source = __DIR__ . '/../../frontend/uploads/media/' . $selected;
        if (!is_file($source)) {
            $error = 'Selected media image was not found.';
            return '';
        }
        $mime = mime_content_type($source) ?: '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $error = 'Selected media must be JPG, PNG, or WebP.';
            return '';
        }
        $name = tt_admin_offer_safe_name($selected) . '-' . date('Ymd-His') . '.webp';
        if (tt_admin_offer_make_webp($source, $uploadDir . $name, $mime)) {
            return 'uploads/offer-posters/' . $name;
        }
        $copyName = tt_admin_offer_safe_name($selected) . '-' . date('Ymd-His') . '.' . pathinfo($selected, PATHINFO_EXTENSION);
        return copy($source, $uploadDir . $copyName) ? 'uploads/offer-posters/' . $copyName : '';
    }

    $file = $_FILES['poster_file'] ?? null;
    if (!$file || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Poster upload failed. Please choose another image.';
        return '';
    }
    if ((int)$file['size'] > 8 * 1024 * 1024) {
        $error = 'Poster image must be 8 MB or smaller.';
        return '';
    }

    $tmp = (string)$file['tmp_name'];
    $mime = is_file($tmp) ? (mime_content_type($tmp) ?: '') : '';
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($extensions[$mime])) {
        $error = 'Please upload a JPG, PNG, WebP, or GIF poster.';
        return '';
    }

    $base = tt_admin_offer_safe_name((string)$file['name']) . '-' . date('Ymd-His') . '-' . substr(uniqid('', true), -6);
    if ($mime !== 'image/gif' && tt_admin_offer_make_webp($tmp, $uploadDir . $base . '.webp', $mime)) {
        return 'uploads/offer-posters/' . $base . '.webp';
    }

    $name = $base . '.' . $extensions[$mime];
    return move_uploaded_file($tmp, $uploadDir . $name) ? 'uploads/offer-posters/' . $name : '';
}

function tt_admin_offer_date_sql(string $key): string
{
    $date = trim((string)($_POST[$key] ?? ''));
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? "'" . $date . "'" : 'NULL';
}

function tt_admin_offer_seed_defaults(mysqli $conn): void
{
    $result = $conn->query('SELECT COUNT(*) AS total FROM offers');
    $total = $result ? (int)($result->fetch_assoc()['total'] ?? 0) : 0;
    if ($total > 0) {
        return;
    }

    $defaults = [
        [
            'title' => 'Basic to Advanced Course Offer',
            'slug' => 'basic-to-advanced-course-offer',
            'course_name' => 'Selected IT Courses',
            'category' => 'Development',
            'badge_text' => 'Popular Offer',
            'short_description' => 'Start from fundamentals and move into practical job-ready training with mentor guidance.',
            'full_description' => 'Best for students and freshers who want a complete guided path with projects, certification and career support.',
            'poster_image' => 'assets/images/home.webp',
            'poster_alt' => 'Students learning technology at Talentteno Institute',
            'original_fee' => 25000,
            'offer_fee' => 14999,
            'discount_percentage' => 40,
            'course_duration' => '3 to 6 Months',
            'training_mode' => 'Offline',
            'batch_timing' => 'Weekday and weekend batches',
            'course_level' => 'Beginner',
            'highlights' => "Live classes\nPractical assignments\nCertificate support\nInterview guidance",
            'learning_outcomes' => "Build portfolio projects\nUnderstand real workflow\nPrepare for entry-level roles",
            'terms_conditions' => "Offer available for selected batches\nSeat confirmation depends on registration",
            'cta_label' => 'Claim Offer',
            'cta_url' => 'contact.php?topic=Basic+to+Advanced+Course+Offer',
            'is_featured' => 1,
            'display_order' => 1,
        ],
        [
            'title' => 'Cyber Security Combo Pack',
            'slug' => 'cyber-security-combo-pack',
            'course_name' => 'Cyber Security',
            'category' => 'Security',
            'badge_text' => 'Combo Deal',
            'short_description' => 'Learn networking, ethical hacking basics, practical labs and security reporting in one guided combo.',
            'full_description' => 'Designed for learners who want a focused security pathway with lab practice and career support.',
            'poster_image' => 'uploads/media/cyber-security-20260703-133329-242125.png',
            'poster_alt' => 'Cyber security training offer',
            'original_fee' => 75000,
            'offer_fee' => 49999,
            'discount_percentage' => 33,
            'course_duration' => '6 Months',
            'training_mode' => 'Hybrid',
            'batch_timing' => 'Flexible batch timing',
            'course_level' => 'Intermediate',
            'highlights' => "Security lab practice\nNetwork basics\nEthical hacking workflow\nResume support",
            'learning_outcomes' => "Practice safe security testing\nCreate reports\nPrepare for support roles",
            'terms_conditions' => "Combo offer cannot be combined with other discounts\nContact office for current seat availability",
            'cta_label' => 'Reserve Seat',
            'cta_url' => 'contact.php?topic=Cyber+Security+Combo+Offer',
            'is_featured' => 1,
            'display_order' => 2,
        ],
        [
            'title' => 'Student Group Enrollment',
            'slug' => 'student-group-enrollment',
            'course_name' => 'All Eligible Courses',
            'category' => 'Students',
            'badge_text' => 'Group Benefit',
            'short_description' => 'Enroll with friends and get structured training support with practical sessions and counselling.',
            'full_description' => 'Useful for college students, classmates and small teams who want the same batch timing.',
            'poster_image' => 'assets/images/home2.webp',
            'poster_alt' => 'Student group enrollment offer',
            'original_fee' => 0,
            'offer_fee' => 0,
            'discount_percentage' => 0,
            'course_duration' => 'Course based',
            'training_mode' => 'Offline',
            'batch_timing' => 'Custom group slots',
            'course_level' => 'Beginner',
            'highlights' => "Group batch planning\nMentor-led practice\nCareer counselling\nCertificate support",
            'learning_outcomes' => "Learn together\nFinish practical tasks\nPrepare for interviews",
            'terms_conditions' => "Minimum group size applies\nOffer depends on selected course and batch",
            'cta_label' => 'Ask Group Fee',
            'cta_url' => 'contact.php?topic=Group+Enrollment+Offer',
            'is_featured' => 0,
            'display_order' => 3,
        ],
    ];

    foreach ($defaults as $offer) {
        $textFields = ['title','slug','course_name','category','badge_text','short_description','full_description','poster_image','poster_alt','course_duration','training_mode','batch_timing','course_level','highlights','learning_outcomes','terms_conditions','cta_label','cta_url'];
        $columns = [];
        $values = [];
        foreach ($textFields as $field) {
            $columns[] = $field;
            $values[] = "'" . $conn->real_escape_string((string)($offer[$field] ?? '')) . "'";
        }
        foreach (['original_fee','offer_fee','discount_percentage','is_featured','display_order'] as $field) {
            $columns[] = $field;
            $values[] = (string)($offer[$field] ?? 0);
        }
        $columns = array_merge($columns, ['certificate_available','internship_available','placement_available','show_remaining_seats','show_on_offers_page','status']);
        $values = array_merge($values, ['1','1','1','1','1',"'active'"]);
        $conn->query('INSERT INTO offers (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')');
    }
}

tt_admin_offer_seed_defaults($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim((string)($_POST['title'] ?? ''));
    $posterImage = (int)($_POST['remove_poster'] ?? 0) === 1 ? '' : trim((string)($_POST['poster_existing'] ?? ''));
    $uploaded = tt_admin_offer_save_image($error);
    if ($uploaded !== '') {
        $posterImage = $uploaded;
    }

    if ($title === '') {
        $error = 'Offer title is required.';
    }

    if ($error === '') {
        $slug = tt_admin_offer_unique_slug($conn, $title, $id);
        $fields = [
            'title' => $title,
            'slug' => $slug,
            'course_name' => trim((string)($_POST['course_name'] ?? '')),
            'category' => trim((string)($_POST['category'] ?? '')),
            'badge_text' => trim((string)($_POST['badge_text'] ?? 'This Week Only')),
            'short_description' => trim((string)($_POST['short_description'] ?? '')),
            'full_description' => trim((string)($_POST['full_description'] ?? '')),
            'poster_image' => $posterImage,
            'poster_alt' => trim((string)($_POST['poster_alt'] ?? '')),
            'currency' => trim((string)($_POST['currency'] ?? 'INR')) ?: 'INR',
            'emi_description' => trim((string)($_POST['emi_description'] ?? '')),
            'course_duration' => trim((string)($_POST['course_duration'] ?? '')),
            'training_mode' => in_array($_POST['training_mode'] ?? '', ['Offline','Online','Hybrid'], true) ? $_POST['training_mode'] : 'Offline',
            'batch_timing' => trim((string)($_POST['batch_timing'] ?? '')),
            'course_level' => in_array($_POST['course_level'] ?? '', ['Beginner','Intermediate','Advanced'], true) ? $_POST['course_level'] : 'Beginner',
            'highlights' => trim((string)($_POST['highlights'] ?? '')),
            'learning_outcomes' => trim((string)($_POST['learning_outcomes'] ?? '')),
            'eligibility' => trim((string)($_POST['eligibility'] ?? '')),
            'career_opportunities' => trim((string)($_POST['career_opportunities'] ?? '')),
            'terms_conditions' => trim((string)($_POST['terms_conditions'] ?? '')),
            'cta_label' => trim((string)($_POST['cta_label'] ?? 'Reserve Your Seat')) ?: 'Reserve Your Seat',
            'cta_url' => trim((string)($_POST['cta_url'] ?? 'contact.php')) ?: 'contact.php',
            'status' => in_array($_POST['status'] ?? '', ['draft','active','inactive'], true) ? $_POST['status'] : 'draft',
        ];
        $num = [
            'original_fee' => (float)($_POST['original_fee'] ?? 0),
            'offer_fee' => (float)($_POST['offer_fee'] ?? 0),
            'discount_percentage' => (float)($_POST['discount_percentage'] ?? 0),
            'emi_available' => isset($_POST['emi_available']) ? 1 : 0,
            'available_seats' => trim((string)($_POST['available_seats'] ?? '')) === '' ? 'NULL' : max(0, (int)$_POST['available_seats']),
            'seats_filled' => max(0, (int)($_POST['seats_filled'] ?? 0)),
            'show_remaining_seats' => isset($_POST['show_remaining_seats']) ? 1 : 0,
            'certificate_available' => isset($_POST['certificate_available']) ? 1 : 0,
            'internship_available' => isset($_POST['internship_available']) ? 1 : 0,
            'placement_available' => isset($_POST['placement_available']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'show_on_offers_page' => isset($_POST['show_on_offers_page']) ? 1 : 0,
            'show_on_home_page' => isset($_POST['show_on_home_page']) ? 1 : 0,
            'display_order' => (int)($_POST['display_order'] ?? 0),
        ];

        $escaped = [];
        foreach ($fields as $key => $value) {
            $escaped[$key] = "'" . $conn->real_escape_string($value) . "'";
        }
        foreach ($num as $key => $value) {
            $escaped[$key] = (string)$value;
        }
        $escaped['start_date'] = tt_admin_offer_date_sql('start_date');
        $escaped['end_date'] = tt_admin_offer_date_sql('end_date');
        $escaped['registration_end_date'] = tt_admin_offer_date_sql('registration_end_date');

        if ($id > 0) {
            $sets = [];
            foreach ($escaped as $key => $value) {
                $sets[] = "$key = $value";
            }
            $ok = $conn->query('UPDATE offers SET ' . implode(', ', $sets) . ' WHERE id = ' . $id);
        } else {
            $columns = implode(', ', array_keys($escaped));
            $values = implode(', ', array_values($escaped));
            $ok = $conn->query("INSERT INTO offers ($columns) VALUES ($values)");
        }

        if ($ok) {
            header('Location: offers.php?saved=' . ($id > 0 ? 'updated' : 'added'));
            exit;
        }
        $error = 'Database error: ' . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $conn->query("DELETE FROM offers WHERE id = $id");
    }
    header('Location: offers.php?saved=deleted');
    exit;
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id > 0) {
        $conn->query("UPDATE offers SET status = IF(status = 'active', 'inactive', 'active') WHERE id = $id");
    }
    header('Location: offers.php');
    exit;
}

$editOffer = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editOffer = $conn->query("SELECT * FROM offers WHERE id = $id")->fetch_assoc();
}

$offers = $conn->query("SELECT * FROM offers ORDER BY is_featured DESC, display_order ASC, id DESC")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT title, category FROM courses WHERE is_active = 1 ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
$mediaImages = [];
foreach (glob(__DIR__ . '/../../frontend/uploads/media/*') ?: [] as $path) {
    if (!is_file($path)) continue;
    $mime = mime_content_type($path) ?: '';
    if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) continue;
    $mediaImages[] = basename($path);
}
usort($mediaImages, 'strnatcasecmp');

$value = static fn(string $key, $default = ''): string => htmlspecialchars((string)($editOffer[$key] ?? $default), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Offers - Talentteno Admin</title>
    <link rel="icon" type="image/png" href="../../frontend/assets/images/logot-transparent.png?v=20260722-logo2">
    <link rel="apple-touch-icon" href="../../frontend/assets/images/logot-transparent.png?v=20260722-logo2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css?v=20260722-adminmobile3">
    <style>
        .offer-admin-layout{display:grid;grid-template-columns:1fr;gap:24px;align-items:start}.offer-admin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.offer-admin-grid .full{grid-column:1/-1}.offer-current-image{display:flex;align-items:center;gap:12px;margin-top:10px;padding:10px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc}.offer-current-image img,.offer-admin-thumb{width:86px;height:58px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0}.offer-checks{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.offer-checks label{display:flex;align-items:center;gap:8px;padding:9px 10px;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;font-size:13px;font-weight:600;color:#334155}.offer-table-title{display:grid;gap:3px}.offer-table-title small{color:#64748b;font-weight:600}.badge-draft{background:#fef3c7;color:#92400e}.badge-active{background:#dcfce7;color:#166534}.badge-inactive{background:#e2e8f0;color:#475569}.offer-modal-open-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:38px;padding:0 14px;border:0;border-radius:9px;color:#fff;background:linear-gradient(135deg,#2563eb,#c026d3);font:inherit;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 12px 24px rgba(79,70,229,.18)}.offer-form-modal{position:fixed;inset:0;z-index:2000;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(15,23,42,.58);backdrop-filter:blur(8px)}.offer-form-modal.is-open{display:flex}.offer-form-panel{width:min(980px,calc(100vw - 28px));max-height:calc(100vh - 48px);display:grid;grid-template-rows:auto minmax(0,1fr);overflow:hidden;border:1px solid rgba(226,232,240,.9);border-radius:16px;background:#fff;box-shadow:0 28px 90px rgba(15,23,42,.34)}.offer-form-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 22px;border-bottom:1px solid #e2e8f0;background:#f8fafc}.offer-form-head h3{font-size:16px;font-weight:800;display:flex;align-items:center;gap:8px}.offer-form-close{width:40px;height:40px;display:grid;place-items:center;border:0;border-radius:10px;background:#eef2ff;color:#1d4ed8;text-decoration:none}.offer-form-scroll{overflow:auto;padding:22px}.offer-form-actions{display:flex;align-items:center;gap:12px;margin-top:18px}.offer-cancel-link{color:#64748b;font-size:13px;text-decoration:none;font-weight:700}@media(max-width:1100px){.offer-admin-grid{grid-template-columns:1fr}}@media(max-width:720px){.offer-form-modal{padding:10px}.offer-form-panel{max-height:calc(100vh - 20px)}.offer-form-scroll{padding:16px}.card-header{align-items:flex-start;gap:12px;flex-direction:column}}
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-tags"></i> Manage Offers</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <p style="margin:-8px 0 18px;color:#64748B;font-size:13.5px;">Add offer posters, fees, validity, benefits and terms. Active offers appear on the live Offers page.</p>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="offer-admin-layout">
            <div class="admin-card offer-list-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Offers (<?= count($offers) ?>)</h3>
                    <button class="offer-modal-open-btn" type="button" data-offer-modal-open><i class="fas fa-plus"></i> Add Offer</button>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Poster</th><th>Offer</th><th>Fee</th><th>Validity</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($offers as $offer): ?>
                            <tr>
                                <td><?php if ($offer['poster_image']): ?><img class="offer-admin-thumb" src="<?= htmlspecialchars(tt_admin_offer_image_url($offer['poster_image'])) ?>" alt=""><?php else: ?><span class="content-admin-placeholder"><i class="fas fa-image"></i></span><?php endif; ?></td>
                                <td><span class="offer-table-title"><strong><?= htmlspecialchars($offer['title']) ?></strong><small><?= htmlspecialchars(trim(($offer['course_name'] ?: '') . ' ' . ($offer['badge_text'] ? ' - ' . $offer['badge_text'] : ''))) ?></small></span></td>
                                <td><strong>Rs <?= number_format((float)$offer['offer_fee'], 0) ?></strong><br><small style="color:#94A3B8;">Old: Rs <?= number_format((float)$offer['original_fee'], 0) ?></small></td>
                                <td><small><?= htmlspecialchars($offer['start_date'] ?: 'Anytime') ?> to <?= htmlspecialchars($offer['end_date'] ?: 'Open') ?></small></td>
                                <td><span class="badge badge-<?= htmlspecialchars($offer['status']) ?>"><?= htmlspecialchars(ucfirst($offer['status'])) ?></span></td>
                                <td style="white-space:nowrap;"><a href="?edit=<?= (int)$offer['id'] ?>" class="btn-xs btn-blue"><i class="fas fa-edit"></i></a> <a href="?toggle=<?= (int)$offer['id'] ?>" class="btn-xs btn-<?= $offer['status'] === 'active' ? 'orange' : 'green' ?>"><i class="fas fa-<?= $offer['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i></a> <a href="?delete=<?= (int)$offer['id'] ?>" class="btn-xs btn-red" onclick="return confirm('Delete this offer?')"><i class="fas fa-trash"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$offers): ?><tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:24px;">No offers added yet. Add your first offer poster and details.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="offer-form-modal<?= ($editOffer || $error) ? ' is-open' : '' ?>" id="offerFormModal" aria-hidden="<?= ($editOffer || $error) ? 'false' : 'true' ?>">
                <div class="offer-form-panel" role="dialog" aria-modal="true" aria-labelledby="offerFormTitle">
                    <div class="offer-form-head">
                        <h3 id="offerFormTitle"><i class="fas fa-<?= $editOffer ? 'edit' : 'plus' ?>" style="color:var(--blue)"></i> <?= $editOffer ? 'Edit Offer' : 'Add Offer' ?></h3>
                        <a class="offer-form-close" href="offers.php" data-offer-modal-close aria-label="Close offer form"><i class="fas fa-times"></i></a>
                    </div>
                    <div class="offer-form-scroll">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($editOffer): ?><input type="hidden" name="id" value="<?= (int)$editOffer['id'] ?>"><?php endif; ?>
                    <input type="hidden" name="poster_existing" value="<?= $value('poster_image') ?>">
                    <div class="form-group"><label>Offer Title *</label><input type="text" name="title" required value="<?= $value('title') ?>"></div>
                    <div class="offer-admin-grid">
                        <div class="form-group"><label>Course Name</label><input type="text" name="course_name" list="courseTitles" value="<?= $value('course_name') ?>"></div>
                        <div class="form-group"><label>Category</label><input type="text" name="category" value="<?= $value('category') ?>" placeholder="Development, Security, Design"></div>
                        <div class="form-group"><label>Badge</label><input type="text" name="badge_text" value="<?= $value('badge_text', 'This Week Only') ?>"></div>
                        <div class="form-group"><label>Training Mode</label><select name="training_mode"><?php foreach (['Offline','Online','Hybrid'] as $mode): ?><option value="<?= $mode ?>" <?= ($editOffer['training_mode'] ?? 'Offline') === $mode ? 'selected' : '' ?>><?= $mode ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Original Fee</label><input type="number" step="0.01" name="original_fee" value="<?= $value('original_fee', '0') ?>"></div>
                        <div class="form-group"><label>Offer Fee</label><input type="number" step="0.01" name="offer_fee" value="<?= $value('offer_fee', '0') ?>"></div>
                        <div class="form-group"><label>Discount %</label><input type="number" step="0.01" name="discount_percentage" value="<?= $value('discount_percentage', '0') ?>"></div>
                        <div class="form-group"><label>Duration</label><input type="text" name="course_duration" value="<?= $value('course_duration') ?>"></div>
                        <div class="form-group"><label>Start Date</label><input type="date" name="start_date" value="<?= $value('start_date') ?>"></div>
                        <div class="form-group"><label>End Date</label><input type="date" name="end_date" value="<?= $value('end_date') ?>"></div>
                        <div class="form-group"><label>Seats Available</label><input type="number" name="available_seats" value="<?= $value('available_seats') ?>"></div>
                        <div class="form-group"><label>Seats Filled</label><input type="number" name="seats_filled" value="<?= $value('seats_filled', '0') ?>"></div>
                    </div>
                    <div class="form-group"><label>Poster Image</label><input type="file" name="poster_file" accept="image/jpeg,image/png,image/webp,image/gif"><small class="field-help">Upload poster image. JPG/PNG/WebP will be compressed to WebP for speed.</small></div>
                    <div class="form-group"><label>Or Select Media Image</label><select name="media_image"><option value="">Keep current / no media selected</option><?php foreach ($mediaImages as $image): ?><option value="<?= htmlspecialchars($image) ?>"><?= htmlspecialchars($image) ?></option><?php endforeach; ?></select></div>
                    <?php if (!empty($editOffer['poster_image'])): ?><div class="offer-current-image"><img src="<?= htmlspecialchars(tt_admin_offer_image_url($editOffer['poster_image'])) ?>" alt=""><span>Current poster</span><label><input type="checkbox" name="remove_poster" value="1"> Remove</label></div><?php endif; ?>
                    <div class="form-group"><label>Poster Alt Text</label><input type="text" name="poster_alt" value="<?= $value('poster_alt') ?>"></div>
                    <div class="form-group"><label>Short Description</label><textarea name="short_description" rows="3" maxlength="500"><?= $value('short_description') ?></textarea></div>
                    <div class="form-group"><label>Full Description</label><textarea name="full_description" rows="4"><?= $value('full_description') ?></textarea></div>
                    <div class="form-group"><label>Highlights (one per line)</label><textarea name="highlights" rows="5"><?= $value('highlights') ?></textarea></div>
                    <div class="form-group"><label>Learning Outcomes (one per line)</label><textarea name="learning_outcomes" rows="4"><?= $value('learning_outcomes') ?></textarea></div>
                    <div class="form-group"><label>Terms & Conditions</label><textarea name="terms_conditions" rows="4"><?= $value('terms_conditions') ?></textarea></div>
                    <div class="offer-admin-grid">
                        <div class="form-group"><label>CTA Label</label><input type="text" name="cta_label" value="<?= $value('cta_label', 'Reserve Your Seat') ?>"></div>
                        <div class="form-group"><label>CTA URL</label><input type="text" name="cta_url" value="<?= $value('cta_url', 'contact.php') ?>"></div>
                        <div class="form-group"><label>Status</label><select name="status"><?php foreach (['draft','active','inactive'] as $status): ?><option value="<?= $status ?>" <?= ($editOffer['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Display Order</label><input type="number" name="display_order" value="<?= $value('display_order', '0') ?>"></div>
                    </div>
                    <div class="offer-checks">
                        <label><input type="checkbox" name="is_featured" <?= !empty($editOffer['is_featured']) ? 'checked' : '' ?>> Featured</label>
                        <label><input type="checkbox" name="show_on_offers_page" <?= ($editOffer['show_on_offers_page'] ?? 1) ? 'checked' : '' ?>> Offers Page</label>
                        <label><input type="checkbox" name="certificate_available" <?= ($editOffer['certificate_available'] ?? 1) ? 'checked' : '' ?>> Certificate</label>
                        <label><input type="checkbox" name="internship_available" <?= !empty($editOffer['internship_available']) ? 'checked' : '' ?>> Internship</label>
                        <label><input type="checkbox" name="placement_available" <?= !empty($editOffer['placement_available']) ? 'checked' : '' ?>> Placement</label>
                        <label><input type="checkbox" name="emi_available" <?= !empty($editOffer['emi_available']) ? 'checked' : '' ?>> EMI</label>
                    </div>
                    <div class="offer-form-actions">
                        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Offer</button>
                        <a class="offer-cancel-link" href="offers.php">Cancel</a>
                    </div>
                </form>
                <datalist id="courseTitles"><?php foreach ($courses as $course): ?><option value="<?= htmlspecialchars($course['title']) ?>"></option><?php endforeach; ?></datalist>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    (() => {
        const modal = document.getElementById('offerFormModal');
        const openButton = document.querySelector('[data-offer-modal-open]');
        const closeLinks = document.querySelectorAll('[data-offer-modal-close]');
        if (!modal || !openButton) return;

        const setOpen = (open) => {
            modal.classList.toggle('is-open', open);
            modal.setAttribute('aria-hidden', open ? 'false' : 'true');
            document.body.style.overflow = open ? 'hidden' : '';
        };

        openButton.addEventListener('click', () => setOpen(true));
        closeLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                if (window.location.search.includes('edit=')) return;
                event.preventDefault();
                setOpen(false);
            });
        });
        modal.addEventListener('click', (event) => {
            if (event.target === modal && !window.location.search.includes('edit=')) {
                setOpen(false);
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('is-open') && !window.location.search.includes('edit=')) {
                setOpen(false);
            }
        });
        if (modal.classList.contains('is-open')) {
            document.body.style.overflow = 'hidden';
        }
    })();
</script>
</body>
</html>
