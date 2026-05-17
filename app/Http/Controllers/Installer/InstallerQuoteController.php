<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallerQuoteController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Quote::where('entered_by', Auth::user()->name);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $quotes = $query->latest()->paginate(20);

        return view('installer.quotes.index', compact('quotes', 'status'));
    }
}
