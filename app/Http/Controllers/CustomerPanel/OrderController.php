<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;

        return view('customer.orders.index', [
            'title' => 'My Orders', 'active' => 'products',
            'orders' => $customer->orders()->with('items.product')->latest('id')->get(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->customer_id === $request->user()->customer->id, 403);
        $order->load('items.product', 'loan');

        return view('customer.orders.show', [
            'title' => 'Order '.$order->order_no, 'active' => 'products',
            'order' => $order,
        ]);
    }
}
