<?php
require_once __DIR__ . '/../controllers/BetController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /WannaBet/login');
    exit();
}

// Initialize controller and get trust score
$betController = new BetController();
$trustScoreResult = $betController->getUserTrustScore();
$trustScore = $trustScoreResult['score'] ?? 0;  // Get the score value or default to 0

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
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --text-color: #f3f4f6;
            --text-light: #9ca3af;
            --background-color: #111827;
            --card-background: #1f2937;
            --border-color: #374151;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --gradient-primary: linear-gradient(135deg, #3b82f6, #2563eb);
            --gradient-accent: linear-gradient(135deg, #f59e0b, #d97706);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
            line-height: 1.6;
        }

        header {
            background-color: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-lg);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .back-button i {
            font-size: 1.2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            color: var(--primary-color);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .trust-score {
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        .logout-button {
            padding: 0.5rem 1rem;
            background: var(--gradient-accent);
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .logout-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .dashboard-card {
            background: rgba(31, 41, 55, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-color);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .card-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .bet-list {
            list-style: none;
        }

        .bet-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .bet-item:last-child {
            border-bottom: none;
        }

        .bet-item:hover {
            background: rgba(59, 130, 246, 0.1);
            border-radius: 0.5rem;
        }

        .bet-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .bet-details {
            display: flex;
            justify-content: space-between;
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .bet-status {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-color);
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .user-info {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <header>
        <a href="/WannaBet" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Landing
        </a>
        <a href="/WannaBet/home" class="logo">
            <i class="fas fa-dice"></i>
            WannaBet
        </a>
        <div class="user-info">
            <div class="trust-score">
                <i class="fas fa-star"></i>
                Trust Score: <?php echo $trustScore; ?>
            </div>
            <a href="/WannaBet/logout" class="logout-button">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Active Bets</h2>
                    <div class="card-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <ul class="bet-list">
                    <?php if (empty($activeBets)): ?>
                        <li class="bet-item">
                            <p>No active bets. Create one to get started!</p>
                        </li>
                    <?php else: ?>
                        <?php foreach ($activeBets as $bet): ?>
                            <li class="bet-item">
                                <h3 class="bet-title"><?php echo htmlspecialchars($bet['title']); ?></h3>
                                <div class="bet-details">
                                    <span>Stake: <?php echo htmlspecialchars($bet['stake_description']); ?></span>
                                    <span class="bet-status <?php echo $bet['status'] === 'active' ? 'status-active' : 'status-pending'; ?>">
                                        <?php echo ucfirst($bet['status']); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                    <div class="card-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="/WannaBet/my_bets" class="btn btn-primary">
                        <i class="fas fa-list"></i>
                        View All Bets
                    </a>
                    <a href="/WannaBet/friends" class="btn btn-outline">
                        <i class="fas fa-users"></i>
                        Manage Friends
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
