<?php
require_once __DIR__ . '/includes/site-data.php';

$items = tt_hiring_items();
$items = $items ?: [
    [
        'icon' => 'fa-chart-line',
        'title' => 'Data Science',
        'short_desc' => 'Data science and analytics trainer needed.',
        'description' => 'Python, statistics, machine learning, dashboards and practical project mentoring experience preferred.',
        'image' => 'assets/images/home1.webp',
    ],
    [
        'icon' => 'fa-pen-nib',
        'title' => 'UI/UX Designer',
        'short_desc' => 'UI/UX design trainer and mentor needed.',
        'description' => 'Figma, wireframing, prototyping, design systems and portfolio guidance experience preferred.',
        'image' => 'assets/images/home2.webp',
    ],
    [
        'icon' => 'fa-vial-circle-check',
        'title' => 'Software Tester',
        'short_desc' => 'Manual and automation testing trainer needed.',
        'description' => 'Testing concepts, test cases, Selenium, API testing and real-time QA workflow experience preferred.',
        'image' => 'assets/images/contact-counsellor-hero.png',
    ],
    [
        'icon' => 'fa-users-gear',
        'title' => 'Staffs Needed',
        'short_desc' => 'Office, counselling and support staff needed.',
        'description' => 'Apply for student counselling, admission support, coordination and institute operations roles.',
        'image' => 'assets/images/home.webp',
    ],
];

$roleOptions = array_column($items, 'title');
$stateOptions = ['Tamil Nadu', 'Kerala', 'Karnataka', 'Andhra Pradesh', 'Telangana', 'Puducherry', 'Other'];
$districtOptions = ['Madurai', 'Chennai', 'Coimbatore', 'Trichy', 'Tirunelveli', 'Salem', 'Dindigul', 'Virudhunagar', 'Theni', 'Other'];
$selectedRole = trim((string)($_GET['role'] ?? $_POST['role'] ?? ''));
if (!in_array($selectedRole, $roleOptions, true)) {
    $selectedRole = '';
}

$db = tt_db();
if ($db) {
    @$db->query("ALTER TABLE enquiries ADD COLUMN IF NOT EXISTS resume_path VARCHAR(255) NULL AFTER message");
}

function tt_hiring_resume_upload(): array
{
    if (!isset($_FILES['resume']) || ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => ''];
    }

    if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'message' => 'Unable to upload resume. Please try again.'];
    }

    $maxBytes = 5 * 1024 * 1024;
    if ((int)($_FILES['resume']['size'] ?? 0) > $maxBytes) {
        return ['ok' => false, 'message' => 'Resume file must be 5 MB or smaller.'];
    }

    $tmp = (string)($_FILES['resume']['tmp_name'] ?? '');
    $mime = is_file($tmp) ? (mime_content_type($tmp) ?: '') : '';
    $allowedMime = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/octet-stream' => '',
        'application/zip' => '',
    ];
    $extension = strtolower(pathinfo((string)($_FILES['resume']['name'] ?? ''), PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'doc', 'docx'];
    if (!in_array($extension, $allowedExtensions, true) || !isset($allowedMime[$mime])) {
        return ['ok' => false, 'message' => 'Upload resume as PDF, DOC or DOCX only.'];
    }

    $dir = __DIR__ . '/uploads/resumes/';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return ['ok' => false, 'message' => 'Resume upload folder is not available.'];
    }

    $name = pathinfo((string)($_FILES['resume']['name'] ?? 'resume'), PATHINFO_FILENAME);
    $safeName = preg_replace('/[^a-z0-9-]+/i', '-', strtolower($name)) ?: 'resume';
    $file = trim($safeName, '-') . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    if (!move_uploaded_file($tmp, $dir . $file)) {
        return ['ok' => false, 'message' => 'Unable to save resume. Please try again.'];
    }

    return ['ok' => true, 'path' => 'uploads/resumes/' . $file];
}

$hiringFormResult = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['form_source'] ?? '') === 'hiring') {
    $role = trim((string)($_POST['role'] ?? ''));
    $state = trim((string)($_POST['state'] ?? ''));
    $district = trim((string)($_POST['district'] ?? ''));
    $referred = isset($_POST['referred']) ? 'Yes' : 'No';
    $captchaOk = ($_POST['captcha_confirm'] ?? '') === '1';
    $experience = trim((string)($_POST['experience'] ?? ''));
    $qualification = trim((string)($_POST['qualification'] ?? ''));
    $details = trim((string)($_POST['message'] ?? ''));
    $resumeUpload = tt_hiring_resume_upload();

    if (!in_array($role, $roleOptions, true)) {
        $hiringFormResult = ['ok' => false, 'message' => 'Please select a valid hiring role.'];
    } elseif (trim((string)($_POST['email'] ?? '')) === '') {
        $hiringFormResult = ['ok' => false, 'message' => 'Please enter your email address.'];
    } elseif ($experience === '' || strlen($experience) < 2) {
        $hiringFormResult = ['ok' => false, 'message' => 'Please enter your experience details.'];
    } elseif ($qualification === '' || strlen($qualification) < 2) {
        $hiringFormResult = ['ok' => false, 'message' => 'Please enter your qualification.'];
    } elseif ($state === '') {
        $hiringFormResult = ['ok' => false, 'message' => 'Please select your state.'];
    } elseif ($district === '') {
        $hiringFormResult = ['ok' => false, 'message' => 'Please select your district.'];
    } elseif ($details === '' || strlen($details) < 10) {
        $hiringFormResult = ['ok' => false, 'message' => 'Please enter your skills, location and availability details.'];
    } elseif (!$resumeUpload['ok']) {
        $hiringFormResult = ['ok' => false, 'message' => $resumeUpload['message']];
    } elseif ($resumeUpload['path'] === '') {
        $hiringFormResult = ['ok' => false, 'message' => 'Please upload your resume.'];
    } elseif (!$captchaOk) {
        $hiringFormResult = ['ok' => false, 'message' => 'Please confirm that you are not a robot.'];
    } else {
        $payload = $_POST;
        $payload['course'] = 'Hiring - ' . $role;
        $payload['resume_path'] = $resumeUpload['path'];
        $payload['message'] = trim(implode("\n", array_filter([
            'Hiring Role: ' . $role,
            $experience !== '' ? 'Experience: ' . $experience : '',
            $qualification !== '' ? 'Qualification: ' . $qualification : '',
            $state !== '' ? 'State: ' . $state : '',
            $district !== '' ? 'District: ' . $district : '',
            'Referred by someone: ' . $referred,
            $resumeUpload['path'] !== '' ? 'Resume: ' . $resumeUpload['path'] : '',
            $details !== '' ? 'Candidate Details: ' . $details : '',
        ])));
        $hiringFormResult = tt_submit_enquiry($payload, 'enquiry');
        if ($hiringFormResult['ok']) {
            $selectedRole = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo(['title' => 'Hiring | Talentteno Institute', 'description' => 'Hiring opportunities for trainers, counsellors and placement partners at Talentteno Institute.', 'canonical' => tt_abs_url('hiring.php')]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260718-brandfix1">
</head>
<body class="static-site hiring-page">
<div class="site-shell">
    <header class="site-header"><div class="site-container nav-wrap"><a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="uploads/optimized/logot-transparent-w64.webp" srcset="uploads/optimized/logot-transparent-w64.webp 64w, uploads/optimized/logot-transparent-w128.webp 128w" sizes="(max-width: 980px) 58px, 68px" alt="Talentteno Institute logo" width="68" height="68" decoding="async"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a><nav class="site-nav">
        <a href="index.php">Home</a><a href="about.php">About</a><div class="nav-item has-menu"><a href="course.php">Course <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="shorttermcourse.php">Short Term Course</a><a href="popularcourse.php">Popular Course</a><a href="advancecourse.php">Advance Course</a></div></div><a href="gallery.php">Gallery</a><a href="contact.php">Contact</a><div class="nav-item has-menu more-menu"><a href="#">More <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="services.php">Services</a><a href="career.php">Career</a><a href="blog.php">Blog</a><a href="project.php">Project</a></div></div>
    </nav><button class="menu-button" type="button" aria-label="Open menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button></div></header>
    <main class="page-main">
        <section class="page-hero has-page-hero-image"><img class="page-hero-bg" src="assets/images/hairin.png" alt="" aria-hidden="true" decoding="async" fetchpriority="high"><span class="page-hero-overlay" aria-hidden="true"></span><div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-user-plus"></i> Hiring</span><h1>Work with Talentteno Institute</h1><p>Data science, UI/UX design, software testing and office staff openings are available. Send your details to our admin team.</p></div></section>
        <section class="section"><div class="site-container detail-grid rich-detail-grid">
            <?php foreach ($items as $item): ?><?php $image = tt_item_image($item, 'hiring'); ?>
            <article class="detail-tile rich-detail-card reveal"><div class="rich-detail-image"><img src="<?= tt_h($image) ?>" alt="<?= tt_h($item['title']) ?>" loading="lazy" decoding="async"></div><div class="rich-detail-body"><i class="fa-solid <?= tt_h($item['icon']) ?>"></i><h3><?= tt_h($item['title']) ?></h3><p class="rich-detail-short"><?= tt_h($item['short_desc']) ?></p><p class="rich-detail-more"><?= tt_h($item['description']) ?></p><button type="button" class="rich-detail-link" data-smd-trigger data-smd-title="<?= tt_h($item['title']) ?>" data-smd-category="Hiring" data-smd-description="<?= tt_h($item['description']) ?>" data-smd-image="<?= tt_h($image) ?>" data-smd-features="<?= tt_h($item['short_desc'] . "\n" . $item['description']) ?>" data-smd-enquire="hiring.php?role=<?= rawurlencode($item['title']) ?>#hiring-enquiry">Apply / Enquire <i class="fa-solid fa-arrow-right"></i></button></div></article>
            <?php endforeach; ?>
        </div></section>
        <section class="section alt hiring-form-section" id="hiring-enquiry">
            <div class="site-container hiring-form-layout">
                <div class="hiring-form-copy reveal">
                    <span class="model-label">Career Application</span>
                    <h2>Join our trainer and support team</h2>
                    <p>Share your profile details, preferred role, location and resume. Our admin team will review your application and contact suitable candidates for the next step.</p>
                    <ul>
                        <li><i class="fa-solid fa-circle-check"></i> Trainer roles for technical courses</li>
                        <li><i class="fa-solid fa-circle-check"></i> Counselling and office support openings</li>
                        <li><i class="fa-solid fa-circle-check"></i> Location details help us plan interviews</li>
                        <li><i class="fa-solid fa-circle-check"></i> Resume is reviewed by the admin team</li>
                    </ul>
                </div>
                <form class="contact-form hiring-enquiry-form reveal" method="POST" enctype="multipart/form-data">
                    <div class="contact-form-heading"><span>Apply / Enquire</span><h2>Hiring enquiry form</h2></div>
                    <input type="hidden" name="form_source" value="hiring">
                    <?php if ($hiringFormResult): ?>
                    <div class="form-alert <?= $hiringFormResult['ok'] ? 'success' : 'error' ?>" role="<?= $hiringFormResult['ok'] ? 'status' : 'alert' ?>"><?= tt_h($hiringFormResult['message']) ?></div>
                    <?php endif; ?>
                    <div class="field-grid">
                        <label class="form-field"><span>Full name <b aria-hidden="true">*</b></span><input type="text" name="name" placeholder="Your full name" autocomplete="name" minlength="2" maxlength="80" required></label>
                        <label class="form-field"><span>Phone number <b aria-hidden="true">*</b></span><input type="tel" name="phone" placeholder="10 digit mobile number" autocomplete="tel" inputmode="numeric" pattern="[6-9][0-9]{9}" minlength="10" maxlength="10" required></label>
                    </div>
                    <div class="field-grid">
                        <label class="form-field"><span>Email address <b aria-hidden="true">*</b></span><input type="email" name="email" placeholder="you@example.com" autocomplete="email" maxlength="190" required></label>
                        <label class="form-field"><span>Hiring role <b aria-hidden="true">*</b></span><select name="role" required>
                            <option value="">Select role</option>
                            <?php foreach ($roleOptions as $role): ?>
                            <option value="<?= tt_h($role) ?>" <?= $selectedRole === $role ? 'selected' : '' ?>><?= tt_h($role) ?></option>
                            <?php endforeach; ?>
                        </select></label>
                    </div>
                    <div class="field-grid">
                        <label class="form-field"><span>Experience <b aria-hidden="true">*</b></span><input type="text" name="experience" placeholder="Example: 2 years / Fresher" minlength="2" maxlength="120" required></label>
                        <label class="form-field"><span>Qualification <b aria-hidden="true">*</b></span><input type="text" name="qualification" placeholder="Example: B.E CSE, B.Sc CS" minlength="2" maxlength="150" required></label>
                    </div>
                    <div class="field-grid">
                        <label class="form-field"><span>State <b aria-hidden="true">*</b></span><select name="state" required>
                            <option value="">Select State</option>
                            <?php foreach ($stateOptions as $stateOption): ?>
                            <option value="<?= tt_h($stateOption) ?>" <?= (($_POST['state'] ?? '') === $stateOption) ? 'selected' : '' ?>><?= tt_h($stateOption) ?></option>
                            <?php endforeach; ?>
                        </select></label>
                        <label class="form-field"><span>District <b aria-hidden="true">*</b></span><select name="district" required>
                            <option value="">Select District</option>
                            <?php foreach ($districtOptions as $districtOption): ?>
                            <option value="<?= tt_h($districtOption) ?>" <?= (($_POST['district'] ?? '') === $districtOption) ? 'selected' : '' ?>><?= tt_h($districtOption) ?></option>
                            <?php endforeach; ?>
                        </select></label>
                    </div>
                    <label class="form-field hiring-resume-field"><span>Resume <b aria-hidden="true">*</b></span><input type="file" name="resume" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required><small>Upload PDF, DOC or DOCX. Maximum 5 MB.</small></label>
                    <label class="form-field"><span>Skills / details <b aria-hidden="true">*</b></span><textarea name="message" placeholder="Tell us about your skills, current location and availability" minlength="10" maxlength="2000" required></textarea></label>
                    <label class="hiring-check-row"><input type="checkbox" name="referred" value="1" <?= isset($_POST['referred']) ? 'checked' : '' ?>><span>Were you referred by someone?</span></label>
                    <label class="hiring-captcha-box"><input type="checkbox" name="captcha_confirm" value="1" required><span class="captcha-check"></span><span>I'm not a robot</span><strong>reCAPTCHA</strong></label>
                    <label class="form-honeypot" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Apply Now</button>
                </form>
            </div>
        </section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div><script src="assets/js/site-pages.min.js?v=20260718-scrollsmooth1" defer></script></body></html>
