<?php
session_start();
require_once __DIR__ . '/../controllers/BetController.php';
require_once __DIR__ . '/../controllers/FriendshipController.php';
require_once __DIR__ . '/../utils/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /WannaBet/login');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize controllers
$betController = new BetController();
$friendshipController = new FriendshipController();

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $result = $betController->createBet();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}

// Get friend ID from URL parameter
$preselectedFriendId = isset($_GET['friend_id']) ? intval($_GET['friend_id']) : 0;

// Get list of friends
$friendsResult = $friendshipController->getFriends();
$friends = $friendsResult['success'] ? $friendsResult['friends'] : [];

// Check if user has friends
$hasFriends = !empty($friends);

// Handle regular form submission (non-AJAX)
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $result = $betController->createBet();
    
    if ($result['success']) {
        $successMessage = $result['message'];
        // Redirect to home page after short delay
        header('Refresh: 2; URL=home.php');
    } else {
        $errorMessage = implode(', ', $result['errors'] ?? ['An error occurred']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a New Bet - WannaBet</title>
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
            max-width: 800px;
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

        .card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow-md);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            background-color: var(--card-bg);
            color: var(--text-color);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button, .btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
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

        .btn-block {
            display: block;
            width: 100%;
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

        .required {
            color: var(--error-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
        }

        .stake-type-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stake-type-option {
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }

        .stake-type-option.active {
            border-color: var(--accent-color);
            background-color: var(--accent-color-light);
        }

        .stake-type-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--accent-color);
        }

        .stake-input-container {
            margin-top: 1rem;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            background: #4CAF50;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .shake {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .payment-options {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .payment-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            background-color: var(--hover-color);
        }

        .payment-option input[type="radio"] {
            margin: 0;
        }

        .payment-option input[type="radio"]:checked + span {
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Create a New Bet</h1>
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

        <?php if (!$hasFriends): ?>
            <div class="empty-state">
                <i class="fas fa-user-friends"></i>
                <p>You need friends to create a bet!</p>
                <p>Go to the <a href="friends.php">Friends page</a> to add some friends first.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <form method="POST" id="betForm" action="create_bet.php">
                    <div class="form-group">
                        <label for="opponent_id">Choose a Friend <span class="required">*</span></label>
                        <select id="opponent_id" name="opponent_id" required>
                            <option value="">Select a friend</option>
                            <?php foreach ($friends as $friend): ?>
                                <option value="<?php echo $friend['user_id']; ?>" <?php echo ($preselectedFriendId == $friend['user_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($friend['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Bet Description <span class="required">*</span></label>
                        <textarea id="description" name="description" required placeholder="Describe what you're betting on..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>What's at Stake? <span class="required">*</span></label>
                        <div class="stake-type-selector">
                            <div class="stake-type-option active" data-type="money">
                                <i class="fas fa-dollar-sign"></i>
                                <div>Money</div>
                            </div>
                            <div class="stake-type-option" data-type="favor">
                                <i class="fas fa-handshake"></i>
                                <div>Favor/Task</div>
                            </div>
                        </div>
                        <input type="hidden" name="stake_type" id="stake_type" value="money">
                        
                        <div id="money-stake-input" class="stake-input-container">
                            <div class="form-group">
                                <label for="stake_amount">Amount (R) <span class="required">*</span></label>
                                <input type="number" id="stake_amount" name="stake_amount" step="0.01" min="0" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Bet Type</label>
                                <div class="payment-options">
                                    <label class="payment-option">
                                        <input type="radio" name="bet_type" value="secured" checked>
                                        <span>Secured Bet</span>
                                    </label>
                                    <label class="payment-option">
                                        <input type="radio" name="bet_type" value="friendly">
                                        <span>Friendly Bet</span>
                                    </label>
                                </div>
                                <small style="display: block; margin-top: 8px; color: var(--text-light);">
                                    <strong>Secured Bet:</strong> Both parties must pay the stake amount upfront. Winner receives both payments.<br>
                                    <strong>Friendly Bet:</strong> No upfront payment required. Honor system between friends.<br>
                                    <em>(Note: This is a demo using test payment endpoints)</em>
                                </small>
                            </div>
                            <div id="secured-bet-options" class="form-group">
                                <label>Payment Timing</label>
                                <div class="payment-options">
                                    <label class="payment-option">
                                        <input type="radio" name="payment_preference" value="pay_now" checked>
                                        <span>Pay Now</span>
                                    </label>
                                    <label class="payment-option">
                                        <input type="radio" name="payment_preference" value="pay_later">
                                        <span>Pay Later</span>
                                    </label>
                                </div>
                                <small style="display: block; margin-top: 8px; color: var(--text-light);">
                                    You must pay before the bet can be activated. Your friend will need to pay when accepting the bet.
                                </small>
                            </div>
                        </div>
                        
                        <div id="favor-stake-input" class="stake-input-container" style="display: none;">
                            <label for="stake_description">Describe the Favor/Task <span class="required">*</span></label>
                            <textarea id="stake_description" name="stake_description" placeholder="Example: Loser buys dinner, Winner gets to pick the movie, etc."></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline <span class="required">*</span></label>
                        <input type="date" id="deadline" name="deadline" required>
                    </div>

                    <button type="submit" class="btn btn-block">Create Bet</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="notification" id="notification">
        Bet invitation sent successfully!
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date for deadline to today
            const deadlineInput = document.getElementById('deadline');
            if (deadlineInput) {
                const today = new Date().toISOString().split('T')[0];
                deadlineInput.setAttribute('min', today);
                
                // Default to a week from now
                const nextWeek = new Date();
                nextWeek.setDate(nextWeek.getDate() + 7);
                deadlineInput.value = nextWeek.toISOString().split('T')[0];
            }

            // Stake type selector
            const stakeTypeOptions = document.querySelectorAll('.stake-type-option');
            const stakeTypeInput = document.getElementById('stake_type');
            const moneyStakeInput = document.getElementById('money-stake-input');
            const favorStakeInput = document.getElementById('favor-stake-input');
            const stakeAmount = document.getElementById('stake_amount');
            const stakeDescription = document.getElementById('stake_description');
            const securedBetOptions = document.getElementById('secured-bet-options');

            // Initialize stake inputs
            moneyStakeInput.style.display = 'block';
            favorStakeInput.style.display = 'none';
            stakeAmount.required = true;
            stakeDescription.required = false;

            // Handle bet type changes
            document.querySelectorAll('input[name="bet_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    securedBetOptions.style.display = this.value === 'secured' ? 'block' : 'none';
                });
            });

            stakeTypeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Update active state
                    document.querySelector('.stake-type-option.active').classList.remove('active');
                    this.classList.add('active');

                    // Update hidden input
                    const stakeType = this.dataset.type;
                    stakeTypeInput.value = stakeType;

                    // Show/hide appropriate input
                    if (stakeType === 'money') {
                        moneyStakeInput.style.display = 'block';
                        favorStakeInput.style.display = 'none';
                        stakeAmount.required = true;
                        stakeDescription.required = false;
                    } else {
                        moneyStakeInput.style.display = 'none';
                        favorStakeInput.style.display = 'block';
                        stakeAmount.required = false;
                        stakeDescription.required = true;
                    }
                });
            });

            // Form submission
            const betForm = document.getElementById('betForm');
            const notification = document.getElementById('notification');

            betForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate form
                const formData = new FormData(betForm);
                const stakeType = formData.get('stake_type');
                const betType = formData.get('bet_type');
                
                if (stakeType === 'money') {
                    if (!formData.get('stake_amount') || formData.get('stake_amount') <= 0) {
                        notification.textContent = 'Please enter a valid stake amount';
                        notification.style.backgroundColor = '#ef4444';
                        notification.style.display = 'block';
                        notification.classList.add('shake');
                        return;
                    }
                } else if (!formData.get('stake_description')) {
                    notification.textContent = 'Please describe the favor/task';
                    notification.style.backgroundColor = '#ef4444';
                    notification.style.display = 'block';
                    notification.classList.add('shake');
                    return;
                }

                // Submit form
                fetch(betForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        notification.textContent = result.message || 'Bet created successfully!';
                        notification.style.backgroundColor = '#4CAF50';
                        notification.style.display = 'block';
                        notification.classList.add('shake');

                        // If payment URL is provided and it's a secured bet with pay now option, redirect to payment page
                        if (result.payment_url && betType === 'secured' && formData.get('payment_preference') === 'pay_now') {
                            setTimeout(() => {
                                window.location.href = result.payment_url;
                            }, 1500);
                        } else {
                            // Otherwise, redirect to home page
                            setTimeout(() => {
                                window.location.href = 'home.php';
                            }, 1500);
                        }
                    } else {
                        notification.textContent = Array.isArray(result.errors) ? result.errors.join(', ') : result.message || 'An error occurred';
                        notification.style.backgroundColor = '#ef4444';
                        notification.style.display = 'block';
                        notification.classList.add('shake');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    notification.textContent = 'An error occurred. Please try again.';
                    notification.style.backgroundColor = '#ef4444';
                    notification.style.display = 'block';
                    notification.classList.add('shake');
                });

                setTimeout(() => {
                    notification.classList.remove('shake');
                }, 500);
            });
        });
    </script>
</body>
</html> 