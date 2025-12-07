<?php
/**
 * Quick test script to update driver location and check tracking
 * Run: php test_driver_location.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Order;

// Find first driver
$driver = User::where('role', 'driver')->first();
if (!$driver) {
    echo "❌ No driver found\n";
    exit(1);
}

// Find first merchant
$merchant = User::where('role', 'merchant')->first();
if (!$merchant) {
    echo "❌ No merchant found\n";
    exit(1);
}

// Find first customer
$customer = User::where('role', 'user')->first();
if (!$customer) {
    echo "❌ No customer found\n";
    exit(1);
}

// Ensure all actors have coordinates
$merchant->update(['latitude' => -7.9826, 'longitude' => 112.6308]); // Merchant at Alun-alun Malang
$driver->update(['latitude' => -7.98, 'longitude' => 112.63]); // Driver nearby
$customer->update(['latitude' => -7.9826, 'longitude' => 112.6308]); // Same as destination

echo "✓ Driver: " . $driver->name . " (ID: " . $driver->id . ")\n";
echo "✓ Merchant: " . $merchant->store_name . " (ID: " . $merchant->id . ")\n";
echo "✓ Customer: " . $customer->name . " (ID: " . $customer->id . ")\n\n";

// Create test order
$order = Order::create([
    'customer_id' => $customer->id,
    'merchant_id' => $merchant->id,
    'driver_id' => $driver->id,
    'delivery_address' => 'Test Delivery Address',
    'dest_latitude' => -7.9826,
    'dest_longitude' => 112.6308,
    'total_price' => 50000,
    'delivery_fee' => 10000,
    'status' => 'delivery'
]);

echo "✓ Test Order created: #" . $order->id . "\n";
echo "  Destination: -7.9826, 112.6308 (Alun-alun Malang)\n";
echo "  Tracking URL: http://localhost/order/track/" . $order->id . "\n\n";

// Update driver location a few times
$locations = [
    [-7.98, 112.63],  // Near merchant
    [-7.9826, 112.6310],  // Towards delivery location
    [-7.9826, 112.6308],  // At delivery location
];

foreach ($locations as $i => $loc) {
    $driver->update(['latitude' => $loc[0], 'longitude' => $loc[1]]);
    echo "✓ Driver location update " . ($i + 1) . ": " . $loc[0] . ", " . $loc[1] . "\n";
    sleep(1);
}

echo "\n✅ Test complete!\n";
echo "📍 Open tracking page to see live updates: http://localhost/order/track/" . $order->id . "\n";
