<?php
// index.php
// Main Login Page & Access Control Gateway for all three user roles

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Handle Logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['success_msg'] = "You have logged out successfully.";
    header("Location: index.php");
    exit();
}

// Redirect if already logged in
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'HOD') {
        header("Location: hod_dashboard.php");
    } elseif ($_SESSION['user_role'] === 'Supervisor') {
        header("Location: supervisor_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($login_id) || empty($password)) {
        $error = "Please enter both Username/ID and Password.";
    } else {
        // 1. Check Student Table
        $stmt = $pdo->prepare("SELECT * FROM Student WHERE No_matrik = ?");
        $stmt->execute([$login_id]);
        $user = $stmt->fetch();
        $role = 'Student';

        // 2. Check Supervisor Table
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM Supervisor WHERE No_staf = ?");
            $stmt->execute([$login_id]);
            $user = $stmt->fetch();
            $role = 'Supervisor';
        }

        // 3. Check HOD Table
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM HOD WHERE No_staf = ?");
            $stmt->execute([$login_id]);
            $user = $stmt->fetch();
            $role = 'HOD';
        }

        // Verify and authenticate
        if ($user && password_verify($password, $user['Katalaluan'])) {
            $_SESSION['user_id'] = ($role === 'Student') ? $user['No_matrik'] : $user['No_staf'];
            $_SESSION['user_name'] = $user['Nama'];
            $_SESSION['user_role'] = $role;
            $_SESSION['user_email'] = $user['Email'];

            // Log entry point redirect
            if ($role === 'HOD') {
                header("Location: hod_dashboard.php");
            } elseif ($role === 'Supervisor') {
                header("Location: supervisor_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Login ID or Password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYP Management System — Oduduwa University Ipetumodu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-logo">
            <h1>FYP Management System</h1>
            <p>Oduduwa University Ipetumodu</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success"><?= sanitize($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="login_id" class="form-label">Username / Matric / Staff ID</label>
                <input type="text" name="login_id" id="login_id" class="form-input" placeholder="e.g. CSC/2022/001 or HOD001" required value="<?= isset($_POST['login_id']) ? sanitize($_POST['login_id']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>
        
        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.75rem; color: var(--text-muted);">
            Department of Computer Science <br>
            Ramon Adedoyin College of Natural and Applied Sciences
        </div>
    </div>
</body>
</html>
