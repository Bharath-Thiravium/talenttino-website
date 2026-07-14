<?php
if (!defined('TT_CATALOG_DATA_ONLY')) {
    require_once __DIR__ . '/includes/site-data.php';
}

$coursePage = [
    'title' => 'Advanced Professional Courses',
    'subtitle' => 'Upgrade your skills with industry-focused advanced programs designed for higher-paying IT careers, certification, live projects, internship and placement support.',
    'courses' => [
        ['icon' => 'fa-atom', 'name' => 'MERN Stack', 'desc' => 'MongoDB, Express, React and Node project training.'],
        ['icon' => 'fa-mug-hot', 'name' => 'Java Full Stack', 'desc' => 'Java backend with frontend and database integration.'],
        ['icon' => 'fa-code', 'name' => 'Python Full Stack', 'desc' => 'Python backend with frontend and database workflows.'],
        ['icon' => 'fa-code', 'name' => 'PHP Full Stack', 'desc' => 'PHP, MySQL and frontend project development.'],
        ['icon' => 'fa-code', 'name' => '.NET Full Stack', 'desc' => '.NET development with database and UI practice.'],
        ['icon' => 'fa-code', 'name' => 'MEAN Stack', 'desc' => 'MongoDB, Express, Angular and Node training.'],
        ['icon' => 'fa-chart-line', 'name' => 'Data Science', 'desc' => 'Analytics, Python and machine learning foundations.'],
        ['icon' => 'fa-brain', 'name' => 'Artificial Intelligence', 'desc' => 'AI concepts, tools and project demonstrations.'],
        ['icon' => 'fa-user-secret', 'name' => 'Cyber Security', 'desc' => 'Security, ethical hacking and network protection.'],
        ['icon' => 'fa-film', 'name' => 'Animation', 'desc' => 'Creative animation and media workflow training.'],
        ['icon' => 'fa-vial-circle-check', 'name' => 'Software Testing Master Program', 'desc' => 'Manual testing, Selenium and API testing basics.'],
        ['icon' => 'fa-code', 'name' => 'Python + SQL + Django', 'desc' => 'Python web development with SQL and Django.'],
        ['icon' => 'fa-mug-hot', 'name' => 'Java + MySQL + Spring Boot', 'desc' => 'Enterprise Java backend project workflow.'],
        ['icon' => 'fa-code', 'name' => 'PHP + MySQL + Laravel', 'desc' => 'Laravel application development with MySQL.'],
        ['icon' => 'fa-laptop-code', 'name' => 'C# + SQL Server + ASP.NET Core', 'desc' => 'Microsoft full-stack development path.'],
        ['icon' => 'fa-chart-pie', 'name' => 'Data Analytics', 'desc' => 'Excel, SQL, Power BI and Python reporting.'],
        ['icon' => 'fa-pencil-ruler', 'name' => 'UI/UX + Frontend Development', 'desc' => 'Design and frontend implementation together.'],
        ['icon' => 'fa-photo-film', 'name' => 'Graphic Design + Video Editing', 'desc' => 'Design, branding and editing skill package.'],
        ['icon' => 'fa-bug', 'name' => 'Software Testing', 'desc' => 'Manual, Selenium and API testing workflows.'],
        ['icon' => 'fa-mobile-screen-button', 'name' => 'React Native + Node.js', 'desc' => 'Mobile app and backend development path.'],
        ['icon' => 'fa-code', 'name' => 'Master Python', 'desc' => 'Advanced Python programming and project practice.'],
        ['icon' => 'fa-robot', 'name' => 'Data Science & AI Professional', 'desc' => 'Advanced data science and AI career package.'],
    ],
];
$managedCourses = !defined('TT_CATALOG_DATA_ONLY') ? tt_courses_by_type('advanced') : [];
if ($managedCourses) {
    $coursePage['courses'] = array_map(static fn(array $course): array => [
        'icon' => tt_course_icon($course['category']),
        'name' => $course['title'],
        'desc' => $course['description'],
        'items' => tt_course_highlights($course, array_values(array_filter([$course['duration'] ? $course['duration'] . ' training' : '', 'Advanced live projects', 'Career support']))),
        'id' => $course['id'], 'category' => $course['category'], 'duration' => $course['duration'],
        'fee' => $course['fee'], 'original_fee' => $course['original_fee'],
        'image' => $course['image'], 'brochure_file' => $course['brochure_file'],
    ], $managedCourses);
}
if (!defined('TT_CATALOG_DATA_ONLY')) {
    require __DIR__ . '/course-catalog.php';
}
