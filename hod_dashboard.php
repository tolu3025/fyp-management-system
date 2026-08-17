<?php
// hod_dashboard.php
// Head of Department Dashboard landing panel

require_once __DIR__ . '/includes/functions.php';
requireRole('HOD');
require_once __DIR__ . '/includes/header.php';

// Fetch Statistics
$total_supervisors = $pdo->query("SELECT COUNT(*) FROM Supervisor")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM Student")->fetchColumn();
$total_projects = $pdo->query("SELECT COUNT(*) FROM Project WHERE Tajuk_Projek IS NOT NULL AND Tajuk_Projek != ''")->fetchColumn();

// Students who are not assigned to a supervisor
$unassigned_students = $pdo->query("SELECT COUNT(*) FROM Student s LEFT JOIN Project p ON s.No_matrik = p.No_matrik WHERE p.No_staf IS NULL")->fetchColumn();

// Fetch Activities
$activities_stmt = $pdo->query("SELECT * FROM Activity ORDER BY Tarikh ASC, Masa ASC LIMIT 5");
$activities = $activities_stmt->fetchAll();

// Fetch Recent Tasks assigned
$recent_tasks_stmt = $pdo->query("
    SELECT t.*, s.Nama AS StudentName, l.Nama AS SupervisorName 
    FROM Task t 
    JOIN Student s ON t.No_matrik = s.No_matrik 
    JOIN Supervisor l ON t.No_staf = l.No_staf 
    ORDER BY t.Tarikh DESC LIMIT 5
");
$recent_tasks = $recent_tasks_stmt->fetchAll();

// Fetch all registered students for HOD dashboard display
$students_list_stmt = $pdo->query("
    SELECT s.No_matrik, s.Nama AS StudentName, s.Email AS StudentEmail, s.Semester,
           l.Nama AS SupervisorName, p.Tajuk_Projek 
    FROM Student s
    LEFT JOIN Project p ON s.No_matrik = p.No_matrik
    LEFT JOIN Supervisor l ON p.No_staf = l.No_staf
    ORDER BY s.Nama ASC
");
$students_list = $students_list_stmt->fetchAll();
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('hod_workspace') ?></h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;"><?= __('system_title') ?></p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span><?= __('total_supervisors') ?></span>
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-value"><?= $total_supervisors ?></div>
        <div class="stat-footer">Registered lecturers</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span><?= __('total_students') ?></span>
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="stat-value"><?= $total_students ?></div>
        <div class="stat-footer">Final year students</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span><?= __('assigned_projects') ?></span>
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <div class="stat-value"><?= $total_projects ?></div>
        <div class="stat-footer">Titles registered by students</div>
    </div>

    <div class="stat-card" style="border-top: 4px solid <?= $unassigned_students > 0 ? 'var(--danger)' : 'var(--success)' ?>;">
        <div class="stat-header">
            <span>Unassigned Students</span>
            <i class="fa-solid fa-triangle-exclamation" style="color: <?= $unassigned_students > 0 ? 'var(--danger)' : 'var(--success)' ?>;"></i>
        </div>
        <div class="stat-value"><?= $unassigned_students ?></div>
        <div class="stat-footer">Awaiting supervisor allocation</div>
    </div>
</div>

<div class="grid-2">
    <!-- Upcoming Activities Card -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('activity_mgmt') ?></h3>
            <a href="hod_activities.php" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;"><i class="fa-solid fa-sliders"></i> <?= __('manage_activities') ?></a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('activity_code') ?></th>
                            <th><?= __('activity_type') ?></th>
                            <th><?= __('date') ?> / <?= __('time') ?></th>
                            <th><?= __('location') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activities)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No departmental activities scheduled.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activities as $act): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= sanitize($act['Kod_aktiviti']) ?></td>
                                    <td><?= sanitize($act['Jenis']) ?></td>
                                    <td>
                                        <?= date('d M Y', strtotime($act['Tarikh'])) ?><br>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?= date('h:i A', strtotime($act['Masa'])) ?></span>
                                    </td>
                                    <td><?= sanitize($act['Lokasi']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Task Assignments -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Tasks Assigned by Supervisors</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('student') ?></th>
                            <th><?= __('task_goal') ?></th>
                            <th><?= __('deadline') ?></th>
                            <th><?= __('status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_tasks)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No tasks assigned yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_tasks as $task): ?>
                                <tr>
                                    <td>
                                        <strong><?= sanitize($task['StudentName']) ?></strong><br>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">By <?= sanitize($task['SupervisorName']) ?></span>
                                    </td>
                                    <td><?= sanitize($task['Jenis']) ?></td>
                                    <td><?= date('d M Y', strtotime($task['Deadline'])) ?></td>
                                    <td>
                                        <?php if ($task['Pengesahan'] === 'Disahkan'): ?>
                                            <span class="badge badge-approved"><?= __('approved') ?></span>
                                        <?php elseif ($task['Pengesahan'] === 'Hantar Semula'): ?>
                                            <span class="badge badge-resubmit"><?= __('resubmit') ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-pending"><?= __('pending') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Registered Students Registry -->
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h3>Registered Students (Departmental Registry)</h3>
        <a href="hod_registration.php" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;"><i class="fa-solid fa-user-plus"></i> <?= __('user_mgmt') ?></a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('student') ?></th>
                        <th><?= __('email') ?></th>
                        <th style="text-align: center;"><?= __('semester') ?></th>
                        <th><?= __('assigned_supervisor') ?></th>
                        <th><?= __('project_title') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students_list)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No registered students found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students_list as $stu): ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($stu['StudentName']) ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($stu['No_matrik']) ?></span>
                                </td>
                                <td><?= sanitize($stu['StudentEmail']) ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= sanitize($stu['Semester']) ?></td>
                                <td>
                                    <?php if ($stu['SupervisorName']): ?>
                                        <span style="color: var(--secondary); font-weight: 600;"><i class="fa-solid fa-chalkboard-user"></i> <?= sanitize($stu['SupervisorName']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-pending" style="font-size: 0.75rem;"><i class="fa-solid fa-triangle-exclamation"></i> Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($stu['Tajuk_Projek']): ?>
                                        <span style="font-size: 0.85rem; font-weight: 500;"><?= sanitize($stu['Tajuk_Projek']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-style: italic; font-size: 0.8rem;">Pending discussion</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
