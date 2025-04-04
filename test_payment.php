<?php session_start(); require_once __DIR__ . "/app/models/PaymentProcessor.php"; $_SESSION["user_id"] = 1; $paymentProcessor = new PaymentProcessor(); $amount = 10.00; $betId = "test_" . time(); $userId = $_SESSION["user_id"]; echo "Creating payment intent for amount: $amount, betId: $betId, userId: $userId

"; $result = $paymentProcessor->createPaymentIntent($amount, $betId, $userId); echo "Result:
"; print_r($result); if ($result["success"]) { echo "

To verify this payment, go to:
"; echo "/app/views/debug_payment.php?reference=" . $result["reference"] . "&verify=true
"; }
