<?php
// index.php
// Redesigned: Cyberpunk High-Tech landing page for Oduduwa University Computer Science FYP Portal.
// Features glowing neon styling, glassmorphism, stats system diagnostics, and illustration nodes.

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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="cyber-theme">
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
            <a href="login.php?mode=login" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-color: rgba(255,255,255,0.15); background: transparent; color: #ffffff;"><i class="fa-solid fa-right-to-bracket"></i> <?= __('login_button') ?></a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1.2fr 1fr; gap: 4rem; align-items: center; text-align: left; padding: 0 1rem;">
            <!-- Left Pane: Hero Details -->
            <div>
                <span style="font-family: monospace; font-size: 0.9rem; color: var(--cyber-glow-pink); text-transform: uppercase; font-weight: 800; letter-spacing: 2px; display: block; margin-bottom: 0.75rem;">[ SYSTEM ACCESS INITIALIZATION ]</span>
                <h1 class="hero-title"><?= __('landing_hero_title') ?></h1>
                <p class="hero-desc"><?= __('landing_hero_desc') ?></p>
                <div class="hero-actions" style="justify-content: flex-start;">
                    <a href="login.php?mode=login" class="btn btn-cyber-cyan" style="padding: 0.85rem 2rem; font-size: 0.95rem;"><i class="fa-solid fa-right-to-bracket"></i> Connect Terminal</a>
                    <a href="login.php?mode=register" class="btn btn-cyber-primary" style="padding: 0.85rem 2rem; font-size: 0.95rem;"><i class="fa-solid fa-network-wired"></i> Initialize Node</a>
                </div>
            </div>

            <!-- Right Pane: Floating holographic illustration -->
            <div style="text-align: center;">
                <div class="cyber-illustration-container">
                    <img src="assets/images/illustrations1.png" alt="Holographic Interface" style="max-width: 100%; height: auto; display: block;">
                </div>
            </div>
        </div>
    </section>

    <!-- Live System Diagnostics Stats -->
    <section style="background-color: var(--cyber-gray); border-top: 1px solid var(--cyber-border); border-bottom: 1px solid var(--cyber-border); padding: 4rem 1rem;">
        <div style="max-width: 1000px; margin: 0 auto;">
            <span style="font-family: monospace; font-size: 0.8rem; color: var(--cyber-glow-cyan); text-transform: uppercase; font-weight: 800; letter-spacing: 1.5px; display: block; text-align: center; margin-bottom: 2.5rem;">[ DIAGNOSTICS: ACTIVE SYSTEM METRICS ]</span>
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="cyber-card" style="text-align: center;">
                    <div style="font-size: 0.75rem; font-family: monospace; color: var(--text-muted); font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">STUDENT NODES</div>
                    <div style="font-size: 2.75rem; font-weight: 850; color: var(--cyber-glow-pink); letter-spacing: -0.03em;"><?= $student_count ?></div>
                    <span style="display: block; font-size: 0.65rem; color: var(--neon-green); font-family: monospace; font-weight: 700; margin-top: 0.5rem;">[ STATUS: ONLINE ]</span>
                </div>
                <div class="cyber-card" style="text-align: center;">
                    <div style="font-size: 0.75rem; font-family: monospace; color: var(--text-muted); font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">LECTURER NODES</div>
                    <div style="font-size: 2.75rem; font-weight: 850; color: var(--cyber-glow-cyan); letter-spacing: -0.03em;"><?= $supervisor_count ?></div>
                    <span style="display: block; font-size: 0.65rem; color: var(--neon-green); font-family: monospace; font-weight: 700; margin-top: 0.5rem;">[ STATUS: ONLINE ]</span>
                </div>
                <div class="cyber-card" style="text-align: center;">
                    <div style="font-size: 0.75rem; font-family: monospace; color: var(--text-muted); font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">ACTIVE PROJECTS</div>
                    <div style="font-size: 2.75rem; font-weight: 850; color: #eab308; letter-spacing: -0.03em;"><?= $project_count ?></div>
                    <span style="display: block; font-size: 0.65rem; color: var(--neon-green); font-family: monospace; font-weight: 700; margin-top: 0.5rem;">[ CORE INTEGRITY: STABLE ]</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Node Gateways Section (replacing plain role cards) -->
    <section style="padding: 6rem 1rem; background-color: var(--cyber-dark);">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.2fr; gap: 4rem; align-items: center; padding: 0 1rem;">
            <!-- Left Pane: Illustration -->
            <div style="text-align: center;">
                <div class="cyber-illustration-container" style="box-shadow: 0 20px 40px rgba(0,0,0,0.5), 0 0 30px rgba(6, 182, 212, 0.15);">
                    <img src="assets/images/illustrations2.png" alt="Console Interface Control" style="max-width: 100%; height: auto; display: block;">
                </div>
            </div>

            <!-- Right Pane: High-Tech Node Terminals -->
            <div>
                <span style="font-family: monospace; font-size: 0.8rem; color: var(--cyber-glow-pink); text-transform: uppercase; font-weight: 800; letter-spacing: 2px; display: block; margin-bottom: 0.75rem;">[ GATEWAY CONTROL ACCESS NODES ]</span>
                <h2 style="font-size: 2.25rem; font-weight: 850; color: #ffffff; margin-bottom: 2rem; letter-spacing: -0.02em;">Tailored Portal Modules</h2>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- HOD Terminal -->
                    <div class="cyber-card" style="display: flex; align-items: flex-start; gap: 1.5rem; padding: 1.75rem 2rem;">
                        <div style="font-size: 1.5rem; color: var(--cyber-glow-cyan); margin-top: 0.25rem;"><i class="fa-solid fa-user-tie"></i></div>
                        <div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: white; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                HOD Administrative Terminal
                                <span style="font-size: 0.65rem; font-family: monospace; padding: 0.15rem 0.4rem; background: rgba(5, 150, 105, 0.15); border: 1px solid rgba(5, 150, 105, 0.3); border-radius: 4px; color: var(--neon-green); font-weight: 700;">[READY]</span>
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Allocate supervisors, configure departmental milestones, and audit system compliance reports.</p>
                        </div>
                    </div>

                    <!-- Supervisor Terminal -->
                    <div class="cyber-card" style="display: flex; align-items: flex-start; gap: 1.5rem; padding: 1.75rem 2rem;">
                        <div style="font-size: 1.5rem; color: var(--cyber-glow-pink); margin-top: 0.25rem;"><i class="fa-solid fa-chalkboard-user"></i></div>
                        <div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: white; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                Lecturer Supervision Node
                                <span style="font-size: 0.65rem; font-family: monospace; padding: 0.15rem 0.4rem; background: rgba(5, 150, 105, 0.15); border: 1px solid rgba(5, 150, 105, 0.3); border-radius: 4px; color: var(--neon-green); font-weight: 700;">[READY]</span>
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Assign tasks, track students progress, write feedback remarks on the comments timeline, and endorse milestone submissions.</p>
                        </div>
                    </div>

                    <!-- Student Terminal -->
                    <div class="cyber-card" style="display: flex; align-items: flex-start; gap: 1.5rem; padding: 1.75rem 2rem;">
                        <div style="font-size: 1.5rem; color: #eab308; margin-top: 0.25rem;"><i class="fa-solid fa-user-graduate"></i></div>
                        <div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: white; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                Student Workspace Node
                                <span style="font-size: 0.65rem; font-family: monospace; padding: 0.15rem 0.4rem; background: rgba(5, 150, 105, 0.15); border: 1px solid rgba(5, 150, 105, 0.3); border-radius: 4px; color: var(--neon-green); font-weight: 700;">[READY]</span>
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Register agreed project titles, manage milestone uploads, submit weekly progress logs, and review supervisor remarks.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Area -->
    <footer style="background-color: #020617; color: white; padding: 4.5rem 2rem; text-align: center; border-top: 4px solid var(--cyber-glow-pink);">
        <div style="max-width: 800px; margin: 0 auto;">
            <h3 style="font-size: 1.65rem; margin-bottom: 1.5rem; font-weight: 850; letter-spacing: -0.02em;">Department of Computer Science</h3>
            <div style="font-size: 0.85rem; color: #64748b; line-height: 1.8; font-weight: 600; font-family: monospace;">
                Ramon Adedoyin College of Natural and Applied Sciences<br>
                Oduduwa University Ipetumodu, Osun State, Nigeria<br>
                <span style="color: var(--cyber-glow-pink); display: block; margin-top: 1rem;">[ PORTAL VER: 2.1 - CORE SYNERGY STABLE ]</span>
            </div>
        </div>
    </footer>
</body>
</html>
