<?php
// includes/header.php
// Standardized header element containing navigation shell and notification tray queries

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Check login status
if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Fetch unread notifications count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE Penerima_ID = ? AND Status_Baca = 0");
$stmt->execute([$user_id]);
$unread_count = $stmt->fetchColumn();

// Fetch recent 5 notifications
$stmt = $pdo->prepare("SELECT * FROM Notifications WHERE Penerima_ID = ? ORDER BY Tarikh_Cipta DESC LIMIT 5");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FYP Management System — Oduduwa University Ipetumodu</title>
    <!-- CSS Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome or simple characters for icons -->
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Include Navigation Sidebar -->
        <?php include_once __DIR__ . '/nav.php'; ?>

        <!-- Main Content Pane -->
        <div class="main-content">
            <!-- Top Nav Panel -->
            <header class="top-nav">
                <div class="page-title">
                    FYP Management System
                </div>
                
                <div class="nav-actions">
                    <!-- Notification Tray -->
                    <div class="notif-dropdown-container">
                        <button class="notif-badge-btn" id="notifBtn" aria-label="Notifications">
                            <span class="notif-icon">🔔</span>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge-count"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </button>
                        
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-dropdown-header">
                                <span>Alerts Dashboard</span>
                                <?php if ($unread_count > 0): ?>
                                    <a href="#" id="markAllNotif" style="font-size: 0.75rem; text-decoration: none; color: var(--primary);">Mark all as read</a>
                                <?php endif; ?>
                            </div>
                            <div class="notif-list">
                                <?php if (empty($notifications)): ?>
                                    <div class="notif-empty">No notifications yet.</div>
                                <?php else: ?>
                                    <?php foreach ($notifications as $n): ?>
                                        <div class="notif-item <?= $n['Status_Baca'] == 0 ? 'unread' : '' ?>" data-id="<?= $n['ID_notifikasi'] ?>">
                                            <div class="notif-item-text"><?= sanitize($n['Mesej']) ?></div>
                                            <div class="notif-item-time"><?= date('M d, H:i', strtotime($n['Tarikh_Cipta'])) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- User Account Meta -->
                    <div style="font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                        <span>👤</span>
                        <span><?= sanitize($user_name) ?> (<?= sanitize($user_role) ?>)</span>
                    </div>
                </div>
            </header>

            <!-- Content Area start -->
            <main class="content-body">
                <?= getSystemMessage() ?>
