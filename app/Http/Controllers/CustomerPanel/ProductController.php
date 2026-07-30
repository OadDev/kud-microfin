<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Services\CodeGenerator;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category')->where('is_active', true);

        if ($categoryId = $request->query('category')) {
            $query->where('category_id', $categoryId);
        }
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        return view('customer.products.index', [
            'title' => 'Shop', 'active' => 'products',
            'products' => $query->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'selectedCategory' => $categoryId,
            'search' => $search ?? '',
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('customer.products.show', [
            'title' => $product->name, 'active' => 'products',
            'product' => $product,
            'razorpayEnabled' => PaymentSetting::current()->razorpayEnabled(),
        ]);
    }

    public function buyNow(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:cod,razorpay'],
        ]);

        if ($product->stock_quantity !== null && $data['quantity'] > $product->stock_quantity) {
            return back()->withInput()->with('error', 'Only '.$product->stock_quantity.' unit(s) left in stock.');
        }

        if ($data['payment_method'] === 'razorpay' && ! PaymentSetting::current()->razorpayEnabled()) {
            return back()->withInput()->with('error', 'Online payment is not available right now — please choose Cash on Delivery.');
        }

        $customer = $request->user()->customer;
        $totalAmount = $product->price * $data['quantity'];

        $order = DB::transaction(function () use ($product, $customer, $data, $totalAmount) {
            $order = Order::create([
                'order_no' => CodeGenerator::nextOrderNo(),
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'unit_price' => $product->price,
                'total_amount' => $totalAmount,
                'delivery_address' => $data['delivery_address'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            if ($product->stock_quantity !== null) {
                $product->decrement('stock_quantity', $data['quantity']);
            }

            return $order;
        });

        if ($data['payment_method'] === 'cod') {
            return redirect()->route('customer.orders.show', $order)
                ->with('success', 'Order placed successfully! Pay cash on delivery.');
        }

        // Razorpay: hand off to the checkout page, which opens Checkout.js.
        return redirect()->route('customer.orders.pay', $order);
    }

    public function pay(Order $order, RazorpayService $razorpay): View|RedirectResponse
    {
        $this->authorizeOwner($order);
        abort_unless($order->payment_method === 'razorpay', 404);
        abort_if($order->payment_status === 'paid', 404);

        $settings = PaymentSetting::current();

        if (! $order->razorpay_order_id) {
            try {
                $order->update(['razorpay_order_id' => $razorpay->createOrder($order)]);
            } catch (\Throwable $e) {
                report($e);

                return redirect()->route('customer.orders.show', $order)
                    ->with('error', 'Online payment could not be started right now. Please try again in a moment, or contact support.');
            }
        }

        return view('customer.products.razorpay-checkout', [
            'title' => 'Complete Payment',
            'order' => $order,
            'razorpayKeyId' => $settings->razorpay_key_id,
            'customer' => $order->customer,
        ]);
    }

    public function verify(Request $request, Order $order, RazorpayService $razorpay): JsonResponse
    {
        $this->authorizeOwner($order);

        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        if ($data['razorpay_order_id'] !== $order->razorpay_order_id) {
            return response()->json(['ok' => false, 'message' => 'Order mismatch.'], 422);
        }

        $verified = $razorpay->verifySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']);

        if (! $verified) {
            $order->update(['payment_status' => 'failed']);

            return response()->json(['ok' => false, 'message' => 'Payment verification failed.'], 422);
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_signature' => $data['razorpay_signature'],
        ]);

        return response()->json(['ok' => true, 'redirect' => route('customer.orders.show', $order)]);
    }

    protected function authorizeOwner(Order $order): void
    {
        abort_unless($order->customer_id === request()->user()->customer->id, 403);
    }
}
