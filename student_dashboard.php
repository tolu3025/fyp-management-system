<?php
// student_dashboard.php
// Student dashboard showing project details, progress meters, and task milestones

require_once __DIR__ . '/includes/functions.php';
requireRole('Student');
require_once __DIR__ . '/includes/header.php';

$no_matrik = $_SESSION['user_id'];

// 1. Fetch Project Details & Supervisor
$project_stmt = $pdo->prepare("
    SELECT p.*, l.Nama AS SupervisorName, l.Email AS SupervisorEmail 
    FROM Project p
    LEFT JOIN Supervisor l ON p.No_staf = l.No_staf
    WHERE p.No_matrik = ?
");
$project_stmt->execute([$no_matrik]);
$project = $project_stmt->fetch();

// 2. Fetch Student Tasks
$tasks_stmt = $pdo->prepare("SELECT * FROM Task WHERE No_matrik = ? ORDER BY Deadline ASC");
$tasks_stmt->execute([$no_matrik]);
$tasks = $tasks_stmt->fetchAll();

// Calculate Progress Metrics
$total_tasks = count($tasks);
$completed_tasks = 0;
foreach ($tasks as $t) {
    if ($t['Pengesahan'] === 'Disahkan') {
        $completed_tasks++;
    }
}
$progress_pct = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;

// 3. Fetch Departmental Activities (Reference)
$activities = $pdo->query("SELECT * FROM Activity ORDER BY Tarikh ASC, Masa ASC LIMIT 3")->fetchAll();

// 4. Fetch Student Submissions
$subs_stmt = $pdo->prepare("SELECT * FROM Submissions WHERE No_matrik = ? ORDER BY Tarikh_Hantar DESC LIMIT 3");
$subs_stmt->execute([$no_matrik]);
$submissions = $subs_stmt->fetchAll();
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('student_workspace') ?></h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;"><?= __('system_title') ?></p>
</div>

<!-- Project Topic Card -->
<div class="card" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
    <div class="card-header" style="background: transparent;">
        <h3><?= __('my_fyp') ?></h3>
        <?php if ($project && empty($project['Tajuk_Projek'])): ?>
            <a href="student_project.php" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.5rem 1rem;"><?= __('reg_proj_title') ?></a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!$project): ?>
            <div style="text-align: center; color: var(--text-muted); padding: 1.5rem 0;">
                <p>⚠️ <?= __('not_assigned') ?></p>
            </div>
        <?php else: ?>
            <div class="detail-list" style="margin-bottom: 1.5rem;">
                <div class="detail-item">
                    <span class="detail-label"><?= __('project_title') ?>:</span>
                    <span class="detail-value" style="color: var(--primary); font-size: 1.1rem; line-height: 1.4;">
                        <?= $project['Tajuk_Projek'] ? sanitize($project['Tajuk_Projek']) : '<em style="color: var(--text-muted); font-weight: normal;">' . __('not_registered') . '. ' . __('discussions_precede') . '</em>' ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><?= __('supervisor') ?>:</span>
                    <span class="detail-value" style="color: var(--secondary);">
                        <?= $project['SupervisorName'] ? sanitize($project['SupervisorName']) : __('not_assigned') ?>
                        <?php if ($project['SupervisorEmail']): ?>
                            <span style="font-size: 0.8rem; font-weight: normal; color: var(--text-muted);"> (<?= sanitize($project['SupervisorEmail']) ?>)</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <!-- Progress Tracker -->
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">
                    <span><?= __('task_completion_audit') ?></span>
                    <span><?= $completed_tasks ?> / <?= $total_tasks ?> <?= __('checklist_approved') ?></span>
                </div>
                <div class="progress-bar-container" style="height: 12px;">
                    <div class="progress-bar-fill" style="width: <?= $progress_pct ?>%;"></div>
                </div>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500; display: block; margin-top: 0.25rem;"><?= $progress_pct ?>% <?= __('approved') ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="grid-2">
    <!-- Supervisor Tasks Checklist -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('supervisor_checklist') ?></h3>
            <a href="student_submissions.php" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;"><?= __('upload_submissions') ?></a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('task_goal') ?></th>
                            <th><?= __('deadline') ?></th>
                            <th><?= __('status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">No supervisor tasks assigned yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td style="font-weight: 500;"><?= sanitize($t['Jenis']) ?></td>
                                    <td><?= date('d M Y', strtotime($t['Deadline'])) ?></td>
                                    <td>
                                        <?php if ($t['Pengesahan'] === 'Disahkan'): ?>
                                            <span class="badge badge-approved"><?= __('approved') ?></span>
                                        <?php elseif ($t['Pengesahan'] === 'Hantar Semula'): ?>
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

    <!-- Upcoming Departmental Activities -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('upcoming_activities') ?></h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('activity_type') ?></th>
                            <th><?= __('date') ?> / <?= __('time') ?></th>
                            <th><?= __('location') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activities)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">No activities scheduled.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activities as $act): ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--primary);"><?= sanitize($act['Kod_aktiviti']) ?></strong><br>
                                        <span style="font-weight: 600; font-size: 0.85rem;"><?= sanitize($act['Jenis']) ?></span>
                                    </td>
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
</div>

<!-- Recent Submission Activity logs -->
<div class="card">
    <div class="card-header">
        <h3><?= __('recent_submissions') ?></h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('report_week') ?></th>
                        <th><?= __('submission_type') ?></th>
                        <th><?= __('date_submitted') ?></th>
                        <th><?= __('status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1.5rem;"><?= __('no_submissions') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td><strong><?= sanitize($sub['Tajuk']) ?></strong></td>
                                <td style="text-transform: capitalize; font-weight: 600; font-size: 0.85rem;"><?= sanitize($sub['Jenis_Hantaran']) ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($sub['Tarikh_Hantar'])) ?></td>
                                <td>
                                    <?php if ($sub['Status'] === 'Disemak'): ?>
                                        <span class="badge badge-approved"><?= __('approved') ?></span>
                                    <?php elseif ($sub['Status'] === 'Hantar Semula'): ?>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
