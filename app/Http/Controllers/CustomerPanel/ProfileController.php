<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.profile', [
            'title' => 'Profile', 'active' => 'profile',
            'customer' => $request->user()->customer,
        ]);
    }
}
