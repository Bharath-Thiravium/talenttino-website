<?php
require_once __DIR__ . '/includes/site-data.php';

$uploadedGalleryImages = [];
$galleryUploadDir = __DIR__ . '/uploads/media/';
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
        'src' => 'uploads/media/' . rawurlencode(basename($path)),
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
    <link rel="stylesheet" href="assets/css/site-pages.css?v=20260714-43">
</head>
<body class="static-site">
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
        <section class="page-hero"><div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-images"></i> Poster, Flyer and Institute Visuals</span><h1>Gallery</h1><p>Visuals from Talentteno Board, Poster and Flyer, plus learning environment images for training, projects and career support.</p></div></section>
        <section class="section">
            <div class="site-container poster-pair">
                <div class="pdf-frame reveal"><img src="assets/images/talentteno-flyer-1.png" alt="Talentteno flyer page one with basic to advanced courses" loading="lazy" decoding="async"></div>
                <div class="pdf-frame reveal"><img src="assets/images/talentteno-flyer-2.png" alt="Talentteno flyer page two with cyber security combo pack" loading="lazy" decoding="async"></div>
            </div>
        </section>
        <section class="section alt">
            <div class="site-container gallery-grid">
                <div class="gallery-card tall reveal"><img src="assets/images/talentteno-poster.png" alt="Talentteno poster with benefits and courses" loading="lazy" decoding="async"></div>
                <div class="gallery-card reveal"><img src="assets/images/logot-transparent.png" alt="Talentteno board logo" loading="lazy" decoding="async"></div>
                <div class="gallery-card reveal"><img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=520&q=80" alt="Technology learning on laptop" loading="lazy" decoding="async"></div>
                <div class="gallery-card reveal"><img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=640&q=80" alt="Instructor mentoring students" loading="lazy" decoding="async"></div>
                <div class="gallery-card reveal"><img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=640&q=80" alt="Students in presentation discussion" loading="lazy" decoding="async"></div>
                <div class="gallery-card reveal"><img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=640&q=80" alt="Career guidance discussion" loading="lazy" decoding="async"></div>
                <?php foreach ($uploadedGalleryImages as $image): ?>
                <div class="gallery-card reveal"><img src="<?= htmlspecialchars($image['src']) ?>" alt="<?= htmlspecialchars($image['alt']) ?>" loading="lazy" decoding="async"></div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="assets/js/site-pages.js?v=20260714-13" defer></script>
</body>
</html>
