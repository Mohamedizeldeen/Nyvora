<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesImageField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthorRequest;
use App\Models\Author;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AuthorController extends Controller
{
    use ManagesImageField;

    public function index(): View
    {
        return view('admin.authors.index', [
            'authors' => Author::query()
                ->withCount('articles')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.authors.form', ['author' => new Author]);
    }

    public function store(AuthorRequest $request): RedirectResponse
    {
        Author::query()->create($this->payload($request, null));

        return redirect()
            ->route('admin.authors.index')
            ->with('status', 'Author created.');
    }

    public function edit(Author $author): View
    {
        return view('admin.authors.form', ['author' => $author]);
    }

    public function update(AuthorRequest $request, Author $author): RedirectResponse
    {
        $author->update($this->payload($request, $author));

        return redirect()
            ->route('admin.authors.index')
            ->with('status', 'Author saved.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        // Articles reference the author with restrictOnDelete.
        if ($author->articles()->exists()) {
            return back()->with('error', 'Reassign this author\'s stories before deleting them.');
        }

        $this->deleteManagedImage($author->avatar_url);

        $author->delete();

        return redirect()
            ->route('admin.authors.index')
            ->with('status', 'Author deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AuthorRequest $request, ?Author $author): array
    {
        return [
            'name' => $request->validated('name'),
            'bio' => $request->validated('bio'),
            'avatar_url' => $this->resolveImageField(
                $request,
                'avatar',
                'avatar_url',
                $author?->avatar_url,
                'authors',
            ),
        ];
    }
}
