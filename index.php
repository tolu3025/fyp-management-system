<?php
// index.php
// Redesigned: Clean Light Theme landing page for Oduduwa University Computer Science FYP Portal.
// Features clean academic styling, solid buttons, system statistics, and visual illustrations.

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
    // Graceful fallback if database is not active
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
            <a href="login.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-color: var(--border); background: var(--portal-card); color: var(--text-main);"><i class="fa-solid fa-right-to-bracket"></i> Log In</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1.25fr 1fr; gap: 4rem; align-items: center; text-align: left; padding: 0 1.5rem;">
            <!-- Left Pane: Hero Details -->
            <div>
                <span style="font-family: monospace; font-size: 0.9rem; color: var(--primary); text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; display: block; margin-bottom: 0.75rem;">[ COMPUTER SCIENCE DEPARTMENT PORTAL ]</span>
                <h1 class="hero-title">Final Year Project Management System</h1>
                <p class="hero-desc">A centralized tracking portal for final year project submissions, task management, and reviews at Oduduwa University Ipetumodu.</p>
                <div class="hero-actions" style="justify-content: flex-start; gap: 1rem; margin-top: 2rem;">
                    <a href="login.php" class="btn btn-portal-primary" style="padding: 0.85rem 2.25rem; font-size: 0.95rem; border-radius: 9999px;"><i class="fa-solid fa-right-to-bracket"></i> Log In</a>
                    <a href="register.php" class="btn btn-portal-secondary" style="padding: 0.85rem 2.25rem; font-size: 0.95rem; border-radius: 9999px;"><i class="fa-solid fa-user-plus"></i> Register Account</a>
                </div>
            </div>

            <!-- Right Pane: Illustration Frame -->
            <div style="text-align: center;">
                <div class="portal-illustration-frame">
                    <img src="assets/images/illustrations1.png" alt="Student Workspace Illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- Live System Statistics -->
    <section style="background-color: var(--portal-bg); border-bottom: 1px solid var(--border); padding: 4rem 1.5rem;">
        <div style="max-width: 1000px; margin: 0 auto;">
            <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; display: block; text-align: center; margin-bottom: 2.5rem;">CURRENT PORTAL STATISTICS</span>
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <div class="portal-card" style="text-align: center; background: white;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Registered Students</div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);"><?= $student_count ?></div>
                </div>
                <div class="portal-card" style="text-align: center; background: white;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Supervisors (Lecturers)</div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: var(--secondary);"><?= $supervisor_count ?></div>
                </div>
                <div class="portal-card" style="text-align: center; background: white;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Active Topics</div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: #10b981;"><?= $project_count ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portal Modules Gateways -->
    <section style="padding: 6.5rem 1.5rem; background-color: white;">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.25fr; gap: 4.5rem; align-items: center;">
            <!-- Left Pane: Illustration Frame -->
            <div style="text-align: center;">
                <div class="portal-illustration-frame">
                    <img src="assets/images/illustrations2.png" alt="Supervisor Management Illustration">
                </div>
            </div>

            <!-- Right Pane: Modules Grid -->
            <div>
                <span style="font-family: monospace; font-size: 0.85rem; color: var(--primary); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; display: block; margin-bottom: 0.75rem;">PORTAL GATEWAY MODULES</span>
                <h2 style="font-size: 2.25rem; font-weight: 850; color: var(--text-main); margin-bottom: 2rem; letter-spacing: -0.02em;">Department Supervision Roles</h2>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- HOD Module -->
                    <div class="portal-card" style="display: flex; align-items: flex-start; gap: 1.5rem; background: var(--portal-bg);">
                        <div style="font-size: 1.75rem; color: var(--primary); margin-top: 0.15rem;"><i class="fa-solid fa-user-tie"></i></div>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem;">HOD Administrative Module</h4>
                            <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;">Configure submission milestones, allocate supervisor loads, and manage the student registry logs.</p>
                        </div>
                    </div>

                    <!-- Supervisor Module -->
                    <div class="portal-card" style="display: flex; align-items: flex-start; gap: 1.5rem; background: var(--portal-bg);">
                        <div style="font-size: 1.75rem; color: var(--secondary); margin-top: 0.15rem;"><i class="fa-solid fa-chalkboard-user"></i></div>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem;">Lecturer Supervision Module</h4>
                            <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;">Assign tasks, track progress logs, endorse deliverables, and participate in feedback discussion timelines.</p>
                        </div>
                    </div>

                    <!-- Student Module -->
                    <div class="portal-card" style="display: flex; align-items: flex-start; gap: 1.5rem; background: var(--portal-bg);">
                        <div style="font-size: 1.75rem; color: #10b981; margin-top: 0.15rem;"><i class="fa-solid fa-user-graduate"></i></div>
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem;">Student Workspace Module</h4>
                            <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;">Register project topics, upload milestone deliverables, view evaluation status, and check remarks timelines.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Area -->
    <footer style="background-color: var(--portal-bg); color: var(--text-main); padding: 4.5rem 1.5rem; text-align: center; border-top: 1px solid var(--border);">
        <div style="max-width: 800px; margin: 0 auto;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1.25rem; font-weight: 800; color: var(--primary); letter-spacing: -0.01em;">Department of Computer Science</h3>
            <div style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.8; font-weight: 500;">
                Ramon Adedoyin College of Natural and Applied Sciences<br>
                Oduduwa University Ipetumodu, Osun State, Nigeria<br>
                <span style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 1rem; font-family: monospace;">[ Portal Version 2.2 ]</span>
            </div>
        </div>
    </footer>
</body>
</html>
