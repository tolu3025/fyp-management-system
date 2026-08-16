<?php
// includes/functions.php
// Common utility functions, session management, alert notifications, and simulated email log dispatch

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Language Switched Listener
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'] === 'ms' ? 'ms' : 'en';
    $_SESSION['lang'] = $lang;
    
    // Redirect to same URL without lang parameter to keep clean URLs
    $uri = strtok($_SERVER["REQUEST_URI"], '?');
    $query = $_GET;
    unset($query['lang']);
    if (!empty($query)) {
        $uri .= '?' . http_build_query($query);
    }
    header("Location: $uri");
    exit();
}

/**
 * Localization helper function
 */
function __($key) {
    global $lang_dict;
    static $dict_loaded = false;
    if (!$dict_loaded) {
        require_once __DIR__ . '/lang.php';
        $dict_loaded = true;
    }
    $lang = $_SESSION['lang'] ?? 'en';
    return $lang_dict[$lang][$key] ?? $lang_dict['en'][$key] ?? $key;
}

/**
 * Sanitizes input data to prevent XSS
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Checks if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Enforces role-based access control. Redirects to login if not authorized.
 * @param array|string $allowed_roles
 */
function requireRole($allowed_roles) {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
    
    $allowed = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];
    if (!in_array($_SESSION['user_role'], $allowed)) {
        header("Location: index.php?error=unauthorized");
        exit();
    }
}

/**
 * Create a dashboard notification alert for a user
 */
function createNotification($pdo, $receiver_id, $message) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Notifications (Penerima_ID, Mesej, Status_Baca, Tarikh_Cipta) VALUES (?, ?, 0, NOW())");
        $stmt->execute([$receiver_id, $message]);
        return true;
    } catch (PDOException $e) {
        // Silent catch or log error
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Simulates and sends an email notification.
 * Logs output to logs/emails.log and executes PHP mail().
 */
function sendSystemEmail($to_email, $to_name, $subject, $body) {
    // 1. Log the email to file for verification and debugging on local environments
    $log_dir = dirname(__DIR__) . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $log_file = $log_dir . '/emails.log';
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "=================================================================\n";
    $log_entry .= "TIMESTAMP:   $timestamp\n";
    $log_entry .= "TO:          $to_name <$to_email>\n";
    $log_entry .= "SUBJECT:     $subject\n";
    $log_entry .= "MESSAGE:\n$body\n";
    $log_entry .= "=================================================================\n\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    // 2. Dispatch real PHP mail (may require SMTP configured in php.ini)
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: FYP System Ramon Adedoyin College <noreply@oduduwa.edu.ng>" . "\r\n";
    
    // Convert newlines to HTML paragraphs for the real mail body
    $html_body = "<html><body>";
    $html_body .= "<h2>FYP Management System — Oduduwa University Ipetumodu</h2>";
    $html_body .= "<p>" . str_replace("\n", "<br>", htmlspecialchars($body)) . "</p>";
    $html_body .= "<hr><p style='font-size: 0.8em; color: #777;'>This is an automated system notification. Please do not reply directly.</p>";
    $html_body .= "</body></html>";
    
    // Suppress mail error in case SMTP is not configured in local environment
    @mail($to_email, $subject, $html_body, $headers);
}

/**
 * Formats system feedback messages (success/error alerts)
 */
function getSystemMessage() {
    if (isset($_SESSION['success_msg'])) {
        $msg = $_SESSION['success_msg'];
        unset($_SESSION['success_msg']);
        return '<div class="alert alert-success">' . sanitize($msg) . '</div>';
    }
    if (isset($_SESSION['error_msg'])) {
        $msg = $_SESSION['error_msg'];
        unset($_SESSION['error_msg']);
        return '<div class="alert alert-danger">' . sanitize($msg) . '</div>';
    }
    return '';
}
?>
