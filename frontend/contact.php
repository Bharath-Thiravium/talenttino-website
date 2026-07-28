<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$phone1Link = tt_phone_href($settings['phone1'] ?? '');
$phone2Link = tt_phone_href($settings['phone2'] ?? '');
$emailLink = trim((string)($settings['email'] ?? ''));
$mapUrl = tt_google_maps_url($settings);
$mapEmbedUrl = tt_google_maps_embed_url($settings);
$contactFormResult = null;
$selectedCourse = trim((string)($_GET['course'] ?? ''));
$selectedTopic = trim((string)($_GET['topic'] ?? ''));
$isFranchiseTopic = strtolower($selectedTopic) === 'franchise';
$topicLabel = $isFranchiseTopic ? 'Talentteno franchise / institute partnership' : $selectedTopic;
$contactCourses = tt_courses();
$contactHeroImage = tt_optimized_image_url('assets/images/conect.png', 1536);

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
    <link rel="stylesheet" href="<?= tt_h(tt_asset_url('assets/css/site-pages.min.css')) ?>">
    <style>
        .contact-page .contact-grid{align-items:stretch}
        .contact-page .contact-card{
            position:relative!important;
            overflow:hidden!important;
            border:0!important;
            border-top:0!important;
            border-radius:18px!important;
            background:linear-gradient(145deg,rgba(255,255,255,.98),rgba(247,251,255,.92))!important;
            box-shadow:0 24px 70px rgba(15,23,42,.10),inset 0 1px 0 rgba(255,255,255,.9)!important;
            transform:translateY(0)!important;
            transition:transform .34s cubic-bezier(.2,.8,.2,1),box-shadow .34s ease,background .34s ease!important;
        }
        .contact-page .contact-card::before{
            content:"";
            position:absolute;
            inset:0;
            border-radius:inherit;
            background:radial-gradient(circle at var(--pointer-x,18%) var(--pointer-y,12%),rgba(37,99,235,.16),transparent 34%),
                       linear-gradient(135deg,rgba(37,99,235,.10),rgba(192,38,211,.08));
            opacity:0;
            transition:opacity .34s ease;
            pointer-events:none;
        }
        .contact-page .contact-card::after{
            content:"";
            position:absolute;
            left:22px;
            right:22px;
            bottom:0;
            height:3px;
            border-radius:999px 999px 0 0;
            background:linear-gradient(90deg,#2563eb,#06b6d4,#c026d3);
            transform:scaleX(.18);
            transform-origin:left center;
            opacity:.72;
            transition:transform .38s cubic-bezier(.2,.8,.2,1),opacity .3s ease;
        }
        .contact-page .contact-card:hover{
            transform:translateY(-10px)!important;
            background:#fff!important;
            box-shadow:0 34px 90px rgba(15,23,42,.16),0 14px 34px rgba(37,99,235,.10)!important;
        }
        .contact-page .contact-card:hover::before{opacity:1}
        .contact-page .contact-card:hover::after{transform:scaleX(1);opacity:1}
        .contact-page .contact-card>i{
            position:relative;
            z-index:1;
            border-radius:18px!important;
            box-shadow:0 16px 32px rgba(37,99,235,.18)!important;
            transition:transform .34s cubic-bezier(.2,.8,.2,1),box-shadow .34s ease!important;
        }
        .contact-page .contact-card:hover>i{
            transform:translateY(-3px) rotate(-6deg) scale(1.06)!important;
            box-shadow:0 20px 42px rgba(124,58,237,.26)!important;
        }
        .contact-page .contact-card h3,
        .contact-page .contact-card p,
        .contact-page .contact-card span{position:relative;z-index:1}
        @media(prefers-reduced-motion:reduce){
            .contact-page .contact-card,
            .contact-page .contact-card::before,
            .contact-page .contact-card::after,
            .contact-page .contact-card>i{transition:none!important}
            .contact-page .contact-card:hover,
            .contact-page .contact-card:hover>i{transform:none!important}
        }
    </style>
</head>
<body class="static-site contact-page">
<div class="site-shell">
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    <main class="page-main">
        <section class="page-hero contact-page-hero has-page-hero-image">
            <img class="page-hero-bg" src="<?= tt_h($contactHeroImage) ?>" alt="" aria-hidden="true" decoding="async" fetchpriority="high">
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
                    <a href="<?= tt_h($phone1Link) ?>"><i class="fa-solid fa-phone"></i>&nbsp; <?= tt_h($settings['phone1']) ?></a>
                    <a href="<?= tt_h($phone2Link) ?>"><i class="fa-solid fa-phone"></i>&nbsp; <?= tt_h($settings['phone2']) ?></a>
                </div>
            </div>
        </section>
        <section class="section alt">
            <div class="site-container contact-grid">
                <a class="contact-card contact-card-link reveal" href="<?= tt_h($mapUrl) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot"></i><h3>Address</h3><p><?= tt_h($settings['address']) ?></p><span>Open in Google Maps <i class="fa-solid fa-arrow-up-right-from-square"></i></span></a>
                <div class="contact-card reveal"><i class="fa-solid fa-phone"></i><h3>Phone</h3><p><a href="<?= tt_h($phone1Link) ?>"><?= tt_h($settings['phone1']) ?></a><br><a href="<?= tt_h($phone2Link) ?>"><?= tt_h($settings['phone2']) ?></a></p></div>
                <div class="contact-card reveal"><i class="fa-solid fa-envelope"></i><h3>Email</h3><p><a href="mailto:<?= tt_h($emailLink) ?>"><?= tt_h($settings['email']) ?></a></p></div>
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
                    <a class="contact-map-fallback" href="<?= tt_h($mapUrl) ?>" target="_blank" rel="noopener">
                        <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                        <span>Open location in Google Maps</span>
                    </a>
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
<script src="<?= tt_h(tt_asset_url('assets/js/site-pages.min.js')) ?>" defer></script>
</body>
</html>
