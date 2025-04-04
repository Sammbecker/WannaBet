<?php require_once __DIR__ . "/app/models/PaymentProcessor.php"; echo "=== TESTING PEACH PAYMENTS INTEGRATION ===

"; $processor = new PaymentProcessor(); $result = $processor->createPaymentIntent(15.00, "test_" . time(), 1); echo "Payment Intent Creation Result:
"; print_r($result); echo "

Directly testing makePeachRequest with Peach params:
"; $directResult = $processor->makePeachRequest(["entityId" => $_ENV["PEACH_ENTITY_ID"], "amount" => "15.00", "currency" => "ZAR", "paymentType" => "DB", "merchantTransactionId" => "DIRECT_" . time()]); print_r($directResult);