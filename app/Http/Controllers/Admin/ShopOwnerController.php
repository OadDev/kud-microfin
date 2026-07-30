<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopOwner;
use App\Models\User;
use App\Services\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ShopOwnerController extends Controller
{
    public function index(Request $request): View
    {
        $query = ShopOwner::with('user')->latest('id');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                    ->orWhere('shop_owner_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->query('status')) {
            $query->whereHas('user', fn ($u) => $u->where('status', $status));
        }

        $shopOwners = $query->get();

        return view('admin.shop-owners.index', [
            'title' => 'Shop Owners', 'active' => 'shop-owners',
            'shopOwners' => $shopOwners,
            'search' => $search ?? '', 'status' => $status ?? 'All',
        ]);
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

        $shopOwner = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'shop_owner',
                'status' => 'approved',
            ]);

            return ShopOwner::create([
                'user_id' => $user->id,
                'shop_owner_code' => CodeGenerator::nextShopOwnerCode(),
                'shop_name' => $data['shop_name'],
                'pan' => $data['pan'] ?? null,
                'aadhaar' => $data['aadhaar'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'reg_type' => 'admin_created',
            ]);
        });

        return back()->with('success', "Shop Owner {$shopOwner->shop_owner_code} created successfully.");
    }

    public function show(ShopOwner $shopOwner): View
    {
        return view('admin.shop-owners.show', ['shopOwner' => $shopOwner->load('user')]);
    }

    public function approve(ShopOwner $shopOwner): RedirectResponse
    {
        $shopOwner->user->update(['status' => 'approved']);

        return back()->with('success', "{$shopOwner->user->name} has been approved.");
    }

    public function reject(Request $request, ShopOwner $shopOwner): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string']]);

        $shopOwner->update(['reject_reason' => $data['reason']]);
        $shopOwner->user->update(['status' => 'rejected']);

        return back()->with('success', "{$shopOwner->user->name} registration was rejected.");
    }

    public function suspend(ShopOwner $shopOwner): RedirectResponse
    {
        $shopOwner->user->update(['status' => 'suspended']);

        return back()->with('warning', "{$shopOwner->user->name} has been suspended.");
    }
}
