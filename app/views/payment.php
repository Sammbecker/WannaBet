<?php
session_start();
require_once __DIR__ . '/../config/paystack.php';

// DEBUG: Log payment page access with session and parameters
error_log("PAYMENT PAGE ACCESSED - SESSION DATA: " . json_encode($_SESSION));
error_log("PAYMENT PAGE PARAMETERS: " . json_encode($_GET));

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if we have test mode enabled (use this for debugging only)
$debug_mode = true;
if ($debug_mode) {
    // Add any missing Paystack keys for testing
    if (empty($_ENV['PAYSTACK_PUBLIC_KEY'])) {
        $_ENV['PAYSTACK_PUBLIC_KEY'] = 'pk_test_yourtestkeyhere';
        error_log("WARNING: Using fallback test Paystack public key");
    }
    
    if (empty($_ENV['PAYSTACK_SECRET_KEY'])) {
        $_ENV['PAYSTACK_SECRET_KEY'] = 'sk_test_yourtestkeyhere';
        error_log("WARNING: Using fallback test Paystack secret key");
    }
    
    if (empty($_ENV['PAYSTACK_CURRENCY'])) {
        $_ENV['PAYSTACK_CURRENCY'] = 'ZAR';
        error_log("WARNING: Using fallback currency ZAR");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Bet Payment - WannaBet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --background-color: #f9f9f9;
            --card-bg: #ffffff;
            --text-color: #111111;
            --border-color: #eeeeee;
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
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }

        .payment-card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .amount-display {
            font-size: 36px;
            font-weight: 700;
            text-align: center;
            margin: 20px 0;
            color: var(--primary-color);
        }

        .payment-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
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

            <button class="payment-button" onclick="payWithPaystack()">Pay Now</button>
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

        function payWithPaystack() {
            // Show processing message
            document.getElementById('successMessage').style.display = 'block';
            document.getElementById('successMessage').textContent = 'Initializing payment...';
            
            // Reference to use
            const reference = <?php 
                if (isset($_SESSION['pending_payment']) && $_SESSION['pending_payment']['bet_id'] == $_GET['bet_id']) {
                    echo "'" . $_SESSION['pending_payment']['reference'] . "'";
                } else {
                    echo "'bet_' + betId + '_' + Math.floor((new Date()).getTime() / 1000)";
                }
            ?>;
            
            console.log('Payment reference:', reference);
            
            try {
                const handler = PaystackPop.setup({
                    key: '<?php echo $_ENV['PAYSTACK_PUBLIC_KEY']; ?>',
                    email: email,
                    amount: amount * 100, // Convert to kobo
                    currency: '<?php echo $_ENV['PAYSTACK_CURRENCY']; ?>',
                    ref: reference,
                    metadata: {
                        bet_id: betId,
                        user_id: '<?php echo $_SESSION['user_id'] ?? ""; ?>',
                        payment_type: '<?php echo isset($_SESSION['pending_payment']) ? "accept_bet" : "create_bet"; ?>',
                        custom_fields: [
                            {
                                display_name: "Bet ID",
                                variable_name: "bet_id",
                                value: betId
                            },
                            {
                                display_name: "Payment Type",
                                variable_name: "payment_type",
                                value: '<?php echo isset($_SESSION['pending_payment']) ? "accept_bet" : "create_bet"; ?>'
                            }
                        ]
                    },
                    callback: function(response) {
                        // Show loading message
                        document.getElementById('successMessage').style.display = 'block';
                        document.getElementById('successMessage').textContent = 'Verifying payment...';
                        
                        console.log('Payment callback received:', response);
                        
                        // Session data to send
                        const sessionData = <?php 
                            echo json_encode(isset($_SESSION['pending_payment']) ? $_SESSION['pending_payment'] : []); 
                        ?>;
                        
                        console.log('Sending session data:', sessionData);
                        
                        // Make AJAX call to verify payment
                        fetch('/app/controllers/verify_payment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                reference: response.reference,
                                bet_id: betId,
                                payment_type: '<?php echo isset($_SESSION['pending_payment']) ? "accept_bet" : "create_bet"; ?>',
                                session_data: sessionData
                            })
                        })
                        .then(response => {
                            console.log('Verification response status:', response.status);
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
                            console.log('Verification response data:', data);
                            if (data.success) {
                                document.getElementById('successMessage').style.display = 'block';
                                document.getElementById('successMessage').textContent = 'Payment successful! Redirecting...';
                                setTimeout(() => {
                                    window.location.href = '/app/views/my_bets.php';
                                }, 2000);
                            } else {
                                throw new Error(data.error || 'Payment verification failed');
                            }
                        })
                        .catch(error => {
                            console.error('Verification error:', error);
                            document.getElementById('errorMessage').style.display = 'block';
                            document.getElementById('errorMessage').textContent = 'Error: ' + error.message;
                            showDebugMessage('error', 'Verification failed: ' + error.message);
                        });
                    },
                    onClose: function() {
                        // Handle popup closure
                        console.log('Payment window closed by user');
                        document.getElementById('errorMessage').style.display = 'block';
                        document.getElementById('errorMessage').textContent = 'Payment canceled. You can try again.';
                    }
                });
                
                // Open the payment form
                handler.openIframe();
            } catch (error) {
                console.error('Error initializing payment:', error);
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('errorMessage').textContent = 'Failed to initialize payment: ' + error.message;
            }
        }
    </script>
</body>
</html> 