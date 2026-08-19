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

            <!-- 2. Dynamic Multi-Step Registration Stepper Form Card -->
            <div id="registrationFormFields" style="display: none; width: 100%;">
                <div class="auth-card" style="text-align: left; max-width: 465px; margin: 0 auto;">
                    
                    <!-- Progress bar stepper indicator -->
                    <span class="wizard-step-indicator" id="stepIndicator">Step 1 of 7</span>
                    <div class="wizard-progress-container">
                        <div class="wizard-progress-bar" id="progressBar"></div>
                    </div>

                    <?php if (!empty($register_error)): ?>
                        <div class="alert alert-danger" style="text-align: left; margin-bottom: 1.5rem;" id="serverErrorAlert"><i class="fa-solid fa-triangle-exclamation"></i> <?= sanitize($register_error) ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" autocomplete="off" id="regForm">
                        <input type="hidden" name="role" id="roleInput" value="">

                        <!-- Step 0: Pre-screening role cards picker -->
                        <div class="wizard-step active" id="step0">
                            <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--primary); margin-bottom: 0.25rem;">Select Role</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Choose your registry category to initialize form parameters</p>
                            <div class="role-picker-grid" id="rolePicker" style="margin: 0;">
                                <div class="role-picker-card" data-role="Student" onclick="selectRole('Student')" style="margin: 0;">
                                    <i class="fa-solid fa-user-graduate"></i>
                                    <span>Student Account</span>
                                </div>
                                <div class="role-picker-card" data-role="Supervisor" onclick="selectRole('Supervisor')" style="margin: 0;">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                    <span>Supervisor Account</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Full Name -->
                        <div class="wizard-step" id="step1">
                            <div class="form-group">
                                <label for="Nama" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">What is your Full Name?</label>
                                <input type="text" name="Nama" id="Nama" class="form-input" placeholder="e.g. Adekunle Tobi" value="<?= isset($_POST['Nama']) ? sanitize($_POST['Nama']) : '' ?>">
                                <span class="error-msg" id="nameError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please enter your full name.</span>
                            </div>
                        </div>

                        <!-- Step 2: Email Address -->
                        <div class="wizard-step" id="step2">
                            <div class="form-group">
                                <label for="Email" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">Enter your Email Address</label>
                                <input type="email" name="Email" id="Email" class="form-input" placeholder="e.g. user@oduduwa.edu.ng" value="<?= isset($_POST['Email']) ? sanitize($_POST['Email']) : '' ?>">
                                <span class="error-msg" id="emailError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please enter a valid email address.</span>
                            </div>
                        </div>

                        <!-- Step 3: Phone Number -->
                        <div class="wizard-step" id="step3">
                            <div class="form-group">
                                <label for="Phone" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">Enter your Phone Number</label>
                                <input type="text" name="Phone" id="Phone" class="form-input" placeholder="e.g. +234 812 345 6789" value="<?= isset($_POST['Phone']) ? sanitize($_POST['Phone']) : '' ?>">
                                <span class="error-msg" id="phoneError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please enter a phone number.</span>
                            </div>
                        </div>

                        <!-- Step 4: Specialization / Program Option -->
                        <div class="wizard-step" id="step4">
                            <div class="form-group">
                                <label for="Specialization" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">Choose your Area of Specialization</label>
                                <select name="Specialization" id="Specialization" class="form-input">
                                    <option value="">-- Choose Option --</option>
                                    <option value="General Computer Science" <?= (isset($_POST['Specialization']) && $_POST['Specialization'] === 'General Computer Science') ? 'selected' : '' ?>>General Computer Science</option>
                                    <option value="Software Engineering" <?= (isset($_POST['Specialization']) && $_POST['Specialization'] === 'Software Engineering') ? 'selected' : '' ?>>Software Engineering</option>
                                    <option value="Information Technology" <?= (isset($_POST['Specialization']) && $_POST['Specialization'] === 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                                    <option value="Cybersecurity" <?= (isset($_POST['Specialization']) && $_POST['Specialization'] === 'Cybersecurity') ? 'selected' : '' ?>>Cybersecurity</option>
                                </select>
                                <span class="error-msg" id="specError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please select an option.</span>
                            </div>
                        </div>

                        <!-- Step 5 (Role specific): Student Matric Number OR Supervisor Lecturer Username -->
                        <div class="wizard-step" id="step5">
                            <!-- Student block -->
                            <div id="studentFieldsStep5" style="display: none;">
                                <div class="form-group">
                                    <label for="No_matrik" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">Enter your Matric Number</label>
                                    <input type="text" name="No_matrik" id="No_matrik" class="form-input" placeholder="e.g. CSC/2022/001" value="<?= isset($_POST['No_matrik']) ? sanitize($_POST['No_matrik']) : '' ?>">
                                    <span class="error-msg" id="matricError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please enter your matric number.</span>
                                </div>
                            </div>
                            <!-- Supervisor block -->
                            <div id="supervisorFieldsStep5" style="display: none;">
                                <div class="form-group">
                                    <label for="No_staf" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">Enter your Lecturer Username</label>
                                    <input type="text" name="No_staf" id="No_staf" class="form-input" placeholder="e.g. dralabi" value="<?= isset($_POST['No_staf']) ? sanitize($_POST['No_staf']) : '' ?>">
                                    <span class="error-msg" id="staffError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please enter a lecturer username.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6 (Role specific): Student Semester OR Supervisor Designation -->
                        <div class="wizard-step" id="step6">
                            <!-- Student block -->
                            <div id="studentFieldsStep6" style="display: none;">
                                <div class="form-group">
                                    <label for="Semester" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">Enter your current Semester</label>
                                    <input type="number" name="Semester" id="Semester" class="form-input" value="<?= isset($_POST['Semester']) ? intval($_POST['Semester']) : 8 ?>" min="1" max="12">
                                    <span class="error-msg" id="semError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please enter a semester between 1 and 12.</span>
                                </div>
                            </div>
                            <!-- Supervisor block -->
                            <div id="supervisorFieldsStep6" style="display: none;">
                                <div class="form-group">
                                    <label for="Jawatan" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">Enter your academic Designation</label>
                                    <input type="text" name="Jawatan" id="Jawatan" class="form-input" placeholder="e.g. Senior Lecturer" value="<?= isset($_POST['Jawatan']) ? sanitize($_POST['Jawatan']) : '' ?>">
                                    <span class="error-msg" id="jawatanError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please enter designation.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 7: Password -->
                        <div class="wizard-step" id="step7">
                            <div class="form-group">
                                <label for="Katalaluan" class="form-label" style="font-size: 1rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">Set password to secure account</label>
                                <input type="password" name="Katalaluan" id="Katalaluan" class="form-input" placeholder="••••••••">
                                <span class="error-msg" id="pwError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Password must be at least 6 characters.</span>
                            </div>
                        </div>

                        <!-- Stepper navigation buttons -->
                        <div class="wizard-actions" id="wizardActionsPanel" style="display: none;">
                            <button type="button" class="btn btn-secondary" id="prevBtn" onclick="prevStep()" style="padding: 0.6rem 1.5rem; font-size: 0.85rem;"><i class="fa-solid fa-chevron-left"></i> Back</button>
                            <button type="button" class="btn btn-portal-primary" id="nextBtn" onclick="nextStep()" style="padding: 0.6rem 2rem; font-size: 0.85rem;">Next <i class="fa-solid fa-chevron-right"></i></button>
                            <button type="submit" class="btn btn-portal-primary" id="submitRegBtn" style="padding: 0.6rem 2rem; font-size: 0.85rem; display: none;"><i class="fa-solid fa-circle-check"></i> Complete Registration</button>
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
                updateStepUI();
            });
        }

        // Stepper State variables
        let currentStep = 0;
        const totalSteps = 7;
        let selectedRole = '';

        // Handles pre-screening role card selection in Step 0
        function selectRole(role) {
            selectedRole = role;
            document.getElementById('roleInput').value = role;

            // Highlight selected card
            document.querySelectorAll('.role-picker-card').forEach(card => {
                if (card.getAttribute('data-role') === role) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });

            // Configure dynamic block fields visibility
            const matricStep5 = document.getElementById('studentFieldsStep5');
            const staffStep5 = document.getElementById('supervisorFieldsStep5');
            const semStep6 = document.getElementById('studentFieldsStep6');
            const jawStep6 = document.getElementById('supervisorFieldsStep6');

            if (role === 'Student') {
                matricStep5.style.display = 'block';
                staffStep5.style.display = 'none';
                semStep6.style.display = 'block';
                jawStep6.style.display = 'none';
            } else {
                matricStep5.style.display = 'none';
                staffStep5.style.display = 'block';
                semStep6.style.display = 'none';
                jawStep6.style.display = 'block';
            }

            // Move to Step 1 automatically after select
            setTimeout(() => {
                currentStep = 1;
                updateStepUI();
            }, 300);
        }

        // Navigate back one step
        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                updateStepUI();
            }
        }

        // Navigate next check with validation
        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateStepUI();
                }
            }
        }

        // Validate the active step input fields
        function validateCurrentStep() {
            // Hide all errors
            document.querySelectorAll('.error-msg').forEach(msg => msg.style.display = 'none');

            if (currentStep === 1) {
                const name = document.getElementById('Nama').value.trim();
                if (name === '') {
                    document.getElementById('nameError').style.display = 'block';
                    return false;
                }
            } else if (currentStep === 2) {
                const email = document.getElementById('Email').value.trim();
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regex.test(email)) {
                    document.getElementById('emailError').style.display = 'block';
                    return false;
                }
            } else if (currentStep === 3) {
                const phone = document.getElementById('Phone').value.trim();
                if (phone === '') {
                    document.getElementById('phoneError').style.display = 'block';
                    return false;
                }
            } else if (currentStep === 4) {
                const spec = document.getElementById('Specialization').value;
                if (spec === '') {
                    document.getElementById('specError').style.display = 'block';
                    return false;
                }
            } else if (currentStep === 5) {
                if (selectedRole === 'Student') {
                    const matric = document.getElementById('No_matrik').value.trim();
                    if (matric === '') {
                        document.getElementById('matricError').style.display = 'block';
                        return false;
                    }
                } else {
                    const staff = document.getElementById('No_staf').value.trim();
                    if (staff === '') {
                        document.getElementById('staffError').style.display = 'block';
                        return false;
                    }
                }
            } else if (currentStep === 6) {
                if (selectedRole === 'Student') {
                    const sem = parseInt(document.getElementById('Semester').value);
                    if (isNaN(sem) || sem < 1 || sem > 12) {
                        document.getElementById('semError').style.display = 'block';
                        return false;
                    }
                } else {
                    const jawatan = document.getElementById('Jawatan').value.trim();
                    if (jawatan === '') {
                        document.getElementById('jawatanError').style.display = 'block';
                        return false;
                    }
                }
            } else if (currentStep === 7) {
                const pw = document.getElementById('Katalaluan').value;
                if (pw.length < 6) {
                    document.getElementById('pwError').style.display = 'block';
                    return false;
                }
            }
            return true;
        }

        // Update active step wizard elements and progress bar width
        function updateStepUI() {
            // Show/hide steps
            document.querySelectorAll('.wizard-step').forEach((step, idx) => {
                if (idx === currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });

            // Update Progress bar indicators
            const progressBar = document.getElementById('progressBar');
            const stepIndicator = document.getElementById('stepIndicator');
            const actionPanel = document.getElementById('wizardActionsPanel');

            if (currentStep === 0) {
                actionPanel.style.display = 'none';
                progressBar.style.width = '0%';
                stepIndicator.innerText = 'Select Role';
            } else {
                actionPanel.style.display = 'flex';
                const percent = Math.round((currentStep / totalSteps) * 100);
                progressBar.style.width = percent + '%';
                
                // Set step labels
                let label = '';
                switch (currentStep) {
                    case 1: label = 'Full Name'; break;
                    case 2: label = 'Email Address'; break;
                    case 3: label = 'Phone Number'; break;
                    case 4: label = 'Specialization'; break;
                    case 5: label = (selectedRole === 'Student') ? 'Matric Number' : 'Lecturer Username'; break;
                    case 6: label = (selectedRole === 'Student') ? 'Current Semester' : 'Designation'; break;
                    case 7: label = 'Password Securing'; break;
                }
                stepIndicator.innerText = `Step ${currentStep} of ${totalSteps}: ${label}`;

                // Control button indicators visibility
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const submitBtn = document.getElementById('submitRegBtn');

                if (currentStep === totalSteps) {
                    nextBtn.style.display = 'none';
                    submitBtn.style.display = 'block';
                } else {
                    nextBtn.style.display = 'block';
                    submitBtn.style.display = 'none';
                }

                // Can go back to step 0
                prevBtn.style.display = 'block';
            }
        }

        // If validation errors are returned from PHP POST session, initialize form
        <?php if (!empty($register_error)): ?>
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('registerActions').style.display = 'none';
            document.getElementById('registrationFormFields').style.display = 'block';
            
            // Re-select role state dynamically
            const oldRole = "<?= isset($_POST['role']) ? sanitize($_POST['role']) : '' ?>";
            if (oldRole !== '') {
                selectRole(oldRole);
                // Advance to final step so errors are immediately editable
                currentStep = 7;
                updateStepUI();
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
