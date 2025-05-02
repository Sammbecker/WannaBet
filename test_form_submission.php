<?php
session_start();
require_once __DIR__ . '/app/config/db.php';
require_once __DIR__ . '/app/controllers/FriendshipController.php';
require_once __DIR__ . '/app/models/User.php';

echo "=== Testing Friend Request Form Submission ===\n\n";

// Connect to database
$conn = getDB();

// First, create a new test user to add as a friend
echo "Creating two test users...\n";
$testUsername1 = "testuser1_" . time();
$testEmail1 = "test1_" . time() . "@example.com";
$testPassword1 = password_hash("testpassword", PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$testUsername1, $testEmail1, $testPassword1]);
$testUserId1 = $conn->lastInsertId();

$testUsername2 = "testuser2_" . time();
$testEmail2 = "test2_" . time() . "@example.com";
$testPassword2 = password_hash("testpassword", PASSWORD_DEFAULT);

$stmt->execute([$testUsername2, $testEmail2, $testPassword2]);
$testUserId2 = $conn->lastInsertId();

echo "Created test user 1: ID=$testUserId1, Username=$testUsername1\n";
echo "Created test user 2: ID=$testUserId2, Username=$testUsername2\n\n";

// Set user 1 as the active user
$_SESSION['user_id'] = $testUserId1;
$_SESSION['username'] = $testUsername1;
echo "Active user: " . $_SESSION['username'] . " (ID: " . $_SESSION['user_id'] . ")\n\n";

// Test sending a friend request from user 1 to user 2
echo "Step 1: Sending friend request from user 1 to user 2\n";
echo "====================================\n";

// Simulate form data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['recipient_id'] = $testUserId2;
$_POST['send_request'] = true;

// Create controller and call method
$controller = new FriendshipController();
$result = $controller->sendRequest();

// Display result
echo "Success: " . ($result['success'] ? 'true' : 'false') . "\n";
if ($result['success']) {
    echo "Message: " . $result['message'] . "\n";
} else {
    echo "Errors: " . implode(', ', $result['errors'] ?? ['Unknown error']) . "\n";
}

// Check the friendship record that was created
$sql = "SELECT * FROM friendships WHERE user_id = ? AND friend_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$testUserId1, $testUserId2]);
$friendship = $stmt->fetch(PDO::FETCH_ASSOC);

if ($friendship) {
    echo "Friendship record created:\n";
    echo "  ID: " . $friendship['id'] . "\n";
    echo "  User ID (sender): " . $friendship['user_id'] . "\n";
    echo "  Friend ID (recipient): " . $friendship['friend_id'] . "\n";
    echo "  Status: " . $friendship['status'] . "\n";
    
    $friendshipId = $friendship['id'];
} else {
    echo "No friendship record found!\n";
    $friendshipId = 0;
}

// Clean up POST data
unset($_POST['recipient_id']);
unset($_POST['send_request']);

echo "\n";

// Now switch to user 2 to accept the request
echo "Step 2: Switching to user 2 to accept the request\n";
echo "====================================\n";
$_SESSION['user_id'] = $testUserId2;
$_SESSION['username'] = $testUsername2;
echo "Active user: " . $_SESSION['username'] . " (ID: " . $_SESSION['user_id'] . ")\n";

// Directly check for pending friendship requests
$sql = "SELECT * FROM friendships WHERE friend_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute([$testUserId2]);
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($pendingRequests) . " pending requests for user 2.\n";
foreach ($pendingRequests as $index => $request) {
    echo "Request " . ($index + 1) . ":\n";
    echo "  ID: " . $request['id'] . "\n";
    echo "  From User ID: " . $request['user_id'] . "\n";
    echo "  To Friend ID: " . $request['friend_id'] . "\n";
    echo "  Status: " . $request['status'] . "\n";
}

if (empty($pendingRequests)) {
    echo "No pending requests found. Cannot proceed with acceptance test.\n";
} else {
    // Get the first pending request
    $pendingRequest = $pendingRequests[0];
    $requestId = $pendingRequest['id'];
    
    echo "\nAccepting request ID: $requestId\n";
    
    // Simulate form data for accepting
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['request_id'] = $requestId;
    $_POST['response'] = 'accept';
    $_POST['respond_request'] = true;
    
    // Call controller method
    $result = $controller->respondToRequest();
    
    // Display result
    echo "Success: " . ($result['success'] ? 'true' : 'false') . "\n";
    if ($result['success']) {
        echo "Message: " . $result['message'] . "\n";
    } else {
        echo "Errors: " . implode(', ', $result['errors'] ?? ['Unknown error']) . "\n";
        
        // Add debug - check if we can update directly through SQL
        echo "\nFallback to direct SQL update...\n";
        $sql = "UPDATE friendships SET status = 'accepted' WHERE id = ?";
        $directStmt = $conn->prepare($sql);
        $directSuccess = $directStmt->execute([$requestId]);
        echo "Direct SQL update result: " . ($directSuccess ? "Success" : "Failed") . "\n";
        echo "Rows affected: " . $directStmt->rowCount() . "\n";
    }
    
    // Verify the friendship status
    $sql = "SELECT * FROM friendships WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$requestId]);
    $updatedFriendship = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($updatedFriendship) {
        echo "Updated friendship record:\n";
        echo "  ID: " . $updatedFriendship['id'] . "\n";
        echo "  User ID (sender): " . $updatedFriendship['user_id'] . "\n";
        echo "  Friend ID (recipient): " . $updatedFriendship['friend_id'] . "\n";
        echo "  Status: " . $updatedFriendship['status'] . "\n";
    } else {
        echo "Friendship record not found after response!\n";
    }
}

// Clean up test users and their relationships
echo "\nCleaning up test data...\n";
$sql = "DELETE FROM friendships WHERE (user_id = ? OR friend_id = ? OR user_id = ? OR friend_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$testUserId1, $testUserId1, $testUserId2, $testUserId2]);

$sql = "DELETE FROM users WHERE id = ? OR id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$testUserId1, $testUserId2]);
echo "Test users and friendship records removed.\n";

echo "\nTest completed.\n";
?> 