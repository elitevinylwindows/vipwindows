<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\ServiceArea;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $images = GalleryImage::orderBy('sort_order')->orderByDesc('created_at')->get();
        $areas = ServiceArea::orderBy('sort_order')->orderBy('name')->get();

        return view('content.index', compact('images', 'areas'));
    }
}
