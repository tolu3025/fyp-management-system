<?php
// hod_registration.php
// Registration and supervisor allocation management for HOD

require_once __DIR__ . '/includes/functions.php';
requireRole('HOD');
require_once __DIR__ . '/includes/header.php';

// Handle Student Registration
if (isset($_POST['register_student'])) {
    $no_matrik = trim($_POST['No_matrik'] ?? '');
    $nama = trim($_POST['Nama'] ?? '');
    $katalaluan = trim($_POST['Katalaluan'] ?? '');
    $semester = intval($_POST['Semester'] ?? 8);
    $email = trim($_POST['Email'] ?? '');

    if (empty($no_matrik) || empty($nama) || empty($katalaluan) || empty($email)) {
        $_SESSION['error_msg'] = "All student registration fields are required.";
    } else {
        try {
            // Check if already exists in Student, Supervisor, or HOD
            $check = $pdo->prepare("SELECT COUNT(*) FROM Student WHERE No_matrik = ?");
            $check->execute([$no_matrik]);
            
            $check_lec = $pdo->prepare("SELECT COUNT(*) FROM Supervisor WHERE No_staf = ?");
            $check_lec->execute([$no_matrik]);
            
            if ($check->fetchColumn() > 0 || $check_lec->fetchColumn() > 0 || $no_matrik === 'HOD001') {
                $_SESSION['error_msg'] = "Account ID '$no_matrik' is already in use.";
            } else {
                $hash = password_hash($katalaluan, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Student (No_matrik, Nama, Katalaluan, Semester, Email) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$no_matrik, $nama, $hash, $semester, $email]);
                
                $_SESSION['success_msg'] = "Student '$nama' successfully registered.";
                header("Location: hod_registration.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
        }
    }
}

// Handle Supervisor Registration
if (isset($_POST['register_supervisor'])) {
    $no_staf = trim($_POST['No_staf'] ?? '');
    $nama = trim($_POST['Nama'] ?? '');
    $katalaluan = trim($_POST['Katalaluan'] ?? '');
    $jawatan = trim($_POST['Jawatan'] ?? '');
    $email = trim($_POST['Email'] ?? '');

    if (empty($no_staf) || empty($nama) || empty($katalaluan) || empty($jawatan) || empty($email)) {
        $_SESSION['error_msg'] = "All supervisor registration fields are required.";
    } else {
        try {
            $check = $pdo->prepare("SELECT COUNT(*) FROM Supervisor WHERE No_staf = ?");
            $check->execute([$no_staf]);
            
            $check_stu = $pdo->prepare("SELECT COUNT(*) FROM Student WHERE No_matrik = ?");
            $check_stu->execute([$no_staf]);

            if ($check->fetchColumn() > 0 || $check_stu->fetchColumn() > 0 || $no_staf === 'HOD001') {
                $_SESSION['error_msg'] = "Account ID '$no_staf' is already in use.";
            } else {
                $hash = password_hash($katalaluan, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Supervisor (No_staf, Nama, Katalaluan, Jawatan, Email) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$no_staf, $nama, $hash, $jawatan, $email]);
                
                $_SESSION['success_msg'] = "Supervisor '$nama' successfully registered.";
                header("Location: hod_registration.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
        }
    }
}

// Handle Allocation/Assignment
if (isset($_POST['assign_supervisor'])) {
    $no_matrik = trim($_POST['No_matrik'] ?? '');
    $no_staf = trim($_POST['No_staf'] ?? '');

    if (empty($no_matrik) || empty($no_staf)) {
        $_SESSION['error_msg'] = "Please select both a student and a supervisor.";
    } else {
        try {
            // Check if student already has a project row
            $check = $pdo->prepare("SELECT * FROM Project WHERE No_matrik = ?");
            $check->execute([$no_matrik]);
            $existing_project = $check->fetch();

            if ($existing_project) {
                // Update supervisor
                $stmt = $pdo->prepare("UPDATE Project SET No_staf = ? WHERE No_matrik = ?");
                $stmt->execute([$no_staf, $no_matrik]);
            } else {
                // Insert new allocation
                $stmt = $pdo->prepare("INSERT INTO Project (No_matrik, No_staf, Tajuk_Projek) VALUES (?, ?, NULL)");
                $stmt->execute([$no_matrik, $no_staf]);
            }

            // Retrieve supervisor name and student name to send email alert & dashboard notification
            $stmt = $pdo->prepare("SELECT Nama, Email FROM Student WHERE No_matrik = ?");
            $stmt->execute([$no_matrik]);
            $student = $stmt->fetch();

            $stmt = $pdo->prepare("SELECT Nama, Email FROM Supervisor WHERE No_staf = ?");
            $stmt->execute([$no_staf]);
            $supervisor = $stmt->fetch();

            // Notify Student
            createNotification($pdo, $no_matrik, "HOD has assigned " . $supervisor['Nama'] . " as your FYP Supervisor.");
            sendSystemEmail(
                $student['Email'], 
                $student['Nama'], 
                "FYP Supervisor Assigned", 
                "Hello " . $student['Nama'] . ",\n\nThe HOD has assigned " . $supervisor['Nama'] . " as your FYP Supervisor.\n\nPlease log into the system to register your project title after discussing with them."
            );

            // Notify Supervisor
            createNotification($pdo, $no_staf, "HOD has assigned a new student to you: " . $student['Nama'] . ".");
            sendSystemEmail(
                $supervisor['Email'], 
                $supervisor['Nama'], 
                "New Student Assigned", 
                "Hello Dr./Mr./Mrs. " . $supervisor['Nama'] . ",\n\nThe HOD has assigned a new student, " . $student['Nama'] . " (" . $no_matrik . "), to your supervision load.\n\nYou can log in to view their dashboard profile."
            );

            $_SESSION['success_msg'] = "Assigned student successfully to " . $supervisor['Nama'] . ".";
            header("Location: hod_registration.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch lists
$supervisors = $pdo->query("SELECT * FROM Supervisor ORDER BY Nama ASC")->fetchAll();
$students = $pdo->query("SELECT * FROM Student ORDER BY Nama ASC")->fetchAll();

// Fetch detailed supervision list
$supervision_list = $pdo->query("
    SELECT s.No_matrik, s.Nama AS StudentName, s.Email AS StudentEmail,
           l.Nama AS SupervisorName, p.Tajuk_Projek 
    FROM Student s
    LEFT JOIN Project p ON s.No_matrik = p.No_matrik
    LEFT JOIN Supervisor l ON p.No_staf = l.No_staf
    ORDER BY l.Nama ASC, s.Nama ASC
")->fetchAll();
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);"><?= __('user_mgmt') ?></h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;"><?= __('user_mgmt_desc') ?></p>
    </div>
    <a href="hod_dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?= __('back_to_dashboard') ?></a>
</div>

<div class="grid-2" style="margin-bottom: 2rem;">
    <!-- Add Student Card -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('reg_new_student') ?></h3>
        </div>
        <div class="card-body">
            <form action="hod_registration.php" method="POST" autocomplete="off">
                <input type="hidden" name="register_student" value="1">
                
                <div class="form-group">
                    <label for="No_matrik" class="form-label"><?= __('matric_no') ?></label>
                    <input type="text" name="No_matrik" id="No_matrik" class="form-input" placeholder="CSC/2022/001" required>
                </div>

                <div class="form-group">
                    <label for="Nama_std" class="form-label"><?= __('full_name') ?></label>
                    <input type="text" name="Nama" id="Nama_std" class="form-input" placeholder="Adekunle Tobi" required>
                </div>

                <div class="grid-2" style="gap: 1rem; margin-bottom: 0;">
                    <div class="form-group">
                        <label for="Semester" class="form-label"><?= __('semester') ?></label>
                        <input type="number" name="Semester" id="Semester" class="form-input" value="8" min="1" max="12" required>
                    </div>
                    <div class="form-group">
                        <label for="Katalaluan_std" class="form-label"><?= __('password') ?></label>
                        <input type="password" name="Katalaluan" id="Katalaluan_std" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Email_std" class="form-label"><?= __('email') ?></label>
                    <input type="email" name="Email" id="Email_std" class="form-input" placeholder="student@student.oduduwa.edu.ng" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-user-plus"></i> <?= __('register_button') ?></button>
            </form>
        </div>
    </div>

    <!-- Add Supervisor Card -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('reg_new_supervisor') ?></h3>
        </div>
        <div class="card-body">
            <form action="hod_registration.php" method="POST" autocomplete="off">
                <input type="hidden" name="register_supervisor" value="1">

                <div class="form-group">
                    <label for="No_staf" class="form-label"><?= __('staff_no') ?></label>
                    <input type="text" name="No_staf" id="No_staf" class="form-input" placeholder="Lec003" required>
                </div>

                <div class="form-group">
                    <label for="Nama_sup" class="form-label"><?= __('full_name') ?></label>
                    <input type="text" name="Nama" id="Nama_sup" class="form-input" placeholder="Dr. Samuel Alabi" required>
                </div>

                <div class="grid-2" style="gap: 1rem; margin-bottom: 0;">
                    <div class="form-group">
                        <label for="Jawatan" class="form-label"><?= __('designation') ?></label>
                        <input type="text" name="Jawatan" id="Jawatan" class="form-input" placeholder="Senior Lecturer" required>
                    </div>
                    <div class="form-group">
                        <label for="Katalaluan_sup" class="form-label"><?= __('password') ?></label>
                        <input type="password" name="Katalaluan" id="Katalaluan_sup" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Email_sup" class="form-label"><?= __('email') ?></label>
                    <input type="email" name="Email" id="Email_sup" class="form-input" placeholder="lecturer@oduduwa.edu.ng" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-user-plus"></i> <?= __('register_button') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Allocation Form -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('assign_stud_sup') ?></h3>
        </div>
        <div class="card-body">
            <form action="hod_registration.php" method="POST">
                <input type="hidden" name="assign_supervisor" value="1">
                
                <div class="form-group">
                    <label for="alloc_student" class="form-label"><?= __('select_student') ?></label>
                    <select name="No_matrik" id="alloc_student" class="form-input" required>
                        <option value=""><?= __('choose_student') ?></option>
                        <?php foreach ($students as $stu): ?>
                            <option value="<?= sanitize($stu['No_matrik']) ?>"><?= sanitize($stu['Nama']) ?> (<?= sanitize($stu['No_matrik']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="alloc_supervisor" class="form-label"><?= __('select_supervisor') ?></label>
                    <select name="No_staf" id="alloc_supervisor" class="form-input" required>
                        <option value=""><?= __('choose_supervisor') ?></option>
                        <?php foreach ($supervisors as $sup): ?>
                            <option value="<?= sanitize($sup['No_staf']) ?>"><?= sanitize($sup['Nama']) ?> (<?= sanitize($sup['No_staf']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-secondary btn-block"><i class="fa-solid fa-link"></i> <?= __('link_assignment') ?></button>
            </form>
        </div>
    </div>

    <!-- Active Assignments Listing -->
    <div class="card">
        <div class="card-header">
            <h3><?= __('supervision_registry') ?></h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= __('student') ?></th>
                            <th><?= __('assigned_supervisor') ?></th>
                            <th><?= __('project_title') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($supervision_list)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">No students registered.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($supervision_list as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?= sanitize($row['StudentName']) ?></strong><br>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($row['No_matrik']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['SupervisorName']): ?>
                                            <span style="color: var(--secondary); font-weight: 500;"><?= sanitize($row['SupervisorName']) ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--danger); font-style: italic; font-size: 0.8rem;"><?= __('not_assigned') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['Tajuk_Projek']): ?>
                                            <span style="font-size: 0.85rem; font-weight: 500;"><?= sanitize($row['Tajuk_Projek']) ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-style: italic; font-size: 0.8rem;">Pending Title Registration</span>
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
