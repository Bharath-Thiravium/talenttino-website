<?php
require_once __DIR__ . '/includes/site-data.php';

$coursePage = $coursePage ?? [
    'title' => 'Courses',
    'subtitle' => 'Practical IT training programs with projects, internship support and placement assistance.',
    'active' => 'course',
    'courses' => [],
];

function tt_icon_class(string $icon): string
{
    return str_contains($icon, ' ') ? $icon : 'fa-solid ' . $icon;
}

function tt_catalog_image_url(?string $image): string
{
    $managedImage = tt_course_image_url($image);
    if ($managedImage !== '') {
        return $managedImage;
    }

    $path = trim((string)$image);
    if ($path === '' || preg_match('#^(https?:)?//#i', $path)) {
        return '';
    }

    $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
    if (str_contains($cleanPath, '..')) {
        return '';
    }

    $allowed = ['assets/images/', 'uploads/media/', 'uploads/course-images/'];
    foreach ($allowed as $prefix) {
        if (str_starts_with($cleanPath, $prefix) && is_file(__DIR__ . '/' . $cleanPath)) {
            return $cleanPath;
        }
    }

    return '';
}

function tt_catalog_fallback_image(array $course): string
{
    $needle = strtolower(trim(($course['name'] ?? '') . ' ' . ($course['category'] ?? '') . ' ' . ($course['desc'] ?? '')));
    $map = [
        'cloud' => 'uploads/media/cloud-computing-20260703-133220-323189.png',
        'cyber' => 'uploads/media/cyber-security-20260703-133329-242125.png',
        'security' => 'uploads/media/cyber-security-20260703-133329-242125.png',
        'data science' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'artificial intelligence' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'ai' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'database' => 'uploads/media/data-analyst-20260703-133130-702998.png',
        'data' => 'uploads/media/data-analyst-20260703-133130-702998.png',
        'digital marketing' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'marketing' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'graphic' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'design' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'web' => 'uploads/media/full-stack-development-20260703-133158-761383.png',
        'full stack' => 'uploads/media/full-stack-development-20260703-133158-761383.png',
        'programming' => 'uploads/media/programming-languages-20260703-133210-630417.png',
        'software' => 'uploads/media/programming-languages-20260703-133210-630417.png',
        'testing' => 'uploads/media/programming-languages-20260703-133210-630417.png',
        'hardware' => 'uploads/media/cloud-computing-20260703-133220-323189.png',
        'computer' => 'uploads/media/programming-languages-20260703-133210-630417.png',
        'accounting' => 'uploads/media/data-analyst-20260703-133130-702998.png',
        'career' => 'assets/images/contact-counsellor-hero.png',
        'english' => 'assets/images/contact-counsellor-hero.png',
    ];

    foreach ($map as $word => $path) {
        if (str_contains($needle, $word) && is_file(__DIR__ . '/' . $path)) {
            return $path;
        }
    }

    return 'uploads/media/full-stack-development-20260703-133158-761383.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => $coursePage['title'] . ' | Talentteno Institute Madurai',
        'description' => $coursePage['subtitle'],
        'canonical' => tt_abs_url(basename($_SERVER['SCRIPT_NAME'] ?? 'course.php')),
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Courses', 'url' => 'course.php'],
            ['name' => $coursePage['title'], 'url' => basename($_SERVER['SCRIPT_NAME'] ?? 'course.php')],
        ],
        'courses' => $coursePage['courses'],
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@1,700;1,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260718-brandfix1">
    <style>
        body.catalog-body .catalog-section{background:#eef6ff!important;padding:56px 0!important}
        body.catalog-body .catalog-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;align-items:stretch!important;gap:22px!important}
        body.catalog-body .catalog-grid>.catalog-card,
        body.catalog-body.compact-catalog .catalog-grid>.catalog-card,
        body.catalog-body .catalog-grid>.catalog-card:nth-child(n){position:relative!important;inset:auto!important;grid-column:auto!important;grid-row:auto!important;display:flex!important;flex-direction:column!important;min-width:0!important;width:100%!important;min-height:520px!important;margin:0!important;padding:0 22px 84px!important;transform:none!important;translate:none!important;border-radius:8px!important;overflow:hidden!important;background:#fff!important;border:1px solid rgba(37,99,235,.14)!important;box-shadow:0 18px 46px rgba(15,23,42,.08)!important;color:#10172a!important}
        body.catalog-body .catalog-grid>.catalog-card::before,
        body.catalog-body .catalog-grid>.catalog-card::after{display:none!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-image{display:block!important;width:calc(100% + 44px)!important;height:220px!important;min-height:220px!important;max-height:220px!important;margin:0 -22px 20px!important;border-radius:8px 8px 0 0!important;overflow:hidden!important;background:#eaf3ff!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-image img{display:block!important;width:100%!important;height:100%!important;object-fit:cover!important;object-position:center!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-icon{position:static!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;width:52px!important;height:52px!important;min-width:52px!important;margin:0 0 18px!important;border-radius:10px!important;background:linear-gradient(135deg,#4f8cff 0%,#7c5cff 48%,#d91cf6 100%)!important;color:#fff!important;-webkit-text-fill-color:#fff!important;box-shadow:0 14px 26px rgba(79,140,255,.18)!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-icon i{color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:22px!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-category{position:absolute!important;top:242px!important;right:22px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;max-width:calc(100% - 96px)!important;min-height:28px!important;padding:6px 13px!important;border-radius:999px!important;background:#eef6ff!important;border:1px solid rgba(79,140,255,.28)!important;color:#1554d1!important;-webkit-text-fill-color:#1554d1!important;font-size:12px!important;font-weight:900!important;line-height:1!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
        body.catalog-body .catalog-grid>.catalog-card h2{display:block!important;visibility:visible!important;opacity:1!important;min-height:58px!important;margin:0 0 10px!important;color:#10172a!important;-webkit-text-fill-color:#10172a!important;background:none!important;font-size:25px!important;font-weight:900!important;line-height:1.16!important;letter-spacing:0!important;text-align:left!important}
        body.catalog-body .catalog-grid>.catalog-card>p{display:-webkit-box!important;visibility:visible!important;opacity:1!important;-webkit-line-clamp:2!important;-webkit-box-orient:vertical!important;overflow:hidden!important;min-height:42px!important;margin:0 0 14px!important;color:#52627a!important;-webkit-text-fill-color:#52627a!important;font-size:14px!important;line-height:1.5!important;text-align:left!important}
        body.catalog-body .catalog-grid>.catalog-card ul,
        body.catalog-body .catalog-grid>.catalog-card .catalog-price{display:none!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions{position:absolute!important;left:22px!important;right:22px!important;bottom:22px!important;display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;margin:0!important;padding-top:14px!important;border-top:0!important;background:#fff!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-detail-btn,
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-cta{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:8px!important;width:100%!important;min-width:0!important;min-height:42px!important;height:42px!important;padding:0 12px!important;border-radius:8px!important;clip-path:none!important;text-decoration:none!important;font-size:13px!important;font-weight:900!important;line-height:1!important;letter-spacing:0!important;white-space:nowrap!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-detail-btn{background:#fff!important;color:#1554d1!important;-webkit-text-fill-color:#1554d1!important;border:1px solid rgba(79,140,255,.42)!important;box-shadow:none!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-cta{background:linear-gradient(135deg,#4f8cff 0%,#7c5cff 48%,#d91cf6 100%)!important;color:#fff!important;-webkit-text-fill-color:#fff!important;border:0!important;box-shadow:0 12px 26px rgba(79,140,255,.25)!important}
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions i{color:inherit!important;-webkit-text-fill-color:inherit!important}
        @media (max-width:1100px){body.catalog-body .catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
        @media (max-width:700px){body.catalog-body .catalog-section{padding:34px 0!important}body.catalog-body .catalog-grid{grid-template-columns:1fr!important}body.catalog-body .catalog-grid>.catalog-card{min-height:500px!important}}
        @media (max-width:700px){
            body.catalog-body .catalog-grid{width:min(100% - 28px,420px)!important;margin:0 auto!important;gap:24px!important}
            body.catalog-body .catalog-grid>.catalog-card,
            body.catalog-body.compact-catalog .catalog-grid>.catalog-card,
            body.catalog-body .catalog-grid>.catalog-card:nth-child(n){min-height:auto!important;padding:0!important;border-radius:18px!important;overflow:hidden!important;box-shadow:0 22px 58px rgba(15,23,42,.10)!important}
            body.catalog-body .catalog-grid>.catalog-card .catalog-image{width:100%!important;height:210px!important;min-height:210px!important;max-height:210px!important;margin:0!important;border-radius:0!important}
            body.catalog-body .catalog-grid>.catalog-card .catalog-icon{margin:24px 22px 18px!important;border-radius:13px!important}
            body.catalog-body .catalog-grid>.catalog-card .catalog-category{top:236px!important;right:22px!important}
            body.catalog-body .catalog-grid>.catalog-card h2{min-height:0!important;margin:14px 22px 14px!important;font-size:clamp(23px,6vw,28px)!important;line-height:1.18!important}
            body.catalog-body .catalog-grid>.catalog-card>p{display:block!important;min-height:0!important;margin:0 22px 24px!important;overflow:visible!important;font-size:14.5px!important;line-height:1.65!important}
            body.catalog-body .catalog-grid>.catalog-card .catalog-actions{position:static!important;left:auto!important;right:auto!important;bottom:auto!important;width:auto!important;margin:0!important;padding:16px 22px 22px!important;display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;border-top:0!important}
            body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-detail-btn,
            body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-cta{min-height:46px!important;height:46px!important;border-radius:10px!important;gap:0!important}
            body.catalog-body .catalog-grid>.catalog-card .catalog-actions i{display:none!important}
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions{
            left:22px!important;
            right:22px!important;
            bottom:22px!important;
            width:auto!important;
            max-width:none!important;
            display:grid!important;
            grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
            align-items:center!important;
            gap:10px!important;
            overflow:visible!important;
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-detail-btn,
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-cta{
            position:relative!important;
            inset:auto!important;
            opacity:1!important;
            visibility:visible!important;
            width:100%!important;
            max-width:100%!important;
            min-width:0!important;
            height:42px!important;
            min-height:42px!important;
            padding:0 14px!important;
            display:inline-flex!important;
            align-items:center!important;
            justify-content:center!important;
            border-radius:10px!important;
            clip-path:none!important;
            transform:none!important;
            font-size:13px!important;
            font-weight:900!important;
            line-height:1!important;
            letter-spacing:0!important;
            text-align:center!important;
            white-space:nowrap!important;
            overflow:hidden!important;
            text-overflow:ellipsis!important;
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-detail-btn{
            background:#ffffff!important;
            color:#1554d1!important;
            -webkit-text-fill-color:#1554d1!important;
            border:1px solid rgba(79,140,255,.46)!important;
            box-shadow:inset 0 0 0 1px rgba(255,255,255,.78)!important;
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-cta{
            background:linear-gradient(135deg,#4f8cff 0%,#7c5cff 48%,#d91cf6 100%)!important;
            color:#ffffff!important;
            -webkit-text-fill-color:#ffffff!important;
            border:1px solid transparent!important;
            box-shadow:0 12px 24px rgba(124,92,255,.24)!important;
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-detail-btn:hover,
        body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-cta:hover{
            transform:translateY(-2px)!important;
            box-shadow:0 16px 30px rgba(37,99,235,.16)!important;
        }
        @media (max-width:700px){
            body.catalog-body .catalog-grid>.catalog-card .catalog-actions{
                position:static!important;
                padding:16px 22px 22px!important;
                grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
                gap:10px!important;
            }
            body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-detail-btn,
            body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-cta{
                height:46px!important;
                min-height:46px!important;
                border-radius:10px!important;
                font-size:13px!important;
            }
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-image{
            position:relative!important;
            display:flex!important;
            align-items:center!important;
            justify-content:center!important;
            background-image:var(--catalog-image-bg)!important;
            background-size:cover!important;
            background-position:center!important;
            isolation:isolate!important;
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-image::before{
            content:""!important;
            position:absolute!important;
            inset:-12px!important;
            z-index:-1!important;
            background:inherit!important;
            filter:blur(16px) saturate(1.08)!important;
            transform:scale(1.08)!important;
            opacity:.55!important;
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-image::after{
            content:""!important;
            position:absolute!important;
            inset:0!important;
            z-index:-1!important;
            background:rgba(244,248,255,.34)!important;
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-image img{
            width:100%!important;
            height:100%!important;
            object-fit:cover!important;
            object-position:center!important;
            background:transparent!important;
        }
        body.catalog-body .catalog-grid>.catalog-card:hover .catalog-image img{
            transform:none!important;
            filter:saturate(1.03) contrast(1.02)!important;
        }
        body.catalog-body .catalog-hero[style*="--catalog-hero-image"]{
            position:relative!important;
            min-height:464px!important;
            height:464px!important;
            padding:0!important;
            background:#061631!important;
        }
        body.catalog-body .catalog-hero .catalog-hero-bg{
            position:absolute!important;
            inset:0!important;
            z-index:0!important;
            width:100%!important;
            height:100%!important;
            object-fit:cover!important;
            object-position:center center!important;
            opacity:1!important;
            filter:saturate(1.06) contrast(1.04)!important;
            pointer-events:none!important;
        }
        body.catalog-body .catalog-hero .catalog-hero-overlay{
            position:absolute!important;
            inset:0!important;
            z-index:1!important;
            background:linear-gradient(90deg,rgba(4,13,32,.92) 0%,rgba(9,36,84,.68) 42%,rgba(12,72,150,.10) 100%)!important;
            pointer-events:none!important;
        }
        body.catalog-body .catalog-hero[style*="--catalog-hero-image"]::before{
            z-index:2!important;
            opacity:.34!important;
        }
        body.catalog-body .catalog-hero[style*="--catalog-hero-image"] .site-container{
            position:relative!important;
            z-index:3!important;
            min-height:100%!important;
            display:flex!important;
            flex-direction:column!important;
            justify-content:center!important;
        }
        body.catalog-body .catalog-hero[style*="--catalog-hero-image"] h1,
        body.catalog-body .catalog-hero[style*="--catalog-hero-image"] p,
        body.catalog-body .catalog-hero[style*="--catalog-hero-image"] .hero-kicker{
            text-shadow:0 14px 38px rgba(0,0,0,.42)!important;
        }
        @media (max-width:760px){
            body.catalog-body .catalog-hero[style*="--catalog-hero-image"]{
                min-height:390px!important;
                height:390px!important;
                background:#061631!important;
            }
            body.catalog-body .catalog-hero .catalog-hero-bg{
                object-position:center center!important;
            }
            body.catalog-body .catalog-hero .catalog-hero-overlay{
                background:linear-gradient(180deg,rgba(4,13,32,.90) 0%,rgba(9,36,84,.70) 58%,rgba(12,72,150,.20) 100%)!important;
            }
        }
        body.catalog-body.popular-course-page .catalog-hero .catalog-hero-bg{
            object-position:center right!important;
        }
        body.catalog-body.popular-course-page .catalog-hero .catalog-hero-overlay{
            background:linear-gradient(90deg,rgba(4,13,32,.94) 0%,rgba(7,24,62,.80) 38%,rgba(8,35,96,.28) 72%,rgba(4,13,32,.12) 100%)!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero .catalog-hero-bg{
            object-fit:contain!important;
            object-position:center right!important;
            background:#eef6ff!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero .catalog-hero-overlay{
            background:linear-gradient(90deg,rgba(4,13,32,.94) 0%,rgba(9,36,84,.76) 38%,rgba(12,72,150,.26) 60%,rgba(238,246,255,.08) 100%)!important;
        }
        @media (max-width:760px){
            body.catalog-body.popular-course-page .catalog-hero .catalog-hero-bg{
                object-position:63% center!important;
            }
            body.catalog-body.popular-course-page .catalog-hero .catalog-hero-overlay{
                background:linear-gradient(180deg,rgba(4,13,32,.94) 0%,rgba(7,24,62,.82) 54%,rgba(8,35,96,.38) 100%)!important;
            }
            body.catalog-body.advanced-course-page .catalog-hero .catalog-hero-bg{
                object-fit:cover!important;
                object-position:62% center!important;
            }
            body.catalog-body.advanced-course-page .catalog-hero .catalog-hero-overlay{
                background:linear-gradient(180deg,rgba(4,13,32,.92) 0%,rgba(9,36,84,.74) 56%,rgba(12,72,150,.26) 100%)!important;
            }
        }
        body.catalog-body .catalog-hero[style*="--catalog-hero-image"]{
            min-height:454px!important;
            height:454px!important;
            display:flex!important;
            align-items:center!important;
            isolation:isolate!important;
        }
        body.catalog-body .catalog-hero .catalog-hero-bg{
            transform:scale(1.015)!important;
            transform-origin:center right!important;
            animation:catalogHeroImageDrift 12s ease-in-out infinite alternate!important;
        }
        body.catalog-body .catalog-hero .catalog-hero-overlay{
            backdrop-filter:saturate(1.05)!important;
        }
        body.catalog-body .catalog-hero[style*="--catalog-hero-image"] .site-container{
            width:min(1200px,calc(100% - 48px))!important;
            min-height:0!important;
            padding-top:10px!important;
            animation:catalogHeroCopyIn .72s cubic-bezier(.16,1,.3,1) both!important;
        }
        body.catalog-body .catalog-hero .hero-kicker{
            width:auto!important;
            max-width:max-content!important;
            min-height:36px!important;
            margin:0 0 28px!important;
            padding:0 18px!important;
            display:inline-flex!important;
            align-items:center!important;
            gap:10px!important;
            border-radius:999px!important;
            background:rgba(255,255,255,.12)!important;
            border:1px solid rgba(255,255,255,.28)!important;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.16),0 12px 30px rgba(0,0,0,.12)!important;
            color:#64a5ff!important;
            -webkit-text-fill-color:#64a5ff!important;
            font-size:13px!important;
            font-weight:900!important;
            letter-spacing:.12em!important;
            line-height:1!important;
            white-space:nowrap!important;
        }
        body.catalog-body .catalog-hero .hero-kicker::before,
        body.catalog-body .catalog-hero .hero-kicker::after{
            display:none!important;
            content:none!important;
        }
        body.catalog-body .catalog-hero h1{
            max-width:720px!important;
            margin:0 0 22px!important;
            color:#fff!important;
            -webkit-text-fill-color:#fff!important;
            font-size:clamp(56px,6vw,86px)!important;
            line-height:.98!important;
            letter-spacing:0!important;
            text-align:left!important;
        }
        body.catalog-body .catalog-hero p{
            max-width:760px!important;
            margin:0!important;
            color:rgba(255,255,255,.94)!important;
            -webkit-text-fill-color:rgba(255,255,255,.94)!important;
            font-size:23px!important;
            line-height:1.55!important;
            font-weight:750!important;
            text-align:left!important;
        }
        body.catalog-body .catalog-section{
            padding:58px 0 72px!important;
            background:linear-gradient(180deg,#eef6ff 0%,#f8fbff 100%)!important;
        }
        body.catalog-body .catalog-grid>.catalog-card{
            animation:catalogCardIn .7s cubic-bezier(.16,1,.3,1) both!important;
        }
        body.catalog-body .catalog-grid>.catalog-card:nth-child(2n){
            animation-delay:.06s!important;
        }
        body.catalog-body .catalog-grid>.catalog-card:nth-child(3n){
            animation-delay:.12s!important;
        }
        body.catalog-body .catalog-grid>.catalog-card:hover{
            transform:translateY(-8px)!important;
            box-shadow:0 24px 58px rgba(15,23,42,.13)!important;
        }
        body.catalog-body .catalog-grid>.catalog-card .catalog-image img{
            transition:transform .8s cubic-bezier(.16,1,.3,1),filter .3s ease!important;
        }
        body.catalog-body .catalog-grid>.catalog-card:hover .catalog-image img{
            transform:scale(1.035)!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero .catalog-hero-bg{
            transform:scale(1)!important;
            animation:catalogAdvancedHeroDrift 12s ease-in-out infinite alternate!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero[style*="--catalog-hero-image"]{
            min-height:520px!important;
            height:auto!important;
            padding:64px 0 58px!important;
            align-items:center!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero[style*="--catalog-hero-image"] .site-container{
            padding-top:0!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero .hero-kicker{
            margin-bottom:24px!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero h1{
            max-width:620px!important;
            margin-bottom:20px!important;
            font-size:clamp(48px,5.2vw,74px)!important;
            line-height:1.04!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero p{
            max-width:590px!important;
            font-size:21px!important;
            line-height:1.55!important;
        }
        body.catalog-body.advanced-course-page .catalog-hero .catalog-hero-overlay{
            background:linear-gradient(90deg,rgba(4,13,32,.96) 0%,rgba(9,36,84,.84) 46%,rgba(12,72,150,.38) 67%,rgba(238,246,255,.10) 100%)!important;
        }
        @media (max-width:900px){
            body.catalog-body .catalog-hero[style*="--catalog-hero-image"]{
                min-height:410px!important;
                height:410px!important;
            }
            body.catalog-body .catalog-hero h1{
                font-size:clamp(42px,9vw,64px)!important;
            }
            body.catalog-body .catalog-hero p{
                max-width:620px!important;
                font-size:18px!important;
            }
            body.catalog-body .catalog-hero .hero-kicker{
                max-width:100%!important;
                white-space:normal!important;
                line-height:1.25!important;
            }
            body.catalog-body.advanced-course-page .catalog-hero[style*="--catalog-hero-image"]{
                min-height:500px!important;
                padding:56px 0 50px!important;
            }
            body.catalog-body.advanced-course-page .catalog-hero h1{
                max-width:560px!important;
                font-size:clamp(42px,8vw,58px)!important;
            }
            body.catalog-body.advanced-course-page .catalog-hero p{
                max-width:540px!important;
                font-size:18px!important;
            }
        }
        @media (max-width:560px){
            body.catalog-body .catalog-hero[style*="--catalog-hero-image"]{
                min-height:380px!important;
                height:380px!important;
            }
            body.catalog-body .catalog-hero[style*="--catalog-hero-image"] .site-container{
                width:min(100% - 30px,420px)!important;
            }
            body.catalog-body .catalog-hero h1{
                margin-bottom:16px!important;
            }
            body.catalog-body.advanced-course-page .catalog-hero[style*="--catalog-hero-image"]{
                min-height:460px!important;
                padding:46px 0 42px!important;
            }
            body.catalog-body.advanced-course-page .catalog-hero h1{
                font-size:clamp(38px,11vw,48px)!important;
            }
        }
        @keyframes catalogHeroImageDrift{
            from{transform:scale(1.015) translate3d(0,0,0)}
            to{transform:scale(1.055) translate3d(-10px,0,0)}
        }
        @keyframes catalogAdvancedHeroDrift{
            from{transform:scale(1) translate3d(0,0,0)}
            to{transform:scale(1.025) translate3d(-8px,0,0)}
        }
        @keyframes catalogHeroCopyIn{
            from{opacity:0;transform:translate3d(0,26px,0)}
            to{opacity:1;transform:translate3d(0,0,0)}
        }
        @keyframes catalogCardIn{
            from{opacity:0;transform:translate3d(0,26px,0)}
            to{opacity:1;transform:translate3d(0,0,0)}
        }
        @media (prefers-reduced-motion:reduce){
            body.catalog-body .catalog-hero .catalog-hero-bg,
            body.catalog-body .catalog-hero[style*="--catalog-hero-image"] .site-container,
            body.catalog-body .catalog-grid>.catalog-card{
                animation:none!important;
            }
        }
        @media (max-width:700px){
            html body.catalog-body .catalog-grid{
                width:min(100% - 28px,420px)!important;
                margin:0 auto!important;
                grid-template-columns:1fr!important;
            }
            html body.catalog-body .catalog-grid>.catalog-card,
            html body.catalog-body.compact-catalog .catalog-grid>.catalog-card,
            html body.catalog-body .catalog-grid>.catalog-card:nth-child(n){
                display:flex!important;
                flex-direction:column!important;
                min-height:0!important;
                height:auto!important;
                padding:0!important;
                overflow:hidden!important;
            }
            html body.catalog-body .catalog-grid>.catalog-card .catalog-actions{
                position:relative!important;
                left:auto!important;
                right:auto!important;
                bottom:auto!important;
                z-index:3!important;
                width:auto!important;
                margin:auto 0 0!important;
                padding:0 22px 22px!important;
                display:grid!important;
                grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
                gap:10px!important;
                background:#fff!important;
            }
            html body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-detail-btn,
            html body.catalog-body .catalog-grid>.catalog-card .catalog-actions .catalog-cta{
                display:inline-flex!important;
                width:100%!important;
                min-width:0!important;
                height:46px!important;
                min-height:46px!important;
                padding:0 10px!important;
                align-items:center!important;
                justify-content:center!important;
                border-radius:10px!important;
                font-size:13px!important;
                font-weight:900!important;
                line-height:1!important;
                white-space:nowrap!important;
                overflow:hidden!important;
                text-overflow:ellipsis!important;
                opacity:1!important;
                visibility:visible!important;
            }
        }
    </style>
</head>
<body class="static-site catalog-body <?= ($coursePage['layout'] ?? '') === 'compact' ? 'compact-catalog' : '' ?> <?= htmlspecialchars($coursePage['body_class'] ?? '') ?>">
<div class="site-shell">
    <header class="site-header compact-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php"><span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="68" height="68" decoding="async"></span><span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span></a>
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
    <main class="page-main catalog-page">
        <?php
            $heroImage = trim((string)($coursePage['hero_image'] ?? ''));
            $heroCssImage = $heroImage;
            if (str_starts_with($heroCssImage, 'assets/images/')) {
                $heroCssImage = '../images/' . substr($heroCssImage, strlen('assets/images/'));
            }
            $heroStyle = $heroImage !== ''
                ? "--catalog-hero-image:url('" . htmlspecialchars($heroCssImage, ENT_QUOTES) . "')"
                : '';
        ?>
        <section class="catalog-hero"<?= $heroStyle !== '' ? ' style="' . $heroStyle . '"' : '' ?>>
            <?php if ($heroImage !== ''): ?>
            <img class="catalog-hero-bg" src="<?= htmlspecialchars($heroImage) ?>" alt="" aria-hidden="true" decoding="async" fetchpriority="high">
            <span class="catalog-hero-overlay" aria-hidden="true"></span>
            <?php endif; ?>
            <div class="site-container reveal">
                <span class="hero-kicker"><i class="fa-solid fa-layer-group"></i> Talentteno Course Catalog</span>
                <h1><?= htmlspecialchars($coursePage['title']) ?></h1>
                <p><?= htmlspecialchars($coursePage['subtitle']) ?></p>
            </div>
        </section>
        <section class="catalog-section">
            <div class="site-container catalog-grid">
                <?php foreach ($coursePage['courses'] as $course): ?>
                <?php
                    $courseImage = tt_catalog_image_url($course['image'] ?? '');
                    if ($courseImage === '') {
                        $courseImage = tt_catalog_fallback_image($course);
                    }
                    $hasBrochure = !empty($course['id']) && tt_course_brochure_exists($course['brochure_file'] ?? '');
                    $enquiryHref = 'contact.php?course=' . rawurlencode($course['name']);
                    $downloadHref = $hasBrochure
                        ? 'download.php?id=' . (int)$course['id']
                        : 'download.php?title=' . rawurlencode($course['name']);
                    $courseHighlights = array_values(array_filter(array_map('trim', $course['items'] ?? [])));
                    $courseFeeValue = (float)($course['fee'] ?? 0);
                    $courseOriginalFeeValue = (float)($course['original_fee'] ?? 0);
                    $courseFeeLabel = $courseFeeValue > 0 ? tt_money($courseFeeValue) : 'Contact for fee';
                ?>
                <article class="catalog-card reveal" tabindex="0"
                    data-course-modal
                    data-title="<?= htmlspecialchars($course['name'], ENT_QUOTES) ?>"
                    data-category="<?= htmlspecialchars($course['category'] ?? '', ENT_QUOTES) ?>"
                    data-description="<?= htmlspecialchars($course['desc'] ?? '', ENT_QUOTES) ?>"
                    data-duration="<?= htmlspecialchars($course['duration'] ?? '', ENT_QUOTES) ?>"
                    data-fee="<?= htmlspecialchars($courseFeeLabel, ENT_QUOTES) ?>"
                    data-image="<?= htmlspecialchars($courseImage, ENT_QUOTES) ?>"
                    data-highlights="<?= htmlspecialchars(implode("\n", $courseHighlights), ENT_QUOTES) ?>"
                    data-enquire="<?= htmlspecialchars($enquiryHref, ENT_QUOTES) ?>"
                    data-download="<?= htmlspecialchars($downloadHref, ENT_QUOTES) ?>">
                    <?php if ($courseImage !== ''): ?>
                    <div class="catalog-image" style="--catalog-image-bg:url('<?= htmlspecialchars($courseImage, ENT_QUOTES) ?>')"><img src="<?= htmlspecialchars($courseImage) ?>" alt="<?= htmlspecialchars($course['name']) ?>" loading="lazy" decoding="async"></div>
                    <?php endif; ?>
                    <div class="catalog-icon"><i class="<?= htmlspecialchars(tt_icon_class($course['icon'])) ?>"></i></div>
                    <?php if (!empty($course['category'])): ?><span class="catalog-category"><?= htmlspecialchars($course['category']) ?></span><?php endif; ?>
                    <h2><?= htmlspecialchars($course['name']) ?></h2>
                    <?php if (!empty($course['desc'])): ?><p><?= htmlspecialchars($course['desc']) ?></p><?php endif; ?>
                    <?php if (!empty($course['items'])): ?>
                    <ul>
                        <?php foreach ($course['items'] as $item): ?><li><i class="fa-solid fa-check"></i> <?= htmlspecialchars($item) ?></li><?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <div class="catalog-price"><?php if ($courseOriginalFeeValue > $courseFeeValue && $courseFeeValue > 0): ?><del><?= tt_money($courseOriginalFeeValue) ?></del><?php endif; ?><strong><?= htmlspecialchars($courseFeeLabel) ?></strong></div>
                    <div class="catalog-actions">
                        <a class="catalog-detail-btn" href="<?= tt_h($enquiryHref) ?>"><i class="fa-solid fa-message"></i> Enquiry</a>
                        <a class="catalog-cta" href="<?= tt_h($downloadHref) ?>"><i class="fa-solid fa-download"></i> Download</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <div class="course-detail-modal" id="courseDetailModal" aria-hidden="true">
        <div class="course-detail-backdrop" data-close-course-detail></div>
        <div class="course-detail-panel" role="dialog" aria-modal="true" aria-labelledby="courseDetailTitle">
            <button class="course-detail-close" type="button" data-close-course-detail aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            <div class="course-detail-image" hidden><img src="" alt="" loading="lazy" decoding="async"></div>
            <span class="course-detail-category"></span>
            <h2 id="courseDetailTitle"></h2>
            <p class="course-detail-description"></p>
            <ul class="course-detail-highlights"></ul>
            <div class="course-detail-meta"><span class="course-detail-duration"></span><strong class="course-detail-fee"></strong></div>
            <div class="course-detail-actions"><a class="btn btn-secondary course-detail-enquire" href="contact.php">Enquire Now</a><a class="btn btn-primary course-detail-download" href="contact.php" hidden><i class="fa-solid fa-download"></i> Brochure</a></div>
        </div>
    </div>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260718-scrollsmooth1" defer></script>
</body>
</html>
