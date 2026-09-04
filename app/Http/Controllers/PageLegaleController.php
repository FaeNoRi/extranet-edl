<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageLegaleController extends Controller
{
    public function mentions(): View
    {
        return view('legal.mentions');
    }

    public function confidentialite(): View
    {
        return view('legal.confidentialite');
    }

    public function accessibilite(): View
    {
        return view('legal.accessibilite');
    }
}
