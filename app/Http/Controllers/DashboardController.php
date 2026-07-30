<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\PaymentSubmission;
use App\Models\ShopOwner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $loans = $isAdmin
            ? Loan::with(['customer', 'emis', 'documents'])->get()
            : Loan::with(['customer', 'emis', 'documents'])->where('shop_owner_id', $user->shopOwner->id)->get();

        $customers = $isAdmin
            ? Customer::with('user')->latest('id')->take(5)->get()
            : Customer::with('user')->where('shop_owner_id', $user->shopOwner->id)->latest('id')->take(5)->get();

        $upcomingEmis = $loans->flatMap(function (Loan $loan) {
            return $loan->emis->filter(fn ($emi) => in_array($emi->displayStatus(), ['Upcoming', 'Due Today']))
                ->map(fn ($emi) => (object) ['emi' => $emi, 'loan' => $loan]);
        })->sortBy(fn ($row) => $row->emi->due_date)->take(6);

        $emiCollected = $loans->sum(fn (Loan $loan) => $loan->amountPaid());
        $totalOutstanding = $loans->sum(fn (Loan $loan) => $loan->outstanding());
        $overdueEmis = $loans->sum(fn (Loan $loan) => $loan->emis->filter(fn ($e) => $e->displayStatus() === 'Overdue')->count());
        $activeLoans = $loans->whereIn('status', ['active', 'overdue'])->count();

        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i);
            $chartLabels[] = $month->format('M');
            $chartData[] = (int) $loans->flatMap->emis
                ->where('status', 'paid')
                ->filter(fn ($e) => $e->payment_date && $e->payment_date->isSameMonth($month) && $e->payment_date->isSameYear($month))
                ->sum('amount');
        }

        if ($isAdmin) {
            $pendingVerifications = PaymentSubmission::where('status', 'under_verification')
                ->with('customer')->latest('submitted_at')->take(5)->get();

            $stats = [
                'total_shop_owners' => ShopOwner::count(),
                'pending_shop_owners' => ShopOwner::whereHas('user', fn ($q) => $q->where('status', 'pending'))->count(),
                'total_customers' => Customer::count(),
                'active_loans' => $activeLoans,
                'total_outstanding' => $totalOutstanding,
                'emi_collected' => $emiCollected,
                'pending_verifications' => $pendingVerifications->count(),
                'overdue_emis' => $overdueEmis,
            ];

            return view('shared.dashboard', [
                'title' => 'Dashboard', 'active' => 'dashboard', 'isAdmin' => true,
                'stats' => $stats, 'customers' => $customers, 'upcomingEmis' => $upcomingEmis,
                'pendingVerifications' => $pendingVerifications,
                'chartLabels' => $chartLabels, 'chartData' => $chartData,
            ]);
        }

        $pendingDocs = $loans->filter(function (Loan $loan) {
            $signedTypes = $loan->documents->where('signed_file_path', '!=', null)->pluck('type');

            return $signedTypes->count() < 2;
        })->count();

        $stats = [
            'total_customers' => Customer::where('shop_owner_id', $user->shopOwner->id)->count(),
            'active_loans' => $activeLoans,
            'emi_collected' => $emiCollected,
            'pending_emi' => $loans->sum(fn (Loan $loan) => $loan->emis->filter(fn ($e) => in_array($e->displayStatus(), ['Upcoming', 'Due Today']))->count()),
            'overdue_emi' => $overdueEmis,
            'pending_documents' => $pendingDocs,
        ];

        return view('shared.dashboard', [
            'title' => 'Dashboard', 'active' => 'dashboard', 'isAdmin' => false,
            'stats' => $stats, 'customers' => $customers, 'upcomingEmis' => $upcomingEmis,
            'chartLabels' => $chartLabels, 'chartData' => $chartData,
        ]);
    }
}
