<?php
require_once __DIR__ . '/../app/config/db.php';

try {
    $db = getDB();
    
    // Drop tables in correct order to handle foreign key constraints
    $db->exec("DROP TABLE IF EXISTS paymentintents");
    $db->exec("DROP TABLE IF EXISTS notifications");
    $db->exec("DROP TABLE IF EXISTS friendships");
    $db->exec("DROP TABLE IF EXISTS bet_participants");
    $db->exec("DROP TABLE IF EXISTS bet_outcomes");
    $db->exec("DROP TABLE IF EXISTS payments");
    $db->exec("DROP TABLE IF EXISTS bets");
    $db->exec("DROP TABLE IF EXISTS users");
    
    echo "All tables dropped successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 