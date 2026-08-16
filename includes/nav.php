<?php
// includes/nav.php
// Dynamic role-based navigation sidebar

$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['user_role'] ?? '';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>FYP System</h2>
        <p>Oduduwa University</p>
    </div>
    
    <nav class="sidebar-menu">
        <ul>
            <?php if ($role === 'HOD'): ?>
                <li class="sidebar-item">
                    <a href="hod_dashboard.php" class="sidebar-link <?= $currentPage === 'hod_dashboard.php' ? 'active' : '' ?>">
                        <span>📊</span> Dashboard
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="hod_registration.php" class="sidebar-link <?= $currentPage === 'hod_registration.php' ? 'active' : '' ?>">
                        <span>👥</span> Register & Assign
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="hod_activities.php" class="sidebar-link <?= $currentPage === 'hod_activities.php' ? 'active' : '' ?>">
                        <span>📅</span> Manage Activities
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="hod_reports.php" class="sidebar-link <?= $currentPage === 'hod_reports.php' ? 'active' : '' ?>">
                        <span>📈</span> Progress Reports
                    </a>
                </li>
            <?php elseif ($role === 'Supervisor'): ?>
                <li class="sidebar-item">
                    <a href="supervisor_dashboard.php" class="sidebar-link <?= $currentPage === 'supervisor_dashboard.php' ? 'active' : '' ?>">
                        <span>📊</span> Dashboard
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="supervisor_students.php" class="sidebar-link <?= $currentPage === 'supervisor_students.php' || $currentPage === 'supervisor_tasks.php' || $currentPage === 'supervisor_review.php' ? 'active' : '' ?>">
                        <span>👨‍🎓</span> My Students
                    </a>
                </li>
            <?php elseif ($role === 'Student'): ?>
                <li class="sidebar-item">
                    <a href="student_dashboard.php" class="sidebar-link <?= $currentPage === 'student_dashboard.php' ? 'active' : '' ?>">
                        <span>📊</span> Dashboard
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="student_project.php" class="sidebar-link <?= $currentPage === 'student_project.php' ? 'active' : '' ?>">
                        <span>📁</span> Register Title
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="student_submissions.php" class="sidebar-link <?= $currentPage === 'student_submissions.php' ? 'active' : '' ?>">
                        <span>📤</span> Tasks & Submissions
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="sidebar-item" style="margin-top: 2rem;">
                <a href="index.php?logout=true" class="sidebar-link" style="color: var(--danger);">
                    <span>🚪</span> Logout
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-user">
        <span class="user-name"><?= sanitize($_SESSION['user_name']) ?></span>
        <span class="user-role"><?= sanitize($_SESSION['user_role']) ?></span>
    </div>
</aside>
