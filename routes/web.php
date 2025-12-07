<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController; 

// --- HALAMAN PUBLIK ---
Route::get('/', function () {
    $merchants = \App\Models\User::where('role', 'merchant')
        ->whereHas('products') // Hanya tampilkan merchant yang punya produk
        ->with(['products' => function($query) {
            $query->where('is_available', true);
        }])
        ->get();
    return view('welcome', compact('merchants'));
})->name('home');

// --- AUTHENTICATION (Login, Register, Logout) ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// --- CUSTOMER CHECKOUT ---
Route::get('/checkout', function () {
    return view('checkout');
})->name('checkout'); 

// Proses Checkout (Masuk ke OrderController)
Route::post('/checkout-process', [OrderController::class, 'checkout'])->name('checkout.process');

// Payment Page (NEW)
Route::get('/order/{id}/payment', [OrderController::class, 'payment'])->name('order.payment');
Route::post('/order/{id}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('order.confirmPayment');

Route::get('/order/track/{id}', [OrderController::class, 'track'])->name('order.track');

// API untuk realtime location tracking
Route::get('/api/order/{id}/location', [OrderController::class, 'getLocationData']);

// Development helper: create a test order with known coordinates
// Only available when app environment is local or debug is true
Route::get('/dev/create-test-order', function () {
    if (!app()->environment('local') && !config('app.debug')) {
        abort(404);
    }

    $merchant = \App\Models\User::where('role', 'merchant')->first();
    $driver = \App\Models\User::where('role', 'driver')->first();
    $customer = \App\Models\User::where('role', 'user')->first();

    if (!$merchant || !$driver || !$customer) {
        return response('Please ensure at least one merchant, driver and user exist in DB.', 400);
    }

    $order = \App\Models\Order::create([
        'customer_id' => $customer->id,
        'merchant_id' => $merchant->id,
        'driver_id' => $driver->id,
        'delivery_address' => 'Test Address',
        'dest_latitude' => -7.9826,
        'dest_longitude' => 112.6308,
        'total_price' => 10000,
        'delivery_fee' => 5000,
        'status' => 'pending'
    ]);

    $link = route('order.track', $order->id);
    return "Test order created: <a href=\"{$link}\">Open tracking for order #{$order->id}</a>";
});

// Development helper: simulate driver sending location updates
// POST /dev/simulate-driver-location with lat/lng to update the first driver's position
Route::post('/dev/simulate-driver-location', function (\Illuminate\Http\Request $request) {
    if (!app()->environment('local') && !config('app.debug')) {
        abort(404);
    }

    $driver = \App\Models\User::where('role', 'driver')->first();
    if (!$driver) {
        return response()->json(['error' => 'No driver found'], 400);
    }

    $lat = $request->input('latitude', $driver->latitude ?? -7.98);
    $lng = $request->input('longitude', $driver->longitude ?? 112.63);

    $driver->update(['latitude' => $lat, 'longitude' => $lng]);

    // Also broadcast the update
    try {
        event(new \App\Events\DriverLocationUpdated($driver->id, $lat, $lng));
    } catch (\Exception $e) {
        \Log::warning('Broadcast failed in dev helper: ' . $e->getMessage());
    }

    return response()->json([
        'status' => 'ok',
        'driver_id' => $driver->id,
        'latitude' => $lat,
        'longitude' => $lng,
        'message' => 'Driver location updated and broadcast'
    ]);
});

// MERCHANT AREA (Wajib Login sebagai Merchant)
Route::middleware(['auth'])->group(function () {

    Route::get('/merchant/dashboard', function () {
        if (!Auth::check() || Auth::user()->role !== 'merchant') {
            return redirect('/')->with('error', 'Akses ditolak.');
        }
        return app(\App\Http\Controllers\MerchantController::class)->index();
    })->name('merchant.dashboard');

    // NEW: API for realtime order notifications
    Route::get('/api/merchant/recent-orders', function (\Illuminate\Http\Request $request) {
        if (!Auth::check() || Auth::user()->role !== 'merchant') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return app(\App\Http\Controllers\MerchantController::class)->getRecentOrders($request);
    })->name('merchant.api.recent-orders');

    Route::post('/merchant/product', function (\Illuminate\Http\Request $request) {
        if (!Auth::check() || Auth::user()->role !== 'merchant') {
            return redirect('/')->with('error', 'Akses ditolak.');
        }
        return app(\App\Http\Controllers\MerchantController::class)->storeProduct($request);
    })->name('merchant.product.store');

    Route::put('/merchant/product/{id}', function (\Illuminate\Http\Request $request, $id) {
        if (!Auth::check() || Auth::user()->role !== 'merchant') {
            return redirect('/')->with('error', 'Akses ditolak.');
        }
        return app(\App\Http\Controllers\MerchantController::class)->updateProduct($request, $id);
    })->name('merchant.product.update');

    Route::delete('/merchant/product/{id}', function ($id) {
        if (!Auth::check() || Auth::user()->role !== 'merchant') {
            return redirect('/')->with('error', 'Akses ditolak.');
        }
        return app(\App\Http\Controllers\MerchantController::class)->deleteProduct($id);
    })->name('merchant.product.delete');

    Route::put('/merchant/order/{id}/update', function (\Illuminate\Http\Request $request, $id) {
        if (!Auth::check() || Auth::user()->role !== 'merchant') {
            return redirect('/')->with('error', 'Akses ditolak.');
        }
        return app(\App\Http\Controllers\OrderController::class)->updateStatus($request, $id);
    })->name('merchant.order.update');
});


//  DRIVER AREA (Wajib Login sebagai Driver) 
Route::middleware(['auth'])->group(function () {
    
    Route::get('/driver/dashboard', [DriverController::class, 'index'])->name('driver.dashboard');
    
    // Fitur Driver
    Route::post('/driver/update-location', [DriverController::class, 'updateLocation']); // GPS Tracker
    Route::post('/driver/order/{id}/accept', [DriverController::class, 'acceptOrder'])->name('driver.order.accept'); // Driver konfirmasi makanan siap / ambil
    Route::post('/driver/toggle-status', [DriverController::class, 'toggleStatus'])->name('driver.toggle'); // On/Off Bid
    Route::post('/driver/order/{id}/complete', [DriverController::class, 'completeOrder'])->name('driver.order.complete'); // Selesaikan Order
});


//  GLOBAL PROFILE UPDATE (Bisa diakses Merchant & Driver & Customer) 
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});