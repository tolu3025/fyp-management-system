<?php
// hod_reports.php
// FYP progress monitoring and supervision reports generated for HOD

require_once __DIR__ . '/includes/functions.php';
requireRole('HOD');
require_once __DIR__ . '/includes/header.php';

// Fetch overall progress report data
$report_stmt = $pdo->query("
    SELECT s.No_matrik, s.Nama AS StudentName, l.Nama AS SupervisorName, p.Tajuk_Projek, p.ID_projek,
           (SELECT COUNT(*) FROM Task t WHERE t.No_matrik = s.No_matrik) AS TotalTasks,
           (SELECT COUNT(*) FROM Task t WHERE t.No_matrik = s.No_matrik AND t.Pengesahan = 'Disahkan') AS CompletedTasks,
           (SELECT COUNT(*) FROM Submissions sb WHERE sb.No_matrik = s.No_matrik AND sb.Jenis_Hantaran = 'weekly') AS WeeklyReportsCount,
           (SELECT COUNT(*) FROM Submissions sb WHERE sb.No_matrik = s.No_matrik AND sb.Jenis_Hantaran = 'final') AS FinalReportCount
    FROM Student s
    LEFT JOIN Project p ON s.No_matrik = p.No_matrik
    LEFT JOIN Supervisor l ON p.No_staf = l.No_staf
    ORDER BY s.Nama ASC
");
$reports = $report_stmt->fetchAll();

// General Summary Metrics
$total_students = count($reports);
$total_finalized = 0;
$total_tasks_assigned = 0;
$total_tasks_completed = 0;

foreach ($reports as $r) {
    if ($r['FinalReportCount'] > 0) {
        $total_finalized++;
    }
    $total_tasks_assigned += $r['TotalTasks'];
    $total_tasks_completed += $r['CompletedTasks'];
}

$task_compliance_rate = $total_tasks_assigned > 0 ? round(($total_tasks_completed / $total_tasks_assigned) * 100) : 0;
$viva_readiness_rate = $total_students > 0 ? round(($total_finalized / $total_students) * 100) : 0;
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('monitoring_reports') ?></h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;"><?= __('monitoring_reports_desc') ?></p>
    </div>
    <a href="hod_dashboard.php" class="btn btn-secondary">⬅ <?= __('back_to_dashboard') ?></a>
</div>

<!-- Progress Summary Grid -->
<div class="stats-grid" style="margin-bottom: 2.5rem;">
    <div class="stat-card">
        <div class="stat-header">
            <span>Overall Final Report Submissions</span>
            <span>📄</span>
        </div>
        <div class="stat-value"><?= $total_finalized ?> / <?= $total_students ?></div>
        <div class="stat-footer">
            <div class="progress-bar-container" style="margin-top: 0.5rem;">
                <div class="progress-bar-fill" style="width: <?= $viva_readiness_rate ?>%; background-color: var(--success);"></div>
            </div>
            <span style="font-size: 0.7rem; display: block; margin-top: 0.25rem;"><?= $viva_readiness_rate ?>% Submission Compliance</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <span><?= __('overall_completion') ?></span>
            <span>✅</span>
        </div>
        <div class="stat-value"><?= $task_compliance_rate ?>%</div>
        <div class="stat-footer">
            <div class="progress-bar-container" style="margin-top: 0.5rem;">
                <div class="progress-bar-fill" style="width: <?= $task_compliance_rate ?>%;"></div>
            </div>
            <span style="font-size: 0.7rem; display: block; margin-top: 0.25rem;"><?= $total_tasks_completed ?> of <?= $total_tasks_assigned ?> tasks approved</span>
        </div>
    </div>
</div>

<!-- Main Reports Table Card -->
<div class="card">
    <div class="card-header">
        <h3>Student Progress Auditor Ledger</h3>
        <!-- Simple Print Shortcut Button -->
        <button class="btn btn-secondary" onclick="window.print()" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">🖨️ <?= __('generate_report') ?></button>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('student') ?></th>
                        <th><?= __('assigned_supervisor') ?></th>
                        <th><?= __('project_title') ?></th>
                        <th style="text-align: center;">Weekly Logs</th>
                        <th style="text-align: center;">Task Progress</th>
                        <th>Completion Ratio</th>
                        <th style="text-align: center;">Final Draft</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">No student records found in the database.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): 
                            $pct = $r['TotalTasks'] > 0 ? round(($r['CompletedTasks'] / $r['TotalTasks']) * 100) : 0;
                        ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($r['StudentName']) ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($r['No_matrik']) ?></span>
                                </td>
                                <td>
                                    <?php if ($r['SupervisorName']): ?>
                                        <span style="font-weight: 500;"><?= sanitize($r['SupervisorName']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--danger); font-style: italic; font-size: 0.8rem;"><?= __('not_assigned') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['Tajuk_Projek']): ?>
                                        <span style="font-size: 0.85rem; font-weight: 500; line-height: 1.3; display: block; max-width: 250px;"><?= sanitize($r['Tajuk_Projek']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-style: italic; font-size: 0.8rem;"><?= __('not_registered') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-approved" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;"><?= $r['WeeklyReportsCount'] ?> logs</span>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-weight: 600;"><?= $r['CompletedTasks'] ?></span> / <span style="color: var(--text-muted);"><?= $r['TotalTasks'] ?></span>
                                </td>
                                <td style="vertical-align: middle; width: 140px;">
                                    <div class="progress-bar-container" style="margin-bottom: 0.25rem;">
                                        <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted);"><?= $pct ?>% Complete</span>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($r['FinalReportCount'] > 0): ?>
                                        <span class="badge badge-approved">Submitted</span>
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
