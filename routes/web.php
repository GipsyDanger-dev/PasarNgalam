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

Route::get('/order/track/{id}', [OrderController::class, 'track'])->name('order.track');

// MERCHANT AREA (Wajib Login sebagai Merchant)
Route::middleware(['auth'])->group(function () {

    Route::get('/merchant/dashboard', [MerchantController::class, 'index'])->name('merchant.dashboard');
    Route::post('/merchant/product', [MerchantController::class, 'storeProduct'])->name('merchant.product.store');
    Route::put('/merchant/product/{id}', [MerchantController::class, 'updateProduct'])->name('merchant.product.update');
    Route::delete('/merchant/product/{id}', [MerchantController::class, 'deleteProduct'])->name('merchant.product.delete');

    Route::put('/merchant/order/{id}/update', [OrderController::class, 'updateStatus'])->name('merchant.order.update');
});


//  DRIVER AREA (Wajib Login sebagai Driver) 
Route::middleware(['auth'])->group(function () {
    
    Route::get('/driver/dashboard', [DriverController::class, 'index'])->name('driver.dashboard');
    
    // Fitur Driver
    Route::post('/driver/update-location', [DriverController::class, 'updateLocation']); // GPS Tracker
    Route::post('/driver/toggle-status', [DriverController::class, 'toggleStatus'])->name('driver.toggle'); // On/Off Bid
    Route::post('/driver/order/{id}/complete', [DriverController::class, 'completeOrder'])->name('driver.order.complete'); // Selesaikan Order
});


//  GLOBAL PROFILE UPDATE (Bisa diakses Merchant & Driver & Customer) 
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});