<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        if ($request->boolean('low_stock')) {
            $query->whereColumn('quantity_in_stock', '<=', 'minimum_stock_level');
        }

        if ($request->boolean('out_of_stock')) {
            $query->where('quantity_in_stock', 0);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $inventory = $query->paginate($perPage);

        return response()->json($inventory);
    }

    public function productTransactions(Product $product): JsonResponse
    {
        $transactions = $product->inventoryTransactions()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($transactions);
    }

    public function transactions(Request $request): JsonResponse
    {
        $query = InventoryTransaction::with(['product', 'creator']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }

    public function adjust(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|not_in:0',
            'type' => 'required|in:adjustment,damage,transfer,return',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            $oldStock = $product->quantity_in_stock;
            $newStock = $oldStock + $request->quantity;

            if ($newStock < 0) {
                throw new \Exception('Adjustment would result in negative stock');
            }

            $product->update(['quantity_in_stock' => $newStock]);

            InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'balance_after' => $newStock,
                'created_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Inventory adjusted successfully',
                'data' => [
                    'product' => $product,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'adjustment' => $request->quantity,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to adjust inventory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function summary(): JsonResponse
    {
        $totalProducts = Product::count();
        $lowStockProducts = Product::whereColumn('quantity_in_stock', '<=', 'minimum_stock_level')->count();
        $outOfStockProducts = Product::where('quantity_in_stock', 0)->count();
        $totalInventoryValue = Product::sum(DB::raw('quantity_in_stock * cost_price'));
        
        $recentTransactions = InventoryTransaction::with(['product', 'creator'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'total_products' => $totalProducts,
                'low_stock_products' => $lowStockProducts,
                'out_of_stock_products' => $outOfStockProducts,
                'total_inventory_value' => round($totalInventoryValue, 2),
                'recent_transactions' => $recentTransactions,
            ]
        ]);
    }
}
