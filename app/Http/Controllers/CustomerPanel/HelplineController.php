<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HelplineController extends Controller
{
    public function index(): View
    {
        return view('customer.helpline', [
            'title' => 'Helpline', 'active' => 'helpline',
        ]);
    }
}
