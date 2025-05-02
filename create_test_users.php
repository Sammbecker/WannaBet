<?php
require_once __DIR__ . '/app/config/db.php';

// Connect to database
$conn = getDB();

// Create test users
$users = [
    ['username' => 'test', 'email' => 'test@example.com', 'password' => 'password123'],
    ['username' => 'partner', 'email' => 'partner@example.com', 'password' => 'password123']
];

foreach ($users as $user) {
    // Check if user already exists
    $checkSql = "SELECT COUNT(*) FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$user['username']]);
    
    if ($checkStmt->fetchColumn() > 0) {
        echo "User {$user['username']} already exists, skipping...\n";
        continue;
    }

    // Create user
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$user['username'], $user['email'], $hashedPassword]);
    
    if ($result) {
        echo "Created user {$user['username']} with password {$user['password']}\n";
    } else {
        echo "Failed to create user {$user['username']}\n";
    }
}

echo "\nAll test users created successfully!\n";
echo "You can login with:\n";
echo "Username: test, Password: password123\n";
echo "Username: partner, Password: password123\n";
?> 