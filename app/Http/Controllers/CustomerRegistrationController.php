<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Services\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Public customer self-registration -- name/email/mobile/password, no OTP
 * (there's no SMS gateway) and no approval step. Account is usable
 * immediately, same as one created by Admin/Shop Owner.
 */
class CustomerRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-customer');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'digits:10', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => Hash::make($data['password']),
                'role' => 'customer',
                'status' => 'approved',
            ]);

            Customer::create([
                'user_id' => $user->id,
                'customer_code' => CodeGenerator::nextCustomerCode(),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('quick-login.setup');
    }
}
