<?php
// config/db.php
// Database configuration for Ramon Adedoyin College of Natural and Applied Sciences, Oduduwa University Ipetumodu

$host = '127.0.0.1';
$db   = 'fyp_management_system';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
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
            <p>We are unable to connect to the MySQL database. Please make sure that XAMPP/WAMP (MySQL/MariaDB) is running and configured correctly.</p>
            <div class="instructions">
                <strong>Error Details:</strong><br>
                <?= htmlspecialchars($e->getMessage()) ?><br><br>
                <strong>Setup Steps:</strong><br>
                1. Open XAMPP Control Panel and start MySQL.<br>
                2. Import the SQL schema from <code>db/fyp_db.sql</code> via phpMyAdmin.<br>
                3. Ensure user 'root' exists with no password on localhost.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>
