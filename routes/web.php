<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MealPlanController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware('auth')->get('/dashboard', function () {
    $user = auth()->user();

    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('user.dashboard');
})->name('dashboard');



// Admin Routes
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/refresh', [DashboardController::class, 'refreshStats'])->name('dashboard.refresh');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');

    // Menu Management
    Route::resource('menu', MenuController::class);
    Route::patch('menu/{menuItem}/toggle-status', [MenuController::class, 'toggleStatus'])
        ->name('menu.toggle-status');

    // Meal Plan Management
    Route::resource('meal-plans', MealPlanController::class);
    Route::patch('meal-plans/{mealPlan}/toggle-status', [MealPlanController::class, 'toggleStatus'])
        ->name('meal-plans.toggle-status');

    // Order Management
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('orders.update-status');
    Route::patch('orders/{order}/assign-driver', [OrderController::class, 'assignDriver'])
        ->name('orders.assign-driver');
    Route::post('orders/bulk-update', [OrderController::class, 'bulkUpdateStatus'])
        ->name('orders.bulk-update');

    // User Management
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])
        ->name('users.toggle-admin');
    Route::patch('users/{user}/verify-email', [UserController::class, 'verifyEmail'])
        ->name('users.verify-email');
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])
        ->name('users.bulk-action');

    // Inventory Management
    Route::resource('inventory', InventoryController::class);
    Route::patch('inventory/{inventory}/update-stock', [InventoryController::class, 'updateStock'])
        ->name('inventory.update-stock');
    Route::post('inventory/bulk-update-stock', [InventoryController::class, 'bulkUpdateStock'])
        ->name('inventory.bulk-update-stock');
    Route::get('inventory-alerts/low-stock', [InventoryController::class, 'lowStockAlert'])
        ->name('inventory.low-stock-alert');
    Route::get('inventory-alerts/expiring', [InventoryController::class, 'expiringItems'])
        ->name('inventory.expiring-items');

    // Subscription Management
    Route::resource('subscriptions', SubscriptionController::class);
    Route::patch('subscriptions/{subscription}/update-status', [SubscriptionController::class, 'updateStatus'])
        ->name('subscriptions.update-status');

    // Plans Meal Management
    Route::resource('plans', MealPlanController::class);
});

require __DIR__ . '/auth.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/user.php';
