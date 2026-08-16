<?php
// register.php
// Public Registration Page for Students and Supervisors

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'HOD') {
        header("Location: hod_dashboard.php");
    } elseif ($_SESSION['user_role'] === 'Supervisor') {
        header("Location: supervisor_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = trim($_POST['role'] ?? 'Student');
    $nama = trim($_POST['Nama'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $katalaluan = trim($_POST['Katalaluan'] ?? '');

    if ($role === 'Student') {
        $no_matrik = trim($_POST['No_matrik'] ?? '');
        $semester = intval($_POST['Semester'] ?? 8);

        if (empty($no_matrik) || empty($nama) || empty($email) || empty($katalaluan)) {
            $error = "All student registration fields are required.";
        } else {
            try {
                // Check duplicate
                $check = $pdo->prepare("SELECT COUNT(*) FROM Student WHERE No_matrik = ?");
                $check->execute([$no_matrik]);
                $check_lec = $pdo->prepare("SELECT COUNT(*) FROM Supervisor WHERE No_staf = ?");
                $check_lec->execute([$no_matrik]);

                if ($check->fetchColumn() > 0 || $check_lec->fetchColumn() > 0 || $no_matrik === 'HOD001') {
                    $error = "Matric / Staff ID '$no_matrik' is already in use.";
                } else {
                    $hash = password_hash($katalaluan, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO Student (No_matrik, Nama, Katalaluan, Semester, Email) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$no_matrik, $nama, $hash, $semester, $email]);

                    $_SESSION['success_msg'] = "Registration successful! Please log in with your credentials.";
                    header("Location: login.php");
                    exit();
                }
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    } else {
        // Supervisor Registration
        $no_staf = trim($_POST['No_staf'] ?? '');
        $jawatan = trim($_POST['Jawatan'] ?? '');

        if (empty($no_staf) || empty($nama) || empty($jawatan) || empty($email) || empty($katalaluan)) {
            $error = "All supervisor registration fields are required.";
        } else {
            try {
                // Check duplicate
                $check = $pdo->prepare("SELECT COUNT(*) FROM Supervisor WHERE No_staf = ?");
                $check->execute([$no_staf]);
                $check_stu = $pdo->prepare("SELECT COUNT(*) FROM Student WHERE No_matrik = ?");
                $check_stu->execute([$no_staf]);

                if ($check->fetchColumn() > 0 || $check_stu->fetchColumn() > 0 || $no_staf === 'HOD001') {
                    $error = "Matric / Staff ID '$no_staf' is already in use.";
                } else {
                    $hash = password_hash($katalaluan, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO Supervisor (No_staf, Nama, Katalaluan, Jawatan, Email) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$no_staf, $nama, $hash, $jawatan, $email]);

                    $_SESSION['success_msg'] = "Registration successful! Please log in with your credentials.";
                    header("Location: login.php");
                    exit();
                }
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('register_page_title') ?> — Oduduwa University</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Public Header -->
    <header class="portal-header">
        <a href="index.php" class="portal-logo" style="text-decoration: none;">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>FYP Portal</span>
        </a>
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <!-- Language Selector Dropdown -->
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-globe" style="color: var(--text-muted); font-size: 0.9rem;"></i>
                <form method="GET" action="" style="margin: 0; display: flex; align-items: center;">
                    <select name="lang" onchange="this.form.submit()" class="form-input" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; border-radius: var(--radius-sm); border: 1px solid var(--border); cursor: pointer; width: auto;">
                        <option value="en" <?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'selected' : '' ?>>EN</option>
                        <option value="ms" <?= ($_SESSION['lang'] ?? 'en') === 'ms' ? 'selected' : '' ?>>MS</option>
                    </select>
                </form>
            </div>
            <a href="login.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><?= __('login_button') ?></a>
        </div>
    </header>

    <div class="register-body" style="padding: 3rem 1rem;">
        <div class="register-card" style="max-width: 550px; margin: 0 auto; background: white; padding: 3rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border);">
            <div class="register-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.75rem; color: var(--bg-dark); font-weight: 800;"><?= __('register_page_title') ?></h2>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.5rem;"><?= __('login_subtitle') ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= sanitize($error) ?></div>
            <?php endif; ?>

            <!-- Role Switch Tabs -->
            <div class="role-selector">
                <div class="role-option active" id="tabStudent" onclick="switchRole('Student')">
                    <i class="fa-solid fa-user-graduate"></i> <?= __('student') ?>
                </div>
                <div class="role-option" id="tabSupervisor" onclick="switchRole('Supervisor')">
                    <i class="fa-solid fa-chalkboard-user"></i> <?= __('supervisor_role') ?>
                </div>
            </div>

            <form action="register.php" method="POST" autocomplete="off" id="regForm">
                <input type="hidden" name="role" id="roleInput" value="Student">

                <!-- Dynamic Student Fields -->
                <div id="studentFields">
                    <div class="form-group">
                        <label for="No_matrik" class="form-label"><?= __('matric_no') ?></label>
                        <input type="text" name="No_matrik" id="No_matrik" class="form-input" placeholder="e.g. CSC/2022/001">
                    </div>
                    <div class="form-group">
                        <label for="Semester" class="form-label"><?= __('semester') ?></label>
                        <input type="number" name="Semester" id="Semester" class="form-input" value="8" min="1" max="12">
                    </div>
                </div>

                <!-- Dynamic Supervisor Fields -->
                <div id="supervisorFields" style="display: none;">
                    <div class="form-group">
                        <label for="No_staf" class="form-label"><?= __('staff_no') ?></label>
                        <input type="text" name="No_staf" id="No_staf" class="form-input" placeholder="e.g. Lec001">
                    </div>
                    <div class="form-group">
                        <label for="Jawatan" class="form-label"><?= __('designation') ?></label>
                        <input type="text" name="Jawatan" id="Jawatan" class="form-input" placeholder="e.g. Senior Lecturer">
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="form-group">
                    <label for="Nama" class="form-label"><?= __('full_name') ?></label>
                    <input type="text" name="Nama" id="Nama" class="form-input" placeholder="e.g. Adekunle Tobi" required>
                </div>

                <div class="form-group">
                    <label for="Email" class="form-label"><?= __('email') ?></label>
                    <input type="email" name="Email" id="Email" class="form-input" placeholder="e.g. user@oduduwa.edu.ng" required>
                </div>

                <div class="form-group">
                    <label for="Katalaluan" class="form-label"><?= __('password') ?></label>
                    <input type="password" name="Katalaluan" id="Katalaluan" class="form-input" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;"><i class="fa-solid fa-user-plus"></i> <?= __('register_button') ?></button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.85rem;">
                <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;"><?= __('already_have_account') ?></a>
            </div>
        </div>
    </div>

    <script>
        function switchRole(role) {
            document.getElementById('roleInput').value = role;
            
            const tabStudent = document.getElementById('tabStudent');
            const tabSupervisor = document.getElementById('tabSupervisor');
            const studentFields = document.getElementById('studentFields');
            const supervisorFields = document.getElementById('supervisorFields');
            
            const matricInput = document.getElementById('No_matrik');
            const semInput = document.getElementById('Semester');
            const staffInput = document.getElementById('No_staf');
            const jawInput = document.getElementById('Jawatan');

            if (role === 'Student') {
                tabStudent.classList.add('active');
                tabSupervisor.classList.remove('active');
                studentFields.style.display = 'block';
                supervisorFields.style.display = 'none';
                
                matricInput.setAttribute('required', 'true');
                semInput.setAttribute('required', 'true');
                staffInput.removeAttribute('required');
                jawInput.removeAttribute('required');
            } else {
                tabStudent.classList.remove('active');
                tabSupervisor.classList.add('active');
                studentFields.style.display = 'none';
                supervisorFields.style.display = 'block';
                
                matricInput.removeAttribute('required');
                semInput.removeAttribute('required');
                staffInput.setAttribute('required', 'true');
                jawInput.setAttribute('required', 'true');
            }
        }
        
        // Initialize validation states
        window.addEventListener('DOMContentLoaded', () => {
            switchRole('Student');
        });
    </script>
</body>
</html>
