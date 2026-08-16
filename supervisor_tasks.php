<?php
// supervisor_tasks.php
// Supervisor Task Assignment Form (Screen 4)

require_once __DIR__ . '/includes/functions.php';
requireRole('Supervisor');
require_once __DIR__ . '/includes/header.php';

$no_staf = $_SESSION['user_id'];
$target_student_id = $_GET['student_id'] ?? '';

// Fetch all assigned students to populate dropdown or validate target
$students_stmt = $pdo->prepare("
    SELECT s.No_matrik, s.Nama AS StudentName, p.ID_projek 
    FROM Student s
    JOIN Project p ON s.No_matrik = p.No_matrik
    WHERE p.No_staf = ?
    ORDER BY s.Nama ASC
");
$students_stmt->execute([$no_staf]);
$assigned_students = $students_stmt->fetchAll();

// Validate target student if supplied
$target_student = null;
if (!empty($target_student_id)) {
    foreach ($assigned_students as $stu) {
        if ($stu['No_matrik'] === $target_student_id) {
            $target_student = $stu;
            break;
        }
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['No_matrik'] ?? '');
    $jenis = trim($_POST['Jenis'] ?? '');
    $deadline = trim($_POST['Deadline'] ?? '');

    // Get specific student project details
    $project = null;
    foreach ($assigned_students as $stu) {
        if ($stu['No_matrik'] === $student_id) {
            $project = $stu;
            break;
        }
    }

    if (empty($student_id) || empty($jenis) || empty($deadline)) {
        $_SESSION['error_msg'] = "All task fields are required.";
    } elseif (!$project) {
        $_SESSION['error_msg'] = "Invalid student selection. The student must be assigned to you.";
    } else {
        try {
            // Insert task
            $stmt = $pdo->prepare("
                INSERT INTO Task (Jenis, Ulasan, Pengesahan, Tarikh, Deadline, No_matrik, No_staf, ID_projek) 
                VALUES (?, NULL, 'Belum Disahkan', CURDATE(), ?, ?, ?, ?)
            ");
            $stmt->execute([$jenis, $deadline, $student_id, $no_staf, $project['ID_projek']]);
            
            // Fetch student email for notification
            $stmt_email = $pdo->prepare("SELECT Email, Nama FROM Student WHERE No_matrik = ?");
            $stmt_email->execute([$student_id]);
            $student_info = $stmt_email->fetch();

            // Trigger Notifications (Dashboard and Email)
            createNotification($pdo, $student_id, "Supervisor has assigned a new task: '$jenis'. Deadline: $deadline.");
            sendSystemEmail(
                $student_info['Email'], 
                $student_info['Nama'], 
                "New Task Assigned - FYP", 
                "Hello " . $student_info['Nama'] . ",\n\nYour Supervisor has assigned a new task to your FYP checklist:\n\nTask: $jenis\nDeadline: $deadline\n\nPlease log in to review the details and submit your weekly progress updates."
            );

            $_SESSION['success_msg'] = "Task successfully assigned to " . $student_info['Nama'] . ".";
            header("Location: supervisor_students.php?student_id=" . urlencode($student_id));
            exit();

        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('add_task') ?></h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Create a custom project deliverable and set strict deadlines</p>
    </div>
    <a href="supervisor_students.php?student_id=<?= urlencode($target_student_id) ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('cancel') ?></a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto 2rem auto;">
    <div class="card-header">
        <h3>Task Assignment Details</h3>
    </div>
    <div class="card-body">
        <form action="supervisor_tasks.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="No_matrik" class="form-label"><?= __('student') ?></label>
                <?php if ($target_student): ?>
                    <input type="text" class="form-input" style="background-color: #f1f5f9;" readonly value="<?= sanitize($target_student['StudentName']) ?> (<?= sanitize($target_student['No_matrik']) ?>)">
                    <input type="hidden" name="No_matrik" value="<?= sanitize($target_student['No_matrik']) ?>">
                <?php else: ?>
                    <select name="No_matrik" id="No_matrik" class="form-input" required>
                        <option value=""><?= __('choose_student') ?></option>
                        <?php foreach ($assigned_students as $stu): ?>
                            <option value="<?= sanitize($stu['No_matrik']) ?>"><?= sanitize($stu['StudentName']) ?> (<?= sanitize($stu['No_matrik']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="Jenis" class="form-label"><?= __('task_goal') ?></label>
                <input type="text" name="Jenis" id="Jenis" class="form-input" placeholder="e.g. Chapter 3 Methodology Write-up" required>
            </div>

            <div class="form-group">
                <label for="Deadline" class="form-label"><?= __('deadline') ?></label>
                <input type="date" name="Deadline" id="Deadline" class="form-input" required min="<?= date('Y-m-d') ?>">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <?= __('add_task') ?></button>
                <a href="supervisor_students.php?student_id=<?= urlencode($target_student_id) ?>" class="btn btn-secondary"><?= __('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
