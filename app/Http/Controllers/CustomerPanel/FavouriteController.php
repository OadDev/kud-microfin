<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavouriteController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;

        return view('customer.favourites.index', [
            'title' => 'Favourites', 'active' => 'products',
            'favourites' => $customer->favourites()->with('product')->get(),
        ]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $customer = $request->user()->customer;

        $favourite = Favourite::where('customer_id', $customer->id)->where('product_id', $product->id)->first();

        if ($favourite) {
            $favourite->delete();

            return back()->with('success', 'Removed from favourites.');
        }

        Favourite::create(['customer_id' => $customer->id, 'product_id' => $product->id]);

        return back()->with('success', 'Added to favourites.');
    }
}
