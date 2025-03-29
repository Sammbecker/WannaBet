<?php
// Error handling - disable HTML error output
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../models/PaymentProcessor.php';

// Check if API mode is requested
$api_mode = isset($_GET['api']) && $_GET['api'] === 'true';

// In API mode, set content type to JSON
if ($api_mode) {
    header('Content-Type: application/json');
    
    // Set error handler for API mode to capture any PHP errors
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // Log the error
        error_log("PHP Error in payment_callback.php: [$errno] $errstr in $errfile on line $errline");
        
        // Return JSON error response
        echo json_encode([
            'success' => false,
            'error' => 'Server error occurred',
            'debug' => "Error [$errno]: $errstr"
        ]);
        exit;
    });
}

// Log access
error_log("Payment callback page accessed. API Mode: " . ($api_mode ? 'yes' : 'no') . ", Session: " . json_encode($_SESSION) . ", GET: " . json_encode($_GET));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in in callback, continuing anyway");
}

try {
    // Check for required parameters
    if (!isset($_GET['bet_id']) && !$api_mode) {
        error_log("Missing required bet_id parameter");
        
        if ($api_mode) {
            echo json_encode(['success' => false, 'error' => 'Missing bet_id parameter']);
            exit;
        } else {
            header("Location: my_bets.php?error=missing_parameters");
            exit;
        }
    }

    // Get the bet ID
    $bet_id = $_GET['bet_id'] ?? null;

    // Check for payment success parameter (from Peach Payments)
    $payment_success = isset($_GET['success']) && $_GET['success'] === 'true';
    $payment_reference = $_GET['merchantTransactionId'] ?? $_GET['reference'] ?? ($_SESSION['pending_payment']['reference'] ?? null);

    // Log the callback parameters
    error_log("Payment callback parameters: " . json_encode([
        'bet_id' => $bet_id,
        'payment_success' => $payment_success,
        'payment_reference' => $payment_reference
    ]));

    // If we have a reference, verify the payment
    if ($payment_reference) {
        try {
            $paymentProcessor = new PaymentProcessor();
            $result = $paymentProcessor->verifyPayment($payment_reference);
            error_log("Payment verification result: " . json_encode($result));
            
            if ($result['success']) {
                // Payment was successful
                $success = true;
                $message = "Payment processed successfully";
            } else {
                // Payment failed or couldn't be verified
                $success = false;
                $message = $result['error'] ?? "Payment verification failed";
            }
        } catch (Exception $e) {
            error_log("Error verifying payment: " . $e->getMessage());
            $success = false;
            $message = "An error occurred while verifying your payment";
        }
    } else {
        // No payment reference found
        $success = false;
        $message = "Payment reference not found";
    }

    // Determine redirect URL
    $redirect_url = "my_bets.php";
    if ($success) {
        $redirect_url .= "?payment_success=true&bet_id=" . urlencode($bet_id ?? '');
    } else {
        $redirect_url .= "?payment_error=" . urlencode($message) . "&bet_id=" . urlencode($bet_id ?? '');
    }

    // For API mode, return JSON response
    if ($api_mode) {
        $response = [
            'success' => $success,
            'message' => $message,
            'redirect_url' => $redirect_url
        ];
        
        if ($success) {
            $response['bet_id'] = $result['bet_id'] ?? $bet_id;
            $response['test_mode'] = $result['test_mode'] ?? false;
        } else {
            $response['error'] = $message;
        }
        
        echo json_encode($response);
        exit;
    }
} catch (Exception $e) {
    error_log("Unhandled exception in payment_callback.php: " . $e->getMessage());
    
    if ($api_mode) {
        echo json_encode([
            'success' => false,
            'error' => 'Server error occurred',
            'debug' => $e->getMessage()
        ]);
        exit;
    }
    
    $success = false;
    $message = "An error occurred while processing your payment";
}

// Auto-redirect after a few seconds (HTML view)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing - WannaBet</title>
    <script src="/js/payment_callback_handler.js"></script>
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
            <h1><?php echo $success ? 'Payment Successful' : 'Payment Processing'; ?></h1>
            
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php if ($success): ?>
                    Your payment has been processed successfully.
                <?php else: ?>
                    <div class="loading"></div> Verifying your payment...
                <?php endif; ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
            
            <p>You will be redirected to your bets in a few seconds.</p>
            <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="button">Go to My Bets</a>
        </div>
    </div>

    <script>
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "<?php echo htmlspecialchars($redirect_url); ?>";
        }, 3000);
    </script>
</body>
</html> 