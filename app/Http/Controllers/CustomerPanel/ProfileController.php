<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.profile', [
            'title' => 'Profile', 'active' => 'profile',
            'customer' => $request->user()->customer,
            'passkeys' => $request->user()->passkeys()->latest()->get(),
        ]);
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:4096'],
        ]);

        $customer = $request->user()->customer;

        if ($customer->photo_path) {
            Storage::disk('public')->delete($customer->photo_path);
        }

        $customer->update([
            'photo_path' => $request->file('photo')->store("customers/{$customer->id}", 'public'),
        ]);

        return back()->with('success', 'Profile picture updated.');
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pin' => ['nullable', 'digits:6'],
            'pan' => ['required', 'string', 'max:10', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i'],
            'aadhaar' => ['required', 'digits:12'],
        ]);

        $data['pan'] = strtoupper($data['pan']);

        $request->user()->customer->update($data);

        return back()->with('success', 'Details updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password updated successfully.');
    }
}
