<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

define('DB_OPTIONAL', false);
require_once __DIR__ . '/../backend/includes/db.php';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function api_json(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function api_ok(array $payload = []): void
{
    api_json(200, ['success' => true] + $payload);
}

function api_fail(int $status, string $code, string $message, array $errors = [], array $extra = []): void
{
    api_json($status, ['success' => false, 'code' => $code, 'message' => $message, 'errors' => $errors] + $extra);
}

function api_input(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

function envv(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    $aliases = [
        'SMTP_HOST' => 'MAIL_HOST',
        'SMTP_PORT' => 'MAIL_PORT',
        'SMTP_USER' => 'MAIL_USERNAME',
        'SMTP_PASS' => 'MAIL_PASSWORD',
        'SMTP_FROM_EMAIL' => 'MAIL_FROM',
        'SMTP_FROM_NAME' => 'MAIL_FROM_NAME',
    ];
    if (isset($aliases[$key])) {
        $aliasValue = getenv($aliases[$key]);
        if ($aliasValue !== false) {
            return $aliasValue;
        }
    }
    return $default;
}

function normalize_text(mixed $value): string
{
    return trim(preg_replace('/\s+/', ' ', (string)($value ?? '')) ?? '');
}

function normalize_email(mixed $value): string
{
    return strtolower(trim(preg_replace('/\s+/', '', (string)($value ?? '')) ?? ''));
}

function normalize_mobile(mixed $value): string
{
    $mobile = preg_replace('/[\s-]+/', '', (string)($value ?? '')) ?? '';
    if (str_starts_with($mobile, '+91')) {
        $mobile = substr($mobile, 3);
    } elseif (preg_match('/^91[6-9][0-9]{9}$/', $mobile)) {
        $mobile = substr($mobile, 2);
    }
    return preg_replace('/\D+/', '', $mobile) ?? '';
}

function api_secret(): string
{
    return envv('BROCHURE_TOKEN_SECRET', envv('SESSION_SECRET', 'talentteno-local-brochure-secret'));
}

function hash_value(string $value): string
{
    return hash_hmac('sha256', $value, api_secret());
}

function hash_otp(string $value): string
{
    return hash('sha256', $value);
}

function random_token(int $bytes = 32): string
{
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
}

function client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        $value = trim((string)($_SERVER[$key] ?? ''));
        if ($value !== '') {
            return substr(trim(explode(',', $value)[0]), 0, 64);
        }
    }
    return '';
}

function db(): mysqli
{
    global $conn;
    if (!$conn instanceof mysqli) {
        api_fail(500, 'DB_UNAVAILABLE', 'Database is not connected.');
    }
    return $conn;
}

function ensure_brochure_tables(): void
{
    $db = db();
    $db->query("CREATE TABLE IF NOT EXISTS brochure_otp_verifications (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(190) NULL,
        mobile VARCHAR(10) NOT NULL,
        course_title VARCHAR(150) NOT NULL,
        otp_hash VARCHAR(255) NOT NULL,
        verification_ref_hash VARCHAR(255) NULL,
        verification_token_hash VARCHAR(255) NULL,
        token_expires_at DATETIME NULL,
        expires_at DATETIME NOT NULL,
        resend_available_at DATETIME NOT NULL,
        attempt_count INT NOT NULL DEFAULT 0,
        send_count INT NOT NULL DEFAULT 1,
        verified_at DATETIME NULL,
        used_at DATETIME NULL,
        ip_address VARCHAR(64) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_bov_email (email),
        INDEX idx_bov_mobile (mobile),
        INDEX idx_bov_ip_created (ip_address, created_at),
        INDEX idx_bov_token (verification_token_hash)
    )");
    $db->query("CREATE TABLE IF NOT EXISTS brochure_download_leads (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        course_id INT NULL,
        course_title VARCHAR(150) NOT NULL,
        full_name VARCHAR(50) NOT NULL,
        email VARCHAR(190) NOT NULL,
        mobile VARCHAR(10) NOT NULL,
        degree VARCHAR(100) NOT NULL,
        college VARCHAR(150) NOT NULL,
        address VARCHAR(300) NOT NULL,
        current_status VARCHAR(30) NOT NULL,
        mobile_verified TINYINT(1) NOT NULL DEFAULT 0,
        otp_verification_id BIGINT UNSIGNED NOT NULL,
        captcha_verified TINYINT(1) NOT NULL DEFAULT 0,
        ip_address VARCHAR(64) NULL,
        user_agent VARCHAR(255) NULL,
        download_token_hash VARCHAR(255) NOT NULL,
        token_expires_at DATETIME NOT NULL,
        downloaded_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_bdl_email (email),
        INDEX idx_bdl_mobile (mobile),
        INDEX idx_bdl_download_token_hash (download_token_hash)
    )");
}

function find_course(string $title): ?array
{
    $db = db();
    $stmt = $db->prepare('SELECT id, title, slug, brochure_file FROM courses WHERE is_active = 1 AND LOWER(title) = LOWER(?) LIMIT 1');
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    return $course ?: null;
}

function resolve_course(string $title): ?array
{
    if ($title === '' || preg_match('/[<>]/', $title) || strlen($title) > 150) {
        return null;
    }
    return find_course($title) ?: [
        'id' => null,
        'title' => $title,
        'slug' => $title,
        'brochure_file' => '',
    ];
}

function smtp_missing_fields(): array
{
    $required = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_FROM_EMAIL'];
    $missing = [];
    foreach ($required as $field) {
        $value = trim(envv($field));
        if ($value === '' || ($field === 'SMTP_PASS' && preg_match('/^your[_-]|google[_-]?app[_-]?password|app[_-]?password[_-]?here|your[_-]?gmail[_-]?app[_-]?password/i', $value))) {
            $missing[] = $field;
        }
    }
    return $missing;
}

function fallback_from_email(): string
{
    $configured = trim(envv('SMTP_FROM_EMAIL', envv('SMTP_USER', envv('MAIL_FROM', ''))));
    if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_EMAIL)) {
        return $configured;
    }
    $host = preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? 'talentteno.local'));
    $host = preg_replace('/[^a-z0-9.-]/i', '', $host) ?: 'talentteno.local';
    return 'no-reply@' . $host;
}

function send_otp_with_php_mail(string $name, string $email, string $otp, string $course): bool
{
    $fromEmail = fallback_from_email();
    $fromName = trim(envv('SMTP_FROM_NAME', envv('MAIL_FROM_NAME', 'Talentteno Institute')));
    $expires = (int)envv('OTP_EXPIRY_MINUTES', '10');
    $subject = 'Your OTP for Talentteno Brochure Download';
    $body = implode("\n", [
        'Talentteno Institute',
        '',
        'Hi ' . ($name ?: 'Student') . ',',
        '',
        'Your 6-digit OTP for ' . ($course ?: 'Talentteno Course Brochure') . ' is ' . $otp . '.',
        'This OTP is valid for ' . $expires . ' minutes.',
        '',
        'Do not share this OTP with anyone.',
        '',
        'Talentteno Institute',
    ]);
    $headers = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];
    return @mail($email, $subject, $body, implode("\r\n", $headers));
}

function send_otp_email(string $name, string $email, string $otp, string $course): void
{
    $missing = smtp_missing_fields();
    if ($missing) {
        if (send_otp_with_php_mail($name, $email, $otp, $course)) {
            return;
        }
        api_fail(500, 'EMAIL_SEND_FAILED', 'Unable to send OTP email. Please try again.');
    }
    if (!class_exists(PHPMailer::class)) {
        if (send_otp_with_php_mail($name, $email, $otp, $course)) {
            return;
        }
        api_fail(500, 'EMAIL_SEND_FAILED', 'Unable to send OTP email. Please try again.');
    }

    $mail = new PHPMailer(true);
    try {
        $fromEmail = trim(envv('SMTP_FROM_EMAIL', envv('SMTP_USER')));
        $fromName = trim(envv('SMTP_FROM_NAME', 'Talentteno Institute'));
        $expires = (int)envv('OTP_EXPIRY_MINUTES', '10');

        $mail->isSMTP();
        $mail->Host = envv('SMTP_HOST');
        $mail->Port = (int)envv('SMTP_PORT', '587');
        $mail->SMTPAuth = true;
        $mail->Username = trim(envv('SMTP_USER'));
        $mail->Password = trim(envv('SMTP_PASS'));
        $mail->SMTPSecure = filter_var(envv('SMTP_SECURE', 'false'), FILTER_VALIDATE_BOOL)
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email);
        $mail->Subject = 'Your OTP for Talentteno Brochure Download';
        $mail->isHTML(true);
        $safeName = htmlspecialchars($name ?: 'Student', ENT_QUOTES, 'UTF-8');
        $safeCourse = htmlspecialchars($course ?: 'Talentteno Course Brochure', ENT_QUOTES, 'UTF-8');
        $mail->Body = '<div style="font-family:Arial,sans-serif;line-height:1.5;color:#111827">'
            . '<h2 style="margin:0 0 12px;color:#0845b2">Talentteno Institute</h2>'
            . '<p>Hi ' . $safeName . ',</p>'
            . '<p>Your 6-digit OTP for <strong>' . $safeCourse . '</strong> is:</p>'
            . '<p style="font-size:28px;font-weight:700;letter-spacing:4px;margin:18px 0;color:#0845b2">' . $otp . '</p>'
            . '<p>This OTP is valid for ' . $expires . ' minutes.</p>'
            . '<p><strong>Do not share this OTP with anyone.</strong></p></div>';
        $mail->AltBody = "Hi {$name},\n\nYour OTP for {$course} is {$otp}. It is valid for {$expires} minutes.\n\nTalentteno Institute";
        $mail->send();
    } catch (MailException $e) {
        error_log('OTP email send failed: ' . $e->getMessage());
        if (send_otp_with_php_mail($name, $email, $otp, $course)) {
            return;
        }
        api_fail(500, 'EMAIL_SEND_FAILED', 'Unable to send OTP email. Please try again.');
    }
}

function handle_send_otp(): void
{
    ensure_brochure_tables();
    $input = api_input();
    $name = normalize_text($input['name'] ?? '');
    $email = normalize_email($input['email'] ?? '');
    $mobile = normalize_mobile($input['mobile'] ?? ($input['phone'] ?? ''));
    $courseTitle = normalize_text($input['course'] ?? ($input['courseTitle'] ?? ''));
    $errors = [];

    if (strlen($name) < 3 || strlen($name) > 50) $errors['name'] = 'Please enter a valid full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) $errors['mobile'] = 'Please enter a valid 10-digit mobile number.';
    $course = resolve_course($courseTitle);
    if (!$course) $errors['course'] = 'Please select a valid course.';
    if ($errors) api_fail(422, 'VALIDATION_FAILED', 'Please correct the highlighted fields.', $errors);

    $db = db();
    $stmt = $db->prepare('SELECT GREATEST(TIMESTAMPDIFF(SECOND, NOW(), resend_available_at), 0) AS retry_after FROM brochure_otp_verifications WHERE email = ? AND course_title = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('ss', $email, $course['title']);
    $stmt->execute();
    $recent = $stmt->get_result()->fetch_assoc();
    $retryAfter = (int)($recent['retry_after'] ?? 0);
    if ($retryAfter > 0) {
        api_fail(429, 'OTP_RESEND_WAIT', 'Please wait before requesting another OTP.', ['email' => "Please wait {$retryAfter} seconds before requesting another OTP."], ['resendAfter' => $retryAfter]);
    }

    $otp = (string)random_int(100000, 999999);
    send_otp_email($name, $email, $otp, (string)$course['title']);

    $otpHash = hash_otp($otp);
    $ip = client_ip();
    $expiry = max(1, (int)envv('OTP_EXPIRY_MINUTES', '10'));
    $resend = max(15, (int)envv('OTP_RESEND_SECONDS', '60'));
    $stmt = $db->prepare('UPDATE brochure_otp_verifications SET used_at = NOW() WHERE email = ? AND course_title = ? AND used_at IS NULL');
    $stmt->bind_param('ss', $email, $course['title']);
    $stmt->execute();
    $stmt = $db->prepare("INSERT INTO brochure_otp_verifications (email, mobile, course_title, otp_hash, expires_at, resend_available_at, ip_address) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL {$expiry} MINUTE), DATE_ADD(NOW(), INTERVAL {$resend} SECOND), ?)");
    $stmt->bind_param('sssss', $email, $mobile, $course['title'], $otpHash, $ip);
    $stmt->execute();

    api_ok(['message' => 'OTP sent successfully. Please check your email.', 'resendAfter' => $resend, 'expiresIn' => $expiry * 60]);
}

function handle_verify_otp(): void
{
    ensure_brochure_tables();
    $input = api_input();
    $email = normalize_email($input['email'] ?? '');
    $otp = trim((string)($input['otp'] ?? ''));
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if (!preg_match('/^[0-9]{6}$/', $otp)) $errors['otp'] = 'Please enter the 6-digit OTP.';
    if ($errors) api_fail(422, isset($errors['email']) ? 'INVALID_EMAIL' : 'INVALID_OTP', 'Please correct the highlighted fields.', $errors);

    $db = db();
    $stmt = $db->prepare('SELECT * FROM brochure_otp_verifications WHERE email = ? AND used_at IS NULL ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
    if (!$record || strtotime((string)$record['expires_at']) < time()) {
        api_fail(422, 'OTP_EXPIRED', 'OTP expired. Please request a new OTP.', ['otp' => 'OTP expired. Please request a new OTP.']);
    }
    if ((int)$record['attempt_count'] >= 5) {
        api_fail(429, 'OTP_ATTEMPT_LIMIT', 'Maximum OTP attempts reached. Please request a new OTP.', ['otp' => 'Maximum OTP attempts reached.']);
    }
    if (!hash_equals((string)$record['otp_hash'], hash_otp($otp))) {
        $stmt = $db->prepare('UPDATE brochure_otp_verifications SET attempt_count = attempt_count + 1 WHERE id = ?');
        $stmt->bind_param('i', $record['id']);
        $stmt->execute();
        api_fail(422, 'INVALID_OTP', 'Incorrect OTP. Please try again.', ['otp' => 'Incorrect OTP. Please try again.']);
    }

    $token = random_token(32);
    $tokenHash = hash_value($token);
    $stmt = $db->prepare('UPDATE brochure_otp_verifications SET verified_at = NOW(), verification_token_hash = ?, token_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id = ?');
    $stmt->bind_param('si', $tokenHash, $record['id']);
    $stmt->execute();
    api_ok(['message' => 'Email verified successfully.', 'verificationToken' => $token]);
}

function pdf_escape(string $value): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
}

function generated_pdf(string $title): string
{
    $safe = substr(preg_replace('/[^\w\s&+.,-]/u', '', $title) ?: 'Talentteno', 0, 90);
    $content = "BT\n/F1 22 Tf\n72 760 Td\n(" . pdf_escape('Talentteno Institute') . ") Tj\n/F1 16 Tf\n0 -42 Td\n(" . pdf_escape($safe) . ") Tj\n/F1 11 Tf\n0 -42 Td\n(Practical IT training with live projects, internship and placement support.) Tj\nET";
    $objects = [
        "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
        "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
        "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
        "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
        "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream\nendobj\n",
    ];
    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $object) {
        $offsets[] = strlen($pdf);
        $pdf .= $object;
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    return $pdf . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
}

function safe_download_name(array $course): string
{
    $name = strtolower((string)($course['slug'] ?? $course['title'] ?? 'talentteno-brochure'));
    $name = trim(preg_replace('/[^a-z0-9-]+/', '-', $name) ?: 'talentteno-brochure', '-');
    return ($name ?: 'talentteno-brochure') . '-brochure.pdf';
}

function handle_download(): void
{
    ensure_brochure_tables();
    $input = api_input();
    $data = [
        'courseTitle' => normalize_text($input['course'] ?? ($input['courseTitle'] ?? ($input['course_title'] ?? ''))),
        'fullName' => normalize_text($input['name'] ?? ($input['fullName'] ?? '')),
        'email' => normalize_email($input['email'] ?? ''),
        'mobile' => normalize_mobile($input['mobile'] ?? ($input['phone'] ?? '')),
        'degree' => normalize_text($input['degree'] ?? ''),
        'college' => normalize_text($input['college'] ?? ''),
        'address' => normalize_text($input['address'] ?? 'Not provided') ?: 'Not provided',
        'currentStatus' => normalize_text($input['currentStatus'] ?? ($input['study_year'] ?? '')),
        'verificationToken' => normalize_text($input['verificationToken'] ?? ''),
    ];
    $errors = [];
    if (strlen($data['fullName']) < 3) $errors['name'] = 'Please enter a valid full name.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if (!preg_match('/^[6-9][0-9]{9}$/', $data['mobile'])) $errors['mobile'] = 'Please enter a valid 10-digit mobile number.';
    if (strlen($data['degree']) < 2) $errors['degree'] = 'Please enter your degree.';
    if (strlen($data['college']) < 2) $errors['college'] = 'Please enter your college name.';
    if (!in_array($data['currentStatus'], ['1st Year', '2nd Year', '3rd Year', 'Passout'], true)) $errors['study_year'] = 'Please select your current year or status.';
    if (!preg_match('/^[A-Za-z0-9_-]{32,}$/', $data['verificationToken'])) $errors['verificationToken'] = 'Email verification expired. Please verify OTP again.';
    if ($errors) api_fail(422, 'VALIDATION_FAILED', 'Please correct the highlighted fields.', $errors);

    $course = resolve_course($data['courseTitle']);
    if (!$course) api_fail(422, 'INVALID_COURSE', 'Please select a valid course.', ['course' => 'Please select a valid course.']);

    $tokenHash = hash_value($data['verificationToken']);
    $db = db();
    $stmt = $db->prepare('SELECT * FROM brochure_otp_verifications WHERE verification_token_hash = ? AND email = ? AND course_title = ? AND verified_at IS NOT NULL AND used_at IS NULL AND token_expires_at >= NOW() ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('sss', $tokenHash, $data['email'], $course['title']);
    $stmt->execute();
    $otpRecord = $stmt->get_result()->fetch_assoc();
    if (!$otpRecord) {
        api_fail(403, 'VERIFICATION_REQUIRED', 'Email verification expired. Please verify OTP again.', ['verificationToken' => 'Email verification expired. Please verify OTP again.']);
    }

    $downloadTokenHash = hash_value(random_token(32));
    $ip = client_ip();
    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $message = "Degree: {$data['degree']}\nCollege: {$data['college']}\nAddress: {$data['address']}\nYear Status: {$data['currentStatus']}";
    $stmt = $db->prepare('INSERT INTO brochure_download_leads (course_id, course_title, full_name, email, mobile, degree, college, address, current_status, mobile_verified, otp_verification_id, captcha_verified, ip_address, user_agent, download_token_hash, token_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 1, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))');
    $stmt->bind_param('issssssssisss', $course['id'], $course['title'], $data['fullName'], $data['email'], $data['mobile'], $data['degree'], $data['college'], $data['address'], $data['currentStatus'], $otpRecord['id'], $ip, $ua, $downloadTokenHash);
    $stmt->execute();
    $stmt = $db->prepare('UPDATE brochure_otp_verifications SET used_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $otpRecord['id']);
    $stmt->execute();
    $stmt = $db->prepare('INSERT INTO enquiries (name, email, phone, course_id, course_name, message, type, status) VALUES (?, ?, ?, ?, ?, ?, "download", "new")');
    $stmt->bind_param('sssiss', $data['fullName'], $data['email'], $data['mobile'], $course['id'], $course['title'], $message);
    $stmt->execute();

    $brochure = basename((string)($course['brochure_file'] ?? ''));
    $file = $brochure !== '' ? __DIR__ . '/../frontend/uploads/brochures/' . $brochure : '';
    if ($file !== '' && is_file($file)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . safe_download_name($course) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
    $pdf = generated_pdf((string)$course['title']);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . safe_download_name($course) . '"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$path = preg_replace('#^.*?/api/?#', '/', $path) ?: '/';
$path = preg_replace('#^/index\.php#', '', $path) ?: '/';

try {
    if ($method === 'GET' && $path === '/health') {
        api_ok(['server' => 'running', 'port' => 5000, 'emailConfigured' => smtp_missing_fields() === [] || function_exists('mail')]);
    }
    if ($method === 'POST' && $path === '/brochure/send-otp') {
        handle_send_otp();
    }
    if ($method === 'POST' && $path === '/brochure/verify-otp') {
        handle_verify_otp();
    }
    if ($method === 'POST' && $path === '/brochure/download') {
        handle_download();
    }
    api_fail(404, 'NOT_FOUND', 'API endpoint not found.');
} catch (Throwable $e) {
    error_log('API error: ' . $e->getMessage());
    api_fail(500, 'SERVER_ERROR', 'Server error. Please try again later.');
}
