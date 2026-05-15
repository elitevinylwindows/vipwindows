<?php

namespace App\Http\Controllers;

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
        return view('pages.gallery');
    }

    public function serviceAreas()
    {
        return view('pages.service-areas');
    }

    public function contact()
    {
        return view('pages.contact');
    }
}
