<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A message a reader sent through one of the site's forms.
 *
 * These replace the mailto: addresses the site used to publish. Nothing is
 * emailed on: the newsroom reads them in the dashboard.
 */
#[Fillable(['topic', 'name', 'email', 'body', 'read_at'])]
class ContactMessage extends Model
{
    /**
     * The topics a reader can pick, replacing one former address each.
     *
     * Keys are stored in the database; changing a key orphans old rows, so
     * add new ones rather than renaming.
     *
     * @var array<string, array{label: string, blurb: string}>
     */
    public const TOPICS = [
        'tip' => [
            'label' => 'Story tip',
            'blurb' => 'Tell us what you know. We treat every tip as confidential unless you say otherwise.',
        ],
        'correction' => [
            'label' => 'Correction',
            'blurb' => 'Include the article link and what we got wrong. We fix errors in place and note the change.',
        ],
        'advertising' => [
            'label' => 'Advertising',
            'blurb' => 'Rate card, formats and availability for display placements.',
        ],
        'pitch' => [
            'label' => 'Freelance pitch',
            'blurb' => 'What the story is, why now, and who you would talk to. Please do not send finished articles.',
        ],
        'editorial' => [
            'label' => 'Editorial complaint',
            'blurb' => 'Tell us the article and what you think went wrong. We will investigate and reply.',
        ],
        'security' => [
            'label' => 'Security report',
            'blurb' => 'Enough detail to reproduce the issue. Please give us a chance to fix it before publishing.',
        ],
        'privacy' => [
            'label' => 'Privacy request',
            'blurb' => 'Ask what we hold about you, or ask us to delete it. We reply within 30 days.',
        ],
        'general' => [
            'label' => 'Something else',
            'blurb' => 'Press, partnerships, syndication — anything that does not fit above.',
        ],
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, string> topic key => label
     */
    public static function topicOptions(): array
    {
        return array_map(fn (array $topic) => $topic['label'], self::TOPICS);
    }

    /**
     * The human label for this message's topic.
     */
    public function topicLabel(): string
    {
        return self::TOPICS[$this->topic]['label'] ?? ucfirst($this->topic);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Not yet opened by anyone in the newsroom.
     *
     * @param  Builder<ContactMessage>  $query
     */
    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /**
     * Newest first — how the inbox is read.
     *
     * @param  Builder<ContactMessage>  $query
     */
    #[Scope]
    protected function latestFirst(Builder $query): void
    {
        $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function markRead(): void
    {
        if ($this->isUnread()) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
