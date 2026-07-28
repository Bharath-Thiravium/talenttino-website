<?php
/**
 * Global header — included by every frontend page.
 * Paths are relative to the frontend/ root.
 */
if (!isset($settings)) {
    $settings = function_exists('tt_settings') ? tt_settings() : [];
}
$_tt_active = basename($_SERVER['PHP_SELF'] ?? '');
function tt_nav_active(string $page): string {
    global $_tt_active;
    return $_tt_active === $page ? ' class="active" aria-current="page"' : '';
}
function tt_nav_parent_active(array $pages): string {
    global $_tt_active;
    return in_array($_tt_active, $pages, true) ? ' active' : '';
}
?>
<link rel="preload" as="image" href="<?= tt_h(tt_asset_url('uploads/optimized/logot-transparent-w128.webp')) ?>" imagesrcset="<?= tt_h(tt_asset_url('uploads/optimized/logot-transparent-w64.webp')) ?> 64w, <?= tt_h(tt_asset_url('uploads/optimized/logot-transparent-w128.webp')) ?> 128w" imagesizes="54px" fetchpriority="high">
<link rel="stylesheet" href="<?= tt_h(tt_asset_url('assets/css/navbar.min.css')) ?>">
<!-- TT_DEPLOY_VERSION: <?= tt_h(tt_deploy_version()) ?> -->
<header class="site-header" id="site-header">
    <div class="nav-wrap">
        <a class="brand" href="index.php" aria-label="Talentteno Institute home">
            <span class="brand-mark logo-mark">
                <img src="<?= tt_h(tt_asset_url('uploads/optimized/logot-transparent-w128.webp')) ?>" srcset="<?= tt_h(tt_asset_url('uploads/optimized/logot-transparent-w64.webp')) ?> 64w, <?= tt_h(tt_asset_url('uploads/optimized/logot-transparent-w128.webp')) ?> 128w" sizes="54px" alt="Talentteno Institute logo" width="48" height="48" decoding="async" fetchpriority="high">
            </span>
            <span class="brand-copy">
                <span class="brand-name">Talentteno Institute</span>
                <span class="brand-sub">IT Training Institute</span>
            </span>
        </a>
        <nav class="site-nav" id="site-nav" aria-label="Main navigation">
            <a href="index.php"<?= tt_nav_active('index.php') ?>>Home</a>
            <a href="about.php"<?= tt_nav_active('about.php') ?>>About</a>
            <div class="nav-item has-menu<?= tt_nav_parent_active(['course.php', 'course-catalog.php', 'shorttermcourse.php', 'popularcourse.php', 'advancecourse.php', 'designingcourse.php', 'cybersecuritycourse.php', 'download.php']) ?>">
                <a href="course.php"<?= tt_nav_active('course.php') ?> aria-haspopup="true" aria-expanded="false" aria-controls="course-nav-menu">Course <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></a>
                <div class="nav-menu" id="course-nav-menu">
                    <a class="nav-menu-rich-link" href="shorttermcourse.php"><i class="fa-solid fa-clock" aria-hidden="true"></i><span>Short Term Course</span></a>
                    <a class="nav-menu-rich-link" href="popularcourse.php"><i class="fa-solid fa-fire" aria-hidden="true"></i><span>Popular Course</span></a>
                    <a class="nav-menu-rich-link" href="advancecourse.php"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><span>Advance Course</span></a>
                    <a class="nav-menu-rich-link" href="designingcourse.php"><i class="fa-solid fa-pen-nib" aria-hidden="true"></i><span>Designing Course</span></a>
                    <a class="nav-menu-rich-link" href="cybersecuritycourse.php"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><span>Cyber Security</span></a>
                </div>
            </div>
            <a href="offers.php"<?= tt_nav_active('offers.php') ?>>Offers</a>
            <a href="gallery.php"<?= tt_nav_active('gallery.php') ?>>Gallery</a>
            <a href="contact.php"<?= tt_nav_active('contact.php') ?>>Contact</a>
            <div class="nav-item has-menu more-menu<?= tt_nav_parent_active(['services.php', 'career.php', 'blog.php', 'project.php', 'review.php', 'why-talentteno.php', 'hiring.php', 'franchise.php']) ?>">
                <a href="#" aria-haspopup="true" aria-expanded="false" aria-controls="more-nav-menu">Others <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></a>
                <div class="nav-menu" id="more-nav-menu">
                    <a class="nav-menu-rich-link" href="services.php"><i class="fa-solid fa-concierge-bell" aria-hidden="true"></i><span>Services</span></a>
                    <a class="nav-menu-rich-link" href="career.php"><i class="fa-solid fa-briefcase" aria-hidden="true"></i><span>Career</span></a>
                    <a class="nav-menu-rich-link" href="blog.php"><i class="fa-solid fa-newspaper" aria-hidden="true"></i><span>Blog</span></a>
                    <a class="nav-menu-rich-link" href="project.php"><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><span>Project</span></a>
                    <a class="nav-menu-rich-link" href="review.php"><i class="fa-solid fa-star" aria-hidden="true"></i><span>Student Reviews</span></a>
                    <a class="nav-menu-rich-link" href="why-talentteno.php"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i><span>Why Talentteno</span></a>
                    <a class="nav-menu-rich-link" href="hiring.php"><i class="fa-solid fa-user-plus" aria-hidden="true"></i><span>Hiring</span></a>
                    <a class="nav-menu-rich-link" href="franchise.php"><i class="fa-solid fa-handshake" aria-hidden="true"></i><span>Franchise Enquiry</span></a>
                </div>
            </div>
            <a class="nav-enroll-cta" href="#home-signup"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i><span>Enroll Now</span></a>
        </nav>
        <button class="menu-button" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="site-nav">
            <span class="menu-button-symbol" aria-hidden="true">&#9776;</span>
        </button>
    </div>
</header>
