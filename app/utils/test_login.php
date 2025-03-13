<?php
require_once __DIR__ . '/../config/db.php';
session_start();

// Create a direct database connection for this script
$dsn = "mysql:host=localhost;dbname=betting_app";
$username = "root"; 
$password = "1909"; 

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Display form for login testing
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo '<h2>Test Login Functionality (Using Email)</h2>';
        echo '<form method="post">';
        echo '<div><label>Email: <input type="email" name="email" required></label></div>';
        echo '<div><label>Password: <input type="password" name="password" required></label></div>';
        echo '<div><button type="submit">Test Login</button></div>';
        echo '</form>';
        
        // Show existing users for reference
        echo '<h3>Existing Users:</h3>';
        $stmt = $pdo->query("SELECT user_id, username, email FROM Users");
        $users = $stmt->fetchAll();
        
        if (count($users) > 0) {
            echo '<table border="1" cellpadding="5">';
            echo '<tr><th>ID</th><th>Username</th><th>Email</th></tr>';
            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . $user['user_id'] . '</td>';
                echo '<td>' . $user['username'] . '</td>';
                echo '<td>' . $user['email'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '<p>Note: Test users created using the test user scripts have password <strong>test123</strong></p>';
        } else {
            echo '<p>No users found in the database. Please run create_test_user.php first.</p>';
        }
    }
    
    // Process login test
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $testEmail = $_POST['email'] ?? '';
        $testPassword = $_POST['password'] ?? '';
        
        echo '<h2>Login Test Results</h2>';
        
        // Step 1: Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$testEmail]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo '<p style="color: red;">User not found with email: ' . htmlspecialchars($testEmail) . '</p>';
            echo '<p><a href="test_login.php">Go back</a></p>';
            exit;
        }
        
        echo '<p style="color: green;">User found in database ✓</p>';
        
        // Step 2: Check password
        if (password_verify($testPassword, $user['password'])) {
            echo '<p style="color: green;">Password is correct ✓</p>';
            echo '<p style="color: green;">Login successful! ✓</p>';
            
            // For testing, also show session data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            
            echo '<h3>Session Data:</h3>';
            echo '<pre>';
            print_r($_SESSION);
            echo '</pre>';
        } else {
            echo '<p style="color: red;">Password verification failed ✗</p>';
            echo '<p>Stored hash: ' . substr($user['password'], 0, 10) . '...</p>';
        }
        
        echo '<p><a href="test_login.php">Go back</a></p>';
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 