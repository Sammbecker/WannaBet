<?php
// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/../../.env')) {
    $envFile = file_get_contents(__DIR__ . '/../../.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Peach Payments configuration - using their test credentials 
$_ENV['PEACH_ENTITY_ID'] = $_ENV['PEACH_ENTITY_ID'] ?? '8ac7a4ca68c22c4d0168c2caab2e0025';
$_ENV['PEACH_SIGNATURE'] = $_ENV['PEACH_SIGNATURE'] ?? 'a668342244a9c77b08a2f9090d033d6e2610b431a5c0ca975f32035ed06164f4';
$_ENV['PEACH_API_URL'] = $_ENV['PEACH_API_URL'] ?? 'https://testsecure.peachpayments.com/checkout/initiate';

// Set default currency to ZAR (South African Rand)
$_ENV['PEACH_CURRENCY'] = 'ZAR';

// Set environment (test or production)
$_ENV['PEACH_ENVIRONMENT'] = $_ENV['PEACH_ENVIRONMENT'] ?? 'test';

// Define the return URL base for your application
// For production, this should be your actual domain
// For local development & testing, we'll use example.com which passes Peach Payments' URL validation
$_ENV['APP_BASE_URL'] = $_ENV['APP_BASE_URL'] ?? 'https://www.example.com'; 