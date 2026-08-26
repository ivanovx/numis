<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Series;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'coinCount'   => Coin::count(),
            'seriesCount' => Series::count(),
        ]);
    }
}
