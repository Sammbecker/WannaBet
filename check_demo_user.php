<?php
require_once __DIR__ . '/app/config/db.php';

// Connect to database
$conn = getDB();

// Get kiko user
$userSql = "SELECT * FROM users WHERE username = 'kiko'";
$userStmt = $conn->query($userSql);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

echo "=== DEMO USER DETAILS ===\n\n";
echo "ID: " . $user['id'] . "\n";
echo "Username: " . $user['username'] . "\n";
echo "Email: " . $user['email'] . "\n\n";

// Get friends
$friendsSql = "SELECT u.id, u.username, u.email FROM users u
              JOIN friendships f ON (u.id = f.friend_id OR u.id = f.user_id)
              WHERE ((f.user_id = ? OR f.friend_id = ?) 
              AND u.id != ?
              AND f.status = 'accepted')";
$friendsStmt = $conn->prepare($friendsSql);
$friendsStmt->execute([$user['id'], $user['id'], $user['id']]);
$friends = $friendsStmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== FRIENDS (" . count($friends) . ") ===\n\n";
foreach ($friends as $friend) {
    echo "ID: " . $friend['id'] . "\n";
    echo "Username: " . $friend['username'] . "\n";
    echo "Email: " . $friend['email'] . "\n";
    echo "--------------------------\n";
}

// Get bets
$betsSql = "SELECT b.*, 
           creator.username as creator_name, 
           opponent.username as opponent_name,
           winner.username as winner_name 
           FROM bets b
           JOIN users creator ON b.creator_id = creator.id
           LEFT JOIN users opponent ON b.opponent_id = opponent.id
           LEFT JOIN users winner ON b.winner_id = winner.id
           WHERE b.creator_id = ? OR b.opponent_id = ?
           ORDER BY b.created_at DESC";
$betsStmt = $conn->prepare($betsSql);
$betsStmt->execute([$user['id'], $user['id']]);
$bets = $betsStmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== BETS (" . count($bets) . ") ===\n\n";
foreach ($bets as $bet) {
    echo "ID: " . $bet['id'] . "\n";
    echo "Title: " . $bet['title'] . "\n";
    echo "Description: " . $bet['description'] . "\n";
    echo "Creator: " . $bet['creator_name'] . " (ID: " . $bet['creator_id'] . ")\n";
    echo "Opponent: " . $bet['opponent_name'] . " (ID: " . $bet['opponent_id'] . ")\n";
    echo "Stake Type: " . $bet['stake_type'] . "\n";
    
    if ($bet['stake_type'] === 'money') {
        echo "Stake Amount: R" . number_format($bet['stake_amount'], 2) . "\n";
    } else {
        echo "Stake Description: " . $bet['stake_description'] . "\n";
    }
    
    echo "Status: " . $bet['status'] . "\n";
    
    if ($bet['status'] === 'completed') {
        echo "Winner: " . $bet['winner_name'] . " (ID: " . $bet['winner_id'] . ")\n";
    }
    
    echo "Created: " . $bet['created_at'] . "\n";
    echo "Updated: " . $bet['updated_at'] . "\n";
    echo "--------------------------\n";
}

// Get notifications
$notifSql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$notifStmt = $conn->prepare($notifSql);
$notifStmt->execute([$user['id']]);
$notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== NOTIFICATIONS (" . count($notifications) . ") ===\n\n";
foreach ($notifications as $notif) {
    echo "ID: " . $notif['id'] . "\n";
    echo "Type: " . $notif['type'] . "\n";
    echo "Message: " . $notif['message'] . "\n";
    echo "Read: " . ($notif['is_read'] ? 'Yes' : 'No') . "\n";
    echo "Created: " . $notif['created_at'] . "\n";
    echo "--------------------------\n";
}
?> 