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
<style>
    html,
    html body {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    html body .site-shell {
        padding-top: var(--nav-height, 84px) !important;
    }

    html body .site-header#site-header,
    html body.home-page .site-header#site-header,
    html body.static-site .site-header#site-header,
    html body.catalog-body .site-header#site-header,
    html body.course-list-page .site-header#site-header {
        --nav-offset: 0px !important;
        top: 0 !important;
        inset-block-start: 0 !important;
    }

    @media (max-width: 1100px) {
        html body .site-shell {
            padding-top: var(--nav-height, 72px) !important;
        }

        html body .site-header#site-header .site-nav {
            top: calc(var(--nav-height) + 8px) !important;
            height: calc(100dvh - var(--nav-height) - 8px) !important;
            max-height: calc(100dvh - var(--nav-height) - 8px) !important;
            gap: 10px !important;
        }

        html body .site-header#site-header .site-nav > a,
        html body .site-header#site-header .site-nav .nav-item > a {
            min-height: 54px !important;
            border: 1px solid rgba(37, 99, 235, .14) !important;
            border-radius: 12px !important;
            background: #ffffff !important;
        }

        html body .site-header#site-header .site-nav > a.active,
        html body .site-header#site-header .site-nav > a[aria-current="page"],
        html body .site-header#site-header .site-nav .nav-item.active > a,
        html body .site-header#site-header .site-nav .nav-item.open > a {
            color: #0845b2 !important;
            background: #eef7ff !important;
            border-color: rgba(37, 99, 235, .22) !important;
        }

        html body .site-header#site-header .site-nav .nav-enroll-cta {
            min-height: 54px !important;
            justify-content: center !important;
            border: 0 !important;
            border-radius: 12px !important;
            background: linear-gradient(135deg, #2563eb 0, #c026d3 100%) !important;
        }
    }
</style>
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
<script>
    (function () {
        var nav = document.getElementById('site-nav');
        if (!nav) return;
        var offersLink = nav.querySelector('a[href="offers.php"]');
        if (!offersLink) {
            offersLink = document.createElement('a');
            offersLink.href = 'offers.php';
            offersLink.textContent = 'Offers';
            if ((window.location.pathname.split('/').pop() || 'index.php') === 'offers.php') {
                offersLink.className = 'active';
                offersLink.setAttribute('aria-current', 'page');
            }
            nav.insertBefore(offersLink, nav.querySelector('a[href="gallery.php"]') || nav.querySelector('a[href="contact.php"]') || nav.querySelector('.more-menu') || nav.querySelector('.nav-enroll-cta'));
        }
        var courseMenu = nav.querySelector('.nav-item.has-menu:not(.more-menu)');
        var galleryLink = nav.querySelector('a[href="gallery.php"]');
        if (courseMenu && offersLink && courseMenu.nextElementSibling !== offersLink) {
            nav.insertBefore(offersLink, galleryLink || courseMenu.nextSibling);
        }
    })();
</script>
