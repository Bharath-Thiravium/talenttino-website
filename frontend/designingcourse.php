<?php
if (!defined('TT_CATALOG_DATA_ONLY')) {
    require_once __DIR__ . '/includes/site-data.php';
}

$coursePage = [
    'title' => 'Designing Courses',
    'subtitle' => 'Creative design programs covering 3D animation, VFX, video editing, motion graphics, graphic design and AI-assisted creative workflows.',
    'body_class' => 'designing-course-page',
    'hero_image' => 'assets/images/design .png',
    'courses' => [
        [
            'icon' => 'fa-cube',
            'name' => '3D Animation & Modeling',
            'category' => 'Designing',
            'desc' => 'Learn 3D asset creation, modeling basics, animation workflow and portfolio-ready creative output.',
            'image' => 'uploads/media/full-stack-development-20260703-133158-761383.png',
            'items' => ['3D modeling basics', 'Animation workflow', 'Portfolio project support'],
        ],
        [
            'icon' => 'fa-clapperboard',
            'name' => 'VFX Compositing',
            'category' => 'Designing',
            'desc' => 'Build visual effects composition skills with layers, masking, tracking and cinematic finishing.',
            'image' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
            'items' => ['Compositing workflow', 'Masking and tracking', 'Creative project practice'],
        ],
        [
            'icon' => 'fa-video',
            'name' => 'Video Editing & Color',
            'category' => 'Designing',
            'desc' => 'Edit videos professionally with timeline workflow, color correction, grading and export settings.',
            'image' => 'assets/images/contact-counsellor-hero.png',
            'items' => ['Timeline editing', 'Color correction', 'Output and delivery formats'],
        ],
        [
            'icon' => 'fa-wand-magic-sparkles',
            'name' => 'Motion Graphics',
            'category' => 'Designing',
            'desc' => 'Create animated titles, social media motion posts, explainer graphics and brand animations.',
            'image' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
            'items' => ['Animated graphics', 'Title animation', 'Social media motion design'],
        ],
        [
            'icon' => 'fa-palette',
            'name' => 'Graphic Design',
            'category' => 'Designing',
            'desc' => 'Master visual design fundamentals for posters, branding, social creatives and marketing assets.',
            'image' => 'assets/images/home.webp',
            'items' => ['Brand design', 'Poster and social creatives', 'Layout and typography'],
        ],
        [
            'icon' => 'fa-layer-group',
            'name' => 'Specialization Program',
            'category' => 'Designing',
            'desc' => 'A focused creative specialization path for learners who want a deeper design portfolio.',
            'image' => 'assets/images/home2.webp',
            'items' => ['Advanced creative practice', 'Portfolio review', 'Career guidance'],
        ],
        [
            'icon' => 'fa-robot',
            'name' => 'AI for Creative Program',
            'category' => 'Designing',
            'desc' => 'Use AI tools for idea generation, creative production, design acceleration and content workflows.',
            'image' => 'uploads/media/programming-languages-20260703-133210-630417.png',
            'items' => ['AI creative tools', 'Prompt workflow', 'Design productivity practice'],
        ],
    ],
];

$managedCourses = !defined('TT_CATALOG_DATA_ONLY') ? tt_courses_by_type('designing') : [];
if ($managedCourses) {
    $coursePage['courses'] = array_map(static fn(array $course): array => [
        'icon' => tt_course_icon($course['category']),
        'name' => $course['title'],
        'desc' => $course['description'],
        'items' => tt_course_highlights($course, array_values(array_filter([$course['duration'] ? $course['duration'] . ' training' : '', 'Creative portfolio projects', 'Career support']))),
        'id' => $course['id'],
        'category' => $course['category'],
        'duration' => $course['duration'],
        'fee' => $course['fee'],
        'original_fee' => $course['original_fee'],
        'image' => $course['image'],
        'brochure_file' => $course['brochure_file'],
    ], $managedCourses);
}

if (!defined('TT_CATALOG_DATA_ONLY')) {
    require __DIR__ . '/course-catalog.php';
}
