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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Public Header -->
    <header class="portal-header">
        <a href="index.php" class="portal-logo" style="text-decoration: none;">
            <span style="font-size: 1.5rem;">🎓</span>
            <span>FYP Portal</span>
        </a>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <!-- Language Selector Dropdown -->
            <form method="GET" action="" style="margin: 0; display: flex; align-items: center;">
                <select name="lang" onchange="this.form.submit()" class="form-input" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; border-radius: var(--radius-sm); border: 1px solid var(--border); cursor: pointer; width: auto;">
                    <option value="en" <?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'selected' : '' ?>>🇬🇧 EN</option>
                    <option value="ms" <?= ($_SESSION['lang'] ?? 'en') === 'ms' ? 'selected' : '' ?>>🇲🇾 MS</option>
                </select>
            </form>
            <a href="login.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><?= __('login_button') ?></a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title"><?= __('landing_hero_title') ?></h1>
        <p class="hero-desc"><?= __('landing_hero_desc') ?></p>
        <div class="hero-actions">
            <a href="login.php" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;"><?= __('login_button') ?></a>
            <a href="register.php" class="btn btn-secondary" style="padding: 0.8rem 2rem; font-size: 1rem;"><?= __('register_button') ?></a>
        </div>
    </section>

    <!-- Live Statistics Counter -->
    <section style="background-color: white; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 3rem 1rem;">
        <div class="stats-grid" style="max-width: 1000px; margin: 0 auto; grid-template-columns: repeat(3, 1fr);">
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);"><?= $student_count ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem; font-weight: 600; margin-top: 0.5rem;"><?= __('total_students') ?></div>
            </div>
            <div style="text-align: center; border-left: 1px solid var(--border); border-right: 1px solid var(--border);">
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--secondary);"><?= $supervisor_count ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem; font-weight: 600; margin-top: 0.5rem;"><?= __('total_supervisors') ?></div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--success);"><?= $project_count ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem; font-weight: 600; margin-top: 0.5rem;"><?= __('assigned_projects') ?></div>
            </div>
        </div>
    </section>

    <!-- Role Explanation Section -->
    <section style="padding: 4rem 1rem; background-color: var(--bg-light);">
        <h2 style="text-align: center; font-size: 2rem; font-weight: 800; color: var(--bg-dark); margin-bottom: 3rem;"><?= __('landing_roles_title') ?></h2>
        
        <div class="portal-grid-3">
            <!-- HOD Card -->
            <div class="portal-card">
                <div class="icon">💼</div>
                <h3><?= __('landing_hod_title') ?></h3>
                <p><?= __('landing_hod_desc') ?></p>
                <a href="login.php" class="btn btn-primary" style="margin-top: auto;"><?= __('login_button') ?></a>
            </div>

            <!-- Supervisor Card -->
            <div class="portal-card">
                <div class="icon">👨‍🏫</div>
                <h3><?= __('landing_supervisor_title') ?></h3>
                <p><?= __('landing_supervisor_desc') ?></p>
                <a href="login.php" class="btn btn-primary" style="margin-top: auto;"><?= __('login_button') ?></a>
            </div>

            <!-- Student Card -->
            <div class="portal-card">
                <div class="icon">👨‍🎓</div>
                <h3><?= __('landing_student_title') ?></h3>
                <p><?= __('landing_student_desc') ?></p>
                <a href="login.php" class="btn btn-primary" style="margin-top: auto;"><?= __('login_button') ?></a>
            </div>
        </div>
    </section>

    <!-- Footer Area -->
    <footer style="background-color: var(--bg-dark); color: white; padding: 4rem 2rem; text-align: center; border-top: 5px solid var(--primary);">
        <div style="max-width: 800px; margin: 0 auto;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;"><?= __('ready_to_start') ?></h3>
            <p style="color: #94a3b8; margin-bottom: 2rem;"><?= __('system_title') ?></p>
            <div style="font-size: 0.85rem; color: #64748b; line-height: 1.6;">
                <?= __('dept_title') ?><br>
                <?= __('college_title') ?><br>
                Oduduwa University Ipetumodu, Osun State, Nigeria
            </div>
        </div>
    </footer>
</body>
</html>
