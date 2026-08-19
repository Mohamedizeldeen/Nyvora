<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reader's comment on an article.
 *
 * Comments are held for moderation: a new one has no approved_at and is
 * invisible on the site until an administrator approves it.
 */
#[Fillable(['article_id', 'name', 'body', 'approved_at'])]
class Comment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    /**
     * The story this comment is on.
     *
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Approved, and therefore visible to readers.
     *
     * @param  Builder<Comment>  $query
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->whereNotNull('approved_at');
    }

    /**
     * Waiting in the moderation queue.
     *
     * @param  Builder<Comment>  $query
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->whereNull('approved_at');
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function approve(): void
    {
        if (! $this->isApproved()) {
            $this->forceFill(['approved_at' => now()])->save();
        }
    }

    /**
     * Send an approved comment back to the queue.
     */
    public function unapprove(): void
    {
        $this->forceFill(['approved_at' => null])->save();
    }

    /**
     * Initials for the avatar bubble beside the comment.
     */
    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
