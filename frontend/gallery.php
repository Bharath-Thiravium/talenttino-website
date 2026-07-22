<?php
require_once __DIR__ . '/includes/site-data.php';

$uploadedGalleryItems = [];
$galleryUploadDir = __DIR__ . '/uploads/gallery/';
$galleryMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$galleryVideoMimeTypes = ['video/mp4', 'video/webm', 'video/ogg'];
foreach (glob($galleryUploadDir . '*') ?: [] as $path) {
    if (!is_file($path)) {
        continue;
    }
    $mime = mime_content_type($path) ?: '';
    $isImage = in_array($mime, $galleryMimeTypes, true);
    $isVideo = in_array($mime, $galleryVideoMimeTypes, true);
    if (!$isImage && !$isVideo) {
        continue;
    }
    $uploadedGalleryItems[] = [
        'src' => 'uploads/gallery/' . rawurlencode(basename($path)),
        'alt' => pathinfo($path, PATHINFO_FILENAME),
        'mime' => $mime,
        'type' => $isVideo ? 'video' : 'image',
        'modified' => filemtime($path),
    ];
}
usort($uploadedGalleryItems, static fn($a, $b) => $b['modified'] <=> $a['modified']);
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
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260721-navbarfix1">
</head>
<body class="static-site gallery-page">
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
            <img class="page-hero-bg" src="assets/images/gallery.png" alt="" aria-hidden="true" decoding="async" fetchpriority="high">
            <span class="page-hero-overlay" aria-hidden="true"></span>
            <div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-photo-film"></i> Admin Gallery</span><h1>Gallery</h1><p>Images and videos uploaded from the admin Media Library appear here automatically.</p></div>
        </section>
        <section class="section alt">
            <?php if ($uploadedGalleryItems): ?>
            <div class="site-container gallery-grid admin-gallery-grid">
                <?php foreach ($uploadedGalleryItems as $item): ?>
                <?php if ($item['type'] === 'video'): ?>
                <article class="gallery-card admin-gallery-card admin-gallery-video-card reveal">
                    <button type="button" class="gallery-video-trigger" data-video-open data-video-src="<?= htmlspecialchars($item['src']) ?>" data-video-type="<?= htmlspecialchars($item['mime']) ?>" aria-label="Play <?= htmlspecialchars($item['alt']) ?>">
                        <video src="<?= htmlspecialchars($item['src']) ?>" preload="metadata" muted playsinline></video>
                        <span class="gallery-video-play"><i class="fa-solid fa-play"></i></span>
                    </button>
                </article>
                <?php else: ?>
                <figure class="gallery-card admin-gallery-card reveal"><img src="<?= htmlspecialchars($item['src']) ?>" alt="<?= htmlspecialchars($item['alt']) ?>" loading="lazy" decoding="async"></figure>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="site-container gallery-empty reveal">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <h2>No gallery items uploaded yet</h2>
                <p>Add gallery images or videos from the backend Media Library. Only admin-uploaded gallery items will appear on this page.</p>
            </div>
            <?php endif; ?>
        </section>
        <div class="training-video-modal" id="trainingVideoModal" aria-hidden="true">
            <div class="training-video-backdrop" data-video-close></div>
            <div class="training-video-panel" role="dialog" aria-modal="true" aria-label="Gallery video">
                <button class="training-video-close" type="button" data-video-close aria-label="Close video"><i class="fa-solid fa-xmark"></i></button>
                <video controls preload="metadata" playsinline>
                    <source src="" type="video/mp4">
                </video>
            </div>
        </div>
    </main>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260721-navbarfix1" defer></script>
</body>
</html>
