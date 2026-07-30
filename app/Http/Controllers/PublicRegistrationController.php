<?php

namespace App\Http\Controllers;

use App\Models\ShopOwner;
use App\Models\User;
use App\Services\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PublicRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-shop-owner');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'shop_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'digits:10', 'unique:users,mobile'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'pan' => ['nullable', 'string', 'max:10'],
            'aadhaar' => ['nullable', 'digits:12'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'shop_owner',
                'status' => 'pending',
            ]);

            ShopOwner::create([
                'user_id' => $user->id,
                'shop_owner_code' => CodeGenerator::nextShopOwnerCode(),
                'shop_name' => $data['shop_name'],
                'pan' => $data['pan'] ?? null,
                'aadhaar' => $data['aadhaar'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'reg_type' => 'self_registered',
            ]);
        });

        return redirect()->route('shop-owner.register')->with('registered', true);
    }
}
