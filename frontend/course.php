<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$courses = tt_courses_by_type('course');
if (!$courses) {
    $courses = [
        ['id' => 0, 'title' => 'Programming Languages', 'category' => 'Programming', 'description' => 'Learn C, C++, Java, Python, PHP, .NET and SQL with practical coding.', 'highlights' => "Live Coding\nProjects\nInterview Training", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Full Stack Development', 'category' => 'Development', 'description' => 'HTML, CSS, JavaScript, Bootstrap, React, Django, Node.js and MySQL.', 'highlights' => "Live Website\nInternship\nPlacement Support", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Digital Marketing', 'category' => 'Marketing', 'description' => 'SEO, Google Ads, Social Media Marketing, Email Marketing and Analytics.', 'highlights' => "Google Ads\nSEO\nLive Campaigns", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Data Analyst', 'category' => 'Analytics', 'description' => 'Excel, SQL, Power BI, Tableau and Python for business analytics.', 'highlights' => "Dashboards\nReports\nCase Studies", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Data Science & AI', 'category' => 'AI', 'description' => 'Machine Learning, Artificial Intelligence and Deep Learning.', 'highlights' => "Python\nMachine Learning\nAI Projects", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Cyber Security', 'category' => 'Security', 'description' => 'Ethical Hacking, Networking, Penetration Testing and Security Tools.', 'highlights' => "Kali Linux\nLive Labs\nCertification", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Cloud Computing', 'category' => 'Cloud', 'description' => 'Learn AWS, Microsoft Azure, DevOps, Docker and Kubernetes.', 'highlights' => "AWS\nDocker\nKubernetes", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'IT Courses in Madurai | Full Stack, Data Science, AI, Cyber Security',
        'description' => 'Explore Talentteno IT courses in Madurai including Full Stack Development, Data Science, AI, Cyber Security, Digital Marketing, UI/UX, Tally and programming with internship and placement support.',
        'canonical' => tt_abs_url('course.php'),
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Courses', 'url' => 'course.php'],
        ],
        'courses' => array_map(static fn(array $course): array => [
            'name' => $course['title'] ?? '',
            'desc' => $course['description'] ?? '',
        ], $courses),
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@1,700;1,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260718-footerhover1">
    <style>
        body.course-list-page .course-showcase-card{position:relative!important;display:flex!important;flex-direction:column!important;aspect-ratio:auto!important;min-height:460px!important;padding:0 22px 82px!important;overflow:hidden!important;cursor:pointer!important}
        body.course-list-page .course-showcase-card .course-image{width:calc(100% + 44px)!important;height:190px!important;min-height:190px!important;max-height:190px!important;margin:0 -22px 18px!important;border-radius:8px 8px 0 0!important;overflow:hidden!important}
        body.course-list-page .course-showcase-card .course-image img{width:100%!important;height:100%!important;object-fit:cover!important;object-position:center!important}
        body.course-list-page .course-showcase-card .course-card-top{position:static!important;margin:0 0 18px!important;opacity:1!important;visibility:visible!important}
        body.course-list-page .course-showcase-card h3{position:static!important;display:block!important;visibility:visible!important;opacity:1!important;margin:0 0 6px!important;color:#10172a!important;-webkit-text-fill-color:#10172a!important;background:none!important;-webkit-background-clip:border-box!important;background-clip:border-box!important;font-size:25px!important;font-weight:900!important;line-height:1.12!important;letter-spacing:0!important}
        body.course-list-page .course-showcase-card>p{position:static!important;visibility:visible!important;opacity:1!important;margin:0 0 12px!important;display:-webkit-box!important;-webkit-line-clamp:2!important;-webkit-box-orient:vertical!important;overflow:hidden!important;color:#52627a!important;-webkit-text-fill-color:#52627a!important;font-size:14px!important;line-height:1.45!important}
        body.course-list-page .course-showcase-card .course-highlights{display:none!important}
        body.course-list-page .course-showcase-card .course-footer{position:absolute!important;left:22px!important;right:22px!important;bottom:22px!important;display:block!important;margin:0!important;padding-top:12px!important;border-top:1px solid rgba(37,99,235,.13)!important;background:#fff!important}
        body.course-list-page .course-showcase-card .course-actions{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;width:100%!important}
        body.course-list-page .course-showcase-card .course-actions .btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:7px!important;width:100%!important;min-height:42px!important;height:42px!important;padding:0 12px!important;border-radius:8px!important;clip-path:none!important;font-size:13px!important;font-weight:900!important;text-decoration:none!important}
        body.course-list-page .course-showcase-card .course-enquiry-btn{background:#fff!important;color:#1554d1!important;-webkit-text-fill-color:#1554d1!important;border:1px solid rgba(79,140,255,.42)!important;box-shadow:none!important}
        body.course-list-page .course-showcase-card .course-download-btn{color:#fff!important;-webkit-text-fill-color:#fff!important;background:linear-gradient(135deg,#4f8cff 0%,#7c5cff 48%,#d91cf6 100%)!important;border:0!important}
        @media (max-width:700px){
            body.course-list-page .course-showcase-grid{width:min(100% - 28px,420px)!important;margin:0 auto!important;grid-template-columns:1fr!important;gap:24px!important}
            body.course-list-page .course-showcase-card{min-height:auto!important;padding:0!important;border-radius:18px!important;overflow:hidden!important;box-shadow:0 22px 58px rgba(15,23,42,.10)!important}
            body.course-list-page .course-showcase-card .course-image{width:100%!important;height:210px!important;min-height:210px!important;max-height:210px!important;margin:0!important;border-radius:0!important}
            body.course-list-page .course-showcase-card .course-card-top{padding:24px 22px 0!important;margin:0!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important}
            body.course-list-page .course-showcase-card h3{margin:26px 22px 14px!important;font-size:clamp(23px,6vw,28px)!important;line-height:1.18!important}
            body.course-list-page .course-showcase-card>p{display:block!important;margin:0 22px 24px!important;overflow:visible!important;font-size:14.5px!important;line-height:1.65!important}
            body.course-list-page .course-showcase-card .course-footer{position:static!important;left:auto!important;right:auto!important;bottom:auto!important;margin:0!important;padding:16px 22px 22px!important;display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;border-top:1px solid rgba(79,140,255,.14)!important}
            body.course-list-page .course-showcase-card .course-actions{display:contents!important}
            body.course-list-page .course-showcase-card .course-actions .btn{min-height:46px!important;height:46px!important;border-radius:10px!important;gap:0!important}
            body.course-list-page .course-showcase-card .course-actions i{display:none!important}
        }
        body.course-list-page .course-showcase-card .course-footer{
            left:22px!important;
            right:22px!important;
            bottom:22px!important;
            width:auto!important;
            max-width:none!important;
            overflow:visible!important;
        }
        body.course-list-page .course-showcase-card .course-actions{
            width:100%!important;
            display:grid!important;
            grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
            align-items:center!important;
            gap:10px!important;
        }
        body.course-list-page .course-showcase-card .course-actions .btn{
            width:100%!important;
            max-width:100%!important;
            min-width:0!important;
            height:42px!important;
            min-height:42px!important;
            padding:0 14px!important;
            display:inline-flex!important;
            align-items:center!important;
            justify-content:center!important;
            border-radius:10px!important;
            clip-path:none!important;
            transform:none!important;
            font-size:13px!important;
            font-weight:900!important;
            line-height:1!important;
            letter-spacing:0!important;
            text-align:center!important;
            white-space:nowrap!important;
            overflow:hidden!important;
            text-overflow:ellipsis!important;
        }
        body.course-list-page .course-showcase-card .course-actions .btn i{
            display:none!important;
        }
        body.course-list-page .course-showcase-card .course-enquiry-btn{
            background:#ffffff!important;
            color:#1554d1!important;
            -webkit-text-fill-color:#1554d1!important;
            border:1px solid rgba(79,140,255,.46)!important;
            box-shadow:inset 0 0 0 1px rgba(255,255,255,.78)!important;
        }
        body.course-list-page .course-showcase-card .course-download-btn{
            background:linear-gradient(135deg,#4f8cff 0%,#7c5cff 48%,#d91cf6 100%)!important;
            color:#ffffff!important;
            -webkit-text-fill-color:#ffffff!important;
            border:1px solid transparent!important;
            box-shadow:0 12px 24px rgba(124,92,255,.24)!important;
        }
        body.course-list-page .course-showcase-card .course-actions .btn:hover{
            transform:translateY(-2px)!important;
            box-shadow:0 16px 30px rgba(37,99,235,.16)!important;
        }
        @media (max-width:700px){
            body.course-list-page .course-showcase-card .course-footer{
                position:static!important;
                padding:16px 22px 22px!important;
                display:block!important;
            }
            body.course-list-page .course-showcase-card .course-actions{
                display:grid!important;
                grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
                gap:10px!important;
            }
            body.course-list-page .course-showcase-card .course-actions .btn{
                height:46px!important;
                min-height:46px!important;
                border-radius:10px!important;
                font-size:13px!important;
            }
            body.course-list-page .course-showcase-card .course-image{
                width:100%!important;
                height:220px!important;
                min-height:220px!important;
                max-height:220px!important;
                margin:0 0 22px!important;
                border-radius:0!important;
            }
        }
        body.course-list-page .course-showcase-card .course-image{
            background:#f4f8ff!important;
            display:block!important;
            width:calc(100% + 44px)!important;
            height:220px!important;
            min-height:220px!important;
            max-height:220px!important;
            margin:0 -22px 22px!important;
        }
        body.course-list-page .course-showcase-card .course-image img{
            display:block!important;
            width:100%!important;
            height:100%!important;
            object-fit:cover!important;
            object-position:center!important;
            background:transparent!important;
        }
        body.course-list-page .course-showcase-card:hover .course-image img{
            transform:none!important;
            filter:saturate(1.03) contrast(1.02)!important;
        }
        body.course-list-page .course-page-hero{
            position:relative!important;
            min-height:460px!important;
            padding:0!important;
            display:flex!important;
            align-items:center!important;
            overflow:hidden!important;
            isolation:isolate!important;
            background:#061631!important;
            text-align:left!important;
        }
        body.course-list-page .course-page-hero .course-hero-bg{
            position:absolute!important;
            inset:0!important;
            z-index:0!important;
            width:100%!important;
            height:100%!important;
            object-fit:cover!important;
            object-position:center right!important;
            opacity:1!important;
            filter:saturate(1.06) contrast(1.04)!important;
            pointer-events:none!important;
        }
        body.course-list-page .course-page-hero .course-hero-overlay{
            position:absolute!important;
            inset:0!important;
            z-index:1!important;
            background:linear-gradient(90deg,rgba(5,15,38,.96) 0%,rgba(8,29,74,.86) 42%,rgba(8,42,112,.24) 72%,rgba(5,15,38,.08) 100%)!important;
            pointer-events:none!important;
        }
        body.course-list-page .course-page-hero .site-container{
            position:relative!important;
            z-index:3!important;
            width:min(1200px,calc(100% - 48px))!important;
            margin:0 auto!important;
        }
        body.course-list-page .course-page-hero h1{
            max-width:620px!important;
            color:#fff!important;
            -webkit-text-fill-color:#fff!important;
            font-size:clamp(48px,5.8vw,82px)!important;
            line-height:.98!important;
            text-align:left!important;
        }
        body.course-list-page .course-page-hero p{
            max-width:680px!important;
            margin-left:0!important;
            margin-right:0!important;
            color:rgba(255,255,255,.94)!important;
            -webkit-text-fill-color:rgba(255,255,255,.94)!important;
            font-size:23px!important;
            line-height:1.55!important;
            text-align:left!important;
        }
        @media (max-width:900px){
            body.course-list-page .course-page-hero{
                min-height:430px!important;
            }
            body.course-list-page .course-page-hero .course-hero-bg{
                object-position:62% center!important;
            }
            body.course-list-page .course-page-hero .course-hero-overlay{
                background:linear-gradient(180deg,rgba(5,15,38,.96) 0%,rgba(8,29,74,.86) 54%,rgba(8,42,112,.52) 100%)!important;
            }
            body.course-list-page .course-page-hero h1{
                font-size:clamp(42px,10vw,64px)!important;
            }
            body.course-list-page .course-page-hero p{
                font-size:18px!important;
            }
        }
    </style>
</head>
<body class="static-site course-list-page">
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
        <section class="page-hero course-page-hero">
            <img class="course-hero-bg" src="assets/images/our trending.png" alt="" aria-hidden="true" decoding="async" fetchpriority="high">
            <span class="course-hero-overlay" aria-hidden="true"></span>
            <div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-book-open"></i> Basic to Advanced IT Training</span><h1>Our Trending Courses</h1><p>Build your career with industry-oriented training, live projects, internships, certifications, and placement assistance.</p></div>
        </section>
        <section class="section course-showcase-section">
            <div class="site-container course-showcase-grid">
                <?php foreach ($courses as $course): ?>
                <?php
                    $courseImage = tt_course_image_url($course['image'] ?? '');
                    $hasBrochure = tt_course_brochure_exists($course['brochure_file'] ?? '');
                    $downloadHref = $hasBrochure && !empty($course['id'])
                        ? 'download.php?id=' . (int)$course['id']
                        : 'download.php?title=' . rawurlencode($course['title']);
                    $enquiryHref = 'contact.php?course=' . rawurlencode($course['title']);
                    $courseFee = (float)($course['fee'] ?? 0) > 0 ? tt_money($course['fee']) : '';
                    $highlights = tt_course_highlights($course);
                ?>
                <article class="course-card course-showcase-card <?= $course['is_featured'] ? 'featured-course' : '' ?> <?= $courseImage !== '' ? 'has-course-image' : '' ?> reveal"
                    role="button"
                    tabindex="0"
                    aria-label="View <?= tt_h($course['title']) ?> course details"
                    data-course-modal
                    data-title="<?= tt_h($course['title']) ?>"
                    data-category="<?= tt_h($course['category']) ?>"
                    data-description="<?= tt_h($course['description']) ?>"
                    data-duration="<?= tt_h($course['duration']) ?>"
                    data-fee="<?= tt_h($courseFee) ?>"
                    data-highlights="<?= tt_h(implode("\n", $highlights)) ?>"
                    data-download="<?= tt_h($downloadHref) ?>"
                    data-enquire="<?= tt_h($enquiryHref) ?>"
                    data-image="<?= tt_h($courseImage) ?>">
                    <?php if ($courseImage !== ''): ?>
                    <div class="course-image">
                        <img src="<?= tt_h($courseImage) ?>" alt="<?= tt_h($course['title']) ?>" loading="lazy" decoding="async">
                    </div>
                    <?php endif; ?>
                    <div class="course-card-top">
                        <div class="course-icon"><i class="fa-solid <?= tt_h(tt_course_icon($course['category'])) ?>"></i></div>
                        <span class="course-pill"><?= tt_h($course['category']) ?></span>
                    </div>
                    <h3><?= tt_h($course['title']) ?></h3>
                    <p><?= tt_h($course['description']) ?></p>
                    <ul class="course-highlights">
                        <?php foreach ($highlights as $highlight): ?><li><i class="fa-solid fa-check"></i> <?= tt_h($highlight) ?></li><?php endforeach; ?>
                    </ul>
                    <div class="course-footer">
                        <div class="course-actions">
                            <a class="btn btn-secondary course-enquiry-btn" href="<?= tt_h($enquiryHref) ?>"><i class="fa-solid fa-message"></i> Enquiry</a>
                            <a class="btn btn-primary course-download-btn" href="<?= tt_h($downloadHref) ?>"><i class="fa-solid fa-download"></i> Download</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <div class="course-detail-modal" id="courseDetailModal" aria-hidden="true">
        <div class="course-detail-backdrop" data-close-course-detail></div>
        <div class="course-detail-panel" role="dialog" aria-modal="true" aria-labelledby="courseDetailTitle">
            <button class="course-detail-close" type="button" data-close-course-detail aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            <div class="course-detail-image" hidden><img src="" alt="" loading="lazy" decoding="async"></div>
            <span class="course-detail-category"></span>
            <h2 id="courseDetailTitle"></h2>
            <p class="course-detail-description"></p>
            <ul class="course-detail-highlights"></ul>
            <div class="course-detail-meta"><span class="course-detail-duration"></span><strong class="course-detail-fee"></strong></div>
            <div class="course-detail-actions"><a class="btn btn-secondary course-detail-enquire" href="contact.php"><i class="fa-solid fa-message"></i> Enquire Now</a><a class="btn btn-primary course-detail-download" href="contact.php"><i class="fa-solid fa-download"></i> Download Brochure</a></div>
        </div>
    </div>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260718-scrollsmooth1" defer></script>
</body>
</html>
