<?php
session_start();
require_once __DIR__ . '/app/config/db.php';

// Parse command line arguments
$acceptId = null;
foreach ($argv as $arg) {
    if (strpos($arg, 'accept=') === 0) {
        $acceptId = (int)substr($arg, 7);
    }
}

// Ensure user is logged in for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Set to a valid user ID for testing
    echo "Set test user ID to: " . $_SESSION['user_id'] . "\n";
}

// Connect to the database
$conn = getDB();

// Show pending friend requests
$sql = "SELECT f.*, u.username, u.email 
        FROM friendships f
        JOIN users u ON f.user_id = u.id
        WHERE f.friend_id = ? AND f.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Pending friend requests for User ID " . $_SESSION['user_id'] . ":\n";
echo "==============================================================\n";
if (empty($requests)) {
    echo "No pending friend requests found.\n";
} else {
    foreach ($requests as $request) {
        echo "Request ID: " . $request['id'] . 
             ", From: " . $request['username'] . " (User ID: " . $request['user_id'] . ")" .
             ", Status: " . $request['status'] . "\n";
    }
}

echo "\nTEST ACCEPTING FRIENDSHIP:\n";
echo "==============================================================\n";

// Check if we have a request to accept
if ($acceptId !== null) {
    $requestId = $acceptId;
    
    // Verify request exists and is for this user
    $sql = "SELECT COUNT(*) FROM friendships WHERE id = ? AND friend_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$requestId, $_SESSION['user_id']]);
    $requestExists = $stmt->fetchColumn() > 0;
    
    if (!$requestExists) {
        echo "Error: Request ID $requestId not found or not for this user.\n";
    } else {
        // Update the request status
        $sql = "UPDATE friendships SET status = 'accepted' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([$requestId]);
        
        if ($success) {
            echo "SUCCESS: Friend request $requestId accepted!\n";
            
            // Show updated friendship
            $sql = "SELECT f.*, u1.username as requester, u2.username as accepter 
                    FROM friendships f
                    JOIN users u1 ON f.user_id = u1.id
                    JOIN users u2 ON f.friend_id = u2.id
                    WHERE f.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$requestId]);
            $friendship = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($friendship) {
                echo "Friendship Details:\n";
                echo "  ID: " . $friendship['id'] . "\n";
                echo "  Requester: " . $friendship['requester'] . " (ID: " . $friendship['user_id'] . ")\n";
                echo "  Accepter: " . $friendship['accepter'] . " (ID: " . $friendship['friend_id'] . ")\n";
                echo "  Status: " . $friendship['status'] . "\n";
                echo "  Created: " . $friendship['created_at'] . "\n";
                echo "  Updated: " . $friendship['updated_at'] . "\n";
            }
        } else {
            echo "ERROR: Failed to accept friend request.\n";
        }
    }
} else {
    echo "To test accepting a request, run: php test_friend_request.php accept=REQUEST_ID\n";
    echo "For example: php test_friend_request.php accept=1\n";
}
?> 