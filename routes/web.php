<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

Route::get('/', function () {
    return redirect('/login');
});


Route::get('/login', [WebController::class, 'login'])->name('login');
Route::post('/login', [WebController::class, 'handleLogin'])->name('login.post');
Route::post('/logout', [WebController::class, 'logout'])->name('logout');

// Role-based routes - Super Admin
Route::prefix('super-admin')->group(function () {
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('super-admin.dashboard');
    Route::get('/products', [WebController::class, 'products'])->name('super-admin.products');
    Route::get('/categories', [WebController::class, 'categories'])->name('super-admin.categories');
    Route::get('/inventory', [WebController::class, 'inventory'])->name('super-admin.inventory');
    Route::get('/suppliers', [WebController::class, 'suppliers'])->name('super-admin.suppliers');
    Route::get('/purchase-orders', [WebController::class, 'purchaseOrders'])->name('super-admin.purchase-orders');
    Route::get('/sales', [WebController::class, 'sales'])->name('super-admin.sales');
    Route::get('/reports', [WebController::class, 'reports'])->name('super-admin.reports');
    Route::get('/alerts', [WebController::class, 'alerts'])->name('super-admin.alerts');
    Route::get('/users', [WebController::class, 'users'])->name('super-admin.users');
    Route::get('/settings', [WebController::class, 'settings'])->name('super-admin.settings');
    Route::get('/backups', [WebController::class, 'backups'])->name('super-admin.backups');
    Route::get('/audit-logs', [WebController::class, 'auditLogs'])->name('super-admin.audit-logs');
    Route::get('/profile', [WebController::class, 'profile'])->name('super-admin.profile');
});

// Role-based routes - Admin
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/products', [WebController::class, 'products'])->name('admin.products');
    Route::get('/categories', [WebController::class, 'categories'])->name('admin.categories');
    Route::get('/inventory', [WebController::class, 'inventory'])->name('admin.inventory');
    Route::get('/suppliers', [WebController::class, 'suppliers'])->name('admin.suppliers');
    Route::get('/purchase-orders', [WebController::class, 'purchaseOrders'])->name('admin.purchase-orders');
    Route::get('/sales', [WebController::class, 'sales'])->name('admin.sales');
    Route::get('/reports', [WebController::class, 'reports'])->name('admin.reports');
    Route::get('/alerts', [WebController::class, 'alerts'])->name('admin.alerts');
    Route::get('/users', [WebController::class, 'users'])->name('admin.users');
    Route::get('/profile', [WebController::class, 'profile'])->name('admin.profile');
});

// Role-based routes - Manager
Route::prefix('manager')->group(function () {
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/products', [WebController::class, 'products'])->name('manager.products');
    Route::get('/categories', [WebController::class, 'categories'])->name('manager.categories');
    Route::get('/inventory', [WebController::class, 'inventory'])->name('manager.inventory');
    Route::get('/suppliers', [WebController::class, 'suppliers'])->name('manager.suppliers');
    Route::get('/purchase-orders', [WebController::class, 'purchaseOrders'])->name('manager.purchase-orders');
    Route::get('/sales', [WebController::class, 'sales'])->name('manager.sales');
    Route::get('/reports', [WebController::class, 'reports'])->name('manager.reports');
    Route::get('/alerts', [WebController::class, 'alerts'])->name('manager.alerts');
    Route::get('/profile', [WebController::class, 'profile'])->name('manager.profile');
});

// Role-based routes - Staff
Route::prefix('staff')->group(function () {
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('staff.dashboard');
    Route::get('/products', [WebController::class, 'products'])->name('staff.products');
    Route::get('/categories', [WebController::class, 'categories'])->name('staff.categories');
    Route::get('/inventory', [WebController::class, 'inventory'])->name('staff.inventory');
    Route::get('/suppliers', [WebController::class, 'suppliers'])->name('staff.suppliers');
    Route::get('/purchase-orders', [WebController::class, 'purchaseOrders'])->name('staff.purchase-orders');
    Route::get('/sales', [WebController::class, 'sales'])->name('staff.sales');
    Route::get('/reports', [WebController::class, 'reports'])->name('staff.reports');
    Route::get('/alerts', [WebController::class, 'alerts'])->name('staff.alerts');
    Route::get('/profile', [WebController::class, 'profile'])->name('staff.profile');
});

