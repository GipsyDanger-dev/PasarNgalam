<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\User;

// Dapatkan order ID dari command line argument
$orderId = isset($argv[1]) ? (int)$argv[1] : 14;

$order = Order::with(['driver', 'merchant', 'customer'])->find($orderId);

if (!$order || !$order->driver) {
    echo "❌ Order #$orderId tidak ditemukan atau tidak ada driver!\n";
    exit;
}

echo "🎯 Simulasi Driver Movement untuk Order #$orderId\n";
echo "🛵 Driver: " . $order->driver->name . "\n";
echo "📍 Dari Warung (" . $order->merchant->latitude . ", " . $order->merchant->longitude . ")\n";
echo "📦 Ke Tujuan (" . $order->dest_latitude . ", " . $order->dest_longitude . ")\n";
echo "\n⏳ Memulai simulasi dalam 2 detik...\n\n";

sleep(2);

// Koordinat start (warung) dan end (tujuan)
$startLat = (float)$order->merchant->latitude;
$startLng = (float)$order->merchant->longitude;
$endLat = (float)$order->dest_latitude;
$endLng = (float)$order->dest_longitude;

// 10 step dari start ke end (setiap step 3 detik)
$steps = 10;

for ($i = 0; $i <= $steps; $i++) {
    // Interpolasi posisi driver antara start dan end
    $progress = $i / $steps;  // 0.0 sampai 1.0
    $newLat = $startLat + ($endLat - $startLat) * $progress;
    $newLng = $startLng + ($endLng - $startLng) * $progress;
    
    // Update driver position
    $order->driver->update([
        'latitude' => $newLat,
        'longitude' => $newLng
    ]);
    
    $percentage = (int)($progress * 100);
    echo "[" . str_repeat("=", $percentage/5) . str_repeat(" ", 20 - $percentage/5) . "] $percentage%\n";
    echo "   📍 " . number_format($newLat, 5) . ", " . number_format($newLng, 5) . "\n";
    
    if ($i < $steps) {
        sleep(3);  // Tunggu 3 detik sebelum update berikutnya
    }
}

echo "\n✅ Driver sudah sampai di tujuan!\n";
echo "📱 Refresh page di browser untuk lihat pergerakan marker.\n";
echo "💡 Tip: Buka page tracking di tab lain, jalankan script ini, dan lihat marker bergerak realtime!\n";
?>
