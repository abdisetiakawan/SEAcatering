<?php

use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\MealPlanController;
use App\Http\Controllers\User\MenuController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Menu
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/{menuItem}', [MenuController::class, 'show'])->name('show');
    });

    // Meal Plans
    Route::prefix('meal-plans')->name('meal-plans.')->group(function () {
        Route::get('/', [MealPlanController::class, 'index'])->name('index');
        Route::get('/{mealPlan}', [MealPlanController::class, 'show'])->name('show');
        Route::post('/{mealPlan}/subscribe', [MealPlanController::class, 'subscribe'])->name('subscribe');
    });

    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{cart}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cart}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::post('/apply-promo', [CartController::class, 'applyPromo'])->name('apply-promo');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/reorder', [OrderController::class, 'reorder'])->name('reorder');
        Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder');
    });

    // Payment
    Route::get('/orders/{order}/payment', [OrderController::class, 'paymentPage'])->name('orders.payment');
    Route::post('/orders/{order}/payment', [OrderController::class, 'processPayment'])->name('orders.payment.process');

    // Subscriptions
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::patch('/{subscription}/pause', [SubscriptionController::class, 'pause'])->name('pause');
        Route::patch('/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('resume');
        Route::patch('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::patch('/{subscription}/modify', [SubscriptionController::class, 'modify'])->name('modify');
        // Payment Subcription
        Route::get('/{subscription}/payment', [SubscriptionController::class, 'paymentPage'])->name('payment');
        Route::post('/{subscription}/payment', [SubscriptionController::class, 'processPayment'])->name('process-payment');
    });


    // Checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/', [CheckoutController::class, 'store'])->name('store');
    });

    // Addresses
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('/create/{order}/{menuItem}', [ReviewController::class, 'create'])->name('create');
        Route::post('/', [ReviewController::class, 'store'])->name('store');
        Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    // Reviews
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::post('/', [ReviewController::class, 'store'])->name('store');
        Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});
