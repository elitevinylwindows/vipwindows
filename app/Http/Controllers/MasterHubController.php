<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MasterHubController extends Controller
{
    public function index()
    {
        return view('master.hub');
    }
}
