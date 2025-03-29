<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing - WannaBet</title>
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

        .card {
            background-color: var(--card-background);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }

        h1 {
            color: var(--primary-color);
            margin-top: 0;
        }

        .message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
        }

        .success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .error {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .button {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .button:hover {
            background-color: var(--secondary-color);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Payment Processing</h1>
            
            <div class="message" id="messageBox">
                <div class="loading"></div> Verifying your payment...
                <p id="messageText">Please wait while we process your payment.</p>
            </div>
            
            <p>You will be redirected automatically once the payment is processed.</p>
            <a href="my_bets.php" class="button" id="redirectButton">Go to My Bets</a>
        </div>
    </div>

    <script>
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Payment callback page loaded');
            
            // Get elements
            const messageBox = document.getElementById('messageBox');
            const messageText = document.getElementById('messageText');
            const redirectButton = document.getElementById('redirectButton');
            
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const reference = urlParams.get('reference') || urlParams.get('merchantTransactionId');
            const betId = urlParams.get('bet_id');
            const success = urlParams.get('success') === 'true';
            
            console.log('URL parameters:', { reference, betId, success });
            
            // If we have a reference, make API call to verify payment
            if (reference) {
                // Build API URL
                let apiUrl = '/api_callback.php?';
                if (reference) apiUrl += 'reference=' + encodeURIComponent(reference) + '&';
                if (betId) apiUrl += 'bet_id=' + encodeURIComponent(betId) + '&';
                if (success !== null) apiUrl += 'success=' + success;
                
                console.log('Making API request to:', apiUrl);
                
                // Make API request
                fetch(apiUrl)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('API response:', data);
                        
                        // Handle successful payment
                        if (data.success) {
                            messageBox.classList.add('success');
                            messageBox.classList.remove('error');
                            messageText.textContent = data.message || 'Payment successful!';
                            redirectButton.href = data.redirect_url || 'my_bets.php';
                            
                            // Auto-redirect
                            setTimeout(function() {
                                window.location.href = data.redirect_url || 'my_bets.php';
                            }, 3000);
                        } 
                        // Handle failed payment
                        else {
                            messageBox.classList.add('error');
                            messageBox.classList.remove('success');
                            messageText.textContent = data.error || 'Payment verification failed.';
                            redirectButton.href = data.redirect_url || 'my_bets.php';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageBox.classList.add('error');
                        messageBox.classList.remove('success');
                        messageText.textContent = 'An error occurred while processing your payment.';
                    });
            } else {
                // No reference found
                messageBox.classList.add('error');
                messageBox.classList.remove('success');
                messageText.textContent = 'No payment reference found.';
            }
        });
    </script>
</body>
</html> 