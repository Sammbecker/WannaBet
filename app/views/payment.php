<?php
session_start();
require_once __DIR__ . '/../config/peach_payments.php';

// Set debug mode
$debug_mode = true;

// Log access
error_log("Payment page accessed. Session: " . json_encode($_SESSION) . ", GET: " . json_encode($_GET));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in, redirecting to login page");
    header("Location: login.php");
    exit;
}

// Check for required parameters
if (!isset($_GET['bet_id']) || !isset($_GET['amount'])) {
    error_log("Missing required parameters");
    header("Location: my_bets.php?error=missing_parameters");
    exit;
}

// Keep original values but make them more explicit
$amount = floatval($_GET['amount']);
$bet_id = $_GET['bet_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - WannaBet</title>
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #0ea5e9;
            --text-color: #374151;
            --background-color: #f9fafb;
            --card-background: #ffffff;
            --border-color: #e5e7eb;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }

        .payment-card {
            background-color: var(--card-background);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-top: 0;
        }

        .amount-display {
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            margin: 30px 0;
            color: var(--text-color);
        }

        .payment-button {
            display: block;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 15px 20px;
            font-size: 1rem;
            font-weight: 600;
            margin: 20px auto;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .payment-button:hover {
            background: var(--secondary-color);
        }

        .bet-details {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .bet-details p {
            margin: 5px 0;
            font-size: 14px;
        }

        .success-message {
            background: #10b981;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .error-message {
            background: #ef4444;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-card">
            <h1>Process Bet Payment</h1>
            
            <div class="bet-details">
                <p><strong>Bet Description:</strong> <span id="betDescription"></span></p>
                <p><strong>Opponent:</strong> <span id="opponentName"></span></p>
                <p><strong>Deadline:</strong> <span id="betDeadline"></span></p>
            </div>

            <div class="amount-display">
                R<span id="amount">0.00</span>
            </div>

            <div class="success-message" id="successMessage"></div>
            <div class="error-message" id="errorMessage"></div>

            <button class="payment-button" id="payButton">Pay Now</button>
        </div>
    </div>

    <script>
        // Get bet details from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const amount = parseFloat(urlParams.get('amount') || 0);
        const betId = urlParams.get('bet_id');
        const email = "<?php echo $_SESSION['email'] ?? ''; ?>";

        // Log initialization values
        console.log('Payment Page Initialization:', {
            amount: amount,
            betId: betId,
            email: email,
            hasPendingPayment: <?php echo isset($_SESSION['pending_payment']) ? 'true' : 'false'; ?>,
            pendingPaymentData: <?php echo isset($_SESSION['pending_payment']) ? json_encode($_SESSION['pending_payment']) : '{}'; ?>
        });

        // Update display
        document.getElementById('amount').textContent = amount.toFixed(2);
        document.getElementById('betDescription').textContent = urlParams.get('description') || '';
        document.getElementById('opponentName').textContent = urlParams.get('opponent') || '';
        document.getElementById('betDeadline').textContent = urlParams.get('deadline') || '';

        // Show a debug message on the page
        function showDebugMessage(type, message) {
            const debugElem = document.createElement('div');
            debugElem.style.padding = '10px';
            debugElem.style.margin = '10px 0';
            debugElem.style.border = '1px solid #ccc';
            debugElem.style.backgroundColor = type === 'error' ? '#ffebee' : '#e8f5e9';
            debugElem.textContent = message;
            document.querySelector('.payment-card').appendChild(debugElem);
            console.log(`[${type}] ${message}`);
        }

        <?php if ($debug_mode): ?>
        showDebugMessage('info', 'Debug mode enabled. Current session data: ' + 
            JSON.stringify(<?php echo json_encode($_SESSION); ?>));
        <?php endif; ?>

        document.getElementById('payButton').addEventListener('click', function() {
            // Show processing message
            document.getElementById('successMessage').style.display = 'block';
            document.getElementById('successMessage').textContent = 'Initializing payment...';
            
            // Disable the button to prevent multiple clicks
            this.disabled = true;
            
            // Make AJAX call to initialize payment
            fetch('/app/controllers/initialize_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    bet_id: betId,
                    amount: amount
                })
            })
            .then(response => {
                console.log('Initialization response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text); // Try to parse as JSON
                        } catch (e) {
                            console.error('Response is not valid JSON:', text);
                            throw new Error(`Server returned status ${response.status}: ${text.substring(0, 100)}...`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Payment initialization response:', data);
                if (data.success && data.checkout_url) {
                    // Redirect to Peach Payments checkout
                    window.location.href = data.checkout_url;
                } else {
                    throw new Error(data.error || 'Failed to initialize payment');
                }
            })
            .catch(error => {
                console.error('Payment initialization error:', error);
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('errorMessage').textContent = 'Error: ' + error.message;
                document.getElementById('payButton').disabled = false;
            });
        });
    </script>
</body>
</html> 