<?php
// supervisor_review.php
// Deliverables review page for Supervisors and HOD

require_once __DIR__ . '/includes/functions.php';
requireRole(['Supervisor', 'HOD']);
require_once __DIR__ . '/includes/header.php';

$sub_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Fetch Submission details
$sub_stmt = $pdo->prepare("
    SELECT sb.*, s.Nama AS StudentName, s.Email AS StudentEmail, p.No_staf AS SupervisorID, l.Nama AS SupervisorName,
           t.ID_tugasan, t.Jenis AS TaskJenis, t.Pengesahan AS TaskPengesahan
    FROM Submissions sb
    JOIN Student s ON sb.No_matrik = s.No_matrik
    JOIN Project p ON sb.ID_projek = p.ID_projek
    LEFT JOIN Supervisor l ON p.No_staf = l.No_staf
    LEFT JOIN Task t ON sb.ID_tugasan = t.ID_tugasan
    WHERE sb.ID_hantaran = ?
");
$sub_stmt->execute([$sub_id]);
$sub = $sub_stmt->fetch();

if (!$sub) {
    echo '<div class="alert alert-danger">Submission record not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Security: If Supervisor, verify they are the assigned supervisor
if ($user_role === 'Supervisor' && $sub['SupervisorID'] !== $user_id) {
    echo '<div class="alert alert-danger">Unauthorized. You can only review submissions from your assigned students.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Handle Comment & Action Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $ulasan = trim($_POST['Ulasan'] ?? '');
    $status_action = $_POST['StatusAction'] ?? ''; // 'endorse', 'resubmit', 'feedback_only'

    if (empty($ulasan)) {
        $_SESSION['error_msg'] = "Please provide feedback text.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Insert comment
            $comment_stmt = $pdo->prepare("
                INSERT INTO Comments (ID_hantaran, Pengulas_ID, Peranan_Pengulas, Ulasan, Tarikh_Ulasan) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $comment_stmt->execute([$sub_id, $user_id, $user_role, $ulasan]);

            // Determine status strings
            $sub_status = 'Disemak'; // Default Checked
            $task_status = 'Disahkan'; // Default Endorsed

            if ($status_action === 'resubmit') {
                $sub_status = 'Hantar Semula';
                $task_status = 'Hantar Semula';
            } elseif ($status_action === 'feedback_only') {
                $sub_status = $sub['Status'];
                $task_status = $sub['TaskPengesahan'];
            }

            // 2. Update Submission Status
            $update_sub = $pdo->prepare("UPDATE Submissions SET Status = ? WHERE ID_hantaran = ?");
            $update_sub->execute([$sub_status, $sub_id]);

            // 3. Update Task table if linked
            if ($sub['ID_tugasan'] && $status_action !== 'feedback_only') {
                $update_task = $pdo->prepare("UPDATE Task SET Pengesahan = ? WHERE ID_tugasan = ?");
                $update_task->execute([$task_status, $sub['ID_tugasan']]);
            }

            // 4. Trigger Notifications & Emails
            $reviewer_name = $_SESSION['user_name'];
            $notif_msg = "$reviewer_name posted feedback on your " . sanitize($sub['Jenis_Hantaran']) . " submission: '" . sanitize($sub['Tajuk']) . "'";
            if ($status_action === 'endorse') {
                $notif_msg .= " and endorsed the progress.";
            } elseif ($status_action === 'resubmit') {
                $notif_msg .= " and requested a resubmission.";
            }

            // Notify Student
            createNotification($pdo, $sub['No_matrik'], $notif_msg);
            
            // Log/Email Student
            sendSystemEmail(
                $sub['StudentEmail'], 
                $sub['StudentName'], 
                "Feedback Received on Submission", 
                "Hello " . $sub['StudentName'] . ",\n\nYour FYP " . $user_role . " (" . $reviewer_name . ") has posted feedback on your submission.\n\nSubmission: " . $sub['Tajuk'] . " (" . $sub['Jenis_Hantaran'] . ")\n\nFeedback:\n\"" . $ulasan . "\"\n\nStatus Action: " . ($status_action === 'endorse' ? 'Approved & Endorsed' : ($status_action === 'resubmit' ? 'Resubmission Requested' : 'Feedback Left')) . "\n\nPlease log in to the portal to review full comments."
            );

            // If HOD is acting as mediator, also notify the Supervisor
            if ($user_role === 'HOD' && $sub['SupervisorID']) {
                $stmt_lec = $pdo->prepare("SELECT Email, Nama FROM Supervisor WHERE No_staf = ?");
                $stmt_lec->execute([$sub['SupervisorID']]);
                $lec_info = $stmt_lec->fetch();
                
                if ($lec_info) {
                    createNotification($pdo, $sub['SupervisorID'], "HOD left feedback on " . $sub['StudentName'] . "'s submission.");
                    sendSystemEmail(
                        $lec_info['Email'], 
                        $lec_info['Nama'], 
                        "HOD Left Feedback on Assigned Student", 
                        "Hello Dr./Mr./Mrs. " . $lec_info['Nama'] . ",\n\nThe HOD (" . $reviewer_name . ") has left feedback on a submission from your assigned student: " . $sub['StudentName'] . ".\n\nSubmission: " . $sub['Tajuk'] . "\nFeedback:\n\"" . $ulasan . "\"\n\nPlease log in to review."
                    );
                }
            }

            $pdo->commit();
            $_SESSION['success_msg'] = "Review submitted successfully and notifications sent.";
            
            // Redirect based on role
            if ($user_role === 'HOD') {
                header("Location: hod_reports.php");
            } else {
                header("Location: supervisor_students.php?student_id=" . urlencode($sub['No_matrik']));
            }
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch comments history
$comment_history_stmt = $pdo->prepare("
    SELECT c.*, 
           COALESCE(l.Nama, h.Nama) AS ReviewerName
    FROM Comments c
    LEFT JOIN Supervisor l ON c.Pengulas_ID = l.No_staf
    LEFT JOIN HOD h ON c.Pengulas_ID = h.No_staf
    WHERE c.ID_hantaran = ?
    ORDER BY c.Tarikh_Ulasan ASC
");
$comment_history_stmt->execute([$sub_id]);
$comments = $comment_history_stmt->fetchAll();
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);">Review Student Deliverable</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Audit student submissions and provide feedback/assessments</p>
    </div>
    
    <?php if ($user_role === 'HOD'): ?>
        <a href="hod_reports.php" class="btn btn-secondary">⬅ Back to Reports</a>
    <?php else: ?>
        <a href="supervisor_students.php?student_id=<?= urlencode($sub['No_matrik']) ?>" class="btn btn-secondary">⬅ Back to Student Details</a>
    <?php endif; ?>
</div>

<div class="grid-2">
    <!-- Left Pane: Submission Content & Files -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3>Submission Overview</h3>
            </div>
            <div class="card-body">
                <div class="detail-list" style="margin-bottom: 1.5rem;">
                    <div class="detail-item">
                        <span class="detail-label">Student Name:</span>
                        <span class="detail-value"><?= sanitize($sub['StudentName']) ?> (<?= sanitize($sub['No_matrik']) ?>)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Deliverable Type:</span>
                        <span class="detail-value" style="text-transform: capitalize; font-weight: 700; color: var(--primary);"><?= sanitize($sub['Jenis_Hantaran']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Title/Topic:</span>
                        <span class="detail-value"><?= sanitize($sub['Tajuk']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date Submitted:</span>
                        <span class="detail-value"><?= date('d M Y, h:i A', strtotime($sub['Tarikh_Hantar'])) ?></span>
                    </div>
                    <?php if ($sub['ID_tugasan']): ?>
                        <div class="detail-item">
                            <span class="detail-label">Linked Task:</span>
                            <span class="detail-value" style="color: var(--secondary);"><?= sanitize($sub['TaskJenis']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="background-color: var(--bg-light); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600;">Submitted Log / Details:</h4>
                    <p style="font-size: 0.875rem; white-space: pre-wrap; color: var(--text-main);"><?= $sub['Kandungan'] ? sanitize($sub['Kandungan']) : '<em>No descriptive log text entered.</em>' ?></p>
                </div>

                <?php if ($sub['File_Path']): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-sm); padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 1.5rem;">📄</span>
                            <div>
                                <span style="font-size: 0.85rem; font-weight: 600; color: #166534;"><?= sanitize($sub['File_Path']) ?></span>
                                <span style="display: block; font-size: 0.7rem; color: #15803d;">Student Deliverable Attachment</span>
                            </div>
                        </div>
                        <!-- Download Link -->
                        <a href="uploads/<?= urlencode($sub['File_Path']) ?>" class="btn btn-secondary" style="background-color: white; border: 1px solid #bbf7d0; color: #166534; font-size: 0.75rem; padding: 0.4rem 0.8rem;" download>⬇ Download File</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comments History -->
        <div class="card">
            <div class="card-header">
                <h3>Feedback History</h3>
            </div>
            <div class="card-body">
                <?php if (empty($comments)): ?>
                    <p style="text-align: center; color: var(--text-muted); font-size: 0.875rem;">No feedback registered yet.</p>
                <?php else: ?>
                    <div class="comment-list">
                        <?php foreach ($comments as $com): ?>
                            <div class="comment-card" style="border-left: 4px solid <?= $com['Peranan_Pengulas'] === 'HOD' ? 'var(--secondary)' : 'var(--primary)' ?>;">
                                <div class="comment-card-header">
                                    <span class="comment-author"><?= sanitize($com['ReviewerName']) ?> (<?= sanitize($com['Peranan_Pengulas']) ?>)</span>
                                    <span><?= date('d M Y, h:i A', strtotime($com['Tarikh_Ulasan'])) ?></span>
                                </div>
                                <div class="comment-text"><?= sanitize($com['Ulasan']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Pane: Review & Actions Panel -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3>Assessment and Endorsement Form</h3>
            </div>
            <div class="card-body">
                <form action="supervisor_review.php?id=<?= $sub_id ?>" method="POST">
                    <input type="hidden" name="submit_review" value="1">
                    
                    <div class="form-group">
                        <label for="Ulasan" class="form-label">Review / Feedback Comments</label>
                        <textarea name="Ulasan" id="Ulasan" class="form-input" rows="6" placeholder="Provide notes, suggestions, or corrections here..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Validation Decision</label>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.5rem;">
                            <!-- Only allow endorsement options if this is a task-linked submission or final report -->
                            <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                                <input type="radio" name="StatusAction" value="endorse" checked style="margin-top: 0.2rem;">
                                <div>
                                    <strong>Validate & Endorse progress (Pengesahan)</strong>
                                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Approves this milestone task and marks student progress as complete.</span>
                                </div>
                            </label>

                            <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                                <input type="radio" name="StatusAction" value="resubmit" style="margin-top: 0.2rem;">
                                <div>
                                    <strong>Request Resubmission (Hantar Semula)</strong>
                                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Rejects submission. Student will be notified to upload corrections.</span>
                                </div>
                            </label>

                            <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                                <input type="radio" name="StatusAction" value="feedback_only" style="margin-top: 0.2rem;">
                                <div>
                                    <strong>Leave Feedback Only</strong>
                                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Posts comments without changing current approval status.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">Submit Assessment Review</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
