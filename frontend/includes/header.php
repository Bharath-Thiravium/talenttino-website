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
<link rel="preload" as="image" href="uploads/optimized/logot-transparent-w128.webp" imagesrcset="uploads/optimized/logot-transparent-w64.webp 64w, uploads/optimized/logot-transparent-w128.webp 128w" imagesizes="54px" fetchpriority="high">
<link rel="stylesheet" href="assets/css/navbar.css?v=20260727-unifiednav1">
<style>
    html body .site-shell{padding-top:calc(var(--nav-height,84px) + var(--nav-offset,18px))!important}
    html body .site-header#site-header,html body.home-page .site-header#site-header,html body.static-site .site-header#site-header,html body.catalog-body .site-header#site-header,html body.course-list-page .site-header#site-header{top:var(--nav-offset,18px)!important;left:50%!important;right:auto!important;width:1200px!important;max-width:1200px!important;height:var(--nav-height,84px)!important;min-height:var(--nav-height,84px)!important;max-height:var(--nav-height,84px)!important;margin:0!important;border:1px solid rgba(29,78,216,.14)!important;border-radius:14px!important;background:rgba(255,255,255,.99)!important;box-shadow:0 18px 48px rgba(15,23,42,.14)!important;transform:translateX(-50%)!important;overflow:visible!important}
    html body .site-header#site-header .nav-wrap,html body.home-page .site-header#site-header .nav-wrap,html body.static-site .site-header#site-header .nav-wrap,html body.catalog-body .site-header#site-header .nav-wrap,html body.course-list-page .site-header#site-header .nav-wrap{width:100%!important;max-width:100%!important;height:100%!important;min-height:100%!important;max-height:100%!important;margin:0!important;padding:0 10px 0 12px!important;display:grid!important;grid-template-columns:minmax(300px,390px) minmax(0,1fr) auto!important;align-items:center!important;gap:clamp(10px,1.2vw,18px)!important;border-radius:14px!important}
    html body .site-header#site-header .brand{width:min(390px,30vw)!important;max-width:390px!important;height:100%!important;display:grid!important;grid-template-columns:54px minmax(0,1fr)!important;column-gap:12px!important;align-items:center!important;align-content:center!important}
    html body .site-header#site-header .brand-mark.logo-mark{width:54px!important;height:54px!important;min-width:54px!important;max-width:54px!important;margin:0!important;padding:3px!important;display:grid!important;place-items:center!important;align-self:center!important;border-radius:50%!important;overflow:hidden!important}
    html body .site-header#site-header .brand-mark.logo-mark img{width:48px!important;height:48px!important;display:block!important;object-fit:contain!important;margin:0!important}
    html body .site-header#site-header .brand-copy{min-width:0!important;height:54px!important;display:flex!important;flex-direction:column!important;justify-content:center!important;align-items:flex-start!important;margin:0!important}
    html body .site-header#site-header .site-nav{display:flex!important;align-items:center!important;justify-content:flex-end!important;gap:clamp(8px,1vw,18px)!important}
    html body .site-header#site-header .site-nav>a,html body .site-header#site-header .nav-item>a{height:42px!important;min-height:42px!important;padding:0 6px!important;font-size:clamp(13.5px,.95vw,15px)!important;white-space:nowrap!important}
    @media(max-width:1248px) and (min-width:981px){html body .site-header#site-header,html body.home-page .site-header#site-header,html body.static-site .site-header#site-header,html body.catalog-body .site-header#site-header,html body.course-list-page .site-header#site-header{width:calc(100% - 48px)!important;max-width:1200px!important}html body .site-header#site-header .nav-wrap{grid-template-columns:minmax(280px,330px) minmax(0,1fr) auto!important;gap:10px!important}html body .site-header#site-header .brand{width:330px!important;max-width:330px!important}html body .site-header#site-header .brand-name{font-size:25px!important}html body .site-header#site-header .site-nav{gap:7px!important}html body .site-header#site-header .site-nav>a,html body .site-header#site-header .nav-item>a{padding:0 4px!important;font-size:13.5px!important}}
    @media(max-width:980px){:root{--nav-height:72px;--nav-offset:10px}html body .site-header#site-header,html body.home-page .site-header#site-header,html body.static-site .site-header#site-header,html body.catalog-body .site-header#site-header,html body.course-list-page .site-header#site-header{width:min(100% - 20px,720px)!important;border-radius:12px!important}html body .site-header#site-header .nav-wrap{display:flex!important;justify-content:space-between!important;padding:0 64px 0 14px!important}html body .site-header#site-header .brand{width:100%!important;max-width:100%!important;grid-template-columns:46px minmax(0,1fr)!important}html body .site-header#site-header .brand-mark.logo-mark{width:46px!important;height:46px!important;min-width:46px!important;max-width:46px!important;padding:3px!important}html body .site-header#site-header .brand-mark.logo-mark img{width:40px!important;height:40px!important}html body .site-header#site-header .brand-copy{height:46px!important}html body .site-header#site-header .site-nav{display:none!important}html body .site-header#site-header .site-nav.open{display:flex!important}}
</style>
<header class="site-header" id="site-header" style="position:fixed!important;top:18px!important;left:50%!important;right:auto!important;width:1200px!important;max-width:1200px!important;height:84px!important;min-height:84px!important;max-height:84px!important;margin:0!important;border:1px solid rgba(29,78,216,.14)!important;border-radius:14px!important;background:rgba(255,255,255,.99)!important;box-shadow:0 18px 48px rgba(15,23,42,.14)!important;transform:translateX(-50%)!important;overflow:visible!important;">
    <div class="nav-wrap" style="width:100%!important;max-width:100%!important;height:100%!important;min-height:100%!important;max-height:100%!important;margin:0!important;padding:0 10px 0 12px!important;display:grid!important;grid-template-columns:minmax(300px,390px) minmax(0,1fr) auto!important;align-items:center!important;gap:clamp(10px,1.2vw,18px)!important;border-radius:14px!important;">
        <a class="brand" href="index.php" aria-label="Talentteno Institute home" style="height:100%!important;display:grid!important;grid-template-columns:54px minmax(0,1fr)!important;column-gap:12px!important;align-items:center!important;align-content:center!important;">
            <span class="brand-mark logo-mark" style="width:54px!important;height:54px!important;min-width:54px!important;max-width:54px!important;margin:0!important;padding:3px!important;display:grid!important;place-items:center!important;align-self:center!important;border-radius:50%!important;overflow:hidden!important;">
                <img src="uploads/optimized/logot-transparent-w128.webp" srcset="uploads/optimized/logot-transparent-w64.webp 64w, uploads/optimized/logot-transparent-w128.webp 128w" sizes="54px" alt="Talentteno Institute logo" width="48" height="48" decoding="async" fetchpriority="high" style="width:48px!important;height:48px!important;display:block!important;object-fit:contain!important;margin:0!important;">
            </span>
            <span class="brand-copy" style="min-width:0!important;height:54px!important;display:flex!important;flex-direction:column!important;justify-content:center!important;align-items:flex-start!important;margin:0!important;">
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
    (() => {
        const header = document.getElementById('site-header');
        const navWrap = header?.querySelector('.nav-wrap');
        const brand = header?.querySelector('.brand');
        const nav = header?.querySelector('.site-nav');
        if (!header || !navWrap) return;

        const setImportant = (element, property, value) => {
            element.style.setProperty(property, value, 'important');
        };

        const applyUnifiedHeader = () => {
            const isMobile = window.matchMedia('(max-width: 980px)').matches;
            document.documentElement.style.setProperty('--nav-height', isMobile ? '72px' : '84px');
            document.documentElement.style.setProperty('--nav-offset', isMobile ? '10px' : '18px');
            document.documentElement.style.setProperty('--header-height', isMobile ? '72px' : '84px');
            setImportant(header, 'position', 'fixed');
            setImportant(header, 'top', isMobile ? '10px' : '18px');
            setImportant(header, 'left', '50%');
            setImportant(header, 'right', 'auto');
            setImportant(header, 'width', isMobile ? 'calc(100% - 20px)' : (window.innerWidth <= 1248 ? 'calc(100% - 48px)' : '1200px'));
            setImportant(header, 'max-width', isMobile ? '720px' : '1200px');
            setImportant(header, 'height', isMobile ? '72px' : '84px');
            setImportant(header, 'min-height', isMobile ? '72px' : '84px');
            setImportant(header, 'max-height', isMobile ? '72px' : '84px');
            setImportant(header, 'margin', '0');
            setImportant(header, 'border-radius', isMobile ? '12px' : '14px');
            setImportant(header, 'border', '1px solid rgba(29, 78, 216, 0.14)');
            setImportant(header, 'background', 'rgba(255, 255, 255, 0.99)');
            setImportant(header, 'box-shadow', '0 18px 48px rgba(15, 23, 42, 0.14)');
            setImportant(header, 'transform', 'translateX(-50%)');
            setImportant(header, 'overflow', 'visible');

            setImportant(navWrap, 'width', '100%');
            setImportant(navWrap, 'max-width', '100%');
            setImportant(navWrap, 'height', '100%');
            setImportant(navWrap, 'min-height', '100%');
            setImportant(navWrap, 'max-height', '100%');
            setImportant(navWrap, 'margin', '0');
            setImportant(navWrap, 'border-radius', isMobile ? '12px' : '14px');
            if (isMobile) {
                setImportant(navWrap, 'display', 'flex');
                setImportant(navWrap, 'justify-content', 'space-between');
                setImportant(navWrap, 'padding', '0 64px 0 14px');
                if (brand) setImportant(brand, 'width', '100%');
            } else {
                setImportant(navWrap, 'display', 'grid');
                setImportant(navWrap, 'grid-template-columns', window.innerWidth <= 1280 ? 'minmax(280px, 330px) minmax(0, 1fr) auto' : 'minmax(300px, 390px) minmax(0, 1fr) auto');
                setImportant(navWrap, 'align-items', 'center');
                setImportant(navWrap, 'gap', window.innerWidth <= 1280 ? '10px' : 'clamp(10px, 1.2vw, 18px)');
                setImportant(navWrap, 'padding', '0 10px 0 12px');
                if (brand) setImportant(brand, 'width', window.innerWidth <= 1280 ? '330px' : 'min(390px, 30vw)');
                if (nav) setImportant(nav, 'display', 'flex');
            }
        };

        applyUnifiedHeader();
        window.addEventListener('resize', applyUnifiedHeader, { passive: true });
    })();
</script>
