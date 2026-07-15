<?php
require_once __DIR__ . '/includes/site-data.php';

$items = [
    ['icon' => 'fa-handshake', 'title' => 'Institute Partnership', 'short_desc' => 'Discuss Talentteno training centre partnership.', 'description' => 'Share your city, space and plan. Our team will explain the partnership workflow and next steps.', 'image' => 'assets/images/contact-counsellor-hero.png'],
    ['icon' => 'fa-chalkboard-teacher', 'title' => 'Training Model', 'short_desc' => 'Course structure, counselling and student support.', 'description' => 'Understand how practical course content, counselling, trainer coordination and student support are handled.', 'image' => 'assets/images/home.jpeg'],
    ['icon' => 'fa-bullhorn', 'title' => 'Brand Support', 'short_desc' => 'Guidance for local admissions and promotion.', 'description' => 'Get clarity on brand usage, enquiry handling, admission process and local centre operations.', 'image' => 'assets/images/home1.webp'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo(['title' => 'Franchise Enquiry | Talentteno Institute', 'description' => 'Talentteno Institute franchise and training centre partnership enquiry.', 'canonical' => tt_abs_url('franchise.php')]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.css?v=20260715-04">
</head>
<body class="static-site franchise-page">
<div class="site-shell">
    <header class="site-header"><div class="site-container nav-wrap"><a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="132" height="62" decoding="async" fetchpriority="high"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a><nav class="site-nav">
        <a href="index.php">Home</a><a href="about.php">About</a><div class="nav-item has-menu"><a href="course.php">Course <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="shorttermcourse.php">Short Term Course</a><a href="popularcourse.php">Popular Course</a><a href="advancecourse.php">Advance Course</a></div></div><a href="gallery.php">Gallery</a><a href="contact.php">Contact</a><div class="nav-item has-menu more-menu"><a href="#">More <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="services.php">Services</a><a href="career.php">Career</a><a href="blog.php">Blog</a><a href="project.php">Project</a></div></div>
    </nav><button class="menu-button" type="button" aria-label="Open menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button></div></header>
    <main class="page-main">
        <section class="page-hero"><div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-handshake"></i> Franchise Enquiry</span><h1>Partner with Talentteno Institute</h1><p>Start a practical IT training centre conversation with our team and understand the course, brand and counselling model.</p></div></section>
        <section class="section"><div class="site-container detail-grid rich-detail-grid">
            <?php foreach ($items as $item): ?><?php $image = tt_item_image($item, 'franchise'); ?>
            <article class="detail-tile rich-detail-card reveal"><div class="rich-detail-image"><img src="<?= tt_h($image) ?>" alt="<?= tt_h($item['title']) ?>" loading="lazy" decoding="async"></div><div class="rich-detail-body"><i class="fa-solid <?= tt_h($item['icon']) ?>"></i><h3><?= tt_h($item['title']) ?></h3><p class="rich-detail-short"><?= tt_h($item['short_desc']) ?></p><p class="rich-detail-more"><?= tt_h($item['description']) ?></p><a class="rich-detail-link" href="contact.php?topic=franchise">Enquire Now <i class="fa-solid fa-arrow-right"></i></a></div></article>
            <?php endforeach; ?>
        </div></section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div><script src="assets/js/site-pages.js?v=20260715-04" defer></script></body></html>
