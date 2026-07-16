<?php
require_once 'auth_check.php';

$success = '';
$error = '';

// Keep older installations compatible with the editable footer fields.
$conn->query("ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS footer_description VARCHAR(500) DEFAULT 'Practical IT training in Madurai with free internship, spoken English support, live projects, certification and placement assistance.'");
$conn->query("ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS footer_copyright VARCHAR(255) DEFAULT '© 2026 Talentteno Institute | All Rights Reserved'");
$conn->query("ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS seo_title VARCHAR(255) DEFAULT 'Talentteno Institute | Best IT Training Institute in Madurai'");
$conn->query("ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS seo_description VARCHAR(500) DEFAULT 'Talentteno Institute offers practical IT training in Madurai for Full Stack Development, Data Science, AI, Cyber Security, Digital Marketing, UI/UX, Tally and programming with live projects, free internship and placement assistance.'");
$conn->query("ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS seo_keywords VARCHAR(700) DEFAULT 'IT training institute in Madurai, best software training institute Madurai, full stack course Madurai, data science course Madurai, cyber security course Madurai, digital marketing course Madurai, UI UX course Madurai, Tally course Madurai'");
$conn->query("ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS business_hours VARCHAR(120) DEFAULT 'Monday to Saturday, 9:00 AM to 7:00 PM'");
$conn->query("ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS map_embed_url TEXT NULL");

$seoDefaults = [
    'seo_title' => 'Talentteno Institute | Best IT Training Institute in Madurai',
    'seo_description' => 'Talentteno Institute offers practical IT training in Madurai for Full Stack Development, Data Science, AI, Cyber Security, Digital Marketing, UI/UX, Tally and programming with live projects, free internship and placement assistance.',
    'seo_keywords' => 'IT training institute in Madurai, best software training institute Madurai, software training centre Madurai, full stack development course Madurai, data science course Madurai, data analyst course Madurai, artificial intelligence course Madurai, cyber security course Madurai, digital marketing course Madurai, UI UX design course Madurai, Python course Madurai, Java course Madurai, Tally GST course Madurai, web development course Madurai, internship training Madurai, placement training Madurai',
    'business_hours' => 'Monday to Saturday, 9:00 AM to 7:00 PM',
];

// Ensure a settings row always exists
$check = $conn->query("SELECT id FROM site_settings WHERE id = 1");
if ($check->num_rows === 0) {
    $conn->query("INSERT INTO site_settings (id) VALUES (1)");
}

foreach ($seoDefaults as $field => $value) {
    $safeValue = $conn->real_escape_string($value);
    $conn->query("UPDATE site_settings SET $field = '$safeValue' WHERE id = 1 AND ($field IS NULL OR TRIM($field) = '')");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'site_name', 'tagline', 'about_title', 'about_content', 'mission', 'vision',
        'founded_year', 'total_students', 'total_trainers', 'success_rate', 'avg_rating',
        'address', 'phone1', 'phone2', 'email',
        'facebook_url', 'instagram_url', 'linkedin_url', 'youtube_url', 'map_embed_url',
        'seo_title', 'seo_description', 'seo_keywords', 'business_hours',
        'footer_description', 'footer_copyright'
    ];
    $set_parts = [];
    foreach ($fields as $f) {
        $val = $conn->real_escape_string(trim($_POST[$f] ?? ''));
        $set_parts[] = "$f='$val'";
    }
    $sql = "UPDATE site_settings SET " . implode(', ', $set_parts) . " WHERE id = 1";
    if ($conn->query($sql)) {
        $success = 'Site content updated successfully! Changes are now live on the website.';
    } else {
        $error = 'Database error: ' . $conn->error;
    }
}

$settings = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About / Site Content — Talentteno Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-info-circle"></i> About / Site Content</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <p style="margin:-8px 0 18px;color:#64748B;font-size:13.5px;">
            Everything on this page controls the <strong>About Us section, stats banner, and contact/footer details</strong>
            on the live website. Save to publish changes instantly — no code changes needed.
        </p>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <form method="POST">
            <div class="admin-card" style="margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;"><i class="fas fa-building" style="color:var(--blue)"></i> Institute Identity</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Site / Institute Name</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Tagline</label>
                        <input type="text" name="tagline" value="<?= htmlspecialchars($settings['tagline']) ?>">
                    </div>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;"><i class="fas fa-info-circle" style="color:var(--blue)"></i> About Us Section</h3>
                <div class="form-group">
                    <label>About Section Heading</label>
                    <input type="text" name="about_title" value="<?= htmlspecialchars($settings['about_title']) ?>">
                </div>
                <div class="form-group">
                    <label>About Us Content (main paragraph shown on homepage)</label>
                    <textarea name="about_content" rows="6"><?= htmlspecialchars($settings['about_content']) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Our Mission</label>
                        <textarea name="mission" rows="4"><?= htmlspecialchars($settings['mission']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Our Vision</label>
                        <textarea name="vision" rows="4"><?= htmlspecialchars($settings['vision']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;"><i class="fas fa-chart-bar" style="color:var(--blue)"></i> Stats Banner</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Founded Year</label>
                        <input type="text" name="founded_year" value="<?= htmlspecialchars($settings['founded_year']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Total Students</label>
                        <input type="text" name="total_students" value="<?= htmlspecialchars($settings['total_students']) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Total Trainers</label>
                        <input type="text" name="total_trainers" value="<?= htmlspecialchars($settings['total_trainers']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Success / Placement Rate</label>
                        <input type="text" name="success_rate" value="<?= htmlspecialchars($settings['success_rate']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Average Rating</label>
                    <input type="text" name="avg_rating" value="<?= htmlspecialchars($settings['avg_rating']) ?>" style="max-width:200px;">
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;"><i class="fas fa-address-book" style="color:var(--blue)"></i> Contact Details</h3>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2"><?= htmlspecialchars($settings['address']) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone 1</label>
                        <input type="text" name="phone1" value="<?= htmlspecialchars($settings['phone1']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone 2</label>
                        <input type="text" name="phone2" value="<?= htmlspecialchars($settings['phone2']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($settings['email']) ?>">
                </div>
                <div class="form-group">
                    <label>Google Maps Embed URL (optional)</label>
                    <input type="text" name="map_embed_url" value="<?= htmlspecialchars($settings['map_embed_url']) ?>" placeholder="https://www.google.com/maps/embed?...">
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;"><i class="fas fa-share-alt" style="color:var(--blue)"></i> Social Links</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fab fa-facebook-f"></i> Facebook URL</label>
                        <input type="text" name="facebook_url" value="<?= htmlspecialchars($settings['facebook_url']) ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-instagram"></i> Instagram URL</label>
                        <input type="text" name="instagram_url" value="<?= htmlspecialchars($settings['instagram_url']) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fab fa-linkedin-in"></i> LinkedIn URL</label>
                        <input type="text" name="linkedin_url" value="<?= htmlspecialchars($settings['linkedin_url']) ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-youtube"></i> YouTube URL</label>
                        <input type="text" name="youtube_url" value="<?= htmlspecialchars($settings['youtube_url']) ?>">
                    </div>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;"><i class="fas fa-magnifying-glass-chart" style="color:var(--blue)"></i> SEO & Business Details</h3>
                <div class="form-group">
                    <label>SEO Title</label>
                    <input type="text" name="seo_title" value="<?= htmlspecialchars($settings['seo_title'] ?? '') ?>" maxlength="255">
                </div>
                <div class="form-group">
                    <label>SEO Description</label>
                    <textarea name="seo_description" rows="3" maxlength="500"><?= htmlspecialchars($settings['seo_description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>SEO Keywords</label>
                    <textarea name="seo_keywords" rows="3" maxlength="700"><?= htmlspecialchars($settings['seo_keywords'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Business Hours</label>
                    <input type="text" name="business_hours" value="<?= htmlspecialchars($settings['business_hours'] ?? '') ?>" placeholder="Monday to Saturday, 9:00 AM to 7:00 PM">
                </div>
                <p style="margin:0;color:#64748B;font-size:12px;">These details feed the frontend meta tags, structured data and Node SEO API.</p>
            </div>

            <div class="admin-card" style="margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;"><i class="fas fa-shoe-prints" style="color:var(--blue)"></i> Footer Content</h3>
                <div class="form-group">
                    <label>Footer Description</label>
                    <textarea name="footer_description" rows="3"><?= htmlspecialchars($settings['footer_description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Copyright Text</label>
                    <input type="text" name="footer_copyright" value="<?= htmlspecialchars($settings['footer_copyright'] ?? '') ?>">
                </div>
                <p style="margin:0;color:#64748B;font-size:12px;">Footer contact details and social links are taken from the sections above.</p>
            </div>

            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save All Changes</button>
        </form>
    </div>
</div>
</body>
</html>
