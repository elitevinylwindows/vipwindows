<?php

namespace App\Http\Controllers;

use App\Models\InstallationOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pending'     => InstallationOrder::where('status', 'pending')->count(),
            'scheduled'   => InstallationOrder::where('status', 'scheduled')->count(),
            'in_progress' => InstallationOrder::where('status', 'in_progress')->count(),
            'completed'   => InstallationOrder::where('status', 'completed')->count(),
        ];

        $recentOrders = InstallationOrder::orderBy('created_at', 'desc')->take(10)->get();

        $todayInstalls = InstallationOrder::where('scheduled_date', today())
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('scheduled_slot')
            ->get();

        return view('dashboard.index', compact('stats', 'recentOrders', 'todayInstalls'));
    }
}
