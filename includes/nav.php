<?php
// includes/nav.php
// Dynamic role-based navigation sidebar

$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['user_role'] ?? '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <h2><i class="fa-solid fa-laptop-code"></i> FYP Portal</h2>
            <button class="mobile-sidebar-close" id="mobileSidebarClose" aria-label="Close Menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <p>Oduduwa University</p>
    </div>
    
    <nav class="sidebar-menu">
        <ul>
            <?php if ($role === 'HOD'): ?>
                <li class="sidebar-item">
                    <a href="hod_dashboard.php" class="sidebar-link <?= $currentPage === 'hod_dashboard.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-pie"></i> <?= __('dashboard') ?>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="hod_registration.php" class="sidebar-link <?= $currentPage === 'hod_registration.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-gear"></i> <?= __('user_mgmt') ?>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="hod_activities.php" class="sidebar-link <?= $currentPage === 'hod_activities.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-calendar-check"></i> <?= __('activity_mgmt') ?>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="hod_reports.php" class="sidebar-link <?= $currentPage === 'hod_reports.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-line"></i> <?= __('monitoring_reports') ?>
                    </a>
                </li>
            <?php elseif ($role === 'Supervisor'): ?>
                <li class="sidebar-item">
                    <a href="supervisor_dashboard.php" class="sidebar-link <?= $currentPage === 'supervisor_dashboard.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-pie"></i> <?= __('dashboard') ?>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="supervisor_students.php" class="sidebar-link <?= $currentPage === 'supervisor_students.php' || $currentPage === 'supervisor_tasks.php' || $currentPage === 'supervisor_review.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-users-viewfinder"></i> <?= __('my_students') ?>
                    </a>
                </li>
            <?php elseif ($role === 'Student'): ?>
                <li class="sidebar-item">
                    <a href="student_dashboard.php" class="sidebar-link <?= $currentPage === 'student_dashboard.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-pie"></i> <?= __('dashboard') ?>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="student_project.php" class="sidebar-link <?= $currentPage === 'student_project.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-folder-plus"></i> <?= __('project_title') ?>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="student_submissions.php" class="sidebar-link <?= $currentPage === 'student_submissions.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Tasks & Submissions
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="sidebar-item" style="margin-top: 2rem;">
                <a href="index.php?logout=true" class="sidebar-link" style="color: var(--danger);">
                    <i class="fa-solid fa-right-from-bracket"></i> <?= __('logout') ?>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-user">
        <div class="sidebar-user-info">
            <span class="user-name"><?= sanitize($_SESSION['user_name']) ?></span>
            <span class="user-role"><?= sanitize($_SESSION['user_role']) ?></span>
        </div>
    </div>
</aside>
