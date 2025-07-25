<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Default XAMPP username
define('DB_PASS', '');      // Default XAMPP password
define('DB_NAME', 'betting_app');

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
