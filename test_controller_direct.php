<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;

$controller = new OrderController();
$fakeRequest = new Request(['id' => 12]);

try {
    $response = $controller->getLocationData(12);
    echo $response->getContent();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}
