<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$services = tt_services();
$fallbackServices = [
    ['icon' => 'fa-laptop-code', 'title' => 'IT Skill Training', 'short_desc' => 'Basic to advanced hands-on IT training.', 'description' => 'Practical training across development, data, security, marketing and design tracks.', 'image' => 'assets/images/home.webp'],
    ['icon' => 'fa-id-badge', 'title' => 'Free Internship Program', 'short_desc' => 'Build real project exposure after training.', 'description' => 'Guided internship support helps students turn learning into portfolio-ready work.', 'image' => 'assets/images/home1.webp'],
    ['icon' => 'fa-briefcase', 'title' => 'Placement Assistance', 'short_desc' => 'Resume, interview and hiring support.', 'description' => 'Career preparation, mock interviews and placement guidance for job-ready learners.', 'image' => 'assets/images/contact-counsellor-hero.png'],
];
$services = $services ?: $fallbackServices;
$servicesHeroImage = tt_optimized_image_url('assets/images/services .png', 1536);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'Services | Talentteno Institute Madurai',
        'description' => 'Explore Talentteno services including IT training, internship support, placement assistance, soft skills and corporate training.',
        'canonical' => tt_abs_url('services.php'),
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Services', 'url' => 'services.php'],
        ],
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <?php $ttPageCss = tt_asset_url('assets/css/site-pages.min.css'); ?>
    <link rel="stylesheet" href="<?= tt_h($ttPageCss) ?>">
    <noscript><link rel="stylesheet" href="<?= tt_h($ttPageCss) ?>"></noscript>
    <link rel="stylesheet" href="<?= tt_h(tt_asset_url('assets/css/navbar.min.css')) ?>">
</head>
<body class="static-site services-page">
<div class="site-shell">
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    <main class="page-main">
        <section class="page-hero has-page-hero-image">
            <img class="page-hero-bg" src="<?= tt_h($servicesHeroImage) ?>" alt="" aria-hidden="true" decoding="async" fetchpriority="high">
            <span class="page-hero-overlay" aria-hidden="true"></span>
            <div class="site-container reveal">
                <span class="hero-kicker"><i class="fa-solid fa-concierge-bell"></i> Training Support</span>
                <h1>Services</h1>
                <p>Practical learning support, internship guidance, placement preparation and career-focused training services.</p>
            </div>
        </section>
        <section class="section">
            <div class="site-container detail-grid rich-detail-grid">
                <?php foreach ($services as $service): ?>
                <?php $image = tt_item_image($service, 'service'); ?>
                <article class="detail-tile rich-detail-card reveal">
                    <div class="rich-detail-image"><img src="<?= tt_h($image) ?>" alt="<?= tt_h($service['title'] ?? 'Talentteno service') ?>" loading="lazy" decoding="async"></div>
                    <div class="rich-detail-body">
                        <i class="fa-solid <?= tt_h($service['icon'] ?? 'fa-laptop-code') ?>"></i>
                        <h3><?= tt_h($service['title'] ?? '') ?></h3>
                        <p class="rich-detail-short"><?= tt_h(($service['short_desc'] ?? '') ?: ($service['description'] ?? '')) ?></p>
                        <?php if (!empty($service['description']) && ($service['description'] !== ($service['short_desc'] ?? ''))): ?>
                        <p class="rich-detail-more"><?= tt_h($service['description']) ?></p>
                        <?php endif; ?>
                        <button
                            type="button"
                            class="rich-detail-link"
                            data-smd-trigger
                            data-smd-title="<?= tt_h($service['title'] ?? '') ?>"
                            data-smd-category="<?= tt_h(($service['icon'] ?? '') ? 'Service' : 'Service') ?>"
                            data-smd-description="<?= tt_h(($service['description'] ?? '') ?: ($service['short_desc'] ?? '')) ?>"
                            data-smd-image="<?= tt_h($image) ?>"
                            data-smd-features="<?= tt_h(($service['short_desc'] ?? '') !== ($service['description'] ?? '') ? ($service['short_desc'] ?? '') . "\n" . ($service['description'] ?? '') : ($service['short_desc'] ?? '')) ?>"
                            data-smd-enquire="contact.php?topic=<?= rawurlencode($service['title'] ?? 'service') ?>">
                            More Details <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
<script src="<?= tt_h(tt_asset_url('assets/js/site-pages.min.js')) ?>" defer></script>
</body>
</html>
