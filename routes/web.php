<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\Staff\AprioriController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\OrderController;
use App\Http\Controllers\Staff\ProductController;
use App\Http\Controllers\Staff\ReportController;
use App\Http\Controllers\Staff\SettingController;
use App\Http\Controllers\Staff\UserController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('home');

Route::prefix('keranjang')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/tambah/{product}', [CartController::class, 'add'])->name('add');
    Route::patch('/{product}', [CartController::class, 'update'])->name('update');
    Route::delete('/{product}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/pesanan/{invoice}/struk', [CheckoutController::class, 'receipt'])->name('orders.receipt');

Route::get('/cek-pesanan', [OrderTrackingController::class, 'show'])->name('orders.track');
Route::post('/cek-pesanan', [OrderTrackingController::class, 'lookup'])->name('orders.track.lookup');
Route::get('/cek-pesanan/{invoice}', [OrderTrackingController::class, 'detail'])->name('orders.track.detail');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:admin,kasir'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class)->except(['show']);

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/payment-proof', [OrderController::class, 'paymentProof'])->name('orders.payment-proof');

    Route::middleware('role:admin')->group(function () {
        Route::get('/apriori', [AprioriController::class, 'index'])->name('apriori.index');
        Route::post('/apriori/run', [AprioriController::class, 'run'])->name('apriori.run');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
