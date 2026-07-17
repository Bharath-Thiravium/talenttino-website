<?php
require_once __DIR__ . '/includes/site-data.php';

$uploadedGalleryImages = [];
$galleryUploadDir = __DIR__ . '/uploads/gallery/';
$galleryMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
foreach (glob($galleryUploadDir . '*') ?: [] as $path) {
    if (!is_file($path)) {
        continue;
    }
    $mime = mime_content_type($path) ?: '';
    if (!in_array($mime, $galleryMimeTypes, true)) {
        continue;
    }
    $uploadedGalleryImages[] = [
        'src' => 'uploads/gallery/' . rawurlencode(basename($path)),
        'alt' => pathinfo($path, PATHINFO_FILENAME),
        'modified' => filemtime($path),
    ];
}
usort($uploadedGalleryImages, static fn($a, $b) => $b['modified'] <=> $a['modified']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'Talentteno Gallery | IT Training Institute Madurai',
        'description' => 'View Talentteno Institute posters, flyers, course visuals and training environment images for IT courses, projects, internship and career support in Madurai.',
        'canonical' => tt_abs_url('gallery.php'),
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Gallery', 'url' => 'gallery.php'],
        ],
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.css?v=20260717-gallerydesign1">
</head>
<body class="static-site gallery-page">
<div class="site-shell">
    <header class="site-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="132" height="62" decoding="async" fetchpriority="high"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a>
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
            <img class="page-hero-bg" src="assets/images/gallery.png" alt="" aria-hidden="true" decoding="async" fetchpriority="high">
            <span class="page-hero-overlay" aria-hidden="true"></span>
            <div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-images"></i> Admin Gallery</span><h1>Gallery</h1><p>Images uploaded from the admin Media Library appear here automatically.</p></div>
        </section>
        <section class="section alt">
            <?php if ($uploadedGalleryImages): ?>
            <div class="site-container gallery-grid admin-gallery-grid">
                <?php foreach ($uploadedGalleryImages as $image): ?>
                <figure class="gallery-card admin-gallery-card reveal"><img src="<?= htmlspecialchars($image['src']) ?>" alt="<?= htmlspecialchars($image['alt']) ?>" loading="lazy" decoding="async"></figure>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="site-container gallery-empty reveal">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <h2>No gallery images uploaded yet</h2>
                <p>Add gallery images from the backend Media Library. Only admin-uploaded gallery images will appear on this page.</p>
            </div>
            <?php endif; ?>
        </section>
    </main>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="assets/js/site-pages.js?v=20260717-gallerydesign1" defer></script>
</body>
</html>
