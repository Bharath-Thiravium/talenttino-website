<?php
require_once __DIR__ . '/includes/site-data.php';

$items = tt_careers();
$items = $items ?: [
    ['icon' => 'fa-user-tie', 'title' => 'Placement Preparation', 'short_desc' => 'Resume, mock interview and job-readiness guidance.', 'description' => 'Practice interview questions, resume structure, LinkedIn profile improvement and job-readiness steps.', 'image' => 'assets/images/contact-counsellor-hero.png'],
    ['icon' => 'fa-briefcase', 'title' => 'Internship to Career Path', 'short_desc' => 'Build confidence through real project practice.', 'description' => 'Move from training to internship work with guided tasks, project reviews and portfolio preparation.', 'image' => 'assets/images/home1.webp'],
    ['icon' => 'fa-handshake', 'title' => 'Hiring Support', 'short_desc' => 'Get guided towards suitable IT career opportunities.', 'description' => 'Get counselling for suitable roles, interview readiness and placement follow-up support.', 'image' => 'assets/images/home2.jpeg'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo(['title' => 'Career Support | Talentteno Institute', 'description' => 'Career support, internship guidance, interview preparation and placement assistance at Talentteno Institute.', 'canonical' => tt_abs_url('career.php')]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.css?v=20260715-04">
</head>
<body class="static-site career-page">
<div class="site-shell">
    <header class="site-header"><div class="site-container nav-wrap"><a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="132" height="62" decoding="async" fetchpriority="high"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a><nav class="site-nav">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <div class="nav-item has-menu"><a href="course.php">Course <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="shorttermcourse.php">Short Term Course</a><a href="popularcourse.php">Popular Course</a><a href="advancecourse.php">Advance Course</a></div></div>
                <a href="gallery.php">Gallery</a>
                <a href="contact.php">Contact</a>
                <div class="nav-item has-menu more-menu"><a href="#">More <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="services.php">Services</a><a href="career.php">Career</a><a href="blog.php">Blog</a><a href="project.php">Project</a></div></div>
            </nav><button class="menu-button" type="button" aria-label="Open menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button></div></header>
    <main class="page-main">
        <section class="page-hero"><div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-briefcase"></i> Career Growth</span><h1>Career Support</h1><p>Build interview confidence with resume support, internship guidance, practical projects and placement preparation.</p></div></section>
        <section class="section"><div class="site-container detail-grid rich-detail-grid"><?php foreach ($items as $item): ?><?php $image = tt_item_image($item, 'career'); ?><article class="detail-tile rich-detail-card reveal"><div class="rich-detail-image"><img src="<?= tt_h($image) ?>" alt="<?= tt_h($item['title'] ?? 'Talentteno career support') ?>" loading="lazy" decoding="async"></div><div class="rich-detail-body"><i class="fa-solid <?= tt_h($item['icon'] ?? 'fa-briefcase') ?>"></i><h3><?= tt_h($item['title']) ?></h3><p class="rich-detail-short"><?= tt_h(($item['short_desc'] ?? '') ?: ($item['description'] ?? '')) ?></p><?php if (!empty($item['description']) && ($item['description'] !== ($item['short_desc'] ?? ''))): ?><p class="rich-detail-more"><?= tt_h($item['description']) ?></p><?php endif; ?><a class="rich-detail-link" href="contact.php?topic=<?= rawurlencode($item['title'] ?? 'career') ?>">More Details <i class="fa-solid fa-arrow-right"></i></a></div></article><?php endforeach; ?></div></section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div><script src="assets/js/site-pages.js?v=20260715-04" defer></script></body></html>
