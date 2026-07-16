<?php
if (!defined('TT_CATALOG_DATA_ONLY')) {
    require_once __DIR__ . '/includes/site-data.php';
}

$cyberImage = 'uploads/media/cyber-security-20260703-133329-242125.png';
$coursePage = [
    'title' => 'Cyber Security Courses',
    'subtitle' => 'Network security, server administration, cloud security, ethical hacking and SOC training with practical lab support.',
    'body_class' => 'cyber-security-course-page',
    'hero_image' => 'assets/images/Cyber Security .png',
    'courses' => [
        [
            'icon' => 'fa-network-wired',
            'name' => 'CCNA (Cisco Certified Network Associate)',
            'category' => 'Security',
            'desc' => 'Build a strong networking foundation with routing, switching, IP services and security basics.',
            'image' => $cyberImage,
            'items' => ['Routing and switching', 'Network troubleshooting', 'Cisco certification guidance'],
        ],
        [
            'icon' => 'fa-sitemap',
            'name' => 'CCNP (Cisco Certified Network Professional)',
            'category' => 'Security',
            'desc' => 'Advance into enterprise networking with scalable routing, switching, automation and security concepts.',
            'image' => $cyberImage,
            'items' => ['Enterprise network design', 'Advanced routing practice', 'Professional certification path'],
        ],
        [
            'icon' => 'fa-server',
            'name' => 'Windows Server',
            'category' => 'Security',
            'desc' => 'Learn Windows Server administration, users, policies, services and secure enterprise configuration.',
            'image' => $cyberImage,
            'items' => ['Active Directory basics', 'Server roles and policies', 'Secure administration'],
        ],
        [
            'icon' => 'fa-cloud',
            'name' => 'AWS (Amazon Web Services)',
            'category' => 'Security',
            'desc' => 'Understand AWS cloud services, access control, storage, networking and cloud security practices.',
            'image' => 'uploads/media/cloud-computing-20260703-133220-323189.png',
            'items' => ['IAM and cloud access', 'AWS networking basics', 'Cloud security workflow'],
        ],
        [
            'icon' => 'fa-terminal',
            'name' => 'Linux',
            'category' => 'Security',
            'desc' => 'Master Linux commands, permissions, services and administration needed for security roles.',
            'image' => $cyberImage,
            'items' => ['Linux command line', 'User and file permissions', 'Security tool setup'],
        ],
        [
            'icon' => 'fa-fire-flame-curved',
            'name' => 'Firewall',
            'category' => 'Security',
            'desc' => 'Practice firewall concepts, rule configuration, traffic filtering and network protection methods.',
            'image' => $cyberImage,
            'items' => ['Firewall rules', 'Traffic filtering', 'Security policy practice'],
        ],
        [
            'icon' => 'fa-user-secret',
            'name' => 'Basic Ethical Hacking',
            'category' => 'Security',
            'desc' => 'Start ethical hacking with security fundamentals, reconnaissance, scanning and lab-based practice.',
            'image' => $cyberImage,
            'items' => ['Reconnaissance basics', 'Scanning practice', 'Responsible lab workflow'],
        ],
        [
            'icon' => 'fa-shield-virus',
            'name' => 'Advanced Ethical Hacking',
            'category' => 'Security',
            'desc' => 'Move into advanced exploitation concepts, vulnerability validation and practical reporting methods.',
            'image' => $cyberImage,
            'items' => ['Advanced vulnerability testing', 'Exploit validation labs', 'Security report writing'],
        ],
        [
            'icon' => 'fa-microchip',
            'name' => 'IoT (Internet of Things) Security',
            'category' => 'Security',
            'desc' => 'Learn IoT device risks, network exposure, firmware basics and protection strategies.',
            'image' => $cyberImage,
            'items' => ['IoT threat basics', 'Device security checks', 'Network hardening'],
        ],
        [
            'icon' => 'fa-bug',
            'name' => 'Penetration Testing',
            'category' => 'Security',
            'desc' => 'Practice penetration testing methodology from scoping and scanning to exploitation and reporting.',
            'image' => $cyberImage,
            'items' => ['Pen-test methodology', 'Hands-on lab tasks', 'Finding and reporting risks'],
        ],
        [
            'icon' => 'fa-desktop',
            'name' => 'SOC (Security Operations Center)',
            'category' => 'Security',
            'desc' => 'Prepare for SOC analyst roles with alert triage, SIEM workflow, incident basics and monitoring.',
            'image' => $cyberImage,
            'items' => ['SIEM monitoring', 'Alert investigation', 'Incident response basics'],
        ],
    ],
];

$managedCourses = !defined('TT_CATALOG_DATA_ONLY') ? tt_courses_by_type('cyber') : [];
if ($managedCourses) {
    $coursePage['courses'] = array_map(static fn(array $course): array => [
        'icon' => tt_course_icon($course['category']),
        'name' => $course['title'],
        'desc' => $course['description'],
        'items' => tt_course_highlights($course, array_values(array_filter([$course['duration'] ? $course['duration'] . ' training' : '', 'Security lab practice', 'Career support']))),
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
