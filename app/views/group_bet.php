<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dsn = "mysql:host=localhost;dbname=betting_app";
$username = "root"; // replace with your MySQL username
$password = "1909"; // replace with your MySQL password

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo json_encode(["status" => "success", "message" => "Connected successfully"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Bet - WannaBet</title>
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
            --text-color: #f5f5f5;
            --text-light: #aaaaaa;
            --border-color: #333333;
            --accent-color: #ffffff;
            --hover-accent: #cccccc;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.2);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.3), 0 1px 3px rgba(0,0,0,0.4);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.3), 0 4px 6px rgba(0,0,0,0.3);
            --gradient-black: linear-gradient(145deg, #121212, #2a2a2a);
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

        /* Navbar */
        .navbar {
            background: var(--gradient-black);
            padding: 1.2rem 0;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-md);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: 2px;
            cursor: pointer;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .greeting {
            font-weight: 500;
        }

        .logout-btn {
            color: white;
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            background: rgba(255,255,255,0.15);
            border-radius: 2rem;
            transition: all 0.3s;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-1px);
        }

        /* Container */
        .container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            background: var(--gradient-black);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .go-back {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: var(--card-bg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .go-back:hover {
            background: var(--accent-color);
            color: white;
        }

        /* Group Bet Card */
        .group-bet-card {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .step {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--border-color);
            margin: 0 0.25rem;
        }

        .step.active {
            background-color: var(--accent-color);
            transform: scale(1.2);
        }

        .step-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-color);
        }

        input, textarea, select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
            font-family: 'Inter', sans-serif;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            background: var(--accent-color);
            color: white;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background: var(--hover-accent);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn.btn-secondary {
            background: #f5f5f5;
            color: var(--text-color);
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }

        .friend-list {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .friend-chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f5f5f5;
            border-radius: 2rem;
            font-size: 0.9rem;
        }

        .remove-friend {
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.2s;
        }

        .remove-friend:hover {
            color: var(--accent-color);
        }

        .friend-suggestions {
            margin-top: 0.5rem;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            max-height: 150px;
            overflow-y: auto;
        }

        .friend-suggestion {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .friend-suggestion:hover {
            background: #f5f5f5;
        }

        .add-friend {
            color: var(--accent-color);
            font-size: 0.8rem;
            font-weight: 600;
        }

        .bet-type-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .bet-type {
            padding: 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .bet-type.active {
            border-color: var(--accent-color);
            background: rgba(0,0,0,0.02);
        }

        .bet-type h3 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .bet-type p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .confirmation-details {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
        }

        .confirm-item {
            display: flex;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .confirm-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .confirm-label {
            font-weight: 600;
            width: 120px;
            flex-shrink: 0;
        }

        .participants-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .participant-chip {
            padding: 0.25rem 0.75rem;
            background: #f0f0f0;
            border-radius: 1rem;
            font-size: 0.9rem;
        }

        .success-message {
            text-align: center;
            padding: 2rem 0;
        }

        .success-icon {
            font-size: 4rem;
            color: var(--accent-color);
            margin-bottom: 1.5rem;
        }

        .success-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .success-text {
            margin-bottom: 2rem;
            color: var(--text-light);
        }

        .pool-amount {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }

        .amount-input {
            flex: 1;
        }

        .currency-select {
            width: 100px;
            margin-left: 1rem;
        }

        /* Template styles */
        .quick-templates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .template-option {
            padding: 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--card-bg);
            box-shadow: var(--shadow-sm);
        }

        .template-option:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent-color);
        }

        .template-option.selected {
            border-color: var(--accent-color);
            background: rgba(0,0,0,0.02);
        }

        .template-option h4 {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .template-option p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin: 0;
        }

        .template-option .template-icon {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            color: var(--accent-color);
        }

        /* Dark Mode Toggle */
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 1rem;
            cursor: pointer;
        }

        .theme-toggle i {
            font-size: 1.2rem;
            transition: transform 0.3s;
        }

        .theme-toggle:hover i {
            transform: rotate(30deg);
        }

        /* Bet Status Visualization */
        .bet-status {
            display: flex;
            align-items: center;
            margin: 2rem 0;
        }

        .status-timeline {
            display: flex;
            align-items: center;
            width: 100%;
            position: relative;
        }

        .status-line {
            height: 3px;
            background-color: var(--border-color);
            flex-grow: 1;
            position: relative;
            z-index: 1;
        }

        .status-line.active {
            background-color: var(--accent-color);
        }

        .status-step {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            z-index: 2;
        }

        .status-step.active {
            background-color: var(--accent-color);
        }

        .status-step.completed {
            background-color: var(--success-color);
        }

        .status-label {
            position: absolute;
            top: 30px;
            transform: translateX(-50%);
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Social Features */
        .social-sharing {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            justify-content: center;
        }

        .share-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--background-color);
            color: var(--text-color);
            transition: all 0.3s;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            font-size: 1.2rem;
        }

        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .share-btn.twitter {
            color: #1DA1F2;
        }

        .share-btn.facebook {
            color: #4267B2;
        }

        .share-btn.whatsapp {
            color: #25D366;
        }

        /* Enhanced Bet Tracking */
        .bet-progress {
            margin-top: 2rem;
            background: var(--background-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .progress-title {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .progress-days {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .progress-bar-container {
            width: 100%;
            height: 8px;
            background-color: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background-color: var(--accent-color);
            border-radius: 4px;
            transition: width 0.5s;
        }

        .reminder-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--text-light);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1.25rem;
            }
            
            .group-bet-card {
                padding: 1.75rem;
            }
            
            .bet-type-options {
                grid-template-columns: 1fr;
            }

            .quick-templates {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo" onclick="location.href='home.php';">WANNABET</div>
            <div class="user-nav">
                <div class="greeting">Hey <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</div>
                <div class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </div>
                <a href="logout.php" class="logout-btn">LOG OUT</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Group Bet</h1>
            <a href="home.php" class="go-back">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="group-bet-card">
            <div class="step-indicator">
                <div class="step active"></div>
                <div class="step"></div>
                <div class="step"></div>
                <div class="step"></div>
            </div>

            <!-- Step 1: Add Participants -->
            <div class="form-step active" id="step1">
                <h2 class="step-title">Add Participants</h2>
                <div class="form-group">
                    <label for="groupName">Give your group bet a name</label>
                    <input type="text" id="groupName" placeholder="Example: Friday Night Football">
                </div>
                <div class="form-group">
                    <label for="friendInput">Invite friends</label>
                    <input type="text" id="friendInput" placeholder="Start typing a name...">
                    <div class="friend-suggestions" id="friendSuggestions">
                        <div class="friend-suggestion" onclick="addFriend('Alex Johnson')">
                            <span>Alex Johnson</span>
                            <span class="add-friend">+ Add</span>
                        </div>
                        <div class="friend-suggestion" onclick="addFriend('Mike Smith')">
                            <span>Mike Smith</span>
                            <span class="add-friend">+ Add</span>
                        </div>
                    </div>
                </div>
                <div class="btn-container">
                    <button class="btn" onclick="nextStep()">Continue <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 2: Choose Bet -->
            <div class="form-step" id="step2">
                <h2 class="step-title">What's the bet about?</h2>
                
                <h3 style="margin-bottom: 1rem;">Quick Templates</h3>
                <div class="quick-templates">
                    <div class="template-option" onclick="selectTemplate('Sports Game', 'I bet my team will win the game tonight')">
                        <div class="template-icon"><i class="fas fa-basketball-ball"></i></div>
                        <h4>Sports Game</h4>
                        <p>Bet on which team will win a game</p>
                    </div>
                    <div class="template-option" onclick="selectTemplate('Fitness Challenge', 'I bet I can do more push-ups than you')">
                        <div class="template-icon"><i class="fas fa-dumbbell"></i></div>
                        <h4>Fitness Challenge</h4>
                        <p>Compete in a physical challenge</p>
                    </div>
                    <div class="template-option" onclick="selectTemplate('Game Night', 'I bet I\'ll win our next game of chess')">
                        <div class="template-icon"><i class="fas fa-chess"></i></div>
                        <h4>Game Night</h4>
                        <p>Bet on board games or video games</p>
                    </div>
                    <div class="template-option" onclick="selectTemplate('Food Challenge', 'I bet I can eat spicier food than you')">
                        <div class="template-icon"><i class="fas fa-utensils"></i></div>
                        <h4>Food Challenge</h4>
                        <p>Compete in a food-related challenge</p>
                    </div>
                    <div class="template-option" onclick="selectTemplate('Weight Loss', 'I bet I can lose more weight than you in 30 days')">
                        <div class="template-icon"><i class="fas fa-weight"></i></div>
                        <h4>Weight Loss</h4>
                        <p>Compete in a weight loss challenge</p>
                    </div>
                    <div class="template-option" onclick="selectTemplate('Skill Learning', 'I bet I can learn to play this song on guitar faster than you')">
                        <div class="template-icon"><i class="fas fa-graduation-cap"></i></div>
                        <h4>Skill Learning</h4>
                        <p>Challenge to learn a new skill faster</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="betDescription">Or describe your bet</label>
                    <textarea id="betDescription" placeholder="Example: I bet I can run 5km faster than you"></textarea>
                </div>
                
                <div class="btn-container">
                    <button class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Back</button>
                    <button class="btn" onclick="nextStep()">Continue <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 3: Set Stakes -->
            <div class="form-step" id="step3">
                <h2 class="step-title">What's at stake?</h2>
                <div class="form-group">
                    <label for="betStake">What does the loser have to do?</label>
                    <input type="text" id="betStake" placeholder="Example: Buy dinner">
                </div>
                <div class="form-group">
                    <label for="betDeadline">When will this bet be decided?</label>
                    <input type="date" id="betDeadline">
                </div>
                <div class="btn-container">
                    <button class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Back</button>
                    <button class="btn" onclick="nextStep()">Review Bet <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 4: Confirmation -->
            <div class="form-step" id="step4">
                <h2 class="step-title">Review Your Bet</h2>
                
                <div class="bet-status">
                    <div class="status-timeline">
                        <div class="status-step active completed">
                            <i class="fas fa-check"></i>
                            <div class="status-label">Created</div>
                        </div>
                        <div class="status-line active"></div>
                        <div class="status-step active">
                            <i class="fas fa-user-plus"></i>
                            <div class="status-label">Waiting</div>
                        </div>
                        <div class="status-line"></div>
                        <div class="status-step">
                            <i class="fas fa-play"></i>
                            <div class="status-label">Active</div>
                        </div>
                        <div class="status-line"></div>
                        <div class="status-step">
                            <i class="fas fa-trophy"></i>
                            <div class="status-label">Complete</div>
                        </div>
                    </div>
                </div>
                
                <div class="confirmation-details">
                    <div class="confirm-item">
                        <div class="confirm-label">Betting with:</div>
                        <div class="confirm-value" id="confirmFriend">Alex Johnson</div>
                    </div>
                    <div class="confirm-item">
                        <div class="confirm-label">Bet:</div>
                        <div class="confirm-value" id="confirmBet">I bet I can run 5km faster than you</div>
                    </div>
                    <div class="confirm-item">
                        <div class="confirm-label">Stake:</div>
                        <div class="confirm-value" id="confirmStake">Loser buys dinner</div>
                    </div>
                    <div class="confirm-item">
                        <div class="confirm-label">Deadline:</div>
                        <div class="confirm-value" id="confirmDeadline">June 15, 2023</div>
                    </div>
                </div>
                <div class="btn-container">
                    <button class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Back</button>
                    <button class="btn" onclick="completeBet()">Send Bet Challenge <i class="fas fa-paper-plane"></i></button>
                </div>
                
                <div class="bet-progress">
                    <div class="progress-header">
                        <div class="progress-title">Bet Timeline</div>
                        <div class="progress-days">Starts: <span id="startDate">Today</span></div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="reminder-toggle">
                        <i class="far fa-bell"></i> Set reminder for this bet
                    </div>
                </div>
                
                <div class="social-sharing">
                    <div class="share-btn twitter"><i class="fab fa-twitter"></i></div>
                    <div class="share-btn facebook"><i class="fab fa-facebook-f"></i></div>
                    <div class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></div>
                </div>
            </div>

            <!-- Success Message -->
            <div class="form-step" id="success">
                <div class="success-message">
                    <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                    <h2 class="success-title">Bet Challenge Sent!</h2>
                    <p class="success-text">Your friend will receive your challenge and can accept or decline.</p>
                    <button class="btn" onclick="location.href='home.php'">Back to Dashboard <i class="fas fa-home"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div id="connection-status"></div>

    <script>
        let currentStep = 1;
        const formData = {
            friend: '',
            bet: '',
            stake: '',
            deadline: ''
        };

        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const icon = themeToggle.querySelector('i');
        
        // Check for saved theme preference or use default
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        // Update icon based on current theme
        if (savedTheme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
        
        // Toggle theme on click
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Toggle icon
            if (newTheme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });

        // Navigation between steps
        function nextStep() {
            // Validate current step
            if (!validateStep(currentStep)) return;
            
            // Update form data
            updateFormData(currentStep);
            
            // Hide current step
            document.getElementById(`step${currentStep}`).classList.remove('active');
            
            // Show next step
            currentStep++;
            document.getElementById(`step${currentStep}`).classList.add('active');
            
            // Update step indicator
            updateStepIndicator();
            
            // If on confirmation step, update the summary
            if (currentStep === 4) {
                updateConfirmation();
            }
        }

        function prevStep() {
            // Hide current step
            document.getElementById(`step${currentStep}`).classList.remove('active');
            
            // Show previous step
            currentStep--;
            document.getElementById(`step${currentStep}`).classList.add('active');
            
            // Update step indicator
            updateStepIndicator();
        }

        function updateStepIndicator() {
            // Reset all steps
            document.querySelectorAll('.step').forEach((step, index) => {
                if (index + 1 <= currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });
        }

        // Form validation
        function validateStep(step) {
            switch(step) {
                case 1:
                    const friend = document.getElementById('friendInput').value;
                    if (!friend) {
                        alert('Please enter a friend\'s name');
                        return false;
                    }
                    return true;
                
                case 2:
                    const bet = document.getElementById('betDescription').value;
                    if (!bet) {
                        alert('Please describe what the bet is about');
                        return false;
                    }
                    return true;
                
                case 3:
                    const stake = document.getElementById('betStake').value;
                    const deadline = document.getElementById('betDeadline').value;
                    if (!stake) {
                        alert('Please enter what\'s at stake');
                        return false;
                    }
                    if (!deadline) {
                        alert('Please select a deadline');
                        return false;
                    }
                    return true;
                
                default:
                    return true;
            }
        }

        // Update form data
        function updateFormData(step) {
            switch(step) {
                case 1:
                    formData.friend = document.getElementById('friendInput').value;
                    break;
                
                case 2:
                    formData.bet = document.getElementById('betDescription').value;
                    break;
                
                case 3:
                    formData.stake = document.getElementById('betStake').value;
                    formData.deadline = document.getElementById('betDeadline').value;
                    break;
            }
        }

        // Update confirmation summary
        function updateConfirmation() {
            document.getElementById('confirmFriend').textContent = formData.friend;
            document.getElementById('confirmBet').textContent = formData.bet;
            document.getElementById('confirmStake').textContent = formData.stake;
            
            // Format date
            const date = new Date(formData.deadline);
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('confirmDeadline').textContent = date.toLocaleDateString('en-US', options);
            document.getElementById('startDate').textContent = new Date().toLocaleDateString('en-US', options);
            
            // Update progress bar
            updateBetProgress(date);
        }
        
        // Calculate and update progress bar
        function updateBetProgress(deadline) {
            const today = new Date();
            const endDate = new Date(deadline);
            const totalDays = Math.ceil((endDate - today) / (1000 * 60 * 60 * 24));
            
            // Set progress bar width based on how far along the bet is
            const progressBar = document.querySelector('.progress-bar');
            if (totalDays <= 0) {
                progressBar.style.width = '100%';
            } else {
                // Always show at least a little progress
                progressBar.style.width = '5%';
            }
        }
        
        // Social sharing functionality
        document.querySelectorAll('.share-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const betDescription = document.getElementById('confirmBet').textContent;
                const shareText = `I just created a bet on WannaBet: "${betDescription}" - Join me!`;
                let shareUrl = '';
                
                if (this.classList.contains('twitter')) {
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}`;
                } else if (this.classList.contains('facebook')) {
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}&quote=${encodeURIComponent(shareText)}`;
                } else if (this.classList.contains('whatsapp')) {
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(shareText)}`;
                }
                
                if (shareUrl) {
                    window.open(shareUrl, '_blank');
                }
            });
        });

        // Select friend from suggestions
        function selectFriend(name) {
            document.getElementById('friendInput').value = name;
            document.getElementById('friendSuggestions').style.display = 'none';
        }

        // Show friend suggestions when typing
        document.getElementById('friendInput').addEventListener('focus', function() {
            document.getElementById('friendSuggestions').style.display = 'block';
        });

        document.getElementById('friendInput').addEventListener('blur', function() {
            // Delay hiding to allow for clicks on suggestions
            setTimeout(() => {
                document.getElementById('friendSuggestions').style.display = 'none';
            }, 200);
        });

        // Select template
        function selectTemplate(title, description) {
            document.getElementById('betDescription').value = description;
            
            // Remove selected class from all templates
            document.querySelectorAll('.template-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked template
            event.currentTarget.classList.add('selected');
        }

        // Complete bet and show success message
        function completeBet() {
            // Here you would normally submit the data to the server
            
            // Hide confirmation step
            document.getElementById('step4').classList.remove('active');
            
            // Show success message
            document.getElementById('success').classList.add('active');
            
            // Update step indicator (hide it on success)
            document.querySelector('.step-indicator').style.display = 'none';
        }

        // Function to check database connection
        function checkConnection() {
            fetch('group_bet.php')
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('connection-status');
                    if (data.status === 'success') {
                        statusDiv.textContent = 'Database connection successful!';
                        statusDiv.style.color = 'green';
                    } else {
                        statusDiv.textContent = 'Database connection failed: ' + data.message;
                        statusDiv.style.color = 'red';
                    }
                })
                .catch(error => {
                    const statusDiv = document.getElementById('connection-status');
                    statusDiv.textContent = 'Error: ' + error.message;
                    statusDiv.style.color = 'red';
                });
        }

        // Check connection on page load
        window.onload = checkConnection;
    </script>
</body>
</html> 