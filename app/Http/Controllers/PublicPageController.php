<?php

namespace App\Http\Controllers;

class PublicPageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function services()
    {
        return view('pages.services');
    }

    public function howItWorks()
    {
        return view('pages.how-it-works');
    }

    public function pricing()
    {
        return view('pages.pricing');
    }

    public function blog()
    {
        return view('pages.blog');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function faq()
    {
        return view('pages.faq');
    }

    public function about()
    {
        return view('pages.about');
    }
}