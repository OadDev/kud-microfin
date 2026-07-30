<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $customers = $isAdmin
            ? Customer::with(['user', 'loans'])->get()
            : Customer::with(['user', 'loans'])->where('shop_owner_id', $user->shopOwner->id)->get();

        return view('shared.documents.index', [
            'title' => 'Documents', 'active' => 'documents',
            'customers' => $customers,
        ]);
    }

    public function show(Request $request, Loan $loan, string $type): View
    {
        $this->authorizeAccess($request, $loan);

        $document = Document::firstOrCreate(
            ['loan_id' => $loan->id, 'type' => $type],
            ['generated_at' => now()]
        );
        if (! $document->generated_at) {
            $document->update(['generated_at' => now()]);
        }

        $loan->load('customer.user', 'customer.shopOwner', 'shopOwner.user');

        return view('documents.'.($type === 'welcome_letter' ? 'welcome-letter' : 'sanction-letter'), [
            'loan' => $loan,
            'document' => $document,
            'canUploadSigned' => $request->user()->isAdmin() || $request->user()->isShopOwner(),
        ]);
    }

    public function storeSigned(Request $request, Loan $loan, string $type): RedirectResponse
    {
        $this->authorizeAccess($request, $loan);
        abort_if($request->user()->isCustomer(), 403);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:8192'],
            'signed_at' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $document = Document::firstOrCreate(['loan_id' => $loan->id, 'type' => $type], ['generated_at' => now()]);
        $path = $request->file('file')->store("documents/{$loan->id}", 'public');

        $document->update([
            'signed_file_path' => $path,
            'signed_at' => $data['signed_at'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        return back()->with('success', 'Signed document uploaded successfully.');
    }

    protected function authorizeAccess(Request $request, Loan $loan): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }
        if ($user->isShopOwner() && $loan->shop_owner_id === $user->shopOwner->id) {
            return;
        }
        if ($user->isCustomer() && $loan->customer_id === $user->customer->id) {
            return;
        }

        abort(403);
    }
}
