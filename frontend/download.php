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

    if ($form['email'] !== '' && (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || !preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $form['email']))) {
        $errors['email'] = 'Enter a valid email address';
    }

    if ($form['name'] !== '' && !tt_is_realistic_text($form['name'], 3, true)) {
        $errors['name'] = 'Enter your real full name';
    }

    if ($form['email'] !== '' && tt_looks_fake_email($form['email'])) {
        $errors['email'] = 'Enter your real email address';
    }

    $cleanPhone = preg_replace('/\D+/', '', $form['phone']);
    if ($form['phone'] !== '' && (!preg_match('/^[6-9][0-9]{9}$/', $cleanPhone) || tt_looks_fake_phone($cleanPhone))) {
        $errors['phone'] = 'Enter a valid 10 digit mobile number';
    }

    if ($form['degree'] !== '' && !tt_is_realistic_text($form['degree'], 3, false)) {
        $errors['degree'] = 'Enter your actual degree';
    }

    if ($form['college'] !== '' && !tt_is_realistic_text($form['college'], 4, false)) {
        $errors['college'] = 'Enter your actual college name';
    }

    if ($form['address'] !== '' && !tt_is_realistic_address($form['address'])) {
        $errors['address'] = 'Enter your actual address';
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

function tt_has_fake_words(string $value): bool
{
    return (bool)preg_match('/\b(test|testing|dummy|fake|sample|example|asdf|qwerty|abcd|xyz|none|null|na|n\/a|admin|user)\b/i', $value);
}

function tt_is_realistic_text(string $value, int $minLength, bool $requireSpace): bool
{
    $clean = trim(preg_replace('/\s+/', ' ', $value) ?? '');
    if (strlen($clean) < $minLength || tt_has_fake_words($clean)) {
        return false;
    }

    if (!preg_match('/[a-zA-Z]{2,}/', $clean)) {
        return false;
    }

    if ($requireSpace && !preg_match('/^[a-zA-Z][a-zA-Z.\' -]*\s+[a-zA-Z][a-zA-Z.\' -]*$/', $clean)) {
        return false;
    }

    if (preg_match('/(.)\1{4,}/i', preg_replace('/\s+/', '', $clean))) {
        return false;
    }

    return true;
}

function tt_is_realistic_address(string $value): bool
{
    $clean = trim(preg_replace('/\s+/', ' ', $value) ?? '');
    if (strlen($clean) < 10 || tt_has_fake_words($clean)) {
        return false;
    }

    return (bool)preg_match('/[a-zA-Z]{3,}/', $clean) && str_word_count($clean) >= 3;
}

function tt_looks_fake_email(string $email): bool
{
    $email = strtolower(trim($email));
    [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
    if ($local === '' || $domain === '') {
        return true;
    }

    if (tt_has_fake_words($local) || preg_match('/^(test|demo|fake|dummy|sample)[0-9._-]*@/i', $email)) {
        return true;
    }

    return preg_match('/(.)\1{5,}/', $local) === 1;
}

function tt_looks_fake_phone(string $phone): bool
{
    if (preg_match('/(\d)\1{4,}/', $phone)) {
        return true;
    }

    $blocked = ['9876543210', '9123456789', '9999999999', '8888888888', '7777777777', '6666666666', '1234567890'];
    if (in_array($phone, $blocked, true)) {
        return true;
    }

    return false;
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
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260718-brandfix1">
</head>
<body class="static-site download-page">
<div class="site-shell">
    <header class="site-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="uploads/optimized/logot-transparent-w64.webp" srcset="uploads/optimized/logot-transparent-w64.webp 64w, uploads/optimized/logot-transparent-w128.webp 128w" sizes="(max-width: 980px) 58px, 68px" alt="Talentteno Institute logo" width="68" height="68" decoding="async"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a>
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
                    <label>Full Name<input type="text" name="name" value="<?= tt_h($form['name']) ?>" minlength="3" data-real-check="name" data-error-message="Enter your real full name" required><?php if (isset($errors['name'])): ?><span class="field-error"><?= tt_h($errors['name']) ?></span><?php endif; ?></label>
                    <label>Email ID<input type="email" name="email" value="<?= tt_h($form['email']) ?>" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" data-error-message="Enter a valid email address, example: name@example.com" required><?php if (isset($errors['email'])): ?><span class="field-error"><?= tt_h($errors['email']) ?></span><?php endif; ?></label>
                    <label>Mobile Number<input type="tel" name="phone" value="<?= tt_h($form['phone']) ?>" inputmode="numeric" pattern="[6-9][0-9]{9}" minlength="10" maxlength="10" data-error-message="Enter a valid 10 digit mobile number" required><?php if (isset($errors['phone'])): ?><span class="field-error"><?= tt_h($errors['phone']) ?></span><?php endif; ?></label>
                    <label>Degree<input type="text" name="degree" value="<?= tt_h($form['degree']) ?>" placeholder="Example: B.Sc CS, BCA, B.E CSE" minlength="3" data-real-check="text" data-error-message="Enter your actual degree" required><?php if (isset($errors['degree'])): ?><span class="field-error"><?= tt_h($errors['degree']) ?></span><?php endif; ?></label>
                    <label>College<input type="text" name="college" value="<?= tt_h($form['college']) ?>" minlength="4" data-real-check="text" data-error-message="Enter your actual college name" required><?php if (isset($errors['college'])): ?><span class="field-error"><?= tt_h($errors['college']) ?></span><?php endif; ?></label>
                    <label>Address<textarea name="address" rows="3" minlength="10" data-real-check="address" data-error-message="Enter your actual address" required><?= tt_h($form['address']) ?></textarea><?php if (isset($errors['address'])): ?><span class="field-error"><?= tt_h($errors['address']) ?></span><?php endif; ?></label>
                    <label>Current Year / Status
                        <select name="study_year" required>
                            <option value="">Select year/status</option>
                            <?php foreach (['1st Year', '2nd Year', '3rd Year', 'Passout'] as $year): ?>
                            <option value="<?= tt_h($year) ?>" <?= $form['study_year'] === $year ? 'selected' : '' ?>><?= tt_h($year) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['study_year'])): ?><span class="field-error"><?= tt_h($errors['study_year']) ?></span><?php endif; ?>
                    </label>
                    <button class="btn btn-primary" type="submit" data-download-submit><i class="fa-solid fa-download"></i> Submit & Download</button>
                </form>
            </div>
        </section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260718-scrollsmooth1" defer></script>
<script>
document.querySelectorAll('[data-download-form]').forEach((form) => {
    const button = form.querySelector('[data-download-submit]');
    const originalText = button ? button.innerHTML : '';
    const fakeWords = /\b(test|testing|dummy|fake|sample|example|asdf|qwerty|abcd|xyz|none|null|na|n\/a|admin|user)\b/i;
    const normalizeValue = (value) => String(value || '').trim().replace(/\s+/g, ' ');
    const looksFakePhone = (value) => {
        const phone = String(value || '').replace(/\D+/g, '');
        return !/^[6-9][0-9]{9}$/.test(phone)
            || /(\d)\1{4,}/.test(phone)
            || ['9876543210', '9123456789', '9999999999', '8888888888', '7777777777', '6666666666', '1234567890'].includes(phone);
    };
    const looksFakeValue = (field) => {
        const value = normalizeValue(field.value);
        const compact = value.replace(/\s+/g, '');
        if (!value || fakeWords.test(value) || /(.)\1{4,}/i.test(compact)) return true;

        if (field.name === 'phone') return looksFakePhone(value);
        if (field.name === 'email') {
            const local = value.split('@')[0] || '';
            return fakeWords.test(local) || /(.)\1{5,}/i.test(local);
        }
        if (field.dataset.realCheck === 'name') {
            return value.length < 3 || !/^[a-zA-Z][a-zA-Z.' -]*\s+[a-zA-Z][a-zA-Z.' -]*$/.test(value);
        }
        if (field.dataset.realCheck === 'address') {
            return value.length < 10 || !/[a-zA-Z]{3,}/.test(value) || value.split(/\s+/).length < 3;
        }
        if (field.dataset.realCheck === 'text') {
            return value.length < Number(field.getAttribute('minlength') || 3) || !/[a-zA-Z]{2,}/.test(value);
        }

        return false;
    };
    const showFieldError = (field) => {
        let error = field.parentElement.querySelector('.field-error.client-error');
        if (!error) {
            error = document.createElement('span');
            error.className = 'field-error client-error';
            field.insertAdjacentElement('afterend', error);
        }
        error.textContent = field.dataset.errorMessage || field.validationMessage || 'Please enter a valid value';
    };
    const clearFieldError = (field) => {
        field.parentElement.querySelector('.field-error.client-error')?.remove();
    };

    form.querySelectorAll('input, select, textarea').forEach((field) => {
        field.addEventListener('input', () => {
            field.setCustomValidity('');
            if (field.checkValidity() && !looksFakeValue(field)) clearFieldError(field);
        });
        field.addEventListener('invalid', () => showFieldError(field));
    });

    form.addEventListener('submit', (event) => {
        form.querySelectorAll('input, textarea').forEach((field) => {
            field.setCustomValidity('');
            if (looksFakeValue(field)) {
                field.setCustomValidity(field.dataset.errorMessage || 'Please enter real details');
            }
        });

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                showFieldError(firstInvalid);
                firstInvalid.focus();
            }
            return;
        }

        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-download"></i> Downloading...';
        }

        window.setTimeout(() => {
            if (button) {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }, 2500);
    });
});
</script>
</body>
</html>
