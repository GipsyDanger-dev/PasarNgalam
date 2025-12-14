<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController; 
use App\Models\User;
use Illuminate\Support\Facades\Storage;

// --- HALAMAN UTAMA (USER DASHBOARD / LANDING PAGE) ---
Route::get('/', function () {
    // Menampilkan Merchant yang aktif
    $merchants = User::where('role', 'merchant')
        ->where('is_active', true)
        ->whereHas('products')
        ->get();
        
    return view('welcome', compact('merchants')); // Pastikan view-nya 'welcome' atau 'user.dashboard'
})->name('home');


// --- AUTHENTICATION ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::get('/register', [AuthController::class, 'showLoginForm'])->name('register.view'); // GET hanya untuk tampilkan form
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// --- CUSTOMER ORDER FLOW ---
Route::middleware(['auth'])->group(function () {
    
    // 1. Halaman Checkout
    // Pastikan method di controller bernama 'indexCheckout' atau sesuaikan (biasanya 'index' atau 'create')
    // Jika mengikuti kode sebelumnya, kita tidak pakai controller khusus untuk view ini, tapi oke kalau mau pakai.
    // Sederhananya pakai view langsung:
    Route::get('/checkout', function() { return view('checkout'); })->name('checkout');
    
    // 2. Proses Submit Order
    Route::post('/checkout/process', [OrderController::class, 'processCheckout'])->name('checkout.process');
    // Guard untuk akses GET agar tidak 405
    Route::get('/checkout/process', function () {
        return redirect()->route('checkout');
    });

    // 3. Halaman Tracking & Detail
    Route::get('/order/track/{id}', [OrderController::class, 'trackOrder'])->name('order.track');

    // 4. Halaman Pembayaran + Konfirmasi
    Route::get('/order/{id}/payment', [OrderController::class, 'showPayment'])->name('order.payment');
    Route::post('/order/{id}/payment/confirm', [OrderController::class, 'confirmPayment'])->name('order.payment.confirm');
    
    // 6. Submit Review (Rating)
    Route::post('/order/{id}/review', [OrderController::class, 'submitReview'])->name('order.review');

});


// --- MERCHANT AREA ---
Route::middleware(['auth'])->group(function () {
    Route::get('/merchant/dashboard', [MerchantController::class, 'index'])->name('merchant.dashboard');

    // PERBAIKAN PENTING: Nama route disamakan dengan JS di dashboard
    Route::get('/merchant/orders/api', [MerchantController::class, 'getPendingOrdersApi'])->name('merchant.orders.api');

    // API Hitung Jumlah Order (Opsional)
    Route::get('/merchant/orders/count', [MerchantController::class, 'countPendingOrders'])->name('merchant.orders.count');

    // Manajemen Produk
    Route::post('/merchant/product', [MerchantController::class, 'storeProduct'])->name('merchant.product.store');
    Route::put('/merchant/product/{id}', [MerchantController::class, 'updateProduct'])->name('merchant.product.update');
    Route::delete('/merchant/product/{id}', [MerchantController::class, 'deleteProduct'])->name('merchant.product.delete');

    // Update Status Order (Terima/Masak/Selesai)
    Route::put('/merchant/order/{id}/update', [OrderController::class, 'updateStatus'])->name('merchant.order.update');
});


// --- DRIVER AREA ---
Route::middleware(['auth'])->group(function () {
    Route::get('/driver/dashboard', [DriverController::class, 'index'])->name('driver.dashboard');
    
    // Operasional Driver
    Route::post('/driver/update-location', [DriverController::class, 'updateLocation']); 
    Route::post('/driver/toggle-status', [DriverController::class, 'toggleStatus'])->name('driver.toggle'); 
    
    // Order Action (Selesaikan)
    Route::post('/driver/order/{id}/complete', [DriverController::class, 'completeOrder'])->name('driver.order.complete'); 
    Route::post('/driver/order/{id}/accept', [DriverController::class, 'acceptOrder'])->name('driver.order.accept'); 
});


// --- GLOBAL USER PROFILE ---
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Fallback untuk melayani file di disk 'public' tanpa symlink di hosting
Route::get('/storage/{path}', function ($path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    $absolutePath = Storage::disk('public')->path($path);
    $mime = function_exists('mime_content_type') ? mime_content_type($absolutePath) : 'application/octet-stream';
    return response()->file($absolutePath, ['Content-Type' => $mime]);
})->where('path', '.*');
