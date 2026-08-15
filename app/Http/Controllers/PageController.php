<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * The static pages. AdSense review expects a site to have real About, Contact
 * and Privacy Policy pages, so they get first-class routes rather than footers.
 */
class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }
}
