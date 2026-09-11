<?php
// register.php
// Clean Light Theme registration page.
// Features a full-screen background cover, centered multi-step wizard, and self-healing DB columns.

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

// Self-healing database check to add Phone and Specialization columns if missing
try {
    $pdo->query("SELECT Phone, Specialization FROM Student LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->exec("ALTER TABLE Student ADD COLUMN Phone VARCHAR(20) NULL");
        $pdo->exec("ALTER TABLE Student ADD COLUMN Specialization VARCHAR(100) NULL");
    } catch (PDOException $ex) {}
}

try {
    $pdo->query("SELECT Phone, Specialization FROM Supervisor LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->exec("ALTER TABLE Supervisor ADD COLUMN Phone VARCHAR(20) NULL");
        $pdo->exec("ALTER TABLE Supervisor ADD COLUMN Specialization VARCHAR(100) NULL");
    } catch (PDOException $ex) {}
}

$register_error = '';
$register_success = '';

// Handle Registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = trim($_POST['role'] ?? 'Student');
    $nama = trim($_POST['Nama'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $phone = trim($_POST['Phone'] ?? '');
    $specialization = trim($_POST['Specialization'] ?? '');
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
                    $stmt = $pdo->prepare("INSERT INTO Student (No_matrik, Nama, Katalaluan, Semester, Email, Phone, Specialization) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$no_matrik, $nama, $hash, $semester, $email, $phone, $specialization]);

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
                    $stmt = $pdo->prepare("INSERT INTO Supervisor (No_staf, Nama, Katalaluan, Jawatan, Email, Phone, Specialization) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$no_staf, $nama, $hash, $jawatan, $email, $phone, $specialization]);

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
    <title>Create Account | <?= __('system_title') ?></title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Registration Split-Panel Layout ── */
        body.reg-page {
            min-height: 100vh;
            display: flex;
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            margin: 0;
        }

        /* Left branding panel */
        .reg-brand-panel {
            width: 380px;
            flex-shrink: 0;
            background: linear-gradient(160deg, #0f1729 0%, #1e3a8a 55%, #1e40af 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .reg-brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .reg-brand-panel::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(180, 83, 9, 0.15);
            filter: blur(60px);
            pointer-events: none;
        }

        .reg-brand-top { position: relative; z-index: 1; }

        .reg-brand-crest {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #fbbf24;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(8px);
        }

        .reg-brand-univ {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 0.4rem;
        }

        .reg-brand-dept {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.25;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        .reg-brand-divider {
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #fbbf24, rgba(251,191,36,0.2));
            border-radius: 9999px;
            margin: 1rem 0 1.25rem;
        }

        .reg-brand-desc {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.7;
        }

        .reg-brand-features {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            margin-top: 2rem;
        }

        .reg-brand-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.75);
        }

        .reg-brand-feature i {
            color: #fbbf24;
            width: 16px;
            text-align: center;
        }

        .reg-brand-bottom {
            position: relative;
            z-index: 1;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.3);
            font-weight: 500;
        }

        /* Right form panel */
        .reg-form-panel {
            flex: 1;
            overflow-y: auto;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 3rem 2rem;
        }

        .reg-form-inner {
            width: 100%;
            max-width: 600px;
        }

        .reg-form-header {
            margin-bottom: 2rem;
        }

        .reg-form-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .reg-form-header p {
            font-size: 0.88rem;
            color: #64748b;
        }

        /* Role Tab Switcher */
        .reg-role-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 2rem;
            background: #f1f5f9;
            padding: 0.35rem;
            border-radius: 12px;
        }

        .reg-role-tab {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.75rem 1rem;
            border-radius: 9px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            color: #64748b;
            border: none;
            background: transparent;
            font-family: inherit;
        }

        .reg-role-tab:hover {
            color: #1e3a8a;
        }

        .reg-role-tab.active {
            background: white;
            color: #1e3a8a;
            box-shadow: 0 2px 8px rgba(15,23,42,0.08);
        }

        .reg-role-tab i { font-size: 1rem; }

        /* Field sections */
        .reg-section {
            margin-bottom: 1.75rem;
        }

        .reg-section-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .reg-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .reg-form-group {
            margin-bottom: 1rem;
        }

        .reg-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }

        .reg-input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            background: #fff;
            color: #0f172a;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .reg-input:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30,58,138,0.1);
        }

        .reg-input::placeholder { color: #94a3b8; }

        .reg-submit-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .reg-login-link {
            font-size: 0.85rem;
            color: #64748b;
        }

        .reg-login-link a {
            color: #1e3a8a;
            font-weight: 700;
            text-decoration: none;
        }

        .reg-login-link a:hover { text-decoration: underline; }

        .btn-reg-submit {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 0.85rem 2.5rem;
            font-size: 0.92rem;
            font-weight: 700;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(30,58,138,0.25);
        }

        .btn-reg-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30,58,138,0.35);
        }

        /* Home link top-right */
        .reg-home-link {
            position: fixed;
            top: 1.25rem;
            right: 1.5rem;
            z-index: 20;
            color: #64748b;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: white;
            padding: 0.5rem 0.9rem;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.2s ease;
        }

        .reg-home-link:hover {
            color: #1e3a8a;
            border-color: #1e3a8a;
        }

        /* Role-specific panels */
        .role-panel { display: none; }
        .role-panel.active { display: block; }

        /* Responsive */
        @media (max-width: 900px) {
            .reg-brand-panel { display: none; }
        }

        @media (max-width: 600px) {
            .reg-grid-2 { grid-template-columns: 1fr; }
            .reg-form-panel { padding: 2rem 1rem; }
        }
    </style>
</head>
<body class="reg-page">
    <a href="index.php" class="reg-home-link"><i class="fa-solid fa-house"></i> Home</a>

    <?php if (!empty($register_success)): ?>
        <!-- Success screen -->
        <div style="width:100%; display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f8fafc;">
            <div style="text-align:center; max-width:420px; padding:3rem 2rem; background:white; border-radius:20px; box-shadow:0 20px 40px rgba(0,0,0,0.08); border:1px solid #e2e8f0;">
                <div style="font-size:3.5rem; color:#059669; margin-bottom:1.25rem;"><i class="fa-solid fa-circle-check"></i></div>
                <h2 style="font-size:1.35rem; font-weight:800; color:#0f172a; margin-bottom:0.5rem;">Account Created Successfully!</h2>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:2rem;">Your registration has been completed. You can now log in to access the portal.</p>
                <a href="login.php" style="display:inline-flex; align-items:center; gap:0.6rem; background:linear-gradient(135deg,#1e3a8a,#1e40af); color:white; padding:0.85rem 2rem; border-radius:9px; text-decoration:none; font-weight:700; font-size:0.9rem; box-shadow:0 4px 12px rgba(30,58,138,0.25);">
                    <i class="fa-solid fa-right-to-bracket"></i> Proceed to Login
                </a>
            </div>
        </div>
    <?php else: ?>

    <!-- Left Branding Panel -->
    <aside class="reg-brand-panel">
        <div class="reg-brand-top">
            <div class="reg-brand-crest"><i class="fa-solid fa-graduation-cap"></i></div>
            <p class="reg-brand-univ">Oduduwa University · Ipetumodu</p>
            <h2 class="reg-brand-dept">Department of<br>Computer Science</h2>
            <div class="reg-brand-divider"></div>
            <p class="reg-brand-desc">Register to access the Final Year Project portal for CS students, supervisors, and department administration.</p>
            <div class="reg-brand-features">
                <div class="reg-brand-feature"><i class="fa-solid fa-file-circle-check"></i> Submit FYP deliverables</div>
                <div class="reg-brand-feature"><i class="fa-solid fa-comments"></i> Supervisor feedback</div>
                <div class="reg-brand-feature"><i class="fa-solid fa-chart-line"></i> Track project milestones</div>
                <div class="reg-brand-feature"><i class="fa-solid fa-bell"></i> Real-time notifications</div>
            </div>
        </div>
        <div class="reg-brand-bottom">
            &copy; <?= date('Y') ?> Department of Computer Science, OUI
        </div>
    </aside>

    <!-- Right Form Panel -->
    <main class="reg-form-panel">
        <div class="reg-form-inner">

            <div class="reg-form-header">
                <h1>Create an Account</h1>
                <p>Fill in your details below to register for the OUI CS FYP Portal.</p>
            </div>

            <?php if (!empty($register_error)): ?>
                <div style="background:#fef2f2; border:1px solid rgba(220,38,38,0.2); border-left:4px solid #dc2626; border-radius:9px; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem; font-size:0.88rem; font-weight:600; color:#dc2626;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= sanitize($register_error) ?>
                </div>
            <?php endif; ?>

            <!-- Role Tabs -->
            <div class="reg-role-tabs" role="tablist">
                <button type="button" class="reg-role-tab active" id="tab-student" onclick="switchRole('Student')" aria-selected="true">
                    <i class="fa-solid fa-user-graduate"></i> Student
                </button>
                <button type="button" class="reg-role-tab" id="tab-supervisor" onclick="switchRole('Supervisor')" aria-selected="false">
                    <i class="fa-solid fa-chalkboard-user"></i> Supervisor (Lecturer)
                </button>
            </div>

            <!-- ── STUDENT FORM ── -->
            <div id="panel-Student" class="role-panel active">
                <form action="register.php" method="POST" autocomplete="off" id="studentForm" onsubmit="return validateStudentForm()">
                    <input type="hidden" name="role" value="Student">

                    <div class="reg-section">
                        <div class="reg-section-title">Personal Information</div>
                        <div class="reg-grid-2">
                            <div class="reg-form-group">
                                <label class="reg-label" for="s_Nama">Full Name <span style="color:#dc2626;">*</span></label>
                                <input type="text" name="Nama" id="s_Nama" class="reg-input" placeholder="e.g. Adekunle Tobi" required value="<?= (isset($_POST['role']) && $_POST['role'] === 'Student') ? sanitize($_POST['Nama'] ?? '') : '' ?>">
                            </div>
                            <div class="reg-form-group">
                                <label class="reg-label" for="s_Email">Email Address <span style="color:#dc2626;">*</span></label>
                                <input type="email" name="Email" id="s_Email" class="reg-input" placeholder="e.g. student@oduduwa.edu.ng" required value="<?= (isset($_POST['role']) && $_POST['role'] === 'Student') ? sanitize($_POST['Email'] ?? '') : '' ?>">
                            </div>
                        </div>
                        <div class="reg-grid-2">
                            <div class="reg-form-group">
                                <label class="reg-label" for="s_Phone">Phone Number</label>
                                <input type="text" name="Phone" id="s_Phone" class="reg-input" placeholder="e.g. +234 812 345 6789" value="<?= (isset($_POST['role']) && $_POST['role'] === 'Student') ? sanitize($_POST['Phone'] ?? '') : '' ?>">
                            </div>
                            <div class="reg-form-group">
                                <label class="reg-label" for="s_Specialization">Area of Specialization</label>
                                <select name="Specialization" id="s_Specialization" class="reg-input">
                                    <option value="">-- Select --</option>
                                    <option value="General Computer Science" <?= (isset($_POST['role']) && $_POST['role'] === 'Student' && ($_POST['Specialization'] ?? '') === 'General Computer Science') ? 'selected' : '' ?>>General Computer Science</option>
                                    <option value="Software Engineering" <?= (isset($_POST['role']) && $_POST['role'] === 'Student' && ($_POST['Specialization'] ?? '') === 'Software Engineering') ? 'selected' : '' ?>>Software Engineering</option>
                                    <option value="Information Technology" <?= (isset($_POST['role']) && $_POST['role'] === 'Student' && ($_POST['Specialization'] ?? '') === 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                                    <option value="Cybersecurity" <?= (isset($_POST['role']) && $_POST['role'] === 'Student' && ($_POST['Specialization'] ?? '') === 'Cybersecurity') ? 'selected' : '' ?>>Cybersecurity</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="reg-section">
                        <div class="reg-section-title">Academic Details</div>
                        <div class="reg-grid-2">
                            <div class="reg-form-group">
                                <label class="reg-label" for="s_No_matrik">Matric Number <span style="color:#dc2626;">*</span></label>
                                <input type="text" name="No_matrik" id="s_No_matrik" class="reg-input" placeholder="e.g. CSC/2022/001" required value="<?= (isset($_POST['role']) && $_POST['role'] === 'Student') ? sanitize($_POST['No_matrik'] ?? '') : '' ?>">
                            </div>
                            <div class="reg-form-group">
                                <label class="reg-label" for="s_Semester">Current Semester <span style="color:#dc2626;">*</span></label>
                                <input type="number" name="Semester" id="s_Semester" class="reg-input" min="1" max="12" value="<?= (isset($_POST['role']) && $_POST['role'] === 'Student') ? intval($_POST['Semester'] ?? 8) : 8 ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="reg-section">
                        <div class="reg-section-title">Account Security</div>
                        <div class="reg-form-group">
                            <label class="reg-label" for="s_Katalaluan">Password <span style="color:#dc2626;">*</span></label>
                            <input type="password" name="Katalaluan" id="s_Katalaluan" class="reg-input" placeholder="Minimum 6 characters" required>
                            <span id="s_pwError" style="display:none; color:#dc2626; font-size:0.78rem; margin-top:0.25rem;">Password must be at least 6 characters.</span>
                        </div>
                    </div>

                    <div class="reg-submit-row">
                        <p class="reg-login-link">Already have an account? <a href="login.php">Log In</a></p>
                        <button type="submit" class="btn-reg-submit">
                            <i class="fa-solid fa-user-plus"></i> Create Student Account
                        </button>
                    </div>
                </form>
            </div>

            <!-- ── SUPERVISOR FORM ── -->
            <div id="panel-Supervisor" class="role-panel">
                <form action="register.php" method="POST" autocomplete="off" id="supervisorForm" onsubmit="return validateSupervisorForm()">
                    <input type="hidden" name="role" value="Supervisor">

                    <div class="reg-section">
                        <div class="reg-section-title">Personal Information</div>
                        <div class="reg-grid-2">
                            <div class="reg-form-group">
                                <label class="reg-label" for="sup_Nama">Full Name <span style="color:#dc2626;">*</span></label>
                                <input type="text" name="Nama" id="sup_Nama" class="reg-input" placeholder="e.g. Dr. Samuel Alabi" required value="<?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor') ? sanitize($_POST['Nama'] ?? '') : '' ?>">
                            </div>
                            <div class="reg-form-group">
                                <label class="reg-label" for="sup_Email">Email Address <span style="color:#dc2626;">*</span></label>
                                <input type="email" name="Email" id="sup_Email" class="reg-input" placeholder="e.g. lecturer@oduduwa.edu.ng" required value="<?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor') ? sanitize($_POST['Email'] ?? '') : '' ?>">
                            </div>
                        </div>
                        <div class="reg-form-group">
                            <label class="reg-label" for="sup_Phone">Phone Number</label>
                            <input type="text" name="Phone" id="sup_Phone" class="reg-input" placeholder="e.g. +234 812 345 6789" value="<?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor') ? sanitize($_POST['Phone'] ?? '') : '' ?>">
                        </div>
                    </div>

                    <div class="reg-section">
                        <div class="reg-section-title">Academic Details</div>
                        <div class="reg-grid-2">
                            <div class="reg-form-group">
                                <label class="reg-label" for="sup_No_staf">Lecturer Username <span style="color:#dc2626;">*</span></label>
                                <input type="text" name="No_staf" id="sup_No_staf" class="reg-input" placeholder="e.g. dralabi" required value="<?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor') ? sanitize($_POST['No_staf'] ?? '') : '' ?>">
                            </div>
                            <div class="reg-form-group">
                                <label class="reg-label" for="sup_Jawatan">Academic Designation <span style="color:#dc2626;">*</span></label>
                                <input type="text" name="Jawatan" id="sup_Jawatan" class="reg-input" placeholder="e.g. Senior Lecturer" required value="<?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor') ? sanitize($_POST['Jawatan'] ?? '') : '' ?>">
                            </div>
                        </div>
                        <div class="reg-form-group">
                            <label class="reg-label" for="sup_Specialization">Area of Specialization</label>
                            <select name="Specialization" id="sup_Specialization" class="reg-input">
                                <option value="">-- Select --</option>
                                <option value="General Computer Science" <?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor' && ($_POST['Specialization'] ?? '') === 'General Computer Science') ? 'selected' : '' ?>>General Computer Science</option>
                                <option value="Software Engineering" <?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor' && ($_POST['Specialization'] ?? '') === 'Software Engineering') ? 'selected' : '' ?>>Software Engineering</option>
                                <option value="Information Technology" <?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor' && ($_POST['Specialization'] ?? '') === 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                                <option value="Cybersecurity" <?= (isset($_POST['role']) && $_POST['role'] === 'Supervisor' && ($_POST['Specialization'] ?? '') === 'Cybersecurity') ? 'selected' : '' ?>>Cybersecurity</option>
                            </select>
                        </div>
                    </div>

                    <div class="reg-section">
                        <div class="reg-section-title">Account Security</div>
                        <div class="reg-form-group">
                            <label class="reg-label" for="sup_Katalaluan">Password <span style="color:#dc2626;">*</span></label>
                            <input type="password" name="Katalaluan" id="sup_Katalaluan" class="reg-input" placeholder="Minimum 6 characters" required>
                            <span id="sup_pwError" style="display:none; color:#dc2626; font-size:0.78rem; margin-top:0.25rem;">Password must be at least 6 characters.</span>
                        </div>
                    </div>

                    <div class="reg-submit-row">
                        <p class="reg-login-link">Already have an account? <a href="login.php">Log In</a></p>
                        <button type="submit" class="btn-reg-submit">
                            <i class="fa-solid fa-user-plus"></i> Create Supervisor Account
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <?php endif; ?>

    <script>
        // Switch role tab and show corresponding form panel
        function switchRole(role) {
            document.querySelectorAll('.reg-role-tab').forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            document.querySelectorAll('.role-panel').forEach(p => p.classList.remove('active'));

            document.getElementById('tab-' + role).classList.add('active');
            document.getElementById('tab-' + role).setAttribute('aria-selected', 'true');
            document.getElementById('panel-' + role).classList.add('active');
        }

        // Restore role tab on server-side error
        <?php if (!empty($register_error) && isset($_POST['role'])): ?>
        window.addEventListener('DOMContentLoaded', () => {
            switchRole('<?= sanitize($_POST['role']) ?>');
        });
        <?php endif; ?>

        // Student form validation
        function validateStudentForm() {
            const pw = document.getElementById('s_Katalaluan').value;
            if (pw.length < 6) {
                document.getElementById('s_pwError').style.display = 'block';
                document.getElementById('s_Katalaluan').focus();
                return false;
            }
            return true;
        }

        // Supervisor form validation
        function validateSupervisorForm() {
            const pw = document.getElementById('sup_Katalaluan').value;
            if (pw.length < 6) {
                document.getElementById('sup_pwError').style.display = 'block';
                document.getElementById('sup_Katalaluan').focus();
                return false;
            }
            return true;
        }
    </script>
</body>
</html>