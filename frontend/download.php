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
$brochureApiBase = rtrim((string)(getenv('BROCHURE_API_BASE') ?: ''), '/');
$turnstileSiteKey = trim((string)(getenv('TURNSTILE_SITE_KEY') ?: ''));

$form = [
    'name' => trim((string)($_POST['name'] ?? '')),
    'email' => trim((string)($_POST['email'] ?? '')),
    'phone' => trim((string)($_POST['phone'] ?? '')),
    'degree' => trim((string)($_POST['degree'] ?? '')),
    'college' => trim((string)($_POST['college'] ?? '')),
    'address' => trim((string)($_POST['address'] ?? '')),
    'study_year' => trim((string)($_POST['study_year'] ?? '')),
];

if (false && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260720-downloadform1">
    <?php if ($turnstileSiteKey !== ''): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script>function ttOnCaptcha(){document.querySelectorAll('[data-download-form]').forEach(f=>{const b=f.querySelector('[data-download-submit]');if(b&&b._updateState)b._updateState();});}</script>
    <?php endif; ?>
</head>
<body class="static-site download-page">
<div class="site-shell">
    <header class="site-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="68" height="68" decoding="async"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a>
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
                <form class="download-form reveal reveal-right" method="POST" data-download-form data-api-base="<?= tt_h($brochureApiBase) ?>" data-turnstile-site-key="<?= tt_h($turnstileSiteKey) ?>" novalidate>
                    <input type="hidden" name="course_id" value="<?= (int)($courseId ?: 0) ?>">
                    <input type="hidden" name="course_title" value="<?= tt_h($courseTitle) ?>">
                    <h2>Student Details</h2>
                    <div class="form-alert error" data-form-error hidden></div>
                    <div class="form-alert success" data-form-success hidden></div>
                    <label>Full Name<input type="text" name="name" value="<?= tt_h($form['name']) ?>" maxlength="50" autocomplete="name" pattern="[A-Za-z ]+" required><div class="field-error" id="nameError"></div></label>
                    <label>Email ID<input type="email" name="email" value="<?= tt_h($form['email']) ?>" maxlength="190" autocomplete="email" required><div class="field-error" id="emailError"></div></label>
                    <label class="download-otp-field">Mobile Number
                        <div class="download-inline-control">
                            <input type="tel" name="phone" value="<?= tt_h($form['phone']) ?>" inputmode="numeric" maxlength="10" autocomplete="tel" pattern="[0-9]{10}" required>
                            <button type="button" id="sendOtpBtn" class="download-small-btn" data-send-otp>Send OTP</button>
                        </div>
                        <div class="field-error" id="mobileError"></div>
                        <div class="download-otp-status" data-otp-status></div>
                    </label>
                    <label class="download-otp-verify" data-otp-wrap hidden>Enter OTP
                        <div class="download-inline-control">
                            <input type="text" name="otp" inputmode="numeric" maxlength="6" autocomplete="one-time-code">
                            <button type="button" class="download-small-btn" data-verify-otp>Verify OTP</button>
                        </div>
                        <div class="field-error" id="otpError"></div>
                    </label>
                    <label>Degree<input type="text" name="degree" value="<?= tt_h($form['degree']) ?>" placeholder="Example: B.Sc CS, BCA, B.E CSE" maxlength="100" required><div class="field-error" id="degreeError"></div></label>
                    <label>College<input type="text" name="college" value="<?= tt_h($form['college']) ?>" maxlength="150" required><div class="field-error" id="collegeError"></div></label>
                    <label>Current Year / Status
                        <select name="study_year" required>
                            <option value="">Select year/status</option>
                            <?php foreach (['1st Year', '2nd Year', '3rd Year', 'Passout'] as $year): ?>
                            <option value="<?= tt_h($year) ?>" <?= $form['study_year'] === $year ? 'selected' : '' ?>><?= tt_h($year) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="field-error" id="studyYearError"></div>
                    </label>
                    <div class="download-captcha" data-captcha-wrap>
                        <?php if ($turnstileSiteKey !== ''): ?>
                        <div class="cf-turnstile" data-sitekey="<?= tt_h($turnstileSiteKey) ?>" data-callback="ttOnCaptcha" data-expired-callback="ttOnCaptcha"></div>
                        <?php else: ?>
                        <input type="hidden" name="cf-turnstile-response" value="dev-turnstile">
                        <small>Captcha is in development mode.</small>
                        <?php endif; ?>
                        <div class="field-error" id="captchaError"></div>
                    </div>
                    <button class="btn btn-primary" type="submit" data-download-submit disabled><i class="fa-solid fa-download"></i> Download Brochure</button>
                </form>
            </div>
        </section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260718-scrollsmooth1" defer></script>
<script>
document.querySelectorAll('[data-download-form]').forEach((form) => {
    const configuredApiBase = form.dataset.apiBase || '';
    const isLocalPage = ['127.0.0.1', 'localhost'].includes(window.location.hostname);
    const apiBase = configuredApiBase || (isLocalPage ? 'http://127.0.0.1:5010' : '');
    const fields = {
        name: form.elements.name,
        email: form.elements.email,
        mobile: form.elements.phone,
        otp: form.elements.otp,
        degree: form.elements.degree,
        college: form.elements.college,
        study_year: form.elements.study_year
    };
    const sendOtpButton = form.querySelector('[data-send-otp]');
    const verifyOtpButton = form.querySelector('[data-verify-otp]');
    const submitButton = form.querySelector('[data-download-submit]');
    const otpWrap = form.querySelector('[data-otp-wrap]');
    const otpStatus = form.querySelector('[data-otp-status]');
    const formError = form.querySelector('[data-form-error]');
    const formSuccess = form.querySelector('[data-form-success]');
    const originalSubmit = submitButton.innerHTML;
    let verificationRef = '';
    let verifiedMobile = '';
    let resendTimer = 0;
    let resendInterval = 0;
    let isSendingOtp = false;

    const errorMap = {
        name: 'nameError',
        email: 'emailError',
        mobile: 'mobileError',
        phone: 'mobileError',
        otp: 'otpError',
        degree: 'degreeError',
        college: 'collegeError',
        study_year: 'studyYearError',
        currentStatus: 'studyYearError',
        captcha: 'captchaError',
        courseTitle: 'courseTitleError',
        form: 'captchaError'
    };
    const fakeNames = new Set(['test', 'testing', 'demo', 'admin', 'user', 'abc', 'xyz', 'none', 'unknown', 'sample', 'fake', 'null']);
    const blockedDomains = new Set(['mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com', 'yopmail.com', 'throwawaymail.com', 'fakeinbox.com']);
    const blockedMobiles = new Set(['0000000000', '1111111111', '2222222222', '3333333333', '4444444444', '5555555555', '6666666666', '7777777777', '8888888888', '9999999999', '1234567890', '9876543210']);
    const cleanText = value => String(value || '').trim().replace(/\s+/g, ' ');
    const cleanEmail = value => String(value || '').trim().replace(/\s+/g, '').toLowerCase();
    const cleanMobile = value => {
        let mobile = String(value || '').trim().replace(/[\s-]+/g, '');
        if (mobile.startsWith('+91')) mobile = mobile.slice(3);
        else if (/^91[6-9][0-9]{9}$/.test(mobile)) mobile = mobile.slice(2);
        return mobile.replace(/\D+/g, '');
    };
    const setButton = (button, text, disabled) => {
        if (!button) return;
        button.disabled = Boolean(disabled);
        button.innerHTML = text;
    };
    const showError = (key, message) => {
        const id = errorMap[key] || `${key}Error`;
        const box = document.getElementById(id);
        if (box) box.textContent = message || '';
        const field = fields[key] || (key === 'mobile' ? fields.mobile : null);
        field?.classList.toggle('is-invalid', Boolean(message));
    };
    const clearErrors = () => {
        form.querySelectorAll('.field-error').forEach(item => { item.textContent = ''; });
        form.querySelectorAll('.is-invalid').forEach(item => item.classList.remove('is-invalid'));
        formError.hidden = true;
        formSuccess.hidden = true;
    };
    const showFormMessage = (box, message) => {
        box.textContent = message;
        box.hidden = false;
    };
    const applyApiErrors = (payload) => {
        Object.entries(payload.errors || {}).forEach(([key, message]) => showError(key, message));
        showFormMessage(formError, payload.message || 'Please correct the highlighted fields.');
        const first = form.querySelector('.field-error:not(:empty)');
        first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };
    const clearOtpErrors = () => {
        showError('mobile', '');
        showError('otp', '');
        formError.hidden = true;
    };
    const showOtpError = (key, message) => {
        showError(key, message);
        formError.hidden = true;
    };
    const isFieldsFilled = () => {
        return cleanText(fields.name.value).length >= 3
            && cleanEmail(fields.email.value).includes('@')
            && cleanMobile(fields.mobile.value).length === 10
            && cleanText(fields.degree.value).length >= 2
            && cleanText(fields.college.value).length >= 2
            && fields.study_year.value !== '';
    };
    const isCaptchaReady = () => {
        const token = form.querySelector('[name="cf-turnstile-response"]')?.value || '';
        return token !== '' && token !== 'EXPIRED';
    };
    const updateSubmitState = () => {
        const otpVerified = !!(verificationRef && verifiedMobile && cleanMobile(fields.mobile.value) === verifiedMobile);
        submitButton.disabled = !(otpVerified && isFieldsFilled() && isCaptchaReady());
    };
    const isMobileValid = () => {
        const mobile = cleanMobile(fields.mobile.value);
        return /^[6-9][0-9]{9}$/.test(mobile) && !blockedMobiles.has(mobile);
    };
    const updateSendOtpState = () => {
        if (isSendingOtp || resendTimer > 0) return;
        sendOtpButton.disabled = !isMobileValid();
        sendOtpButton.innerHTML = sendOtpButton.dataset.hasSent === 'true' ? 'Resend OTP' : 'Send OTP';
    };
    const validateAll = (includeOtp = false) => {
        clearErrors();
        const name = cleanText(fields.name.value);
        const email = cleanEmail(fields.email.value);
        const mobile = cleanMobile(fields.mobile.value);
        let valid = true;
        if (name.length < 3 || name.length > 50 || !/^[A-Za-z ]+$/.test(name) || /^(.)\1{4,}$/i.test(name.replace(/\s+/g, '')) || fakeNames.has(name.toLowerCase())) {
            showError('name', 'Name should contain letters only.'); valid = false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/.test(email)) {
            showError('email', 'Please enter a valid email address.'); valid = false;
        } else if (blockedDomains.has(email.split('@').pop())) {
            showError('email', 'Temporary email addresses are not allowed.'); valid = false;
        }
        if (!/^[6-9][0-9]{9}$/.test(mobile) || blockedMobiles.has(mobile)) {
            showError('mobile', 'Mobile number should contain 10 digits only.'); valid = false;
        }
        if (cleanText(fields.degree.value).length < 2 || cleanText(fields.degree.value).length > 100) { showError('degree', 'Please enter your degree.'); valid = false; }
        if (cleanText(fields.college.value).length < 2 || cleanText(fields.college.value).length > 150) { showError('college', 'Please enter your college name.'); valid = false; }
        if (!fields.study_year.value) { showError('study_year', 'Please select your current year or status.'); valid = false; }
        if (includeOtp && !/^[0-9]{6}$/.test(fields.otp.value)) { showError('otp', 'Please enter the 6-digit OTP.'); valid = false; }
        if (!valid) form.querySelector('.field-error:not(:empty)')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return valid;
    };
    const postJson = async (path, body) => {
        let response;
        try {
            response = await fetch(`${apiBase}${path}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(body)
            });
        } catch (error) {
            if (error instanceof TypeError) {
                throw {
                    success: false,
                    code: 'OTP_SERVICE_UNAVAILABLE',
                    message: isLocalPage
                        ? 'OTP service is not connected. Please start the local backend and try again.'
                        : 'Unable to connect to OTP service. Please try again later.',
                    errors: {}
                };
            }
            throw error;
        }
        const payload = await response.json().catch(() => ({ success: false, message: 'Server error. Please try again later.' }));
        if (!response.ok || !payload.success) throw payload;
        return payload;
    };
    const startResendTimer = (seconds) => {
        window.clearInterval(resendInterval);
        resendTimer = seconds;
        const tick = () => {
            if (resendTimer <= 0) {
                window.clearInterval(resendInterval);
                updateSendOtpState();
                return;
            }
            setButton(sendOtpButton, `Resend in ${resendTimer}s`, true);
            resendTimer -= 1;
        };
        tick();
        resendInterval = window.setInterval(tick, 1000);
    };
    const resetVerification = () => {
        verificationRef = '';
        verifiedMobile = '';
        fields.otp.value = '';
        otpWrap.hidden = true;
        otpStatus.textContent = '';
        updateSubmitState();
    };
    const resetSendOtpButton = () => {
        window.clearInterval(resendInterval);
        resendTimer = 0;
        isSendingOtp = false;
        sendOtpButton.dataset.hasSent = '';
        updateSendOtpState();
    };
    const handleOtpSendError = (payload) => {
        const code = payload.code || '';
        const message = payload.errors?.mobile || payload.message || 'Unable to send OTP right now. Please try again.';
        if (code === 'OTP_RESEND_WAIT') {
            const retryAfter = Number(payload.retryAfter || 60);
            showOtpError('mobile', `Please wait ${retryAfter} seconds before requesting another OTP.`);
            startResendTimer(retryAfter);
            return;
        }
        showOtpError('mobile', message);
        setButton(sendOtpButton, sendOtpButton.dataset.hasSent === 'true' ? 'Resend OTP' : 'Send OTP', false);
        updateSendOtpState();
    };

    fields.name.addEventListener('input', () => {
        fields.name.value = fields.name.value.replace(/[^A-Za-z ]+/g, '').replace(/\s{2,}/g, ' ').slice(0, 50);
    });
    fields.mobile.addEventListener('input', () => {
        fields.mobile.value = fields.mobile.value.replace(/\D+/g, '').slice(0, 10);
        clearOtpErrors();
        resetSendOtpButton();
        if (verifiedMobile && cleanMobile(fields.mobile.value) !== verifiedMobile) resetVerification();
        updateSendOtpState();
    });
    fields.otp.addEventListener('input', () => {
        fields.otp.value = fields.otp.value.replace(/\D+/g, '').slice(0, 6);
    });
    Object.values(fields).forEach(field => field?.addEventListener('input', () => {
        const key = field.name === 'phone' ? 'mobile' : field.name;
        showError(key, '');
        formError.hidden = true;
        updateSubmitState();
    }));
    fields.study_year.addEventListener('change', updateSubmitState);

    sendOtpButton.addEventListener('click', async (event) => {
        event.preventDefault();
        if (isSendingOtp) return;
        clearOtpErrors();
        if (!isMobileValid()) {
            showOtpError('mobile', 'Mobile number should contain 10 digits only.');
            updateSendOtpState();
            return;
        }
        isSendingOtp = true;
        setButton(sendOtpButton, 'Sending...', true);
        try {
            const payload = await postJson('/api/brochure/send-otp', {
                mobile: cleanMobile(fields.mobile.value),
                courseTitle: form.elements.course_title.value
            });
            resetVerification();
            otpWrap.hidden = false;
            otpStatus.textContent = `${payload.message} OTP expires in 5 minutes.`;
            sendOtpButton.dataset.hasSent = 'true';
            startResendTimer(Number(payload.resendAfterSeconds || 60));
        } catch (payload) {
            handleOtpSendError(payload);
        } finally {
            isSendingOtp = false;
            if (resendTimer <= 0) updateSendOtpState();
        }
    });

    verifyOtpButton.addEventListener('click', async () => {
        showError('otp', '');
        if (!validateAll(true)) return;
        setButton(verifyOtpButton, 'Verifying...', true);
        try {
            const payload = await postJson('/api/brochure/verify-otp', {
                mobile: cleanMobile(fields.mobile.value),
                courseTitle: form.elements.course_title.value,
                otp: fields.otp.value
            });
            verificationRef = payload.verificationRef || '';
            verifiedMobile = cleanMobile(fields.mobile.value);
            otpStatus.innerHTML = '<i class="fa-solid fa-check"></i> Mobile number verified successfully.';
            setButton(verifyOtpButton, 'Verified', true);
            updateSubmitState();
        } catch (payload) {
            showOtpError(payload.errors?.mobile ? 'mobile' : 'otp', payload.errors?.mobile || payload.errors?.otp || payload.message || 'Unable to verify OTP.');
            setButton(verifyOtpButton, 'Verify OTP', false);
            updateSubmitState();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();
        if (!validateAll(false)) return;
        if (!verificationRef || cleanMobile(fields.mobile.value) !== verifiedMobile) {
            showError('mobile', 'Please verify your mobile number with OTP.');
            updateSubmitState();
            return;
        }
        const captchaToken = form.querySelector('[name="cf-turnstile-response"]')?.value || '';
        if (!captchaToken) {
            showError('captcha', 'Captcha verification failed. Please try again.');
            return;
        }
        setButton(submitButton, 'Validating...', true);
        try {
            setButton(submitButton, 'Preparing Download...', true);
            const payload = await postJson('/api/brochure/submit', {
                courseTitle: form.elements.course_title.value,
                fullName: cleanText(fields.name.value),
                email: cleanEmail(fields.email.value),
                mobile: cleanMobile(fields.mobile.value),
                degree: cleanText(fields.degree.value),
                college: cleanText(fields.college.value),
                address: '',
                currentStatus: fields.study_year.value,
                otpVerificationRef: verificationRef,
                captchaToken
            });
            showFormMessage(formSuccess, 'Your brochure download has started successfully.');
            setButton(submitButton, '<i class="fa-solid fa-check"></i> Downloaded', true);
            window.location.href = `${apiBase}${payload.downloadUrl}`;
        } catch (payload) {
            applyApiErrors(payload);
            if (window.turnstile) window.turnstile.reset();
            setButton(submitButton, originalSubmit, false);
            updateSubmitState();
        }
    });
    updateSubmitState();
    submitButton._updateState = updateSubmitState;
    updateSendOtpState();
});
</script>
</body>
</html>
