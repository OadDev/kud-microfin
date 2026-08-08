<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Loan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\CodeGenerator;
use App\Services\CustomerNotifier;
use App\Services\EmiScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;
        $items = $customer->cartItems()->with('product')->get();

        // EMI financing only makes sense for a single financeable product
        // line at a time -- see checkout() for why.
        $financeableSingleItem = $items->count() === 1 && $items->first()->product->financeable()
            ? $items->first()
            : null;

        return view('customer.cart.index', [
            'title' => 'My Cart', 'active' => 'products',
            'items' => $items,
            'total' => $items->sum(fn (CartItem $i) => $i->lineTotal()),
            'financeableSingleItem' => $financeableSingleItem,
            'razorpayEnabled' => PaymentSetting::current()->razorpayEnabled(),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);
        $quantity = $data['quantity'] ?? 1;
        $customer = $request->user()->customer;

        $item = CartItem::firstOrNew(['customer_id' => $customer->id, 'product_id' => $product->id]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->save();

        return back()->with('success', $product->name.' added to cart.');
    }

    public function updateQuantity(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $cartItem->update(['quantity' => $data['quantity']]);

        return back();
    }

    public function remove(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);
        $cartItem->delete();

        return back()->with('success', 'Removed from cart.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $customer = $request->user()->customer;
        $items = $customer->cartItems()->with('product')->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $data = $request->validate([
            'delivery_address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:cod,razorpay,emi_financing'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'num_installments' => ['nullable', 'integer', 'min:1', 'max:24'],
        ]);

        foreach ($items as $item) {
            if ($item->product->stock_quantity !== null && $item->quantity > $item->product->stock_quantity) {
                return back()->with('error', "Only {$item->product->stock_quantity} unit(s) of {$item->product->name} left in stock.");
            }
        }

        if ($data['payment_method'] === 'razorpay' && ! PaymentSetting::current()->razorpayEnabled()) {
            return back()->with('error', 'Online payment is not available right now — please choose Cash on Delivery.');
        }

        if ($data['payment_method'] === 'emi_financing') {
            if ($items->count() > 1) {
                return back()->with('error', 'EMI financing is only available when checking out one financeable product at a time. Please remove other items from your cart first.');
            }
            $product = $items->first()->product;
            if (! $product->financeable()) {
                return back()->with('error', 'This product is not eligible for EMI financing.');
            }
            $minDownPayment = (float) $product->down_payment;
            $downPayment = (float) ($data['down_payment'] ?? $minDownPayment);
            if ($downPayment < $minDownPayment) {
                return back()->with('error', 'Down payment must be at least ₹'.number_format($minDownPayment, 2).'.');
            }
        }

        $totalAmount = $items->sum(fn (CartItem $i) => $i->lineTotal());

        $order = DB::transaction(function () use ($items, $customer, $data, $totalAmount) {
            $order = Order::create([
                'order_no' => CodeGenerator::nextOrderNo(),
                'customer_id' => $customer->id,
                'total_amount' => $totalAmount,
                'down_payment_amount' => $data['payment_method'] === 'emi_financing' ? ($data['down_payment'] ?? null) : null,
                'delivery_address' => $data['delivery_address'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->price,
                    'line_total' => $item->lineTotal(),
                ]);

                if ($item->product->stock_quantity !== null) {
                    $item->product->decrement('stock_quantity', $item->quantity);
                }
            }

            if ($data['payment_method'] === 'emi_financing') {
                $this->createFinancingLoan($order, $items->first(), $customer, (float) $data['down_payment'], (int) ($data['num_installments'] ?? 6));
            }

            $customer->cartItems()->delete();

            return $order;
        });

        // Notify Admin of every order placed (email + in-app). Best-effort --
        // a broken mail server must not turn a successfully placed order
        // into a 500 for the customer.
        try {
            Notification::send(User::where('role', 'admin')->get(), new NewOrderNotification($order));
        } catch (\Throwable $e) {
            report($e);
        }

        CustomerNotifier::send('order_placed', $customer, [
            'order_no' => $order->order_no,
            'amount' => number_format((float) $order->total_amount, 2),
            'payment_method' => $data['payment_method'] === 'emi_financing' ? 'EMI Financing' : strtoupper($data['payment_method']),
        ]);

        if ($data['payment_method'] === 'cod') {
            return redirect()->route('customer.orders.show', $order)->with('success', 'Order placed successfully! Pay cash on delivery.');
        }
        if ($data['payment_method'] === 'emi_financing') {
            return redirect()->route('customer.orders.show', $order)->with('success', 'Order placed! Your EMI financing application is awaiting Admin approval.');
        }

        return redirect()->route('customer.orders.pay', $order);
    }

    protected function createFinancingLoan(Order $order, CartItem $item, $customer, float $downPayment, int $numInstallments): void
    {
        $devicePrice = (float) $item->product->price * $item->quantity;
        $principal = max(0, $devicePrice - $downPayment);
        $emiAmount = $numInstallments > 0 ? round($principal / $numInstallments) : 0;

        $loan = Loan::create([
            'customer_id' => $customer->id,
            'shop_owner_id' => $customer->shop_owner_id,
            'loan_account_no' => CodeGenerator::nextLoanAccountNo(),
            'purpose' => 'Purchase: '.$item->product->name,
            'principal' => $principal,
            'interest' => 0,
            'processing_fee' => 0,
            'total_payable' => $principal,
            'num_emis' => $numInstallments,
            'emi_amount' => $emiAmount,
            'frequency' => 'Monthly',
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonthNoOverflow()->toDateString(),
            'status' => 'pending',
            'loan_type' => 'product',
            'order_id' => $order->id,
        ]);

        EmiScheduleService::generate($loan);
        $order->update(['loan_id' => $loan->id]);
    }

    protected function authorizeItem(Request $request, CartItem $cartItem): void
    {
        abort_unless($cartItem->customer_id === $request->user()->customer->id, 403);
    }
}
