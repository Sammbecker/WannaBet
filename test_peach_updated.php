<?php require_once __DIR__ . "/app/models/PaymentProcessor.php"; echo "=== TESTING PEACH PAYMENTS INTEGRATION ===

"; $processor = new PaymentProcessor(); $test_result = $processor->createPaymentIntent(15.00, "test_" . time(), 1); echo "Test Mode Result:
"; print_r($test_result); $testWebhookParams = array("id" => "test123", "merchantTransactionId" => $test_result["reference"], "amount" => "15.00"); $testSignature = "fake_signature"; $validationResult = $processor->validateWebhookSignature($testWebhookParams, $testSignature); echo "

Webhook Signature Validation Result (should be true in test mode):
"; var_dump($validationResult);
