<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;

$order = Order::with(['driver', 'merchant'])->find(9);
if ($order) {
    echo "Order #9 found\n";
    echo "Driver: " . ($order->driver ? $order->driver->name : "NULL") . "\n";
    echo "Driver Lat: " . ($order->driver?->latitude ?? "NULL") . "\n";
    echo "Driver Lng: " . ($order->driver?->longitude ?? "NULL") . "\n";
    echo "\nAPI Response would be:\n";
    echo json_encode([
        'order_id' => $order->id,
        'dest_latitude' => $order->dest_latitude,
        'dest_longitude' => $order->dest_longitude,
        'driver_latitude' => $order->driver?->latitude,
        'driver_longitude' => $order->driver?->longitude,
        'merchant_latitude' => $order->merchant?->latitude,
        'merchant_longitude' => $order->merchant?->longitude,
        'status' => $order->status,
    ], JSON_PRETTY_PRINT);
} else {
    echo "Order #9 NOT found\n";
}
