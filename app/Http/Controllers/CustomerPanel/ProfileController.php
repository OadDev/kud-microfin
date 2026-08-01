<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
}
