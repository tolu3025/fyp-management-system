<?php
// api/mark_notifications.php
// AJAX API endpoint to mark dashboard notifications as read

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['all']) && $_POST['all'] === 'true') {
        try {
            $stmt = $pdo->prepare("UPDATE Notifications SET Status_Baca = 1 WHERE Penerima_ID = ?");
            $stmt->execute([$user_id]);
            echo json_encode(['success' => true]);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    } elseif (isset($_POST['id'])) {
        $notif_id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("UPDATE Notifications SET Status_Baca = 1 WHERE ID_notifikasi = ? AND Penerima_ID = ?");
            $stmt->execute([$notif_id, $user_id]);
            echo json_encode(['success' => true]);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid Request']);
?>
