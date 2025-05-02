<?php
// This script tests the direct approach to accepting a friend request
// to isolate where the issue might be occurring
require_once __DIR__ . '/app/config/db.php';
require_once __DIR__ . '/app/controllers/FriendshipController.php';

echo "=== FINAL FRIENDSHIP ACCEPTANCE TEST ===\n\n";

// Connect to database
$conn = getDB();

// Step 1: Create test users
echo "Step 1: Creating test users...\n";
$user1 = createTestUser($conn, "sender_" . time());
$user2 = createTestUser($conn, "receiver_" . time());

echo "Created sender user: ID={$user1['id']}, Username={$user1['username']}\n";
echo "Created receiver user: ID={$user2['id']}, Username={$user2['username']}\n\n";

// Step 2: Create friendship request directly in the database
echo "Step 2: Creating friendship request...\n";
$sql = "INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'pending')";
$stmt = $conn->prepare($sql);
$stmt->execute([$user1['id'], $user2['id']]);
$friendshipId = $conn->lastInsertId();

$sql = "SELECT * FROM friendships WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$friendshipId]);
$friendship = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Created friendship request: ID=$friendshipId\n";
echo "  From: {$user1['username']} (ID: {$user1['id']})\n";
echo "  To: {$user2['username']} (ID: {$user2['id']})\n";
echo "  Status: {$friendship['status']}\n\n";

// Step 3: Test controller method for accepting request
echo "Step 3: Testing controller accept method...\n";
// Set up session for the receiver user
session_start();
$_SESSION['user_id'] = $user2['id'];
$_SESSION['username'] = $user2['username'];

// Set up POST parameters
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['request_id'] = $friendshipId;
$_POST['response'] = 'accept';
$_POST['respond_request'] = true;

// Create controller and call the method
$controller = new FriendshipController();
$result = $controller->respondToRequest();

// Display result
echo "Controller result:\n";
echo "  Success: " . ($result['success'] ? 'true' : 'false') . "\n";
if ($result['success']) {
    echo "  Message: " . $result['message'] . "\n";
} else {
    echo "  Errors: " . implode(', ', $result['errors'] ?? ['Unknown error']) . "\n";
}

// Check the updated friendship status
$sql = "SELECT * FROM friendships WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$friendshipId]);
$updatedFriendship = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nFriendship status after controller method:\n";
if ($updatedFriendship) {
    echo "  ID: {$updatedFriendship['id']}\n";
    echo "  From: {$user1['id']}\n";
    echo "  To: {$user2['id']}\n";
    echo "  Status: {$updatedFriendship['status']}\n";
} else {
    echo "  FRIENDSHIP NOT FOUND! It may have been deleted.\n";
}

// Step 4: If still pending, attempt direct SQL update
if ($updatedFriendship && $updatedFriendship['status'] === 'pending') {
    echo "\nStep 4: Attempting direct SQL update...\n";
    
    $sql = "UPDATE friendships SET status = 'accepted' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([$friendshipId]);
    
    echo "  SQL Update result: " . ($success ? "Success" : "Failed") . "\n";
    echo "  Rows affected: " . $stmt->rowCount() . "\n";
    
    // Check the status again
    $sql = "SELECT status FROM friendships WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$friendshipId]);
    $finalStatus = $stmt->fetchColumn();
    
    echo "  Final friendship status: " . ($finalStatus ?: "Not found") . "\n";
}

// Step 5: Cleanup
echo "\nStep 5: Cleaning up test data...\n";
$sql = "DELETE FROM friendships WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$friendshipId]);

$sql = "DELETE FROM users WHERE id IN (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$user1['id'], $user2['id']]);

echo "Test users and friendship records removed.\n";
echo "\nTest completed.\n";

// Helper function to create a test user
function createTestUser($conn, $username) {
    $email = $username . "@example.com";
    $password = password_hash("testpassword", PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username, $email, $password]);
    
    return [
        'id' => $conn->lastInsertId(),
        'username' => $username,
        'email' => $email
    ];
}
?> 