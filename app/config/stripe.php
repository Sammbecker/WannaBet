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

// Stripe configuration
$_ENV['STRIPE_SECRET_KEY'] = $_ENV['STRIPE_SECRET_KEY'] ?? 'your_stripe_secret_key';
$_ENV['STRIPE_PUBLISHABLE_KEY'] = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? 'your_stripe_publishable_key';
$_ENV['STRIPE_WEBHOOK_SECRET'] = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? 'your_stripe_webhook_secret'; 