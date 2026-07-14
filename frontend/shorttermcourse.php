<?php
if (!defined('TT_CATALOG_DATA_ONLY')) {
    require_once __DIR__ . '/includes/site-data.php';
}

function tt_short_course_icon(string $title, string $category = ''): string
{
    $needle = strtolower($title . ' ' . $category);
    $map = [
        'career' => 'fa-user-graduate',
        'english' => 'fa-comments',
        'testing' => 'fa-vial',
        'graphic' => 'fa-pen-nib',
        'design' => 'fa-pen-nib',
        'hardware' => 'fa-screwdriver-wrench',
        'database' => 'fa-database',
        'artificial' => 'fa-robot',
        'ai' => 'fa-robot',
        'web' => 'fa-laptop-code',
        'programming' => 'fa-code',
        'computer' => 'fa-desktop',
        'account' => 'fa-calculator',
        'marketing' => 'fa-bullhorn',
        'cyber' => 'fa-shield-halved',
    ];

    foreach ($map as $word => $icon) {
        if (str_contains($needle, $word)) {
            return $icon;
        }
    }

    return tt_course_icon($category);
}

$coursePage = [
    'title' => 'Short Term Courses',
    'subtitle' => 'Fast, practical modules for students and job seekers who want focused skill development.',
    'courses' => [
        ['icon' => 'fa-desktop', 'name' => 'Computer Courses', 'desc' => 'Foundation computer training for daily office and study needs.', 'items' => ['Basic Computer', 'Computer Fundamentals', 'MS Office', 'Internet & Email', 'Typing Course']],
        ['icon' => 'fa-calculator', 'name' => 'Accounting', 'desc' => 'Practical accounting and tax workflow training.', 'items' => ['Tally Prime', 'GST Practical', 'Payroll & Income Tax']],
        ['icon' => 'fa-code', 'name' => 'Programming', 'desc' => 'Start coding with guided syntax and problem-solving practice.', 'items' => ['C', 'C++', 'Basic Python', 'JavaScript', 'PHP']],
        ['icon' => 'fa-laptop-code', 'name' => 'Web Development', 'desc' => 'Build frontend and backend-ready web foundations.', 'items' => ['HTML & CSS', 'Bootstrap', 'JavaScript', 'React', 'Node.js', 'Express.js', 'MongoDB']],
        ['icon' => 'fa-robot', 'name' => 'Artificial Intelligence', 'desc' => 'Useful AI skills for students and professionals.', 'items' => ['Generative AI', 'Prompt Engineering']],
        ['icon' => 'fa-database', 'name' => 'Database', 'desc' => 'Database basics for application and analytics work.', 'items' => ['SQL', 'MySQL', 'Oracle SQL']],
        ['icon' => 'fa-shield-halved', 'name' => 'Cyber Security', 'desc' => 'Introductory cyber security and networking practice.', 'items' => ['Network Security', 'Penetration Testing', 'Computer Networking']],
        ['icon' => 'fa-screwdriver-wrench', 'name' => 'Hardware', 'desc' => 'Hands-on hardware support foundation.', 'items' => ['Laptop Repair']],
        ['icon' => 'fa-palette', 'name' => 'Graphic Design', 'desc' => 'Creative software training for design work.', 'items' => ['Adobe Photoshop', 'Illustrator', 'Figma', 'Video Editing']],
        ['icon' => 'fa-bullhorn', 'name' => 'Digital Marketing', 'desc' => 'Marketing skills for online business growth.', 'items' => ['SEO', 'Google Ads', 'Social Media Marketing']],
        ['icon' => 'fa-vial', 'name' => 'Software Testing', 'desc' => 'Quality testing foundation for software projects.', 'items' => ['Manual Testing']],
        ['icon' => 'fa-user-graduate', 'name' => 'Career Development', 'desc' => 'Communication and interview preparation support.', 'items' => ['Spoken English', 'Interview Preparation', 'Resume Building', 'Aptitude Training']],
    ],
];
$managedCourses = !defined('TT_CATALOG_DATA_ONLY') ? tt_courses_by_type('short') : [];
if ($managedCourses) {
    $coursePage['courses'] = array_map(static fn(array $course): array => [
        'icon' => tt_short_course_icon($course['title'], $course['category']),
        'name' => $course['title'],
        'desc' => $course['description'],
        'items' => tt_course_highlights($course, array_values(array_filter([$course['duration'] ? $course['duration'] . ' training' : '', 'Live projects', 'Internship support']))),
        'id' => $course['id'], 'category' => $course['category'], 'duration' => $course['duration'],
        'fee' => $course['fee'], 'original_fee' => $course['original_fee'],
        'image' => $course['image'], 'brochure_file' => $course['brochure_file'],
    ], $managedCourses);
}
if (!defined('TT_CATALOG_DATA_ONLY')) {
    require __DIR__ . '/course-catalog.php';
}
