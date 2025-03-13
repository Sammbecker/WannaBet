<?php
session_start();
require_once __DIR__ . '/../config/paystack.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
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

        // Update display
        document.getElementById('amount').textContent = amount.toFixed(2);
        document.getElementById('betDescription').textContent = urlParams.get('description') || '';
        document.getElementById('opponentName').textContent = urlParams.get('opponent') || '';
        document.getElementById('betDeadline').textContent = urlParams.get('deadline') || '';

        function payWithPaystack() {
            const handler = PaystackPop.setup({
                key: '<?php echo $_ENV['PAYSTACK_PUBLIC_KEY']; ?>',
                email: email,
                amount: amount * 100, // Convert to kobo
                currency: '<?php echo $_ENV['PAYSTACK_CURRENCY']; ?>',
                ref: 'bet_' + betId + '_' + Math.floor((new Date()).getTime() / 1000),
                metadata: {
                    bet_id: betId,
                    custom_fields: [
                        {
                            display_name: "Bet ID",
                            variable_name: "bet_id",
                            value: betId
                        }
                    ]
                },
                callback: function(response) {
                    // Make AJAX call to verify payment
                    fetch('/app/controllers/verify_payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reference: response.reference,
                            bet_id: betId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
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
                        document.getElementById('errorMessage').style.display = 'block';
                        document.getElementById('errorMessage').textContent = error.message;
                    });
                },
                onClose: function() {
                    // Handle popup closure
                }
            });
            handler.openIframe();
        }
    </script>
</body>
</html> 