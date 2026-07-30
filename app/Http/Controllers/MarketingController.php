<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function index(): View
    {
        return view('marketing.home', [
            'featuredProducts' => Product::where('is_active', true)->latest('id')->take(4)->get(),
            'banners' => Banner::where('is_active', true)->orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }
}
