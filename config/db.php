<?php
// config/db.php
// Database configuration and auto-initialization for Local and Railway deployment

// Helper function to safely retrieve environment variables across different PHP runtimes
function get_env_var($key, $default = '') {
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    $val = getenv($key);
    return ($val !== false && $val !== '') ? $val : $default;
}

// 1. Load connection variables (prioritizing Railway environment variables)
$host = get_env_var('MYSQLHOST', '127.0.0.1');
$db   = get_env_var('MYSQLDATABASE', 'fyp_management_system');
$user = get_env_var('MYSQLUSER', 'root');
$pass = get_env_var('MYSQLPASSWORD', '');
$port = get_env_var('MYSQLPORT', '3306');
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
    // Collect available environment variable keys for debugging
    $serverKeys = array_keys($_SERVER);
    $envKeys = array_keys($_ENV);
    
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
                padding: 20px;
            }
            .error-card {
                background: white;
                padding: 2.5rem;
                border-radius: 12px;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                max-width: 800px;
                width: 100%;
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
                overflow-x: auto;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <h1>Database Connection Failed</h1>
            <p>We are unable to connect to the MySQL database.</p>
            <div class="instructions">
                <strong>Error Details:</strong><br>
                <?= htmlspecialchars($e->getMessage()) ?><br><br>
                
                <strong>Diagnostic Info (Available Variable Keys):</strong><br>
                <p>If MYSQLPASSWORD is not listed below, Railway is not passing it to the app.</p>
                <b>$_SERVER keys:</b><br>
                <?= htmlspecialchars(implode(', ', $serverKeys)) ?><br><br>
                
                <b>$_ENV keys:</b><br>
                <?= htmlspecialchars(implode(', ', $envKeys)) ?><br><br>
                
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
