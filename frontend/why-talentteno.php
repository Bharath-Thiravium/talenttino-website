<?php
require_once __DIR__ . '/includes/site-data.php';

$items = tt_why_items();
$items = $items ?: [
    ['icon' => 'fa-laptop-code', 'title' => 'Practical IT Training', 'short_desc' => 'Learn by building tasks, labs and live projects.', 'description' => 'Training focuses on real workflow, not only theory, so students can explain and apply what they learn.', 'image' => 'assets/images/home.webp'],
    ['icon' => 'fa-user-tie', 'title' => 'Mentor Guidance', 'short_desc' => 'Get support from trainers during practice.', 'description' => 'Students receive structured guidance, doubt clarification, project review and career direction.', 'image' => 'assets/images/contact-counsellor-hero.png'],
    ['icon' => 'fa-briefcase', 'title' => 'Career Support', 'short_desc' => 'Resume, interview and placement preparation.', 'description' => 'The institute supports job readiness through portfolio projects, mock interviews and placement guidance.', 'image' => 'assets/images/home2.webp'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo(['title' => 'Why Talentteno | Talentteno Institute', 'description' => 'Why students choose Talentteno Institute for practical IT training in Madurai.', 'canonical' => tt_abs_url('why-talentteno.php')]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260727-aligncards1">
</head>
<body class="static-site why-page">
<div class="site-shell">
        <?php require_once __DIR__ . '/includes/header.php'; ?>
    <main class="page-main">
        <section class="page-hero"><div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-graduation-cap"></i> Why Talentteno</span><h1>Training built for student career growth</h1><p>Talentteno combines practical classes, projects, internship guidance and placement preparation for IT learners.</p></div></section>
        <section class="section"><div class="site-container detail-grid rich-detail-grid">
            <?php foreach ($items as $item): ?><?php $image = tt_item_image($item, 'why'); ?>
            <article class="detail-tile rich-detail-card reveal"><div class="rich-detail-image"><img src="<?= tt_h($image) ?>" alt="<?= tt_h($item['title']) ?>" loading="lazy" decoding="async"></div><div class="rich-detail-body"><i class="fa-solid <?= tt_h($item['icon']) ?>"></i><h3><?= tt_h($item['title']) ?></h3><p class="rich-detail-short"><?= tt_h($item['short_desc']) ?></p><p class="rich-detail-more"><?= tt_h($item['description']) ?></p><button type="button" class="rich-detail-link" data-smd-trigger data-smd-title="<?= tt_h($item['title']) ?>" data-smd-category="Why Talentteno" data-smd-description="<?= tt_h($item['description']) ?>" data-smd-image="<?= tt_h($image) ?>" data-smd-features="<?= tt_h($item['short_desc'] . "\n" . $item['description']) ?>" data-smd-enquire="contact.php?topic=Why%20Talentteno">More Details <i class="fa-solid fa-arrow-right"></i></button></div></article>
            <?php endforeach; ?>
        </div></section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div><script src="assets/js/site-pages.min.js?v=20260727-aligncards1" defer></script></body></html>
