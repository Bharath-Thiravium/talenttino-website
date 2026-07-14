<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site-data.php';

$courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
$fallbackTitle = trim((string)($_GET['title'] ?? $_POST['course_title'] ?? 'Talentteno Course Brochure'));
$db = tt_db();
$errors = [];
$course = null;

if ($courseId && $db) {
    $stmt = $db->prepare('SELECT id, title, slug, brochure_file FROM courses WHERE id = ? AND is_active = 1 LIMIT 1');
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc() ?: null;
}

$courseTitle = trim((string)($course['title'] ?? $fallbackTitle));
$courseTitle = $courseTitle !== '' ? $courseTitle : 'Talentteno Course Brochure';

$form = [
    'name' => trim((string)($_POST['name'] ?? '')),
    'email' => trim((string)($_POST['email'] ?? '')),
    'phone' => trim((string)($_POST['phone'] ?? '')),
    'degree' => trim((string)($_POST['degree'] ?? '')),
    'college' => trim((string)($_POST['college'] ?? '')),
    'address' => trim((string)($_POST['address'] ?? '')),
    'study_year' => trim((string)($_POST['study_year'] ?? '')),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowedYears = ['1st Year', '2nd Year', '3rd Year', 'Passout'];

    foreach (['name', 'email', 'phone', 'degree', 'college', 'address', 'study_year'] as $field) {
        if ($form[$field] === '') {
            $errors[$field] = 'Required';
        }
    }

    if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address';
    }

    if ($form['phone'] !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $form['phone'])) {
        $errors['phone'] = 'Enter a valid mobile number';
    }

    if ($form['study_year'] !== '' && !in_array($form['study_year'], $allowedYears, true)) {
        $errors['study_year'] = 'Select a valid year';
    }

    if (!$errors) {
        if ($db) {
            tt_save_download_enquiry($db, $courseId ?: null, $courseTitle, $form);
        }

        if ($courseId && $db && $course && !empty($course['brochure_file'])) {
            $storedName = basename((string)$course['brochure_file']);
            $filePath = __DIR__ . '/uploads/brochures/' . $storedName;

            if (is_file($filePath) && is_readable($filePath)) {
                $update = $db->prepare('UPDATE courses SET download_count = download_count + 1 WHERE id = ?');
                $update->bind_param('i', $courseId);
                $update->execute();

                $downloadName = preg_replace('/[^a-z0-9-]+/i', '-', (string)$course['slug']);
                $downloadName = trim((string)$downloadName, '-') ?: 'course-brochure';
                tt_send_file_brochure($filePath, $downloadName);
            }
        }

        tt_send_generated_brochure($courseTitle);
    }
}

function tt_save_download_enquiry(mysqli $db, ?int $courseId, string $courseTitle, array $form): void
{
    $message = implode("\n", [
        'Degree: ' . $form['degree'],
        'College: ' . $form['college'],
        'Address: ' . $form['address'],
        'Year Status: ' . $form['study_year'],
        'Requested Brochure: ' . $courseTitle,
    ]);
    $email = $form['email'];
    $type = 'download';
    $status = 'new';
    $name = $form['name'];
    $phone = $form['phone'];
    $courseIdValue = $courseId;

    $stmt = $db->prepare('INSERT INTO enquiries (name, email, phone, course_id, course_name, message, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param(
        'sssissss',
        $name,
        $email,
        $phone,
        $courseIdValue,
        $courseTitle,
        $message,
        $type,
        $status
    );
    if ($stmt->execute()) {
        tt_notify_company_enquiry([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'course' => $courseTitle,
            'message' => $message,
            'type' => $type,
        ]);
    }
}

function tt_send_file_brochure(string $filePath, string $downloadName): void
{
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $downloadName . '.pdf"');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit;
}

function tt_send_generated_brochure(string $title): void
{
    $title = trim($title) !== '' ? trim($title) : 'Talentteno Course Brochure';
    $safeTitle = substr(preg_replace('/[^\w\s&+.,-]/u', '', $title) ?: 'Talentteno Course Brochure', 0, 90);
    $body = [
        'Talentteno Institute',
        $safeTitle,
        'Practical IT training with live projects, internship support, certification, and placement assistance.',
        'Contact Talentteno Institute for the complete syllabus, batch timing, fee details, and admission support.',
    ];

    $content = "BT\n/F1 22 Tf\n72 760 Td\n(" . tt_pdf_escape($body[0]) . ") Tj\n/F1 16 Tf\n0 -42 Td\n(" . tt_pdf_escape($body[1]) . ") Tj\n/F1 11 Tf\n0 -42 Td\n(" . tt_pdf_escape($body[2]) . ") Tj\n0 -24 Td\n(" . tt_pdf_escape($body[3]) . ") Tj\nET";
    $objects = [
        "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
        "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
        "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
        "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
        "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n$content\nendstream\nendobj\n",
    ];

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $object) {
        $offsets[] = strlen($pdf);
        $pdf .= $object;
    }
    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xrefOffset\n%%EOF";

    $downloadName = preg_replace('/[^a-z0-9-]+/i', '-', strtolower($safeTitle));
    $downloadName = trim((string)$downloadName, '-') ?: 'talentteno-brochure';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $downloadName . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    header('X-Content-Type-Options: nosniff');
    echo $pdf;
    exit;
}

function tt_pdf_escape(string $text): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'Download ' . $courseTitle . ' Brochure | Talentteno Institute',
        'description' => 'Download the Talentteno course brochure for syllabus, batch timing, fees, internship support, certification and placement assistance details.',
        'canonical' => tt_abs_url('download.php'),
        'robots' => 'noindex, follow',
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Download Brochure', 'url' => 'download.php'],
        ],
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.css?v=20260714-43">
</head>
<body class="static-site download-page">
<div class="site-shell">
    <header class="site-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="132" height="62" decoding="async" fetchpriority="high"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a>
            <nav class="site-nav">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <div class="nav-item has-menu"><a href="course.php">Course <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="shorttermcourse.php">Short Term Course</a><a href="popularcourse.php">Popular Course</a><a href="advancecourse.php">Advance Course</a></div></div>
                <a href="gallery.php">Gallery</a>
                <a href="contact.php">Contact</a>
                <div class="nav-item has-menu more-menu"><a href="#">More <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="services.php">Services</a><a href="career.php">Career</a><a href="blog.php">Blog</a><a href="project.php">Project</a></div></div>
            </nav>
            <button class="menu-button" type="button" aria-label="Open menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
        </div>
    </header>
    <main class="page-main">
        <section class="download-section">
            <div class="site-container download-layout">
                <div class="download-copy reveal reveal-left">
                    <span class="hero-kicker"><i class="fa-solid fa-download"></i> Brochure Download</span>
                    <h1><?= tt_h($courseTitle) ?></h1>
                    <p>Fill the student details to download the course brochure. Our counsellor can use this information to guide the right batch and course path.</p>
                </div>
                <form class="download-form reveal reveal-right" method="POST" data-download-form>
                    <input type="hidden" name="course_id" value="<?= (int)($courseId ?: 0) ?>">
                    <input type="hidden" name="course_title" value="<?= tt_h($courseTitle) ?>">
                    <h2>Student Details</h2>
                    <?php if ($errors): ?><div class="form-alert error">Please fill all required details correctly.</div><?php endif; ?>
                    <label>Full Name<input type="text" name="name" value="<?= tt_h($form['name']) ?>" required></label>
                    <label>Email ID<input type="email" name="email" value="<?= tt_h($form['email']) ?>" required></label>
                    <label>Mobile Number<input type="tel" name="phone" value="<?= tt_h($form['phone']) ?>" required></label>
                    <label>Degree<input type="text" name="degree" value="<?= tt_h($form['degree']) ?>" placeholder="Example: B.Sc CS, BCA, B.E CSE" required></label>
                    <label>College<input type="text" name="college" value="<?= tt_h($form['college']) ?>" required></label>
                    <label>Address<textarea name="address" rows="3" required><?= tt_h($form['address']) ?></textarea></label>
                    <label>Current Year / Status
                        <select name="study_year" required>
                            <option value="">Select year/status</option>
                            <?php foreach (['1st Year', '2nd Year', '3rd Year', 'Passout'] as $year): ?>
                            <option value="<?= tt_h($year) ?>" <?= $form['study_year'] === $year ? 'selected' : '' ?>><?= tt_h($year) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="btn btn-primary" type="submit" data-download-submit><i class="fa-solid fa-download"></i> Submit & Download</button>
                </form>
            </div>
        </section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
<script src="assets/js/site-pages.js?v=20260714-13" defer></script>
<script>
document.querySelectorAll('[data-download-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        if (!form.checkValidity()) {
            return;
        }

        const button = form.querySelector('[data-download-submit]');
        const originalText = button ? button.innerHTML : '';
        const iframeName = 'brochure-download-frame';
        let iframe = document.querySelector(`iframe[name="${iframeName}"]`);

        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.name = iframeName;
            iframe.hidden = true;
            document.body.appendChild(iframe);
        }

        form.target = iframeName;

        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-download"></i> Downloading...';
        }

        window.setTimeout(() => {
            form.reset();
            if (button) {
                button.disabled = false;
                button.innerHTML = originalText;
            }

            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = 'course.php';
            }
        }, 1200);
    });
});
</script>
</body>
</html>
