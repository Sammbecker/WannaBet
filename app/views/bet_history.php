<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bet History - WannaBet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/common.css">
    <style>
        /* Bet history page specific styles */
        .page-header {
            margin-bottom: 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            background: var(--gradient-primary);
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
            background: var(--card-background);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .go-back:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .filter-container {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .filter-label {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            background: var(--card-background);
            color: var(--text-color);
        }

        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 300px;
        }

        .search-input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            width: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--card-background);
            color: var(--text-color);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .history-container {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
        }

        .bet-list {
            margin-bottom: 2rem;
        }

        .bet-item {
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            background: var(--background-color);
            border: 1px solid var(--border-color);
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            transition: all 0.3s;
        }

        .bet-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .bet-info {
            flex: 1;
            min-width: 250px;
        }

        .bet-detail-row {
            display: flex;
            margin-bottom: 0.5rem;
            align-items: center;
        }

        .bet-detail-row i {
            margin-right: 0.75rem;
            color: var(--text-light);
            font-size: 0.9rem;
            width: 18px;
        }

        .bet-date {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .bet-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .bet-result {
            min-width: 120px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
        }

        .result-badge {
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .result-win {
            background: var(--success-color);
            color: white;
        }

        .result-lose {
            background: var(--danger-color);
            color: white;
        }

        .result-draw {
            background: var(--primary-color);
            color: white;
        }

        .bet-stake {
            font-weight: 700;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .page-item {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            background: var(--background-color);
            border: 1px solid var(--border-color);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .page-item:hover {
            background: var(--primary-color);
            color: white;
        }

        .page-item.active {
            background: var(--primary-color);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 3rem;
            color: var(--text-light);
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: var(--text-light);
            max-width: 400px;
            margin: 0 auto 1.5rem;
        }

        .rebet-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            font-size: 0.85rem;
        }

        .rebet-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
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
            padding: 2rem;
            border-radius: 1rem;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-lg);
            transform: translateY(20px);
            transition: all 0.3s;
        }

        .modal-overlay.active .modal-container {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .bet-details {
            margin-bottom: 1.5rem;
        }

        .bet-details h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .detail-item i {
            margin-right: 0.75rem;
            color: var(--text-light);
            font-size: 0.9rem;
            width: 18px;
        }

        .stake-options {
            margin-bottom: 1.5rem;
        }

        .stake-options h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }

        .stake-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stake-option {
            flex: 1;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .stake-option.selected {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
            font-weight: 600;
        }

        .custom-stake {
            margin-top: 1rem;
        }

        .custom-stake input {
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 2px solid var(--border-color);
            width: 100%;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
        }

        .modal-btn {
            flex: 1;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .cancel-btn {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }

        .confirm-btn {
            background: var(--primary-color);
            color: white;
        }

        .confirm-btn:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.25rem;
            }
            
            .filter-group {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-box {
                width: 100%;
                max-width: none;
            }
            
            .history-container {
                padding: 1.5rem;
            }
            
            .bet-result {
                width: 100%;
                align-items: flex-start;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="/home" class="logo">WANNABET</a>
                <div class="user-info">
                    <div>Hey <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</div>
                    <a href="/logout" class="btn btn-outline">LOG OUT</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Bet History</h1>
            <a href="/home" class="go-back">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="filter-container">
            <div class="filter-group">
                <span class="filter-label">Show:</span>
                <select class="filter-select" id="resultFilter">
                    <option value="all">All Results</option>
                    <option value="win">Wins</option>
                    <option value="lose">Losses</option>
                    <option value="draw">Draws</option>
                </select>
            </div>
            
            <div class="filter-group">
                <span class="filter-label">Period:</span>
                <select class="filter-select" id="timeFilter">
                    <option value="all">All Time</option>
                    <option value="month">Last Month</option>
                    <option value="6months">Last 6 Months</option>
                    <option value="year">Last Year</option>
                </select>
            </div>
            
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search bets..." id="searchInput">
            </div>
        </div>

        <div class="history-container">
            <div class="bet-list" id="betList">
                <!-- Sample bet items - these would be generated from database in real app -->
                <div class="bet-item" data-result="win" data-title="5km Running Race" data-opponent="Alex" data-stake="Dinner at Italian Place">
                    <div class="bet-info">
                        <div class="bet-date">Completed on March 15, 2023</div>
                        <h3 class="bet-title">5km Running Race</h3>
                        <div class="bet-detail-row">
                            <i class="fas fa-user"></i>
                            <div>Against: Alex</div>
                        </div>
                        <div class="bet-detail-row">
                            <i class="fas fa-trophy"></i>
                            <div>Stake: <span class="bet-stake">Dinner at Italian Place</span></div>
                        </div>
                    </div>
                    <div class="bet-result">
                        <span class="result-badge result-win">You won</span>
                        <div>Beat by 2 minutes</div>
                        <div class="action-buttons">
                            <button class="rebet-btn" onclick="openRebetModal(this)">
                                <i class="fas fa-redo-alt"></i> Rebet
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="bet-item" data-result="lose" data-title="Basketball Game Prediction" data-opponent="Mike" data-stake="$20">
                    <div class="bet-info">
                        <div class="bet-date">Completed on February 28, 2023</div>
                        <h3 class="bet-title">Basketball Game Prediction</h3>
                        <div class="bet-detail-row">
                            <i class="fas fa-user"></i>
                            <div>Against: Mike</div>
                        </div>
                        <div class="bet-detail-row">
                            <i class="fas fa-trophy"></i>
                            <div>Stake: <span class="bet-stake">$20</span></div>
                        </div>
                    </div>
                    <div class="bet-result">
                        <span class="result-badge result-lose">You lost</span>
                        <div>Lakers won instead</div>
                        <div class="action-buttons">
                            <button class="rebet-btn" onclick="openRebetModal(this)">
                                <i class="fas fa-redo-alt"></i> Rebet
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="bet-item" data-result="draw" data-title="Trivia Night Score" data-opponent="Sarah" data-stake="Coffee for a week">
                    <div class="bet-info">
                        <div class="bet-date">Completed on January 10, 2023</div>
                        <h3 class="bet-title">Trivia Night Score</h3>
                        <div class="bet-detail-row">
                            <i class="fas fa-user"></i>
                            <div>Against: Sarah</div>
                        </div>
                        <div class="bet-detail-row">
                            <i class="fas fa-trophy"></i>
                            <div>Stake: <span class="bet-stake">Coffee for a week</span></div>
                        </div>
                    </div>
                    <div class="bet-result">
                        <span class="result-badge result-draw">Draw</span>
                        <div>Both scored 15 points</div>
                        <div class="action-buttons">
                            <button class="rebet-btn" onclick="openRebetModal(this)">
                                <i class="fas fa-redo-alt"></i> Rebet
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="bet-item" data-result="win" data-title="Weight Loss Challenge" data-opponent="John" data-stake="$50">
                    <div class="bet-info">
                        <div class="bet-date">Completed on December 5, 2022</div>
                        <h3 class="bet-title">Weight Loss Challenge</h3>
                        <div class="bet-detail-row">
                            <i class="fas fa-user"></i>
                            <div>Against: John</div>
                        </div>
                        <div class="bet-detail-row">
                            <i class="fas fa-trophy"></i>
                            <div>Stake: <span class="bet-stake">$50</span></div>
                        </div>
                    </div>
                    <div class="bet-result">
                        <span class="result-badge result-win">You won</span>
                        <div>Lost 3kg more</div>
                        <div class="action-buttons">
                            <button class="rebet-btn" onclick="openRebetModal(this)">
                                <i class="fas fa-redo-alt"></i> Rebet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="pagination">
                <div class="page-item active">1</div>
                <div class="page-item">2</div>
                <div class="page-item">3</div>
                <div class="page-item"><i class="fas fa-ellipsis-h"></i></div>
                <div class="page-item">10</div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="rebetModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Rebet Challenge</h3>
                <button class="close-modal" onclick="closeRebetModal()">×</button>
            </div>
            <div class="bet-details">
                <h3>Original Bet</h3>
                <div class="detail-item">
                    <i class="fas fa-trophy"></i>
                    <div id="modalBetTitle">5km Running Race</div>
                </div>
                <div class="detail-item">
                    <i class="fas fa-user"></i>
                    <div id="modalOpponent">Against: Alex</div>
                </div>
            </div>
            <div class="stake-options">
                <h3>Choose Your Stake</h3>
                <div class="stake-selector">
                    <div class="stake-option" onclick="selectStakeOption(this)" data-multiplier="1">
                        <div>Same Stake</div>
                        <div id="originalStake">Dinner at Italian Place</div>
                    </div>
                    <div class="stake-option" onclick="selectStakeOption(this)" data-multiplier="2">
                        <div>Double Stake</div>
                        <div id="doubleStake">2× Dinner</div>
                    </div>
                </div>
                <div class="custom-stake">
                    <input type="text" placeholder="Or enter custom stake..." id="customStake">
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel-btn" onclick="closeRebetModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="confirmRebet()">Send Challenge</button>
            </div>
        </div>
    </div>

    <script>
        // Filter functionality
        document.getElementById('resultFilter').addEventListener('change', filterBets);
        document.getElementById('timeFilter').addEventListener('change', filterBets);
        document.getElementById('searchInput').addEventListener('input', filterBets);
        
        function filterBets() {
            const resultFilter = document.getElementById('resultFilter').value;
            const searchQuery = document.getElementById('searchInput').value.toLowerCase();
            const betItems = document.querySelectorAll('.bet-item');
            
            betItems.forEach(item => {
                const result = item.dataset.result;
                const title = item.querySelector('.bet-title').textContent.toLowerCase();
                const details = item.querySelector('.bet-info').textContent.toLowerCase();
                
                let resultMatch = true;
                if (resultFilter !== 'all') {
                    resultMatch = result === resultFilter;
                }
                
                const textMatch = title.includes(searchQuery) || details.includes(searchQuery);
                
                if (resultMatch && textMatch) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Check if any items are visible
            const visibleItems = document.querySelectorAll('.bet-item[style="display: flex;"]');
            const emptyState = document.querySelector('.empty-state');
            
            if (visibleItems.length === 0) {
                // If no empty state exists, create one
                if (!emptyState) {
                    const emptyEl = document.createElement('div');
                    emptyEl.className = 'empty-state';
                    emptyEl.innerHTML = `
                        <div class="empty-icon"><i class="fas fa-search"></i></div>
                        <h3 class="empty-title">No bets found</h3>
                        <p class="empty-text">Try adjusting your filters or search query</p>
                    `;
                    document.getElementById('betList').appendChild(emptyEl);
                }
            } else {
                // Remove empty state if it exists
                if (emptyState) {
                    emptyState.remove();
                }
            }
        }
        
        // Pagination functionality
        document.querySelectorAll('.page-item').forEach(item => {
            item.addEventListener('click', function() {
                if (this.innerHTML.includes('ellipsis')) return;
                
                document.querySelector('.page-item.active').classList.remove('active');
                this.classList.add('active');
                
                // In a real app, this would fetch the appropriate page of results
                // For now, we'll just show a notification
                if (this.textContent !== '1') {
                    alert('In a complete app, this would load page ' + this.textContent);
                }
            });
        });

        // Rebet Modal Functionality
        let currentBetData = null;

        function openRebetModal(button) {
            const betItem = button.closest('.bet-item');
            currentBetData = {
                title: betItem.dataset.title,
                opponent: betItem.dataset.opponent,
                stake: betItem.dataset.stake
            };
            
            // Populate modal with bet data
            document.getElementById('modalBetTitle').textContent = currentBetData.title;
            document.getElementById('modalOpponent').textContent = 'Against: ' + currentBetData.opponent;
            document.getElementById('originalStake').textContent = currentBetData.stake;
            document.getElementById('doubleStake').textContent = '2× ' + currentBetData.stake;
            
            // Clear and reset form
            document.getElementById('customStake').value = '';
            document.querySelectorAll('.stake-option').forEach(opt => opt.classList.remove('selected'));
            document.querySelector('.stake-option[data-multiplier="1"]').classList.add('selected');
            
            // Show modal
            document.getElementById('rebetModal').classList.add('active');
        }
        
        function closeRebetModal() {
            document.getElementById('rebetModal').classList.remove('active');
        }
        
        function selectStakeOption(option) {
            document.querySelectorAll('.stake-option').forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
        }
        
        function confirmRebet() {
            const selectedOption = document.querySelector('.stake-option.selected');
            let stake = '';
            
            const customStake = document.getElementById('customStake').value;
            if (customStake.trim() !== '') {
                stake = customStake;
            } else {
                const multiplier = selectedOption.dataset.multiplier;
                stake = multiplier === '1' ? currentBetData.stake : '2× ' + currentBetData.stake;
            }
            
            // Here you would normally send the data to the server
            // For now, we'll show an alert
            alert(`New bet challenge sent to ${currentBetData.opponent}!\n\nBet: ${currentBetData.title}\nStake: ${stake}`);
            
            // Close modal
            closeRebetModal();
        }
    </script>
</body>
</html> 