<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\User;

// Get first merchant
$merchant = User::where('role', 'merchant')->first();

if (!$merchant) {
    echo "❌ Merchant tidak ditemukan!\n";
    exit;
}

// Create new order untuk merchant ini
$order = Order::create([
    'customer_id' => 1,
    'merchant_id' => $merchant->id,
    'driver_id' => 1,
    'delivery_address' => 'Jl Test Order',
    'dest_latitude' => -7.9826,
    'dest_longitude' => 112.6308,
    'total_price' => 50000,
    'delivery_fee' => 5000,
    'status' => 'pending',
    'payment_method' => 'cod',
    'payment_status' => 'paid',
    'payment_code' => 'TEST' . uniqid()
]);

echo "✅ Order #" . $order->id . " created for merchant ID " . $merchant->id . "\n";
echo "🧪 Test API endpoint: /api/merchant/recent-orders\n";
echo "\n📝 Output JSON:\n";

// Simulate API call
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test';
Auth::setUser($merchant);

$controller = new \App\Http\Controllers\MerchantController();
$response = $controller->getRecentOrders(new \Illuminate\Http\Request(['last_check' => time() - 300]));

echo $response->getContent();
?>
