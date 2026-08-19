<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Key/value site settings the admin can edit without a deploy.
 *
 * Reads go through a single cached array so a page render costs one query at
 * most, and the cache is dropped whenever a value is written.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** The settings table is keyed by a string, not an auto-increment id. */
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Only the rows from the database are cached under this key — never the
     * defaults merged in. See all_settings() for why.
     *
     * The key is versioned: a deploy that changes the cached shape bumps it, so
     * an old entry is ignored instead of having to be cleared by hand on every
     * server.
     */
    private const CACHE_KEY = 'settings.stored.v2';

    /**
     * Defaults used when a key has never been saved. Editing these changes the
     * out-of-the-box site; the admin UI writes over them per key.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        'site_tagline' => 'Technology news, reviews & analysis',
        'footer_description' => 'Independent reporting on the technology industry — the funding, the hardware, the security holes and the science underneath it all.',
        'articles_per_page' => '8',
        'promo_enabled' => '0',
        'promo_eyebrow' => '',
        'promo_text' => '',
        'promo_cta_label' => '',
        'promo_cta_url' => '',
        'promo_tone' => 'accent',
        'adsense_client_id' => '',
        // Paste the AdSense unit snippet (or just its slot id) for each
        // placement. Empty = that slot keeps showing the sized placeholder.
        'adsense_slot_sidebar' => '',
        'adsense_slot_leaderboard' => '',
        'adsense_slot_in_feed' => '',
        // Auto ads let Google place extra units itself.
        'adsense_auto_ads' => '0',
        // Google Analytics 4 measurement ID, e.g. G-XXXXXXXXXX. Empty = no
        // analytics code loads at all.
        'analytics_measurement_id' => '',
        // Who asks the reader for consent: this site's own banner, or Google's
        // certified CMP (Privacy & Messaging in AdSense). Never both — two
        // banners would fight over the same consent signal.
        'consent_manager' => 'built_in',
        // Indexing is on by default — a site nobody can find is the worse failure.
        'search_indexable' => '1',
        // The newsletter is switched off. Everything behind it (table, routes,
        // Mailgun wiring) is intact, so turning this back on restores it.
        'newsletter_enabled' => '0',
        // Reader comments. Every comment is held for approval regardless.
        'comments_enabled' => '1',
    ];

    /**
     * Every setting as a key => value array, merged over the defaults.
     *
     * @return array<string, string>
     */
    public static function all_settings(): array
    {
        // Cache the stored rows only, and merge the defaults on every read.
        //
        // Caching the *merged* array is the obvious version and it is wrong:
        // the cache lives forever, so a setting added in a later deploy is
        // absent from it, and every view doing $settings['new_key'] throws
        // "Undefined array key" until someone clears the cache by hand. Merging
        // here means a new key works the moment the code ships.
        $stored = Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                return self::query()->pluck('value', 'key')->all();
            } catch (Throwable) {
                // Before the first migration there is no settings table yet —
                // fall back to the defaults rather than 500 the whole site.
                return [];
            }
        });

        return array_merge(self::DEFAULTS, $stored);
    }

    /**
     * Read one setting.
     */
    public static function get(string $key, ?string $fallback = null): ?string
    {
        return self::all_settings()[$key] ?? $fallback ?? self::DEFAULTS[$key] ?? null;
    }

    /**
     * Read a setting as a boolean ("1"/"0" checkboxes).
     */
    public static function bool(string $key): bool
    {
        return filter_var(self::get($key), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Write a batch of settings and drop the cache.
     *
     * @param  array<string, string|null>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Pull the ad slot id out of whatever AdSense gave the user.
     *
     * They can paste the whole <ins> snippet or just the numeric id — both are
     * accepted, because asking someone to pick the number out of a script tag
     * is exactly the kind of step that gets done wrong.
     */
    public static function extractAdSlotId(?string $pasted): string
    {
        $pasted = trim((string) $pasted);

        if ($pasted === '') {
            return '';
        }

        // Already just the id.
        if (preg_match('/^\d{6,20}$/', $pasted) === 1) {
            return $pasted;
        }

        // Pulled from data-ad-slot="1234567890" in a pasted snippet.
        if (preg_match('/data-ad-slot=["\']?(\d{6,20})["\']?/', $pasted, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Drop the cached settings (used after a seed or a manual DB edit).
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
