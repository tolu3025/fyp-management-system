<?php
// supervisor_dashboard.php
// Supervisor Panel displaying assigned students, pending submissions, and quick stats

require_once __DIR__ . '/includes/functions.php';
requireRole('Supervisor');
require_once __DIR__ . '/includes/header.php';

$no_staf = $_SESSION['user_id'];

// 1. Statistics
$total_students = $pdo->prepare("SELECT COUNT(*) FROM Project WHERE No_staf = ?");
$total_students->execute([$no_staf]);
$count_students = $total_students->fetchColumn();

$pending_reviews = $pdo->prepare("
    SELECT COUNT(*) 
    FROM Submissions sb
    JOIN Project p ON sb.ID_projek = p.ID_projek
    WHERE p.No_staf = ? AND sb.Status = 'Menunggu Semakan'
");
$pending_reviews->execute([$no_staf]);
$count_pending = $pending_reviews->fetchColumn();

$completed_tasks = $pdo->prepare("SELECT COUNT(*) FROM Task WHERE No_staf = ? AND Pengesahan = 'Disahkan'");
$completed_tasks->execute([$no_staf]);
$count_completed = $completed_tasks->fetchColumn();

// 2. Fetch Assigned Students details
$students_stmt = $pdo->prepare("
    SELECT s.No_matrik, s.Nama AS StudentName, s.Email AS StudentEmail, p.Tajuk_Projek, p.ID_projek,
           (SELECT COUNT(*) FROM Task t WHERE t.No_matrik = s.No_matrik) AS TotalTasks,
           (SELECT COUNT(*) FROM Task t WHERE t.No_matrik = s.No_matrik AND t.Pengesahan = 'Disahkan') AS CompletedTasks
    FROM Student s
    JOIN Project p ON s.No_matrik = p.No_matrik
    WHERE p.No_staf = ?
    ORDER BY s.Nama ASC
");
$students_stmt->execute([$no_staf]);
$assigned_students = $students_stmt->fetchAll();

// 3. Fetch Recent submissions pending review
$recent_submissions_stmt = $pdo->prepare("
    SELECT sb.*, s.Nama AS StudentName 
    FROM Submissions sb
    JOIN Student s ON sb.No_matrik = s.No_matrik
    JOIN Project p ON sb.ID_projek = p.ID_projek
    WHERE p.No_staf = ?
    ORDER BY sb.Tarikh_Hantar DESC LIMIT 5
");
$recent_submissions_stmt->execute([$no_staf]);
$recent_submissions = $recent_submissions_stmt->fetchAll();
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('supervisor_workspace') ?></h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;"><?= __('system_title') ?></p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span><?= __('my_students_load') ?></span>
            <i class="fa-solid fa-user-graduate"></i>
        </div>
        <div class="stat-value"><?= $count_students ?></div>
        <div class="stat-footer">Assigned by Department HOD</div>
    </div>
    
    <div class="stat-card" style="border-top: 4px solid <?= $count_pending > 0 ? 'var(--warning)' : 'var(--border)' ?>;">
        <div class="stat-header">
            <span><?= __('waiting_review') ?></span>
            <i class="fa-solid fa-inbox"></i>
        </div>
        <div class="stat-value"><?= $count_pending ?></div>
        <div class="stat-footer">Awaiting feedback or validation</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span><?= __('approved') ?> Tasks</span>
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-value"><?= $count_completed ?></div>
        <div class="stat-footer">Validated student goals</div>
    </div>
</div>

<div class="grid-2">
    <!-- Supervised Students List -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('active_supervision_load') ?></h3>
            <a href="supervisor_students.php" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;"><i class="fa-solid fa-users"></i> <?= __('my_students') ?></a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('student') ?></th>
                            <th><?= __('project_title') ?></th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assigned_students)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;"><?= __('no_students_assigned') ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assigned_students as $stu): 
                                $pct = $stu['TotalTasks'] > 0 ? round(($stu['CompletedTasks'] / $stu['TotalTasks']) * 100) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= sanitize($stu['StudentName']) ?></strong><br>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($stu['No_matrik']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($stu['Tajuk_Projek']): ?>
                                            <span style="font-size: 0.8rem; display: block; max-width: 250px; line-height: 1.3; font-weight: 500;"><?= sanitize($stu['Tajuk_Projek']) ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-style: italic; font-size: 0.8rem;"><?= __('not_registered') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="width: 120px;">
                                        <div class="progress-bar-container" style="margin-bottom: 0.25rem;">
                                            <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                        <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;"><?= $pct ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Submissions Pending Feedback -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('tasks_submissions') ?></h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('student') ?></th>
                            <th><?= __('submission_type') ?></th>
                            <th><?= __('date') ?></th>
                            <th><?= __('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_submissions)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;"><?= __('no_submissions') ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_submissions as $sub): ?>
                                <tr>
                                    <td><strong><?= sanitize($sub['StudentName']) ?></strong></td>
                                    <td>
                                        <span style="text-transform: capitalize; font-weight: 600; font-size: 0.8rem;"><?= sanitize($sub['Jenis_Hantaran']) ?></span><br>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($sub['Tajuk']) ?></span>
                                    </td>
                                    <td><?= date('d M, H:i', strtotime($sub['Tarikh_Hantar'])) ?></td>
                                    <td>
                                        <a href="supervisor_review.php?id=<?= $sub['ID_hantaran'] ?>" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;"><i class="fa-solid fa-magnifying-glass"></i> <?= __('submit') ?></a>
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
