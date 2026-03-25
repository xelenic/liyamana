<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display user's orders list
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('template')
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display order details (invoice view)
     */
    public function show($id)
    {
        $order = Order::with('template', 'user')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $orderProduct = null;
        if (! empty($order->checkout_data['product_id'])) {
            $orderProduct = \App\Models\Product::find($order->checkout_data['product_id']);
        }

        return view('orders.show', compact('order', 'orderProduct'));
    }

    /**
     * Download order invoice as PDF
     */
    public function invoice($id)
    {
        $order = Order::with('user', 'template')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $orderProduct = null;
        if (! empty($order->checkout_data['product_id'])) {
            $orderProduct = \App\Models\Product::find($order->checkout_data['product_id']);
        }

        $pdf = Pdf::loadView('pdf.order', compact('order', 'orderProduct'));

        return $pdf->download('invoice-order-'.$order->id.'.pdf');
    }
}
