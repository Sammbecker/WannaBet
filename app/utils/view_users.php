<?php
require_once __DIR__ . '/../config/db.php';

// Create a direct database connection for this script
$dsn = "mysql:host=localhost;dbname=betting_app";
$username = "root"; 
$password = "1909"; 

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all users
    $stmt = $pdo->query("SELECT * FROM Users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>All Users in Database</h2>";
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='5'>";
        
        // Display table header based on first user's columns
        echo "<tr>";
        foreach (array_keys($users[0]) as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";
        
        // Display all users
        foreach ($users as $user) {
            echo "<tr>";
            foreach ($user as $key => $value) {
                // Truncate password hash for display
                if ($key === 'password') {
                    echo "<td>" . substr(htmlspecialchars($value), 0, 15) . "...</td>";
                } else {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 