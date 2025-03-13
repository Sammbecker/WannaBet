<?php
session_start();
require_once __DIR__ . '/../controllers/BetController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize controller and get trust score
$betController = new BetController();
$trustScoreResult = $betController->getUserTrustScore();
$trustScore = $trustScoreResult['trust_score'];

// Get active bets
$betsResult = $betController->getUserBets();
$activeBets = array_filter($betsResult['participating_bets'], function($bet) {
    return $bet['status'] === 'active' || $bet['status'] === 'pending';
});

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WannaBet - Friend Betting Made Fun</title>
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

        /* Layout */
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
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-link {
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

        .nav-link:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-1px);
        }

        .nav-link.active {
            background: white;
            color: black;
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

        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        /* Dashboard Header */
        .dashboard-header {
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .welcome-text {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: var(--gradient-black);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .sub-text {
            color: var(--text-light);
            font-size: 1.1rem;
            max-width: 600px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 3.5rem;
        }

        .action-card {
            background: var(--card-bg);
            padding: 2.5rem 2rem;
            border-radius: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: #aaa;
        }

        .action-card:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 4px;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }

        .action-card:hover:after {
            width: 100%;
        }

        .action-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .action-card:hover .action-icon {
            transform: scale(1.1);
        }

        .action-card h3 {
            margin-bottom: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .action-card p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* Bet Container */
        .bet-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-color);
            position: relative;
            padding-bottom: 0.75rem;
            display: flex;
            align-items: center;
        }

        .section-title:before {
            content: '';
            width: 8px;
            height: 24px;
            background: var(--accent-color);
            margin-right: 12px;
            border-radius: 4px;
        }

        /* Bet Form */
        .bet-form {
            background: var(--card-bg);
            padding: 2.75rem;
            border-radius: 1.25rem;
            box-shadow: var(--shadow-md);
        }

        .form-group {
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
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
            min-height: 120px;
        }

        .bet-type-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }

        .bet-type-option {
            padding: 1.5rem;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .bet-type-option i {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .bet-type-option div {
            font-weight: 600;
        }

        .bet-type-option:hover {
            border-color: #999;
            background: #fafafa;
        }

        .bet-type-option.active {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            box-shadow: var(--shadow-md);
        }

        /* Button */
        .btn {
            background: var(--accent-color);
            color: white;
            padding: 1.25rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            letter-spacing: 1px;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .btn:hover:before {
            left: 100%;
        }

        .btn:hover {
            background: var(--hover-accent);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Active Bets */
        .active-bets {
            background: var(--card-bg);
            padding: 2.75rem;
            border-radius: 1.25rem;
            box-shadow: var(--shadow-md);
        }

        .bet-card {
            background: var(--background-color);
            padding: 1.8rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
            border: 1px solid var(--border-color);
            position: relative;
        }

        .bet-card:last-child {
            margin-bottom: 0;
        }

        .bet-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: #ddd;
        }

        .bet-status {
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-pending {
            background: #f5f5f5;
            color: #777;
            border: 1px solid #ddd;
        }

        .status-accepted {
            background: #000;
            color: white;
        }

        .bet-card h3 {
            font-size: 1.2rem;
            margin-bottom: 1.25rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .bet-detail {
            display: flex;
            margin-bottom: 0.75rem;
            align-items: center;
        }

        .bet-detail:last-child {
            margin-bottom: 0;
        }

        .bet-detail i {
            margin-right: 0.75rem;
            color: var(--text-light);
            font-size: 0.9rem;
            width: 18px;
        }

        .bet-detail p {
            color: var(--text-light);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            font-weight: 600;
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

        /* Stats Dashboard */
        .stats-dashboard {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 1.25rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem 1rem;
            background: var(--background-color);
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: var(--gradient-black);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .reliability-card {
            grid-column: span 2;
            display: flex;
            align-items: center;
            gap: 2rem;
            padding: 2rem;
        }

        .reliability-score {
            position: relative;
            width: 120px;
            height: 120px;
        }

        .reliability-circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(
                var(--accent-color) calc(var(--percentage) * 1%),
                var(--border-color) calc(var(--percentage) * 1%)
            );
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .reliability-circle::before {
            content: '';
            position: absolute;
            width: calc(100% - 15px);
            height: calc(100% - 15px);
            background: var(--background-color);
            border-radius: 50%;
        }

        .reliability-value {
            position: relative;
            font-size: 2rem;
            font-weight: 800;
            background: var(--gradient-black);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .reliability-info {
            flex: 1;
        }

        .reliability-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .reliability-desc {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .reliability-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }

        .reliability-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .reliability-stat i {
            color: var(--accent-color);
        }

        .reliability-stat span {
            font-weight: 600;
        }

        /* Achievements */
        .achievements {
            margin-top: 3rem;
        }

        .achievement-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .achievement {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
            border: 1px solid var(--border-color);
            position: relative;
            transition: all 0.3s;
        }

        .achievement:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .achievement-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: var(--background-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .achievement.locked {
            opacity: 0.7;
        }

        .achievement.locked .achievement-icon {
            color: var(--text-light);
        }

        .achievement-title {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .achievement-desc {
            color: var(--text-light);
            font-size: 0.85rem;
        }

        /* Notification Badge */
        .notification-badge {
            position: relative;
            margin-left: 1rem;
        }

        .notification-icon {
            font-size: 1.2rem;
            cursor: pointer;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-color);
            color: white;
            width: 16px;
            height: 16px;
            font-size: 0.65rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 300px;
            background: var(--card-bg);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            z-index: 100;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease;
            opacity: 0;
        }

        .notification-dropdown.show {
            max-height: 400px;
            opacity: 1;
        }

        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h4 {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .mark-read {
            font-size: 0.75rem;
            color: var(--text-light);
            cursor: pointer;
        }

        .notification-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: var(--background-color);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-content {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .notification-icon-sm {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--background-color);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .notification-text {
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .notification-time {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        .notification-empty {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
        }

        /* For large displays */
        @media (min-width: 1400px) {
            .container {
                max-width: 1300px;
            }
        }

        /* For tablets */
        @media (max-width: 992px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .achievement-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .bet-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .active-bets {
                margin-top: 1rem;
            }
        }
        
        /* For mobile */
        @media (max-width: 768px) {
            .quick-actions {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .bet-container {
                grid-template-columns: 1fr;
            }
            
            .navbar-container {
                padding: 0 1rem;
            }
            
            .container {
                margin: 2rem auto;
                padding: 0 1.25rem;
            }
            
            .bet-form, .active-bets {
                padding: 1.75rem;
            }
            
            .welcome-text {
                font-size: 1.75rem;
            }
        }

        /* For small mobile */
        @media (max-width: 480px) {
            .bet-form, .active-bets {
                padding: 1.5rem;
                border-radius: 1rem;
            }
            
            .action-card {
                padding: 1.75rem 1.5rem;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
            
            .greeting {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">WannaBet</div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-links">
                <a href="home.php" class="nav-link active">Dashboard</a>
                <a href="my_bets.php" class="nav-link">My Bets</a>
                <a href="friends.php" class="nav-link">Friends</a>
                <a href="documentation.php" class="nav-link">Documentation</a>
            </div>
            <div class="user-nav">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1 class="welcome-text">Your Betting Dashboard</h1>
            <p class="sub-text">Create challenges, track outcomes, and have fun with friendly wagers</p>
        </div>

        <div class="stats-dashboard">
            <h2 class="section-title">Your Stats</h2>
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-number">12</div>
                    <div class="stat-label">Total Bets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Wins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Active Bets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">67%</div>
                    <div class="stat-label">Win Rate</div>
                </div>
                <div class="reliability-card">
                    <div class="reliability-score">
                        <div class="reliability-circle" style="--percentage: <?php echo $trustScore['score']; ?>">
                            <div class="reliability-value"><?php echo $trustScore['score']; ?></div>
                        </div>
                    </div>
                    <div class="reliability-info">
                        <h3 class="reliability-title">Trust Score</h3>
                        <p class="reliability-desc">Your trust score reflects how reliable you are in paying and completing bets. Keep it high to unlock premium features and build trust with other users.</p>
                        <div class="reliability-stats">
                            <div class="reliability-stat">
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo $trustScore['payment_reliability']; ?>% On-time Payments</span>
                            </div>
                            <div class="reliability-stat">
                                <i class="fas fa-handshake"></i>
                                <span><?php echo $trustScore['completion_rate']; ?>% Bet Completion</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="create_bet.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3>Create New Bet</h3>
                <p>Challenge your friends with a new bet</p>
            </a>
            <a href="my_bets.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-dice"></i>
                </div>
                <h3>My Bets</h3>
                <p>View and manage all your active and past bets</p>
            </a>
            <a href="friends.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h3>Friends</h3>
                <p>Add new friends and manage your connections</p>
            </a>
        </div>

        <div class="bet-container">
            <div class="bet-form">
                <h2 class="section-title">Create a New Bet</h2>
                
                <div class="bet-type-selector">
                    <div class="bet-type-option active">
                        <i class="fas fa-user"></i>
                        <div>1-on-1 Bet</div>
                    </div>
                    <div class="bet-type-option">
                        <i class="fas fa-users"></i>
                        <div>Group Bet</div>
                    </div>
                </div>

                <form id="newBetForm">
                    <div class="form-group friend-selector">
                        <label>Who are you betting with?</label>
                        <input type="text" placeholder="Type friend's name" id="friendInput">
                    </div>

                    <div class="form-group">
                        <label>What's the bet about?</label>
                        <textarea rows="3" placeholder="Example: I bet I can run 5km faster than you" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>What's at stake?</label>
                        <input type="text" placeholder="Example: Loser buys dinner" required>
                    </div>

                    <div class="form-group">
                        <label>When does this bet end?</label>
                        <input type="date" required>
                    </div>

                    <button type="submit" class="btn">SEND BET CHALLENGE</button>
                </form>
            </div>

            <div class="active-bets">
                <h2 class="section-title">Your Active Bets</h2>
                
                <?php if (empty($activeBets)): ?>
                    <div class="empty-state">
                        <i class="fas fa-dice"></i>
                        <h3>No Active Bets</h3>
                        <p>Create a new bet to get started!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeBets as $bet): ?>
                        <div class="bet-card">
                            <span class="bet-status status-<?php echo strtolower($bet['status']); ?>">
                                <?php echo ucfirst($bet['status']); ?>
                            </span>
                            <h3><?php echo htmlspecialchars($bet['description']); ?></h3>
                            <div class="bet-detail">
                                <i class="fas fa-user"></i>
                                <p>With: <?php echo htmlspecialchars($bet['opponent_name']); ?></p>
                            </div>
                            <div class="bet-detail">
                                <i class="fas fa-trophy"></i>
                                <p>Stake: <?php 
                                    if ($bet['stake_type'] === 'money') {
                                        echo '$' . number_format($bet['stake_amount'], 2);
                                    } else {
                                        echo htmlspecialchars($bet['stake_description']);
                                    }
                                ?></p>
                            </div>
                            <div class="bet-detail">
                                <i class="fas fa-calendar"></i>
                                <p>Ends: <?php echo date('M j, Y', strtotime($bet['deadline'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const body = document.body;
            const isDark = body.getAttribute('data-theme') === 'dark';
            body.setAttribute('data-theme', isDark ? 'light' : 'dark');
            
            const themeIcon = document.querySelector('.theme-toggle i');
            themeIcon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
            
            // Save theme preference
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.classList.toggle('active');
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            const themeIcon = document.querySelector('.theme-toggle i');
            themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });

        // Notifications dropdown toggle
        const notificationBadge = document.getElementById('notificationBadge');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        notificationBadge.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
        });
        
        // Close notifications dropdown when clicking outside
        document.addEventListener('click', function() {
            notificationDropdown.classList.remove('show');
        });

        // Toggle bet type selection
        document.querySelectorAll('.bet-type-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelector('.bet-type-option.active').classList.remove('active');
                this.classList.add('active');
            });
        });

        // Form submission
        document.getElementById('newBetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const friend = document.getElementById('friendInput').value;
            const description = this.querySelector('textarea').value;
            const stake = this.querySelector('input[type="text"]:nth-of-type(2)').value;
            const deadline = this.querySelector('input[type="date"]').value;
            
            // Format the date
            const deadlineDate = new Date(deadline);
            const formattedDate = deadlineDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            // Create new bet card
            const betCard = document.createElement('div');
            betCard.className = 'bet-card';
            betCard.innerHTML = `
                <span class="bet-status status-pending">Pending</span>
                <h3>${description}</h3>
                <div class="bet-detail">
                    <i class="fas fa-user"></i>
                    <p>With: ${friend}</p>
                </div>
                <div class="bet-detail">
                    <i class="fas fa-trophy"></i>
                    <p>Stake: ${stake}</p>
                </div>
                <div class="bet-detail">
                    <i class="fas fa-calendar"></i>
                    <p>Ends: ${formattedDate}</p>
                </div>
            `;
            
            // Add the new bet card
            document.querySelector('.active-bets').appendChild(betCard);
            
            // Reset form
            this.reset();
            
            // Show success message
            alert('Bet challenge sent!');
        });
    </script>
</body>
</html>
