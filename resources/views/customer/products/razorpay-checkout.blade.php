<x-customer-layout :title="$title" active="products">

<div class="card-flat p-3 mb-3">
  <div class="section-title mb-2">Order Summary</div>
  <div class="dc-row"><span class="text-muted-fin">Order No.</span><span>{{ $order->order_no }}</span></div>
  @foreach($order->items as $item)
    <div class="dc-row"><span class="text-muted-fin">{{ $item->product->name ?? 'Product' }}</span><span>× {{ $item->quantity }}</span></div>
  @endforeach
  <div class="dc-row"><span class="text-muted-fin fw-semibold">Amount to Pay</span><span class="fw-bold">₹{{ number_format($order->total_amount, 2) }}</span></div>
</div>

<div class="card-flat p-3 text-center" id="checkoutCard">
  <i class="fa-solid fa-shield-halved text-primary" style="font-size:2rem;"></i>
  <div class="fw-semibold mt-2 mb-3">Secure payment powered by Razorpay</div>
  <button class="btn btn-primary-fin w-100" id="btnPayNow" type="button"><i class="fa-solid fa-lock me-1"></i>Pay ₹{{ number_format($order->total_amount, 2) }}</button>
  <div id="payError" class="alert alert-danger mt-3 d-none" style="font-size:.85rem;"></div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('btnPayNow').addEventListener('click', function(){
  var btn = this;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Opening secure checkout...';

  var options = {
    key: @json($razorpayKeyId),
    amount: {{ (int) round($order->total_amount * 100) }},
    currency: 'INR',
    name: 'BluePeak Fintech',
    description: 'Order {{ $order->order_no }}',
    order_id: @json($order->razorpay_order_id),
    prefill: {
      name: @json($customer->user->name),
      contact: @json($customer->user->mobile)
    },
    theme: { color: '#0b5cc5' },
    handler: function(response){
      fetch(@json(route('customer.orders.verify', $order)), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': @json(csrf_token())
        },
        body: JSON.stringify({
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_signature: response.razorpay_signature
        })
      }).then(function(r){ return r.json(); }).then(function(data){
        if(data.ok){
          window.location.href = data.redirect;
        } else {
          showPayError(data.message || 'Payment verification failed.');
        }
      }).catch(function(){
        showPayError('Could not verify payment. If any amount was deducted, contact support with your Order No.');
      });
    },
    modal: {
      ondismiss: function(){
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-lock me-1"></i>Pay ₹{{ number_format($order->total_amount, 2) }}';
      }
    }
  };

  var rzp = new Razorpay(options);
  rzp.on('payment.failed', function(response){
    showPayError('Payment failed: ' + (response.error && response.error.description ? response.error.description : 'please try again.'));
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-lock me-1"></i>Pay ₹{{ number_format($order->total_amount, 2) }}';
  });
  rzp.open();
});

function showPayError(msg){
  var el = document.getElementById('payError');
  el.textContent = msg;
  el.classList.remove('d-none');
}
</script>
</x-customer-layout>
