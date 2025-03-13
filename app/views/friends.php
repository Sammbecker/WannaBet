<?php
session_start();
require_once __DIR__ . '/../controllers/FriendshipController.php';
require_once __DIR__ . '/../utils/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize controller
$friendshipController = new FriendshipController();

// Get data for different sections
$potentialFriendsResult = $friendshipController->getPotentialFriends();
$friendsResult = $friendshipController->getFriends();
$pendingRequestsResult = $friendshipController->getPendingRequests();

// Extract data
$potentialFriends = $potentialFriendsResult['success'] ? $potentialFriendsResult['users'] : [];
$friends = $friendsResult['success'] ? $friendsResult['friends'] : [];
$receivedRequests = $pendingRequestsResult['success'] ? $pendingRequestsResult['received_requests'] : [];
$sentRequests = $pendingRequestsResult['success'] ? $pendingRequestsResult['sent_requests'] : [];

// Handle friend request submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_request'])) {
        $result = $friendshipController->sendRequest();
        if ($result['success']) {
            $successMessage = $result['message'];
            // Refresh the page to update lists
            header('Refresh: 2');
        } else {
            $errorMessage = implode(', ', $result['errors'] ?? ['An error occurred']);
        }
    } elseif (isset($_POST['respond_request'])) {
        $result = $friendshipController->respondToRequest();
        if ($result['success']) {
            $successMessage = $result['message'];
            // Refresh the page to update lists
            header('Refresh: 2');
        } else {
            $errorMessage = implode(', ', $result['errors'] ?? ['An error occurred']);
        }
    } elseif (isset($_POST['remove_friend'])) {
        $result = $friendshipController->removeFriend();
        if ($result['success']) {
            $successMessage = $result['message'];
            // Refresh the page to update lists
            header('Refresh: 2');
        } else {
            $errorMessage = implode(', ', $result['errors'] ?? ['An error occurred']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - WannaBet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --background-color: #f9f9f9;
            --card-bg: #ffffff;
            --text-color: #111111;
            --text-light: #555555;
            --border-color: #eeeeee;
            --accent-color: #000000;
            --hover-accent: #333333;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.05), 0 4px 6px rgba(0,0,0,0.05);
            --gradient-black: linear-gradient(145deg, #000000, #222222);
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --info-color: #3b82f6;
        }

        /* Dark mode colors */
        [data-theme="dark"] {
            --primary-color: #ffffff;
            --secondary-color: #cccccc;
            --background-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #f3f4f6;
            --text-light: #d1d5db;
            --border-color: #2e2e2e;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.4), 0 1px 3px rgba(0,0,0,0.3);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.4), 0 4px 6px rgba(0,0,0,0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1, h2, h3 {
            color: var(--primary-color);
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
        }

        h2 {
            font-size: 20px;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 5px;
        }

        h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: var(--success-color);
            color: white;
        }

        .alert-error {
            background-color: var(--error-color);
            color: white;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 15px;
            box-shadow: var(--shadow-md);
        }

        .card-body {
            margin-bottom: 15px;
        }

        .card-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .card-text {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        button, .btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        button:hover, .btn:hover {
            background: var(--hover-accent);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-success {
            background: var(--success-color);
        }

        .btn-success:hover {
            background: #0ea573;
        }

        .btn-danger {
            background: var(--error-color);
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-primary {
            background: var(--primary-color);
            color: white;
        }

        .badge-warning {
            background: var(--warning-color);
            color: white;
        }

        .nav-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .nav-tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .nav-tab.active {
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }

        .tab-content {
            margin-bottom: 30px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background: var(--card-bg);
            color: var(--text-color);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .empty-state {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .card-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Friends</h1>
            <a href="home.php" class="btn btn-outline">Back to Home</a>
        </header>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="nav-tabs">
            <div class="nav-tab active" data-tab="tab-find">Find Friends</div>
            <div class="nav-tab" data-tab="tab-current">My Friends</div>
            <div class="nav-tab" data-tab="tab-requests">Friend Requests</div>
        </div>

        <div class="tab-content">
            <!-- Find Friends Tab -->
            <div class="tab-pane active" id="tab-find">
                <div class="search-box">
                    <input type="text" id="search-users" placeholder="Search users by username or email...">
                </div>

                <?php if (empty($potentialFriends)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>No more users to add as friends</p>
                    </div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($potentialFriends as $user): ?>
                            <div class="card user-card">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h3>
                                    <p class="card-text"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <div class="card-actions">
                                    <form method="POST">
                                        <input type="hidden" name="friend_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" name="send_request" class="btn">Add Friend</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- My Friends Tab -->
            <div class="tab-pane" id="tab-current">
                <?php if (empty($friends)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-friends"></i>
                        <p>You don't have any friends yet</p>
                    </div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($friends as $friend): ?>
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo htmlspecialchars($friend['username']); ?></h3>
                                    <p class="card-text"><?php echo htmlspecialchars($friend['email']); ?></p>
                                </div>
                                <div class="card-actions">
                                    <a href="create_bet.php?friend_id=<?php echo $friend['user_id']; ?>" class="btn btn-success">Create Bet</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="friend_id" value="<?php echo $friend['user_id']; ?>">
                                        <button type="submit" name="remove_friend" class="btn btn-danger">Remove</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Friend Requests Tab -->
            <div class="tab-pane" id="tab-requests">
                <h2>Pending Requests</h2>
                <?php if (empty($receivedRequests) && empty($sentRequests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-envelope-open"></i>
                        <p>No pending friend requests</p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($receivedRequests)): ?>
                        <h3>Requests Received</h3>
                        <div class="card-grid">
                            <?php foreach ($receivedRequests as $request): ?>
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title"><?php echo htmlspecialchars($request['username']); ?></h3>
                                        <p class="card-text"><?php echo htmlspecialchars($request['email']); ?></p>
                                        <p class="card-text">
                                            <small>Sent on: <?php echo date('M j, Y', strtotime($request['request_date'])); ?></small>
                                        </p>
                                    </div>
                                    <div class="card-actions">
                                        <form method="POST">
                                            <input type="hidden" name="friendship_id" value="<?php echo $request['friendship_id']; ?>">
                                            <input type="hidden" name="response" value="accepted">
                                            <button type="submit" name="respond_request" class="btn btn-success">Accept</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="friendship_id" value="<?php echo $request['friendship_id']; ?>">
                                            <input type="hidden" name="response" value="rejected">
                                            <button type="submit" name="respond_request" class="btn btn-danger">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($sentRequests)): ?>
                        <h3>Requests Sent</h3>
                        <div class="card-grid">
                            <?php foreach ($sentRequests as $request): ?>
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title"><?php echo htmlspecialchars($request['username']); ?></h3>
                                        <p class="card-text"><?php echo htmlspecialchars($request['email']); ?></p>
                                        <p class="card-text">
                                            <small>Sent on: <?php echo date('M j, Y', strtotime($request['request_date'])); ?></small>
                                        </p>
                                        <span class="badge badge-warning">Pending</span>
                                    </div>
                                    <div class="card-actions">
                                        <form method="POST">
                                            <input type="hidden" name="friend_id" value="<?php echo $request['user_id']; ?>">
                                            <button type="submit" name="remove_friend" class="btn btn-danger">Cancel Request</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab navigation
            const tabs = document.querySelectorAll('.nav-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all tab panes
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('active');
                    });
                    
                    // Show the corresponding tab pane
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Search functionality
            const searchInput = document.getElementById('search-users');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const userCards = document.querySelectorAll('.user-card');
                    
                    userCards.forEach(card => {
                        const username = card.querySelector('.card-title').textContent.toLowerCase();
                        const email = card.querySelector('.card-text').textContent.toLowerCase();
                        
                        if (username.includes(searchTerm) || email.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html> 