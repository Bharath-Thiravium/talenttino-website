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
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260723-edgefix5">
    <style>
        body.about-page .about-intro-section .site-container.about-intro {
            width: min(100% - 96px, 1360px) !important;
            max-width: 1360px !important;
            grid-template-columns: minmax(0, 560px) minmax(680px, 1fr) !important;
            column-gap: clamp(44px, 4vw, 64px) !important;
            align-items: center !important;
            margin-inline: auto !important;
        }

        body.about-page .about-story {
            max-width: 620px !important;
            justify-self: start !important;
        }

        body.about-page .about-story h2,
        body.about-page .about-story .about-lead {
            max-width: 100% !important;
        }

        body.about-page .tt-stat-row {
            grid-column: 1 / -1 !important;
            width: 100% !important;
            max-width: 100% !important;
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: clamp(12px, 1.35vw, 20px) !important;
            margin: clamp(8px, 1.2vw, 16px) 0 0 !important;
            align-items: stretch !important;
        }

        body.about-page .tt-stat-card {
            min-width: 0 !important;
            min-height: 166px !important;
            padding: clamp(22px, 1.7vw, 28px) clamp(14px, 1.6vw, 22px) !important;
            display: grid !important;
            place-items: center !important;
            align-content: center !important;
            gap: 10px !important;
            text-align: center !important;
            border-radius: 18px !important;
            overflow: hidden !important;
            isolation: isolate !important;
            background: radial-gradient(circle at 20% 0%, rgba(37, 99, 235, 0.13), transparent 34%), linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 251, 255, 0.98) 100%) !important;
            box-shadow: 0 22px 56px rgba(30, 41, 59, 0.10) !important;
            animation: aboutStatRiseInline 720ms cubic-bezier(0.16, 1, 0.3, 1) both !important;
        }

        body.about-page .tt-stat-card::after {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            z-index: -1 !important;
            background: linear-gradient(110deg, transparent 0%, rgba(255, 255, 255, 0.72) 45%, transparent 70%) !important;
            transform: translateX(-120%) !important;
            animation: aboutStatSheenInline 4.8s ease-in-out infinite !important;
        }

        body.about-page .tt-stat-card:nth-child(2) { animation-delay: 90ms !important; }
        body.about-page .tt-stat-card:nth-child(3) { animation-delay: 180ms !important; }
        body.about-page .tt-stat-card:nth-child(4) { animation-delay: 270ms !important; }

        body.about-page .tt-stat-card:hover {
            transform: translateY(-8px) !important;
            box-shadow: 0 30px 72px rgba(30, 41, 59, 0.16) !important;
        }

        body.about-page .tt-stat-card::before {
            margin: 0 0 4px !important;
            animation: aboutStatIconInline 2.8s ease-in-out infinite !important;
        }

        body.about-page .tt-stat-card:nth-child(3)::before { content: "\f19d" !important; }
        body.about-page .tt-stat-card:nth-child(4)::before { content: "\f0b1" !important; }
        body.about-page .tt-stat-num { font-size: clamp(40px, 3vw, 54px) !important; line-height: 0.94 !important; }
        body.about-page .tt-stat-title { font-size: clamp(15px, 1vw, 18px) !important; line-height: 1.25 !important; }

        body.about-page .about-visual-main,
        body.about-page .about-visual-mini {
            background: #ffffff !important;
            overflow: hidden !important;
        }

        body.about-page .about-visual-stack {
            position: relative !important;
            width: min(100%, 740px) !important;
            min-height: 0 !important;
            margin: 0 !important;
            justify-self: end !important;
            overflow: hidden !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        body.about-page .about-visual-main {
            position: relative !important;
            inset: auto !important;
            z-index: 2 !important;
            width: 100% !important;
            aspect-ratio: 3 / 2 !important;
            border: 7px solid #ffffff !important;
            border-radius: 22px !important;
            box-shadow: 0 30px 76px rgba(15, 23, 42, 0.16) !important;
        }

        body.about-page .about-visual-mini-one {
            display: none !important;
        }

        body.about-page .about-visual-mini-two {
            display: none !important;
        }

        body.about-page .about-visual-main::after {
            display: none !important;
            content: none !important;
        }

        body.about-page .about-visual-main img,
        body.about-page .about-visual-mini img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center center !important;
            background: #ffffff !important;
        }

        body.about-page .about-visual-badge {
            top: calc(100% + 14px) !important;
            bottom: auto !important;
            left: 20px !important;
            z-index: 8 !important;
        }

        @media (max-width: 900px) {
            body.about-page .about-intro-section .site-container.about-intro {
                width: min(100% - 28px, 640px) !important;
                grid-template-columns: 1fr !important;
                row-gap: 28px !important;
            }

            body.about-page .about-story,
            body.about-page .about-visual-stack {
                justify-self: center !important;
                width: 100% !important;
            }

            body.about-page .about-visual-stack {
                min-height: 0 !important;
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 14px !important;
            }

            body.about-page .about-visual-main,
            body.about-page .about-visual-mini {
                position: relative !important;
                inset: auto !important;
                width: 100% !important;
                aspect-ratio: auto !important;
            }

            body.about-page .about-visual-main {
                grid-column: 1 / -1 !important;
                aspect-ratio: 3 / 2 !important;
            }

            body.about-page .about-visual-mini {
                aspect-ratio: 16 / 10 !important;
            }

            body.about-page .about-visual-badge {
                position: static !important;
                grid-column: 1 / -1 !important;
                width: fit-content !important;
                max-width: 100% !important;
            }
        }

        @keyframes aboutStatRiseInline {
            from { opacity: 0; transform: translate3d(0, 26px, 0) scale(0.96); }
            to { opacity: 1; transform: translate3d(0, 0, 0) scale(1); }
        }

        @keyframes aboutStatSheenInline {
            0%, 48% { transform: translateX(-120%); }
            68%, 100% { transform: translateX(120%); }
        }

        @keyframes aboutStatIconInline {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-3px) scale(1.05); }
        }

        @media (max-width: 980px) {
            body.about-page .tt-stat-row {
                display: flex !important;
                grid-template-columns: none !important;
                overflow-x: auto !important;
                overflow-y: hidden !important;
                scroll-snap-type: x proximity !important;
                -webkit-overflow-scrolling: touch !important;
                padding: 2px 2px 14px !important;
            }

            body.about-page .tt-stat-card {
                flex: 0 0 min(74vw, 320px) !important;
                scroll-snap-align: start !important;
            }
        }

        @media (max-width: 520px) {
            body.about-page .tt-stat-card {
                flex-basis: min(82vw, 300px) !important;
                min-height: 154px !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            body.about-page .tt-stat-card,
            body.about-page .tt-stat-card::after,
            body.about-page .tt-stat-card::before {
                animation: none !important;
            }
        }
    </style>
</head>
<body class="static-site about-page">
<div class="site-shell">
    <header class="site-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png?v=20260722-logo2" alt="Talentteno Institute logo" width="68" height="68" decoding="async"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a>
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
                </div>
                <div class="identity-image about-visual-stack reveal reveal-right">
                    <div class="about-visual-main">
                        <img src="assets/images/About .png" alt="Talentteno students learning with mentor" loading="lazy" decoding="async" width="1536" height="1024" style="width:100%!important;height:100%!important;display:block!important;object-fit:cover!important;object-position:center center!important;background:#fff!important;border-radius:15px!important;">
                    </div>
                </div>
                <div class="tt-stat-row reveal" style="grid-column:1/-1!important;width:100%!important;max-width:100%!important;display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:16px!important;margin:12px 0 0!important;align-items:stretch!important;">
                    <div class="tt-stat-card" style="min-width:0!important;min-height:156px!important;padding:22px 14px!important;display:grid!important;place-items:center!important;align-content:center!important;text-align:center!important;">
                        <span class="tt-stat-num"><?= tt_h($settings['total_students']) ?></span>
                        <span class="tt-stat-title">Students trained</span>
                    </div>
                    <div class="tt-stat-card" style="min-width:0!important;min-height:156px!important;padding:22px 14px!important;display:grid!important;place-items:center!important;align-content:center!important;text-align:center!important;">
                        <span class="tt-stat-num"><?= tt_h($settings['total_trainers']) ?></span>
                        <span class="tt-stat-title">Expert trainers</span>
                    </div>
                    <div class="tt-stat-card" style="min-width:0!important;min-height:156px!important;padding:22px 14px!important;display:grid!important;place-items:center!important;align-content:center!important;text-align:center!important;">
                        <span class="tt-stat-num">20+</span>
                        <span class="tt-stat-title">Career courses</span>
                    </div>
                    <div class="tt-stat-card" style="min-width:0!important;min-height:156px!important;padding:22px 14px!important;display:grid!important;place-items:center!important;align-content:center!important;text-align:center!important;">
                        <span class="tt-stat-num"><?= tt_h($settings['success_rate']) ?></span>
                        <span class="tt-stat-title">Career support</span>
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
<script src="assets/js/site-pages.min.js?v=20260722-counteranim1" defer></script>
</body>
</html>
