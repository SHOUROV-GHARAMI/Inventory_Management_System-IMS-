<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LowStockAlertController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ProductImageController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/health', [HealthController::class, 'check']);
Route::get('/info', [HealthController::class, 'info']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Product Management
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->middleware('permission:products.view');
        Route::post('/', [ProductController::class, 'store'])->middleware('permission:products.create');
        Route::get('/low-stock', [ProductController::class, 'lowStock'])->middleware('permission:products.view');
        Route::get('/needs-reorder', [ProductController::class, 'needsReorder'])->middleware('permission:products.view');
        Route::get('/{product}', [ProductController::class, 'show'])->middleware('permission:products.view');
        Route::put('/{product}', [ProductController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.delete');
        
        // Product Images
        Route::post('/{product}/image', [ProductImageController::class, 'upload'])->middleware('permission:products.update');
        Route::post('/{product}/images', [ProductImageController::class, 'uploadMultiple'])->middleware('permission:products.update');
        Route::delete('/{product}/image', [ProductImageController::class, 'delete'])->middleware('permission:products.update');
        Route::delete('/{product}/images/remove', [ProductImageController::class, 'deleteFromGallery'])->middleware('permission:products.update');
    });

    // Category Management
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{category}', [CategoryController::class, 'show']);
        Route::put('/{category}', [CategoryController::class, 'update']);
        Route::delete('/{category}', [CategoryController::class, 'destroy']);
    });

    // Supplier Management
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('/{supplier}', [SupplierController::class, 'show']);
        Route::put('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
    });

    // Purchase Order Management
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::post('/', [PurchaseOrderController::class, 'store']);
        Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
        Route::post('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve']);
        Route::post('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
        Route::post('/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
        Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);
    });

    // Sales Management
    Route::prefix('sales')->group(function () {
        Route::get('/', [SaleController::class, 'index']);
        Route::post('/', [SaleController::class, 'store']);
        Route::get('/{sale}', [SaleController::class, 'show']);
        Route::put('/{sale}', [SaleController::class, 'update']);
        Route::post('/{sale}/complete', [SaleController::class, 'complete']);
        Route::post('/{sale}/cancel', [SaleController::class, 'cancel']);
        Route::delete('/{sale}', [SaleController::class, 'destroy']);
    });

    // Inventory Management
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::get('/summary', [InventoryController::class, 'summary']);
        Route::get('/transactions', [InventoryController::class, 'transactions']);
        Route::get('/products/{product}/transactions', [InventoryController::class, 'productTransactions']);
        Route::post('/adjust', [InventoryController::class, 'adjust']);
    });

    // Low Stock Alerts
    Route::prefix('low-stock-alerts')->group(function () {
        Route::get('/', [LowStockAlertController::class, 'index']);
        Route::get('/count', [LowStockAlertController::class, 'unresolvedCount']);
        Route::post('/check', [LowStockAlertController::class, 'checkAndCreate']);
        Route::post('/auto-resolve', [LowStockAlertController::class, 'autoResolve']);
        Route::post('/{alert}/resolve', [LowStockAlertController::class, 'resolve']);
        Route::delete('/{alert}', [LowStockAlertController::class, 'destroy']);
    });

    // Reports & Analytics
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard', [ReportController::class, 'dashboard']);
        Route::get('/sales', [ReportController::class, 'salesReport']);
        Route::get('/inventory', [ReportController::class, 'inventoryReport']);
        Route::get('/suppliers', [ReportController::class, 'supplierReport']);
        Route::get('/product-movement', [ReportController::class, 'productMovementReport']);
    });

    // Import/Export
    Route::prefix('import-export')->group(function () {
        // Export
        Route::get('/export/products', [ImportExportController::class, 'exportProducts']);
        Route::get('/export/suppliers', [ImportExportController::class, 'exportSuppliers']);
        
        // Import
        Route::post('/import/products', [ImportExportController::class, 'importProducts']);
        Route::post('/import/suppliers', [ImportExportController::class, 'importSuppliers']);
        
        // Templates
        Route::get('/template/products', [ImportExportController::class, 'productTemplate']);
        Route::get('/template/suppliers', [ImportExportController::class, 'supplierTemplate']);
    });

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Settings Management
    Route::prefix('settings')->middleware('role:Admin')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('/{key}', [SettingController::class, 'show']);
        Route::put('/', [SettingController::class, 'update']);
        Route::delete('/{key}', [SettingController::class, 'destroy']);
        Route::post('/initialize', [SettingController::class, 'initializeDefaults']);
    });

    // Audit Logs
    Route::prefix('audit-logs')->middleware('role:Admin,Manager')->group(function () {
        Route::get('/', [AuditLogController::class, 'index']);
        Route::get('/statistics', [AuditLogController::class, 'statistics']);
        Route::get('/{id}', [AuditLogController::class, 'show']);
        Route::delete('/cleanup', [AuditLogController::class, 'cleanup']);
    });

    // Backup & Restore
    Route::prefix('backups')->middleware('role:Admin')->group(function () {
        Route::get('/', [BackupController::class, 'index']);
        Route::post('/', [BackupController::class, 'create']);
        Route::get('/{filename}', [BackupController::class, 'download']);
        Route::post('/{filename}/restore', [BackupController::class, 'restore']);
        Route::delete('/{filename}', [BackupController::class, 'destroy']);
    });
});

