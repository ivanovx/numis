<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Coin;
use App\Models\Series;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'coinCount' => Coin::count(),
            'seriesCount' => Series::count(),
            'artistCount' => Artist::count(),
            'completeness' => Coin::completenessReport(),
            'completenessFields' => Coin::COMPLETENESS_FIELDS,
        ]);
    }
}
