<?php
// student_project.php
// Project Title Registration Page (Screen 3)

require_once __DIR__ . '/includes/functions.php';
requireRole('Student');
require_once __DIR__ . '/includes/header.php';

$no_matrik = $_SESSION['user_id'];

// Fetch project/assignment details
$project_stmt = $pdo->prepare("
    SELECT p.*, l.Nama AS SupervisorName 
    FROM Project p
    LEFT JOIN Supervisor l ON p.No_staf = l.No_staf
    WHERE p.No_matrik = ?
");
$project_stmt->execute([$no_matrik]);
$project = $project_stmt->fetch();

// Check if assigned supervisor exists
if (!$project || !$project['No_staf']) {
    echo '
    <div class="card" style="max-width: 600px; margin: 2rem auto;">
        <div class="card-header" style="background-color: #fee2e2; border-bottom-color: #fca5a5;">
            <h3 style="color: #991b1b;"><i class="fa-solid fa-triangle-exclamation"></i> Supervision Allocation Required</h3>
        </div>
        <div class="card-body" style="text-align: center;">
            <p style="margin-bottom: 1.5rem; color: #7f1d1d;">The Head of Department (HOD) must assign a Supervisor to your profile before you can register your project title.</p>
            <a href="student_dashboard.php" class="btn btn-secondary">' . __('back_to_dashboard') . '</a>
        </div>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Handle project registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tajuk_projek = trim($_POST['Tajuk_Projek'] ?? '');

    if (empty($tajuk_projek)) {
        $_SESSION['error_msg'] = "Project Title cannot be empty.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE Project SET Tajuk_Projek = ? WHERE No_matrik = ?");
            $stmt->execute([$tajuk_projek, $no_matrik]);

            $_SESSION['success_msg'] = "Project Title registered successfully.";
            
            // Notify Supervisor of new project registration
            $notif_msg = $_SESSION['user_name'] . " registered project title: '$tajuk_projek'";
            createNotification($pdo, $project['No_staf'], $notif_msg);

            // Fetch Supervisor Email
            $stmt_lec = $pdo->prepare("SELECT Email, Nama FROM Supervisor WHERE No_staf = ?");
            $stmt_lec->execute([$project['No_staf']]);
            $lec_info = $stmt_lec->fetch();

            if ($lec_info) {
                sendSystemEmail(
                    $lec_info['Email'], 
                    $lec_info['Nama'], 
                    "Project Title Registered - FYP", 
                    "Hello Dr./Mr./Mrs. " . $lec_info['Nama'] . ",\n\nYour assigned student, " . $_SESSION['user_name'] . " (" . $no_matrik . "), has registered their project title:\n\nTitle: " . $tajuk_projek . "\n\nPlease log in to review and start assigning tasks."
                );
            }

            header("Location: student_dashboard.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('reg_proj_title') ?></h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;"><?= __('project_title_desc') ?></p>
    </div>
    <a href="student_dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('back_to_dashboard') ?></a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto 2rem auto;">
    <div class="card-header">
        <h3><?= __('project_title_form') ?></h3>
    </div>
    <div class="card-body">
        <div style="background-color: var(--bg-light); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.5rem;">
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block;"><?= __('assigned_supervisor') ?></span>
            <span style="font-size: 1.05rem; font-weight: 700; color: var(--secondary);"><?= sanitize($project['SupervisorName']) ?></span>
        </div>

        <form action="student_project.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="Tajuk_Projek" class="form-label"><?= __('project_title') ?></label>
                <textarea name="Tajuk_Projek" id="Tajuk_Projek" class="form-input" rows="4" placeholder="e.g. Design and Implementation of an Automated Patient Monitoring System using IoT" required><?= $project['Tajuk_Projek'] ? sanitize($project['Tajuk_Projek']) : '' ?></textarea>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> <?= __('reg_proj_title') ?></button>
                <a href="student_dashboard.php" class="btn btn-secondary"><?= __('back') ?></a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
