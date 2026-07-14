<?php
require_once 'auth_check.php';

// Stats
$total_enquiries = $conn->query("SELECT COUNT(*) FROM enquiries")->fetch_row()[0];
$new_enquiries = $conn->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'")->fetch_row()[0];
$total_downloads = $conn->query("SELECT COUNT(*) FROM enquiries WHERE type = 'download'")->fetch_row()[0];
$total_courses = $conn->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetch_row()[0];
$total_services = $conn->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetch_row()[0];
$total_steps = $conn->query("SELECT COUNT(*) FROM process_steps WHERE is_active = 1")->fetch_row()[0];

// Recent enquiries
$recent = $conn->query("SELECT e.*, c.title AS course_title FROM enquiries e LEFT JOIN courses c ON e.course_id = c.id ORDER BY e.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

// Top downloaded courses
$top_downloads = $conn->query("SELECT title, download_count FROM courses WHERE is_active = 1 ORDER BY download_count DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Talentteno Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-th-large"></i> Dashboard</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card" style="border-left: 4px solid #3B82F6;">
                <div class="stat-icon" style="background:#EEF2FF;color:#3B82F6"><i class="fas fa-inbox"></i></div>
                <div>
                    <div class="stat-num"><?= $total_enquiries ?></div>
                    <div class="stat-lbl">Total Enquiries</div>
                </div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #F59E0B;">
                <div class="stat-icon" style="background:#FFFBEB;color:#D97706"><i class="fas fa-bell"></i></div>
                <div>
                    <div class="stat-num"><?= $new_enquiries ?></div>
                    <div class="stat-lbl">New (Unread)</div>
                </div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #16A34A;">
                <div class="stat-icon" style="background:#F0FDF4;color:#16A34A"><i class="fas fa-download"></i></div>
                <div>
                    <div class="stat-num"><?= $total_downloads ?></div>
                    <div class="stat-lbl">Brochure Downloads</div>
                </div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #8B5CF6;">
                <div class="stat-icon" style="background:#F5F3FF;color:#8B5CF6"><i class="fas fa-book"></i></div>
                <div>
                    <div class="stat-num"><?= $total_courses ?></div>
                    <div class="stat-lbl">Active Courses</div>
                </div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #0EA5E9;">
                <div class="stat-icon" style="background:#F0F9FF;color:#0EA5E9"><i class="fas fa-concierge-bell"></i></div>
                <div>
                    <div class="stat-num"><?= $total_services ?></div>
                    <div class="stat-lbl">Active Services</div>
                </div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #E11D48;">
                <div class="stat-icon" style="background:#FFF1F2;color:#E11D48"><i class="fas fa-route"></i></div>
                <div>
                    <div class="stat-num"><?= $total_steps ?></div>
                    <div class="stat-lbl">Process Steps</div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Recent Enquiries -->
            <div class="admin-card" style="grid-column: span 2;">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Recent Enquiries</h3>
                    <a href="enquiries.php" class="btn-sm">View All</a>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Name</th><th>Phone</th><th>Course</th><th>Type</th><th>Date</th><th>Status</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $i => $r): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                                <td><a href="tel:<?= $r['phone'] ?>"><?= htmlspecialchars($r['phone']) ?></a></td>
                                <td><?= htmlspecialchars($r['course_name'] ?: '—') ?></td>
                                <td>
                                    <span class="badge badge-<?= $r['type'] === 'download' ? 'green' : ($r['type'] === 'callback' ? 'orange' : 'blue') ?>">
                                        <?= ucfirst($r['type']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M, H:i', strtotime($r['created_at'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= $r['status'] === 'new' ? 'orange' : ($r['status'] === 'enrolled' ? 'green' : 'gray') ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="enquiries.php?update_status=<?= $r['id'] ?>&status=contacted" class="btn-xs btn-blue">Mark Contacted</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Downloads -->
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-fire"></i> Top Downloaded Courses</h3>
                </div>
                <?php foreach ($top_downloads as $td): ?>
                <div class="download-row">
                    <span><?= htmlspecialchars($td['title']) ?></span>
                    <strong><?= $td['download_count'] ?> downloads</strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
