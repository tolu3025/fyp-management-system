<?php
// index.php
// Redesigned: Clean Landing Page for the Ramon Adedoyin College of Natural and Applied Sciences, Oduduwa University Ipetumodu
// Redirects logged-in users to dashboards, displays live stats, role details, and global language selector.

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

// Fetch live database statistics for the landing page
$student_count = 0;
$supervisor_count = 0;
$project_count = 0;

try {
    $student_count = $pdo->query("SELECT COUNT(*) FROM Student")->fetchColumn();
    $supervisor_count = $pdo->query("SELECT COUNT(*) FROM Supervisor")->fetchColumn();
    $project_count = $pdo->query("SELECT COUNT(*) FROM Project WHERE Tajuk_Projek IS NOT NULL")->fetchColumn();
} catch (PDOException $e) {
    // If database connection fails during initial load, fallback gracefully
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Public Header -->
    <header class="portal-header">
        <a href="index.php" class="portal-logo" style="text-decoration: none;">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>FYP Portal</span>
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
            <a href="login.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><?= __('login_button') ?></a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title"><?= __('landing_hero_title') ?></h1>
        <p class="hero-desc"><?= __('landing_hero_desc') ?></p>
        <div class="hero-actions">
            <a href="login.php" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;"><i class="fa-solid fa-right-to-bracket"></i> <?= __('login_button') ?></a>
            <a href="register.php" class="btn btn-secondary" style="padding: 0.8rem 2rem; font-size: 1rem;"><i class="fa-solid fa-user-plus"></i> <?= __('register_button') ?></a>
        </div>
    </section>

    <!-- Live Statistics Counter -->
    <section style="background-color: white; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 3.5rem 1rem;">
        <div class="stats-grid" style="max-width: 1000px; margin: 0 auto; grid-template-columns: repeat(3, 1fr);">
            <div style="text-align: center;">
                <div style="font-size: 2.75rem; font-weight: 800; color: var(--primary); letter-spacing: -0.03em;"><?= $student_count ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem; font-weight: 700; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;"><?= __('total_students') ?></div>
            </div>
            <div style="text-align: center; border-left: 1px solid var(--border); border-right: 1px solid var(--border);">
                <div style="font-size: 2.75rem; font-weight: 800; color: var(--secondary); letter-spacing: -0.03em;"><?= $supervisor_count ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem; font-weight: 700; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;"><?= __('total_supervisors') ?></div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.75rem; font-weight: 800; color: var(--success); letter-spacing: -0.03em;"><?= $project_count ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem; font-weight: 700; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;"><?= __('assigned_projects') ?></div>
            </div>
        </div>
    </section>

    <!-- Role Explanation Section -->
    <section style="padding: 5rem 1rem; background-color: var(--bg-light);">
        <h2 style="text-align: center; font-size: 2.25rem; font-weight: 850; color: var(--bg-dark); margin-bottom: 3.5rem; letter-spacing: -0.03em;"><?= __('landing_roles_title') ?></h2>
        
        <div class="portal-grid-3" style="padding-top: 0; padding-bottom: 0;">
            <!-- HOD Card -->
            <div class="portal-card">
                <div class="icon-wrapper">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h3><?= __('landing_hod_title') ?></h3>
                <p><?= __('landing_hod_desc') ?></p>
                <a href="login.php" class="btn btn-primary" style="margin-top: auto;"><i class="fa-solid fa-right-to-bracket"></i> <?= __('login_button') ?></a>
            </div>

            <!-- Supervisor Card -->
            <div class="portal-card">
                <div class="icon-wrapper">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <h3><?= __('landing_supervisor_title') ?></h3>
                <p><?= __('landing_supervisor_desc') ?></p>
                <a href="login.php" class="btn btn-primary" style="margin-top: auto;"><i class="fa-solid fa-right-to-bracket"></i> <?= __('login_button') ?></a>
            </div>

            <!-- Student Card -->
            <div class="portal-card">
                <div class="icon-wrapper">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <h3><?= __('landing_student_title') ?></h3>
                <p><?= __('landing_student_desc') ?></p>
                <a href="login.php" class="btn btn-primary" style="margin-top: auto;"><i class="fa-solid fa-right-to-bracket"></i> <?= __('login_button') ?></a>
            </div>
        </div>
    </section>

    <!-- Footer Area -->
    <footer style="background-color: var(--bg-dark); color: white; padding: 4.5rem 2rem; text-align: center; border-top: 5px solid var(--primary);">
        <div style="max-width: 800px; margin: 0 auto;">
            <h3 style="font-size: 1.65rem; margin-bottom: 1rem; font-weight: 800;"><?= __('ready_to_start') ?></h3>
            <p style="color: #94a3b8; margin-bottom: 2.5rem; font-size: 0.95rem;"><?= __('system_title') ?></p>
            <div style="font-size: 0.85rem; color: #64748b; line-height: 1.8; font-weight: 500;">
                <?= __('dept_title') ?><br>
                <?= __('college_title') ?><br>
                Oduduwa University Ipetumodu, Osun State, Nigeria
            </div>
        </div>
    </footer>
</body>
</html>
