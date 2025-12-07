<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Create merchant (warung di satu tempat)
$merchant = User::updateOrCreate(
    ['id' => 2],
    [
        'name' => 'geprek legend',
        'email' => 'merchant@test.com',
        'phone' => '6281234567890',
        'store_name' => 'geprek legend',
        'latitude' => -7.97700,   // Lokasi warung
        'longitude' => 112.63500,
        'is_merchant' => 1,
    ]
);

// Create driver (di jalan menuju warung)
$driver = User::updateOrCreate(
    ['id' => 3],
    [
        'name' => 'Sigit Triyono',
        'email' => 'driver@test.com',
        'phone' => '6281111111111',
        'vehicle_plate' => 'R 2121 HM',
        'latitude' => -7.96800,   // Driver sedang di jalan
        'longitude' => 112.64200,
        'is_online' => 1,
    ]
);

// Create customer (pengguna di lokasi lain)
$customer = User::first();  // customer ID 1

// Create realistic order
$order = Order::create([
    'customer_id' => $customer->id,
    'driver_id' => $driver->id,
    'merchant_id' => $merchant->id,
    'delivery_address' => 'Jl. Sudirman 42, Malang',
    'dest_latitude' => -7.99000,   // Customer location (berbeda dari driver & warung)
    'dest_longitude' => 112.62500,
    'status' => 'delivery',
    'total_price' => 60000,
    'delivery_fee' => 5000,
    'payment_method' => 'cash',
    'picked_at' => now(),
]);

echo "✅ Order #" . $order->id . " created successfully!\n";
echo "📍 Merchant (Warung):  " . $merchant->latitude . ", " . $merchant->longitude . "\n";
echo "🛵 Driver:            " . $driver->latitude . ", " . $driver->longitude . "\n";
echo "📦 Customer (Tujuan): " . $order->dest_latitude . ", " . $order->dest_longitude . "\n";
echo "\n🔗 Open tracking: http://127.0.0.1:8001/order/track/" . $order->id . "\n";
?>
