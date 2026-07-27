<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

define('DB_OPTIONAL', false);
require_once '../includes/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result && password_verify($password, $result['password'])) {
            $_SESSION['admin_id'] = $result['id'];
            $_SESSION['admin_name'] = $result['full_name'];
            $_SESSION['admin_role'] = $result['role'];
            $conn->query("UPDATE admin_users SET last_login = NOW() WHERE id = " . $result['id']);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Talentteno Institute</title>
    <link rel="icon" type="image/png" href="../../frontend/assets/images/logot-transparent.png?v=20260722-logo2">
    <link rel="apple-touch-icon" href="../../frontend/assets/images/logot-transparent.png?v=20260722-logo2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0F172A, #1E3A8A); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: white; border-radius: 20px; padding: 48px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
        .login-logo { text-align: center; margin-bottom: 32px; }
        .login-logo img { width: 70px; height: 70px; object-fit: contain; border-radius: 14px; border: 1px solid #DBEAFE; box-shadow: 0 12px 26px rgba(29,78,216,0.12); }
        .login-logo h1 { font-size: 22px; font-weight: 700; color: #1E293B; margin-top: 10px; }
        .login-logo p { color: #64748B; font-size: 13px; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 15px; }
        .form-group input { width: 100%; padding: 12px 14px 12px 42px; border: 1.5px solid #E2E8F0; border-radius: 8px; font-size: 14px; background: #F8FAFC; transition: all 0.2s; }
        .form-group input:focus { outline: none; border-color: #1D4ED8; background: white; box-shadow: 0 0 0 3px rgba(29,78,216,0.08); }
        .btn-login { width: 100%; background: linear-gradient(135deg, #1D4ED8, #3B82F6); color: white; border: none; padding: 14px; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 8px; transition: all 0.2s; }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(29,78,216,0.3); }
        .error { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 16px; display: flex; gap: 8px; align-items: center; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #64748B; font-size: 13px; text-decoration: none; }
        .back-link a:hover { color: #1D4ED8; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <img src="../../frontend/assets/images/logot-transparent.png?v=20260722-logo2" alt="Talentteno Institute logo">
        <h1>Talentteno Admin</h1>
        <p>Institute Management Panel</p>
    </div>
    <?php if ($error): ?>
    <div class="error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <div class="input-wrap">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Enter username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login to Admin Panel</button>
    </form>
    <div class="back-link"><a href="../../frontend/index.php"><i class="fas fa-arrow-left"></i> Back to Website</a></div>
</div>
</body>
</html>
