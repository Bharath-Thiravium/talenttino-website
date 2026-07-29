<?php
declare(strict_types=1);

if (
    PHP_SAPI !== 'cli'
    && !headers_sent()
    && extension_loaded('zlib')
    && !ini_get('zlib.output_compression')
    && !ob_get_level()
) {
    ob_start('ob_gzhandler');
}

define('DB_OPTIONAL', true);
require_once __DIR__ . '/../../backend/includes/db.php';

function tt_db(): ?mysqli
{
    global $conn;
    return $conn instanceof mysqli ? $conn : null;
}

function tt_h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function tt_asset_url(string $relativePath, string $fallbackVersion = '20260727'): string
{
    static $cache = [];

    $relativePath = trim($relativePath);
    if ($relativePath === '') {
        return '';
    }

    $cacheKey = $relativePath . '|' . $fallbackVersion;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    if (preg_match('#^(?:https?:)?//#i', $relativePath) || str_starts_with($relativePath, 'data:')) {
        return $cache[$cacheKey] = $relativePath;
    }

    $parts = parse_url($relativePath);
    $path = isset($parts['path']) ? str_replace('\\', '/', (string)$parts['path']) : '';
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, '..')) {
        return $cache[$cacheKey] = $relativePath;
    }

    $fullPath = realpath(__DIR__ . '/../' . $path);
    $frontendRoot = realpath(__DIR__ . '/..');
    $version = $fallbackVersion;
    if ($fullPath && $frontendRoot && str_starts_with($fullPath, $frontendRoot . DIRECTORY_SEPARATOR) && is_file($fullPath)) {
        $mtime = @filemtime($fullPath);
        if ($mtime) {
            $version = (string)$mtime;
        }
    }

    $query = [];
    if (!empty($parts['query'])) {
        parse_str((string)$parts['query'], $query);
    }
    $query['v'] = $version;
    $url = $path . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    if (!empty($parts['fragment'])) {
        $url .= '#' . rawurlencode((string)$parts['fragment']);
    }

    return $cache[$cacheKey] = $url;
}

function tt_safe_public_href(?string $href, string $fallback = 'contact.php'): string
{
    $href = trim((string)$href);
    if ($href === '') {
        return $fallback;
    }

    if (preg_match('#^https?://#i', $href)) {
        return $href;
    }

    if (str_starts_with($href, '#')) {
        return preg_match('/^#[A-Za-z0-9_-]+$/', $href) ? $href : $fallback;
    }

    $path = str_replace('\\', '/', (string)(parse_url($href, PHP_URL_PATH) ?? ''));
    $scheme = parse_url($href, PHP_URL_SCHEME);
    if ($scheme || $path === '' || str_starts_with($path, '/') || str_contains($path, '..')) {
        return $fallback;
    }

    return $href;
}

function tt_deploy_version(): string
{
    static $version = null;
    if ($version !== null) {
        return $version;
    }

    $headFile = __DIR__ . '/../../.git/HEAD';
    $version = 'unknown-production';
    if (is_file($headFile)) {
        $head = trim((string)@file_get_contents($headFile));
        if (preg_match('/^ref:\s*(.+)$/', $head, $match)) {
            $refPath = __DIR__ . '/../../.git/' . trim($match[1]);
            if (is_file($refPath)) {
                $hash = trim((string)@file_get_contents($refPath));
                if (preg_match('/^[a-f0-9]{7,40}$/i', $hash)) {
                    $version = substr($hash, 0, 12);
                }
            }
        } elseif (preg_match('/^[a-f0-9]{7,40}$/i', $head)) {
            $version = substr($head, 0, 12);
        }
    }

    return $version;
}

function tt_phone_digits(?string $phone): string
{
    return preg_replace('/\D+/', '', (string)$phone) ?? '';
}

function tt_phone_href(?string $phone): string
{
    $digits = tt_phone_digits($phone);
    return $digits !== '' ? 'tel:+' . $digits : '#';
}

function tt_whatsapp_url(?array $settings = null): string
{
    $settings = $settings ?? tt_settings();
    $digits = tt_phone_digits($settings['phone1'] ?? '');
    if ($digits === '') {
        $digits = '918248415023';
    }

    return 'https://wa.me/' . $digits . '?text=' . rawurlencode('Hello Talentteno, I would like course information.');
}

function tt_public_contact_payload(?array $settings = null): array
{
    $settings = $settings ?? tt_settings();

    return [
        'name' => $settings['site_name'] ?? 'Talentteno Institute',
        'address' => $settings['address'] ?? '',
        'phone1' => $settings['phone1'] ?? '',
        'phone2' => $settings['phone2'] ?? '',
        'email' => $settings['email'] ?? '',
        'businessHours' => $settings['business_hours'] ?? '',
        'whatsappUrl' => tt_whatsapp_url($settings),
        'instagramUrl' => tt_social_url($settings['instagram_url'] ?? '', 'instagram'),
    ];
}

function tt_abs_url(string $path = ''): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $basePath = $basePath === '/' ? '' : $basePath;
    $cleanPath = ltrim($path, '/');

    return $scheme . '://' . $host . $basePath . ($cleanPath !== '' ? '/' . $cleanPath : '');
}

function tt_site_base_url(): string
{
    return tt_abs_url('');
}

function tt_seo_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return tt_abs_url('index.php');
    }

    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }

    return tt_abs_url($url);
}

function tt_fetch_all(string $sql): array
{
    static $cache = [];

    if (isset($cache[$sql])) {
        return $cache[$sql];
    }

    $db = tt_db();
    if (!$db) {
        return [];
    }

    $result = @$db->query($sql);
    if (!$result) {
        error_log('Frontend query failed: ' . $db->error);
        return [];
    }

    return $cache[$sql] = $result->fetch_all(MYSQLI_ASSOC);
}

function tt_fetch_one(string $sql): ?array
{
    $rows = tt_fetch_all($sql);
    return $rows[0] ?? null;
}

function tt_settings(): array
{
    static $settingsCache = null;
    if ($settingsCache !== null) {
        return $settingsCache;
    }

    $defaults = [
        'site_name' => 'Talentteno Institute',
        'tagline' => 'The Future of Your IT Career Starts Here',
        'about_title' => 'About Talentteno Institute',
        'about_content' => 'Talentteno Institute is a practical IT training institute focused on helping students move from basic knowledge to advanced job-ready skills.',
        'mission' => 'To bridge the gap between classroom learning and industry requirements through practical, mentor-led IT training.',
        'vision' => 'To become South Tamil Nadu\'s most trusted IT training institute.',
        'total_students' => '250+',
        'total_trainers' => '15+',
        'success_rate' => '100%',
        'address' => 'Plot 81, Poriyalar Nagar, Tiruppalai, Madurai, Tamil Nadu - 625014',
        'phone1' => '+91 82484 15023',
        'phone2' => '+91 63836 43141',
        'email' => 'talentteno.socials@gmail.com',
        'map_embed_url' => '',
        'facebook_url' => '#',
        'instagram_url' => 'https://www.instagram.com/talentteno__/',
        'linkedin_url' => 'https://www.linkedin.com/search/results/companies/?keywords=Talentteno%20Institute',
        'youtube_url' => '#',
        'footer_description' => 'Practical IT training in Madurai with free internship, spoken English support, live projects, certification and placement assistance.',
        'footer_copyright' => '© 2026 Talentteno Institute | All Rights Reserved',
        'seo_title' => 'Talentteno Institute | Best IT Training Institute in Madurai',
        'seo_description' => 'Talentteno Institute offers practical IT training in Madurai for Full Stack Development, Data Science, AI, Cyber Security, Digital Marketing, UI/UX, Tally and programming with live projects, free internship and placement assistance.',
        'seo_keywords' => 'IT training institute in Madurai, best software training institute Madurai, full stack course Madurai, data science course Madurai, cyber security course Madurai, digital marketing course Madurai, UI UX course Madurai, Tally course Madurai',
        'business_hours' => 'Monday to Saturday, 9:00 AM to 7:00 PM',
    ];

    $row = tt_fetch_one('SELECT * FROM site_settings WHERE id = 1');
    $settings = array_merge($defaults, $row ?: []);
    $settings['total_students'] = '250+';

    foreach ($settings as $key => $value) {
        if (is_string($value)) {
            $oldBrand = 'Athen' . 'a Solutions';
            $settings[$key] = trim(str_ireplace(
                [
                    'Best IT Training Institute in Madurai | Talentteno Institute',
                    'powered by ' . $oldBrand . ', ',
                    $oldBrand,
                ],
                [
                    'Talentteno Institute | Best IT Training Institute in Madurai',
                    '',
                    '',
                ],
                $value
            ));
        }
    }

    $settings['instagram_url'] = 'https://www.instagram.com/talentteno__/';

    return $settingsCache = $settings;
}

function tt_social_url(?string $value, string $network): string
{
    $instagramUrl = 'https://www.instagram.com/talentteno__/';
    $fallbacks = [
        'facebook' => 'https://www.facebook.com/',
        'instagram' => $instagramUrl,
        'linkedin' => 'https://www.linkedin.com/search/results/companies/?keywords=Talentteno%20Institute',
        'youtube' => 'https://www.youtube.com/',
    ];

    $url = trim((string)$value);
    if ($url === '' || $url === '#') {
        $url = $fallbacks[$network] ?? '#';
    }

    if ($network === 'instagram') {
        $handle = trim($url);
        if (!preg_match('#^https?://#i', $handle)) {
            $handle = ltrim($handle, '@/');
            $handle = preg_replace('/[^a-z0-9._]/i', '', $handle) ?: 'talentteno__';
            if (strtolower($handle) === 'talentteno_') {
                return $instagramUrl;
            }
            return 'https://www.instagram.com/' . $handle . '/';
        }

        $parts = parse_url($handle);
        $path = trim((string)($parts['path'] ?? ''), '/');
        $profile = explode('/', $path)[0] ?? '';
        $profile = preg_replace('/[^a-z0-9._]/i', '', $profile) ?: 'talentteno__';
        if (strtolower($profile) === 'talentteno_') {
            return $instagramUrl;
        }
        return 'https://www.instagram.com/' . $profile . '/';
    }

    if ($network === 'linkedin') {
        if (preg_match('#^https?://#i', $url) && stripos(parse_url($url, PHP_URL_HOST) ?: '', 'linkedin.com') !== false) {
            return $url;
        }

        return $fallbacks['linkedin'];
    }

    if (!preg_match('#^https?://#i', $url)) {
        return $fallbacks[$network] ?? '#';
    }

    return $url;
}

function tt_social_click_url(?string $value, string $network): string
{
    return tt_social_url($value, $network);
}

function tt_google_maps_url(?array $settings = null): string
{
    $settings = $settings ?? tt_settings();
    $savedMapUrl = trim((string)($settings['map_embed_url'] ?? ''));
    if ($savedMapUrl !== '' && preg_match('#^https?://#i', $savedMapUrl)) {
        return $savedMapUrl;
    }

    $query = rawurlencode(trim('Talentteno Institute, ' . (string)($settings['address'] ?? '')));
    return 'https://www.google.com/maps/search/?api=1&query=' . $query;
}

function tt_google_maps_embed_url(?array $settings = null): string
{
    $settings = $settings ?? tt_settings();
    $savedMapUrl = trim((string)($settings['map_embed_url'] ?? ''));
    if (
        $savedMapUrl !== ''
        && preg_match('#^https?://#i', $savedMapUrl)
        && (
            stripos($savedMapUrl, '/maps/embed') !== false
            || stripos($savedMapUrl, 'output=embed') !== false
        )
    ) {
        return $savedMapUrl;
    }

    $query = rawurlencode(trim('Talentteno Institute, ' . (string)($settings['address'] ?? '')));
    return 'https://maps.google.com/maps?hl=en&z=16&iwloc=B&q=' . $query . '&output=embed';
}

function tt_company_profile(?array $settings = null): array
{
    $settings = $settings ?? tt_settings();

    return [
        'name' => $settings['site_name'],
        'type' => 'IT Training Institute',
        'tagline' => $settings['tagline'],
        'description' => $settings['seo_description'] ?: $settings['footer_description'],
        'address' => $settings['address'],
        'phone' => array_values(array_filter([$settings['phone1'], $settings['phone2']])),
        'email' => $settings['email'],
        'hours' => $settings['business_hours'] ?? 'Monday to Saturday, 9:00 AM to 7:00 PM',
        'stats' => [
            'students_trained' => $settings['total_students'],
            'expert_trainers' => $settings['total_trainers'],
            'career_support' => $settings['success_rate'],
            'average_rating' => $settings['avg_rating'] ?? '4.9',
        ],
        'services' => [
            'Basic to advanced IT training',
            'Live project training',
            'Free internship guidance',
            'Spoken English and soft-skill support',
            'Resume building and interview preparation',
            'Placement assistance',
            'Corporate and campus training',
        ],
        'social' => [
            'facebook' => tt_social_url($settings['facebook_url'] ?? '', 'facebook'),
            'instagram' => tt_social_url($settings['instagram_url'] ?? '', 'instagram'),
            'linkedin' => tt_social_url($settings['linkedin_url'] ?? '', 'linkedin'),
            'youtube' => tt_social_url($settings['youtube_url'] ?? '', 'youtube'),
        ],
    ];
}

function tt_plain_text(string $value, int $maxLength = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?? '');
    if (strlen($text) <= $maxLength) {
        return $text;
    }

    return rtrim(substr($text, 0, $maxLength - 3), ' .,;:-') . '...';
}

function tt_render_seo(array $page = []): void
{
    $settings = tt_settings();
    $title = trim((string)($page['title'] ?? $settings['seo_title'] ?? $settings['site_name']));
    $description = tt_plain_text((string)($page['description'] ?? $settings['seo_description'] ?? $settings['footer_description']), 170);
    $keywords = trim((string)($page['keywords'] ?? $settings['seo_keywords'] ?? ''));
    $canonical = tt_seo_url((string)($page['canonical'] ?? basename($_SERVER['SCRIPT_NAME'] ?? 'index.php')));
    $defaultLogo = 'assets/images/logot-transparent.png';
    $pageImage = (string)($page['image'] ?? $defaultLogo);
    $image = $pageImage === $defaultLogo
        ? tt_seo_url(tt_asset_url($defaultLogo))
        : tt_seo_url(tt_optimized_image_url($pageImage, 1200) ?: $pageImage);
    $type = $page['type'] ?? 'website';
    $robots = $page['robots'] ?? 'index, follow, max-image-preview:large';
    $company = tt_company_profile($settings);
    $sameAs = array_values(array_filter($company['social'], static fn($url): bool => is_string($url) && $url !== '' && $url !== '#'));
    $logo = tt_abs_url(tt_asset_url($defaultLogo));
    $organizationId = tt_site_base_url() . '#organization';
    $websiteId = tt_site_base_url() . '#website';
    $webpageId = $canonical . '#webpage';
    $businessSchema = [
        '@type' => ['EducationalOrganization', 'LocalBusiness'],
        '@id' => $organizationId,
        'name' => $settings['site_name'],
        'description' => $settings['seo_description'] ?: $settings['footer_description'],
        'url' => tt_site_base_url(),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logo,
        ],
        'image' => $image,
        'telephone' => $settings['phone1'],
        'email' => $settings['email'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $settings['address'],
            'addressLocality' => 'Madurai',
            'addressRegion' => 'Tamil Nadu',
            'postalCode' => '625014',
            'addressCountry' => 'IN',
        ],
        'areaServed' => ['Madurai', 'Tamil Nadu', 'India'],
        'priceRange' => 'Rs 8,000 - Rs 75,000',
        'openingHours' => 'Mo-Sa 09:00-19:00',
        'sameAs' => $sameAs,
        'makesOffer' => array_map(static fn(string $service): array => [
            '@type' => 'Offer',
            'itemOffered' => ['@type' => 'Service', 'name' => $service],
        ], $company['services']),
    ];
    $schemaGraph = [
        $businessSchema,
        [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => tt_site_base_url(),
            'name' => $settings['site_name'],
            'description' => $settings['seo_description'] ?: $settings['footer_description'],
            'publisher' => ['@id' => $organizationId],
            'inLanguage' => 'en-IN',
        ],
        [
            '@type' => 'WebPage',
            '@id' => $webpageId,
            'url' => $canonical,
            'name' => $title,
            'description' => $description,
            'isPartOf' => ['@id' => $websiteId],
            'about' => ['@id' => $organizationId],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url' => $image,
            ],
            'inLanguage' => 'en-IN',
        ],
    ];

    if (!empty($page['breadcrumbs']) && is_array($page['breadcrumbs'])) {
        $schemaGraph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => array_values(array_map(static fn(array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => (string)($item['name'] ?? ''),
                'item' => tt_seo_url((string)($item['url'] ?? 'index.php')),
            ], $page['breadcrumbs'], array_keys($page['breadcrumbs']))),
        ];
    }

    if (!empty($page['courses']) && is_array($page['courses'])) {
        $schemaGraph[] = [
            '@type' => 'ItemList',
            '@id' => $canonical . '#courses',
            'name' => $title,
            'itemListElement' => array_values(array_map(static fn(array $course, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@type' => 'Course',
                    'name' => (string)($course['name'] ?? $course['title'] ?? ''),
                    'description' => tt_plain_text((string)($course['desc'] ?? $course['description'] ?? ''), 160),
                    'provider' => ['@id' => tt_site_base_url() . '#organization'],
                ],
            ], array_slice($page['courses'], 0, 24), array_keys(array_slice($page['courses'], 0, 24)))),
        ];
    }

    if (!empty($page['schema'])) {
        $schemaGraph[] = $page['schema'];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => $schemaGraph,
    ];

    echo '<title>' . tt_h($title) . '</title>' . PHP_EOL;
    echo '    <meta name="author" content="' . tt_h($settings['site_name']) . '">' . PHP_EOL;
    echo '    <meta name="description" content="' . tt_h($description) . '">' . PHP_EOL;
    if ($keywords !== '') {
        echo '    <meta name="keywords" content="' . tt_h($keywords) . '">' . PHP_EOL;
    }
    echo '    <meta name="robots" content="' . tt_h($robots) . '">' . PHP_EOL;
    echo '    <meta name="theme-color" content="#11143d">' . PHP_EOL;
    echo '    <link rel="icon" type="image/png" href="' . tt_h(tt_abs_url(tt_asset_url($defaultLogo))) . '">' . PHP_EOL;
    echo '    <link rel="apple-touch-icon" href="' . tt_h(tt_abs_url(tt_asset_url($defaultLogo))) . '">' . PHP_EOL;
    echo '    <link rel="canonical" href="' . tt_h($canonical) . '">' . PHP_EOL;
    echo '    <link rel="alternate" hreflang="en-IN" href="' . tt_h($canonical) . '">' . PHP_EOL;
    echo '    <link rel="alternate" hreflang="x-default" href="' . tt_h($canonical) . '">' . PHP_EOL;
    echo '    <meta property="og:type" content="' . tt_h($type) . '">' . PHP_EOL;
    echo '    <meta property="og:locale" content="en_IN">' . PHP_EOL;
    echo '    <meta property="og:title" content="' . tt_h($title) . '">' . PHP_EOL;
    echo '    <meta property="og:description" content="' . tt_h($description) . '">' . PHP_EOL;
    echo '    <meta property="og:url" content="' . tt_h($canonical) . '">' . PHP_EOL;
    echo '    <meta property="og:image" content="' . tt_h($image) . '">' . PHP_EOL;
    echo '    <meta property="og:image:alt" content="' . tt_h($settings['site_name'] . ' logo') . '">' . PHP_EOL;
    echo '    <meta property="og:site_name" content="' . tt_h($settings['site_name']) . '">' . PHP_EOL;
    echo '    <meta name="twitter:card" content="summary_large_image">' . PHP_EOL;
    echo '    <meta name="twitter:title" content="' . tt_h($title) . '">' . PHP_EOL;
    echo '    <meta name="twitter:description" content="' . tt_h($description) . '">' . PHP_EOL;
    echo '    <meta name="twitter:image" content="' . tt_h($image) . '">' . PHP_EOL;
    echo '    <script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . PHP_EOL;
}

function tt_courses(int $limit = 0, bool $featuredOnly = false): array
{
    $where = $featuredOnly ? 'WHERE is_active = 1 AND is_featured = 1' : 'WHERE is_active = 1';
    $limitSql = $limit > 0 ? ' LIMIT ' . $limit : '';
    return tt_fetch_all("SELECT * FROM courses $where ORDER BY is_featured DESC, id DESC$limitSql");
}

function tt_course_highlights(array $course, array $fallback = []): array
{
    $raw = trim((string)($course['highlights'] ?? ''));
    $items = $raw === '' ? [] : preg_split('/\r\n|\r|\n/', $raw);
    $items = array_values(array_filter(array_map(static fn($item): string => trim($item), $items ?: [])));

    if ($items === []) {
        $items = $fallback;
    }

    if ($items === []) {
        $items = array_values(array_filter([
            !empty($course['duration']) ? $course['duration'] . ' training' : '',
            'Live projects',
            'Placement support',
        ]));
    }

    return array_slice($items, 0, 5);
}

function tt_courses_by_type(string $type): array
{
    if (!in_array($type, ['course', 'short', 'popular', 'advanced', 'designing', 'cyber'], true)) {
        return [];
    }

    $db = tt_db();
    if (!$db) {
        return [];
    }

    $column = @$db->query("SHOW COLUMNS FROM courses LIKE 'course_type'");
    if (!$column || $column->num_rows === 0) {
        return [];
    }

    $safeType = $db->real_escape_string($type);
    return tt_fetch_all("SELECT * FROM courses WHERE is_active = 1 AND course_type = '$safeType' ORDER BY is_featured DESC, id DESC");
}

function tt_services(int $limit = 0): array
{
    $services = tt_fetch_all('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
    $uniqueServices = [];
    $seen = [];

    foreach ($services as $service) {
        $title = trim((string)($service['title'] ?? ''));
        $shortDesc = trim((string)($service['short_desc'] ?? ''));
        $description = trim((string)($service['description'] ?? ''));
        $key = strtolower($title . '|' . ($shortDesc !== '' ? $shortDesc : $description));

        if ($title === '' || isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $uniqueServices[] = $service;
    }

    return $limit > 0 ? array_slice($uniqueServices, 0, $limit) : $uniqueServices;
}

function tt_process_steps(int $limit = 0): array
{
    $limitSql = $limit > 0 ? ' LIMIT ' . $limit : '';
    return tt_fetch_all("SELECT * FROM process_steps WHERE is_active = 1 ORDER BY sort_order ASC, step_number ASC$limitSql");
}

function tt_testimonials(int $limit = 3): array
{
    return tt_fetch_all("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id DESC LIMIT $limit");
}

function tt_review_showcase(): array
{
    $rows = tt_fetch_all("SELECT * FROM review_showcase WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    if ($rows) return $rows;
    return [
        ['image' => 'uploads/media/full-stack-development-20260703-133158-761383.png', 'title' => 'Full Stack Development', 'icon' => 'fa-code'],
        ['image' => 'uploads/media/data-science-ai-20260703-133112-527863.png', 'title' => 'AI & Machine Learning', 'icon' => 'fa-brain'],
        ['image' => 'uploads/media/cyber-security-20260703-133329-242125.png', 'title' => 'Cyber Security', 'icon' => 'fa-shield-halved'],
        ['image' => 'uploads/media/data-analyst-20260703-133130-702998.png', 'title' => 'Data Analyst', 'icon' => 'fa-chart-line'],
        ['image' => 'uploads/media/digital-marketing-20260703-133146-981935.png', 'title' => 'Digital Marketing', 'icon' => 'fa-bullhorn'],
        ['image' => 'uploads/media/programming-languages-20260703-133210-630417.png', 'title' => 'Programming Languages', 'icon' => 'fa-terminal'],
    ];
}

function tt_content_items(string $table, int $limit = 0): array
{
    if (!in_array($table, ['careers', 'blog_posts', 'projects', 'why_items', 'hiring_items', 'franchise_items'], true)) {
        return [];
    }

    $limitSql = $limit > 0 ? ' LIMIT ' . $limit : '';
    return tt_fetch_all("SELECT * FROM $table WHERE is_active = 1 ORDER BY sort_order ASC, id DESC$limitSql");
}

function tt_content_image_url(?string $image): string
{
    static $cache = [];

    $image = trim((string)$image);
    if (isset($cache[$image])) {
        return $cache[$image];
    }

    if ($image === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $cache[$image] = $image;
    }

    $image = ltrim($image, '/');
    $candidates = [];

    if (str_contains($image, '/')) {
        $decodedImage = ltrim(rawurldecode($image), '/');
        if ($decodedImage !== '' && !str_contains($decodedImage, '..')) {
            $candidates[] = [$image, __DIR__ . '/../' . $decodedImage];
        }
    } else {
        $file = basename($image);
        $candidates[] = ['uploads/offer-posters/' . rawurlencode($file), __DIR__ . '/../uploads/offer-posters/' . $file];
        $candidates[] = ['uploads/media/' . rawurlencode($file), __DIR__ . '/../uploads/media/' . $file];
        $candidates[] = ['uploads/course-images/' . rawurlencode($file), __DIR__ . '/../uploads/course-images/' . $file];
        $candidates[] = ['assets/images/' . rawurlencode($file), __DIR__ . '/../assets/images/' . $file];
    }

    foreach ($candidates as [$url, $path]) {
        if (is_file($path)) {
            return $url;
        }
    }

    return $cache[$image] = '';
}

function tt_optimized_image_url(?string $image, int $preferredWidth = 800): string
{
    static $cache = [];

    $cacheKey = trim((string)$image) . '|' . $preferredWidth;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $resolved = tt_content_image_url($image);
    if ($resolved === '' || preg_match('/^https?:\/\//i', $resolved)) {
        return $cache[$cacheKey] = $resolved;
    }

    $path = (string)(parse_url($resolved, PHP_URL_PATH) ?: $resolved);
    $path = ltrim(rawurldecode($path), '/');
    if ($path === '' || str_contains($path, '..')) {
        return $cache[$cacheKey] = $resolved;
    }

    if (str_starts_with($path, 'uploads/optimized/') || str_starts_with($path, 'assets/images/optimized/')) {
        return $cache[$cacheKey] = $resolved;
    }

    $base = pathinfo(basename($path), PATHINFO_FILENAME);
    $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $base), '-'));
    if ($base === '') {
        return $cache[$cacheKey] = $resolved;
    }

    $preferredWidth = max(1, (int)$preferredWidth);
    $exactCandidates = [
        'uploads/optimized/' . $base . '-w' . $preferredWidth . '.webp',
        'assets/images/optimized/' . $base . '-w' . $preferredWidth . '.webp',
    ];

    foreach ($exactCandidates as $candidate) {
        if (is_file(__DIR__ . '/../' . $candidate)) {
            return $cache[$cacheKey] = $candidate;
        }
    }

    $generated = 'uploads/optimized/' . $base . '-w' . $preferredWidth . '.webp';
    if (tt_generate_optimized_image($path, $generated, $preferredWidth)) {
        return $cache[$cacheKey] = $generated;
    }

    $fallbackWidths = array_values(array_unique([
        400,
        430,
        768,
        800,
        900,
        1200,
        1400,
        1536,
    ]));

    $candidates = [];
    if (str_starts_with($path, 'assets/images/')) {
        $candidates[] = 'assets/images/optimized/' . $base . '-mobile.webp';
        $candidates[] = 'assets/images/optimized/' . $base . '-desktop.webp';
    }

    foreach ($fallbackWidths as $width) {
        $candidates[] = 'uploads/optimized/' . $base . '-w' . (int)$width . '.webp';
        $candidates[] = 'assets/images/optimized/' . $base . '-w' . (int)$width . '.webp';
    }

    foreach ($candidates as $candidate) {
        if (is_file(__DIR__ . '/../' . $candidate)) {
            return $cache[$cacheKey] = $candidate;
        }
    }

    return $cache[$cacheKey] = $resolved;
}

function tt_optimized_image_srcset(?string $image, array $widths = [400, 800, 1200]): string
{
    $items = [];
    $seen = [];

    foreach ($widths as $width) {
        $width = max(1, (int)$width);
        $candidate = tt_optimized_image_url($image, $width);
        if ($candidate === '' || preg_match('/^https?:\/\//i', $candidate) || isset($seen[$candidate])) {
            continue;
        }

        $seen[$candidate] = true;
        $items[] = $candidate . ' ' . $width . 'w';
    }

    return implode(', ', $items);
}

function tt_generate_optimized_image(string $sourcePath, string $targetPath, int $width): bool
{
    if (!function_exists('imagewebp')) {
        return false;
    }

    $sourcePath = ltrim(rawurldecode($sourcePath), '/');
    if ($sourcePath === '' || str_contains($sourcePath, '..')) {
        return false;
    }

    if (!str_starts_with($sourcePath, 'uploads/') && !str_starts_with($sourcePath, 'assets/images/')) {
        return false;
    }

    $source = __DIR__ . '/../' . $sourcePath;
    if (!is_file($source)) {
        return false;
    }

    $info = @getimagesize($source);
    if (!$info || empty($info[0]) || empty($info[1])) {
        return false;
    }

    $create = match ((int)$info[2]) {
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG => 'imagecreatefrompng',
        IMAGETYPE_WEBP => 'imagecreatefromwebp',
        default => '',
    };
    if ($create === '' || !function_exists($create)) {
        return false;
    }

    $src = @$create($source);
    if (!$src) {
        return false;
    }

    $sourceWidth = (int)$info[0];
    $sourceHeight = (int)$info[1];
    $scale = min(1, max(1, $width) / max(1, $sourceWidth));
    $targetWidth = max(1, (int)round($sourceWidth * $scale));
    $targetHeight = max(1, (int)round($sourceHeight * $scale));
    $dst = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($dst, true);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

    $target = __DIR__ . '/../' . ltrim($targetPath, '/');
    $dir = dirname($target);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $ok = imagewebp($dst, $target, 72);
    imagedestroy($dst);
    imagedestroy($src);

    return $ok && is_file($target);
}

function tt_item_image(array $item, string $type = 'general'): string
{
    $stored = tt_optimized_image_url($item['image'] ?? '', 800);
    if ($stored !== '') {
        return $stored;
    }

    $text = strtolower(($item['title'] ?? '') . ' ' . ($item['short_desc'] ?? '') . ' ' . ($item['description'] ?? '') . ' ' . $type);
    $map = [
        'cyber' => 'uploads/media/cyber-security-20260703-133329-242125.png',
        'security' => 'uploads/media/cyber-security-20260703-133329-242125.png',
        'data' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'dashboard' => 'uploads/media/data-analyst-20260703-133130-702998.png',
        'full stack' => 'uploads/media/full-stack-development-20260703-133158-761383.png',
        'website' => 'uploads/media/full-stack-development-20260703-133158-761383.png',
        'digital' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'marketing' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'programming' => 'uploads/media/programming-languages-20260703-133210-630417.png',
        'career' => 'assets/images/contact-counsellor-hero.png',
        'placement' => 'assets/images/contact-counsellor-hero.png',
        'interview' => 'assets/images/contact-counsellor-hero.png',
        'blog' => 'assets/images/home1.webp',
        'project' => 'assets/images/home2.webp',
        'service' => 'assets/images/home.webp',
    ];

    foreach ($map as $needle => $image) {
        $optimized = tt_optimized_image_url($image, 800);
        if (str_contains($text, $needle) && $optimized !== '') {
            return $optimized;
        }
    }

    return tt_optimized_image_url('assets/images/home2.webp', 800) ?: 'assets/images/home2.webp';
}

function tt_home_slider_images(): array
{
    $fallback = [
        ['image' => 'assets/images/home.webp', 'mobile_image' => 'assets/images/optimized/home-mobile.webp'],
        ['image' => 'assets/images/home1.webp', 'mobile_image' => 'assets/images/optimized/home1-mobile.webp'],
        ['image' => 'assets/images/home2.webp', 'mobile_image' => 'assets/images/optimized/home2-mobile.webp'],
        ['image' => 'assets/images/home3.webp', 'mobile_image' => 'assets/images/optimized/home3-mobile.webp'],
        ['image' => 'assets/images/home4.webp', 'mobile_image' => 'assets/images/optimized/home4-mobile.webp'],
    ];

    $defaultMobileImages = [];
    foreach ($fallback as $slide) {
        $defaultMobileImages[$slide['image']] = $slide['mobile_image'];
    }

    $hasTable = tt_fetch_one("SHOW TABLES LIKE 'home_slides'");
    $hasDisplayOrder = $hasTable ? tt_fetch_one("SHOW COLUMNS FROM home_slides LIKE 'display_order'") : null;
    $hasMobileImage = $hasTable ? tt_fetch_one("SHOW COLUMNS FROM home_slides LIKE 'mobile_image'") : null;
    $orderSql = $hasDisplayOrder
        ? 'display_order ASC, sort_order ASC, updated_at DESC, id DESC'
        : 'sort_order ASC, updated_at DESC, id DESC';
    $imageSql = $hasMobileImage ? 'image, mobile_image, title' : 'image, title';
    $rows = $hasTable ? tt_fetch_all("SELECT $imageSql FROM home_slides WHERE is_active = 1 ORDER BY $orderSql") : [];
    $images = [];
    $seenImages = [];
    foreach ($rows as $row) {
        $image = tt_content_image_url($row['image'] ?? '');
        $mobileImage = $hasMobileImage ? tt_content_image_url($row['mobile_image'] ?? '') : '';
        if ($mobileImage === '' && isset($defaultMobileImages[$row['image'] ?? ''])) {
            $defaultMobileImage = tt_content_image_url($defaultMobileImages[$row['image']]);
            $mobileImage = $defaultMobileImage !== '' ? $defaultMobileImage : '';
        }
        if ($image === '' || isset($seenImages[$image])) {
            continue;
        }
        $seenImages[$image] = true;
        $images[] = [
            'image' => $image,
            'mobile_image' => $mobileImage !== '' ? $mobileImage : $image,
            'title' => trim((string)($row['title'] ?? '')),
        ];
    }

    if ($images) {
        return $images;
    }

    return array_map(static fn(array $slide): array => [
        'image' => $slide['image'],
        'mobile_image' => tt_content_image_url($slide['mobile_image']) !== '' ? $slide['mobile_image'] : $slide['image'],
        'title' => '',
    ], $fallback);
}

function tt_careers(int $limit = 0): array
{
    return tt_content_items('careers', $limit);
}

function tt_blog_posts(int $limit = 0): array
{
    return tt_content_items('blog_posts', $limit);
}

function tt_projects(int $limit = 0): array
{
    return tt_content_items('projects', $limit);
}

function tt_why_items(int $limit = 0): array
{
    return tt_content_items('why_items', $limit);
}

function tt_hiring_items(int $limit = 0): array
{
    return tt_content_items('hiring_items', $limit);
}

function tt_franchise_items(int $limit = 0): array
{
    return tt_content_items('franchise_items', $limit);
}

function tt_offer_default_image(string $text = ''): string
{
    $text = strtolower($text);
    $map = [
        'cyber' => 'uploads/media/cyber-security-20260703-133329-242125.png',
        'security' => 'uploads/media/cyber-security-20260703-133329-242125.png',
        'data' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'ai' => 'uploads/media/data-science-ai-20260703-133112-527863.png',
        'digital' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'marketing' => 'uploads/media/digital-marketing-20260703-133146-981935.png',
        'design' => 'assets/images/design .png',
        'student' => 'assets/images/home1.webp',
        'group' => 'assets/images/home2.webp',
        'online' => 'assets/images/home3.webp',
        'career' => 'assets/images/contact-counsellor-hero.png',
    ];

    foreach ($map as $needle => $image) {
        $optimized = tt_optimized_image_url($image, 800);
        if (str_contains($text, $needle) && $optimized !== '') {
            return $optimized;
        }
    }

    return tt_optimized_image_url('assets/images/home.webp', 1400) ?: 'assets/images/home.webp';
}

function tt_offer_image(array $offer): string
{
    $stored = tt_optimized_image_url($offer['poster_image'] ?? '', 800);
    if ($stored !== '') {
        return $stored;
    }

    return tt_offer_default_image(($offer['title'] ?? '') . ' ' . ($offer['course_name'] ?? '') . ' ' . ($offer['category'] ?? ''));
}

function tt_offer_hero_image(array $offer): string
{
    $stored = tt_optimized_image_url($offer['hero_image'] ?? '', 1400);
    if ($stored !== '') {
        return $stored;
    }

    $poster = tt_optimized_image_url($offer['poster_image'] ?? '', 1400);
    if ($poster !== '') {
        return $poster;
    }

    return tt_offer_default_image(($offer['title'] ?? '') . ' ' . ($offer['course_name'] ?? '') . ' ' . ($offer['category'] ?? ''));
}

function tt_offer_slider_images(array $offers, int $limit = 5): array
{
    $limit = max(1, $limit);
    $rankedOffers = $offers;
    usort($rankedOffers, static function (array $a, array $b): int {
        $homeCompare = (int)($b['show_on_home_page'] ?? 0) <=> (int)($a['show_on_home_page'] ?? 0);
        if ($homeCompare !== 0) return $homeCompare;
        $featuredCompare = (int)($b['is_featured'] ?? 0) <=> (int)($a['is_featured'] ?? 0);
        if ($featuredCompare !== 0) return $featuredCompare;
        $orderCompare = (int)($a['display_order'] ?? 0) <=> (int)($b['display_order'] ?? 0);
        if ($orderCompare !== 0) return $orderCompare;
        return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
    });

    $slides = [];
    $seen = [];
    foreach ($rankedOffers as $offer) {
        $image = tt_offer_hero_image($offer);
        if ($image === '' || isset($seen[$image])) {
            continue;
        }
        $seen[$image] = true;
        $slides[] = [
            'image' => $image,
            'mobile_image' => $image,
            'title' => trim((string)($offer['title'] ?? 'Course Offer')),
            'alt' => trim((string)($offer['hero_alt'] ?? '')) ?: (trim((string)($offer['poster_alt'] ?? '')) ?: trim((string)($offer['title'] ?? 'Talentteno course offer'))),
        ];
        if (count($slides) >= $limit) {
            return $slides;
        }
    }

    foreach (tt_home_slider_images() as $index => $slide) {
        $image = tt_optimized_image_url($slide['image'] ?? '', 1400) ?: tt_content_image_url($slide['image'] ?? '');
        if ($image === '' || isset($seen[$image])) {
            continue;
        }
        $mobileImage = tt_optimized_image_url($slide['mobile_image'] ?? '', 900) ?: $image;
        $seen[$image] = true;
        $slides[] = [
            'image' => $image,
            'mobile_image' => $mobileImage,
            'title' => trim((string)($slide['title'] ?? '')),
            'alt' => 'Talentteno course offer image ' . ($index + 1),
        ];
        if (count($slides) >= $limit) {
            break;
        }
    }

    return $slides;
}

function tt_offer_lines(?string $text, int $limit = 5): array
{
    $lines = preg_split('/\r\n|\r|\n/', trim((string)$text)) ?: [];
    $lines = array_values(array_filter(array_map('trim', $lines), static fn(string $line): bool => $line !== ''));
    return $limit > 0 ? array_slice($lines, 0, $limit) : $lines;
}

function tt_offer_money($value, string $currency = 'INR'): string
{
    $amount = (float)($value ?? 0);
    if ($amount <= 0) {
        return '';
    }

    $prefix = strtoupper($currency) === 'INR' ? 'Rs ' : strtoupper($currency) . ' ';
    return $prefix . number_format($amount, 0);
}

function tt_offers(): array
{
    $hasTable = tt_fetch_one("SHOW TABLES LIKE 'offers'");
    if ($hasTable) {
        $rows = tt_fetch_all("
            SELECT *
            FROM offers
            WHERE status = 'active'
              AND show_on_offers_page = 1
              AND (start_date IS NULL OR start_date <= CURDATE())
              AND (end_date IS NULL OR end_date >= CURDATE())
            ORDER BY is_featured DESC, display_order ASC, end_date IS NULL ASC, end_date ASC, id DESC
        ");
        if ($rows) {
            return $rows;
        }
    }

    return [
        [
            'title' => 'Basic to Advanced Course Offer',
            'course_name' => 'Selected IT Courses',
            'category' => 'Development',
            'badge_text' => 'Popular Offer',
            'short_description' => 'Start from fundamentals and move into practical job-ready training with mentor guidance.',
            'full_description' => 'Best for students and freshers who want a complete guided path with projects, certification and career support.',
            'poster_image' => 'assets/images/home.webp',
            'original_fee' => 25000,
            'offer_fee' => 14999,
            'discount_percentage' => 40,
            'course_duration' => '3 to 6 Months',
            'training_mode' => 'Offline',
            'batch_timing' => 'Weekday and weekend batches',
            'course_level' => 'Beginner',
            'certificate_available' => 1,
            'internship_available' => 1,
            'placement_available' => 1,
            'highlights' => "Live classes\nPractical assignments\nCertificate support\nInterview guidance",
            'learning_outcomes' => "Build portfolio projects\nUnderstand real workflow\nPrepare for entry-level roles",
            'terms_conditions' => "Offer available for selected batches\nSeat confirmation depends on registration",
            'cta_label' => 'Claim Offer',
            'cta_url' => 'contact.php?topic=Basic+to+Advanced+Course+Offer',
            'is_featured' => 1,
        ],
        [
            'title' => 'Cyber Security Combo Pack',
            'course_name' => 'Cyber Security',
            'category' => 'Security',
            'badge_text' => 'Combo Deal',
            'short_description' => 'Learn networking, ethical hacking basics, practical labs and security reporting in one guided combo.',
            'full_description' => 'Designed for learners who want a focused security pathway with lab practice and career support.',
            'poster_image' => 'uploads/media/cyber-security-20260703-133329-242125.png',
            'original_fee' => 75000,
            'offer_fee' => 49999,
            'discount_percentage' => 33,
            'course_duration' => '6 Months',
            'training_mode' => 'Hybrid',
            'batch_timing' => 'Flexible batch timing',
            'course_level' => 'Intermediate',
            'certificate_available' => 1,
            'internship_available' => 1,
            'placement_available' => 1,
            'highlights' => "Security lab practice\nNetwork basics\nEthical hacking workflow\nResume support",
            'learning_outcomes' => "Practice safe security testing\nCreate reports\nPrepare for support roles",
            'terms_conditions' => "Combo offer cannot be combined with other discounts\nContact office for current seat availability",
            'cta_label' => 'Reserve Seat',
            'cta_url' => 'contact.php?topic=Cyber+Security+Combo+Offer',
            'is_featured' => 1,
        ],
        [
            'title' => 'Student Group Enrollment',
            'course_name' => 'All Eligible Courses',
            'category' => 'Students',
            'badge_text' => 'Group Benefit',
            'short_description' => 'Enroll with friends and get structured training support with practical sessions and counselling.',
            'full_description' => 'Useful for college students, classmates and small teams who want the same batch timing.',
            'poster_image' => 'assets/images/home2.webp',
            'original_fee' => 0,
            'offer_fee' => 0,
            'discount_percentage' => 0,
            'course_duration' => 'Course based',
            'training_mode' => 'Offline',
            'batch_timing' => 'Custom group slots',
            'course_level' => 'Beginner',
            'certificate_available' => 1,
            'internship_available' => 1,
            'placement_available' => 1,
            'highlights' => "Group batch planning\nMentor-led practice\nCareer counselling\nCertificate support",
            'learning_outcomes' => "Learn together\nFinish practical tasks\nPrepare for interviews",
            'terms_conditions' => "Minimum group size applies\nOffer depends on selected course and batch",
            'cta_label' => 'Ask Group Fee',
            'cta_url' => 'contact.php?topic=Group+Enrollment+Offer',
            'is_featured' => 0,
        ],
    ];
}

function tt_money($value): string
{
    $amount = (float)($value ?? 0);
    return $amount > 0 ? 'Rs ' . number_format($amount, 0) . '/-' : 'Contact for fee';
}

function tt_course_image_url(?string $image): string
{
    $file = basename(trim((string)$image));
    if ($file === '') {
        return '';
    }

    $path = __DIR__ . '/../uploads/course-images/' . $file;
    if (!is_file($path)) {
        return '';
    }

    $original = 'uploads/course-images/' . rawurlencode($file);
    return tt_optimized_image_url($original, 800) ?: $original;
}

function tt_course_brochure_exists(?string $brochure): bool
{
    $file = basename(trim((string)$brochure));
    return $file !== '' && is_file(__DIR__ . '/../uploads/brochures/' . $file);
}

function tt_course_icon(string $category): string
{
    $map = [
        'Data & AI' => 'fa-database',
        'Development' => 'fa-code',
        'Marketing' => 'fa-bullhorn',
        'Security' => 'fa-shield-halved',
        'Design' => 'fa-pen-nib',
        'Business' => 'fa-calculator',
    ];

    return $map[$category] ?? 'fa-laptop-code';
}

function tt_notify_company_enquiry(array $details): void
{
    $settings = tt_settings();
    $to = trim((string)($settings['email'] ?? ''));
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $siteName = trim((string)($settings['site_name'] ?? 'Talentteno Institute'));
    $type = ucfirst((string)($details['type'] ?? 'enquiry'));
    $name = trim((string)($details['name'] ?? ''));
    $phone = trim((string)($details['phone'] ?? ''));
    $email = trim((string)($details['email'] ?? ''));
    $course = trim((string)($details['course'] ?? ''));
    $message = trim((string)($details['message'] ?? ''));
    $submittedAt = date('d M Y h:i A');

    $subject = $siteName . ' - New ' . $type . ' Received';
    $lines = [
        'New ' . $type . ' received from the website.',
        '',
        'Name: ' . ($name !== '' ? $name : '-'),
        'Phone: ' . ($phone !== '' ? $phone : '-'),
        'Email: ' . ($email !== '' ? $email : '-'),
        'Course: ' . ($course !== '' ? $course : '-'),
        'Type: ' . $type,
        'Submitted: ' . $submittedAt,
        '',
        'Person / Student Details:',
        $message !== '' ? $message : '-',
    ];

    $host = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['HTTP_HOST'] ?? 'talentteno.local');
    $host = $host !== '' ? preg_replace('/:\d+$/', '', $host) : 'talentteno.local';
    $from = 'no-reply@' . $host;
    $headers = [
        'From: ' . $siteName . ' <' . $from . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
    }

    @mail($to, $subject, implode("\n", $lines), implode("\r\n", $headers));
}

function tt_submit_enquiry(array $input, string $source = 'enquiry'): array
{
    $db = tt_db();
    if (!$db) {
        return ['ok' => false, 'message' => 'Database is not connected.'];
    }

    $name = trim((string)($input['name'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $phone = trim((string)($input['phone'] ?? ''));
    $courseName = trim((string)($input['course'] ?? ''));
    $message = trim((string)($input['message'] ?? ''));
    $type = in_array($source, ['enquiry', 'download', 'callback'], true) ? $source : 'enquiry';

    if (trim((string)($input['website'] ?? '')) !== '') {
        return ['ok' => true, 'message' => 'Successfully submitted. Our counsellor will contact you soon.'];
    }

    if ($name === '' || $phone === '') {
        return ['ok' => false, 'message' => 'Please fill all required details before submitting.'];
    }

    if (strlen($name) < 2 || strlen($name) > 80) {
        return ['ok' => false, 'message' => 'Please enter a valid name (2–80 characters).'];
    }

    $phoneDigits = preg_replace('/\D+/', '', $phone);
    if (!preg_match('/^[6-9][0-9]{9}$/', $phoneDigits)) {
        return ['ok' => false, 'message' => 'Please enter a valid 10 digit mobile number.'];
    }
    $phone = $phoneDigits;

    if ($type === 'callback' && ($email === '' || $courseName === '')) {
        return ['ok' => false, 'message' => 'Please fill name, phone, email and course to get free counselling.'];
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Please enter a valid email address.'];
    }

    if ($email === '') {
        $email = 'not-provided@talentteno.local';
    }

    $name = mb_substr($name, 0, 80);
    $email = mb_substr($email, 0, 190);
    $phone = mb_substr($phone, 0, 24);
    $courseName = mb_substr($courseName, 0, 150);
    $message = mb_substr($message, 0, 2000);
    $resumePath = mb_substr(trim((string)($input['resume_path'] ?? '')), 0, 255);

    $hasResumeColumn = false;
    $resumeColumnResult = @$db->query("SHOW COLUMNS FROM enquiries LIKE 'resume_path'");
    if ($resumeColumnResult && $resumeColumnResult->num_rows > 0) {
        $hasResumeColumn = true;
    }

    if ($hasResumeColumn) {
        $stmt = $db->prepare('INSERT INTO enquiries (name, email, phone, course_name, message, type, resume_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, "new")');
    } else {
        $stmt = $db->prepare('INSERT INTO enquiries (name, email, phone, course_name, message, type, status) VALUES (?, ?, ?, ?, ?, ?, "new")');
    }
    if (!$stmt) {
        return ['ok' => false, 'message' => 'Unable to save enquiry.'];
    }

    if ($hasResumeColumn) {
        $stmt->bind_param('sssssss', $name, $email, $phone, $courseName, $message, $type, $resumePath);
    } else {
        $stmt->bind_param('ssssss', $name, $email, $phone, $courseName, $message, $type);
    }
    if (!$stmt->execute()) {
        return ['ok' => false, 'message' => 'Unable to save enquiry.'];
    }

    tt_notify_company_enquiry([
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'course' => $courseName,
        'message' => $message,
        'type' => $type,
    ]);

    return ['ok' => true, 'message' => 'Successfully submitted. Our counsellor will contact you soon.'];
}
?>
