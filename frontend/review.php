<?php
require_once __DIR__ . '/includes/site-data.php';

$reviews = tt_testimonials(9);
$reviews = $reviews ?: [
    ['student_name' => 'Talentteno Student', 'course' => 'Full Stack Development', 'review' => 'The classes were practical and the project tasks helped me understand real development flow.', 'rating' => 5],
    ['student_name' => 'Career Switcher', 'course' => 'Data Science', 'review' => 'Mentor guidance, assignments and interview support made the learning path clear.', 'rating' => 5],
    ['student_name' => 'Final Year Student', 'course' => 'Cyber Security', 'review' => 'The lab practice and career support helped me build confidence for interviews.', 'rating' => 5],
];
$images = [
    tt_optimized_image_url('assets/images/home.webp', 800),
    tt_optimized_image_url('assets/images/home1.webp', 800),
    tt_optimized_image_url('assets/images/home2.webp', 800),
    tt_optimized_image_url('assets/images/contact-counsellor-hero.png', 800),
];
$reviewHeroImage = tt_optimized_image_url('assets/images/review.png', 1536);
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
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260727-headermedia1">
</head>
<body class="static-site review-page">
<div class="site-shell">
        <?php require_once __DIR__ . '/includes/header.php'; ?>
    <main class="page-main">
        <section class="page-hero has-page-hero-image"><img class="page-hero-bg" src="<?= tt_h($reviewHeroImage) ?>" alt="" aria-hidden="true" decoding="async" fetchpriority="high"><span class="page-hero-overlay" aria-hidden="true"></span><div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-star"></i> Student Reviews</span><h1>What learners say about Talentteno</h1><p>Real student feedback about practical training, project work, mentor support and career guidance.</p></div></section>
        <section class="section"><div class="site-container detail-grid rich-detail-grid">
            <?php foreach ($reviews as $index => $review): ?>
            <?php $reviewImage = tt_optimized_image_url($review['image'] ?? '', 800) ?: $images[$index % count($images)]; ?>
            <article class="detail-tile rich-detail-card review-detail-card reveal">
                <div class="rich-detail-image"><img src="<?= tt_h($reviewImage) ?>" alt="<?= tt_h($review['student_name'] ?? 'Talentteno student') ?> review" loading="lazy" decoding="async"></div>
                <div class="rich-detail-body"><i class="fa-solid fa-star"></i><h3><?= tt_h($review['student_name'] ?? 'Talentteno Student') ?></h3><p class="rich-detail-short"><?= tt_h($review['course'] ?? 'Talentteno Training') ?></p><p class="rich-detail-more"><?= tt_h($review['review'] ?? '') ?></p><a class="rich-detail-link" href="contact.php?topic=student%20review">Join Like Them <i class="fa-solid fa-arrow-right"></i></a></div>
            </article>
            <?php endforeach; ?>
        </div></section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div><script src="assets/js/site-pages.min.js?v=20260727-headermedia1" defer></script></body></html>
