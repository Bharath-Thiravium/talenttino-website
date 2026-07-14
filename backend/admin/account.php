<?php
require_once 'auth_check.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $stmt = $conn->prepare('SELECT * FROM admin_users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['admin_id']);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if (!$admin) {
        $error = 'Admin account not found.';
    } elseif (!password_verify($currentPassword, $admin['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirm password do not match.';
    } else {
        if ($newPassword !== '') {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE admin_users SET full_name = ?, email = ?, password = ? WHERE id = ?');
            $stmt->bind_param('sssi', $fullName, $email, $hash, $_SESSION['admin_id']);
        } else {
            $stmt = $conn->prepare('UPDATE admin_users SET full_name = ?, email = ? WHERE id = ?');
            $stmt->bind_param('ssi', $fullName, $email, $_SESSION['admin_id']);
        }
        $stmt->execute();
        $_SESSION['admin_name'] = $fullName !== '' ? $fullName : ($_SESSION['admin_name'] ?? 'Admin');
        $success = 'Account details updated successfully.';
    }
}

$stmt = $conn->prepare('SELECT username, full_name, email, role, last_login FROM admin_users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['admin_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account — Talentteno Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-title"><i class="fas fa-user-shield"></i> Admin Account</h1>
        <div class="topbar-right">
            <span class="admin-name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="form-section">
                <h3>Profile Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?= htmlspecialchars($admin['username'] ?? '') ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" value="<?= htmlspecialchars(ucfirst($admin['role'] ?? 'admin')) ?>" disabled>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                    <small class="field-help">Required to save profile details or change password.</small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" minlength="8">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" minlength="8">
                    </div>
                </div>
                <small class="field-help">Leave new password fields empty if you only want to update profile details.</small>
            </div>

            <button class="btn-save" type="submit"><i class="fas fa-save"></i> Save Account</button>
        </form>
    </div>
</div>
</body>
</html>
