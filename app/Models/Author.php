<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A byline. Kept separate from `users` so contributors can be credited on an
 * article without needing a login account.
 */
#[Fillable(['name', 'slug', 'bio', 'avatar_url'])]
#[RouteKey('slug')] // /author/{author} resolves on the slug, not the id.
class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    /**
     * The articles written by this author.
     *
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * A unique slug derived from a name, skipping one author's own row so an
     * edit that leaves the name alone does not collide with itself.
     */
    public static function uniqueSlug(string $name, ?self $ignore = null): string
    {
        $base = Str::slug($name) ?: 'author';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignore?->exists, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * The author's initials, used as the avatar fallback when there is no image.
     */
    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
