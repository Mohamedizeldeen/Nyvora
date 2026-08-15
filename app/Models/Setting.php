<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    private const CACHE_KEY = 'settings.all';

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
    ];

    /**
     * Every setting as a key => value array, merged over the defaults.
     *
     * @return array<string, string>
     */
    public static function all_settings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                return array_merge(self::DEFAULTS, self::query()->pluck('value', 'key')->all());
            } catch (Throwable) {
                // Before the first migration there is no settings table yet —
                // fall back to the defaults rather than 500 the whole site.
                return self::DEFAULTS;
            }
        });
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
     * Drop the cached settings (used after a seed or a manual DB edit).
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
