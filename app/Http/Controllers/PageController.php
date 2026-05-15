<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\ServiceArea;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function about()
    {
        return view('pages.about');
    }

    public function services()
    {
        return view('pages.services');
    }

    public function gallery()
    {
        $images = GalleryImage::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $categories = $images->pluck('category')->unique()->values();

        return view('pages.gallery', compact('images', 'categories'));
    }

    public function serviceAreas()
    {
        $areas = ServiceArea::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.service-areas', compact('areas'));
    }

    public function contact()
    {
        return view('pages.contact');
    }
}
