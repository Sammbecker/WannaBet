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

// Paystack configuration
$_ENV['PAYSTACK_SECRET_KEY'] = $_ENV['PAYSTACK_SECRET_KEY'] ?? 'your_paystack_secret_key';
$_ENV['PAYSTACK_PUBLIC_KEY'] = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? 'your_paystack_public_key';

// Set default currency to ZAR (South African Rand)
$_ENV['PAYSTACK_CURRENCY'] = 'ZAR'; 