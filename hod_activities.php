<?php
// hod_activities.php
// Activity CRUD manager for HOD (Add, Edit, Delete departmental milestones)

require_once __DIR__ . '/includes/functions.php';
requireRole('HOD');
require_once __DIR__ . '/includes/header.php';

$edit_mode = false;
$activity = [
    'Kod_aktiviti' => '',
    'Jenis' => '',
    'Masa' => '',
    'Tarikh' => '',
    'Lokasi' => ''
];

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $kod_aktiviti = trim($_POST['Kod_aktiviti'] ?? '');
        $jenis = trim($_POST['Jenis'] ?? '');
        $masa = trim($_POST['Masa'] ?? '');
        $tarikh = trim($_POST['Tarikh'] ?? '');
        $lokasi = trim($_POST['Lokasi'] ?? '');

        if ($_POST['action'] === 'create') {
            // Validation
            if (empty($kod_aktiviti) || empty($jenis) || empty($masa) || empty($tarikh) || empty($lokasi)) {
                $_SESSION['error_msg'] = "All activity fields are required.";
            } else {
                try {
                    // Check duplicate code
                    $check = $pdo->prepare("SELECT COUNT(*) FROM Activity WHERE Kod_aktiviti = ?");
                    $check->execute([$kod_aktiviti]);
                    if ($check->fetchColumn() > 0) {
                        $_SESSION['error_msg'] = "Activity Code already exists.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO Activity (Kod_aktiviti, Masa, Tarikh, Lokasi, Jenis) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$kod_aktiviti, $masa, $tarikh, $lokasi, $jenis]);
                        $_SESSION['success_msg'] = "Activity '$kod_aktiviti' successfully created.";
                        
                        // Notify all students of new departmental activity
                        $students = $pdo->query("SELECT No_matrik FROM Student")->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($students as $matrik) {
                            createNotification($pdo, $matrik, "HOD added a new FYP activity: $jenis on $tarikh at $lokasi.");
                        }
                        
                        header("Location: hod_activities.php");
                        exit();
                    }
                } catch (PDOException $e) {
                    $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'update') {
            // Validation
            if (empty($kod_aktiviti) || empty($jenis) || empty($masa) || empty($tarikh) || empty($lokasi)) {
                $_SESSION['error_msg'] = "All activity fields are required.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE Activity SET Masa = ?, Tarikh = ?, Lokasi = ?, Jenis = ? WHERE Kod_aktiviti = ?");
                    $stmt->execute([$masa, $tarikh, $lokasi, $jenis, $kod_aktiviti]);
                    $_SESSION['success_msg'] = "Activity '$kod_aktiviti' successfully updated.";
                    header("Location: hod_activities.php");
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Handle Edit request (GET)
if (isset($_GET['edit'])) {
    $edit_id = trim($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM Activity WHERE Kod_aktiviti = ?");
    $stmt->execute([$edit_id]);
    $act_data = $stmt->fetch();
    if ($act_data) {
        $edit_mode = true;
        $activity = $act_data;
    }
}

// Handle Delete request (GET)
if (isset($_GET['delete'])) {
    $delete_id = trim($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM Activity WHERE Kod_aktiviti = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success_msg'] = "Activity '$delete_id' has been deleted.";
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Cannot delete activity. It might be linked to other records.";
    }
    header("Location: hod_activities.php");
    exit();
}

// Fetch all activities
$all_activities = $pdo->query("SELECT * FROM Activity ORDER BY Tarikh ASC, Masa ASC")->fetchAll();
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--bg-dark);">Manage Departmental Activities</h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Add, edit, or remove departmental milestones for final year students</p>
    </div>
    <a href="hod_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
</div>

<div class="grid-2">
    <!-- Form Card -->
    <div class="card">
        <div class="card-header">
            <h3><?= $edit_mode ? 'Edit Activity Details' : 'Schedule New Activity' ?></h3>
        </div>
        <div class="card-body">
            <form action="hod_activities.php" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="<?= $edit_mode ? 'update' : 'create' ?>">
                
                <div class="form-group">
                    <label for="Kod_aktiviti" class="form-label">Activity Code (Kod Aktiviti)</label>
                    <input type="text" name="Kod_aktiviti" id="Kod_aktiviti" class="form-input" 
                           placeholder="e.g. ACT004" required 
                           <?= $edit_mode ? 'readonly style="background-color: #f1f5f9;"' : '' ?> 
                           value="<?= sanitize($activity['Kod_aktiviti']) ?>">
                </div>

                <div class="form-group">
                    <label for="Jenis" class="form-label">Activity Type / Title (Jenis)</label>
                    <input type="text" name="Jenis" id="Jenis" class="form-input" 
                           placeholder="e.g. Thesis Draft Submission" required 
                           value="<?= sanitize($activity['Jenis']) ?>">
                </div>

                <div class="grid-2" style="gap: 1rem; margin-bottom: 0;">
                    <div class="form-group">
                        <label for="Tarikh" class="form-label">Date (Tarikh)</label>
                        <input type="date" name="Tarikh" id="Tarikh" class="form-input" required 
                               value="<?= sanitize($activity['Tarikh']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="Masa" class="form-label">Time (Masa)</label>
                        <input type="time" name="Masa" id="Masa" class="form-input" required 
                               value="<?= sanitize($activity['Masa']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="Lokasi" class="form-label">Location (Lokasi)</label>
                    <input type="text" name="Lokasi" id="Lokasi" class="form-input" 
                           placeholder="e.g. Computer Science Seminar Room" required 
                           value="<?= sanitize($activity['Lokasi']) ?>">
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Update Activity' : 'Schedule Activity' ?></button>
                    <?php if ($edit_mode): ?>
                        <a href="hod_activities.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Listing Card -->
    <div class="card">
        <div class="card-header">
            <h3>Scheduled Activities</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Details</th>
                            <th>Schedule</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_activities)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No activities configured. Use the form to schedule one.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_activities as $act): ?>
                                <tr>
                                    <td style="font-weight: 700; color: var(--primary);"><?= sanitize($act['Kod_aktiviti']) ?></td>
                                    <td>
                                        <strong><?= sanitize($act['Jenis']) ?></strong><br>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">📍 <?= sanitize($act['Lokasi']) ?></span>
                                    </td>
                                    <td>
                                        <?= date('d M Y', strtotime($act['Tarikh'])) ?><br>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?= date('h:i A', strtotime($act['Masa'])) ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="hod_activities.php?edit=<?= urlencode($act['Kod_aktiviti']) ?>" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" title="Edit">✏️</a>
                                            <a href="hod_activities.php?delete=<?= urlencode($act['Kod_aktiviti']) ?>" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="return confirm('Are you sure you want to delete this activity?')" title="Delete">🗑️</a>
                                        </div>
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
