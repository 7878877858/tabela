<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboard)
    {
        $data = $dashboard->build();
        $data['userName'] = auth()->user()->name ?? 'Admin';

        return view('dashboard.index', $data);
    }
}
