<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['customer.user', 'items.product'])->latest('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return view('admin.orders.index', [
            'title' => 'Orders', 'active' => 'orders',
            'orders' => $query->get(),
            'status' => $status ?? 'All',
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer.user', 'items.product', 'loan']);

        return view('admin.orders.show', [
            'title' => 'Order '.$order->order_no, 'active' => 'orders',
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,shipped,delivered,cancelled'],
        ]);

        $statusChanged = $order->status !== $data['status'];

        $order->update($data);

        // Marking a COD order delivered is treated as the point of collection.
        if ($data['status'] === 'delivered' && $order->payment_method === 'cod' && $order->payment_status === 'pending') {
            $order->update(['payment_status' => 'paid']);
        }

        if ($statusChanged) {
            CustomerNotifier::send('order_status_changed', $order->customer, [
                'order_no' => $order->order_no,
                'status' => ucfirst($data['status']),
            ]);
        }

        return back()->with('success', 'Order status updated.');
    }

    public function markCodPaid(Order $order): RedirectResponse
    {
        abort_unless($order->payment_method === 'cod', 400, 'Only Cash on Delivery orders can be marked paid this way.');

        $order->update(['payment_status' => 'paid']);

        return back()->with('success', 'Order marked as paid (cash collected).');
    }
}
