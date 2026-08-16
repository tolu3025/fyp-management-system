<?php
// supervisor_students.php
// Detailed monitoring portal for supervisors to inspect student tasks and submissions

require_once __DIR__ . '/includes/functions.php';
requireRole('Supervisor');
require_once __DIR__ . '/includes/header.php';

$no_staf = $_SESSION['user_id'];
$selected_student_id = $_GET['student_id'] ?? '';

// Fetch all assigned students
$students_stmt = $pdo->prepare("
    SELECT s.*, p.Tajuk_Projek, p.ID_projek 
    FROM Student s
    JOIN Project p ON s.No_matrik = p.No_matrik
    WHERE p.No_staf = ?
    ORDER BY s.Nama ASC
");
$students_stmt->execute([$no_staf]);
$assigned_students = $students_stmt->fetchAll();

// If no student is selected, but students exist, select the first one by default
if (empty($selected_student_id) && !empty($assigned_students)) {
    $selected_student_id = $assigned_students[0]['No_matrik'];
}

$selected_student = null;
$tasks = [];
$submissions = [];

if (!empty($selected_student_id)) {
    // Verify that the student is assigned to this supervisor
    foreach ($assigned_students as $stu) {
        if ($stu['No_matrik'] === $selected_student_id) {
            $selected_student = $stu;
            break;
        }
    }

    if ($selected_student) {
        // Fetch Tasks assigned to this student
        $tasks_stmt = $pdo->prepare("SELECT * FROM Task WHERE No_matrik = ? ORDER BY Deadline ASC");
        $tasks_stmt->execute([$selected_student_id]);
        $tasks = $tasks_stmt->fetchAll();

        // Fetch Submissions from this student
        $subs_stmt = $pdo->prepare("SELECT * FROM Submissions WHERE No_matrik = ? ORDER BY Tarikh_Hantar DESC");
        $subs_stmt->execute([$selected_student_id]);
        $submissions = $subs_stmt->fetchAll();
    }
}

// Fetch activities configured by HOD (for reference)
$activities = $pdo->query("SELECT * FROM Activity ORDER BY Tarikh ASC")->fetchAll();
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);">Supervision & Progress Auditor</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">View assigned student progress and assign milestone deliverables</p>
    </div>
    <a href="supervisor_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
</div>

<div class="dashboard-wrapper" style="min-height: auto; gap: 2rem; background: transparent; padding: 0;">
    <!-- Left Pane: Students List -->
    <div style="width: 320px; flex-shrink: 0;">
        <div class="card">
            <div class="card-header">
                <h3>Supervised Students</h3>
            </div>
            <div class="card-body" style="padding: 0.75rem;">
                <ul style="list-style: none;">
                    <?php if (empty($assigned_students)): ?>
                        <li style="padding: 1rem; color: var(--text-muted); font-style: italic; text-align: center;">No assigned students found.</li>
                    <?php else: ?>
                        <?php foreach ($assigned_students as $stu): ?>
                            <li style="margin-bottom: 0.5rem;">
                                <a href="supervisor_students.php?student_id=<?= urlencode($stu['No_matrik']) ?>" 
                                   class="btn <?= $stu['No_matrik'] === $selected_student_id ? 'btn-primary' : 'btn-secondary' ?>" 
                                   style="display: block; text-align: left; padding: 0.75rem 1rem; width: 100%;">
                                    <strong style="display: block; font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= sanitize($stu['Nama']) ?></strong>
                                    <span style="font-size: 0.7rem; opacity: 0.8;"><?= sanitize($stu['No_matrik']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Pane: Active Student Progress Monitoring -->
    <div style="flex-grow: 1; min-width: 0;">
        <?php if (!$selected_student): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <h3>No Student Selected</h3>
                    <p style="margin-top: 0.5rem;">Select a student from the sidebar panel to audit progress.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Student profile card -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header" style="background-color: #f8fafc;">
                    <div>
                        <h3><?= sanitize($selected_student['Nama']) ?></h3>
                        <p style="font-size: 0.8rem; color: var(--text-muted);"><?= sanitize($selected_student['No_matrik']) ?> — Semester <?= $selected_student['Semester'] ?> Student</p>
                    </div>
                    <div>
                        <a href="supervisor_tasks.php?student_id=<?= urlencode($selected_student['No_matrik']) ?>" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">➕ Assign Specific Task</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="detail-item">
                            <span class="detail-label">Project Title:</span>
                            <span class="detail-value" style="color: var(--primary); font-size: 0.95rem;">
                                <?= $selected_student['Tajuk_Projek'] ? sanitize($selected_student['Tajuk_Projek']) : '<em style="color: var(--text-muted); font-weight: normal;">No title registered yet</em>' ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?= sanitize($selected_student['Email']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departmental Milestones Activity list (Reference) -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3>Departmental Milestones (Reference)</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Activity Code</th>
                                    <th>Milestone / Activity</th>
                                    <th>Deadline</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activities)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: var(--text-muted);">No departmental milestones configured by HOD.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activities as $act): ?>
                                        <tr>
                                            <td style="font-weight: 600;"><?= sanitize($act['Kod_aktiviti']) ?></td>
                                            <td><?= sanitize($act['Jenis']) ?></td>
                                            <td><?= date('d M Y', strtotime($act['Tarikh'])) ?></td>
                                            <td><?= sanitize($act['Lokasi']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <!-- Tasks List -->
                <div class="card">
                    <div class="card-header">
                        <h3>Assigned Tasks Checklist</h3>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Task Type / Desc</th>
                                        <th>Deadline</th>
                                        <th>Endorsement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tasks)): ?>
                                        <tr>
                                            <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">No specific tasks assigned yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tasks as $task): ?>
                                            <tr>
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

                <!-- Submissions Review Panel -->
                <div class="card">
                    <div class="card-header">
                        <h3>Student Deliverables / Logs</h3>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type / Deliverable</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($submissions)): ?>
                                        <tr>
                                            <td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-muted);">No reports or files uploaded.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($submissions as $sub): ?>
                                            <tr>
                                                <td>
                                                    <span style="text-transform: capitalize; font-weight: 600; font-size: 0.8rem;"><?= sanitize($sub['Jenis_Hantaran']) ?></span><br>
                                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($sub['Tajuk']) ?></span>
                                                </td>
                                                <td><?= date('d M, H:i', strtotime($sub['Tarikh_Hantar'])) ?></td>
                                                <td>
                                                    <a href="supervisor_review.php?id=<?= $sub['ID_hantaran'] ?>" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">View</a>
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
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
