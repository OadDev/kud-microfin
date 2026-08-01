<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        if ($brand = $request->query('brand')) {
            $query->where('brand', $brand);
        }
        if ($storage = $request->query('storage')) {
            $query->where('storage', $storage);
        }
        if ($ram = $request->query('ram')) {
            $query->where('ram', $ram);
        }
        if ($network = $request->query('network')) {
            $query->where('network_type', $network);
        }
        if ($priceMax = $request->query('price_max')) {
            $query->where('price', '<=', (float) $priceMax);
        }
        if ($request->query('financeable')) {
            $query->whereNotNull('down_payment');
        }

        $products = $query->orderBy('name')->get();

        if ($downMax = $request->query('down_payment_max')) {
            $products = $products->filter(fn (Product $p) => $p->down_payment !== null && (float) $p->down_payment <= (float) $downMax);
        }
        if ($emiMax = $request->query('emi_max')) {
            $products = $products->filter(fn (Product $p) => $p->referenceMonthlyEmi() !== null && $p->referenceMonthlyEmi() <= (float) $emiMax);
        }

        return view('customer.products.index', [
            'title' => 'Shop', 'active' => 'products',
            'products' => $products->values(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => AdminProductController::BRANDS,
            'storages' => Product::whereNotNull('storage')->distinct()->orderBy('storage')->pluck('storage'),
            'rams' => Product::whereNotNull('ram')->distinct()->orderBy('ram')->pluck('ram'),
            'filters' => $request->only(['category', 'search', 'brand', 'storage', 'ram', 'network', 'price_max', 'down_payment_max', 'emi_max', 'financeable']),
            'favouriteIds' => $request->user()->customer->favourites()->pluck('product_id')->all(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load('images', 'category');

        return view('customer.products.show', [
            'title' => $product->name, 'active' => 'products',
            'product' => $product,
            'razorpayEnabled' => PaymentSetting::current()->razorpayEnabled(),
            'inCart' => request()->user()->customer->cartItems()->where('product_id', $product->id)->exists(),
            'isFavourite' => request()->user()->customer->favourites()->where('product_id', $product->id)->exists(),
        ]);
    }

    public function pay(Order $order, RazorpayService $razorpay): View|RedirectResponse
    {
        $this->authorizeOwner($order);
        abort_unless($order->payment_method === 'razorpay', 404);
        abort_if($order->payment_status === 'paid', 404);

        $order->load('items.product');
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
