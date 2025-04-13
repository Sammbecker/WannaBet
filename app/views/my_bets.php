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
    header('Location: /WannaBet/login');
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

        .header-actions {
            display: flex;
            gap: 10px;
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
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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

        .card-details {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
        }

        .card-detail {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-detail-label {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .card-detail-value {
            font-weight: 600;
            font-size: 16px;
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

        .badge-success {
            background: var(--success-color);
            color: white;
        }

        .badge-danger {
            background: var(--error-color);
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
            
            .header-actions {
                flex-direction: column;
            }
        }

        .btn-sm {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            margin-left: 0.5rem;
            border-radius: 0.25rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: var(--card-bg);
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            position: relative;
            box-shadow: var(--shadow-lg);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-light);
        }

        .close:hover {
            color: var(--text-color);
        }

        .info-btn {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }

        .info-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .modal-section {
            margin-bottom: 20px;
        }

        .modal-section h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .modal-section p {
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .step-list {
            list-style-type: none;
            padding: 0;
        }

        .step-list li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
        }

        .step-list li:before {
            content: "•";
            position: absolute;
            left: 10px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div style="display: flex; align-items: center;">
                <h1>My Bets</h1>
                <button class="info-btn" onclick="openModal()">
                    <i class="fas fa-info"></i>
                </button>
            </div>
            <div class="header-actions">
                <a href="create_bet.php" class="btn">Create New Bet</a>
                <a href="documentation.php" class="btn btn-outline">Documentation</a>
                <a href="home.php" class="btn btn-outline">Back to Home</a>
            </div>
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
            <div class="nav-tab active" data-tab="tab-active">Active Bets</div>
            <div class="nav-tab" data-tab="tab-pending">Pending Bets</div>
            <div class="nav-tab" data-tab="tab-completed">Completed Bets</div>
        </div>

        <div class="tab-content">
            <!-- Active Bets Tab -->
            <div class="tab-pane active" id="tab-active">
                <?php if (empty($createdBets)): ?>
                    <div class="empty-state">
                        <i class="fas fa-running"></i>
                        <p>No active bets at the moment</p>
                    </div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($createdBets as $bet): ?>
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo htmlspecialchars($bet['title']); ?></h3>
                                    <p class="card-text"><?php echo htmlspecialchars($bet['description']); ?></p>
                                    <p class="card-text">
                                        <small>Stake: <?php echo htmlspecialchars($bet['stake_type']); ?></small>
                                    </p>
                                    <span class="badge badge-primary">Active</span>
                                </div>
                                <div class="card-actions">
                                    <a href="/WannaBet/bet/<?php echo $bet['id']; ?>" class="btn">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pending Bets Tab -->
            <div class="tab-pane" id="tab-pending">
                <?php if (empty($pendingNotifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <p>No pending bets</p>
                    </div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($pendingNotifications as $notification): ?>
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title">
                                        <span>Bet from <?php echo htmlspecialchars($notification['creator_username']); ?></span>
                                        <span class="badge badge-warning">Pending</span>
                                    </div>
                                    <p class="card-text"><?php echo htmlspecialchars($notification['description']); ?></p>
                                    
                                    <div class="card-details">
                                        <div class="card-detail">
                                            <span class="card-detail-label">Stake</span>
                                            <span class="card-detail-value">
                                                <?php if ($notification['type'] === 'bet_invitation'): ?>
                                                    <?php echo htmlspecialchars($notification['stake_display']); ?>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($notification['stake_description']); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="card-detail">
                                            <span class="card-detail-label">Deadline</span>
                                            <span class="card-detail-value"><?php echo date('M j, Y', strtotime($notification['deadline'])); ?></span>
                                        </div>
                                    </div>

                                    <?php if ($notification['type'] === 'bet_invitation'): ?>
                                        <div class="notification-details">
                                            <p class="notification-text"><?php echo htmlspecialchars($notification['notification_text']); ?></p>
                                            <p class="stake-info">
                                                <strong>Stake:</strong> 
                                                <?php echo htmlspecialchars($notification['stake_display']); ?>
                                            </p>
                                            <p class="deadline-info">
                                                <strong>Deadline:</strong> 
                                                <?php echo date('M j, Y', strtotime($notification['deadline'])); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-actions">
                                    <form method="POST" class="bet-response-form">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                        <input type="hidden" name="response" value="accepted">
                                        <button type="submit" name="respond_bet" class="btn btn-success">Accept</button>
                                    </form>
                                    <form method="POST" class="bet-response-form">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                        <input type="hidden" name="response" value="rejected">
                                        <button type="submit" name="respond_bet" class="btn btn-danger">Reject</button>
                                    </form>
                                </div>

                                <script>
                                    document.querySelectorAll('.bet-response-form').forEach(form => {
                                        form.addEventListener('submit', async (e) => {
                                            e.preventDefault();
                                            
                                            try {
                                                const formData = new FormData(e.target);
                                                formData.append('respond_bet', '1');
                                                
                                                // Display processing message
                                                const submitButton = e.target.querySelector('button[type="submit"]');
                                                const originalText = submitButton.innerHTML;
                                                submitButton.innerHTML = 'Processing...';
                                                submitButton.disabled = true;
                                                
                                                const response = await fetch(window.location.href, {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-Requested-With': 'XMLHttpRequest'
                                                    },
                                                    body: formData
                                                });
                                                
                                                if (!response.ok) {
                                                    throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                                                }
                                                
                                                // Get response content type
                                                const contentType = response.headers.get('Content-Type');
                                                let result;
                                                
                                                if (contentType && contentType.includes('application/json')) {
                                                    result = await response.json();
                                                } else {
                                                    const text = await response.text();
                                                    console.error('Received non-JSON response:', text.substring(0, 100));
                                                    throw new Error('Server did not return a valid JSON response');
                                                }
                                                
                                                // Reset button state
                                                submitButton.innerHTML = originalText;
                                                submitButton.disabled = false;
                                                
                                                console.log('Response:', result);
                                                
                                                if (result.success) {
                                                    if (result.payment_url) {
                                                        // For payment URL, we'll do a direct window location change
                                                        console.log('Redirecting to payment URL:', result.payment_url);
                                                        window.location.href = result.payment_url;
                                                        return; // Stop execution here
                                                    } else if (result.redirect_url) {
                                                        window.location.href = result.redirect_url;
                                                        return; // Stop execution here
                                                    } else {
                                                        alert(result.message || 'Bet response submitted successfully');
                                                        window.location.reload();
                                                    }
                                                } else {
                                                    const errorMessage = result.errors ? result.errors.join('\n') : 'An unknown error occurred';
                                                    alert('Error: ' + errorMessage);
                                                }
                                            } catch (error) {
                                                console.error('Error processing request:', error);
                                                alert('Error: ' + error.message);
                                                
                                                // Reset buttons on error too
                                                const submitButton = e.target.querySelector('button[type="submit"]');
                                                if (submitButton.disabled) {
                                                    submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'Submit';
                                                    submitButton.disabled = false;
                                                }
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Completed Bets Tab -->
            <div class="tab-pane" id="tab-completed">
                <?php if (empty($createdBets)): ?>
                    <div class="empty-state">
                        <i class="fas fa-flag-checkered"></i>
                        <p>No completed bets yet</p>
                    </div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($createdBets as $bet): ?>
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo htmlspecialchars($bet['title']); ?></h3>
                                    <p class="card-text"><?php echo htmlspecialchars($bet['description']); ?></p>
                                    <p class="card-text">
                                        <small>Stake: <?php echo htmlspecialchars($bet['stake_type']); ?></small>
                                    </p>
                                    <span class="badge <?php echo $bet['winner_id'] == $_SESSION['user_id'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $bet['winner_id'] == $_SESSION['user_id'] ? 'Won' : 'Lost'; ?>
                                    </span>
                                </div>
                                <div class="card-actions">
                                    <a href="/WannaBet/bet/<?php echo $bet['id']; ?>" class="btn">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- How It Works Modal -->
    <div id="howItWorksModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>How WannaBet Works</h2>
            
            <div class="modal-section">
                <h3>Types of Bets</h3>
                <p>WannaBet supports three types of bets:</p>
                <ul class="step-list">
                    <li><strong>One-on-One Money Bets:</strong> Bet with real money between two people</li>
                    <li><strong>Group Money Bets:</strong> Bet with multiple participants, everyone contributes to the pot</li>
                    <li><strong>Favor Bets:</strong> Bet with favors or tasks instead of money</li>
                </ul>
            </div>

            <div class="modal-section">
                <h3>Money Bet Process</h3>
                <ul class="step-list">
                    <li><strong>One-on-One Bets:</strong>
                        <ul>
                            <li>Creator and participant each pay their stake</li>
                            <li>Bet activates once both have paid</li>
                            <li>Winner receives both stakes</li>
                        </ul>
                    </li>
                    <li><strong>Group Bets:</strong>
                        <ul>
                            <li>All participants must accept the invitation</li>
                            <li>Each participant must pay their stake</li>
                            <li>Bet only activates when everyone has paid</li>
                            <li>Winner receives the entire pot (all stakes combined)</li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="modal-section">
                <h3>Payment Flow</h3>
                <ul class="step-list">
                    <li><strong>For One-on-One Bets:</strong>
                        <ul>
                            <li>1. Creator creates bet and pays their stake (now or later)</li>
                            <li>2. Participant accepts bet and pays their stake</li>
                            <li>3. Money is held securely during the bet</li>
                            <li>4. Winner is declared in the system</li>
                            <li>5. Both stakes are sent to the winner</li>
                        </ul>
                    </li>
                    <li><strong>For Group Bets:</strong>
                        <ul>
                            <li>1. Creator invites multiple participants</li>
                            <li>2. Each participant must accept and pay their stake</li>
                            <li>3. Bet remains pending until all have paid</li>
                            <li>4. All stakes are held securely during the bet</li>
                            <li>5. Winner receives the entire pot automatically</li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="modal-section">
                <h3>Safety & Security</h3>
                <ul class="step-list">
                    <li>All payments are processed securely through Paystack</li>
                    <li>Money is held in escrow until a winner is determined</li>
                    <li>Automatic payouts ensure fair distribution</li>
                    <li>All transactions are recorded and traceable</li>
                    <li>Group bets won't start until all participants are ready</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('howItWorksModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('howItWorksModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('howItWorksModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Add event listeners to all bet response forms
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.bet-response-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    formData.append('respond_bet', '1');
                    
                    // Show loading indicator
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton.innerHTML;
                    submitButton.innerHTML = 'Processing...';
                    submitButton.disabled = true;
                    
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        // First check if response is OK
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        
                        // Try to parse as JSON
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return response.json().catch(error => {
                                console.error('JSON parse error:', error);
                                throw new Error('Invalid JSON response from server');
                            });
                        } else {
                            // If not JSON, handle as text
                            return response.text().then(text => {
                                console.error('Received non-JSON response:', text.substring(0, 100) + '...');
                                throw new Error('Unexpected response format from server');
                            });
                        }
                    })
                    .then(data => {
                        // Reset button
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                        
                        if (data.success) {
                            // Show success message
                            const successAlert = document.createElement('div');
                            successAlert.className = 'alert alert-success';
                            successAlert.innerText = data.message || 'Response submitted successfully';
                            
                            // Insert before the form
                            form.parentNode.insertBefore(successAlert, form);
                            
                            // If there's a payment URL, redirect to it
                            if (data.payment_url) {
                                // Show message first
                                successAlert.innerText = 'Redirecting to payment page...';
                                setTimeout(() => {
                                    window.location.href = data.payment_url;
                                }, 1000);
                            } else if (data.redirect_url) {
                                // Show message first
                                successAlert.innerText = 'Redirecting...';
                                setTimeout(() => {
                                    window.location.href = data.redirect_url;
                                }, 1000);
                            } else {
                                // No redirect, just reload the page after a short delay
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            }
                        } else {
                            // Show error message
                            const errorAlert = document.createElement('div');
                            errorAlert.className = 'alert alert-error';
                            
                            // Create error message
                            if (data.errors && Array.isArray(data.errors)) {
                                errorAlert.innerText = data.errors.join(', ');
                            } else {
                                errorAlert.innerText = 'An error occurred. Please try again.';
                            }
                            
                            // Insert before the form
                            form.parentNode.insertBefore(errorAlert, form);
                        }
                    })
                    .catch(error => {
                        // Reset button
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                        
                        console.error('Error:', error);
                        
                        // Show a more descriptive error message
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-error';
                        errorAlert.innerText = 'Error processing request: ' + error.message;
                        
                        // Insert before the form
                        form.parentNode.insertBefore(errorAlert, form);
                    });
                });
            });
        });

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
        });
    </script>
</body>
</html> 