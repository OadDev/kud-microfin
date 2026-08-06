<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\ShopOwner;
use App\Models\User;
use App\Services\CodeGenerator;
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

        if ($status = $request->query('status')) {
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
            'interest' => ['required', 'numeric', 'min:0'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'num_emis' => ['required', 'integer', 'min:1'],
            'frequency' => ['required', 'in:Weekly,Monthly'],
            'start_date' => ['required', 'date'],
            'first_due_date' => ['required', 'date'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'shop_owner_id' => $isAdmin ? ['required', 'exists:shop_owners,id'] : ['nullable'],
        ]);

        $shopOwnerId = $isAdmin ? (int) $data['shop_owner_id'] : $user->shopOwner->id;

        $loan = DB::transaction(function () use ($data, $request, $shopOwnerId) {
            $customerUser = User::create([
                'name' => $data['full_name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make(str()->random(10)),
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
            $interest = (float) $data['interest'];
            $fee = (float) ($data['fee'] ?? 0);
            $totalPayable = $principal + $interest + $fee;
            $numEmis = (int) $data['num_emis'];
            $emiAmount = round($totalPayable / $numEmis);

            $loan = Loan::create([
                'customer_id' => $customer->id,
                'shop_owner_id' => $shopOwnerId,
                'loan_account_no' => CodeGenerator::nextLoanAccountNo(),
                'purpose' => $data['purpose'],
                'principal' => $principal,
                'interest' => $interest,
                'processing_fee' => $fee,
                'total_payable' => $totalPayable,
                'num_emis' => $numEmis,
                'emi_amount' => $emiAmount,
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
            ->with('created_customer_id', $loan->customer_id);
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
