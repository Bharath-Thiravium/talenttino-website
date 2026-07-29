<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$courses = tt_courses_by_type('course');
if (!$courses) {
    $courses = [
        ['id' => 0, 'title' => 'Programming Languages', 'category' => 'Programming', 'description' => 'Learn C, C++, Java, Python, PHP, .NET and SQL with practical coding.', 'highlights' => "Live Coding\nProjects\nInterview Training", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Full Stack Development', 'category' => 'Development', 'description' => 'HTML, CSS, JavaScript, Bootstrap, React, Django, Node.js and MySQL.', 'highlights' => "Live Website\nInternship\nPlacement Support", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Digital Marketing', 'category' => 'Marketing', 'description' => 'SEO, Google Ads, Social Media Marketing, Email Marketing and Analytics.', 'highlights' => "Google Ads\nSEO\nLive Campaigns", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Data Analyst', 'category' => 'Analytics', 'description' => 'Excel, SQL, Power BI, Tableau and Python for business analytics.', 'highlights' => "Dashboards\nReports\nCase Studies", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Data Science & AI', 'category' => 'AI', 'description' => 'Machine Learning, Artificial Intelligence and Deep Learning.', 'highlights' => "Python\nMachine Learning\nAI Projects", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Cyber Security', 'category' => 'Security', 'description' => 'Ethical Hacking, Networking, Penetration Testing and Security Tools.', 'highlights' => "Kali Linux\nLive Labs\nCertification", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
        ['id' => 0, 'title' => 'Cloud Computing', 'category' => 'Cloud', 'description' => 'Learn AWS, Microsoft Azure, DevOps, Docker and Kubernetes.', 'highlights' => "AWS\nDocker\nKubernetes", 'duration' => '', 'fee' => 0, 'original_fee' => 0, 'brochure_file' => '', 'image' => '', 'is_featured' => 0],
    ];
}
$courseHeroImage = tt_optimized_image_url('assets/images/our trending.png', 1536);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'IT Courses in Madurai | Full Stack, Data Science, AI, Cyber Security',
        'description' => 'Explore Talentteno IT courses in Madurai including Full Stack Development, Data Science, AI, Cyber Security, Digital Marketing, UI/UX, Tally and programming with internship and placement support.',
        'canonical' => tt_abs_url('course.php'),
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Courses', 'url' => 'course.php'],
        ],
        'courses' => array_map(static fn(array $course): array => [
            'name' => $course['title'] ?? '',
            'desc' => $course['description'] ?? '',
        ], $courses),
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@1,700;1,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <?php $ttPageCss = tt_asset_url('assets/css/site-pages.min.css'); ?>
    <link rel="stylesheet" href="<?= tt_h($ttPageCss) ?>">
    <noscript><link rel="stylesheet" href="<?= tt_h($ttPageCss) ?>"></noscript>
    <link rel="stylesheet" href="<?= tt_h(tt_asset_url('assets/css/navbar.min.css')) ?>">
    <style>
        body.course-list-page .course-showcase-card{position:relative!important;display:flex!important;flex-direction:column!important;aspect-ratio:auto!important;min-height:460px!important;padding:0 22px 82px!important;overflow:hidden!important;cursor:pointer!important}
        body.course-list-page .course-showcase-card .course-image{width:calc(100% + 44px)!important;height:190px!important;min-height:190px!important;max-height:190px!important;margin:0 -22px 18px!important;border-radius:8px 8px 0 0!important;overflow:hidden!important}
        body.course-list-page .course-showcase-card .course-image img{width:100%!important;height:100%!important;object-fit:cover!important;object-position:center!important}
        body.course-list-page .course-showcase-card .course-card-top{position:static!important;margin:0 0 18px!important;opacity:1!important;visibility:visible!important}
        body.course-list-page .course-showcase-card h3{position:static!important;display:block!important;visibility:visible!important;opacity:1!important;margin:0 0 6px!important;color:#10172a!important;-webkit-text-fill-color:#10172a!important;background:none!important;-webkit-background-clip:border-box!important;background-clip:border-box!important;font-size:25px!important;font-weight:900!important;line-height:1.12!important;letter-spacing:0!important}
        body.course-list-page .course-showcase-card>p{position:static!important;visibility:visible!important;opacity:1!important;margin:0 0 12px!important;display:-webkit-box!important;-webkit-line-clamp:2!important;-webkit-box-orient:vertical!important;overflow:hidden!important;color:#52627a!important;-webkit-text-fill-color:#52627a!important;font-size:14px!important;line-height:1.45!important}
        body.course-list-page .course-showcase-card .course-highlights{display:none!important}
        body.course-list-page .course-showcase-card .course-footer{position:absolute!important;left:22px!important;right:22px!important;bottom:22px!important;display:block!important;margin:0!important;padding-top:12px!important;border-top:0!important;background:#fff!important}
        body.course-list-page .course-showcase-card .course-actions{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;width:100%!important}
        body.course-list-page .course-showcase-card .course-actions .btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:7px!important;width:100%!important;min-height:42px!important;height:42px!important;padding:0 12px!important;border-radius:8px!important;clip-path:none!important;font-size:13px!important;font-weight:900!important;text-decoration:none!important}
        body.course-list-page .course-showcase-card .course-enquiry-btn{background:#fff!important;color:#1554d1!important;-webkit-text-fill-color:#1554d1!important;border:1px solid rgba(79,140,255,.42)!important;box-shadow:none!important}
        body.course-list-page .course-showcase-card .course-download-btn{color:#fff!important;-webkit-text-fill-color:#fff!important;background:linear-gradient(135deg,#4f8cff 0%,#7c5cff 48%,#d91cf6 100%)!important;border:0!important}
        @media (max-width:700px){
            body.course-list-page .course-showcase-grid{width:min(100% - 28px,420px)!important;margin:0 auto!important;grid-template-columns:1fr!important;gap:24px!important}
            body.course-list-page .course-showcase-card{min-height:auto!important;padding:0!important;border-radius:18px!important;overflow:visible!important;box-shadow:0 22px 58px rgba(15,23,42,.10)!important}
            body.course-list-page .course-showcase-card .course-image{width:100%!important;height:210px!important;min-height:210px!important;max-height:210px!important;margin:0!important;border-radius:0!important}
            body.course-list-page .course-showcase-card .course-card-top{padding:24px 22px 0!important;margin:0!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important}
            body.course-list-page .course-showcase-card h3{margin:26px 22px 14px!important;font-size:clamp(23px,6vw,28px)!important;line-height:1.18!important}
            body.course-list-page .course-showcase-card>p{display:block!important;margin:0 22px 24px!important;overflow:visible!important;font-size:14.5px!important;line-height:1.65!important}
            body.course-list-page .course-showcase-card .course-footer{position:static!important;left:auto!important;right:auto!important;bottom:auto!important;margin:0!important;padding:14px 18px 18px!important;display:block!important;background:#fff!important;border-top:0!important}
            body.course-list-page .course-showcase-card .course-actions{display:grid!important;grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;gap:10px!important;width:100%!important}
            body.course-list-page .course-showcase-card .course-actions .btn{width:100%!important;min-width:0!important;min-height:46px!important;height:46px!important;border-radius:10px!important;gap:6px!important}
            body.course-list-page .course-showcase-card .course-actions i{display:inline!important}
        }
        body.course-list-page .course-showcase-card .course-footer{
            left:22px!important;
            right:22px!important;
            bottom:22px!important;
            width:auto!important;
            max-width:none!important;
            overflow:visible!important;
        }
        body.course-list-page .course-showcase-card .course-actions{
            width:100%!important;
            display:grid!important;
            grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
            align-items:center!important;
            gap:10px!important;
        }
        body.course-list-page .course-showcase-card .course-actions .btn{
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
        body.course-list-page .course-showcase-card .course-actions .btn i{
            display:none!important;
        }
        body.course-list-page .course-showcase-card .course-enquiry-btn{
            background:#ffffff!important;
            color:#1554d1!important;
            -webkit-text-fill-color:#1554d1!important;
            border:1px solid rgba(79,140,255,.46)!important;
            box-shadow:inset 0 0 0 1px rgba(255,255,255,.78)!important;
        }
        body.course-list-page .course-showcase-card .course-download-btn{
            background:linear-gradient(135deg,#4f8cff 0%,#7c5cff 48%,#d91cf6 100%)!important;
            color:#ffffff!important;
            -webkit-text-fill-color:#ffffff!important;
            border:1px solid transparent!important;
            box-shadow:0 12px 24px rgba(124,92,255,.24)!important;
        }
        body.course-list-page .course-showcase-card .course-actions .btn:hover{
            transform:translateY(-2px)!important;
            box-shadow:0 16px 30px rgba(37,99,235,.16)!important;
        }
        @media (max-width:700px){
            body.course-list-page .course-showcase-card .course-footer{
                position:static!important;
                left:auto!important;right:auto!important;bottom:auto!important;
                padding:14px 18px 18px!important;
                display:block!important;
                background:#fff!important;
            }
            body.course-list-page .course-showcase-card .course-actions{
                display:grid!important;
                grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
                gap:10px!important;
                width:100%!important;
            }
            body.course-list-page .course-showcase-card .course-actions .btn{
                width:100%!important;
                min-width:0!important;
                height:46px!important;
                min-height:46px!important;
                border-radius:10px!important;
                font-size:13px!important;
                white-space:nowrap!important;
                overflow:hidden!important;
                text-overflow:ellipsis!important;
            }
            body.course-list-page .course-showcase-card .course-actions i{
                display:inline!important;
            }
            body.course-list-page .course-showcase-card .course-image{
                width:100%!important;
                height:220px!important;
                min-height:220px!important;
                max-height:220px!important;
                margin:0!important;
                border-radius:0!important;
            }
        }
        body.course-list-page .course-showcase-card .course-image{
            background:#f4f8ff!important;
            display:block!important;
            width:calc(100% + 44px)!important;
            height:220px!important;
            min-height:220px!important;
            max-height:220px!important;
            margin:0 -22px 22px!important;
        }
        body.course-list-page .course-showcase-card .course-image img{
            display:block!important;
            width:100%!important;
            height:100%!important;
            object-fit:cover!important;
            object-position:center!important;
            background:transparent!important;
        }
        body.course-list-page .course-showcase-card:hover .course-image img{
            transform:none!important;
            filter:saturate(1.03) contrast(1.02)!important;
        }
        body.course-list-page .course-page-hero{
            position:relative!important;
            min-height:460px!important;
            padding:0!important;
            display:flex!important;
            align-items:center!important;
            overflow:hidden!important;
            isolation:isolate!important;
            background:#061631!important;
            text-align:left!important;
        }
        body.course-list-page .course-page-hero .course-hero-bg{
            position:absolute!important;
            inset:0!important;
            z-index:0!important;
            width:100%!important;
            height:100%!important;
            object-fit:cover!important;
            object-position:center right!important;
            opacity:1!important;
            filter:saturate(1.06) contrast(1.04)!important;
            pointer-events:none!important;
        }
        body.course-list-page .course-page-hero .course-hero-overlay{
            position:absolute!important;
            inset:0!important;
            z-index:1!important;
            background:linear-gradient(90deg,rgba(5,15,38,.96) 0%,rgba(8,29,74,.86) 42%,rgba(8,42,112,.24) 72%,rgba(5,15,38,.08) 100%)!important;
            pointer-events:none!important;
        }
        body.course-list-page .course-page-hero .site-container{
            position:relative!important;
            z-index:3!important;
            width:min(1200px,calc(100% - 48px))!important;
            margin:0 auto!important;
        }
        body.course-list-page .course-page-hero h1{
            max-width:620px!important;
            color:#fff!important;
            -webkit-text-fill-color:#fff!important;
            font-size:clamp(48px,5.8vw,82px)!important;
            line-height:.98!important;
            text-align:left!important;
        }
        body.course-list-page .course-page-hero p{
            max-width:680px!important;
            margin-left:0!important;
            margin-right:0!important;
            color:rgba(255,255,255,.94)!important;
            -webkit-text-fill-color:rgba(255,255,255,.94)!important;
            font-size:23px!important;
            line-height:1.55!important;
            text-align:left!important;
        }
        @media (max-width:900px){
            body.course-list-page .course-page-hero{
                min-height:430px!important;
            }
            body.course-list-page .course-page-hero .course-hero-bg{
                object-position:62% center!important;
            }
            body.course-list-page .course-page-hero .course-hero-overlay{
                background:linear-gradient(180deg,rgba(5,15,38,.96) 0%,rgba(8,29,74,.86) 54%,rgba(8,42,112,.52) 100%)!important;
            }
            body.course-list-page .course-page-hero h1{
                font-size:clamp(42px,10vw,64px)!important;
            }
            body.course-list-page .course-page-hero p{
                font-size:18px!important;
            }
        }
        @media (max-width:700px){
            html body.course-list-page .course-showcase-card{
                min-height:auto!important;
                padding:0!important;
            }
            html body.course-list-page .course-showcase-card .course-footer{
                position:static!important;
                left:auto!important;
                right:auto!important;
                bottom:auto!important;
                padding:14px 18px 18px!important;
                display:block!important;
                background:#fff!important;
            }
            html body.course-list-page .course-showcase-card .course-actions{
                display:grid!important;
                grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
                gap:10px!important;
                width:100%!important;
            }
            html body.course-list-page .course-showcase-card .course-actions .btn{
                width:100%!important;
                min-width:0!important;
                height:46px!important;
                min-height:46px!important;
                padding:0 10px!important;
                font-size:13px!important;
                white-space:nowrap!important;
                overflow:hidden!important;
                text-overflow:ellipsis!important;
            }
        }
        /* Final course-card action fix: keep Enquiry and Download visible on every card. */
        html body.course-list-page .course-showcase-grid{
            align-items:stretch!important;
        }
        html body.course-list-page .course-card.course-showcase-card{
            height:auto!important;
            min-height:520px!important;
            max-height:none!important;
            display:grid!important;
            grid-template-rows:auto auto auto 1fr auto!important;
            padding:0 22px 22px!important;
            overflow:hidden!important;
            border-radius:18px!important;
            background:#fff!important;
        }
        html body.course-list-page .course-showcase-card .course-image{
            width:calc(100% + 44px)!important;
            height:210px!important;
            min-height:210px!important;
            max-height:210px!important;
            margin:0 -22px 18px!important;
            border-radius:18px 18px 0 0!important;
            overflow:hidden!important;
        }
        html body.course-list-page .course-showcase-card .course-card-top{
            margin:0 0 18px!important;
            display:flex!important;
            align-items:center!important;
            justify-content:space-between!important;
            gap:12px!important;
        }
        html body.course-list-page .course-card.course-showcase-card h3{
            min-height:58px!important;
            margin:0 0 12px!important;
            display:-webkit-box!important;
            -webkit-line-clamp:2!important;
            -webkit-box-orient:vertical!important;
            overflow:hidden!important;
            font-size:clamp(22px,2.2vw,26px)!important;
            line-height:1.12!important;
        }
        html body.course-list-page .course-card.course-showcase-card > p{
            min-height:64px!important;
            margin:0 0 18px!important;
            display:-webkit-box!important;
            -webkit-line-clamp:3!important;
            -webkit-box-orient:vertical!important;
            overflow:hidden!important;
            font-size:14px!important;
            line-height:1.52!important;
        }
        html body.course-list-page .course-showcase-card .course-footer{
            position:static!important;
            inset:auto!important;
            width:100%!important;
            margin:auto 0 0!important;
            padding:16px 0 0!important;
            display:block!important;
            border-top:1px solid rgba(37,99,235,.12)!important;
            background:#fff!important;
        }
        html body.course-list-page .course-showcase-card .course-actions{
            display:grid!important;
            grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
            gap:10px!important;
            width:100%!important;
        }
        html body.course-list-page .course-showcase-card .course-actions .btn{
            width:100%!important;
            min-width:0!important;
            height:46px!important;
            min-height:46px!important;
            padding:0 10px!important;
            border-radius:10px!important;
            display:inline-flex!important;
            align-items:center!important;
            justify-content:center!important;
            gap:7px!important;
            font-size:13px!important;
            font-weight:900!important;
            line-height:1!important;
            white-space:nowrap!important;
            overflow:hidden!important;
            text-overflow:ellipsis!important;
            transform:none!important;
        }
        html body.course-list-page .course-showcase-card .course-actions .btn i{
            display:inline-flex!important;
            flex:0 0 auto!important;
        }
        @media (max-width:700px){
            html body.course-list-page .course-showcase-grid{
                width:min(100% - 28px,420px)!important;
                grid-template-columns:1fr!important;
                gap:24px!important;
                margin-inline:auto!important;
            }
            html body.course-list-page .course-card.course-showcase-card{
                min-height:0!important;
                padding:0 18px 18px!important;
                grid-template-rows:auto auto auto auto auto!important;
            }
            html body.course-list-page .course-showcase-card .course-image{
                width:calc(100% + 36px)!important;
                height:204px!important;
                min-height:204px!important;
                max-height:204px!important;
                margin:0 -18px 18px!important;
            }
            html body.course-list-page .course-showcase-card .course-card-top{
                padding:0!important;
                margin:0 0 18px!important;
            }
            html body.course-list-page .course-card.course-showcase-card h3{
                min-height:0!important;
                margin:0 0 12px!important;
                font-size:24px!important;
            }
            html body.course-list-page .course-card.course-showcase-card > p{
                min-height:0!important;
                margin:0 0 18px!important;
                -webkit-line-clamp:3!important;
            }
            html body.course-list-page .course-showcase-card .course-footer{
                margin:0!important;
                padding:14px 0 0!important;
            }
            html body.course-list-page .course-showcase-card .course-actions .btn{
                height:44px!important;
                min-height:44px!important;
                font-size:12px!important;
            }
        }
        @media (max-width:700px){
            html body.course-list-page .course-showcase-grid{
                width:min(calc(100vw - 30px),386px)!important;
                gap:18px!important;
            }
            html body.course-list-page .course-card.course-showcase-card{
                display:flex!important;
                flex-direction:column!important;
                align-items:stretch!important;
                min-height:0!important;
                padding:0!important;
                border-radius:14px!important;
                overflow:hidden!important;
            }
            html body.course-list-page .course-showcase-card .course-image{
                width:100%!important;
                height:118px!important;
                min-height:118px!important;
                max-height:118px!important;
                margin:0!important;
                border-radius:0!important;
            }
            html body.course-list-page .course-showcase-card .course-image img{
                object-fit:cover!important;
                object-position:center!important;
            }
            html body.course-list-page .course-showcase-card .course-card-top{
                width:100%!important;
                padding:16px 24px 0!important;
                margin:0 0 18px!important;
                display:grid!important;
                grid-template-columns:1fr!important;
                justify-items:center!important;
                gap:12px!important;
                text-align:center!important;
            }
            html body.course-list-page .course-showcase-card .course-card-top i{
                width:50px!important;
                height:50px!important;
                min-width:50px!important;
                margin:0!important;
                font-size:22px!important;
                border-radius:11px!important;
            }
            html body.course-list-page .course-showcase-card .course-level{
                position:absolute!important;
                top:128px!important;
                right:20px!important;
                width:auto!important;
                max-width:118px!important;
                min-height:28px!important;
                padding:0 14px!important;
                display:inline-flex!important;
                align-items:center!important;
                justify-content:center!important;
                font-size:12px!important;
                line-height:1!important;
                white-space:nowrap!important;
            }
            html body.course-list-page .course-card.course-showcase-card h3{
                width:100%!important;
                min-height:0!important;
                margin:0!important;
                padding:0 24px!important;
                display:block!important;
                text-align:center!important;
                font-size:25px!important;
                line-height:1.2!important;
            }
            html body.course-list-page .course-card.course-showcase-card > p{
                width:100%!important;
                min-height:0!important;
                margin:0!important;
                padding:66px 24px 38px!important;
                display:block!important;
                overflow:visible!important;
                text-align:center!important;
                font-size:14px!important;
                line-height:1.55!important;
            }
            html body.course-list-page .course-showcase-card .course-footer{
                width:100%!important;
                margin:0!important;
                padding:0 24px 20px!important;
                border-top:0!important;
            }
            html body.course-list-page .course-showcase-card .course-actions{
                width:100%!important;
                display:flex!important;
                justify-content:center!important;
                gap:10px!important;
            }
            html body.course-list-page .course-showcase-card .course-actions .btn{
                flex:0 0 auto!important;
                width:auto!important;
                min-width:82px!important;
                height:46px!important;
                min-height:46px!important;
                padding:0 14px!important;
                font-size:12px!important;
                border-radius:9px!important;
            }
            html body.course-list-page .course-showcase-card .course-actions .btn i{
                display:none!important;
            }
        }
        /* Final all-course card alignment: every card uses the same rows. */
        html body.course-list-page .course-showcase-grid{
            width:min(1220px,calc(100% - 48px))!important;
            margin:0 auto!important;
            display:grid!important;
            grid-template-columns:repeat(3,minmax(0,1fr))!important;
            align-items:stretch!important;
            gap:22px!important;
        }
        html body.course-list-page .course-card.course-showcase-card{
            position:relative!important;
            height:100%!important;
            min-height:630px!important;
            display:grid!important;
            grid-template-rows:220px 72px 74px 104px 1fr 68px!important;
            padding:0 22px 20px!important;
            border-radius:18px!important;
            overflow:hidden!important;
            background:#fff!important;
            border:1px solid rgba(37,99,235,.16)!important;
            box-shadow:0 18px 46px rgba(15,23,42,.08)!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-image{
            grid-row:1!important;
            width:calc(100% + 44px)!important;
            height:220px!important;
            min-height:220px!important;
            max-height:220px!important;
            margin:0 -22px!important;
            display:block!important;
            border-radius:18px 18px 0 0!important;
            overflow:hidden!important;
            background:linear-gradient(135deg,#071a3d 0%,#0b55ff 48%,#eef6ff 100%)!important;
        }
        html body.course-list-page .course-card.course-showcase-card:not(.has-course-image)::before{
            content:""!important;
            grid-row:1!important;
            width:calc(100% + 44px)!important;
            height:220px!important;
            margin:0 -22px!important;
            display:block!important;
            border-radius:18px 18px 0 0!important;
            background:radial-gradient(circle at 22% 18%,rgba(255,255,255,.42),transparent 26%),linear-gradient(135deg,#071a3d 0%,#1458e8 52%,#9f26f4 100%)!important;
        }
        html body.course-list-page .course-card.course-showcase-card:not(.has-course-image)::after{
            content:"\f121"!important;
            position:absolute!important;
            top:74px!important;
            left:50%!important;
            width:72px!important;
            height:72px!important;
            display:grid!important;
            place-items:center!important;
            transform:translateX(-50%)!important;
            border-radius:18px!important;
            color:#fff!important;
            -webkit-text-fill-color:#fff!important;
            background:rgba(255,255,255,.16)!important;
            border:1px solid rgba(255,255,255,.28)!important;
            font-family:"Font Awesome 6 Free"!important;
            font-weight:900!important;
            font-size:30px!important;
            z-index:1!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-image img{
            width:100%!important;
            height:100%!important;
            display:block!important;
            object-fit:cover!important;
            object-position:center!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-card-top{
            grid-row:2!important;
            min-height:72px!important;
            margin:0!important;
            padding:18px 0 0!important;
            display:flex!important;
            align-items:flex-start!important;
            justify-content:space-between!important;
            gap:12px!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-icon{
            width:48px!important;
            height:48px!important;
            min-width:48px!important;
            margin:0!important;
            display:inline-grid!important;
            place-items:center!important;
            border-radius:10px!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-pill,
        html body.course-list-page .course-card.course-showcase-card .course-level{
            position:static!important;
            width:auto!important;
            max-width:calc(100% - 62px)!important;
            min-height:28px!important;
            margin:0!important;
            display:inline-flex!important;
            align-items:center!important;
            justify-content:center!important;
            white-space:nowrap!important;
            overflow:hidden!important;
            text-overflow:ellipsis!important;
        }
        html body.course-list-page .course-card.course-showcase-card h3{
            grid-row:3!important;
            min-height:74px!important;
            margin:0!important;
            padding:0!important;
            display:-webkit-box!important;
            -webkit-line-clamp:2!important;
            -webkit-box-orient:vertical!important;
            overflow:hidden!important;
            color:#10172a!important;
            -webkit-text-fill-color:#10172a!important;
            font-size:clamp(23px,2vw,27px)!important;
            line-height:1.15!important;
            text-align:left!important;
        }
        html body.course-list-page .course-card.course-showcase-card > p{
            grid-row:4!important;
            min-height:104px!important;
            margin:0!important;
            padding:0!important;
            display:-webkit-box!important;
            -webkit-line-clamp:4!important;
            -webkit-box-orient:vertical!important;
            overflow:hidden!important;
            color:#52627a!important;
            -webkit-text-fill-color:#52627a!important;
            font-size:14px!important;
            line-height:1.55!important;
            text-align:left!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-highlights{
            grid-row:5!important;
            display:none!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-footer{
            grid-row:6!important;
            align-self:end!important;
            position:static!important;
            inset:auto!important;
            width:100%!important;
            margin:0!important;
            padding:16px 0 0!important;
            display:block!important;
            border-top:1px solid rgba(37,99,235,.12)!important;
            background:#fff!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-actions{
            width:100%!important;
            display:grid!important;
            grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;
            gap:10px!important;
        }
        html body.course-list-page .course-card.course-showcase-card .course-actions .btn{
            width:100%!important;
            min-width:0!important;
            height:46px!important;
            min-height:46px!important;
            padding:0 10px!important;
            display:inline-flex!important;
            align-items:center!important;
            justify-content:center!important;
            gap:7px!important;
            border-radius:10px!important;
            font-size:13px!important;
            font-weight:900!important;
            line-height:1!important;
            white-space:nowrap!important;
            overflow:hidden!important;
            text-overflow:ellipsis!important;
        }
        @media(max-width:1100px){
            html body.course-list-page .course-showcase-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}
        }
        @media(max-width:700px){
            html body.course-list-page .course-showcase-grid{
                width:min(100% - 28px,420px)!important;
                grid-template-columns:1fr!important;
                gap:20px!important;
            }
            html body.course-list-page .course-card.course-showcase-card{
                min-height:0!important;
                grid-template-rows:180px 68px auto auto auto!important;
                padding:0 18px 18px!important;
            }
            html body.course-list-page .course-card.course-showcase-card .course-image,
            html body.course-list-page .course-card.course-showcase-card:not(.has-course-image)::before{
                width:calc(100% + 36px)!important;
                height:180px!important;
                min-height:180px!important;
                max-height:180px!important;
                margin:0 -18px!important;
            }
            html body.course-list-page .course-card.course-showcase-card:not(.has-course-image)::after{
                top:54px!important;
            }
            html body.course-list-page .course-card.course-showcase-card h3{
                min-height:0!important;
                margin:0 0 12px!important;
                font-size:24px!important;
            }
            html body.course-list-page .course-card.course-showcase-card > p{
                min-height:0!important;
                margin:0 0 18px!important;
                -webkit-line-clamp:4!important;
            }
            html body.course-list-page .course-card.course-showcase-card .course-footer{
                padding-top:14px!important;
            }
        }
    </style>
</head>
<body class="static-site course-list-page">
<div class="site-shell">
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    <main class="page-main">
        <section class="page-hero course-page-hero">
            <img class="course-hero-bg" src="<?= tt_h($courseHeroImage) ?>" alt="" aria-hidden="true" decoding="async" fetchpriority="high">
            <span class="course-hero-overlay" aria-hidden="true"></span>
            <div class="site-container reveal"><span class="hero-kicker"><i class="fa-solid fa-book-open"></i> Basic to Advanced IT Training</span><h1>Our Trending Courses</h1><p>Build your career with industry-oriented training, live projects, internships, certifications, and placement assistance.</p></div>
        </section>
        <section class="section course-showcase-section">
            <div class="site-container course-showcase-grid">
                <?php foreach ($courses as $course): ?>
                <?php
                    $courseImage = tt_course_image_url($course['image'] ?? '');
                    $hasBrochure = tt_course_brochure_exists($course['brochure_file'] ?? '');
                    $downloadHref = $hasBrochure && !empty($course['id'])
                        ? 'download.php?id=' . (int)$course['id']
                        : 'download.php?title=' . rawurlencode($course['title']);
                    $enquiryHref = 'contact.php?course=' . rawurlencode($course['title']);
                    $courseFee = (float)($course['fee'] ?? 0) > 0 ? tt_money($course['fee']) : '';
                    $highlights = tt_course_highlights($course);
                ?>
                <article class="course-card course-showcase-card <?= $course['is_featured'] ? 'featured-course' : '' ?> <?= $courseImage !== '' ? 'has-course-image' : '' ?> reveal"
                    role="button"
                    tabindex="0"
                    aria-label="View <?= tt_h($course['title']) ?> course details"
                    data-course-modal
                    data-title="<?= tt_h($course['title']) ?>"
                    data-category="<?= tt_h($course['category']) ?>"
                    data-description="<?= tt_h($course['description']) ?>"
                    data-duration="<?= tt_h($course['duration']) ?>"
                    data-fee="<?= tt_h($courseFee) ?>"
                    data-highlights="<?= tt_h(implode("\n", $highlights)) ?>"
                    data-download="<?= tt_h($downloadHref) ?>"
                    data-enquire="<?= tt_h($enquiryHref) ?>"
                    data-image="<?= tt_h($courseImage) ?>">
                    <?php if ($courseImage !== ''): ?>
                    <div class="course-image">
                        <img src="<?= tt_h($courseImage) ?>" alt="<?= tt_h($course['title']) ?>" loading="lazy" decoding="async">
                    </div>
                    <?php endif; ?>
                    <div class="course-card-top">
                        <div class="course-icon"><i class="fa-solid <?= tt_h(tt_course_icon($course['category'])) ?>"></i></div>
                        <span class="course-pill"><?= tt_h($course['category']) ?></span>
                    </div>
                    <h3><?= tt_h($course['title']) ?></h3>
                    <p><?= tt_h($course['description']) ?></p>
                    <ul class="course-highlights">
                        <?php foreach ($highlights as $highlight): ?><li><i class="fa-solid fa-check"></i> <?= tt_h($highlight) ?></li><?php endforeach; ?>
                    </ul>
                    <div class="course-footer">
                        <div class="course-actions">
                            <a class="btn btn-secondary course-enquiry-btn" href="<?= tt_h($enquiryHref) ?>"><i class="fa-solid fa-message"></i> Enquiry</a>
                            <a class="btn btn-primary course-download-btn" href="<?= tt_h($downloadHref) ?>"><i class="fa-solid fa-download"></i> Syllabus</a>
                        </div>
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
            <div class="course-detail-actions"><a class="btn btn-secondary course-detail-enquire" href="contact.php"><i class="fa-solid fa-message"></i> Enquire Now</a><a class="btn btn-primary course-detail-download" href="contact.php"><i class="fa-solid fa-download"></i> View Syllabus</a></div>
        </div>
    </div>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="<?= tt_h(tt_asset_url('assets/js/site-pages.min.js')) ?>" defer></script>
</body>
</html>
