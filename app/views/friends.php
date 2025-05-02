<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/FriendshipController.php';
require_once __DIR__ . '/../utils/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
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
    <link rel="stylesheet" href="/css/common.css">
    <style>
        /* Friends page specific styles */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .card-text {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
        }

        .empty-state {
            background: var(--card-background);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            color: var(--text-light);
            margin-bottom: 40px;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .card-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }

        .badge-primary {
            background: var(--primary-color);
            color: white;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .user-initial {
            text-transform: uppercase;
        }

        @media (max-width: 768px) {
            .card-grid {
                grid-template-columns: 1fr;
            }
            
            .card-actions {
                flex-direction: column;
            }
            
            .card-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="/home" class="logo">
                    <i class="fas fa-user-friends"></i>
                    WannaBet
                </a>
                <div class="nav-links">
                    <a href="/home">Dashboard</a>
                    <a href="/my_bets">My Bets</a>
                    <a href="/create_bet" class="btn btn-primary">Create Bet</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($errorMessage); ?>
                <?php if (isset($_POST) && !empty($_POST)): ?>
                    <div style="margin-top: 10px; font-size: 0.8em;">
                        <strong>Debug Info:</strong> 
                        <?php foreach ($_POST as $key => $value): ?>
                            <?php echo htmlspecialchars($key) . ': ' . htmlspecialchars($value) . '; '; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="section-header">
            <h1>My Friends</h1>
        </div>

        <?php if (empty($friends)): ?>
            <div class="empty-state">
                <i class="fas fa-user-friends"></i>
                <h3>You don't have any friends yet</h3>
                <p>Send friend requests to start connecting!</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($friends as $friend): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="user-avatar">
                                <span class="user-initial"><?php echo substr($friend['username'], 0, 1); ?></span>
                            </div>
                            <h3 class="card-title"><?php echo htmlspecialchars($friend['username']); ?></h3>
                            <p class="card-text"><?php echo htmlspecialchars($friend['email']); ?></p>
                            <div class="card-actions">
                                <a href="/create_bet?friend_id=<?php echo $friend['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Bet
                                </a>
                                <form method="POST" action="/friends">
                                    <input type="hidden" name="friend_id" value="<?php echo $friend['id']; ?>">
                                    <button type="submit" name="remove_friend" class="btn btn-outline">
                                        <i class="fas fa-user-minus"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="section-header">
            <h2>Find New Friends</h2>
        </div>

        <div class="search-box">
            <input type="text" id="userSearchInput" placeholder="Search for users...">
            <button class="btn btn-primary" id="searchButton">Search</button>
        </div>

        <div id="potentialFriends" class="card-grid">
            <?php if (empty($potentialFriends)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No users found</h3>
                    <p>Try searching for someone else.</p>
                </div>
            <?php else: ?>
                <?php foreach ($potentialFriends as $user): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="user-avatar">
                                <span class="user-initial"><?php echo substr($user['username'], 0, 1); ?></span>
                            </div>
                            <h3 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h3>
                            <p class="card-text"><?php echo htmlspecialchars($user['email']); ?></p>
                            <div class="card-actions">
                                <form method="POST" action="/friends">
                                    <input type="hidden" name="recipient_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="send_request" class="btn btn-primary">
                                        <i class="fas fa-user-plus"></i> Add Friend
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($receivedRequests)): ?>
            <div class="section-header">
                <h2>Friend Requests</h2>
                <span class="card-badge badge-primary"><?php echo count($receivedRequests); ?></span>
            </div>

            <div class="card-grid">
                <?php foreach ($receivedRequests as $request): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="user-avatar">
                                <span class="user-initial"><?php echo substr($request['username'], 0, 1); ?></span>
                            </div>
                            <h3 class="card-title"><?php echo htmlspecialchars($request['username']); ?></h3>
                            <p class="card-text"><?php echo htmlspecialchars($request['email']); ?></p>
                            <p class="card-text">
                                <small>Received: <?php echo formatDate($request['request_date']); ?></small>
                            </p>
                            <div class="card-actions">
                                <form method="POST" action="/friends">
                                    <input type="hidden" name="request_id" value="<?php echo $request['friendship_id']; ?>">
                                    <input type="hidden" name="response" value="accept">
                                    <button type="submit" name="respond_request" class="btn btn-success">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                </form>
                                <form method="POST" action="/friends">
                                    <input type="hidden" name="request_id" value="<?php echo $request['friendship_id']; ?>">
                                    <input type="hidden" name="response" value="reject">
                                    <button type="submit" name="respond_request" class="btn btn-danger">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sentRequests)): ?>
            <div class="section-header">
                <h2>Sent Requests</h2>
            </div>

            <div class="card-grid">
                <?php foreach ($sentRequests as $request): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="user-avatar">
                                <span class="user-initial"><?php echo substr($request['username'], 0, 1); ?></span>
                            </div>
                            <h3 class="card-title"><?php echo htmlspecialchars($request['username']); ?></h3>
                            <p class="card-text"><?php echo htmlspecialchars($request['email']); ?></p>
                            <p class="card-text">
                                <small>Sent: <?php echo formatDate($request['request_date']); ?></small>
                            </p>
                            <div class="card-actions">
                                <form method="POST" action="/friends">
                                    <input type="hidden" name="request_id" value="<?php echo $request['friendship_id']; ?>">
                                    <input type="hidden" name="response" value="cancel">
                                    <button type="submit" name="respond_request" class="btn btn-outline">
                                        <i class="fas fa-times"></i> Cancel Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Simple search implementation
        document.getElementById('searchButton').addEventListener('click', function() {
            const searchQuery = document.getElementById('userSearchInput').value.toLowerCase();
            const cards = document.querySelectorAll('#potentialFriends .card');
            
            let foundAny = false;
            
            cards.forEach(card => {
                const username = card.querySelector('.card-title').textContent.toLowerCase();
                const email = card.querySelector('.card-text').textContent.toLowerCase();
                
                if (username.includes(searchQuery) || email.includes(searchQuery)) {
                    card.style.display = 'block';
                    foundAny = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show empty state if no results
            const emptyState = document.querySelector('#potentialFriends .empty-state');
            if (emptyState) {
                emptyState.style.display = foundAny ? 'none' : 'block';
            }
        });

        // Allow searching by pressing Enter
        document.getElementById('userSearchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });
    </script>
</body>
</html> 