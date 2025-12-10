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
    // Menampilkan Merchant yang punya produk aktif
    $merchants = \App\Models\User::where('role', 'merchant')
        ->whereHas('products') 
        ->with(['products' => function($query) {
            $query->where('is_available', true);
        }])
        ->get();
    return view('welcome', compact('merchants'));
})->name('home');

// --- AUTHENTICATION ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- CUSTOMER ORDER FLOW ---
Route::get('/checkout', function () { return view('checkout'); })->name('checkout'); 
Route::post('/checkout-process', [OrderController::class, 'checkout'])->name('checkout.process');

// Payment & Tracking
Route::get('/order/{id}/payment', [OrderController::class, 'payment'])->name('order.payment');
Route::post('/order/{id}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('order.confirmPayment');
Route::get('/order/track/{id}', [OrderController::class, 'track'])->name('order.track');

// API Public (Untuk Tracking Map di halaman Customer)
Route::get('/api/order/{id}/location', [OrderController::class, 'getLocationData']);


// --- MERCHANT AREA ---
// Menggunakan middleware auth. Pengecekan role 'merchant' dilakukan di dalam Controller
Route::middleware(['auth'])->group(function () {

    Route::get('/merchant/dashboard', [MerchantController::class, 'index'])->name('merchant.dashboard');

    // === [PENTING] ROUTE API REALTIME NOTIFIKASI ===
    // Route ini wajib ada agar fitur suara & auto-reload berfungsi
    Route::get('/merchant/orders/api', [MerchantController::class, 'getPendingOrdersApi'])->name('merchant.orders.api');

    // CRUD Produk
    Route::post('/merchant/product', [MerchantController::class, 'storeProduct'])->name('merchant.product.store');
    Route::put('/merchant/product/{id}', [MerchantController::class, 'updateProduct'])->name('merchant.product.update');
    Route::delete('/merchant/product/{id}', [MerchantController::class, 'deleteProduct'])->name('merchant.product.delete');

    // Update Status Order (Terima/Masak/Selesai)
    // Note: Ini mengarah ke OrderController karena logika update status ada di sana, tapi diakses merchant
    Route::put('/merchant/order/{id}/update', [OrderController::class, 'updateStatus'])->name('merchant.order.update');
});


// --- DRIVER AREA ---
Route::middleware(['auth'])->group(function () {
    Route::get('/driver/dashboard', [DriverController::class, 'index'])->name('driver.dashboard');
    
    // Fitur Operasional Driver
    Route::post('/driver/update-location', [DriverController::class, 'updateLocation']); 
    Route::post('/driver/order/{id}/accept', [DriverController::class, 'acceptOrder'])->name('driver.order.accept'); 
    Route::post('/driver/toggle-status', [DriverController::class, 'toggleStatus'])->name('driver.toggle'); 
    Route::post('/driver/order/{id}/complete', [DriverController::class, 'completeOrder'])->name('driver.order.complete'); 
});


// --- GLOBAL USER PROFILE ---
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});


// --- DEV HELPERS (Untuk Testing Localhost) ---
// Hanya aktif jika environment local atau debug mode on
if (app()->environment('local') || config('app.debug')) {
    
    Route::get('/dev/create-test-order', function () {
        $merchant = \App\Models\User::where('role', 'merchant')->first();
        $driver = \App\Models\User::where('role', 'driver')->first();
        $customer = \App\Models\User::where('role', 'user')->first();

        if (!$merchant || !$driver || !$customer) return 'Pastikan User, Merchant, dan Driver sudah ada di database.';

        $order = \App\Models\Order::create([
            'customer_id' => $customer->id,
            'merchant_id' => $merchant->id,
            'driver_id' => $driver->id,
            'delivery_address' => 'Test Address Dev',
            'dest_latitude' => -7.9826,
            'dest_longitude' => 112.6308,
            'total_price' => 15000,
            'delivery_fee' => 7000,
            'status' => 'pending',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'payment_code' => 'DEVTEST',
            'items' => []
        ]);

        return redirect()->route('order.track', $order->id);
    });
}