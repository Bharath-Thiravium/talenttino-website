<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$services = tt_services(6);
$steps = tt_process_steps(4);
$defaultAboutSteps = [
    ['title' => 'Free Counselling', 'description' => 'Talk to our career counsellor about your background, interests and goals to choose the right course.'],
    ['title' => 'Skill Assessment', 'description' => 'Understand your current level and get a practical learning path from basics to advanced topics.'],
    ['title' => 'Hands-on Training', 'description' => 'Learn through guided labs, assignments, live projects and mentor-led practice sessions.'],
    ['title' => 'Career Support', 'description' => 'Get internship guidance, resume preparation, interview support and placement assistance.'],
];
$uniqueStepTitles = [];
$cleanSteps = [];
foreach ($steps as $step) {
    $titleKey = strtolower(trim((string)($step['title'] ?? '')));
    if ($titleKey === '' || isset($uniqueStepTitles[$titleKey])) {
        continue;
    }
    $uniqueStepTitles[$titleKey] = true;
    $cleanSteps[] = $step;
}
$steps = count($cleanSteps) >= 4 ? array_slice($cleanSteps, 0, 4) : $defaultAboutSteps;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'About Talentteno Institute | Practical IT Training in Madurai',
        'description' => $settings['about_content'],
        'canonical' => tt_abs_url('about.php'),
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'About', 'url' => 'about.php'],
        ],
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preload" as="image" href="assets/images/optimized/about-w1536.webp" imagesrcset="assets/images/optimized/about-w768.webp 768w, assets/images/optimized/about-w1200.webp 1200w, assets/images/optimized/about-w1536.webp 1536w" imagesizes="100vw" fetchpriority="high">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Space+Grotesk:wght@600;700&amp;display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Space+Grotesk:wght@600;700&amp;display=swap">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </noscript>
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260721-navbarfix1">
</head>
<body class="static-site about-page">
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
        <section class="page-hero has-page-hero-image">
            <img class="page-hero-bg" src="assets/images/optimized/about-w1536.webp" srcset="assets/images/optimized/about-w768.webp 768w, assets/images/optimized/about-w1200.webp 1200w, assets/images/optimized/about-w1536.webp 1536w" sizes="100vw" alt="" aria-hidden="true" decoding="async" fetchpriority="high" width="1536" height="1024">
            <span class="page-hero-overlay" aria-hidden="true"></span>
            <div class="site-container reveal">
                <span class="hero-kicker"><i class="fa-solid fa-graduation-cap"></i> Practical IT Institute</span>
                <h1><?= tt_h($settings['about_title']) ?></h1>
                <p><?= tt_h($settings['tagline']) ?></p>
            </div>
        </section>
        <section class="section about-intro-section">
            <div class="site-container about-intro">
                <div class="about-story reveal reveal-left">
                    <span class="section-label">Institute overview</span>
                    <h2>Practical IT training built around student career growth</h2>
                    <p class="about-lead"><?= tt_h($settings['about_content']) ?></p>
                    <div class="tt-stat-row">
                        <div class="tt-stat-card">
                            <span class="tt-stat-num"><?= tt_h($settings['total_students']) ?></span>
                            <span class="tt-stat-title">Students trained</span>
                        </div>
                        <div class="tt-stat-card">
                            <span class="tt-stat-num"><?= tt_h($settings['total_trainers']) ?></span>
                            <span class="tt-stat-title">Expert trainers</span>
                        </div>
                        <div class="tt-stat-card">
                            <span class="tt-stat-num"><?= tt_h($settings['success_rate']) ?></span>
                            <span class="tt-stat-title">Career support</span>
                        </div>
                    </div>
                </div>
                <div class="identity-image about-visual-stack reveal reveal-right">
                    <div class="about-visual-main">
                        <img src="uploads/optimized/home-w900.webp" srcset="uploads/optimized/home-w430.webp 430w, uploads/optimized/home-w900.webp 900w, uploads/optimized/home-w1400.webp 1400w" sizes="(max-width: 1100px) 100vw, 520px" alt="Students receiving practical coding training at Talentteno Institute" loading="lazy" decoding="async" width="900" height="506">
                        <span class="about-visual-badge"><i class="fa-solid fa-code"></i> Live project practice</span>
                    </div>
                    <div class="about-visual-mini about-visual-mini-one">
                        <img src="uploads/media/cyber-security-20260703-133329-242125.png" alt="Cyber security course visual" loading="lazy" decoding="async" width="420" height="280">
                    </div>
                    <div class="about-visual-mini about-visual-mini-two">
                        <img src="uploads/media/data-science-ai-20260703-133112-527863.png" alt="Data science and artificial intelligence course visual" loading="lazy" decoding="async" width="420" height="280">
                    </div>
                </div>
            </div>
        </section>
        <section class="section mission-vision-section">
            <div class="site-container">
                <div class="section-head reveal">
                    <span class="section-label">Training direction</span>
                    <h2>Structured learning with measurable career outcomes.</h2>
                    <p>Every programme is designed around practical ability, professional confidence and sustainable career growth.</p>
                </div>
                <div class="mission-vision-grid">
                    <article class="purpose-card mission-card reveal reveal-left">
                        <span class="purpose-icon"><i class="fa-solid fa-bullseye"></i></span>
                        <div><h3>Our Mission</h3><p><?= tt_h($settings['mission']) ?></p></div>
                    </article>
                    <article class="purpose-card vision-card reveal reveal-right">
                        <span class="purpose-icon"><i class="fa-solid fa-binoculars"></i></span>
                        <div><h3>Our Vision</h3><p><?= tt_h($settings['vision']) ?></p></div>
                    </article>
                </div>
            </div>
        </section>
        <section class="section about-values-section">
            <div class="site-container">
                <div class="section-head reveal">
                    <span class="section-label">Student support system</span>
                    <h2>More than classroom training</h2>
                    <p>Students receive a complete learning environment that connects technical knowledge with workplace readiness.</p>
                </div>
                <div class="about-values-grid">
                    <article class="about-value reveal"><i class="fa-solid fa-laptop-code"></i><h3>Practice First</h3><p>Guided labs, assignments and live projects turn every concept into usable skill.</p></article>
                    <article class="about-value reveal"><i class="fa-solid fa-people-group"></i><h3>Mentor Support</h3><p>Experienced trainers provide direct feedback and individual learning guidance.</p></article>
                    <article class="about-value reveal"><i class="fa-solid fa-comments"></i><h3>Communication Skills</h3><p>Spoken English and presentation support help students become interview-ready.</p></article>
                    <article class="about-value reveal"><i class="fa-solid fa-briefcase"></i><h3>Career Preparation</h3><p>Internship, certification and placement assistance support the move into employment.</p></article>
                </div>
            </div>
        </section>
        <section class="section about-image-band">
            <div class="site-container">
                <div class="about-band-copy reveal reveal-left">
                    <span class="section-label">Training environment</span>
                    <h2>Built for hands-on learners</h2>
                    <p>Students work through modern programming, AI, cyber security, analytics and digital marketing workflows with guided practice and portfolio-ready output.</p>
                </div>
                <div class="about-image-strip reveal reveal-right" aria-label="Talentteno course visuals">
                    <img src="uploads/media/full-stack-development-20260703-133158-761383.png" alt="Full stack development training visual" loading="lazy" decoding="async">
                    <img src="uploads/media/programming-languages-20260703-133210-630417.png" alt="Programming languages training visual" loading="lazy" decoding="async">
                    <img src="uploads/media/digital-marketing-20260703-133146-981935.png" alt="Digital marketing training visual" loading="lazy" decoding="async">
                </div>
            </div>
        </section>
        <section class="section alt">
            <div class="site-container">
                <div class="section-head reveal">
                    <h2>Why Students Choose Us</h2>
                    <p>Talentteno is designed for students who need useful skills, visible project work and structured career support.</p>
                </div>
            </div>
            <div class="site-container feature-grid">
                <?php foreach ($services as $service): ?>
                <div class="feature-card reveal"><i class="fa-solid <?= tt_h($service['icon']) ?>"></i><h3><?= tt_h($service['title']) ?></h3><p><?= tt_h($service['short_desc'] ?: $service['description']) ?></p></div>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="section">
            <div class="site-container section-head reveal">
                <span class="section-label">Institute process</span>
                <h2>From counselling to career support</h2>
                <p>A clear path from counselling and course selection to practical training and career support.</p>
            </div>
            <div class="site-container timeline">
                <?php foreach ($steps as $step): ?>
                <div class="timeline-card reveal"><h3><?= tt_h($step['title']) ?></h3><p><?= tt_h($step['description']) ?></p></div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260721-navbarfix1" defer></script>
</body>
</html>
