<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';

echo "<h2>Stripe Test</h2>";
echo "Secret Key: " . substr($secretKey, 0, 15) . "..." . "<br>";

\Stripe\Stripe::setApiKey($secretKey);

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => 'Test Game'],
                'unit_amount' => 1999,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:8000/success',
        'cancel_url' => 'http://localhost:8000/cancel',
    ]);
    
    echo "<br><strong style='color: green;'>✓ Stripe connection works!</strong><br>";
    echo "Session ID: " . $session->id . "<br>";
    echo "Checkout URL: <a href='" . $session->url . "' target='_blank'>Test Checkout</a><br>";
    
} catch (Exception $e) {
    echo "<br><strong style='color: red;'>✗ Error:</strong> " . $e->getMessage();
}
