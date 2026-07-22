<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$homePhoneLink = preg_replace('/\D+/', '', (string)($settings['phone1'] ?? ''));
$homeWhatsappPhone = '918248415023';
$homeWhatsappUrl = 'https://wa.me/' . $homeWhatsappPhone . '?text=' . rawurlencode('Hello Talentteno, I would like course information.');
$allCourses = tt_courses();
$featuredCourses = tt_courses(6, true);
$services = tt_services(6);
$testimonials = tt_testimonials(3);
$homeHeroSlides = tt_home_slider_images();
if (empty($homeHeroSlides)) {
    $homeHeroSlides = [['image' => 'assets/images/home.webp', 'title' => '']];
}
$homeSliderCount = count($homeHeroSlides);
$homeFirstHeroImage = (string)($homeHeroSlides[0]['image'] ?? 'assets/images/home.webp');
$homeFirstHeroMobileImage = (string)($homeHeroSlides[0]['mobile_image'] ?? $homeFirstHeroImage);
$homeFirstHeroPreload = tt_home_optimized_image($homeFirstHeroImage, 1400) ?: $homeFirstHeroImage;
$homeFirstHeroSrcset = tt_home_optimized_srcset($homeFirstHeroImage, [430, 900, 1400]);
$homeFirstHeroMobilePreload = tt_home_optimized_image($homeFirstHeroMobileImage, 430) ?: $homeFirstHeroMobileImage;
$homeFirstHeroMobileSrcset = tt_home_optimized_srcset($homeFirstHeroMobileImage, [430, 900]);
$homeFormResult = null;
function tt_home_css_asset_url(string $url): string
{
    if (str_starts_with($url, 'uploads/')) {
        return '../../' . $url;
    }

    if (str_starts_with($url, 'assets/images/')) {
        return '../images/' . substr($url, strlen('assets/images/'));
    }

    return $url;
}
$fallbackCourses = [
    ['title' => 'Full Stack Development', 'category' => 'Development', 'short_desc' => 'Frontend, backend, database and deployment training with project practice.', 'description' => 'Complete full stack development training with live project support.', 'fee' => 15000, 'original_fee' => 25000],
    ['title' => 'Data Science & AI', 'category' => 'Data & AI', 'short_desc' => 'Python, analytics, machine learning basics and AI project workflow.', 'description' => 'Practical data science and AI training for job-ready skills.', 'fee' => 18000, 'original_fee' => 30000],
    ['title' => 'Cyber Security', 'category' => 'Security', 'short_desc' => 'Security fundamentals, ethical hacking basics and practical defence concepts.', 'description' => 'Cyber security combo training with practical lab guidance.', 'fee' => 12000, 'original_fee' => 20000],
    ['title' => 'Digital Marketing', 'category' => 'Marketing', 'short_desc' => 'SEO, social media, paid ads and campaign planning for business growth.', 'description' => 'Digital marketing course with campaign practice and portfolio tasks.', 'fee' => 9000, 'original_fee' => 15000],
    ['title' => 'UI / UX Design', 'category' => 'Design', 'short_desc' => 'User research, wireframes, visual design and portfolio-ready app screens.', 'description' => 'UI UX design training with practical tools and design projects.', 'fee' => 10000, 'original_fee' => 18000],
    ['title' => 'Cloud Computing', 'category' => 'Development', 'short_desc' => 'Cloud basics, server setup, deployment flow and practical infrastructure skills.', 'description' => 'Cloud computing foundation course with deployment practice.', 'fee' => 11000, 'original_fee' => 19000],
];
$fallbackServices = [
    ['icon' => 'fa-laptop-code', 'title' => 'Live Project Training', 'short_desc' => 'Practice each concept through real project tasks and mentor review.', 'description' => ''],
    ['icon' => 'fa-user-graduate', 'title' => 'Free Internship Support', 'short_desc' => 'Get guided internship exposure after completing course modules.', 'description' => ''],
    ['icon' => 'fa-comments', 'title' => 'Spoken English Support', 'short_desc' => 'Improve interview communication with practical speaking guidance.', 'description' => ''],
    ['icon' => 'fa-briefcase', 'title' => 'Placement Assistance', 'short_desc' => 'Resume support, interview practice and career opportunity guidance.', 'description' => ''],
];
$allCourses = $allCourses ?: $fallbackCourses;
$featuredCourses = $featuredCourses ?: array_slice($fallbackCourses, 0, 2);
$services = $services ?: $fallbackServices;
$popularTracks = $allCourses;
$careerSteps = [
    ['icon' => 'fa-user-check', 'title' => 'Career Counselling', 'text' => 'Choose the right course after a quick skill and goal discussion.'],
    ['icon' => 'fa-laptop-code', 'title' => 'Hands-on Training', 'text' => 'Build strong fundamentals with lab sessions, assignments and mentor review.'],
    ['icon' => 'fa-diagram-project', 'title' => 'Live Project Practice', 'text' => 'Work on portfolio-ready projects that show real implementation skills.'],
    ['icon' => 'fa-briefcase', 'title' => 'Interview Preparation', 'text' => 'Get resume guidance, mock interviews and placement assistance.'],
];
$homeHighlights = [
    ['icon' => 'fa-chalkboard-user', 'title' => 'Mentor-led batches', 'text' => 'Small, focused sessions with trainers who explain concepts from basics to advanced level.'],
    ['icon' => 'fa-business-time', 'title' => 'Flexible timing', 'text' => 'Weekday and weekend learning options for students, graduates and working professionals.'],
    ['icon' => 'fa-certificate', 'title' => 'Certification support', 'text' => 'Complete course certification with project evaluation and internship guidance.'],
];
$modelTeam = [
    ['name' => 'Senior Full Stack Mentor', 'role' => 'Web Development Trainer', 'image' => 'assets/images/home1.webp'],
    ['name' => 'Data & AI Coach', 'role' => 'Python and Analytics Mentor', 'image' => 'uploads/media/data-science-ai-20260703-133112-527863.png'],
    ['name' => 'Career Guidance Lead', 'role' => 'Interview Preparation Mentor', 'image' => 'assets/images/home2.webp'],
    ['name' => 'Digital Skills Trainer', 'role' => 'Marketing and Design Mentor', 'image' => 'assets/images/home3.webp'],
];
$learningNotes = [
    ['title' => 'How to choose the right IT course after college', 'image' => 'assets/images/%20IT%20course%20after%20college.png', 'tag' => 'Career Guide'],
    ['title' => 'Why live projects matter for freshers', 'image' => 'assets/images/live%20projects%20matter.png', 'tag' => 'Project Practice'],
    ['title' => 'Interview preparation checklist for IT roles', 'image' => 'assets/images/Interview%20preparation.png', 'tag' => 'Placement'],
];
$reviewItems = $testimonials ?: [
    ['student_name' => 'Talentteno Student', 'course' => 'Full Stack Development', 'review' => 'The classes were practical and the project tasks helped me understand real development flow.', 'rating' => 5],
    ['student_name' => 'Career Switcher', 'course' => 'Data Science', 'review' => 'Mentor guidance, assignments and interview support made the learning path clear.', 'rating' => 5],
];
$reviewShowcaseImages = tt_review_showcase();
$coursePathTracks = [
    ['course' => 'Full Stack Development', 'step' => 'Step 01', 'title' => 'Full stack project training', 'desc' => 'Build frontend, backend, database and deployment skills through practical project work.', 'image' => 'uploads/media/full-stack-development-20260703-133158-761383.png'],
    ['course' => 'Data Science & AI', 'step' => 'Step 02', 'title' => 'Data science and AI practice', 'desc' => 'Learn Python, analytics, machine learning basics and AI workflow through guided practical tasks.', 'image' => 'uploads/media/data-science-ai-20260703-133112-527863.png'],
    ['course' => 'Cyber Security', 'step' => 'Step 03', 'title' => 'Cyber security lab training', 'desc' => 'Practice security fundamentals, guided lab workflows and beginner-friendly cyber project tasks.', 'image' => 'uploads/media/cyber-security-20260703-133329-242125.png'],
    ['course' => 'Digital Marketing', 'step' => 'Step 04', 'title' => 'Digital marketing projects', 'desc' => 'Build practical confidence with campaign planning, SEO basics, social media workflow and reporting.', 'image' => 'uploads/media/digital-marketing-20260703-133146-981935.png'],
    ['course' => 'UI / UX and Design', 'step' => 'Step 05', 'title' => 'UI / UX portfolio guidance', 'desc' => 'Learn design foundations, interface planning, tool practice and portfolio-ready project presentation.', 'image' => 'assets/images/design%20.png'],
];

function tt_home_course_icon(array $course): string
{
    $text = strtolower(trim(($course['title'] ?? '') . ' ' . ($course['category'] ?? '')));
    $map = [
        'cyber' => 'fa-shield-halved',
        'security' => 'fa-shield-halved',
        'full stack' => 'fa-code',
        'web' => 'fa-code',
        'programming' => 'fa-terminal',
        'digital marketing' => 'fa-bullhorn',
        'marketing' => 'fa-bullhorn',
        'data science' => 'fa-chart-line',
        'data analyst' => 'fa-chart-line',
        'analytics' => 'fa-chart-line',
        'artificial intelligence' => 'fa-brain',
        'machine learning' => 'fa-brain',
        'ai' => 'fa-brain',
        'cloud' => 'fa-cloud-arrow-up',
        'ui' => 'fa-pen-ruler',
        'ux' => 'fa-pen-ruler',
        'design' => 'fa-pen-ruler',
        'tally' => 'fa-calculator',
        'account' => 'fa-calculator',
        'testing' => 'fa-vial-circle-check',
        'hardware' => 'fa-microchip',
        'database' => 'fa-database',
    ];

    foreach ($map as $needle => $icon) {
        if (strpos($text, $needle) !== false) {
            return $icon;
        }
    }

    return tt_course_icon($course['category'] ?? '');
}

function tt_home_course_summary(array $course): string
{
    $text = strtolower(trim(($course['title'] ?? '') . ' ' . ($course['category'] ?? '')));
    $summaries = [
        'cyber' => 'Network security, ethical hacking and firewall practice with career guidance.',
        'security' => 'Network security, ethical hacking and firewall practice with career guidance.',
        'full stack' => 'Frontend, backend, database and deployment training with live projects.',
        'web' => 'Build responsive websites and real web applications from basics to deployment.',
        'digital marketing' => 'SEO, social media, ads and analytics practice for business campaigns.',
        'marketing' => 'SEO, social media, ads and analytics practice for business campaigns.',
        'data science' => 'Python, analytics, visualization and machine learning with project tasks.',
        'data analyst' => 'Excel, SQL, Python and dashboard skills for data analyst roles.',
        'artificial intelligence' => 'AI, ML, NLP and model-building concepts with practical workflows.',
        'machine learning' => 'AI, ML, NLP and model-building concepts with practical workflows.',
        'cloud' => 'Cloud fundamentals, server setup and deployment practice for modern apps.',
        'ui' => 'Design user-friendly screens, prototypes and portfolios with practical tools.',
        'ux' => 'Design user-friendly screens, prototypes and portfolios with practical tools.',
        'tally' => 'Accounting, GST and business reporting practice for office-ready skills.',
    ];

    foreach ($summaries as $needle => $summary) {
        if (strpos($text, $needle) !== false) {
            return $summary;
        }
    }

    $summary = trim((string)(($course['short_desc'] ?? '') ?: ($course['description'] ?? '')));
    return $summary !== '' ? $summary : 'Practical training with projects, certification and placement support.';
}

function tt_home_course_examples(array $course): string
{
    $text = strtolower(trim(($course['title'] ?? '') . ' ' . ($course['category'] ?? '')));
    $examples = [
        'cyber' => ['Secure a sample office network', 'Practice ethical hacking lab tasks', 'Configure firewall and security reports'],
        'security' => ['Secure a sample office network', 'Practice ethical hacking lab tasks', 'Configure firewall and security reports'],
        'full stack' => ['Build a login-based web app', 'Connect frontend, backend and database', 'Deploy a portfolio-ready project'],
        'web' => ['Build a responsive business website', 'Create forms, dashboards and database pages', 'Deploy a portfolio-ready project'],
        'digital marketing' => ['Create an SEO plan for a local business', 'Run sample social media ad creatives', 'Track leads with campaign analytics'],
        'marketing' => ['Create an SEO plan for a local business', 'Run sample social media ad creatives', 'Track leads with campaign analytics'],
        'data science' => ['Clean and analyze a real dataset', 'Create charts and dashboard insights', 'Build a basic prediction model'],
        'data analyst' => ['Clean Excel and SQL data', 'Create dashboard reports', 'Present insights from sample business data'],
        'artificial intelligence' => ['Train a basic ML model', 'Work with NLP or computer vision examples', 'Build an AI mini project'],
        'machine learning' => ['Train a basic ML model', 'Work with NLP or computer vision examples', 'Build an AI mini project'],
        'cloud' => ['Set up a cloud server', 'Deploy a web application', 'Practice hosting and monitoring basics'],
        'ui' => ['Design a mobile app screen', 'Create clickable prototypes', 'Build a portfolio case study'],
        'ux' => ['Design a mobile app screen', 'Create clickable prototypes', 'Build a portfolio case study'],
        'tally' => ['Create GST billing entries', 'Prepare business reports', 'Practice accounts workflow examples'],
    ];

    foreach ($examples as $needle => $items) {
        if (strpos($text, $needle) !== false) {
            return implode("\n", $items);
        }
    }

    return "Work on guided practical tasks\nBuild a course-related mini project\nPrepare examples for your portfolio";
}

function tt_home_course_image(array $course): string
{
    $managedImage = tt_course_image_url($course['image'] ?? '');
    if ($managedImage !== '') {
        return $managedImage;
    }

    $text = strtolower(trim(($course['title'] ?? '') . ' ' . ($course['category'] ?? '')));
    $map = [
        'cloud' => 'uploads/media/cloud-computing-20260703-133220-323189.png',
        'cyber' => 'uploads/media/cyber-security-20260703-133329-242125.png',
        'security' => 'uploads/media/cyber-security-20260703-133329-242125.png',
        'data analyst' => 'uploads/media/data-analyst-20260703-133130-702998.png',
        'data science' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'artificial intelligence' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'machine learning' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'ai' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'digital marketing' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'marketing' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'full stack' => 'uploads/media/full-stack-development-20260703-133158-761383.png',
        'web' => 'uploads/media/full-stack-development-20260703-133158-761383.png',
        'programming' => 'uploads/media/programming-languages-20260703-133210-630417.png',
    ];

    foreach ($map as $needle => $image) {
        if (strpos($text, $needle) !== false) {
            return $image;
        }
    }

    return 'assets/images/home2.webp';
}

function tt_home_optimized_base(string $image): string
{
    $path = (string)(parse_url($image, PHP_URL_PATH) ?: $image);
    $file = pathinfo(rawurldecode(basename($path)), PATHINFO_FILENAME);
    $file = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $file), '-'));
    return $file !== '' ? $file : 'image';
}

function tt_home_optimized_image(string $image, int $width): string
{
    $name = tt_home_optimized_base($image) . '-w' . $width . '.webp';
    $relative = 'uploads/optimized/' . $name;
    return is_file(__DIR__ . '/' . $relative) ? $relative : '';
}

function tt_home_optimized_srcset(string $image, array $widths): string
{
    $items = [];
    foreach ($widths as $width) {
        $candidate = tt_home_optimized_image($image, (int)$width);
        if ($candidate !== '') {
            $items[] = $candidate . ' ' . (int)$width . 'w';
        }
    }

    return implode(', ', $items);
}

function tt_home_image_src(string $image, int $preferredWidth): string
{
    return tt_home_optimized_image($image, $preferredWidth) ?: $image;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['form_source'] ?? '') === 'home_signup') {
    $homeFormResult = tt_submit_enquiry($_POST, 'enquiry');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php tt_render_seo([
        'title' => 'Talentteno Institute | Best IT Training Institute in Madurai',
        'description' => 'Join Talentteno Institute in Madurai for practical IT courses, live projects, free internship, spoken English support, certification and placement assistance.',
        'canonical' => tt_abs_url('index.php'),
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => 'index.php'],
        ],
    ]); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <?php if ($homeFirstHeroPreload !== ''): ?>
    <link rel="preload" as="image" href="<?= tt_h($homeFirstHeroPreload) ?>"<?= $homeFirstHeroSrcset !== '' ? ' imagesrcset="' . tt_h($homeFirstHeroSrcset) . '" imagesizes="100vw"' : '' ?> media="(min-width: 768px)" fetchpriority="high">
    <?php endif; ?>
    <?php if ($homeFirstHeroMobilePreload !== ''): ?>
    <link rel="preload" as="image" href="<?= tt_h($homeFirstHeroMobilePreload) ?>"<?= $homeFirstHeroMobileSrcset !== '' ? ' imagesrcset="' . tt_h($homeFirstHeroMobileSrcset) . '" imagesizes="100vw"' : '' ?> media="(max-width: 767px)" fetchpriority="high">
    <?php endif; ?>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Space+Grotesk:wght@600;700&amp;display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Space+Grotesk:wght@600;700&amp;display=swap">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </noscript>
    <style>
        :root{--header-height:86px;--text:#07142d;--brand:#0845b2}*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;color:var(--text);background:#f4f9ff;font-family:"Plus Jakarta Sans",Arial,sans-serif}.site-container{width:min(1200px,calc(100% - 40px));margin-inline:auto}.site-header{min-height:var(--header-height);background:linear-gradient(90deg,rgba(235,248,255,.94) 0%,rgba(214,235,252,.98) 46%,rgba(228,239,255,.96) 100%);border-bottom:1px solid rgba(27,99,179,.16);box-shadow:0 16px 38px rgba(12,63,126,.12);position:sticky;top:0;z-index:1000}.nav-wrap{min-height:var(--header-height);display:grid;grid-template-columns:minmax(320px,.95fr) minmax(560px,auto) minmax(132px,.34fr);align-items:center;column-gap:20px;padding:0 20px}.brand{min-height:68px;display:inline-flex;align-items:center;gap:12px;text-decoration:none}.brand-mark.logo-mark{width:62px;height:62px;min-width:62px;padding:6px;border-radius:18px;background:linear-gradient(180deg,#fff 0%,#eef7ff 100%);border:1px solid rgba(38,113,205,.2);box-shadow:0 14px 30px rgba(15,92,174,.14);display:grid;place-items:center}.brand-mark img{width:100%;height:100%;object-fit:contain}.brand-name{display:block;color:var(--brand);font-size:30px;font-weight:900;line-height:.98}.brand-sub{display:block;margin-top:8px;color:#0b66ff;font-size:11px;font-weight:900;line-height:1;letter-spacing:4.8px}.site-nav{min-height:54px;height:54px;display:inline-flex;align-items:center;justify-content:center;justify-self:center;gap:5px;padding:6px;border:1px solid rgba(61,126,194,.2);border-radius:999px;background:linear-gradient(180deg,rgba(255,255,255,.56) 0%,rgba(177,215,245,.4) 100%);box-shadow:inset 0 1px 0 rgba(255,255,255,.82),inset 0 -1px 0 rgba(59,118,182,.1),0 12px 30px rgba(22,82,145,.1)}.site-nav>a,.site-nav .nav-item>a{height:42px;min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 18px;border-radius:999px;color:#102945;background:transparent;font-size:15px;font-weight:900;line-height:1;text-decoration:none;white-space:nowrap}.site-nav>a.active,.site-nav>a:hover,.site-nav .nav-item:hover>a,.site-nav .nav-item.active>a{color:#004ebd;background:linear-gradient(180deg,#fff 0%,#e7f5ff 100%);box-shadow:0 10px 22px rgba(32,101,172,.15),inset 0 1px 0 rgba(255,255,255,.92)}.nav-menu{display:none}.nav-item:hover .nav-menu,.nav-item:focus-within .nav-menu{display:block}.menu-button{display:none}.page-main{display:block}.home-hero{position:relative;min-height:calc(100vh - var(--header-height));overflow:hidden;background:#07142d}.hero-grid{width:100%;min-height:calc(100vh - var(--header-height));display:grid;grid-template-columns:minmax(0,1fr);padding:0}.hero-slider-col{position:absolute;inset:0;min-height:calc(100vh - var(--header-height));overflow:hidden}.hero-slider,.slider-track,.slider-slide{width:100%;height:100%}.slider-track{display:flex;transition:transform .6s ease}.slider-slide{flex:0 0 100%;min-width:100%;background-size:cover;background-position:center top;background-repeat:no-repeat}.slider-slide img{width:100%;height:100%;object-fit:cover;object-position:center top;display:block}.hero-slider:after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,18,44,.72) 0%,rgba(8,18,44,.4) 34%,rgba(8,18,44,.08) 68%,rgba(8,18,44,.18) 100%);pointer-events:none}.home-hero-copy{position:relative;z-index:5;display:flex;align-items:flex-end;width:min(560px,calc(100% - 48px));min-height:calc(100vh - var(--header-height));margin-left:clamp(24px,7.5vw,112px);padding-bottom:clamp(38px,7vh,72px)}.home-view-courses-btn{display:inline-flex;align-items:center;gap:12px;min-height:58px;padding:0 24px;border-radius:16px;color:#fff;background:linear-gradient(135deg,#5b83ff 0%,#c51cff 100%);font-weight:900;text-decoration:none;box-shadow:0 18px 36px rgba(91,131,255,.25)}.home-counselling-card{position:absolute;z-index:8;right:clamp(52px,9vw,145px);top:50%;width:min(390px,calc(100vw - 48px));transform:translateY(-50%);padding:22px;border-radius:12px;background:rgba(255,255,255,.92);box-shadow:0 24px 60px rgba(0,0,0,.22);border-top:7px solid #c51cff}.home-counselling-card h2{margin:0 0 14px;font-size:22px;line-height:1.2}.home-counselling-card p,.form-note{display:none}.home-counselling-form{display:grid;gap:11px}.home-counselling-form input,.home-counselling-form select{height:48px;border:1px solid #bdd6f4;border-radius:8px;padding:0 14px;font:inherit;font-weight:800;background:#f8fbff}.home-counselling-form button{height:48px;border:0;border-radius:8px;color:#fff;background:linear-gradient(135deg,#5b83ff 0%,#d414ef 100%);font:inherit;font-weight:900}.home-social-rail{position:fixed;right:28px;top:50%;z-index:50;display:grid;gap:12px}.home-social-rail a,.scroll-top{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;color:#fff;text-decoration:none;background:linear-gradient(135deg,#527dff,#c21cf2)}.home-ai-chat{position:fixed;right:28px;bottom:28px;z-index:70}.home-ai-toggle{height:50px;border:0;border-radius:16px;padding:0 22px;color:#fff;background:linear-gradient(135deg,#527dff,#c21cf2);font:inherit;font-weight:900}@media(max-width:980px){:root{--header-height:90px}.site-header{background:#fff}.nav-wrap{display:flex;justify-content:space-between}.brand-name{font-size:20px}.brand-sub{font-size:8px;letter-spacing:2px}.site-nav{display:none}.menu-button{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #cfe2ff;border-radius:12px;background:#eef6ff}.home-hero,.hero-grid,.hero-slider-col,.home-hero-copy{min-height:calc(100vh - var(--header-height))}.home-counselling-card{display:none}.home-social-rail{right:16px}.home-view-courses-btn{min-height:50px}}
        @media(max-width:980px){html body.nav-open,html.nav-open{overflow:hidden!important}html body .site-header{--header-height:78px!important;background:#fff!important;border-bottom:1px solid #d8e8ff!important;box-shadow:none!important}html body .site-header .nav-wrap{width:100%!important;min-height:77px!important;margin:0!important;padding:8px 7px!important;display:grid!important;grid-template-columns:minmax(0,1fr) 48px!important;gap:8px!important;background:#fff!important}html body .site-header .brand{min-width:0!important;gap:10px!important}html body .site-header .brand-mark.logo-mark{width:56px!important;height:56px!important;min-width:56px!important;border-radius:12px!important}html body .site-header .brand-name{max-width:calc(100vw - 134px)!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:25px!important}html body .site-header .brand-sub{max-width:calc(100vw - 134px)!important;overflow:hidden!important;white-space:nowrap!important;font-size:11px!important;letter-spacing:4px!important}html body .site-header .menu-button{width:42px!important;height:42px!important;min-width:42px!important;min-height:42px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;justify-self:end!important;border:1px solid #b9d8ff!important;border-radius:12px!important;color:#0750d8!important;background:#eff7ff!important}html body.nav-open .site-header .site-nav.open{position:fixed!important;inset:var(--header-height) 0 0 0!important;width:100vw!important;height:calc(100vh - var(--header-height))!important;display:flex!important;flex-direction:column!important;align-items:stretch!important;justify-content:flex-start!important;gap:10px!important;padding:12px 7px 26px!important;overflow-y:auto!important;border-top:1px solid #d8e8ff!important;border-radius:0!important;background:#edf6ff!important;z-index:10040!important}html body.nav-open .site-header .site-nav.open>a,html body.nav-open .site-header .site-nav.open>.nav-item>a,html body.nav-open .site-header .site-nav.open .nav-enroll-cta{width:100%!important;min-height:54px!important;height:54px!important;display:flex!important;align-items:center!important;justify-content:flex-start!important;margin:0!important;padding:0 16px!important;color:#061632!important;border:1px solid #cfe3ff!important;border-radius:14px!important;background:rgba(255,255,255,.9)!important;text-align:left!important}html body.nav-open .site-header .site-nav.open>.nav-item>a{justify-content:space-between!important}html body.nav-open .site-header .site-nav.open .nav-item{width:100%!important;height:auto!important;display:flex!important;flex-direction:column!important;align-items:stretch!important}html body.nav-open .site-header .site-nav.open .nav-item.has-menu.open .nav-menu{position:static!important;width:100%!important;display:grid!important;gap:6px!important;margin:8px 0 0!important;padding:8px!important;border:1px solid #d6e7ff!important;border-radius:14px!important;background:#fff!important;opacity:1!important;visibility:visible!important;transform:none!important}html body.nav-open .site-header .site-nav.open .nav-item.has-menu:not(.open) .nav-menu{display:none!important}html body.nav-open .site-header .site-nav.open .nav-enroll-cta{justify-content:center!important;color:#fff!important;border:0!important;background:linear-gradient(135deg,#5d82ff 0%,#c51cff 100%)!important}}
    </style>
    <link rel="stylesheet" href="assets/css/site-pages.min.css?v=20260722-homevisual1">
    <style>
        html body.home-page .home-enroll-modal{display:none!important}
        html body.home-page.enroll-popup-open::before{content:""!important;position:fixed!important;inset:0!important;z-index:10090!important;background:rgba(4,13,34,.62)!important;backdrop-filter:blur(8px)!important}
        html body.home-page.enroll-popup-open .home-enroll-modal{position:fixed!important;top:50%!important;left:50%!important;right:auto!important;z-index:10100!important;display:block!important;width:min(430px,calc(100vw - 32px))!important;max-height:calc(100vh - 36px)!important;overflow-y:auto!important;transform:translate(-50%,-50%)!important;padding:28px 20px 20px!important;border:1px solid rgba(191,219,254,.82)!important;border-top:0!important;border-radius:18px!important;background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(246,250,255,.98))!important;box-shadow:0 28px 80px rgba(2,8,23,.34)!important}
        html body.home-page.enroll-popup-open .home-enroll-modal::before{content:""!important;position:absolute!important;left:0!important;right:0!important;top:0!important;height:7px!important;border-radius:18px 18px 0 0!important;background:linear-gradient(90deg,#2563eb 0%,#c51cff 100%)!important}
        html body.home-page .home-enroll-close{position:absolute!important;top:15px!important;right:15px!important;width:42px!important;height:42px!important;display:inline-grid!important;place-items:center!important;border:3px solid #dbeafe!important;border-radius:14px!important;color:#172554!important;background:linear-gradient(180deg,#ffffff 0%,#eef6ff 100%)!important;box-shadow:0 12px 26px rgba(37,99,235,.18)!important;cursor:pointer!important;transition:transform .18s ease,box-shadow .18s ease,background .18s ease!important}
        html body.home-page .home-enroll-close:hover{transform:translateY(-1px)!important;color:#fff!important;background:linear-gradient(135deg,#527dff,#c21cf2)!important;box-shadow:0 16px 30px rgba(124,58,237,.26)!important}
        html body.home-page .home-enroll-modal h2{margin:0 54px 6px 0!important;padding:0!important;color:#0f172a!important;-webkit-text-fill-color:#0f172a!important;font-size:22px!important;line-height:1.2!important;font-weight:900!important;letter-spacing:0!important}
        html body.home-page .home-enroll-modal>p{display:block!important;margin:0 54px 18px 0!important;color:#64748b!important;-webkit-text-fill-color:#64748b!important;font-size:13px!important;line-height:1.55!important;font-weight:700!important}
        html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form{display:grid!important;gap:12px!important;margin:0!important}
        html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form input,html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form select{width:100%!important;height:52px!important;min-height:52px!important;padding:0 15px!important;border:1px solid #bfdbfe!important;border-radius:10px!important;background:#f8fbff!important;color:#172033!important;-webkit-text-fill-color:#172033!important;font-size:14px!important;font-weight:800!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.9)!important}
        html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form input::placeholder{color:#7c8ba1!important;-webkit-text-fill-color:#7c8ba1!important;opacity:1!important}
        html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form input:focus,html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form select:focus{border-color:#2563eb!important;background:#fff!important;box-shadow:0 0 0 4px rgba(37,99,235,.12)!important}
        html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form button{width:100%!important;height:52px!important;min-height:52px!important;margin-top:2px!important;border:0!important;border-radius:10px!important;background:linear-gradient(135deg,#4f7cff 0%,#d414ef 100%)!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:14px!important;font-weight:900!important;box-shadow:0 16px 34px rgba(124,58,237,.24)!important}
        @media(max-width:460px){html body.home-page.enroll-popup-open .home-enroll-modal{width:calc(100vw - 24px)!important;padding:24px 16px 16px!important;border-radius:16px!important}html body.home-page .home-enroll-close{top:12px!important;right:12px!important;width:38px!important;height:38px!important}html body.home-page .home-enroll-modal h2{margin-right:48px!important;font-size:20px!important}html body.home-page .home-enroll-modal>p{margin-right:48px!important;font-size:12px!important}html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form input,html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form select,html body.home-page.enroll-popup-open .home-enroll-modal .home-counselling-form button{height:49px!important;min-height:49px!important}}
        html body.home-page .hero-slider .slider-slide{background-position:center 42%!important}
        html body.home-page .hero-slider .slider-slide img{object-position:center 42%!important}
        @media(min-width:981px) and (max-width:1366px){html body.home-page .hero-slider .slider-slide{background-position:45% 44%!important}html body.home-page .hero-slider .slider-slide img{object-position:45% 44%!important}}
        @media(max-width:980px){html body.home-page .hero-slider .slider-slide{background-position:center 38%!important}html body.home-page .hero-slider .slider-slide img{object-position:center 38%!important}}
        @media(max-width:560px){html body.home-page .hero-slider .slider-slide{background-position:center top!important}html body.home-page .hero-slider .slider-slide img{object-position:center top!important}}
    </style>
</head>
<body class="static-site home-page<?= $homeFormResult ? ' enroll-popup-open' : '' ?>">
<div class="site-shell">
    <header class="site-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php">
                <span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="68" height="68" decoding="async"></span>
                <span><span class="brand-name">Talentteno Institute</span><span class="brand-sub">IT TRAINING INSTITUTE</span></span>
            </a>
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
        <section class="hero-lite home-hero">
            <div class="site-container hero-grid">
                <div class="hero-copy home-hero-copy reveal">
                    <a class="home-view-courses-btn" href="course.php">
                        <span>View All Courses</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="hero-slider-col hero-right-content reveal reveal-right">
                    <div class="hero-slider" id="heroSlider" data-hero-slider aria-label="Course highlights slider">
                        <div class="slider-track" data-slider-track>
                            <?php foreach ($homeHeroSlides as $index => $slide): ?>
                            <?php
                                $slideImage = (string)($slide['image'] ?? '');
                                $slideMobileImage = (string)($slide['mobile_image'] ?? $slideImage);
                                $slideSrc = tt_home_image_src($slideImage, 1400);
                                $slideSrcset = tt_home_optimized_srcset($slideImage, [430, 900, 1400]);
                                $slideMobileSrc = tt_home_image_src($slideMobileImage, 430);
                                $slideMobileSrcset = tt_home_optimized_srcset($slideMobileImage, [430, 900]);
                                $slideBgImage = str_replace("'", '%27', tt_home_css_asset_url($slideSrc));
                                $slideMobileBgImage = str_replace("'", '%27', tt_home_css_asset_url($slideMobileSrc));
                                $slideStyle = "--hero-slide-image: url('" . tt_h($slideBgImage) . "'); --hero-slide-mobile-image: url('" . tt_h($slideMobileBgImage) . "');";
                                $slideDataBg = tt_h($slideBgImage);
                                $slideDataMobileBg = tt_h($slideMobileBgImage);
                            ?>
                            <div class="slider-slide<?= $index === 0 ? ' is-active' : '' ?>" data-slide aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>"<?= $index === 0 ? ' style="' . $slideStyle . '"' : ' data-bg="' . $slideDataBg . '" data-mobile-bg="' . $slideDataMobileBg . '"' ?>>
                                <picture>
                                    <source
                                        media="(max-width: 767px)"
                                        <?= $index === 0 ? 'srcset="' . tt_h($slideMobileSrcset !== '' ? $slideMobileSrcset : $slideMobileSrc) . '"' : 'data-srcset="' . tt_h($slideMobileSrcset !== '' ? $slideMobileSrcset : $slideMobileSrc) . '"' ?>
                                        sizes="100vw"
                                    >
                                    <img
                                        <?= $index === 0 ? 'src="' . tt_h($slideSrc) . '"' : 'data-src="' . tt_h($slideSrc) . '"' ?>
                                        <?= $slideSrcset !== '' ? ($index === 0 ? 'srcset="' . tt_h($slideSrcset) . '"' : 'data-srcset="' . tt_h($slideSrcset) . '"') : '' ?>
                                        sizes="100vw"
                                        alt="<?= tt_h($slide['title'] ?: 'Talentteno course highlight ' . ($index + 1)) ?>"
                                        loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                                        decoding="async"
                                        <?= $index === 0 ? 'fetchpriority="high"' : '' ?>
                                    >
                                </picture>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($homeSliderCount > 1): ?>
                        <button class="slider-prev" data-slider-prev aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="slider-next" data-slider-next aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
                        <div class="slider-dots" data-slider-dots aria-label="Slide navigation">
                            <?php for ($i = 0; $i < $homeSliderCount; $i++): ?>
                            <button class="slider-dot<?= $i === 0 ? ' is-active' : '' ?>" data-dot="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="home-counselling-card hero-counselling-form home-enroll-modal" id="home-signup" role="dialog" aria-modal="true" aria-labelledby="home-signup-title" aria-hidden="<?= $homeFormResult ? 'false' : 'true' ?>">
            <button class="home-enroll-close" type="button" data-enroll-close aria-label="Close enrolment form"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            <h2 id="home-signup-title">Sign Up for Free Counselling</h2>
            <p>Share your details. Our admission counsellor will contact you.</p>
            <?php if ($homeFormResult): ?>
            <div class="form-alert <?= $homeFormResult['ok'] ? 'success' : 'error' ?>" role="<?= $homeFormResult['ok'] ? 'status' : 'alert' ?>"><?= tt_h($homeFormResult['message']) ?></div>
            <?php endif; ?>
            <form class="home-counselling-form" method="POST" action="index.php#home-signup">
                <input type="hidden" name="form_source" value="home_signup">
                <input type="hidden" name="message" value="Home page enrolment form - free course counselling request.">
                <label class="sr-only" for="home-name">Your full name</label>
                <input id="home-name" type="text" name="name" placeholder="Your Full Name" autocomplete="name" minlength="2" maxlength="80" required>
                <label class="sr-only" for="home-phone">Phone number</label>
                <input id="home-phone" type="tel" name="phone" placeholder="10 Digit Mobile Number" autocomplete="tel" inputmode="numeric" pattern="[6-9][0-9]{9}" minlength="10" maxlength="10" required>
                <label class="sr-only" for="home-email">Email address</label>
                <input id="home-email" type="email" name="email" placeholder="Email Address" autocomplete="email" maxlength="190" required>
                <label class="sr-only" for="home-course">Course of interest</label>
                <select id="home-course" name="course" required>
                    <option value="">Select Course</option>
                    <?php foreach ($allCourses as $course): ?>
                    <option><?= tt_h($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-honeypot" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                <button type="submit">Sign Up Now <i class="fa-solid fa-arrow-right"></i></button>
            </form>
            <span class="form-note"><i class="fa-solid fa-shield-halved"></i> 100% Free. No Spam.</span>
        </div>

        <section class="model-section model-about">
            <div class="site-container model-about-grid">
                <div class="model-copy reveal">
                    <span class="model-label">About Talentteno</span>
                    <h2>Choose a practical IT training institute built for career-ready learning.</h2>
                    <strong>We train students, graduates and professionals through clear concepts, lab practice and real project exposure.</strong>
                    <p>Talentteno Institute focuses on job-oriented IT courses in Madurai with trainer-led classes, internship guidance, communication support and placement preparation.</p>
                    <ul class="model-check-list">
                        <li><i class="fa-solid fa-check"></i> Basic to advanced syllabus</li>
                        <li><i class="fa-solid fa-check"></i> Live project and portfolio practice</li>
                        <li><i class="fa-solid fa-check"></i> Resume, mock interview and career support</li>
                    </ul>
                    <a class="model-gradient-btn" href="about.php">Read More <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="model-about-visual reveal reveal-right">
                    <?php $aboutMainImage = 'uploads/media/full-stack-development-20260703-133158-761383.png'; $aboutMainSrcset = tt_home_optimized_srcset($aboutMainImage, [400, 800]); ?>
                    <img class="model-about-main" src="<?= tt_h(tt_home_image_src($aboutMainImage, 800)) ?>"<?= $aboutMainSrcset !== '' ? ' srcset="' . tt_h($aboutMainSrcset) . '" sizes="(max-width: 767px) 100vw, 645px"' : '' ?> alt="Full stack project training visual" loading="lazy" decoding="async" width="645" height="430">
                    <?php $aboutFloatImage = 'assets/images/home2.webp'; $aboutFloatSrcset = tt_home_optimized_srcset($aboutFloatImage, [430, 900]); ?>
                    <img class="model-about-float" src="<?= tt_h(tt_home_image_src($aboutFloatImage, 430)) ?>"<?= $aboutFloatSrcset !== '' ? ' srcset="' . tt_h($aboutFloatSrcset) . '" sizes="220px"' : '' ?> alt="Students learning with mentor" loading="lazy" decoding="async" width="220" height="147">
                    <?php $aboutThirdImage = 'assets/images/home3.webp'; $aboutThirdSrcset = tt_home_optimized_srcset($aboutThirdImage, [430, 900]); ?>
                    <img class="model-about-third" src="<?= tt_h(tt_home_image_src($aboutThirdImage, 430)) ?>"<?= $aboutThirdSrcset !== '' ? ' srcset="' . tt_h($aboutThirdSrcset) . '" sizes="220px"' : '' ?> alt="AI and technology training" loading="lazy" decoding="async" width="220" height="147">
                </div>
            </div>
        </section>

        <section class="model-section model-projects">
            <div class="site-container">
                <div class="model-dark-head reveal"><span class="model-label">Featured Courses</span><h2>Choose a practical course to start learning</h2></div>
                <div class="model-project-track">
                    <?php foreach (array_slice($popularTracks, 0, 6) as $course): ?>
                    <?php
                        $courseTitle = (string)($course['title'] ?? 'Course');
                        $courseSummary = tt_home_course_summary($course);
                        $courseImage = tt_home_course_image($course);
                        $courseFee = !empty($course['fee']) ? 'Fee: ₹' . number_format((float)$course['fee']) : '';
                        $courseDuration = (string)($course['duration'] ?? '');
                    ?>
                    <article class="model-project-card model-course-showcase-card reveal">
                        <?php $courseImageSrcset = tt_home_optimized_srcset($courseImage, [400, 800]); ?>
                        <img src="<?= tt_h(tt_home_image_src($courseImage, 400)) ?>"<?= $courseImageSrcset !== '' ? ' srcset="' . tt_h($courseImageSrcset) . '" sizes="(max-width: 767px) 100vw, 394px"' : '' ?> alt="<?= tt_h($courseTitle) ?> course" loading="lazy" decoding="async" width="394" height="263">
                        <div class="model-course-card-content">
                            <span><?= tt_h(($course['category'] ?? '') ?: 'Course') ?></span>
                            <h3><?= tt_h($courseTitle) ?></h3>
                            <p><?= tt_h($courseSummary) ?></p>
                            <button
                                type="button"
                                class="model-course-enquire"
                                data-course-modal
                                data-title="<?= tt_h($courseTitle) ?>"
                                data-category="<?= tt_h(($course['category'] ?? '') ?: 'Course') ?>"
                                data-description="<?= tt_h($courseSummary) ?>"
                                data-highlights="<?= tt_h(tt_home_course_examples($course)) ?>"
                                data-duration="<?= tt_h($courseDuration) ?>"
                                data-fee="<?= tt_h($courseFee) ?>"
                                data-image="<?= tt_h(tt_home_image_src($courseImage, 800)) ?>"
                                data-enquire="contact.php?course=<?= rawurlencode($courseTitle) ?>">
                                Enquire Now <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="model-section model-team-section">
            <div class="site-container">
                <div class="model-center-head reveal"><span class="model-label">Our Team</span><h2>Meet our training mentors</h2></div>
                <div class="model-team-grid">
                    <?php foreach ($modelTeam as $member): ?>
                    <article class="model-team-card reveal"><img src="<?= tt_h($member['image']) ?>" alt="<?= tt_h($member['name']) ?>" loading="lazy" decoding="async"><div><strong><?= tt_h($member['name']) ?></strong><span><?= tt_h($member['role']) ?></span></div><a href="contact.php" aria-label="Contact <?= tt_h($member['name']) ?>"><i class="fa-solid fa-address-book"></i></a></article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="model-section model-path-section">
            <div class="site-container">
                <div class="model-center-head reveal"><span class="model-label">Course Path</span><h2>What learning tracks we serve</h2></div>
                <div class="model-path-grid">
                    <div class="model-path-visual reveal">
                        <?php $activePathTrack = $coursePathTracks[0]; $pathImage = $activePathTrack['image']; $pathImageSrcset = tt_home_optimized_srcset($pathImage, [400, 800]); ?>
                        <img src="<?= tt_h(tt_home_image_src($pathImage, 800)) ?>"<?= $pathImageSrcset !== '' ? ' srcset="' . tt_h($pathImageSrcset) . '" sizes="(max-width: 767px) 100vw, 520px"' : '' ?> alt="<?= tt_h($activePathTrack['course']) ?> training path" loading="lazy" decoding="async" width="520" height="347">
                        <div><span><?= tt_h($activePathTrack['step']) ?></span><h3><?= tt_h($activePathTrack['title']) ?></h3><p><?= tt_h($activePathTrack['desc']) ?></p></div>
                    </div>
                    <div class="model-path-list reveal" data-path-tabs>
                        <?php foreach ($coursePathTracks as $index => $track): ?>
                        <?php $trackSrcset = tt_home_optimized_srcset($track['image'], [400, 800]); ?>
                        <button type="button" class="<?= $index === 0 ? 'active' : '' ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" data-step="<?= tt_h($track['step']) ?>" data-title="<?= tt_h($track['title']) ?>" data-desc="<?= tt_h($track['desc']) ?>" data-image="<?= tt_h(tt_home_image_src($track['image'], 800)) ?>"<?= $trackSrcset !== '' ? ' data-srcset="' . tt_h($trackSrcset) . '"' : '' ?>><strong><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></strong><span><?= tt_h($track['course']) ?></span></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="model-section model-hire-section" id="hire">
            <div class="site-container">
                <div class="model-hire-hero reveal">
                    <?php $hireImage = 'assets/images/home4.webp'; $hireImageSrcset = tt_home_optimized_srcset($hireImage, [430, 900]); ?>
                    <img src="<?= tt_h(tt_home_image_src($hireImage, 900)) ?>"<?= $hireImageSrcset !== '' ? ' srcset="' . tt_h($hireImageSrcset) . '" sizes="(max-width: 767px) 100vw, 700px"' : '' ?> alt="Students discussing project work" loading="lazy" decoding="async" width="700" height="467">
                    <div class="model-hire-title">
                        <strong class="model-hire-stat">90%</strong>
                        <p>Skill confidence improvement through practice-led learning</p>
                        <ul>
                            <li><i class="fa-solid fa-check"></i> Updated IT Lab Practice</li>
                            <li><i class="fa-solid fa-check"></i> Experienced Trainers</li>
                            <li><i class="fa-solid fa-check"></i> Live Project Training</li>
                            <li><i class="fa-solid fa-check"></i> Placement Guidance</li>
                        </ul>
                    </div>
                    <aside><ul><li><i class="fa-solid fa-circle-check"></i> Mentor-led classes</li><li><i class="fa-solid fa-circle-check"></i> Project practice</li><li><i class="fa-solid fa-circle-check"></i> Interview guidance</li><li><i class="fa-solid fa-circle-check"></i> Placement support</li></ul><strong>90%</strong><span>Skill confidence improvement through practice-led learning</span></aside>
                </div>
            </div>
        </section>

        <section class="model-section model-review-section" id="reviews">
            <div class="site-container">
                <div class="model-split-head reveal"><div><span class="model-label">Student Review</span><h2>What learners say about us</h2></div></div>
                <div class="review-scroll-stage reveal" aria-label="Student reviews and course images">
                    <div class="review-scroll-row review-scroll-row-one">
                        <?php for ($loop = 0; $loop < 2; $loop++): ?>
                        <?php foreach ($reviewShowcaseImages as $index => $slide): $item = $reviewItems[$index % count($reviewItems)]; ?>
                        <article class="review-scroll-card">
                            <?php $reviewImageSrcset = tt_home_optimized_srcset((string)$slide['image'], [400, 800]); ?>
                            <img src="<?= tt_h(tt_home_image_src((string)$slide['image'], 400)) ?>"<?= $reviewImageSrcset !== '' ? ' srcset="' . tt_h($reviewImageSrcset) . '" sizes="(max-width: 767px) 80vw, 320px"' : '' ?> alt="<?= tt_h($slide['title']) ?> training image" loading="lazy" decoding="async" width="320" height="213">
                            <div class="review-scroll-content">
                                <span><i class="fa-solid <?= tt_h($slide['icon']) ?>"></i> <?= tt_h($slide['title']) ?></span>
                                <p><?= tt_h($item['review']) ?></p>
                                <strong><?= tt_h($item['student_name']) ?></strong>
                            </div>
                        </article>
                        <?php endforeach; ?>
                        <?php endfor; ?>
                    </div>
                    <div class="review-scroll-row review-scroll-row-two" aria-hidden="true">
                        <?php for ($loop = 0; $loop < 2; $loop++): ?>
                        <?php foreach (array_reverse($reviewShowcaseImages) as $index => $slide): $item = $reviewItems[$index % count($reviewItems)]; ?>
                        <article class="review-scroll-card review-scroll-card-small">
                            <?php $reviewSmallSrcset = tt_home_optimized_srcset((string)$slide['image'], [400, 800]); ?>
                            <img src="<?= tt_h(tt_home_image_src((string)$slide['image'], 400)) ?>"<?= $reviewSmallSrcset !== '' ? ' srcset="' . tt_h($reviewSmallSrcset) . '" sizes="220px"' : '' ?> alt="" loading="lazy" decoding="async" width="220" height="147">
                            <div class="review-scroll-content">
                                <span><i class="fa-solid <?= tt_h($slide['icon']) ?>"></i> <?= tt_h($slide['title']) ?></span>
                            </div>
                        </article>
                        <?php endforeach; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="model-section model-blog-section">
            <div class="site-container">
                <div class="model-center-head reveal"><span class="model-label">Learning Notes</span><h2>Career tips and course guidance</h2></div>
                <div class="model-blog-grid">
                    <?php foreach ($learningNotes as $note): ?>
                    <?php $noteImageSrcset = tt_home_optimized_srcset((string)$note['image'], [400, 800]); ?>
                    <article class="model-blog-card reveal"><img src="<?= tt_h(tt_home_image_src((string)$note['image'], 400)) ?>"<?= $noteImageSrcset !== '' ? ' srcset="' . tt_h($noteImageSrcset) . '" sizes="(max-width: 767px) 100vw, 360px"' : '' ?> alt="<?= tt_h($note['title']) ?>" loading="lazy" decoding="async" width="360" height="240"><div class="meta"><span><i class="fa-solid fa-user"></i> Talentteno</span><span><i class="fa-solid fa-comment"></i> <?= tt_h($note['tag']) ?></span></div><h3><?= tt_h($note['title']) ?></h3><a href="blog.php">Read More</a><time>2026</time></article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    <div class="home-social-rail" aria-label="Talentteno social links">
        <a href="<?= tt_h(!empty($settings['instagram_url']) && $settings['instagram_url'] !== '#' ? $settings['instagram_url'] : 'https://www.instagram.com/') ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        <a href="tel:+<?= tt_h($homePhoneLink) ?>" aria-label="Call Talentteno at <?= tt_h($settings['phone1']) ?>" title="<?= tt_h($settings['phone1']) ?>"><i class="fa-solid fa-phone"></i></a>
        <a href="<?= tt_h($homeWhatsappUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Open WhatsApp chat"><i class="fa-brands fa-whatsapp"></i></a>
    </div>
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
            <div class="course-detail-actions"><a class="btn btn-secondary course-detail-enquire" href="contact.php">Enquire Now</a></div>
        </div>
    </div>
    <div class="home-ai-chat" data-ai-chat aria-label="Talentteno AI chat">
        <button class="home-ai-toggle" type="button" data-ai-toggle aria-label="Open Talentteno AI chat" aria-controls="homeAiPanel" aria-expanded="false">
            <i class="fa-solid fa-comments"></i>
            <span>AI Chat</span>
        </button>
        <section class="home-ai-panel" id="homeAiPanel" aria-hidden="true">
            <div class="home-ai-head">
                <button class="home-ai-back" type="button" data-ai-back aria-label="Back to chat button"><i class="fa-solid fa-arrow-left"></i></button>
                <div><span><i class="fa-solid fa-robot"></i></span><strong>Talentteno AI Assistant</strong><small>Auto reply</small></div>
                <button class="home-ai-close" type="button" data-ai-close aria-label="Close AI chat"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="home-ai-messages" data-ai-messages>
                <div class="ai-message bot">Hi! I am Talentteno AI Assistant. Ask me about courses, fees, internship, placement, demo class, address, phone number, timings or admission.</div>
            </div>
            <div class="home-ai-suggestions" aria-label="Quick questions">
                <button type="button" data-ai-question="Courses">Courses</button>
                <button type="button" data-ai-question="Fees">Fees</button>
                <button type="button" data-ai-question="Internship">Internship</button>
                <button type="button" data-ai-question="Address">Address</button>
                <button type="button" data-ai-question="Contact">Contact</button>
            </div>
            <form class="home-ai-form" data-ai-form>
                <input type="text" name="question" placeholder="Type your question..." autocomplete="off" maxlength="240" required>
                <button type="submit" aria-label="Send message"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </section>
    </div>
    <?php include __DIR__ . "/includes/footer.php"; ?>
</div>
<script src="assets/js/site-pages.min.js?v=20260720-coursepath1" defer></script>
</body>
</html>
