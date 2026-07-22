<?php
declare(strict_types=1);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/includes/site-data.php';

$courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
$fallbackTitle = trim((string)($_GET['title'] ?? $_POST['course_title'] ?? 'Talentteno Course Brochure'));
$db = tt_db();
$course = null;

if ($courseId && $db) {
    $stmt = $db->prepare('SELECT id, title, slug, brochure_file FROM courses WHERE id = ? AND is_active = 1 LIMIT 1');
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc() ?: null;
}

$courseTitle = trim((string)($course['title'] ?? $fallbackTitle)) ?: 'Talentteno Course Brochure';
$degreeOptions = [
    'B.E / B.Tech',
    'M.E / M.Tech',
    'B.Sc Computer Science',
    'B.Sc Information Technology',
    'B.Sc Data Science',
    'BCA',
    'MCA',
    'B.Com',
    'B.Com CA',
    'BBA',
    'MBA',
    'B.A',
    'M.A',
    'B.Ed',
    'Diploma',
    'Polytechnic',
    '12th Completed',
    'Other',
];
$maduraiCollegeOptions = [
    'American College',
    'Thiagarajar College',
    'Thiagarajar College of Engineering',
    'Madurai Kamaraj University',
    'Anna University Regional Campus, Madurai',
    'Agricultural College and Research Institute, Madurai',
    'Madurai Medical College',
    'Government Law College, Madurai',
    'Lady Doak College',
    'Fatima College',
    'Mannar Thirumalai Naicker College',
    'Sourashtra College',
    'Sourashtra College for Women',
    'Yadava College',
    'E.M.G. Yadava Women\'s College',
    'Vivekananda College',
    'Madurai Institute of Social Sciences',
    'Madurai Sivakasi Nadars Pioneer Meenakshi Women\'s College',
    'Sri Meenakshi Government Arts College for Women',
    'Madura College',
    'Senthamarai College of Arts and Science',
    'Subbalakshmi Lakshmipathy College of Science',
    'Thiagarajar School of Management',
    'Velammal Medical College Hospital and Research Institute',
    'CSI College of Dental Sciences and Research',
    'Raja College of Engineering and Technology',
    'Velammal College of Engineering and Technology',
    'Latha Mathavan Engineering College',
    'K.L.N. College of Engineering',
    'K.L.N. College of Information Technology',
    'Sethu Institute of Technology',
    'P.T.R. College of Engineering and Technology',
    'Vaigai College of Engineering',
    'Ultra College of Engineering and Technology for Women',
    'Mangayarkarasi College of Engineering',
    'Mangayarkarasi College of Arts and Science for Women',
    'R.L. Institute of Nautical Sciences',
    'OAA MAVMM School of Management',
    'MAVMM Ayira Vaisyar College',
    'N.M.S. Sermathai Vasan College for Women',
    'Sri Nagalakshmi Ammal College of Sciences',
    'Thiruvalluvar College',
    'Arul Anandar College',
    'The Standard Fireworks Rajaratnam College for Women',
    'Government Polytechnic College, Madurai',
    'Tamil Nadu Polytechnic College',
    'Other College',
];

function dl_is_selected(string $current, string $option): bool
{
    return strcasecmp(trim($current), trim($option)) === 0;
}

function dl_render_dropdown(string $name, string $label, array $options, string $current, string $placeholder): void
{
    $selected = trim($current);
    $buttonText = $selected !== '' ? $selected : $placeholder;
    ?>
    <div class="dl-field">
        <label><?= tt_h($label) ?> *</label>
        <div class="dl-custom-select" data-custom-select>
            <input type="hidden" name="<?= tt_h($name) ?>" value="<?= tt_h($selected) ?>" required>
            <button type="button" class="dl-select-btn" data-select-button aria-haspopup="listbox" aria-expanded="false">
                <span data-select-label><?= tt_h($buttonText) ?></span>
                <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
            </button>
            <div class="dl-select-menu" data-select-menu role="listbox">
                <button type="button" class="dl-select-option is-placeholder" data-value=""><?= tt_h($placeholder) ?></button>
                <?php foreach ($options as $option): ?>
                <button type="button" class="dl-select-option<?= dl_is_selected($selected, $option) ? ' is-selected' : '' ?>" data-value="<?= tt_h($option) ?>" role="option"><?= tt_h($option) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

// ── helpers ──────────────────────────────────────────────────────────────────
function dl_pdf_escape(string $t): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $t);
}

function dl_send_file(string $path, string $name): void
{
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $name . '.pdf"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

function dl_send_generated(string $title): void
{
    $safe = substr(preg_replace('/[^\w\s&+.,-]/u', '', $title) ?: 'Talentteno', 0, 90);
    $c = "BT\n/F1 22 Tf\n72 760 Td\n(" . dl_pdf_escape('Talentteno Institute') . ") Tj\n/F1 16 Tf\n0 -42 Td\n(" . dl_pdf_escape($safe) . ") Tj\n/F1 11 Tf\n0 -42 Td\n(Practical IT training with live projects, internship and placement support.) Tj\nET";
    $objs = [
        "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
        "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
        "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
        "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
        "5 0 obj\n<< /Length " . strlen($c) . " >>\nstream\n$c\nendstream\nendobj\n",
    ];
    $pdf = "%PDF-1.4\n"; $offs = [0];
    foreach ($objs as $o) { $offs[] = strlen($pdf); $pdf .= $o; }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objs)+1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objs); $i++) $pdf .= sprintf("%010d 00000 n \n", $offs[$i]);
    $pdf .= "trailer\n<< /Size " . (count($objs)+1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    $name = trim(preg_replace('/[^a-z0-9-]+/i', '-', strtolower($safe)), '-') ?: 'talentteno-brochure';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $name . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf; exit;
}

// ── FORM SUBMIT: download ─────────────────────────────────────────────────────
$errors = [];
$form   = ['name'=>'','email'=>'','phone'=>'','degree'=>'','college'=>'','study_year'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'download') {
    foreach ($form as $k => $_) $form[$k] = trim((string)($_POST[$k] ?? ''));

    if (strlen($form['name']) < 3)  $errors['name']  = 'Enter your full name.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email.';
    $ph = preg_replace('/\D+/', '', $form['phone']);
    if (!preg_match('/^[6-9][0-9]{9}$/', $ph)) $errors['phone'] = 'Enter a valid 10-digit mobile number.';
    if (strlen($form['degree']) < 2)  $errors['degree']  = 'Enter your degree.';
    if (strlen($form['college']) < 3) $errors['college'] = 'Enter your college name.';
    if (!in_array($form['study_year'], ['1st Year','2nd Year','3rd Year','Passout'], true)) $errors['study_year'] = 'Select your year.';

    // OTP check
    if (empty($errors)) {
        if (empty($_SESSION['dl_verified']) || ($_SESSION['dl_otp_email'] ?? '') !== $form['email']) {
            $errors['otp'] = 'Please verify your email with OTP before downloading.';
        }
    }

    if (empty($errors)) {
        if ($db) tt_save_download_enquiry($db, $courseId ?: null, $courseTitle, $form);
        unset($_SESSION['dl_otp'], $_SESSION['dl_otp_email'], $_SESSION['dl_otp_time'], $_SESSION['dl_verified']);

        if ($course && !empty($course['brochure_file'])) {
            $fp = __DIR__ . '/uploads/brochures/' . basename((string)$course['brochure_file']);
            if (is_file($fp)) {
                $db && $db->query("UPDATE courses SET download_count = download_count + 1 WHERE id=" . (int)$courseId);
                $slug = trim(preg_replace('/[^a-z0-9-]+/i', '-', (string)($course['slug'] ?? '')), '-') ?: 'brochure';
                dl_send_file($fp, $slug);
            }
        }
        dl_send_generated($courseTitle);
    }
}

function tt_save_download_enquiry(mysqli $db, ?int $courseId, string $courseTitle, array $form): void
{
    $message = "Degree: {$form['degree']}\nCollege: {$form['college']}\nYear: {$form['study_year']}\nBrochure: $courseTitle";
    $type = 'download'; $status = 'new';
    $stmt = $db->prepare('INSERT INTO enquiries (name,email,phone,course_id,course_name,message,type,status) VALUES (?,?,?,?,?,?,?,?)');
    if (!$stmt) return;
    $stmt->bind_param('sssissss', $form['name'], $form['email'], $form['phone'], $courseId, $courseTitle, $message, $type, $status);
    if ($stmt->execute()) {
        tt_notify_company_enquiry(['name'=>$form['name'],'phone'=>$form['phone'],'email'=>$form['email'],'course'=>$courseTitle,'message'=>$message,'type'=>$type]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title'       => 'Download ' . $courseTitle . ' Brochure | Talentteno Institute',
        'description' => 'Download the Talentteno course brochure for syllabus, batch timing, fees and placement details.',
        'canonical'   => tt_abs_url('download.php'),
        'robots'      => 'noindex, follow',
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260721-dl2">
    <style>
        .dl-wrap{min-height:calc(100vh - 86px);display:flex;align-items:center;justify-content:center;padding:32px 16px 96px;background:#f0f6ff}
        .dl-card{width:100%;max-width:480px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(8,69,178,.13);padding:28px 28px 24px;border-top:5px solid #0845b2}
        .dl-card h2{margin:0 0 4px;font-size:17px;font-weight:800;color:#07142d}
        .dl-course-name{font-size:13px;color:#0845b2;font-weight:700;margin:0 0 18px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .dl-grid{display:grid;gap:10px}
        .dl-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:10px}
        .dl-field{display:flex;flex-direction:column;gap:4px;min-width:0}
        .dl-field label{font-size:12px;font-weight:700;color:#374151}
        .dl-field input,.dl-field select{width:100%;min-width:0;height:38px;border:1.5px solid #d1dff7;border-radius:8px;padding:0 10px;font:inherit;font-size:13px;font-weight:600;background:#f8fbff;outline:none;transition:border .2s}
        .dl-field select{padding-right:32px;text-overflow:ellipsis}
        .dl-field input:focus,.dl-field select:focus{border-color:#0845b2}
        .dl-field input.is-invalid,.dl-select-btn.is-invalid{border-color:#dc2626;background:#fff5f5}
        .dl-field .err,.dl-grid .err{font-size:11px;color:#dc2626;min-height:14px}
        .dl-custom-select{position:relative;width:100%}
        .dl-select-btn{width:100%;min-width:0;height:38px;border:1.5px solid #d1dff7;border-radius:8px;padding:0 10px;background:#f8fbff;color:#07142d;font:inherit;font-size:13px;font-weight:700;display:grid;grid-template-columns:minmax(0,1fr) 16px;gap:8px;align-items:center;text-align:left;cursor:pointer}
        .dl-select-btn span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .dl-select-btn i{font-size:11px;color:#334155}
        .dl-custom-select.is-open .dl-select-btn,.dl-select-btn:focus{border-color:#0845b2;background:#fff;outline:none}
        .dl-select-menu{position:absolute;z-index:50;top:calc(100% + 4px);left:0;right:0;display:none;max-height:220px;overflow-y:auto;border:1.5px solid #b8cdf2;border-radius:8px;background:#fff;box-shadow:0 18px 36px rgba(15,23,42,.18);padding:4px}
        .dl-custom-select.is-open .dl-select-menu{display:block}
        .dl-select-option{width:100%;min-height:34px;border:0;border-radius:6px;background:#fff;color:#07142d;font:inherit;font-size:13px;font-weight:650;text-align:left;padding:8px 10px;cursor:pointer}
        .dl-select-option:hover,.dl-select-option:focus,.dl-select-option.is-selected{background:#eaf2ff;color:#0845b2;outline:none}
        .dl-select-option.is-placeholder{color:#64748b}
        .dl-otp-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;align-items:end}
        .dl-otp-row input{height:38px}
        .btn-otp{height:38px;padding:0 14px;border:0;border-radius:8px;background:#0845b2;color:#fff;font:inherit;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap}
        .btn-otp:disabled{opacity:.6;cursor:not-allowed}
        .otp-msg{font-size:11px;margin-top:2px;min-height:14px}
        .otp-msg.ok{color:#16a34a}.otp-msg.fail{color:#dc2626}
        .dl-submit{width:100%;height:42px;border:0;border-radius:10px;background:linear-gradient(135deg,#0845b2,#7c3aed);color:#fff;font:inherit;font-size:14px;font-weight:800;cursor:pointer;margin-top:6px}
        .dl-submit:disabled{opacity:.5;cursor:not-allowed}
        .dl-err-box{background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:8px 12px;font-size:12px;color:#dc2626;margin-bottom:10px}
        .verified-badge{font-size:11px;color:#16a34a;font-weight:700}
        @media (max-width:560px){
            .dl-wrap{align-items:flex-start;padding:18px 12px 118px}
            .dl-card{max-width:100%;padding:22px 18px 24px;border-radius:12px}
            .dl-row{grid-template-columns:1fr;gap:8px}
            .dl-grid{gap:9px}
            .dl-course-name{white-space:normal;line-height:1.35}
            .dl-field input,.dl-field select,.dl-select-btn{height:40px;font-size:14px}
            .dl-select-menu{position:static;max-height:230px;margin-top:6px;box-shadow:0 12px 28px rgba(15,23,42,.13)}
            .dl-otp-row{grid-template-columns:1fr;gap:8px}
            .btn-otp{width:100%;height:40px}
            .dl-submit{height:44px}
        }
    </style>
</head>
<body class="static-site download-page">
<div class="site-shell">
    <header class="site-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png?v=20260722-logo2" alt="Talentteno" width="68" height="68" decoding="async"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a>
            <nav class="site-nav">
                <a href="index.php">Home</a><a href="about.php">About</a>
                <div class="nav-item has-menu"><a href="course.php">Course <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="shorttermcourse.php">Short Term Course</a><a href="popularcourse.php">Popular Course</a><a href="advancecourse.php">Advance Course</a></div></div>
                <a href="gallery.php">Gallery</a><a href="contact.php">Contact</a>
                <div class="nav-item has-menu more-menu"><a href="#">More <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="services.php">Services</a><a href="career.php">Career</a><a href="blog.php">Blog</a><a href="project.php">Project</a></div></div>
            </nav>
            <button class="menu-button" type="button" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
        </div>
    </header>
    <main class="page-main">
        <div class="dl-wrap">
            <div class="dl-card">
                <h2><i class="fa-solid fa-download" style="color:#0845b2"></i> Download Brochure</h2>
                <p class="dl-course-name"><?= tt_h($courseTitle) ?></p>

                <?php if (!empty($errors['otp'])): ?>
                <div class="dl-err-box"><i class="fa-solid fa-circle-exclamation"></i> <?= tt_h($errors['otp']) ?></div>
                <?php endif; ?>

                <form class="dl-grid" method="POST" id="dlForm">
                    <input type="hidden" name="action" value="download">
                    <input type="hidden" name="course_id" value="<?= (int)($courseId ?: 0) ?>">
                    <input type="hidden" name="course_title" value="<?= tt_h($courseTitle) ?>">

                    <div class="dl-row">
                        <div class="dl-field">
                            <label>Full Name *</label>
                            <input type="text" name="name" value="<?= tt_h($form['name']) ?>" maxlength="60" required>
                            <span class="err" data-error-for="name"><?= tt_h($errors['name'] ?? '') ?></span>
                        </div>
                        <div class="dl-field">
                            <label>Mobile Number *</label>
                            <input type="tel" name="phone" id="phoneInput" value="<?= tt_h($form['phone']) ?>" inputmode="numeric" maxlength="10" required>
                            <span class="err" data-error-for="mobile" data-alt-error-for="phone"><?= tt_h($errors['phone'] ?? '') ?></span>
                        </div>
                    </div>

                    <div class="dl-field">
                        <label>Email ID *</label>
                        <div class="dl-otp-row">
                            <input type="email" name="email" id="emailInput" value="<?= tt_h($form['email']) ?>" required>
                            <button type="button" class="btn-otp" id="sendOtpBtn">Send OTP</button>
                        </div>
                        <span class="err" id="emailErr" data-error-for="email"><?= tt_h($errors['email'] ?? '') ?></span>
                        <div class="otp-msg" id="otpSendMsg"></div>
                    </div>

                    <div class="dl-field" id="otpField" style="display:none">
                        <label>Enter OTP <span class="verified-badge" id="verifiedBadge" style="display:none"><i class="fa-solid fa-check-circle"></i> Verified</span></label>
                        <div class="dl-otp-row">
                            <input type="text" name="otp_input" id="otpInput" inputmode="numeric" maxlength="6" placeholder="6-digit OTP">
                            <button type="button" class="btn-otp" id="verifyOtpBtn">Verify</button>
                        </div>
                        <span class="err" id="otpErr"></span>
                        <div class="otp-msg" id="otpVerifyMsg"></div>
                    </div>

                    <div class="dl-row">
                        <div>
                            <?php dl_render_dropdown('degree', 'Degree', $degreeOptions, $form['degree'], 'Select Degree'); ?>
                            <span class="err" data-error-for="degree"><?= tt_h($errors['degree'] ?? '') ?></span>
                        </div>
                        <div>
                            <?php dl_render_dropdown('college', 'College', $maduraiCollegeOptions, $form['college'], 'Select College'); ?>
                            <span class="err" data-error-for="college"><?= tt_h($errors['college'] ?? '') ?></span>
                        </div>
                    </div>

                    <div>
                        <?php dl_render_dropdown('study_year', 'Current Year / Status', ['1st Year','2nd Year','3rd Year','Passout'], $form['study_year'], 'Select'); ?>
                        <span class="err" data-error-for="study_year" data-alt-error-for="currentStatus"><?= tt_h($errors['study_year'] ?? '') ?></span>
                    </div>

                    <button type="submit" class="dl-submit" id="dlSubmit" disabled>
                        <i class="fa-solid fa-download"></i> Download Brochure
                    </button>
                </form>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260721-navbarfix1" defer></script>
<script>
(function(){
    const isLocalHost = ['localhost', '127.0.0.1'].includes(window.location.hostname);
    const pathParts = window.location.pathname.split('/');
    const frontendIndex = pathParts.findIndex(part => part === 'frontend');
    const appBasePath = frontendIndex > 1 ? '/' + pathParts.slice(1, frontendIndex).join('/') : '';
    const currentDirPath = window.location.pathname.replace(/\/[^/]*$/, '');
    function joinPath(base, path){
        const cleanBase = String(base || '').replace(/\/+$/, '');
        const cleanPath = String(path || '').replace(/^\/+/, '');
        return `${cleanBase}/${cleanPath}`;
    }
    const sameHostApiUrls = [
        joinPath(appBasePath, 'api/index.php'),
        joinPath(appBasePath, 'frontend/api/index.php'),
        joinPath(currentDirPath, 'api/index.php'),
        joinPath(appBasePath, 'api'),
        joinPath(currentDirPath, 'api'),
        '/api'
    ];
    const API_BASE_URLS = Array.from(new Set([
        ...sameHostApiUrls,
        ...(isLocalHost ? ['http://127.0.0.1:5000/api'] : [])
    ].filter(Boolean)));
    const emailInput  = document.getElementById('emailInput');
    const phoneInput  = document.getElementById('phoneInput');
    const dlForm      = document.getElementById('dlForm');
    const sendOtpBtn  = document.getElementById('sendOtpBtn');
    const otpField    = document.getElementById('otpField');
    const otpInput    = document.getElementById('otpInput');
    const verifyBtn   = document.getElementById('verifyOtpBtn');
    const dlSubmit    = document.getElementById('dlSubmit');
    const otpSendMsg  = document.getElementById('otpSendMsg');
    const otpVerifyMsg= document.getElementById('otpVerifyMsg');
    const otpErr      = document.getElementById('otpErr');
    const verifiedBadge = document.getElementById('verifiedBadge');
    let verified = false;
    let verificationToken = '';
    let resendTimer = null;
    let sendingOtp = false;

    function checkSubmit(){
        const name = dlForm.name.value.trim();
        const phone = dlForm.phone.value.replace(/\D+/g, '');
        const degree = dlForm.degree.value.trim();
        const college = dlForm.college.value.trim();
        const year = dlForm.study_year.value;
        dlSubmit.disabled = !(verified && name.length>=3 && phone.length===10 && degree.length>=2 && college.length>=2 && year);
    }

    function setMessage(el, ok, message){
        el.className = 'otp-msg ' + (ok ? 'ok' : 'fail');
        el.textContent = message || '';
    }

    function backendMessage(data, fallback){
        const message = data && data.message ? data.message : fallback;
        if (
            data && data.code === 'SMTP_CONFIG_MISSING' &&
            Array.isArray(data.missingFields) &&
            ['127.0.0.1', 'localhost'].includes(window.location.hostname)
        ) {
            return message + ' Missing backend field: ' + data.missingFields.join(', ') + '.';
        }
        return message;
    }

    function clearServerErrors(){
        dlForm.querySelectorAll('[data-error-for]').forEach(el => {
            el.textContent = '';
        });
        dlForm.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }

    function fieldElementFor(key){
        if(key === 'study_year' || key === 'currentStatus') return dlForm.study_year.closest('[data-custom-select]')?.querySelector('[data-select-button]');
        if(key === 'degree') return dlForm.degree.closest('[data-custom-select]')?.querySelector('[data-select-button]');
        if(key === 'college') return dlForm.college.closest('[data-custom-select]')?.querySelector('[data-select-button]');
        if(key === 'mobile' || key === 'phone') return phoneInput;
        if(key === 'email') return emailInput;
        if(key === 'name') return dlForm.name;
        return null;
    }

    function showServerErrors(errors){
        if(!errors || typeof errors !== 'object') return;
        Object.entries(errors).forEach(([key, message]) => {
            const target = dlForm.querySelector(`[data-error-for="${key}"], [data-alt-error-for="${key}"]`);
            if(target) target.textContent = String(message || '');
            const field = fieldElementFor(key);
            if(field) field.classList.add('is-invalid');
        });
    }

    async function parseJson(response){
        const contentType = response.headers.get('Content-Type') || '';
        if(!contentType.toLowerCase().includes('application/json')){
            await response.text().catch(() => '');
            return {
                success: false,
                message: 'Server returned invalid response.'
            };
        }
        return response.json().catch(() => ({
            success: false,
            message: 'Server returned invalid response.'
        }));
    }

    async function apiFetch(path, options){
        let lastResponse = null;
        let lastUrl = '';
        const triedUrls = [];
        for(const baseUrl of API_BASE_URLS){
            const cleanBase = baseUrl.replace(/\/+$/, '');
            const url = `${cleanBase}${path}`;
            triedUrls.push(url);
            const response = await fetch(url, options);
            lastResponse = response;
            lastUrl = url;
            const contentType = response.headers.get('Content-Type') || '';
            const lowerType = contentType.toLowerCase();
            if(lowerType.includes('application/json') || lowerType.includes('application/pdf')){
                return response;
            }
            if(response.ok && !lowerType.includes('text/html')){
                return response;
            }
        }
        if(lastResponse){
            lastResponse.apiUrl = lastUrl;
        }
        console.warn('Talentteno brochure API returned a non-JSON response.', {path, triedUrls});
        return lastResponse;
    }

    function resetVerification(){
        verified = false;
        verificationToken = '';
        verifiedBadge.style.display = 'none';
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify';
        dlSubmit.disabled = true;
        checkSubmit();
    }

    function startResendCountdown(seconds){
        let remaining = Number(seconds || 60);
        window.clearInterval(resendTimer);
        sendOtpBtn.disabled = true;
        sendOtpBtn.textContent = 'Resend OTP in ' + remaining + 's';
        resendTimer = window.setInterval(function(){
            remaining -= 1;
            if(remaining <= 0){
                window.clearInterval(resendTimer);
                sendOtpBtn.disabled = false;
                sendOtpBtn.textContent = 'Resend OTP';
                return;
            }
            sendOtpBtn.textContent = 'Resend OTP in ' + remaining + 's';
        }, 1000);
    }

    function closeCustomSelects(except){
        document.querySelectorAll('[data-custom-select].is-open').forEach(select => {
            if(select !== except){
                select.classList.remove('is-open');
                select.querySelector('[data-select-button]')?.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.querySelectorAll('[data-custom-select]').forEach(select => {
        const input = select.querySelector('input[type="hidden"]');
        const button = select.querySelector('[data-select-button]');
        const label = select.querySelector('[data-select-label]');
        const options = select.querySelectorAll('[data-value]');

        button.addEventListener('click', function(event){
            event.preventDefault();
            const willOpen = !select.classList.contains('is-open');
            closeCustomSelects(select);
            select.classList.toggle('is-open', willOpen);
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        options.forEach(option => {
            option.addEventListener('click', function(){
                const value = option.dataset.value || '';
                input.value = value;
                label.textContent = option.textContent;
                options.forEach(item => item.classList.toggle('is-selected', item === option));
                select.classList.remove('is-open');
                button.setAttribute('aria-expanded', 'false');
                input.dispatchEvent(new Event('input', {bubbles: true}));
                input.dispatchEvent(new Event('change', {bubbles: true}));
            });
        });

        select.addEventListener('keydown', function(event){
            if(event.key === 'Escape'){
                select.classList.remove('is-open');
                button.setAttribute('aria-expanded', 'false');
                button.focus();
            }
        });
    });

    document.addEventListener('click', function(event){
        if(!event.target.closest('[data-custom-select]')) closeCustomSelects();
    });

    dlForm.querySelectorAll('input,select').forEach(el => {
        el.addEventListener('input', checkSubmit);
        el.addEventListener('change', checkSubmit);
    });
    [emailInput, phoneInput].forEach(el => el.addEventListener('input', resetVerification));

    sendOtpBtn.addEventListener('click', async function(){
        if(sendingOtp) return;
        const fullName = dlForm.name.value.trim();
        const email = emailInput.value.trim();
        const mobile = phoneInput.value.replace(/\D+/g, '');
        const courseTitle = dlForm.course_title.value.trim();
        if(fullName.length < 3){
            setMessage(otpSendMsg, false, 'Enter your full name before requesting OTP.'); return;
        }
        if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
            document.getElementById('emailErr').textContent = 'Enter a valid email address.'; return;
        }
        if(!/^[0-9]{10}$/.test(mobile)){
            setMessage(otpSendMsg, false, 'Enter a valid 10-digit mobile number before requesting OTP.'); return;
        }
        document.getElementById('emailErr').textContent = '';
        sendingOtp = true;
        sendOtpBtn.disabled = true; sendOtpBtn.textContent = 'Sending...';
        otpSendMsg.className = 'otp-msg'; otpSendMsg.textContent = '';
        resetVerification();
        try {
            const res = await apiFetch('/brochure/send-otp', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    name: fullName,
                    email,
                    mobile,
                    course: courseTitle
                })
            });
            const data = await parseJson(res);
            setMessage(otpSendMsg, res.ok && data.success, backendMessage(data, 'Unable to send OTP email. Please try again.'));
            if(res.ok && data.success){
                otpField.style.display = '';
                otpInput.value = '';
                otpVerifyMsg.textContent = '';
                startResendCountdown(data.resendAfter || 60);
            } else {
                sendOtpBtn.disabled = false;
                sendOtpBtn.textContent = 'Resend OTP';
            }
        } catch(e){
            setMessage(otpSendMsg, false, 'Network error. Try again.');
            sendOtpBtn.disabled = false;
            sendOtpBtn.textContent = 'Resend OTP';
        } finally {
            sendingOtp = false;
        }
    });

    verifyBtn.addEventListener('click', async function(){
        const otp = otpInput.value.trim();
        const email = emailInput.value.trim();
        if(!/^[0-9]{6}$/.test(otp)){ otpErr.textContent='Enter the 6-digit OTP.'; return; }
        otpErr.textContent='';
        verifyBtn.disabled=true; verifyBtn.textContent='Verifying...';
        try {
            const res = await apiFetch('/brochure/verify-otp', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({email, otp})
            });
            const data = await parseJson(res);
            setMessage(otpVerifyMsg, res.ok && data.success, data.message || 'Unable to verify OTP.');
            if(res.ok && data.success && data.verificationToken){
                verified = true;
                verificationToken = data.verificationToken;
                verifiedBadge.style.display='';
                verifyBtn.textContent='Verified';
                checkSubmit();
            }
            else { verifyBtn.disabled=false; verifyBtn.textContent='Verify'; }
        } catch(e){ setMessage(otpVerifyMsg, false, 'Network error.'); verifyBtn.disabled=false; verifyBtn.textContent='Verify'; }
    });

    dlForm.addEventListener('submit', async function(event){
        event.preventDefault();
        clearServerErrors();
        if(!verified || !verificationToken){
            setMessage(otpVerifyMsg, false, 'Please verify your email with OTP before downloading.');
            return;
        }
        dlSubmit.disabled = true;
        dlSubmit.textContent = 'Preparing Download...';
        try {
            const response = await apiFetch('/brochure/download', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    name: dlForm.name.value.trim(),
                    email: emailInput.value.trim(),
                    mobile: phoneInput.value.replace(/\D+/g, ''),
                    degree: dlForm.degree.value.trim(),
                    college: dlForm.college.value.trim(),
                    study_year: dlForm.study_year.value,
                    course: dlForm.course_title.value.trim(),
                    verificationToken
                })
            });
            const contentType = response.headers.get('Content-Type') || '';
            if(!response.ok || contentType.toLowerCase().includes('application/json') || contentType.toLowerCase().includes('text/html')){
                const data = await parseJson(response);
                setMessage(otpVerifyMsg, false, data.message || 'Unable to download brochure. Please try again.');
                showServerErrors(data.errors);
                dlSubmit.textContent = 'Download Brochure';
                checkSubmit();
                return;
            }
            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') || '';
            const match = disposition.match(/filename="?([^"]+)"?/i);
            const filename = match ? match[1] : 'talentteno-brochure.pdf';
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
            dlSubmit.textContent = 'Download Brochure';
            checkSubmit();
        } catch(e){
            setMessage(otpVerifyMsg, false, 'Network error. Try again.');
            dlSubmit.textContent = 'Download Brochure';
            checkSubmit();
        }
    });

    checkSubmit();
})();
</script>
</body>
</html>
