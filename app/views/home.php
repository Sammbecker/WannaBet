<?php
require_once __DIR__ . '/../controllers/BetController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
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
    <link rel="stylesheet" href="/css/common.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="/" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Landing
            </a>
            <a href="/home" class="logo">
                <i class="fas fa-dice"></i>
                WannaBet
            </a>
            <div class="user-info">
                <div class="trust-score">
                    <i class="fas fa-star"></i>
                    Trust Score: <?php echo $trustScore; ?>
                </div>
                <a href="/logout" class="logout-button">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-grid">
            <div class="card dashboard-card">
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
                                <h3 class="bet-title"><?php echo htmlspecialchars($bet['title'] ?? ''); ?></h3>
                                <div class="bet-details">
                                    <p class="bet-desc">
                                        <span>Stake: <?php echo htmlspecialchars($bet['stake_description'] ?? ''); ?></span>
                                    </p>
                                    <span class="bet-status <?php echo $bet['status'] === 'active' ? 'status-active' : 'status-pending'; ?>">
                                        <?php echo ucfirst($bet['status']); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="card dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                    <div class="card-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="/my_bets" class="btn btn-primary">
                        <i class="fas fa-list"></i>
                        View All Bets
                    </a>
                    <a href="/friends" class="btn btn-outline">
                        <i class="fas fa-users"></i>
                        Manage Friends
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
