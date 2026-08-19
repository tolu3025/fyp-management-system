<?php
// register.php
// Clean Light Theme registration page.
// Features a full-screen background cover, centered action button, and academic forms.

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

$register_error = '';
$register_success = '';

// Handle Registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = trim($_POST['role'] ?? 'Student');
    $nama = trim($_POST['Nama'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $katalaluan = trim($_POST['Katalaluan'] ?? '');

    if ($role === 'Student') {
        $no_matrik = trim($_POST['No_matrik'] ?? '');
        $semester = intval($_POST['Semester'] ?? 8);

        if (empty($no_matrik) || empty($nama) || empty($email) || empty($katalaluan)) {
            $register_error = "All student registration fields are required.";
        } else {
            try {
                // Check duplicate
                $check = $pdo->prepare("SELECT COUNT(*) FROM Student WHERE No_matrik = ?");
                $check->execute([$no_matrik]);
                $check_lec = $pdo->prepare("SELECT COUNT(*) FROM Supervisor WHERE No_staf = ?");
                $check_lec->execute([$no_matrik]);

                if ($check->fetchColumn() > 0 || $check_lec->fetchColumn() > 0 || $no_matrik === 'HOD001') {
                    $register_error = "Username / Matric Number '$no_matrik' is already in use.";
                } else {
                    $hash = password_hash($katalaluan, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO Student (No_matrik, Nama, Katalaluan, Semester, Email) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$no_matrik, $nama, $hash, $semester, $email]);

                    $register_success = "Registration successful! You can now log in.";
                }
            } catch (PDOException $e) {
                $register_error = "Database Error: " . $e->getMessage();
            }
        }
    } else {
        // Supervisor Registration
        $no_staf = trim($_POST['No_staf'] ?? '');
        $jawatan = trim($_POST['Jawatan'] ?? '');

        if (empty($no_staf) || empty($nama) || empty($jawatan) || empty($email) || empty($katalaluan)) {
            $register_error = "All supervisor registration fields are required.";
        } else {
            try {
                // Check duplicate
                $check = $pdo->prepare("SELECT COUNT(*) FROM Supervisor WHERE No_staf = ?");
                $check->execute([$no_staf]);
                $check_stu = $pdo->prepare("SELECT COUNT(*) FROM Student WHERE No_matrik = ?");
                $check_stu->execute([$no_staf]);

                if ($check->fetchColumn() > 0 || $check_stu->fetchColumn() > 0 || $no_staf === 'HOD001') {
                    $register_error = "Username / Matric Number '$no_staf' is already in use.";
                } else {
                    $hash = password_hash($katalaluan, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO Supervisor (No_staf, Nama, Katalaluan, Jawatan, Email) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$no_staf, $nama, $hash, $jawatan, $email]);

                    $register_success = "Registration successful! You can now log in.";
                }
            } catch (PDOException $e) {
                $register_error = "Database Error: " . $e->getMessage();
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
    <title><?= __('system_title') ?></title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="portal-theme register-cover-page">
    <!-- Transparent absolute home link -->
    <div style="position: absolute; top: 1.5rem; right: 2rem; z-index: 10;">
        <a href="index.php" style="color: #ffffff; text-decoration: none; font-weight: 700; font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0,0,0,0.5);"><i class="fa-solid fa-house"></i> Home</a>
    </div>

    <div class="register-cover-container">
        <!-- Success Alert (Centered card layout) -->
        <?php if (!empty($register_success)): ?>
            <div class="auth-card" style="text-align: center; max-width: 440px;">
                <div style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"><i class="fa-solid fa-circle-check"></i></div>
                <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 800;">Account Created Successfully!</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Your registration has been completed.</p>
                <a href="login.php" class="btn btn-portal-primary" style="padding: 0.75rem 2rem;"><i class="fa-solid fa-right-to-bracket"></i> Proceed to Login</a>
            </div>
        <?php else: ?>
            <!-- 1. Centered welcome buttons (No card container initially) -->
            <div id="registerActions">
                <button type="button" id="startRegisterBtn" class="btn btn-register-start"><i class="fa-solid fa-user-plus"></i> Register Account</button>
                <div style="margin-top: 1.5rem;">
                    <a href="login.php" class="register-login-link">Already have an account? Log In</a>
                </div>
            </div>

            <!-- 2. Dynamic Registration Form (loads inside card overlay once clicked) -->
            <div id="registrationFormFields" style="display: none; width: 100%;">
                <div class="auth-card" style="text-align: left; max-width: 460px; margin: 0 auto;">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--primary); margin-bottom: 0.25rem;">Create Account</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Select role and input credentials</p>

                    <?php if (!empty($register_error)): ?>
                        <div class="alert alert-danger" style="text-align: left; margin-bottom: 1.5rem;"><i class="fa-solid fa-triangle-exclamation"></i> <?= sanitize($register_error) ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" autocomplete="off" id="regForm">
                        <input type="hidden" name="role" id="roleInput" value="">

                        <!-- Pre-screening cards picker -->
                        <div class="role-picker-grid" id="rolePicker">
                            <div class="role-picker-card" data-role="Student" onclick="selectRole('Student')" style="margin: 0;">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span>Student</span>
                            </div>
                            <div class="role-picker-card" data-role="Supervisor" onclick="selectRole('Supervisor')" style="margin: 0;">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span>Supervisor</span>
                            </div>
                        </div>

                        <!-- Dynamic fields container -->
                        <div class="registration-slide-fields" id="regDynamicFields" style="padding: 0; border: none; box-shadow: none; margin-bottom: 0;">
                            <!-- Student details -->
                            <div id="studentFieldsBlock" style="display: none;">
                                <div class="form-group">
                                    <label for="No_matrik" class="form-label"><?= __('matric_no') ?></label>
                                    <input type="text" name="No_matrik" id="No_matrik" class="form-input" placeholder="CSC/2022/001">
                                </div>
                                <div class="form-group">
                                    <label for="Semester" class="form-label"><?= __('semester') ?></label>
                                    <input type="number" name="Semester" id="Semester" class="form-input" value="8" min="1" max="12">
                                </div>
                            </div>

                            <!-- Supervisor details -->
                            <div id="supervisorFieldsBlock" style="display: none;">
                                <div class="form-group">
                                    <label for="No_staf" class="form-label">Lecturer Username</label>
                                    <input type="text" name="No_staf" id="No_staf" class="form-input" placeholder="e.g. dralabi">
                                </div>
                                <div class="form-group">
                                    <label for="Jawatan" class="form-label"><?= __('designation') ?></label>
                                    <input type="text" name="Jawatan" id="Jawatan" class="form-input" placeholder="e.g. Senior Lecturer">
                                </div>
                            </div>

                            <!-- Common inputs -->
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

                            <button type="submit" class="btn btn-portal-primary btn-block" style="margin-top: 1.5rem; padding: 0.75rem;"><i class="fa-solid fa-user-plus"></i> Submit Registration</button>
                            
                            <div style="margin-top: 1.5rem; text-align: center;">
                                <a href="register.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;"><i class="fa-solid fa-chevron-left"></i> Cancel and Go Back</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const startRegBtn = document.getElementById('startRegisterBtn');
        if (startRegBtn) {
            startRegBtn.addEventListener('click', () => {
                document.getElementById('registerActions').style.display = 'none';
                document.getElementById('registrationFormFields').style.display = 'block';
            });
        }

        // Handles pre-screening role card selection
        function selectRole(role) {
            document.getElementById('roleInput').value = role;
            
            // Toggle active card styles
            document.querySelectorAll('.role-picker-card').forEach(card => {
                if (card.getAttribute('data-role') === role) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });

            // Slide down inputs fields
            document.getElementById('regDynamicFields').style.display = 'block';
            
            const studBlock = document.getElementById('studentFieldsBlock');
            const supBlock = document.getElementById('supervisorFieldsBlock');
            const matric = document.getElementById('No_matrik');
            const sem = document.getElementById('Semester');
            const staff = document.getElementById('No_staf');
            const jawatan = document.getElementById('Jawatan');

            if (role === 'Student') {
                studBlock.style.display = 'block';
                supBlock.style.display = 'none';
                matric.setAttribute('required', 'true');
                sem.setAttribute('required', 'true');
                staff.removeAttribute('required');
                jawatan.removeAttribute('required');
            } else {
                studBlock.style.display = 'none';
                supBlock.style.display = 'block';
                matric.removeAttribute('required');
                sem.removeAttribute('required');
                staff.setAttribute('required', 'true');
                jawatan.setAttribute('required', 'true');
            }
        }

        // Initialize display checks if error exists
        <?php if (!empty($register_error)): ?>
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('registerActions').style.display = 'none';
            document.getElementById('registrationFormFields').style.display = 'block';
        });
        <?php endif; ?>
    </script>
</body>
</html>
