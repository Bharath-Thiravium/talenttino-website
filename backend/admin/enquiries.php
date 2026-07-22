<?php
require_once 'auth_check.php';

$conn->query("ALTER TABLE enquiries ADD COLUMN IF NOT EXISTS resume_path VARCHAR(255) NULL AFTER message");

$allowedTypes = ['enquiry', 'download', 'callback'];
$allowedStatuses = ['new', 'contacted', 'enrolled', 'closed'];

function tt_admin_valid_date(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function tt_admin_enquiry_filters(mysqli $conn, array $allowedTypes, array $allowedStatuses): array
{
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $from = trim($_GET['from_date'] ?? '');
    $to = trim($_GET['to_date'] ?? '');

    if (!in_array($type, $allowedTypes, true)) {
        $type = '';
    }
    if (!in_array($status, $allowedStatuses, true)) {
        $status = '';
    }
    if ($from !== '' && !tt_admin_valid_date($from)) {
        $from = '';
    }
    if ($to !== '' && !tt_admin_valid_date($to)) {
        $to = '';
    }

    $where = ['1'];
    $params = [];
    $paramTypes = '';

    if ($type !== '') {
        $where[] = 'e.type = ?';
        $params[] = $type;
        $paramTypes .= 's';
    }
    if ($status !== '') {
        $where[] = 'e.status = ?';
        $params[] = $status;
        $paramTypes .= 's';
    }
    if ($from !== '') {
        $where[] = 'e.created_at >= ?';
        $params[] = $from . ' 00:00:00';
        $paramTypes .= 's';
    }
    if ($to !== '') {
        $where[] = 'e.created_at <= ?';
        $params[] = $to . ' 23:59:59';
        $paramTypes .= 's';
    }

    return [
        'type' => $type,
        'status' => $status,
        'from_date' => $from,
        'to_date' => $to,
        'where' => implode(' AND ', $where),
        'params' => $params,
        'param_types' => $paramTypes,
    ];
}

function tt_admin_prepare(mysqli $conn, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Unable to prepare query.');
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

function tt_admin_enquiry_rows(mysqli $conn, array $filters, ?int $limit = null, int $offset = 0): array
{
    $sql = "SELECT e.*, c.title AS course_title
            FROM enquiries e
            LEFT JOIN courses c ON e.course_id = c.id
            WHERE {$filters['where']}
            ORDER BY e.created_at DESC";
    $types = $filters['param_types'];
    $params = $filters['params'];

    if ($limit !== null) {
        $sql .= ' LIMIT ? OFFSET ?';
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;
    }

    return tt_admin_prepare($conn, $sql, $types, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function tt_admin_filter_query(array $filters, array $extra = []): string
{
    $query = array_filter([
        'type' => $filters['type'],
        'status' => $filters['status'],
        'from_date' => $filters['from_date'],
        'to_date' => $filters['to_date'],
    ], static fn($value) => $value !== '');

    return http_build_query(array_merge($query, $extra));
}

function tt_pdf_escape(string $text): string
{
    $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/', '', $text) ?? '';
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function tt_pdf_text_line(string $text, int $maxChars): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
    if (strlen($text) <= $maxChars) {
        return $text;
    }

    return rtrim(substr($text, 0, max(0, $maxChars - 3))) . '...';
}

function tt_pdf_wrap_lines(string $text, int $maxChars, int $maxLines = 4): array
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
    if ($text === '') {
        return ['-'];
    }

    $lines = explode("\n", wordwrap($text, $maxChars, "\n", true));
    if (count($lines) > $maxLines) {
        $lines = array_slice($lines, 0, $maxLines);
        $lines[$maxLines - 1] = tt_pdf_text_line($lines[$maxLines - 1], max(4, $maxChars - 3)) . '...';
    }

    return $lines;
}

function tt_pdf_draw_text(array $lines, float $x, float $y, int $fontSize = 8, int $leading = 10): string
{
    $content = "BT /F1 {$fontSize} Tf {$x} {$y} Td ";
    foreach ($lines as $index => $line) {
        if ($index > 0) {
            $content .= "0 -" . $leading . " Td ";
        }
        $content .= '(' . tt_pdf_escape($line) . ') Tj ';
    }
    return $content . "ET\n";
}

function tt_pdf_logo_jpeg(string $path): ?array
{
    if (!is_file($path) || !function_exists('imagecreatefrompng')) {
        return null;
    }

    $image = @imagecreatefrompng($path);
    if (!$image) {
        return null;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $canvas = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
    imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);

    ob_start();
    imagejpeg($canvas, null, 88);
    $jpeg = ob_get_clean();

    imagedestroy($image);
    imagedestroy($canvas);

    return $jpeg !== false ? ['data' => $jpeg, 'width' => $width, 'height' => $height] : null;
}

function tt_admin_send_enquiries_pdf(array $rows, array $filters): void
{
    $logo = tt_pdf_logo_jpeg(__DIR__ . '/../../frontend/assets/images/talentteno-board.png');
    $pageWidth = 842;
    $pageHeight = 595;
    $margin = 28;
    $columns = [
        ['label' => '#', 'x' => 32, 'w' => 24, 'chars' => 4],
        ['label' => 'Name', 'x' => 58, 'w' => 70, 'chars' => 16],
        ['label' => 'Phone', 'x' => 132, 'w' => 76, 'chars' => 15],
        ['label' => 'Email', 'x' => 212, 'w' => 118, 'chars' => 24],
        ['label' => 'Course', 'x' => 334, 'w' => 96, 'chars' => 20],
        ['label' => 'Type', 'x' => 434, 'w' => 56, 'chars' => 12],
        ['label' => 'Student Details', 'x' => 494, 'w' => 170, 'chars' => 38],
        ['label' => 'Date', 'x' => 668, 'w' => 82, 'chars' => 18],
        ['label' => 'Status', 'x' => 754, 'w' => 60, 'chars' => 12],
    ];
    $pages = [];
    $content = '';
    $y = 0;
    $rowNumber = 1;

    $newPage = static function () use (&$content, &$y, &$pages, $pageWidth, $pageHeight, $margin, $logo, $columns, $filters, $rows): void {
        if ($content !== '') {
            $pages[] = $content;
        }

        $content = "q 0.96 0.98 1 rg 0 0 {$pageWidth} {$pageHeight} re f Q\n";
        $content .= "q 1 1 1 rg {$margin} 518 786 49 re f Q\n";
        $content .= "q 0.12 0.29 0.78 RG 1.2 w {$margin} 518 786 49 re S Q\n";
        if ($logo) {
            $content .= "q 90 0 0 42 36 524 cm /Im1 Do Q\n";
        } else {
            $content .= "q 0.1 0.43 0.94 rg 36 527 42 32 re f Q\n";
            $content .= tt_pdf_draw_text(['T'], 50, 538, 18, 18);
        }

        $range = ($filters['from_date'] ?: 'All dates') . ' to ' . ($filters['to_date'] ?: 'Today');
        $type = $filters['type'] !== '' ? ucfirst($filters['type']) : 'All Types';
        $status = $filters['status'] !== '' ? ucfirst($filters['status']) : 'All Status';
        $content .= "0.03 0.09 0.18 rg\n";
        $content .= tt_pdf_draw_text(['Talentteno Institute'], 138, 548, 18, 18);
        $content .= "0.31 0.38 0.49 rg\n";
        $content .= tt_pdf_draw_text(['Enquiries & Downloads Report'], 138, 530, 10, 10);
        $content .= tt_pdf_draw_text(['Date: ' . $range, 'Filter: ' . $type . ' / ' . $status, 'Records: ' . count($rows)], 604, 548, 8, 11);

        $content .= "q 0.90 0.94 0.98 rg {$margin} 486 786 24 re f Q\n";
        $content .= "0.29 0.36 0.48 rg\n";
        foreach ($columns as $column) {
            $content .= tt_pdf_draw_text([$column['label']], $column['x'], 500, 7, 9);
        }
        $content .= "q 0.82 0.87 0.93 RG .7 w {$margin} 486 786 24 re S Q\n";
        $y = 470;
    };

    $newPage();

    foreach ($rows as $row) {
        $course = ($row['course_name'] ?: $row['course_title']) ?: '-';
        $date = !empty($row['created_at']) ? date('d M Y h:i A', strtotime($row['created_at'])) : '-';
        $lineSets = [
            [tt_pdf_text_line((string)$rowNumber, 4)],
            tt_pdf_wrap_lines((string)($row['name'] ?? '-'), 16, 2),
            [tt_pdf_text_line((string)($row['phone'] ?? '-'), 15)],
            tt_pdf_wrap_lines((string)($row['email'] ?: '-'), 24, 2),
            tt_pdf_wrap_lines((string)$course, 20, 3),
            [tt_pdf_text_line(ucfirst((string)($row['type'] ?? '-')), 12)],
            tt_pdf_wrap_lines((string)($row['message'] ?: '-'), 38, 5),
            tt_pdf_wrap_lines($date, 18, 2),
            [tt_pdf_text_line(ucfirst((string)($row['status'] ?? '-')), 12)],
        ];
        $maxLines = max(array_map('count', $lineSets));
        $rowHeight = max(34, 12 + ($maxLines * 10));

        if ($y - $rowHeight < 36) {
            $newPage();
        }

        $fill = $rowNumber % 2 === 0 ? '0.98 0.99 1' : '1 1 1';
        $content .= "q {$fill} rg {$margin} " . ($y - $rowHeight + 8) . " 786 {$rowHeight} re f Q\n";
        $content .= "q 0.86 0.89 0.93 RG .5 w {$margin} " . ($y - $rowHeight + 8) . " 786 0 m 814 " . ($y - $rowHeight + 8) . " l S Q\n";
        $content .= "0.05 0.10 0.20 rg\n";
        foreach ($columns as $index => $column) {
            $content .= tt_pdf_draw_text($lineSets[$index], $column['x'], $y, 7, 10);
        }

        $y -= $rowHeight;
        $rowNumber++;
    }

    $pages[] = $content;

    $objects = [
        "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
    ];
    $kids = [];
    $nextObject = 3;
    $fontObject = $nextObject++;
    $imageObject = $logo ? $nextObject++ : null;
    $contentObjects = [];

    foreach ($pages as $pageContent) {
        $pageObject = $nextObject++;
        $contentObject = $nextObject++;
        $kids[] = "{$pageObject} 0 R";
        $contentObjects[] = [$pageObject, $contentObject, $pageContent];
    }

    $objects[] = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($kids) . " >>\nendobj\n";
    $objects[] = "{$fontObject} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    if ($logo && $imageObject) {
        $objects[] = "{$imageObject} 0 obj\n<< /Type /XObject /Subtype /Image /Width {$logo['width']} /Height {$logo['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($logo['data']) . " >>\nstream\n{$logo['data']}\nendstream\nendobj\n";
    }

    foreach ($contentObjects as [$pageObject, $contentObject, $pageContent]) {
        $xObject = $logo && $imageObject ? " /XObject << /Im1 {$imageObject} 0 R >>" : '';
        $objects[] = "{$pageObject} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] /Resources << /Font << /F1 {$fontObject} 0 R >>{$xObject} >> /Contents {$contentObject} 0 R >>\nendobj\n";
        $objects[] = "{$contentObject} 0 obj\n<< /Length " . strlen($pageContent) . " >>\nstream\n{$pageContent}\nendstream\nendobj\n";
    }

    usort($objects, static function (string $a, string $b): int {
        preg_match('/^(\d+) 0 obj/', $a, $am);
        preg_match('/^(\d+) 0 obj/', $b, $bm);
        return ((int)($am[1] ?? 0)) <=> ((int)($bm[1] ?? 0));
    });

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $object) {
        preg_match('/^(\d+) 0 obj/', $object, $match);
        $offsets[(int)$match[1]] = strlen($pdf);
        $pdf .= $object;
    }

    $objectCount = max(array_keys($offsets));
    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . ($objectCount + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= $objectCount; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
    }
    $pdf .= "trailer\n<< /Size " . ($objectCount + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="talentteno-enquiries-' . date('Ymd-His') . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    header('X-Content-Type-Options: nosniff');
    echo $pdf;
    exit;
}

$filters = tt_admin_enquiry_filters($conn, $allowedTypes, $allowedStatuses);
$returnQuery = tt_admin_filter_query($filters, ['page' => max(1, (int)($_GET['page'] ?? 1))]);

if (isset($_GET['update_status']) && (isset($_GET['update_to']) || isset($_GET['status']))) {
    $id = (int)$_GET['update_status'];
    $newStatus = $_GET['update_to'] ?? $_GET['status'];
    if ($id > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $stmt = $conn->prepare('UPDATE enquiries SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $newStatus, $id);
        $stmt->execute();
    }
    header('Location: enquiries.php' . ($returnQuery ? '?' . $returnQuery : ''));
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $conn->prepare('DELETE FROM enquiries WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    header('Location: enquiries.php' . ($returnQuery ? '?' . $returnQuery : ''));
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $rows = tt_admin_enquiry_rows($conn, $filters);
    tt_admin_send_enquiries_pdf($rows, $filters);
}

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countRow = tt_admin_prepare($conn, "SELECT COUNT(*) AS total FROM enquiries e WHERE {$filters['where']}", $filters['param_types'], $filters['params'])->get_result()->fetch_assoc();
$totalRecords = (int)($countRow['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalRecords / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;
$enquiries = tt_admin_enquiry_rows($conn, $filters, $perPage, $offset);
$shownFrom = $totalRecords > 0 ? $offset + 1 : 0;
$shownTo = min($offset + count($enquiries), $totalRecords);
$exportQuery = tt_admin_filter_query($filters, ['export' => 'pdf']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiries — Talentteno Admin</title>
    <link rel="icon" type="image/png" href="../../frontend/assets/images/logot-transparent.png">
    <link rel="apple-touch-icon" href="../../frontend/assets/images/logot-transparent.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css?v=20260722-adminmobile3">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-inbox"></i> Enquiries & Downloads</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <div class="admin-card enquiry-filter-card">
            <form method="GET" class="enquiry-filter-form">
                <div class="filter-field">
                    <label>Type</label>
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="enquiry" <?= $filters['type'] === 'enquiry' ? 'selected' : '' ?>>Enquiry</option>
                        <option value="download" <?= $filters['type'] === 'download' ? 'selected' : '' ?>>Download</option>
                        <option value="callback" <?= $filters['type'] === 'callback' ? 'selected' : '' ?>>Callback</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <?php foreach ($allowedStatuses as $status): ?>
                        <option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field">
                    <label>From Date</label>
                    <input type="date" name="from_date" value="<?= htmlspecialchars($filters['from_date']) ?>">
                </div>
                <div class="filter-field">
                    <label>To Date</label>
                    <input type="date" name="to_date" value="<?= htmlspecialchars($filters['to_date']) ?>">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-save"><i class="fas fa-filter"></i> Filter</button>
                    <a href="enquiries.php" class="btn-light">Reset</a>
                    <a href="enquiries.php?<?= htmlspecialchars($exportQuery) ?>" class="btn-export"><i class="fas fa-file-pdf"></i> Download PDF</a>
                </div>
                <div class="record-count">
                    <strong><?= $totalRecords ?></strong> records found
                    <span>Showing <?= $shownFrom ?>-<?= $shownTo ?>, 10 per page</span>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <div class="table-wrap enquiry-table-wrap">
                <table class="admin-table enquiry-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Type</th>
                            <th>Student Details</th>
                            <th>Resume</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enquiries as $i => $e): ?>
                        <?php
                            $phoneDigits = preg_replace('/[^0-9]/', '', $e['phone'] ?? '');
                            $courseName = ($e['course_name'] ?: $e['course_title']) ?: '—';
                            $resumePath = trim((string)($e['resume_path'] ?? ''));
                            $resumeHref = '';
                            if ($resumePath !== '' && preg_match('#^uploads/resumes/[a-z0-9._/-]+$#i', $resumePath)) {
                                $resumeHref = '../../frontend/' . $resumePath;
                            }
                        ?>
                        <tr>
                            <td><?= $offset + $i + 1 ?></td>
                            <td class="cell-name"><strong><?= htmlspecialchars($e['name']) ?></strong></td>
                            <td><a href="tel:<?= htmlspecialchars($phoneDigits) ?>"><?= htmlspecialchars($e['phone']) ?></a></td>
                            <td class="cell-email"><?= htmlspecialchars($e['email'] ?: '—') ?></td>
                            <td class="cell-course"><?= htmlspecialchars($courseName) ?></td>
                            <td><span class="badge badge-<?= $e['type'] === 'download' ? 'green' : ($e['type'] === 'callback' ? 'orange' : 'blue') ?>"><?= ucfirst($e['type']) ?></span></td>
                            <td class="cell-details"><?= nl2br(htmlspecialchars($e['message'] ?: '—')) ?></td>
                            <td class="cell-resume">
                                <?php if ($resumeHref !== ''): ?>
                                <a class="btn-xs btn-blue" href="<?= htmlspecialchars($resumeHref) ?>" target="_blank" rel="noopener"><i class="fas fa-file-arrow-down"></i> View</a>
                                <?php else: ?>
                                —
                                <?php endif; ?>
                            </td>
                            <td class="cell-date"><?= date('d M Y', strtotime($e['created_at'])) ?><span><?= date('h:i A', strtotime($e['created_at'])) ?></span></td>
                            <td>
                                <select class="status-select" onchange="updateStatus(<?= (int)$e['id'] ?>, this.value)">
                                    <?php foreach ($allowedStatuses as $s): ?>
                                    <option value="<?= $s ?>" <?= $e['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="cell-actions">
                                <a href="https://wa.me/91<?= htmlspecialchars($phoneDigits) ?>?text=Hi+<?= urlencode($e['name']) ?>%2C+Thank+you+for+your+interest+in+Talentteno+Institute." target="_blank" class="btn-xs btn-green" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                <a href="mailto:<?= htmlspecialchars($e['email']) ?>" class="btn-xs btn-blue" title="Email"><i class="fas fa-envelope"></i></a>
                                <a href="enquiries.php?<?= htmlspecialchars(tt_admin_filter_query($filters, ['page' => $page, 'delete' => (int)$e['id']])) ?>" class="btn-xs btn-red" title="Delete" onclick="return confirm('Delete this enquiry?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($enquiries)): ?>
                        <tr><td colspan="11" style="text-align:center;padding:40px;color:#64748B;">No enquiries found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <a class="<?= $page <= 1 ? 'disabled' : '' ?>" href="enquiries.php?<?= htmlspecialchars(tt_admin_filter_query($filters, ['page' => max(1, $page - 1)])) ?>"><i class="fas fa-chevron-left"></i> Prev</a>
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <a class="<?= $page >= $totalPages ? 'disabled' : '' ?>" href="enquiries.php?<?= htmlspecialchars(tt_admin_filter_query($filters, ['page' => min($totalPages, $page + 1)])) ?>">Next <i class="fas fa-chevron-right"></i></a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
const currentQuery = <?= json_encode(tt_admin_filter_query($filters, ['page' => $page])) ?>;
function updateStatus(id, status) {
    const params = new URLSearchParams(currentQuery);
    params.set('update_status', id);
    params.set('update_to', status);
    window.location.href = 'enquiries.php?' + params.toString();
}
</script>
</body>
</html>
