<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesImageField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use ManagesImageField;

    /**
     * Story list with search, section filter and status filter.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $categoryId = $request->query('category');

        $articles = Article::query()
            ->with(['category', 'author'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%'))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($status === 'published', fn ($query) => $query->published())
            ->when($status === 'draft', fn ($query) => $query->whereNull('published_at'))
            ->when($status === 'scheduled', fn ($query) => $query->whereNotNull('published_at')->where('published_at', '>', now()))
            ->when($status === 'featured', fn ($query) => $query->where('is_featured', true))
            ->orderByRaw('published_at IS NULL DESC') // drafts first, they need attention
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', [
            'articles' => $articles,
            'categories' => Category::query()->orderBy('name')->get(),
            'search' => $search,
            'status' => $status,
            'categoryId' => $categoryId,
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.form', [
            'article' => new Article(['published_at' => now()]),
            'categories' => Category::query()->orderBy('name')->get(),
            'authors' => Author::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $article = Article::query()->create($this->payload($request, null));

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Story created.');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', [
            'article' => $article,
            'categories' => Category::query()->orderBy('name')->get(),
            'authors' => Author::query()->orderBy('name')->get(),
        ]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($this->payload($request, $article));

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Story saved.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        // Remove the uploaded thumbnail along with the row.
        $this->deleteManagedImage($article->thumbnail_url);

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Story deleted.');
    }

    /**
     * Quick action from the list: add or remove the homepage hero flag.
     */
    public function toggleFeatured(Article $article): RedirectResponse
    {
        $article->update(['is_featured' => ! $article->is_featured]);

        return back()->with('status', $article->is_featured ? 'Story featured.' : 'Story unfeatured.');
    }

    /**
     * Quick action from the list: publish now, or pull back to a draft.
     */
    public function togglePublished(Article $article): RedirectResponse
    {
        $article->update(['published_at' => $article->isPublished() ? null : now()]);

        return back()->with('status', $article->isPublished() ? 'Story published.' : 'Story moved to drafts.');
    }

    /**
     * Map the validated request onto the article's columns.
     *
     * @return array<string, mixed>
     */
    private function payload(ArticleRequest $request, ?Article $article): array
    {
        $data = $request->safe()->only([
            'title', 'slug', 'excerpt', 'body', 'category_id', 'author_id', 'published_at',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['comments_open'] = $request->boolean('comments_open');
        $data['views_count'] = (int) $request->input('views_count', $article?->views_count ?? 0);
        $data['thumbnail_url'] = $this->resolveImageField(
            $request,
            'thumbnail',
            'thumbnail_url',
            $article?->thumbnail_url,
            'articles',
        );

        return $data;
    }
}
