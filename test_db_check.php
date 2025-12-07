<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Order;

$driver = User::find(3);
$merchant = User::find(2);
$customer = User::find(1);
$order = Order::find(12);

echo "=== DATABASE CHECK ===\n";
echo "Driver ID 3: lat=" . $driver->latitude . ", lng=" . $driver->longitude . "\n";
echo "Merchant ID 2: lat=" . $merchant->latitude . ", lng=" . $merchant->longitude . "\n";
echo "Customer ID 1: lat=" . $customer->latitude . ", lng=" . $customer->longitude . "\n";
echo "Order #12 dest: lat=" . $order->dest_latitude . ", lng=" . $order->dest_longitude . "\n";
echo "Order #12 driver_id=" . $order->driver_id . "\n";

echo "\n=== WITH RELATIONS ===\n";
$order->load(['driver', 'merchant']);
echo "Order->driver?: " . ($order->driver ? "YES (ID " . $order->driver->id . ")" : "NO") . "\n";
echo "Order->driver->latitude: " . ($order->driver?->latitude ?? "NULL") . "\n";
echo "Order->merchant?: " . ($order->merchant ? "YES (ID " . $order->merchant->id . ")" : "NO") . "\n";
echo "Order->merchant->latitude: " . ($order->merchant?->latitude ?? "NULL") . "\n";
