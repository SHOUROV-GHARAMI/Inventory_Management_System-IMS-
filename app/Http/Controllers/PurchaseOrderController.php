<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        if ($request->has('search')) {
            $query->where('order_number', 'LIKE', "%{$request->search}%");
        }

        $sortBy = $request->get('sort_by', 'order_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
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

            $orderNumber = 'PO-' . date('Ymd') . '-' . str_pad(PurchaseOrder::count() + 1, 5, '0', STR_PAD_LEFT);

            $order = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'supplier_id' => $request->supplier_id,
                'created_by' => auth()->id(),
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'draft',
                'tax' => $request->tax ?? 0,
                'discount' => $request->discount ?? 0,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) 
                           + ($item['tax'] ?? 0) 
                           - ($item['discount'] ?? 0);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax' => $item['tax'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            $order->calculateTotals();

            DB::commit();

            return response()->json([
                'message' => 'Purchase order created successfully',
                'data' => $order->load(['supplier', 'items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return response()->json([
            'data' => $purchaseOrder->load(['supplier', 'creator', 'items.product'])
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return response()->json([
                'message' => 'Cannot update a purchase order that is already received or cancelled'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'order_date' => 'sometimes|required|date',
            'expected_delivery_date' => 'nullable|date',
            'status' => 'sometimes|in:draft,pending,approved,received,cancelled',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $purchaseOrder->update($validator->validated());

        return response()->json([
            'message' => 'Purchase order updated successfully',
            'data' => $purchaseOrder->load(['supplier', 'items.product'])
        ]);
    }

    public function approve(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending purchase orders can be approved'
            ], 422);
        }

        $purchaseOrder->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Purchase order approved successfully',
            'data' => $purchaseOrder
        ]);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status === 'received') {
            return response()->json([
                'message' => 'Purchase order already received'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'received_date' => 'required|date',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|integer|min:0',
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
                $orderItem = PurchaseOrderItem::findOrFail($item['id']);
                $receivedQty = $item['received_quantity'];

                $orderItem->update(['received_quantity' => $receivedQty]);

                $product = Product::findOrFail($orderItem->product_id);
                $oldStock = $product->quantity_in_stock;
                $product->increment('quantity_in_stock', $receivedQty);

                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'type' => 'purchase',
                    'quantity' => $receivedQty,
                    'balance_after' => $oldStock + $receivedQty,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $purchaseOrder->id,
                    'created_by' => auth()->id(),
                    'notes' => "Received from PO: {$purchaseOrder->order_number}",
                ]);
            }

            $purchaseOrder->update([
                'status' => 'received',
                'received_date' => $request->received_date,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Purchase order received and inventory updated successfully',
                'data' => $purchaseOrder->load(['items.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to receive purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status === 'received') {
            return response()->json([
                'message' => 'Cannot cancel a received purchase order'
            ], 422);
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Purchase order cancelled successfully',
            'data' => $purchaseOrder
        ]);
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status === 'received') {
            return response()->json([
                'message' => 'Cannot delete a received purchase order'
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json([
            'message' => 'Purchase order deleted successfully'
        ]);
    }
}
