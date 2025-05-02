<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/BetController.php';
require_once __DIR__ . '/../utils/functions.php';

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Check if it's an AJAX request
$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Redirect if not logged in (only for non-AJAX requests)
if (!isset($_SESSION['user_id']) && !$isAjaxRequest) {
    header('Location: /login');
    exit();
} elseif (!isset($_SESSION['user_id']) && $isAjaxRequest) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'errors' => ['Not logged in']]);
    exit;
}

// Initialize controller before any processing
$betController = new BetController();

// Process form submission first before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['respond_bet'])) {
        $result = $betController->respondToBet();
        
        // If this is an AJAX request, return JSON response and exit
        if ($isAjaxRequest) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        
        // For non-AJAX requests, handle normally
        if ($result['success']) {
            if (isset($result['payment_url'])) {
                header('Location: ' . $result['payment_url']);
                exit;
            }
            $successMessage = $result['message'];
            // Refresh the page to update lists
            header('Refresh: 2');
        } else {
            $errorMessage = implode(', ', $result['errors'] ?? ['An error occurred']);
        }
    } elseif (isset($_POST['complete_bet'])) {
        $result = $betController->completeBet();
        if ($result['success']) {
            $successMessage = $result['message'];
            // Refresh the page to update lists
            header('Refresh: 2');
        } else {
            $errorMessage = implode(', ', $result['errors'] ?? ['An error occurred']);
        }
    }
}

// Get user's bets and notifications
$betsResult = $betController->getUserBets();
$notificationsResult = $betController->getPendingNotifications();

// Extract data
$createdBets = $betsResult['success'] ? $betsResult['created_bets'] : [];
$participatingBets = $betsResult['success'] ? $betsResult['participating_bets'] : [];
$pendingNotifications = $notificationsResult['success'] ? $notificationsResult['notifications'] : [];

// Helper function to display bet status
function getBetStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'badge-warning';
        case 'active':
            return 'badge-info';
        case 'completed':
            return 'badge-success';
        default:
            return 'badge-primary';
    }
}

// Don't proceed with HTML output for AJAX requests
if ($isAjaxRequest) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bets - WannaBet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/common.css">
    <style>
        /* My Bets page specific styles */
        .header-actions {
            display: flex;
            gap: 10px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            transition: all 0.3s;
        }

        .tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .bet-list {
            margin-bottom: 20px;
        }

        .bet-item {
            background: var(--card-background);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }

        .bet-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .bet-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .bet-title {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .bet-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .bet-details {
            margin-bottom: 15px;
        }

        .bet-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .bet-detail i {
            width: 16px;
            text-align: center;
        }

        .bet-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .notification-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: var(--danger-color);
            border-radius: 50%;
            margin-left: 5px;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-container {
            background: var(--card-background);
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-lg);
            transform: translateY(20px);
            transition: all 0.3s;
        }

        .modal-overlay.active .modal-container {
            transform: translateY(0);
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .tabs {
                flex-wrap: wrap;
            }
            
            .bet-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .bet-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .bet-actions .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="/home" class="logo">
                    <i class="fas fa-dice"></i>
                    WannaBet
                </a>
                <div class="header-actions">
                    <a href="/create_bet" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Bet
                    </a>
                    <a href="/home" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
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
            </div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" data-tab="all-bets">All Bets</div>
            <div class="tab" data-tab="my-created">Bets I Created</div>
            <div class="tab" data-tab="im-participating">Bets I'm Participating In</div>
            <div class="tab" data-tab="notifications">
                Notifications
                <?php if (count($pendingNotifications) > 0): ?>
                    <span class="notification-dot"></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- All Bets Tab -->
        <div class="tab-content active" id="all-bets">
            <?php if (empty($createdBets) && empty($participatingBets)): ?>
                <div class="empty-state">
                    <i class="fas fa-dice"></i>
                    <h3>No bets yet!</h3>
                    <p>Create a bet to challenge your friends.</p>
                    <a href="/create_bet" class="btn btn-primary">Create Your First Bet</a>
                </div>
            <?php else: ?>
                <div class="bet-list">
                    <?php 
                    // Combine and sort all bets by created date (newest first)
                    $allBets = array_merge($createdBets, $participatingBets);
                    usort($allBets, function($a, $b) {
                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                    });
                    
                    foreach ($allBets as $bet): 
                        $isCreator = $bet['creator_id'] == $_SESSION['user_id'];
                        $opponent = $isCreator ? $bet['opponent_username'] : $bet['creator_username'];
                    ?>
                        <div class="bet-item">
                            <div class="bet-header">
                                <h3 class="bet-title"><?php echo htmlspecialchars($bet['title'] ?? $bet['description']); ?></h3>
                                <span class="badge <?php echo getBetStatusClass($bet['status']); ?>">
                                    <?php echo ucfirst($bet['status']); ?>
                                </span>
                            </div>
                            <div class="bet-details">
                                <div class="bet-detail">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo $isCreator ? 'Against: ' : 'From: '; ?> <?php echo htmlspecialchars($opponent ?? ''); ?></span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-trophy"></i>
                                    <span>Stake: <?php echo htmlspecialchars($bet['stake_description'] ?? ''); ?></span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Deadline: <?php echo formatDate($bet['deadline'] ?? ''); ?></span>
                                </div>
                                <?php if ($bet['status'] === 'completed'): ?>
                                <div class="bet-detail">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Winner: <?php echo htmlspecialchars($bet['winner_username'] ?? 'Draw'); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="bet-actions">
                                <?php if ($bet['status'] === 'active'): ?>
                                    <form method="POST" action="/my_bets">
                                        <input type="hidden" name="bet_id" value="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                        <button type="submit" name="complete_bet" class="btn btn-primary">Complete Bet</button>
                                    </form>
                                <?php elseif ($bet['status'] === 'pending' && !$isCreator): ?>
                                    <form method="POST" action="/my_bets">
                                        <input type="hidden" name="bet_id" value="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                        <input type="hidden" name="response" value="accept">
                                        <button type="submit" name="respond_bet" class="btn btn-success">Accept</button>
                                    </form>
                                    <form method="POST" action="/my_bets">
                                        <input type="hidden" name="bet_id" value="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                        <input type="hidden" name="response" value="decline">
                                        <button type="submit" name="respond_bet" class="btn btn-danger">Decline</button>
                                    </form>
                                <?php endif; ?>
                                <button class="btn btn-outline view-details-btn" data-bet-id="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bets I Created Tab -->
        <div class="tab-content" id="my-created">
            <?php if (empty($createdBets)): ?>
                <div class="empty-state">
                    <i class="fas fa-hand-holding-usd"></i>
                    <h3>You haven't created any bets yet</h3>
                    <p>Start by challenging a friend to a bet!</p>
                    <a href="/create_bet" class="btn btn-primary">Create a Bet</a>
                </div>
            <?php else: ?>
                <div class="bet-list">
                    <?php foreach ($createdBets as $bet): ?>
                        <div class="bet-item">
                            <div class="bet-header">
                                <h3 class="bet-title"><?php echo htmlspecialchars($bet['title'] ?? $bet['description']); ?></h3>
                                <span class="badge <?php echo getBetStatusClass($bet['status']); ?>">
                                    <?php echo ucfirst($bet['status']); ?>
                                </span>
                            </div>
                            <div class="bet-details">
                                <div class="bet-detail">
                                    <i class="fas fa-user"></i>
                                    <span>Against: <?php echo htmlspecialchars($bet['opponent_username'] ?? ''); ?></span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-trophy"></i>
                                    <span>Stake: <?php echo htmlspecialchars($bet['stake_description'] ?? ''); ?></span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Deadline: <?php echo formatDate($bet['deadline'] ?? ''); ?></span>
                                </div>
                                <?php if ($bet['status'] === 'completed'): ?>
                                <div class="bet-detail">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Winner: <?php echo htmlspecialchars($bet['winner_username'] ?? 'Draw'); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="bet-actions">
                                <?php if ($bet['status'] === 'active'): ?>
                                    <form method="POST" action="/my_bets">
                                        <input type="hidden" name="bet_id" value="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                        <button type="submit" name="complete_bet" class="btn btn-primary">Complete Bet</button>
                                    </form>
                                <?php endif; ?>
                                <button class="btn btn-outline view-details-btn" data-bet-id="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bets I'm Participating In Tab -->
        <div class="tab-content" id="im-participating">
            <?php if (empty($participatingBets)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No bets to participate in yet</h3>
                    <p>You'll see bets your friends have invited you to here.</p>
                </div>
            <?php else: ?>
                <div class="bet-list">
                    <?php foreach ($participatingBets as $bet): ?>
                        <div class="bet-item">
                            <div class="bet-header">
                                <h3 class="bet-title"><?php echo htmlspecialchars($bet['title'] ?? $bet['description']); ?></h3>
                                <span class="badge <?php echo getBetStatusClass($bet['status']); ?>">
                                    <?php echo ucfirst($bet['status']); ?>
                                </span>
                            </div>
                            <div class="bet-details">
                                <div class="bet-detail">
                                    <i class="fas fa-user"></i>
                                    <span>From: <?php echo htmlspecialchars($bet['creator_username'] ?? ''); ?></span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-trophy"></i>
                                    <span>Stake: <?php echo htmlspecialchars($bet['stake_description'] ?? ''); ?></span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Deadline: <?php echo formatDate($bet['deadline'] ?? ''); ?></span>
                                </div>
                                <?php if ($bet['status'] === 'completed'): ?>
                                <div class="bet-detail">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Winner: <?php echo htmlspecialchars($bet['winner_username'] ?? 'Draw'); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="bet-actions">
                                <?php if ($bet['status'] === 'pending'): ?>
                                    <form method="POST" action="/my_bets">
                                        <input type="hidden" name="bet_id" value="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                        <input type="hidden" name="response" value="accept">
                                        <button type="submit" name="respond_bet" class="btn btn-success">Accept</button>
                                    </form>
                                    <form method="POST" action="/my_bets">
                                        <input type="hidden" name="bet_id" value="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                        <input type="hidden" name="response" value="decline">
                                        <button type="submit" name="respond_bet" class="btn btn-danger">Decline</button>
                                    </form>
                                <?php endif; ?>
                                <button class="btn btn-outline view-details-btn" data-bet-id="<?php echo $bet['bet_id'] ?? $bet['id'] ?? ''; ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notifications Tab -->
        <div class="tab-content" id="notifications">
            <?php if (empty($pendingNotifications)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No notifications</h3>
                    <p>You're all caught up!</p>
                </div>
            <?php else: ?>
                <div class="bet-list">
                    <?php foreach ($pendingNotifications as $notification): ?>
                        <div class="bet-item">
                            <div class="bet-header">
                                <h3 class="bet-title"><?php echo htmlspecialchars($notification['title'] ?? ''); ?></h3>
                            </div>
                            <div class="bet-details">
                                <div class="bet-detail">
                                    <i class="<?php echo $notification['icon'] ?? 'fas fa-bell'; ?>"></i>
                                    <span><?php echo htmlspecialchars($notification['message'] ?? ''); ?></span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo formatDate($notification['created_at'] ?? ''); ?></span>
                                </div>
                            </div>
                            <div class="bet-actions">
                                <?php if (isset($notification['actions'])): ?>
                                    <?php foreach ($notification['actions'] as $action): ?>
                                        <form method="POST" action="<?php echo $action['url']; ?>">
                                            <?php foreach ($action['params'] as $key => $value): ?>
                                                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                                            <?php endforeach; ?>
                                            <button type="submit" class="btn <?php echo $action['class']; ?>">
                                                <?php echo $action['label']; ?>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for bet details -->
    <div class="modal-overlay" id="betDetailsModal">
        <div class="modal-container">
            <button class="close-modal" onclick="closeModal()">&times;</button>
            <div id="modalContent">
                <!-- Content will be dynamically populated -->
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelector('.tab.active').classList.remove('active');
                this.classList.add('active');
                
                // Update active content
                const tabId = this.getAttribute('data-tab');
                document.querySelector('.tab-content.active').classList.remove('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Modal functionality
        function openModal() {
            document.getElementById('betDetailsModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('betDetailsModal').classList.remove('active');
        }
        
        // View details button click
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const betId = this.getAttribute('data-bet-id');
                const modalContent = document.getElementById('modalContent');
                
                // Fetch bet details or populate from existing data
                modalContent.innerHTML = `
                    <h2>Loading bet details...</h2>
                    <p>Please wait...</p>
                `;
                
                // In a real app, you would fetch the details via AJAX
                // For demo, we'll simulate a delay
                setTimeout(() => {
                    // Find the bet from our data
                    const allBets = <?php echo json_encode(array_merge($createdBets, $participatingBets)); ?>;
                    console.log("Looking for bet ID:", betId);
                    console.log("Available bets:", allBets);
                    
                    // Try to find the bet by comparing as strings to ensure proper matching
                    const bet = allBets.find(b => {
                        // Check for both bet_id and id fields
                        const objectId = b.bet_id || b.id;
                        return String(objectId) === String(betId);
                    });
                    
                    if (bet) {
                        console.log("Found bet:", bet);
                        // Safely access properties with optional chaining or nullish coalescing
                        modalContent.innerHTML = `
                            <h2>${bet.title || bet.description || 'Bet Details'}</h2>
                            <div class="bet-details">
                                <div class="bet-detail">
                                    <i class="fas fa-user"></i>
                                    <span>Creator: ${bet.creator_username || 'Unknown'}</span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-user"></i>
                                    <span>Opponent: ${bet.opponent_username || 'Unknown'}</span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-trophy"></i>
                                    <span>Stake: ${bet.stake_description || bet.stake_amount || 'Not specified'}</span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Deadline: ${bet.deadline || 'Not specified'}</span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Status: ${bet.status || 'Unknown'}</span>
                                </div>
                                <div class="bet-detail">
                                    <i class="fas fa-clock"></i>
                                    <span>Created: ${bet.created_at || 'Unknown'}</span>
                                </div>
                                ${bet.status === 'completed' ? 
                                    `<div class="bet-detail">
                                        <i class="fas fa-trophy"></i>
                                        <span>Winner: ${bet.winner_username || 'Draw'}</span>
                                    </div>` : ''}
                            </div>
                            <div class="modal-actions">
                                <button class="btn btn-outline" onclick="closeModal()">Close</button>
                            </div>
                        `;
                    } else {
                        modalContent.innerHTML = `
                            <h2>Error</h2>
                            <p>Could not find bet details.</p>
                            <div class="modal-actions">
                                <button class="btn btn-outline" onclick="closeModal()">Close</button>
                            </div>
                        `;
                    }
                }, 500);
                
                openModal();
            });
        });
    </script>
</body>
</html> 