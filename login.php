<?php
// login.php
// Clean Light Theme sliding-cover login page.
// Features a slide-aside uncover animation upon entering the page.

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

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

// Handle Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($login_id) || empty($password)) {
        $error = "Please enter both Username/Matric Number and Password.";
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

            if ($role === 'HOD') {
                header("Location: hod_dashboard.php");
            } elseif ($role === 'Supervisor') {
                header("Location: supervisor_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Username or Password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('system_title') ?></title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="portal-theme">
    <!-- Public Header -->
    <header class="portal-header">
        <a href="index.php" class="portal-logo" style="text-decoration: none;">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>CS FYP Portal</span>
        </a>
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <!-- Language Selector Dropdown -->
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-globe" style="color: var(--text-muted); font-size: 0.9rem;"></i>
                <form method="GET" action="" style="margin: 0; display: flex; align-items: center;">
                    <select name="lang" onchange="this.form.submit()" class="form-input" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; border-radius: var(--radius-sm); border: 1px solid var(--border); cursor: pointer; width: auto;">
                        <option value="en" <?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'selected' : '' ?>>EN</option>
                        <option value="ms" <?= ($_SESSION['lang'] ?? 'en') === 'ms' ? 'selected' : '' ?>>MS</option>
                    </select>
                </form>
            </div>
            <a href="index.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-color: var(--border); background: var(--portal-card); color: var(--text-main);"><i class="fa-solid fa-house"></i> Home</a>
        </div>
    </header>

    <div class="auth-container" id="authContainer">
        <!-- 1. Left Pane: Login Credentials Form -->
        <div class="auth-form-pane">
            <div class="auth-card">
                <h2>Account Login</h2>
                <p class="subtitle"><?= __('login_subtitle') ?></p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="text-align: left;"><i class="fa-solid fa-triangle-exclamation"></i> <?= sanitize($error) ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST" autocomplete="off">
                    <div class="form-group" style="text-align: left;">
                        <label for="login_id" class="form-label"><?= __('username_or_id') ?></label>
                        <input type="text" name="login_id" id="login_id" class="form-input" placeholder="e.g. CSC/2022/001 or dralabi" required value="<?= isset($_POST['login_id']) ? sanitize($_POST['login_id']) : '' ?>">
                    </div>

                    <div class="form-group" style="text-align: left;">
                        <label for="password" class="form-label"><?= __('password') ?></label>
                        <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-portal-primary btn-block" style="margin-top: 1.5rem; padding: 0.75rem;"><i class="fa-solid fa-right-to-bracket"></i> <?= __('login_button') ?></button>
                </form>

                <div style="margin-top: 2rem; text-align: center; font-size: 0.9rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                    Don't have an account? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 700;">Register Here</a>
                </div>
            </div>
        </div>

        <!-- 2. Right Pane: Background illustration covering form initially -->
        <div class="auth-illustration-pane" id="illustrationPane"></div>
    </div>

    <script>
        // Triggers the cover-slide transition animation to uncover the login form
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const container = document.getElementById('authContainer');
                const illustration = document.getElementById('illustrationPane');
                
                // Add classes to animate
                illustration.classList.add('slide-right');
                container.classList.add('login-loaded');
            }, 100); // 100ms timeout for smooth DOM mount rendering
        });
    </script>
</body>
</html>
