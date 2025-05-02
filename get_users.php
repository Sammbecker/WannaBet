<?php
require_once __DIR__ . '/app/config/db.php';

// Connect to database
$conn = getDB();

// Get user data
$sql = "SELECT id, username, email, password FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display users
echo "=== User Accounts ===\n\n";
foreach ($users as $user) {
    echo "ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Password Hash: " . $user['password'] . "\n";
    echo "-------------------------\n";
}
?> 