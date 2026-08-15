<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    /**
     * Category archive: every published story in one section, newest first.
     *
     * The {category} route parameter is matched on the slug (see the model's
     * #[RouteKey] attribute), so an unknown slug 404s before we get here.
     */
    public function show(Category $category): View
    {
        $articles = $category->articles()
            ->published()
            ->with(['category', 'author'])
            ->latestFirst()
            ->paginate((int) setting('articles_per_page'));

        return view('category', [
            'category' => $category,
            'articles' => $articles,
            // Sidebar "Most Popular" — scoped to this section, so the archive
            // page stays about the section the reader chose.
            'popular' => $category->articles()
                ->published()
                ->with('category')
                ->popular()
                ->take(5)
                ->get(),
        ]);
    }
}
