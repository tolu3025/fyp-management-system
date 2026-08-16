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
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);">Welcome back, HOD</h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">FYP Management System — Oduduwa University Ipetumodu</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span>Total Supervisors</span>
            <span>👥</span>
        </div>
        <div class="stat-value"><?= $total_supervisors ?></div>
        <div class="stat-footer">Registered lecturers</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span>Total Students</span>
            <span>🎓</span>
        </div>
        <div class="stat-value"><?= $total_students ?></div>
        <div class="stat-footer">Final year students</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span>Registered Projects</span>
            <span>📁</span>
        </div>
        <div class="stat-value"><?= $total_projects ?></div>
        <div class="stat-footer">Titles registered by students</div>
    </div>

    <div class="stat-card" style="border-top: 4px solid <?= $unassigned_students > 0 ? 'var(--danger)' : 'var(--success)' ?>;">
        <div class="stat-header">
            <span>Unassigned Students</span>
            <span>⚠️</span>
        </div>
        <div class="stat-value"><?= $unassigned_students ?></div>
        <div class="stat-footer">Awaiting supervisor allocation</div>
    </div>
</div>

<div class="grid-2">
    <!-- Upcoming Activities Card -->
    <div class="card">
        <div class="card-header">
            <h3>Departmental FYP Activities</h3>
            <a href="hod_activities.php" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">Manage</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Activity</th>
                            <th>Date / Time</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activities)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted);">No departmental activities scheduled.</td>
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
                            <th>Student</th>
                            <th>Task</th>
                            <th>Deadline</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_tasks)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted);">No tasks assigned yet.</td>
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
                                            <span class="badge badge-approved">Approved</span>
                                        <?php elseif ($task['Pengesahan'] === 'Hantar Semula'): ?>
                                            <span class="badge badge-resubmit">Resubmit</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Pending</span>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
