<?php
require_once __DIR__ . '/includes/site-data.php';

$reviews = tt_testimonials(9);
$reviews = $reviews ?: [
    ['student_name' => 'Talentteno Student', 'course' => 'Full Stack Development', 'review' => 'The classes were practical and the project tasks helped me understand real development flow.', 'rating' => 5],
    ['student_name' => 'Career Switcher', 'course' => 'Data Science', 'review' => 'Mentor guidance, assignments and interview support made the learning path clear.', 'rating' => 5],
    ['student_name' => 'Final Year Student', 'course' => 'Cyber Security', 'review' => 'The lab practice and career support helped me build confidence for interviews.', 'rating' => 5],
];
$images = ['assets/images/home.png', 'assets/images/home1.png', 'assets/images/home2.png', 'assets/images/contact-counsellor-hero.png'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo(['title' => 'Student Reviews | Talentteno Institute', 'description' => 'Student reviews and learning experience at Talentteno Institute Madurai.', 'canonical' => tt_abs_url('review.php')]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.css?v=20260716-imagefix1">
</head>
<body class="static-site review-page">
<div class="site-shell">
    <header class="site-header"><div class="site-container nav-wrap"><a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="132" height="62" decoding="async" fetchpriority="high"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a><nav class="site-nav">
        <a href="index.php">Home</a><a href="about.php">About</a><div class="nav-item has-menu"><a href="course.php">Course <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="shorttermcourse.php">Short Term Course</a><a href="popularcourse.php">Popular Course</a><a href="advancecourse.php">Advance Course</a></div></div><a href="gallery.php">Gallery</a><a href="contact.php">Contact</a><div class="nav-item has-menu more-menu"><a href="#">More <i class="fa-solid fa-chevron-down"></i></a><div class="nav-menu"><a href="services.php">Services</a><a href="career.php">Career</a><a href="blog.php">Blog</a><a href="project.php">Project</a></div></div>
    </nav><button class="menu-button" type="button" aria-label="Open menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button></div></header>
    <main class="page-main">
        <section class="page-hero"><div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-star"></i> Student Reviews</span><h1>What learners say about Talentteno</h1><p>Real student feedback about practical training, project work, mentor support and career guidance.</p></div></section>
        <section class="section"><div class="site-container detail-grid rich-detail-grid">
            <?php foreach ($reviews as $index => $review): ?>
            <article class="detail-tile rich-detail-card review-detail-card reveal">
                <div class="rich-detail-image"><img src="<?= tt_h($images[$index % count($images)]) ?>" alt="<?= tt_h($review['student_name'] ?? 'Talentteno student') ?> review" loading="lazy" decoding="async"></div>
                <div class="rich-detail-body"><i class="fa-solid fa-star"></i><h3><?= tt_h($review['student_name'] ?? 'Talentteno Student') ?></h3><p class="rich-detail-short"><?= tt_h($review['course'] ?? 'Talentteno Training') ?></p><p class="rich-detail-more"><?= tt_h($review['review'] ?? '') ?></p><a class="rich-detail-link" href="contact.php?topic=student%20review">Join Like Them <i class="fa-solid fa-arrow-right"></i></a></div>
            </article>
            <?php endforeach; ?>
        </div></section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div><script src="assets/js/site-pages.js?v=20260716-menutap1" defer></script></body></html>
