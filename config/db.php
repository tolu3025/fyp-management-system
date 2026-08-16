<?php
// config/db.php
// Database configuration and auto-initialization for Local and Railway deployment

// 1. Load connection variables (prioritizing Railway environment variables)
$host = getenv('MYSQLHOST') ?: '127.0.0.1';
$db   = getenv('MYSQLDATABASE') ?: 'fyp_management_system';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : '';
$port = getenv('MYSQLPORT') ?: '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // 2. Database Auto-Builder: Checks if tables exist and seeds them if missing.
    // This removes the need for manual SQL import on Railway.
    $tableExists = false;
    try {
        $result = $pdo->query("SELECT 1 FROM HOD LIMIT 1");
        $tableExists = ($result !== false);
    } catch (\PDOException $e) {
        $tableExists = false;
    }

    if (!$tableExists) {
        $sql_file = __DIR__ . '/../db/fyp_db.sql';
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            
            // Strip database creation/switch commands since Railway provides pre-created DB
            $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS[^;]+;/i', '', $sql);
            $sql = preg_replace('/USE [^;]+;/i', '', $sql);
            
            // Execute the schema script
            $pdo->exec($sql);
        }
    }

} catch (\PDOException $e) {
    // Provide a beautiful error message to the user if DB connection fails
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Connection Error</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8fafc;
                color: #0f172a;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .error-card {
                background: white;
                padding: 2.5rem;
                border-radius: 12px;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                max-width: 500px;
                width: 100%;
                text-align: center;
                border-top: 5px solid #ef4444;
            }
            h1 {
                color: #dc2626;
                font-size: 1.5rem;
                margin-top: 0;
            }
            p {
                color: #64748b;
                line-height: 1.5;
            }
            .instructions {
                background: #f1f5f9;
                padding: 1rem;
                border-radius: 6px;
                text-align: left;
                font-size: 0.875rem;
                margin-top: 1.5rem;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <h1>Database Connection Failed</h1>
            <p>We are unable to connect to the MySQL database. Please make sure that XAMPP/WAMP (MySQL/MariaDB) or Railway service is running and configured correctly.</p>
            <div class="instructions">
                <strong>Error Details:</strong><br>
                <?= htmlspecialchars($e->getMessage()) ?><br><br>
                <strong>Setup Steps:</strong><br>
                1. Local: Open XAMPP Control Panel and start MySQL.<br>
                2. Live: Ensure environment variables (MYSQLHOST, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD, MYSQLPORT) are set on Railway.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>
