<x-customer-layout :title="$title" active="helpline" pageTitle="Helpline" backUrl="{{ route('customer.home') }}">

<div class="card-flat p-4 text-center">
  <i class="fa-solid fa-headset text-primary" style="font-size:2.6rem;"></i>
  <h5 class="fw-bold mt-3 mb-1">Need Help?</h5>
  <div class="small-note mb-4">Our support team is just a call away.</div>

  <a href="tel:+917002128302" class="btn btn-primary-fin w-100 py-3 mb-2" style="font-size:1.1rem;">
    <i class="fa-solid fa-phone me-2"></i>+91 70021 28302
  </a>
  <a href="https://wa.me/917002128302" target="_blank" rel="noopener" class="btn w-100 py-3 text-white" style="font-size:1.1rem;background:#25D366;border-color:#25D366;">
    <i class="fa-brands fa-whatsapp me-2"></i>Chat on WhatsApp
  </a>
  <div class="small-note mt-3">Tap a button above to call or chat with us directly.</div>
</div>
</x-customer-layout>
