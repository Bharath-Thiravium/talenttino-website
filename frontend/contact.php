<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$mapUrl = tt_google_maps_url($settings);
$mapEmbedUrl = tt_google_maps_embed_url($settings);
$contactFormResult = null;
$selectedCourse = trim((string)($_GET['course'] ?? ''));
$selectedTopic = trim((string)($_GET['topic'] ?? ''));
$isFranchiseTopic = strtolower($selectedTopic) === 'franchise';
$topicLabel = $isFranchiseTopic ? 'Talentteno franchise / institute partnership' : $selectedTopic;
$contactCourses = tt_courses();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['form_source'] ?? '') === 'contact') {
    $contactFormResult = tt_submit_enquiry($_POST, 'enquiry');
    $selectedCourse = trim((string)($_POST['course'] ?? $selectedCourse));
}
$selectedCourseExists = $selectedCourse === '';
foreach ($contactCourses as $course) {
    if ($selectedCourse === ($course['title'] ?? '')) {
        $selectedCourseExists = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'Contact Talentteno Institute Madurai | Course Counselling',
        'description' => 'Contact Talentteno Institute in Tiruppalai, Madurai for IT course admission, free counselling, demo class, EMI details, internship support and placement assistance.',
        'canonical' => tt_abs_url('contact.php'),
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Contact', 'url' => 'contact.php'],
        ],
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260718-footerhover1">
</head>
<body class="static-site contact-page">
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
        <section class="page-hero contact-page-hero has-page-hero-image">
            <img class="page-hero-bg" src="assets/images/conect.png" alt="" aria-hidden="true" decoding="async" fetchpriority="high">
            <span class="page-hero-overlay" aria-hidden="true"></span>
            <div class="site-container contact-hero-layout">
                <div class="contact-hero-copy reveal">
                    <span class="hero-kicker"><i class="fa-solid <?= $isFranchiseTopic ? 'fa-handshake' : 'fa-headset' ?>"></i> <?= $isFranchiseTopic ? 'Franchise and Partnership Enquiry' : 'Admission and Course Counselling' ?></span>
                    <h1><?= $isFranchiseTopic ? 'Partner with Talentteno' : 'Contact Talentteno' ?></h1>
                    <p><?= $isFranchiseTopic ? 'Share your city, space, and training plan. Our team will explain Talentteno institute partnership support, course model, branding, counselling process, and next steps.' : 'Visit us, call us, or send a message. Our counsellors will guide you to the right course, offer details, internship support and EMI finance options.' ?></p>
                </div>
            </div>
        </section>
        <section class="section">
            <div class="site-container contact-banner reveal">
                <div>
                    <h2><?= $isFranchiseTopic ? 'Open a Practical IT Training Centre' : 'Start Your IT Career Today' ?></h2>
                    <p><?= $isFranchiseTopic ? 'Get details about institute setup guidance, course content, student counselling, branding support, trainer coordination and admission workflow.' : 'Ask about the Rs 14,999 basic-to-advanced course offer, Rs 49,999 cyber security combo pack, free internship, spoken English class and placement assistance.' ?></p>
                </div>
                <div class="contact-actions">
                    <a href="tel:<?= preg_replace('/\s+/', '', $settings['phone1']) ?>"><i class="fa-solid fa-phone"></i>&nbsp; <?= tt_h($settings['phone1']) ?></a>
                    <a href="tel:<?= preg_replace('/\s+/', '', $settings['phone2']) ?>"><i class="fa-solid fa-phone"></i>&nbsp; <?= tt_h($settings['phone2']) ?></a>
                </div>
            </div>
        </section>
        <section class="section alt">
            <div class="site-container contact-grid">
                <a class="contact-card contact-card-link reveal" href="<?= tt_h($mapUrl) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot"></i><h3>Address</h3><p><?= tt_h($settings['address']) ?></p><span>Open in Google Maps <i class="fa-solid fa-arrow-up-right-from-square"></i></span></a>
                <div class="contact-card reveal"><i class="fa-solid fa-phone"></i><h3>Phone</h3><p><a href="tel:<?= preg_replace('/\s+/', '', $settings['phone1']) ?>"><?= tt_h($settings['phone1']) ?></a><br><a href="tel:<?= preg_replace('/\s+/', '', $settings['phone2']) ?>"><?= tt_h($settings['phone2']) ?></a></p></div>
                <div class="contact-card reveal"><i class="fa-solid fa-envelope"></i><h3>Email</h3><p><a href="mailto:<?= tt_h($settings['email']) ?>"><?= tt_h($settings['email']) ?></a></p></div>
            </div>
            <div class="site-container contact-workspace">
                <form class="contact-form reveal" method="POST">
                    <div class="contact-form-heading"><span>Send an enquiry</span><h2>Talk to our course counsellor</h2></div>
                    <input type="hidden" name="form_source" value="contact">
                    <?php if ($contactFormResult): ?>
                    <div class="form-alert <?= $contactFormResult['ok'] ? 'success' : 'error' ?>" role="<?= $contactFormResult['ok'] ? 'status' : 'alert' ?>"><?= tt_h($contactFormResult['message']) ?></div>
                    <?php endif; ?>
                    <div class="field-grid">
                        <label class="form-field"><span>Full name <b aria-hidden="true">*</b></span><input type="text" name="name" placeholder="e.g. Priya Kumar" autocomplete="name" minlength="2" maxlength="80" required></label>
                        <label class="form-field"><span>Phone number <b aria-hidden="true">*</b></span><input type="tel" name="phone" placeholder="10 digit mobile number" autocomplete="tel" inputmode="numeric" pattern="[6-9][0-9]{9}" minlength="10" maxlength="10" required></label>
                    </div>
                    <div class="field-grid">
                        <label class="form-field"><span>Email address</span><input type="email" name="email" placeholder="you@example.com" autocomplete="email" maxlength="190"></label>
                        <label class="form-field"><span>Course of interest</span><select name="course">
                            <option value="">Select course</option>
                            <?php if ($selectedCourse !== '' && !$selectedCourseExists): ?>
                            <option selected><?= tt_h($selectedCourse) ?></option>
                            <?php endif; ?>
                            <?php foreach ($contactCourses as $course): ?>
                            <option <?= $selectedCourse === ($course['title'] ?? '') ? 'selected' : '' ?>><?= tt_h($course['title']) ?></option>
                            <?php endforeach; ?>
                        </select></label>
                    </div>
                    <label class="form-field"><span>How can we help?</span><textarea name="message" placeholder="<?= $isFranchiseTopic ? 'Tell us your city, available space and partnership plan' : 'Tell us about your learning goal' ?>" maxlength="2000"><?= $topicLabel !== '' ? tt_h('I want more details about ' . $topicLabel . '.') : ($selectedCourse !== '' ? tt_h('I want enquiry details for ' . $selectedCourse . '.') : '') ?></textarea></label>
                    <label class="form-honeypot" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
                </form>
                <div class="contact-map reveal">
                    <iframe src="<?= tt_h($mapEmbedUrl) ?>" title="Talentteno Institute location on Google Maps" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    <div class="contact-map-details">
                        <div><i class="fa-solid fa-location-dot"></i><span><strong>Visit Talentteno Institute</strong><?= tt_h($settings['address']) ?></span></div>
                        <a href="<?= tt_h($mapUrl) ?>" target="_blank" rel="noopener">Directions <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </div>
                </div>
            </div>
        </section>
        <section class="section">
            <div class="site-container timeline">
                <div class="timeline-card reveal"><h3>Call or Visit</h3><p>Contact the institute or visit the Tiruppalai, Madurai centre.</p></div>
                <div class="timeline-card reveal"><h3>Choose Course</h3><p>Select from IT, AI, design, marketing, programming, Tally or cyber tracks.</p></div>
                <div class="timeline-card reveal"><h3>Confirm Offer</h3><p>Check available discount, EMI finance and batch timing details.</p></div>
                <div class="timeline-card reveal"><h3>Start Training</h3><p>Begin practical learning with projects, internship support and career guidance.</p></div>
            </div>
        </section>
    </main>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260718-scrollsmooth1" defer></script>
</body>
</html>
