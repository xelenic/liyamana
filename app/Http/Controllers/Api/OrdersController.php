<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * List orders for the authenticated user (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 50);

        $orders = Order::where('user_id', auth()->id())
            ->with('template:id,name')
            ->latest()
            ->paginate($perPage);

        $items = $orders->getCollection()->map(fn (Order $o) => $this->formatOrder($o))->values()->all();

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Show a single order.
     */
    public function show(string $id): JsonResponse
    {
        $order = Order::with('template:id,name')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatOrderDetail($order),
        ]);
    }

    private function formatOrder(Order $o): array
    {
        return [
            'id' => $o->id,
            'template_id' => $o->template_id,
            'template_name' => $o->template_name,
            'quantity' => $o->quantity,
            'total_amount' => (float) $o->total_amount,
            'payment_method' => $o->payment_method,
            'status' => $o->status,
            'delivery_status' => $o->delivery_status,
            'created_at' => $o->created_at->toIso8601String(),
        ];
    }

    private function formatOrderDetail(Order $o): array
    {
        $data = $this->formatOrder($o);
        $data['checkout_data'] = $o->checkout_data;
        $data['invoice_url'] = route('orders.invoice', ['id' => $o->id]);

        return $data;
    }
}
