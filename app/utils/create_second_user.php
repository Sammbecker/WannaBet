<?php
require_once __DIR__ . '/../config/db.php';

// Create a direct database connection for this script
$dsn = "mysql:host=localhost;dbname=betting_app";
$username = "root"; 
$password = "1909"; 

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create a second test user with a properly hashed password
    $testUsername = 'betpartner';
    $testEmail = 'partner@example.com';
    $plainPassword = 'test123';
    $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE username = ? OR email = ?");
    $stmt->execute([$testUsername, $testEmail]);
    
    if ($stmt->fetchColumn() > 0) {
        echo "Second test user already exists. Updating password...<br>";
        
        // Update the password
        $stmt = $pdo->prepare("UPDATE Users SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $testUsername]);
        
        echo "Password updated for user: $testUsername<br>";
    } else {
        // Insert the test user
        $stmt = $pdo->prepare("INSERT INTO Users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$testUsername, $testEmail, $hashedPassword]);
        
        echo "Second test user created:<br>";
        echo "Username: $testUsername<br>";
        echo "Email: $testEmail<br>";
        echo "Password: $plainPassword<br>";
    }
    
    echo "<br>You can now log in with either test user and send bets between them.";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 