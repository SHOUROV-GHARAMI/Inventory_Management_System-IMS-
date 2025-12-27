<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    
    public function dashboard(): JsonResponse
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $todaySales = Sale::whereDate('sale_date', $today)->where('status', 'completed')->sum('total');
        $monthSales = Sale::whereDate('sale_date', '>=', $thisMonth)->where('status', 'completed')->sum('total');
        $lastMonthSales = Sale::whereBetween('sale_date', [$lastMonth, $thisMonth])
            ->where('status', 'completed')
            ->sum('total');

        $monthPurchases = PurchaseOrder::whereDate('order_date', '>=', $thisMonth)->sum('total');

        $totalProducts = Product::count();
        $lowStockProducts = Product::whereColumn('quantity_in_stock', '<=', 'minimum_stock_level')->count();
        $outOfStockProducts = Product::where('quantity_in_stock', 0)->count();
        $totalInventoryValue = Product::sum(DB::raw('quantity_in_stock * cost_price'));

        $recentSales = Sale::with(['customer', 'creator'])
            ->where('status', 'completed')
            ->latest('sale_date')
            ->limit(5)
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', 'products.sku', DB::raw('SUM(sale_items.quantity) as total_sold'))
            ->whereDate('sales.sale_date', '>=', $thisMonth)
            ->where('sales.status', 'completed')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'sales' => [
                    'today' => round($todaySales, 2),
                    'this_month' => round($monthSales, 2),
                    'last_month' => round($lastMonthSales, 2),
                    'growth_percentage' => $lastMonthSales > 0 
                        ? round((($monthSales - $lastMonthSales) / $lastMonthSales) * 100, 2) 
                        : 0,
                ],
                'purchases' => [
                    'this_month' => round($monthPurchases, 2),
                ],
                'inventory' => [
                    'total_products' => $totalProducts,
                    'low_stock_products' => $lowStockProducts,
                    'out_of_stock_products' => $outOfStockProducts,
                    'total_value' => round($totalInventoryValue, 2),
                ],
                'recent_sales' => $recentSales,
                'top_products' => $topProducts,
            ]
        ]);
    }

    public function salesReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfDay());

        $sales = Sale::with(['customer', 'items.product'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->orderBy('sale_date', 'desc')
            ->get();

        $totalSales = $sales->sum('total');
        $totalTax = $sales->sum('tax');
        $totalDiscount = $sales->sum('discount');
        $totalProfit = $sales->sum(function($sale) {
            return $sale->items->sum(function($item) {
                return ($item->unit_price - $item->product->cost_price) * $item->quantity;
            });
        });

        $salesByPaymentMethod = $sales->groupBy('payment_method')
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total' => round($group->sum('total'), 2),
                ];
            });

        $dailySales = $sales->groupBy(function($sale) {
            return $sale->sale_date->format('Y-m-d');
        })->map(function($group) {
            return round($group->sum('total'), 2);
        });

        return response()->json([
            'data' => [
                'summary' => [
                    'total_sales' => round($totalSales, 2),
                    'total_tax' => round($totalTax, 2),
                    'total_discount' => round($totalDiscount, 2),
                    'total_profit' => round($totalProfit, 2),
                    'average_sale' => $sales->count() > 0 ? round($totalSales / $sales->count(), 2) : 0,
                    'total_transactions' => $sales->count(),
                ],
                'sales_by_payment_method' => $salesByPaymentMethod,
                'daily_sales' => $dailySales,
                'sales' => $sales,
            ]
        ]);
    }

    public function inventoryReport(Request $request): JsonResponse
    {
        $query = Product::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get();

        $totalValue = $products->sum(function($product) {
            return $product->quantity_in_stock * $product->cost_price;
        });

        $potentialValue = $products->sum(function($product) {
            return $product->quantity_in_stock * $product->selling_price;
        });

        $lowStockItems = $products->filter(function($product) {
            return $product->isLowStock();
        });

        $outOfStockItems = $products->filter(function($product) {
            return $product->quantity_in_stock == 0;
        });

        $inventoryByCategory = $products->groupBy('category.name')
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_quantity' => $group->sum('quantity_in_stock'),
                    'total_value' => round($group->sum(function($p) {
                        return $p->quantity_in_stock * $p->cost_price;
                    }), 2),
                ];
            });

        return response()->json([
            'data' => [
                'summary' => [
                    'total_products' => $products->count(),
                    'total_stock_value' => round($totalValue, 2),
                    'potential_value' => round($potentialValue, 2),
                    'potential_profit' => round($potentialValue - $totalValue, 2),
                    'low_stock_count' => $lowStockItems->count(),
                    'out_of_stock_count' => $outOfStockItems->count(),
                ],
                'inventory_by_category' => $inventoryByCategory,
                'low_stock_items' => $lowStockItems->values(),
                'out_of_stock_items' => $outOfStockItems->values(),
            ]
        ]);
    }

    public function supplierReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now()->endOfDay());

        $suppliers = DB::table('suppliers')
            ->leftJoin('purchase_orders', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.company_name',
                'suppliers.email',
                DB::raw('COUNT(purchase_orders.id) as total_orders'),
                DB::raw('SUM(CASE WHEN purchase_orders.status = "received" THEN 1 ELSE 0 END) as completed_orders'),
                DB::raw('SUM(CASE WHEN purchase_orders.status = "received" THEN purchase_orders.total ELSE 0 END) as total_purchase_value')
            )
            ->whereBetween('purchase_orders.order_date', [$startDate, $endDate])
            ->orWhereNull('purchase_orders.id')
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.company_name', 'suppliers.email')
            ->get();

        return response()->json([
            'data' => [
                'suppliers' => $suppliers,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
            ]
        ]);
    }

    public function productMovementReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfDay());
        $productId = $request->get('product_id');

        $query = InventoryTransaction::with(['product', 'creator'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $byType = $transactions->groupBy('type')
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_quantity' => $group->sum('quantity'),
                ];
            });

        $byProduct = $transactions->groupBy('product.name')
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_in' => $group->where('quantity', '>', 0)->sum('quantity'),
                    'total_out' => abs($group->where('quantity', '<', 0)->sum('quantity')),
                ];
            });

        return response()->json([
            'data' => [
                'summary' => [
                    'total_transactions' => $transactions->count(),
                    'by_type' => $byType,
                    'by_product' => $byProduct,
                ],
                'transactions' => $transactions,
            ]
        ]);
    }
}
