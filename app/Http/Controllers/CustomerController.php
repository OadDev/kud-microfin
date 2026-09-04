<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\PaymentSetting;
use App\Models\ShopOwner;
use App\Models\User;
use App\Services\CodeGenerator;
use App\Services\EmiQuoteService;
use App\Services\EmiScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $query = Customer::with(['user', 'loans' => fn ($q) => $q->latest('id')]);
        if (! $isAdmin) {
            $query->where('shop_owner_id', $user->shopOwner->id);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"))
                    ->orWhereHas('loans', fn ($l) => $l->where('loan_account_no', 'like', "%{$search}%"));
            });
        }

        $customers = $query->get();

        // Filtering/sorting by loan status is Admin-only -- Shop Owner
        // doesn't see EMI/loan status at all, so the query param is simply
        // ignored rather than silently leaking it through which customers
        // get filtered in or out.
        if ($isAdmin && ($status = $request->query('status'))) {
            $customers = $customers->filter(fn (Customer $c) => $c->currentLoan()?->status === $status);
        }

        return view('shared.customers.index', [
            'title' => $isAdmin ? 'Customer List' : 'My Customers', 'active' => 'customers', 'isAdmin' => $isAdmin,
            'customers' => $customers, 'search' => $search ?? '', 'status' => $status ?? 'All',
        ]);
    }

    public function create(Request $request): View
    {
        $isAdmin = $request->user()->isAdmin();

        return view('shared.customers.create', [
            'title' => 'Create Customer', 'active' => 'create-customer', 'isAdmin' => $isAdmin,
            'shopOwners' => $isAdmin ? ShopOwner::whereHas('user', fn ($q) => $q->where('status', 'approved'))->with('user')->get() : null,
            'defaultRate' => (float) PaymentSetting::current()->product_emi_interest_rate,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mobile' => ['required', 'digits:10', 'unique:users,mobile'],
            'alt_mobile' => ['nullable', 'digits:10'],
            'email' => ['nullable', 'email', 'max:255'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'pin' => ['nullable', 'digits:6'],
            'pan' => ['required', 'string', 'max:10'],
            'aadhaar' => ['required', 'digits:12'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'aadhaar_doc' => ['nullable', 'file', 'max:4096'],
            'pan_doc' => ['nullable', 'file', 'max:4096'],
            'purpose' => ['required', 'string', 'max:255'],
            'principal' => ['required', 'numeric', 'min:1'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'num_emis' => ['required', 'integer', 'min:1'],
            'frequency' => ['required', 'in:Weekly,Monthly'],
            'start_date' => ['required', 'date'],
            'first_due_date' => ['required', 'date'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'shop_owner_id' => $isAdmin ? ['required', 'exists:shop_owners,id'] : ['nullable'],
        ]);

        $shopOwnerId = $isAdmin ? (int) $data['shop_owner_id'] : $user->shopOwner->id;

        // A 6-digit numeric password (not a random string) so it's easy for
        // staff to read aloud/type when handing it to the customer -- there's
        // no SMS/email delivery guaranteed, so this is communicated manually.
        $tempPassword = (string) random_int(100000, 999999);

        $loan = DB::transaction(function () use ($data, $request, $shopOwnerId, $tempPassword) {
            $customerUser = User::create([
                'name' => $data['full_name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($tempPassword),
                'role' => 'customer',
                'status' => 'approved',
            ]);

            $customer = Customer::create([
                'user_id' => $customerUser->id,
                'customer_code' => CodeGenerator::nextCustomerCode(),
                'father_name' => $data['father_name'] ?? null,
                'alt_mobile' => $data['alt_mobile'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'] ?? null,
                'pin' => $data['pin'] ?? null,
                'pan' => strtoupper($data['pan']),
                'aadhaar' => $data['aadhaar'],
                'shop_owner_id' => $shopOwnerId,
            ]);

            if ($request->hasFile('photo')) {
                $customer->photo_path = $request->file('photo')->store("customers/{$customer->id}", 'public');
            }
            if ($request->hasFile('aadhaar_doc')) {
                $customer->aadhaar_doc_path = $request->file('aadhaar_doc')->store("customers/{$customer->id}", 'public');
            }
            if ($request->hasFile('pan_doc')) {
                $customer->pan_doc_path = $request->file('pan_doc')->store("customers/{$customer->id}", 'public');
            }
            $customer->save();

            $principal = (float) $data['principal'];
            $rate = (float) $data['interest_rate'];
            $fee = (float) ($data['fee'] ?? 0);
            $numEmis = (int) $data['num_emis'];
            $quote = EmiQuoteService::quote($principal, 0, $fee, $numEmis, $rate);

            $loan = Loan::create([
                'customer_id' => $customer->id,
                'shop_owner_id' => $shopOwnerId,
                'loan_account_no' => CodeGenerator::nextLoanAccountNo(),
                'purpose' => $data['purpose'],
                'principal' => $principal,
                'interest' => $quote['interest'],
                'processing_fee' => $fee,
                'total_payable' => $quote['total_payable'],
                'num_emis' => $numEmis,
                'emi_amount' => $quote['installment'],
                'frequency' => $data['frequency'],
                'late_fee' => $data['late_fee'] ?? 200,
                'start_date' => $data['start_date'],
                'first_due_date' => $data['first_due_date'],
                'status' => 'pending',
            ]);

            EmiScheduleService::generate($loan);

            return $loan;
        });

        return redirect()
            ->route($isAdmin ? 'admin.customers.create' : 'shopowner.customers.create')
            ->with('success', "Customer and loan created successfully: {$loan->customer->customer_code}")
            ->with('created_loan_id', $loan->id)
            ->with('created_customer_id', $loan->customer_id)
            ->with('created_customer_password', $tempPassword);
    }

    /**
     * Admin-only, and only when the customer never had a loan -- once a
     * loan exists, its EMI/payment history is a financial record that must
     * be retained (see the Privacy Policy's data-retention commitment), not
     * something a delete button should be able to wipe via the DB cascade.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->loans()->exists()) {
            return back()->with('error', 'This customer has loan history and cannot be deleted -- loan and payment records must be retained.');
        }

        foreach ([$customer->photo_path, $customer->aadhaar_doc_path, $customer->pan_doc_path] as $path) {
            if ($path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }

        $customer->user->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }

    public function show(Request $request, Customer $customer): View
    {
        $user = $request->user();
        $allowed = $user->isAdmin()
            || ($user->isShopOwner() && $customer->shop_owner_id === $user->shopOwner->id)
            || ($user->isCustomer() && $customer->id === $user->customer->id);

        abort_unless($allowed, 403);

        $customer->load(['user', 'shopOwner', 'loans.emis', 'loans.paymentSubmissions', 'loans.documents']);
        $loan = $customer->currentLoan();

        // EMI/payment tracking is Admin-only -- Shop Owner sees customer and
        // loan-approval details but not payment progress, so these tabs
        // aren't reachable even via a direct URL.
        $tab = $request->query('tab', 'overview');
        if (! $user->isAdmin() && in_array($tab, ['emi', 'payments'], true)) {
            $tab = 'overview';
        }

        return view('shared.customers.show', [
            'title' => 'Customer Details',
            'customer' => $customer,
            'loan' => $loan,
            'tab' => $tab,
        ]);
    }
}
