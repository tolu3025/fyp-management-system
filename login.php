<?php
// login.php
// Redesigned: Unified, sliding interactive authentication controller
// Handles both login and registration dynamically in a futuristic cyber theme.

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
$register_error = '';
$register_success = '';

// Handle Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($login_id) || empty($password)) {
        $error = "Please enter both Username/ID and Password.";
    } else {
        // 1. Check Student Table
        $stmt = $pdo->prepare("SELECT * FROM Student WHERE No_matrik = ?");
        $stmt->execute([$login_id]);
        $user = $stmt->fetch();
        $role = 'Student';

        // 2. Check Supervisor Table
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM Supervisor WHERE No_staf = ?");
            $stmt->execute([$login_id]);
            $user = $stmt->fetch();
            $role = 'Supervisor';
        }

        // 3. Check HOD Table
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM HOD WHERE No_staf = ?");
            $stmt->execute([$login_id]);
            $user = $stmt->fetch();
            $role = 'HOD';
        }

        // Verify and authenticate
        if ($user && password_verify($password, $user['Katalaluan'])) {
            $_SESSION['user_id'] = ($role === 'Student') ? $user['No_matrik'] : $user['No_staf'];
            $_SESSION['user_name'] = $user['Nama'];
            $_SESSION['user_role'] = $role;
            $_SESSION['user_email'] = $user['Email'];

            if ($role === 'HOD') {
                header("Location: hod_dashboard.php");
            } elseif ($role === 'Supervisor') {
                header("Location: supervisor_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Login credentials.";
        }
    }
}

// Handle Registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
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
                    $register_error = "Matric / Username '$no_matrik' is already in use.";
                } else {
                    $hash = password_hash($katalaluan, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO Student (No_matrik, Nama, Katalaluan, Semester, Email) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$no_matrik, $nama, $hash, $semester, $email]);

                    $register_success = "Registration successful! You can now access your node.";
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
                    $register_error = "Matric / Username '$no_staf' is already in use.";
                } else {
                    $hash = password_hash($katalaluan, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO Supervisor (No_staf, Nama, Katalaluan, Jawatan, Email) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$no_staf, $nama, $hash, $jawatan, $email]);

                    $register_success = "Registration successful! You can now access your node.";
                }
            } catch (PDOException $e) {
                $register_error = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// Initial state parameters
$init_mode = $_GET['mode'] ?? 'login';
if (!empty($register_error) || !empty($register_success)) {
    $init_mode = 'register';
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="cyber-theme">
    <!-- Public Header -->
    <header class="portal-header">
        <a href="index.php" class="portal-logo" style="text-decoration: none;">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>CS FYP Portal</span>
        </a>
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <!-- Language Selector Dropdown -->
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-globe" style="color: var(--text-muted); font-size: 0.9rem;"></i>
                <form method="GET" action="" style="margin: 0; display: flex; align-items: center;">
                    <input type="hidden" name="mode" id="langModeInput" value="<?= htmlspecialchars($init_mode) ?>">
                    <select name="lang" onchange="this.form.submit()" class="form-input" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; border-radius: var(--radius-sm); border: 1px solid var(--border); cursor: pointer; width: auto;">
                        <option value="en" <?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'selected' : '' ?>>EN</option>
                        <option value="ms" <?= ($_SESSION['lang'] ?? 'en') === 'ms' ? 'selected' : '' ?>>MS</option>
                    </select>
                </form>
            </div>
            <a href="index.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;"><i class="fa-solid fa-house"></i> Home</a>
        </div>
    </header>

    <div class="auth-container <?= ($init_mode === 'login') ? 'login-active' : '' ?>" id="authContainer">
        <!-- 1. Left Pane: Login Credentials Form -->
        <div class="auth-form-pane">
            <div class="auth-overlay-card">
                <h2>PORTAL GATEWAY</h2>
                <p class="subtitle"><?= __('login_subtitle') ?></p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="text-align: left;"><i class="fa-solid fa-triangle-exclamation"></i> <?= sanitize($error) ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST" autocomplete="off">
                    <input type="hidden" name="login_submit" value="1">
                    
                    <div class="form-group" style="text-align: left;">
                        <label for="login_id" class="form-label"><?= __('username_or_id') ?></label>
                        <input type="text" name="login_id" id="login_id" class="form-input" placeholder="e.g. CSC/2022/001 or dralabi" required value="<?= isset($_POST['login_id']) ? sanitize($_POST['login_id']) : '' ?>">
                    </div>

                    <div class="form-group" style="text-align: left;">
                        <label for="password" class="form-label"><?= __('password') ?></label>
                        <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-cyber-cyan btn-block" style="margin-top: 1.5rem;"><i class="fa-solid fa-right-to-bracket"></i> <?= __('login_button') ?></button>
                </form>

                <div style="margin-top: 1.5rem; text-align: center; font-size: 0.85rem;">
                    <a href="#" id="toRegisterBtn" style="color: var(--cyber-glow-pink); text-decoration: none; font-weight: 700;"><i class="fa-solid fa-user-plus"></i> Initialize Portal Node</a>
                </div>
            </div>
        </div>

        <!-- 2. Right Pane: Background illustration + welcome card + dynamic registration overlays -->
        <div class="auth-illustration-pane">
            <!-- Registration Card Overlay -->
            <div class="auth-overlay-card" id="registerCard">
                <!-- Toggle-able intro contents -->
                <div id="registerIntro">
                    <h2>CS PORTAL NODE</h2>
                    <p class="subtitle">Ramon Adedoyin College of Natural and Applied Sciences</p>
                    
                    <?php if (!empty($register_success)): ?>
                        <div class="alert alert-success" style="text-align: left; margin-bottom: 2rem;"><i class="fa-solid fa-circle-check"></i> <?= sanitize($register_success) ?></div>
                        <a href="#" onclick="toggleMode('login')" class="btn btn-cyber-cyan btn-block" style="margin-bottom: 1.5rem;"><i class="fa-solid fa-right-to-bracket"></i> Click Here to Log In</a>
                    <?php else: ?>
                        <button type="button" id="startRegisterBtn" class="btn btn-cyber-primary btn-block" style="padding: 1rem; font-size: 1rem;"><i class="fa-solid fa-network-wired"></i> REGISTER PORTAL NODE</button>
                        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.85rem; color: #9ca3af;">
                            Already have an active node? <a href="#" id="toLoginBtn" style="color: var(--cyber-glow-cyan); text-decoration: none; font-weight: 700;">Log In Here</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Registration fields inputs (Hidden initially) -->
                <div id="registerFormFields" style="display: none;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: white; margin-bottom: 0.25rem;">Node Registration</h3>
                    <p style="font-size: 0.8rem; color: #9ca3af; margin-bottom: 1.5rem;">Select your role node and authorize credentials</p>

                    <?php if (!empty($register_error)): ?>
                        <div class="alert alert-danger" style="text-align: left; margin-bottom: 1rem;"><i class="fa-solid fa-triangle-exclamation"></i> <?= sanitize($register_error) ?></div>
                    <?php endif; ?>

                    <form action="login.php?mode=register" method="POST" autocomplete="off" id="regForm">
                        <input type="hidden" name="register_submit" value="1">
                        <input type="hidden" name="role" id="roleInput" value="">

                        <!-- Pre-screening cards picker -->
                        <div class="role-picker-grid" id="rolePicker">
                            <div class="role-picker-card" data-role="Student" onclick="selectRole('Student')">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span><?= __('student') ?></span>
                            </div>
                            <div class="role-picker-card" data-role="Supervisor" onclick="selectRole('Supervisor')">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span><?= __('supervisor_role') ?></span>
                            </div>
                        </div>

                        <!-- Dynamic fields container -->
                        <div class="registration-slide-fields" id="regDynamicFields">
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
                                    <label for="No_staf" class="form-label"><?= __('staff_no') ?></label>
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

                            <button type="submit" class="btn btn-cyber-primary btn-block" style="margin-top: 1rem;"><i class="fa-solid fa-user-plus"></i> Post Registration Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('authContainer');
        const langModeInput = document.getElementById('langModeInput');

        // Toggles between register mode (full screen illustration) and login mode (split screen)
        function toggleMode(mode) {
            if (mode === 'login') {
                container.classList.add('login-active');
                langModeInput.value = 'login';
            } else {
                container.classList.remove('login-active');
                langModeInput.value = 'register';
            }
        }

        // Connect button listeners
        document.getElementById('toLoginBtn').addEventListener('click', (e) => {
            e.preventDefault();
            toggleMode('login');
        });

        document.getElementById('toRegisterBtn').addEventListener('click', (e) => {
            e.preventDefault();
            toggleMode('register');
        });

        document.getElementById('startRegisterBtn').addEventListener('click', () => {
            document.getElementById('registerIntro').style.display = 'none';
            document.getElementById('registerFormFields').style.display = 'block';
        });

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
            document.getElementById('registerIntro').style.display = 'none';
            document.getElementById('registerFormFields').style.display = 'block';
        });
        <?php endif; ?>
    </script>
</body>
</html>
