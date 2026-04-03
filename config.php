<?php
/**
 * Database Configuration
 * Secure database connection using PDO with error handling
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'crud_users_db');
define('DB_USER', 'root');
define('DB_PASS', 'Nidhi#20');
define('DB_CHARSET', 'utf8mb4');

// PDO connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // First, create the database if it doesn't exist
    $dsn_init = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo_init = new PDO($dsn_init, DB_USER, DB_PASS, $options);
    $pdo_init->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo_init = null;

    // Connect to the database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Create the users table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `phone` VARCHAR(20) DEFAULT NULL,
            `role` ENUM('Admin', 'Editor', 'Viewer') NOT NULL DEFAULT 'Viewer',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
?>
