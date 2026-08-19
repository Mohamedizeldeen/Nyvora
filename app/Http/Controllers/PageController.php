<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Contracts\View\View;

/**
 * The static pages.
 *
 * AdSense review, and readers generally, expect a publication to say who it
 * is, how to reach it, how it makes editorial decisions and what it does with
 * their data. Each of these gets a first-class route rather than a footer note.
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

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function cookiePolicy(): View
    {
        return view('pages.cookie-policy');
    }

    public function editorialPolicy(): View
    {
        return view('pages.editorial-policy');
    }

    public function advertise(): View
    {
        return view('pages.advertise');
    }

    /**
     * Our team — how the newsroom is organised, with the bylines behind it.
     */
    public function team(): View
    {
        return view('pages.team', [
            'authors' => Author::query()
                ->withCount(['articles' => fn ($query) => $query->published()])
                ->orderBy('name')
                ->get(),
        ]);
    }
}
