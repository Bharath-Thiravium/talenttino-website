<?php
if (!defined('TT_CATALOG_DATA_ONLY')) {
    require_once __DIR__ . '/includes/site-data.php';
}

$coursePage = [
    'title' => 'Our Popular Courses',
    'subtitle' => 'Industry-oriented training programs with practical sessions, certification, internship support and placement assistance.',
    'layout' => 'compact',
    'courses' => [
        ['icon' => 'fa-calculator', 'name' => 'Advanced Tally with GST', 'desc' => 'Accounting, GST and business finance workflow training.'],
        ['icon' => 'fa-user-tie', 'name' => 'Office Administration', 'desc' => 'Office tools, documentation and workplace process training.'],
        ['icon' => 'fa-brands fa-java', 'name' => 'Core Java', 'desc' => 'Java programming fundamentals and OOP concepts.'],
        ['icon' => 'fa-brands fa-java', 'name' => 'Advanced Java', 'desc' => 'Advanced Java concepts and application workflow.'],
        ['icon' => 'fa-brands fa-python', 'name' => 'Python Programming', 'desc' => 'Python basics, scripting and project practice.'],
        ['icon' => 'fa-code', 'name' => 'C# & .NET', 'desc' => 'Microsoft stack application development basics.'],
        ['icon' => 'fa-chart-line', 'name' => 'Data Analytics', 'desc' => 'Excel, SQL, dashboards and business reporting.'],
        ['icon' => 'fa-robot', 'name' => 'Machine Learning', 'desc' => 'Model concepts, data workflows and practical demos.'],
        ['icon' => 'fa-brands fa-aws', 'name' => 'AWS Cloud', 'desc' => 'Cloud basics and deployment concepts.'],
        ['icon' => 'fa-brands fa-windows', 'name' => 'Microsoft Azure', 'desc' => 'Azure cloud services and foundation practice.'],
        ['icon' => 'fa-brands fa-google', 'name' => 'Google Cloud', 'desc' => 'Cloud computing concepts with Google Cloud.'],
        ['icon' => 'fa-server', 'name' => 'DevOps', 'desc' => 'Tools, pipelines, deployment and automation basics.'],
        ['icon' => 'fa-user-secret', 'name' => 'Ethical Hacking', 'desc' => 'Security testing mindset and lab practice.'],
        ['icon' => 'fa-network-wired', 'name' => 'CCNA', 'desc' => 'Network foundation and routing concepts.'],
        ['icon' => 'fa-network-wired', 'name' => 'CCNP', 'desc' => 'Advanced networking and enterprise concepts.'],
        ['icon' => 'fa-desktop', 'name' => 'Computer Hardware', 'desc' => 'Hardware support and troubleshooting basics.'],
        ['icon' => 'fa-microchip', 'name' => 'Chip Level Repair', 'desc' => 'Electronics and repair practice foundation.'],
        ['icon' => 'fa-palette', 'name' => 'Graphic Design', 'desc' => 'Photoshop, Illustrator and creative design work.'],
        ['icon' => 'fa-pencil-ruler', 'name' => 'UI / UX Design', 'desc' => 'Figma, wireframes, prototypes and design systems.'],
        ['icon' => 'fa-photo-film', 'name' => 'Motion Graphics', 'desc' => 'Visual effects and motion design basics.'],
        ['icon' => 'fa-bullhorn', 'name' => 'Digital Marketing', 'desc' => 'SEO, ads and social media campaign practice.'],
        ['icon' => 'fa-mobile-screen-button', 'name' => 'Flutter', 'desc' => 'Cross-platform mobile application development.'],
        ['icon' => 'fa-brands fa-react', 'name' => 'React Native', 'desc' => 'Mobile development using React Native.'],
        ['icon' => 'fa-vial', 'name' => 'Automation Testing (Selenium)', 'desc' => 'Automation testing workflows and Selenium basics.'],
        ['icon' => 'fa-laptop', 'name' => 'Basic Computer + MS Office', 'desc' => 'Computer basics, office tools and typing practice.'],
        ['icon' => 'fa-file-excel', 'name' => 'MS Office + Advanced Excel + Tally Prime', 'desc' => 'Office productivity with accounting support.'],
        ['icon' => 'fa-file-invoice-dollar', 'name' => 'Tally Prime + GST + Income Tax + Payroll', 'desc' => 'Complete practical accounting package.'],
        ['icon' => 'fa-code', 'name' => 'C, C++', 'desc' => 'Programming foundation for beginners.'],
        ['icon' => 'fa-globe', 'name' => 'Web Development', 'desc' => 'HTML, CSS, JavaScript and project building.'],
        ['icon' => 'fa-laptop-code', 'name' => 'Frontend Development', 'desc' => 'Modern frontend UI and responsive layouts.'],
        ['icon' => 'fa-database', 'name' => 'Backend Development', 'desc' => 'Server-side logic and database workflows.'],
        ['icon' => 'fa-paintbrush', 'name' => 'Digital Marketing + Graphic Design', 'desc' => 'Combined creative and marketing skills.'],
        ['icon' => 'fa-mobile', 'name' => 'Flutter + Firebase', 'desc' => 'Mobile app development with Firebase backend.'],
    ],
];
$managedCourses = !defined('TT_CATALOG_DATA_ONLY') ? tt_courses_by_type('popular') : [];
if ($managedCourses) {
    $coursePage['courses'] = array_map(static fn(array $course): array => [
        'icon' => tt_course_icon($course['category']),
        'name' => $course['title'],
        'desc' => $course['description'],
        'items' => tt_course_highlights($course, array_values(array_filter([$course['duration'] ? $course['duration'] . ' training' : '', 'Live projects', 'Placement support']))),
        'id' => $course['id'], 'category' => $course['category'], 'duration' => $course['duration'],
        'fee' => $course['fee'], 'original_fee' => $course['original_fee'],
        'image' => $course['image'], 'brochure_file' => $course['brochure_file'],
    ], $managedCourses);
}
if (!defined('TT_CATALOG_DATA_ONLY')) {
    require __DIR__ . '/course-catalog.php';
}
