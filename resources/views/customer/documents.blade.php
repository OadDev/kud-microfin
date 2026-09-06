<x-customer-layout :title="$title" :active="$active" pageTitle="My Documents">
<div class="row g-3">
  <x-document-card label="Welcome Letter" icon="fa-envelope-open-text" :loan="$loan" type="welcome_letter" :previewable="(bool) $loan" />
  <x-document-card label="Loan Sanction Letter" icon="fa-file-signature" :loan="$loan" type="sanction_letter" :previewable="(bool) $loan" />
  <x-document-card label="EMI Schedule" icon="fa-calendar-check" />
  <x-document-card label="Payment Receipts" icon="fa-receipt" :href="route('customer.documents.receipts')" note="One receipt per approved payment" />
  <x-document-card label="Customer Statement" icon="fa-file-lines" />
</div>
</x-customer-layout>
