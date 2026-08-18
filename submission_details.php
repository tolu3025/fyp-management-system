<?php
// submission_details.php
// Unified Details and Remarks Page for Students, Supervisors, and HOD
// Students view comments/remarks timeline; Supervisors/HOD evaluate and post feedback.

require_once __DIR__ . '/includes/functions.php';
requireRole(['Student', 'Supervisor', 'HOD']);
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
    echo '<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Submission record not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Security: Check authorization
if ($user_role === 'Student' && $sub['No_matrik'] !== $user_id) {
    echo '<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Unauthorized. You can only view your own submissions.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}
if ($user_role === 'Supervisor' && $sub['SupervisorID'] !== $user_id) {
    echo '<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Unauthorized. You can only review submissions from your assigned students.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Handle Comment & Action Submission (Supervisors and HOD only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && $user_role !== 'Student') {
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
            $sub_status = 'Disemak'; // Checked/Approved
            $task_status = 'Disahkan'; // Endorsed

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
            
            // Send Email to Student
            sendSystemEmail(
                $sub['StudentEmail'], 
                $sub['StudentName'], 
                "Feedback Received on Submission", 
                "Hello " . $sub['StudentName'] . ",\n\nYour FYP " . $user_role . " (" . $reviewer_name . ") has posted feedback on your submission.\n\nSubmission: " . $sub['Tajuk'] . " (" . $sub['Jenis_Hantaran'] . ")\n\nRemarks/Feedback:\n\"" . $ulasan . "\"\n\nStatus Action: " . ($status_action === 'endorse' ? 'Approved & Endorsed' : ($status_action === 'resubmit' ? 'Resubmission Requested' : 'Feedback Left')) . "\n\nPlease log in to the portal to review full comments."
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
            $_SESSION['success_msg'] = "Feedback posted and status updated successfully.";
            
            // Redirect back to same page to refresh data
            header("Location: submission_details.php?id=" . $sub_id);
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

// Dynamic Initials generator function for reviewer avatars
function getReviewerInitials($name) {
    $cleaned = preg_replace('/^(Dr\.|Mr\.|Mrs\.|Ms\.)\s+/i', '', $name); // Strip titles
    $words = explode(" ", preg_replace('/[^a-zA-Z\s]/', '', $cleaned));
    $initials = "";
    $count = 0;
    foreach ($words as $w) {
        if (trim($w) !== "") {
            $initials .= strtoupper($w[0]);
            $count++;
            if ($count >= 2) break;
        }
    }
    return $initials ? $initials : "L";
}
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('view_details') ?></h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Department of Computer Science — Final Year Project Workspace</p>
    </div>
    
    <?php if ($user_role === 'HOD'): ?>
        <a href="hod_reports.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('back') ?></a>
    <?php elseif ($user_role === 'Supervisor'): ?>
        <a href="supervisor_students.php?student_id=<?= urlencode($sub['No_matrik']) ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('back') ?></a>
    <?php else: ?>
        <a href="student_submissions.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('back_to_dashboard') ?></a>
    <?php endif; ?>
</div>

<div class="grid-2">
    <!-- Left Pane: Submission Content & Files -->
    <div>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3><?= __('submission_details') ?></h3>
                <?php if ($sub['Status'] === 'Disemak'): ?>
                    <span class="badge badge-approved"><?= __('approved') ?></span>
                <?php elseif ($sub['Status'] === 'Hantar Semula'): ?>
                    <span class="badge badge-resubmit"><?= __('resubmit') ?></span>
                <?php else: ?>
                    <span class="badge badge-pending"><?= __('pending') ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="detail-list" style="margin-bottom: 1.5rem;">
                    <div class="detail-item">
                        <span class="detail-label"><?= __('student_name') ?>:</span>
                        <span class="detail-value"><?= sanitize($sub['StudentName']) ?> (<?= sanitize($sub['No_matrik']) ?>)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?= __('submission_type') ?>:</span>
                        <span class="detail-value" style="text-transform: capitalize; font-weight: 700; color: var(--primary);"><?= sanitize($sub['Jenis_Hantaran']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?= __('project_title') ?>:</span>
                        <span class="detail-value" style="text-align: right; max-width: 70%;"><?= sanitize($sub['Tajuk']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?= __('date_submitted') ?>:</span>
                        <span class="detail-value"><?= date('d M Y, h:i A', strtotime($sub['Tarikh_Hantar'])) ?></span>
                    </div>
                    <?php if ($sub['ID_tugasan']): ?>
                        <div class="detail-item">
                            <span class="detail-label">Linked Task Goal:</span>
                            <span class="detail-value" style="color: var(--primary-light);"><?= sanitize($sub['TaskJenis']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="background-color: var(--bg-light); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);"><?= __('content_text') ?>:</h4>
                    <p style="font-size: 0.9rem; white-space: pre-wrap; color: var(--text-main); font-weight: 500;"><?= $sub['Kandungan'] ? sanitize($sub['Kandungan']) : '<em>No descriptive log text was attached with this submission.</em>' ?></p>
                </div>

                <?php if ($sub['File_Path']): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; background-color: var(--success-light); border: 1px solid rgba(5, 150, 105, 0.15); border-radius: var(--radius-sm); padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
                            <i class="fa-solid fa-file-pdf" style="font-size: 2rem; color: var(--success);"></i>
                            <div style="min-width: 0;">
                                <span style="font-size: 0.85rem; font-weight: 700; color: var(--success); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= sanitize($sub['File_Path']) ?></span>
                                <span style="display: block; font-size: 0.72rem; color: var(--text-muted); font-weight: 600;"><?= __('file_attachment') ?></span>
                            </div>
                        </div>
                        <a href="uploads/<?= urlencode($sub['File_Path']) ?>" class="btn btn-secondary" style="background-color: white; border-color: rgba(5, 150, 105, 0.2); color: var(--success); font-size: 0.75rem; padding: 0.5rem 1rem; font-weight: 700;" download><i class="fa-solid fa-file-arrow-down"></i> <?= __('download_file') ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Remarks & Comments timeline -->
        <h3 class="comments-feed-title"><i class="fa-regular fa-comments" style="color: var(--primary);"></i> <?= __('comments_timeline') ?> (<?= count($comments) ?>)</h3>
        <div class="comment-list">
            <?php if (empty($comments)): ?>
                <div class="comment-card" style="text-align: center; color: var(--text-muted); padding: 2.5rem 1.5rem;">
                    <i class="fa-regular fa-comment-dots" style="font-size: 2rem; margin-bottom: 0.75rem; display: block; opacity: 0.4;"></i>
                    <p style="font-weight: 600; font-size: 0.9rem;"><?= __('no_comments') ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $com): 
                    $initials = getReviewerInitials($com['ReviewerName']);
                    $role_badge = ($com['Peranan_Pengulas'] === 'HOD') ? 'HOD' : 'Supervisor';
                ?>
                    <div class="comment-card">
                        <div class="comment-card-header">
                            <span class="comment-author <?= ($com['Peranan_Pengulas'] === 'HOD') ? 'reviewer-hod' : '' ?>">
                                <span class="comment-author-avatar"><?= $initials ?></span>
                                <span style="font-weight: 800;"><?= sanitize($com['ReviewerName']) ?></span>
                                <span style="font-size: 0.7rem; padding: 0.15rem 0.4rem; background: var(--bg-light); border-radius: 4px; font-weight: 700; color: var(--text-muted); margin-left: 0.5rem; text-transform: uppercase;"><?= $role_badge ?></span>
                            </span>
                            <span style="font-size: 0.72rem; font-weight: 600;"><i class="fa-regular fa-clock"></i> <?= date('d M Y, h:i A', strtotime($com['Tarikh_Ulasan'])) ?></span>
                        </div>
                        <div class="comment-text"><?= sanitize($com['Ulasan']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Pane: Conditional View (Resubmit warning or Supervisor form) -->
    <div>
        <?php if ($user_role === 'Student'): ?>
            <!-- Student Role Panel -->
            <?php if ($sub['Status'] === 'Hantar Semula'): ?>
                <div class="correction-banner">
                    <div class="correction-banner-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <h4 class="correction-banner-title"><?= __('corrections_requested') ?></h4>
                        <p class="correction-banner-desc"><?= __('corrections_requested_desc') ?></p>
                        <a href="student_submissions.php?task_id=<?= $sub['ID_tugasan'] ?>" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.5rem 1.25rem;"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Corrections Now</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card" style="border-top: 4px solid var(--primary-light);">
                    <div class="card-body" style="text-align: center; padding: 3rem 2rem;">
                        <i class="fa-solid fa-circle-info" style="font-size: 2.5rem; color: var(--primary-light); margin-bottom: 1rem;"></i>
                        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--bg-dark);">Submission State Info</h3>
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
                            This submission is currently marked as: <strong><?= sanitize($sub['Status']) ?></strong>.<br>
                            Once your supervisor evaluates the draft, their decision and detailed comments will appear in the timeline ledger.
                        </p>
                        <a href="student_submissions.php" class="btn btn-secondary btn-block"><i class="fa-solid fa-arrow-left"></i> Submissions Ledger</a>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- HOD / Supervisor Role Panel (Assessment Action Form) -->
            <div class="card" style="position: sticky; top: 95px;">
                <div class="card-header">
                    <h3><?= __('assessment_decision') ?></h3>
                </div>
                <div class="card-body">
                    <form action="submission_details.php?id=<?= $sub_id ?>" method="POST">
                        <input type="hidden" name="submit_review" value="1">
                        
                        <div class="form-group">
                            <label for="Ulasan" class="form-label"><?= __('feedback_comments') ?></label>
                            <textarea name="Ulasan" id="Ulasan" class="form-input" rows="6" placeholder="Provide notes, suggestions, or corrections here..." required></textarea>
                        </div>

                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label class="form-label">Validation Decision</label>
                            
                            <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 0.75rem;">
                                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-size: 0.875rem;">
                                    <input type="radio" name="StatusAction" value="endorse" checked style="margin-top: 0.25rem;">
                                    <div>
                                        <strong style="color: var(--success);"><i class="fa-solid fa-circle-check"></i> Validate & Approve progress (Pengesahan)</strong>
                                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Approves this milestone task and marks student progress as complete.</span>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-size: 0.875rem;">
                                    <input type="radio" name="StatusAction" value="resubmit" style="margin-top: 0.25rem;">
                                    <div>
                                        <strong style="color: var(--danger);"><i class="fa-solid fa-arrows-spin"></i> Request Resubmission (Hantar Semula)</strong>
                                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Rejects submission. Student will be flagged to upload corrections.</span>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-size: 0.875rem;">
                                    <input type="radio" name="StatusAction" value="feedback_only" style="margin-top: 0.25rem;">
                                    <div>
                                        <strong style="color: var(--text-main);"><i class="fa-solid fa-comment-dots"></i> Leave Comments Only</strong>
                                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Posts feedback comments on the timeline without updating status.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 2rem;"><i class="fa-solid fa-paper-plane"></i> Submit Feedback Remarks</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
