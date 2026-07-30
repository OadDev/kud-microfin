<?php

namespace App\Http\Controllers;

use App\Models\Emi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmiController extends Controller
{
    public function index(Request $request): View
    {
        $shopOwnerId = $request->user()->shopOwner->id;

        $rows = Emi::with(['loan.customer.user'])
            ->whereHas('loan', fn ($q) => $q->where('shop_owner_id', $shopOwnerId))
            ->get()
            ->sortBy('due_date');

        return view('shopowner.emi-list', [
            'title' => 'EMI List', 'active' => 'emi-list',
            'rows' => $rows,
        ]);
    }
}
