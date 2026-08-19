<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Str;

/**
 * A newsletter address, captured by the sidebar form.
 *
 * The list is double opt-in: a signup starts as pending and only counts as a
 * subscriber once the reader clicks the confirmation link Mailgun delivers.
 * That keeps typos and other people's addresses off the list, and it is what
 * the GDPR/CAN-SPAM posture in the privacy policy promises.
 */
#[Fillable(['email', 'token', 'subscribed_at', 'confirmed_at', 'unsubscribed_at'])]
#[RouteKey('token')] // Confirm/unsubscribe links resolve on the token, never the id.
class Subscriber extends Model
{
    use Prunable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    /**
     * Rows the scheduled `model:prune` command deletes.
     *
     * Signups that were never confirmed are erased after 30 days — holding an
     * address nobody consented to is exactly what double opt-in exists to
     * avoid, and the privacy policy promises this deletion.
     *
     * Unsubscribed rows are deliberately NOT pruned: that minimal record is
     * what stops someone being silently re-added, and it evidences that the
     * opt-out was honoured.
     *
     * @return Builder<Subscriber>
     */
    public function prunable(): Builder
    {
        return static::query()
            ->whereNull('confirmed_at')
            ->whereNull('unsubscribed_at')
            ->where('created_at', '<=', now()->subDays(30));
    }

    /**
     * A fresh, unguessable token for the confirm and unsubscribe links.
     */
    public static function newToken(): string
    {
        return Str::random(48);
    }

    /**
     * On the list: confirmed, and not since unsubscribed.
     *
     * @param  Builder<Subscriber>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    /**
     * Signed up but has not clicked the confirmation link yet.
     *
     * @param  Builder<Subscriber>  $query
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->whereNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null && $this->unsubscribed_at === null;
    }

    public function hasUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }

    /**
     * One of: confirmed | pending | unsubscribed — used by the admin list.
     */
    public function status(): string
    {
        return match (true) {
            $this->hasUnsubscribed() => 'unsubscribed',
            $this->isConfirmed() => 'confirmed',
            default => 'pending',
        };
    }

    /**
     * Mark the address as confirmed. Re-confirming is a no-op.
     */
    public function confirm(): void
    {
        if ($this->isConfirmed()) {
            return;
        }

        $this->forceFill([
            'confirmed_at' => now(),
            'unsubscribed_at' => null,
        ])->save();
    }

    /**
     * Take the address off the list, keeping the row so the same person is not
     * re-added silently and so we can prove they opted out.
     */
    public function unsubscribe(): void
    {
        if ($this->hasUnsubscribed()) {
            return;
        }

        $this->forceFill(['unsubscribed_at' => now()])->save();
    }
}
