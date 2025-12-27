<?php

namespace App\Http\Controllers;

use App\Models\LowStockAlert;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LowStockAlertController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = LowStockAlert::with('product.category');

        if ($request->has('is_resolved')) {
            $query->where('is_resolved', $request->boolean('is_resolved'));
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $alerts = $query->paginate($perPage);

        return response()->json($alerts);
    }

    public function checkAndCreate(): JsonResponse
    {
        $lowStockProducts = Product::whereColumn('quantity_in_stock', '<=', 'minimum_stock_level')
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->get();

        $createdAlerts = 0;

        foreach ($lowStockProducts as $product) {

            $existingAlert = LowStockAlert::where('product_id', $product->id)
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                LowStockAlert::create([
                    'product_id' => $product->id,
                    'current_stock' => $product->quantity_in_stock,
                    'minimum_stock' => $product->minimum_stock_level,
                    'is_resolved' => false,
                ]);
                $createdAlerts++;
            }
        }

        return response()->json([
            'message' => "Created {$createdAlerts} new low stock alerts",
            'data' => [
                'created_count' => $createdAlerts,
                'total_low_stock_products' => $lowStockProducts->count(),
            ]
        ]);
    }

    public function resolve(LowStockAlert $alert): JsonResponse
    {
        if ($alert->is_resolved) {
            return response()->json([
                'message' => 'Alert is already resolved'
            ], 422);
        }

        $alert->resolve();

        return response()->json([
            'message' => 'Alert resolved successfully',
            'data' => $alert
        ]);
    }

    public function autoResolve(): JsonResponse
    {
        $unresolvedAlerts = LowStockAlert::where('is_resolved', false)
            ->with('product')
            ->get();

        $resolvedCount = 0;

        foreach ($unresolvedAlerts as $alert) {
            $product = $alert->product;

            if ($product && $product->quantity_in_stock > $product->minimum_stock_level) {
                $alert->resolve();
                $resolvedCount++;
            }
        }

        return response()->json([
            'message' => "Auto-resolved {$resolvedCount} alerts",
            'data' => [
                'resolved_count' => $resolvedCount,
            ]
        ]);
    }

    public function unresolvedCount(): JsonResponse
    {
        $count = LowStockAlert::where('is_resolved', false)->count();

        return response()->json([
            'data' => [
                'unresolved_count' => $count,
            ]
        ]);
    }

    public function destroy(LowStockAlert $alert): JsonResponse
    {
        $alert->delete();

        return response()->json([
            'message' => 'Alert deleted successfully'
        ]);
    }
}
