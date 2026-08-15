<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single story. Everything the front end renders — hero, feed, archives,
 * "Most Popular" — is a different slice of this table.
 */
#[Fillable([
    'title',
    'slug',
    'excerpt',
    'body',
    'thumbnail_url',
    'category_id',
    'author_id',
    'views_count',
    'is_featured',
    'published_at',
])]
#[RouteKey('slug')] // /article/{article} resolves on the slug, not the id.
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The section this article is filed under.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The byline on this article.
     *
     * @return BelongsTo<Author, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * Limit the query to articles that are live right now — drafts (a null
     * `published_at`) and future-dated posts stay hidden from the public site.
     *
     * @param  Builder<Article>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Newest stories first — the ordering used by the feed and archive pages.
     *
     * @param  Builder<Article>  $query
     */
    #[Scope]
    protected function latestFirst(Builder $query): void
    {
        // `id` breaks ties so pagination stays stable when two posts share a timestamp.
        $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * Most-read stories first — powers the "Most Popular" and headline widgets.
     *
     * @param  Builder<Article>  $query
     */
    #[Scope]
    protected function popular(Builder $query): void
    {
        $query->orderByDesc('views_count')->orderByDesc('id');
    }

    /**
     * Is this article live right now? The single-article page uses this to 404
     * on drafts and embargoed posts, mirroring the published() scope.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Record one more read of this article.
     *
     * Uses an atomic SQL increment rather than a read-modify-write so that
     * simultaneous requests cannot overwrite each other's count.
     */
    public function recordView(): void
    {
        $this->incrementQuietly('views_count');
    }

    /**
     * Rough read time in minutes, based on ~200 words per minute.
     */
    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 200));
    }
}
