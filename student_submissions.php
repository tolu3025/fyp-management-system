<?php
// student_submissions.php
// Student submission management page (Weekly updates, task uploads, final report)

require_once __DIR__ . '/includes/functions.php';
requireRole('Student');
require_once __DIR__ . '/includes/header.php';

$no_matrik = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'];

// Fetch project/supervisor details
$project_stmt = $pdo->prepare("
    SELECT p.*, l.Email AS SupervisorEmail, l.Nama AS SupervisorName 
    FROM Project p
    LEFT JOIN Supervisor l ON p.No_staf = l.No_staf
    WHERE p.No_matrik = ?
");
$project_stmt->execute([$no_matrik]);
$project = $project_stmt->fetch();

if (!$project || !$project['No_staf']) {
    echo '
    <div class="card" style="max-width: 600px; margin: 2rem auto;">
        <div class="card-header" style="background-color: #fee2e2; border-bottom-color: #fca5a5;">
            <h3 style="color: #991b1b;"><i class="fa-solid fa-triangle-exclamation"></i> Supervision Allocation Required</h3>
        </div>
        <div class="card-body" style="text-align: center;">
            <p style="margin-bottom: 1.5rem; color: #7f1d1d;">You cannot make submissions until the HOD has assigned a supervisor to your profile.</p>
            <a href="student_dashboard.php" class="btn btn-secondary">' . __('back_to_dashboard') . '</a>
        </div>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$supervisor_id = $project['No_staf'];
$supervisor_email = $project['SupervisorEmail'];
$supervisor_name = $project['SupervisorName'];
$project_id = $project['ID_projek'];

// Fetch pending tasks to populate dropdown (tasks that are not yet endorsed)
$tasks_stmt = $pdo->prepare("SELECT * FROM Task WHERE No_matrik = ? AND Pengesahan != 'Disahkan' ORDER BY Deadline ASC");
$tasks_stmt->execute([$no_matrik]);
$pending_tasks = $tasks_stmt->fetchAll();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_type = $_POST['submission_type'] ?? '';

    // Create uploads folder if not exists
    $upload_dir = __DIR__ . '/uploads';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if ($submission_type === 'weekly') {
        $week_title = trim($_POST['Tajuk'] ?? '');
        $content = trim($_POST['Kandungan'] ?? '');

        if (empty($week_title) || empty($content)) {
            $_SESSION['error_msg'] = "Please provide both the report week title and progress details.";
        } else {
            try {
                // Insert weekly submission
                $stmt = $pdo->prepare("
                    INSERT INTO Submissions (ID_projek, No_matrik, ID_tugasan, Jenis_Hantaran, Tajuk, Kandungan, File_Path, Status) 
                    VALUES (?, ?, NULL, 'weekly', ?, ?, NULL, 'Menunggu Semakan')
                ");
                $stmt->execute([$project_id, $no_matrik, $week_title, $content]);

                // Trigger email & dashboard alert
                triggerSubmissionNotifications($pdo, $student_name, $no_matrik, 'Weekly Progress Report', $week_title, $supervisor_id, $supervisor_email, $supervisor_name);

                $_SESSION['success_msg'] = "Weekly progress report submitted successfully.";
                header("Location: student_submissions.php");
                exit();
            } catch (PDOException $e) {
                $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
            }
        }
    } elseif ($submission_type === 'task') {
        $task_id = intval($_POST['ID_tugasan'] ?? 0);
        $task_title = '';
        
        // Find task title
        foreach ($pending_tasks as $t) {
            if ($t['ID_tugasan'] === $task_id) {
                $task_title = $t['Jenis'];
                break;
            }
        }

        if (empty($task_id) || empty($task_title)) {
            $_SESSION['error_msg'] = "Please select a valid assigned task.";
        } elseif (!isset($_FILES['DeliverableFile']) || $_FILES['DeliverableFile']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_msg'] = "Please upload a valid deliverable file.";
        } else {
            $file = $_FILES['DeliverableFile'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Allow basic documents
            $allowed_exts = ['pdf', 'zip', 'docx', 'doc', 'png', 'jpg'];
            if (!in_array($file_ext, $allowed_exts)) {
                $_SESSION['error_msg'] = "Invalid file type. Allowed formats: " . implode(', ', $allowed_exts);
            } else {
                $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file['name']);
                $dest_path = $upload_dir . '/' . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                    try {
                        $pdo->beginTransaction();

                        // Insert submission
                        $stmt = $pdo->prepare("
                            INSERT INTO Submissions (ID_projek, No_matrik, ID_tugasan, Jenis_Hantaran, Tajuk, Kandungan, File_Path, Status) 
                            VALUES (?, ?, ?, 'task', ?, NULL, ?, 'Menunggu Semakan')
                        ");
                        $stmt->execute([$project_id, $no_matrik, $task_id, $task_title, $new_filename]);

                        // Update task status locally
                        $up = $pdo->prepare("UPDATE Task SET Pengesahan = 'Menunggu Semakan' WHERE ID_tugasan = ?");
                        $up->execute([$task_id]);

                        $pdo->commit();

                        // Trigger notifications
                        triggerSubmissionNotifications($pdo, $student_name, $no_matrik, 'Completed Task File', $task_title, $supervisor_id, $supervisor_email, $supervisor_name);

                        $_SESSION['success_msg'] = "Task deliverable uploaded successfully.";
                        header("Location: student_submissions.php");
                        exit();
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error_msg'] = "File upload failed to write to disk.";
                }
            }
        }
    } elseif ($submission_type === 'final') {
        if (!isset($_FILES['FinalReportFile']) || $_FILES['FinalReportFile']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_msg'] = "Please select a valid final report file.";
        } else {
            $file = $_FILES['FinalReportFile'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file_ext !== 'pdf') {
                $_SESSION['error_msg'] = "Only PDF files are allowed for final project report submissions.";
            } else {
                $new_filename = "FINAL_" . time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file['name']);
                $dest_path = $upload_dir . '/' . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                    try {
                        // Insert final report submission
                        $stmt = $pdo->prepare("
                            INSERT INTO Submissions (ID_projek, No_matrik, ID_tugasan, Jenis_Hantaran, Tajuk, Kandungan, File_Path, Status) 
                            VALUES (?, ?, NULL, 'final', 'Final FYP Report Submission', NULL, ?, 'Menunggu Semakan')
                        ");
                        $stmt->execute([$project_id, $no_matrik, $new_filename]);

                        // Trigger notification
                        triggerSubmissionNotifications($pdo, $student_name, $no_matrik, 'Final Project Thesis', 'Final FYP Report Draft', $supervisor_id, $supervisor_email, $supervisor_name);

                        $_SESSION['success_msg'] = "Final Project Report submitted successfully.";
                        header("Location: student_submissions.php");
                        exit();
                    } catch (PDOException $e) {
                        $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error_msg'] = "File upload failed.";
                }
            }
        }
    }
}

/**
 * Triggers alerts for both Supervisor and HOD (Dashboard and Email logs)
 */
function triggerSubmissionNotifications($pdo, $student_name, $no_matrik, $deliverable_type, $title, $supervisor_id, $supervisor_email, $supervisor_name) {
    $now = date('Y-m-d H:i:s');
    $notif_text = "New submission by $student_name ($no_matrik): $deliverable_type - '$title' on $now.";

    // 1. Dashboard alerts
    createNotification($pdo, $supervisor_id, $notif_text);
    createNotification($pdo, 'HOD001', $notif_text);

    // 2. Email alerts
    if ($supervisor_email) {
        sendSystemEmail(
            $supervisor_email,
            $supervisor_name,
            "New Student Submission - FYP",
            "Hello Dr./Mr./Mrs. $supervisor_name,\n\nYour assigned student $student_name ($no_matrik) has made a new submission:\n\nType: $deliverable_type\nTitle: $title\nTime: $now\n\nPlease log in to review and post feedback comments."
        );
    }

    sendSystemEmail(
        'hod@oduduwa.edu.ng',
        'Dr. J. A. Adedoyin (HOD)',
        "New Student Submission Alert - FYP",
        "Hello HOD,\n\nA student $student_name ($no_matrik) has made a new submission in the portal:\n\nType: $deliverable_type\nTitle: $title\nAssigned Supervisor: $supervisor_name\n\nPlease log in to audit student progress."
    );
}

// Fetch all submissions for listing below forms
$history_stmt = $pdo->prepare("SELECT * FROM Submissions WHERE No_matrik = ? ORDER BY Tarikh_Hantar DESC");
$history_stmt->execute([$no_matrik]);
$submissions_history = $history_stmt->fetchAll();
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('tasks_submissions') ?></h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;"><?= __('submit_deliverables_desc') ?></p>
    </div>
    <a href="student_dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('back_to_dashboard') ?></a>
</div>

<div class="grid-2" style="margin-bottom: 2rem;">
    <!-- Column 1: Progress updates & Final Submission -->
    <div>
        <!-- Submit Weekly Update -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3><?= __('weekly_progress_report') ?></h3>
            </div>
            <div class="card-body">
                <form action="student_submissions.php" method="POST">
                    <input type="hidden" name="submission_type" value="weekly">

                    <div class="form-group">
                        <label for="weekly_title" class="form-label"><?= __('report_week') ?></label>
                        <input type="text" name="Tajuk" id="weekly_title" class="form-input" placeholder="e.g. Week 4 Progress Report" required>
                    </div>

                    <div class="form-group">
                        <label for="weekly_content" class="form-label"><?= __('report_content') ?></label>
                        <textarea name="Kandungan" id="weekly_content" class="form-input" rows="4" placeholder="Explain tasks performed, findings, or challenges this week..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <?= __('submit') ?></button>
                </form>
            </div>
        </div>

        <!-- Submit Final Project Thesis -->
        <div class="card">
            <div class="card-header">
                <h3><?= __('submit_final_report') ?></h3>
            </div>
            <div class="card-body">
                <form action="student_submissions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="submission_type" value="final">

                    <div class="form-group">
                        <label for="final_file" class="form-label"><?= __('final_thesis') ?></label>
                        <input type="file" name="FinalReportFile" id="final_file" class="form-input" accept=".pdf" required>
                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Only PDF files are accepted. This will trigger a critical department review alert.</span>
                    </div>

                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-file-pdf"></i> <?= __('submit_final_report') ?></button>
                </form>
            </div>
        </div>
    </div>

    <!-- Column 2: Upload Task Deliverables -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3><?= __('upload_task_file') ?></h3>
            </div>
            <div class="card-body">
                <form action="student_submissions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="submission_type" value="task">

                    <div class="form-group">
                        <label for="task_dropdown" class="form-label"><?= __('select_task') ?></label>
                        <select name="ID_tugasan" id="task_dropdown" class="form-input" required>
                            <option value=""><?= __('choose_task') ?></option>
                            <?php foreach ($pending_tasks as $t): ?>
                                <option value="<?= $t['ID_tugasan'] ?>"><?= sanitize($t['Jenis']) ?> (Deadline: <?= date('d M Y', strtotime($t['Deadline'])) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="task_file" class="form-label"><?= __('upload_file') ?></label>
                        <input type="file" name="DeliverableFile" id="task_file" class="form-input" accept=".pdf,.zip,.docx,.doc,.png,.jpg" required>
                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Accepted: PDF, ZIP, DOCX, Word, Images. Max 10MB.</span>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-block"><i class="fa-solid fa-cloud-arrow-up"></i> <?= __('submit') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Submissions History Ledger -->
<div class="card">
    <div class="card-header">
        <h3><?= __('my_submission_history') ?></h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title / Target</th>
                        <th>Type</th>
                        <th>Date Submitted</th>
                        <th>Attachment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions_history)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No items submitted yet. Use the forms above to make a submission.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions_history as $sub): ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($sub['Tajuk']) ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= $sub['Kandungan'] ? substr(sanitize($sub['Kandungan']), 0, 75) . '...' : 'No text content' ?></span>
                                </td>
                                <td style="text-transform: capitalize; font-weight: 600;"><?= sanitize($sub['Jenis_Hantaran']) ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($sub['Tarikh_Hantar'])) ?></td>
                                <td>
                                    <?php if ($sub['File_Path']): ?>
                                        <a href="uploads/<?= urlencode($sub['File_Path']) ?>" style="color: var(--secondary); font-weight: 600; text-decoration: none;" download>
                                            <i class="fa-solid fa-file-arrow-down"></i> <?= __('download_file') ?> (<?= sanitize(pathinfo($sub['File_Path'], PATHINFO_EXTENSION)) ?>)
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-style: italic; font-size: 0.8rem;">None</span>
                                    <?php endif; ?>
                                </td>
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
