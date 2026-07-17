<?php
require_once __DIR__ . '/includes/site-data.php';

$settings = tt_settings();
$homeWhatsappPhone = preg_replace('/\D+/', '', (string)($settings['phone1'] ?? ''));
$homeWhatsappUrl = 'https://web.whatsapp.com/send?phone=' . $homeWhatsappPhone . '&text=' . rawurlencode('Hello Talentteno, I would like course information.');
$allCourses = tt_courses();
$featuredCourses = tt_courses(6, true);
$services = tt_services(6);
$testimonials = tt_testimonials(3);
$homeHeroSlides = tt_home_slider_images();
if (empty($homeHeroSlides)) {
    $homeHeroSlides = [['image' => 'assets/images/home.webp', 'title' => '']];
}
$homeSlideSeen = [];
foreach ($homeHeroSlides as $slide) {
    $homeSlideSeen[$slide['image'] ?? ''] = true;
}
foreach ([
    ['image' => 'assets/images/home.webp', 'title' => 'Talentteno practical training'],
    ['image' => 'assets/images/home1.webp', 'title' => 'Talentteno classroom learning'],
    ['image' => 'assets/images/home2.webp', 'title' => 'Talentteno student projects'],
] as $fallbackSlide) {
    if (count($homeHeroSlides) >= 3) {
        break;
    }
    if (!isset($homeSlideSeen[$fallbackSlide['image']])) {
        $homeHeroSlides[] = $fallbackSlide;
        $homeSlideSeen[$fallbackSlide['image']] = true;
    }
}
$homeSliderCount = count($homeHeroSlides);
$homeFormResult = null;
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
    ['title' => 'How to choose the right IT course after college', 'image' => 'assets/images/home1.webp', 'tag' => 'Career Guide'],
    ['title' => 'Why live projects matter for freshers', 'image' => 'assets/images/home2.webp', 'tag' => 'Project Practice'],
    ['title' => 'Interview preparation checklist for IT roles', 'image' => 'assets/images/contact-counsellor-hero.png', 'tag' => 'Placement'],
];
$reviewItems = $testimonials ?: [
    ['student_name' => 'Talentteno Student', 'course' => 'Full Stack Development', 'review' => 'The classes were practical and the project tasks helped me understand real development flow.', 'rating' => 5],
    ['student_name' => 'Career Switcher', 'course' => 'Data Science', 'review' => 'Mentor guidance, assignments and interview support made the learning path clear.', 'rating' => 5],
];
$reviewShowcaseImages = [
    ['image' => 'uploads/media/full-stack-development-20260703-133158-761383.png', 'title' => 'Full Stack Development', 'icon' => 'fa-code'],
    ['image' => 'uploads/media/data-science-ai-20260703-133112-527863.png', 'title' => 'AI & Machine Learning', 'icon' => 'fa-brain'],
    ['image' => 'uploads/media/cyber-security-20260703-133329-242125.png', 'title' => 'Cyber Security', 'icon' => 'fa-shield-halved'],
    ['image' => 'uploads/media/data-analyst-20260703-133130-702998.png', 'title' => 'Data Analyst', 'icon' => 'fa-chart-line'],
    ['image' => 'uploads/media/digital-marketing-20260703-133146-981935.png', 'title' => 'Digital Marketing', 'icon' => 'fa-bullhorn'],
    ['image' => 'uploads/media/programming-languages-20260703-133210-630417.png', 'title' => 'Programming Languages', 'icon' => 'fa-terminal'],
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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && in_array(($_POST['form_source'] ?? ''), ['home_counselling', 'home_signup'], true)) {
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-pages.css?v=20260717-smoothslide2">
</head>
<body class="static-site home-page">
<div class="site-shell">
    <header class="site-header">
        <div class="site-container nav-wrap">
            <a class="brand" href="index.php">
                <span class="brand-mark logo-mark"><img src="assets/images/logot-transparent.png" alt="Talentteno Institute logo" width="132" height="62" decoding="async" fetchpriority="high"></span>
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
                    <span class="hero-kicker home-kicker"><i class="fa-solid fa-star"></i> #1 IT Training Institute in Madurai</span>
                    <h1 class="home-image-title">
                        <span class="sr-only"><?= tt_h($settings['tagline']) ?></span>
                        <div class="hero-left-image-frame">
                            <img src="assets/images/home left.jpeg" alt="<?= tt_h($settings['tagline']) ?>" decoding="async" fetchpriority="high">
                        </div>
                    </h1>
                    <p>From basics to advanced technologies, learn everything you need to succeed in the IT industry. <?= tt_h($settings['success_rate']) ?> Job Assistance + Free Internship.</p>
                    <div class="home-stats">
                        <div><strong><?= tt_h($settings['total_students']) ?></strong><span>Students Trained</span></div>
                        <div><strong><?= tt_h($settings['success_rate']) ?></strong><span>Job Assistance</span></div>
                        <div><strong><?= tt_h($settings['total_trainers']) ?></strong><span>Expert Trainers</span></div>
                    </div>
                    <div class="hero-actions">
                        <a class="btn btn-primary home-primary-btn" href="course.php">Explore Courses</a>
                        <a class="btn btn-secondary home-secondary-btn" href="contact.php"><i class="fa-solid fa-circle-play"></i> Free Demo Class</a>
                    </div>
                </div>
                <div class="hero-slider-col hero-right-content reveal reveal-right">
                    <div class="hero-slider" id="heroSlider" data-hero-slider aria-label="Course highlights slider">
                        <div class="slider-track" data-slider-track>
                            <?php foreach ($homeHeroSlides as $index => $slide): ?>
                            <div class="slider-slide<?= $index === 0 ? ' is-active' : '' ?>" data-slide aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                                <img
                                    src="<?= tt_h($slide['image']) ?>"
                                    alt="<?= tt_h($slide['title'] ?: 'Talentteno course highlight ' . ($index + 1)) ?>"
                                    loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                                    decoding="async"
                                    <?= $index === 0 ? 'fetchpriority="high"' : '' ?>
                                >
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
                    <div class="home-counselling-card hero-counselling-form" id="home-signup">
                        <h2>Sign Up for Free Counselling</h2>
                        <p>Share your details. Our admission counsellor will contact you.</p>
                        <?php if ($homeFormResult): ?>
                        <div class="form-alert <?= $homeFormResult['ok'] ? 'success' : 'error' ?>" role="<?= $homeFormResult['ok'] ? 'status' : 'alert' ?>"><?= tt_h($homeFormResult['message']) ?></div>
                        <?php endif; ?>
                        <form class="home-counselling-form" method="POST" action="index.php#home-signup">
                            <input type="hidden" name="form_source" value="home_signup">
                            <input type="hidden" name="message" value="Home page sign up form - free course counselling request.">
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
                </div>
            </div>
        </section>

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
                    <img class="model-about-main" src="uploads/media/full-stack-development-20260703-133158-761383.png" alt="Full stack project training visual" loading="lazy" decoding="async">
                    <img class="model-about-float" src="assets/images/home2.webp" alt="Students learning with mentor" loading="lazy" decoding="async">
                    <button class="model-play" type="button" aria-label="Watch training preview" data-video-open><i class="fa-solid fa-play"></i></button>
                </div>
            </div>
        </section>

        <section class="model-section model-services">
            <div class="site-container">
                <div class="model-split-head reveal">
                    <div><span class="model-label">IT Services</span><h2>Our career-focused training programs</h2></div>
                    <p>Each course combines classroom learning, guided practice, assignments, project work and career preparation for students who want practical confidence.</p>
                </div>
                <div class="model-services-layout">
                    <div class="model-service-stack">
                        <?php foreach (array_slice($popularTracks, 0, 2) as $course): ?>
                        <article class="model-service-card reveal">
                            <span><i class="fa-solid <?= tt_h(tt_home_course_icon($course)) ?>"></i></span>
                            <h3><?= tt_h($course['title']) ?></h3>
                            <p><?= tt_h(tt_home_course_summary($course)) ?></p>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="model-service-center reveal"><a href="course.php">View More Details <i class="fa-solid fa-arrow-up-right-from-square"></i></a><img src="assets/images/home1.webp" alt="IT training discussion" loading="lazy" decoding="async"></div>
                    <div class="model-service-stack">
                        <?php foreach (array_slice($popularTracks, 2, 2) as $course): ?>
                        <article class="model-service-card reveal">
                            <span><i class="fa-solid <?= tt_h(tt_home_course_icon($course)) ?>"></i></span>
                            <h3><?= tt_h($course['title']) ?></h3>
                            <p><?= tt_h(tt_home_course_summary($course)) ?></p>
                        </article>
                        <?php endforeach; ?>
                    </div>
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
                        <img src="<?= tt_h($courseImage) ?>" alt="<?= tt_h($courseTitle) ?> course" loading="lazy" decoding="async">
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
                                data-image="<?= tt_h($courseImage) ?>"
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
                        <img src="assets/images/home2.webp" alt="Students collaborating during IT training" loading="lazy" decoding="async">
                        <div><span>Step 01</span><h3>Practical course selection</h3><p>Start with counselling, choose the right course and follow a clear project-based learning path.</p></div>
                    </div>
                    <div class="model-path-list reveal" data-path-tabs>
                        <button type="button" class="active" aria-pressed="true" data-step="Step 01" data-title="Practical course selection" data-desc="Start with counselling, choose the right course and follow a clear project-based learning path." data-image="assets/images/home.webp"><strong>01</strong><span>Full Stack Development</span></button>
                        <button type="button" aria-pressed="false" data-step="Step 02" data-title="Data science and AI practice" data-desc="Learn Python, analytics, machine learning basics and AI workflow through guided practical tasks." data-image="assets/images/home1.webp"><strong>02</strong><span>Data Science & AI</span></button>
                        <button type="button" aria-pressed="false" data-step="Step 03" data-title="Cyber security lab training" data-desc="Practice security fundamentals, guided lab workflows and beginner-friendly cyber project tasks." data-image="assets/images/home2.webp"><strong>03</strong><span>Cyber Security</span></button>
                        <button type="button" aria-pressed="false" data-step="Step 04" data-title="Digital marketing projects" data-desc="Build practical confidence with campaign planning, SEO basics, social media workflow and reporting." data-image="assets/images/home3.webp"><strong>04</strong><span>Digital Marketing</span></button>
                        <button type="button" aria-pressed="false" data-step="Step 05" data-title="UI / UX portfolio guidance" data-desc="Learn design foundations, interface planning, tool practice and portfolio-ready project presentation." data-image="assets/images/home4.webp"><strong>05</strong><span>UI / UX and Design</span></button>
                    </div>
                </div>
            </div>
        </section>

        <section class="model-video-section" aria-label="Talentteno training preview">
            <img src="assets/images/home3.webp" alt="Hands-on IT project planning" loading="lazy" decoding="async">
            <button type="button" aria-label="Watch training preview" data-video-open><i class="fa-solid fa-play"></i></button>
        </section>

        <section class="model-section model-hire-section" id="hire">
            <div class="site-container">
                <div class="model-hire-hero reveal">
                    <img src="assets/images/home4.webp" alt="Students discussing project work" loading="lazy" decoding="async">
                    <div class="model-hire-title"><span>Why students choose</span><h2>Talentteno Institute</h2></div>
                    <aside><ul><li><i class="fa-solid fa-circle-check"></i> Mentor-led classes</li><li><i class="fa-solid fa-circle-check"></i> Project practice</li><li><i class="fa-solid fa-circle-check"></i> Interview guidance</li><li><i class="fa-solid fa-circle-check"></i> Placement support</li></ul><strong>90%</strong><span>Skill confidence improvement through practice-led learning</span></aside>
                </div>
                <div class="model-hire-points">
                    <div><i class="fa-solid fa-check"></i><span>Updated IT Lab Practice</span></div>
                    <div><i class="fa-solid fa-check"></i><span>Experienced Trainers</span></div>
                    <div><i class="fa-solid fa-check"></i><span>Live Project Training</span></div>
                    <div><i class="fa-solid fa-check"></i><span>Placement Guidance</span></div>
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
                            <img src="<?= tt_h($slide['image']) ?>" alt="<?= tt_h($slide['title']) ?> training image" loading="<?= $loop === 0 && $index < 3 ? 'eager' : 'lazy' ?>" decoding="async">
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
                            <img src="<?= tt_h($slide['image']) ?>" alt="" loading="lazy" decoding="async">
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
                    <article class="model-blog-card reveal"><img src="<?= tt_h($note['image']) ?>" alt="<?= tt_h($note['title']) ?>" loading="lazy" decoding="async"><div class="meta"><span><i class="fa-solid fa-user"></i> Talentteno</span><span><i class="fa-solid fa-comment"></i> <?= tt_h($note['tag']) ?></span></div><h3><?= tt_h($note['title']) ?></h3><a href="blog.php">Read More</a><time>2026</time></article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    <div class="home-social-rail" aria-label="Talentteno social links">
        <a href="<?= tt_h(!empty($settings['instagram_url']) && $settings['instagram_url'] !== '#' ? $settings['instagram_url'] : 'https://www.instagram.com/') ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        <a href="<?= tt_h(!empty($settings['linkedin_url']) && $settings['linkedin_url'] !== '#' ? $settings['linkedin_url'] : 'https://www.linkedin.com/') ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
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
    <div class="training-video-modal" id="trainingVideoModal" aria-hidden="true">
        <div class="training-video-backdrop" data-video-close></div>
        <div class="training-video-panel" role="dialog" aria-modal="true" aria-label="Talentteno training preview video">
            <button class="training-video-close" type="button" data-video-close aria-label="Close video"><i class="fa-solid fa-xmark"></i></button>
            <video controls preload="metadata" playsinline poster="assets/images/home.webp">
                <source src="assets/videos/talentteno-training-preview.mp4" type="video/mp4">
            </video>
        </div>
    </div>
    <div class="home-ai-chat" data-ai-chat aria-label="Talentteno AI chat">
        <button class="home-ai-toggle" type="button" data-ai-toggle aria-controls="homeAiPanel" aria-expanded="false">
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
<script src="assets/js/site-pages.js?v=20260717-chatfix1" defer></script>
</body>
</html>
