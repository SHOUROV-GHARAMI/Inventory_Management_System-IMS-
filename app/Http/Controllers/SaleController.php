<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        if ($request->has('search')) {
            $query->where('invoice_number', 'LIKE', "%{$request->search}%");
        }

        $sortBy = $request->get('sort_by', 'sale_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $sales = $query->paginate($perPage);

        return response()->json($sales);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:users,id',
            'sale_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->track_inventory && $product->quantity_in_stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->quantity_in_stock}");
                }
            }

            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 5, '0', STR_PAD_LEFT);

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $request->customer_id,
                'created_by' => auth()->id(),
                'sale_date' => $request->sale_date,
                'status' => 'draft',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'tax' => $request->tax ?? 0,
                'discount' => $request->discount ?? 0,
                'paid_amount' => $request->paid_amount ?? 0,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) 
                           + ($item['tax'] ?? 0) 
                           - ($item['discount'] ?? 0);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax' => $item['tax'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            $sale->calculateTotals();

            if ($sale->paid_amount >= $sale->total) {
                $sale->update(['payment_status' => 'paid']);
            } elseif ($sale->paid_amount > 0) {
                $sale->update(['payment_status' => 'partial']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Sale created successfully',
                'data' => $sale->load(['customer', 'items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Sale $sale): JsonResponse
    {
        return response()->json([
            'data' => $sale->load(['customer', 'creator', 'items.product'])
        ]);
    }

    public function update(Request $request, Sale $sale): JsonResponse
    {
        if (in_array($sale->status, ['completed', 'refunded'])) {
            return response()->json([
                'message' => 'Cannot update a completed or refunded sale'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:users,id',
            'sale_date' => 'sometimes|required|date',
            'status' => 'sometimes|in:draft,completed,cancelled,refunded',
            'payment_status' => 'sometimes|in:pending,paid,partial,refunded',
            'payment_method' => 'nullable|string|max:50',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $sale->update($validator->validated());

        if ($request->has('paid_amount')) {
            if ($sale->paid_amount >= $sale->total) {
                $sale->update(['payment_status' => 'paid']);
            } elseif ($sale->paid_amount > 0) {
                $sale->update(['payment_status' => 'partial']);
            } else {
                $sale->update(['payment_status' => 'pending']);
            }
        }

        return response()->json([
            'message' => 'Sale updated successfully',
            'data' => $sale->load(['customer', 'items.product'])
        ]);
    }

    public function complete(Sale $sale): JsonResponse
    {
        if ($sale->status === 'completed') {
            return response()->json([
                'message' => 'Sale already completed'
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($sale->items as $item) {
                $product = Product::findOrFail($item->product_id);

                if ($product->track_inventory && $product->quantity_in_stock < $item->quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                if ($product->track_inventory) {
                    $oldStock = $product->quantity_in_stock;
                    $product->decrement('quantity_in_stock', $item->quantity);

                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'type' => 'sale',
                        'quantity' => -$item->quantity,
                        'balance_after' => $oldStock - $item->quantity,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'created_by' => auth()->id(),
                        'notes' => "Sale: {$sale->invoice_number}",
                    ]);
                }
            }

            $sale->update(['status' => 'completed']);

            DB::commit();

            return response()->json([
                'message' => 'Sale completed and inventory updated successfully',
                'data' => $sale->load(['items.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to complete sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Sale $sale): JsonResponse
    {
        if ($sale->status === 'completed') {
            return response()->json([
                'message' => 'Cannot cancel a completed sale. Use refund instead.'
            ], 422);
        }

        $sale->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Sale cancelled successfully',
            'data' => $sale
        ]);
    }

    public function destroy(Sale $sale): JsonResponse
    {
        if ($sale->status === 'completed') {
            return response()->json([
                'message' => 'Cannot delete a completed sale'
            ], 422);
        }

        $sale->delete();

        return response()->json([
            'message' => 'Sale deleted successfully'
        ]);
    }
}
