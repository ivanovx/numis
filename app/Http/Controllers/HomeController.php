<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('index');
    }

    public function about(): View
    {
        return view('about');
    }

    public function privacyPolicy(): View
    {
        return view('privacy-policy');
    }
}
