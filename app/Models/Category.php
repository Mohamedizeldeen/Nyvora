<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A section of the magazine ("AI", "Security", ...). The `color` column drives
 * the category label tint everywhere the category is rendered.
 */
#[Fillable(['name', 'slug', 'color'])]
#[RouteKey('slug')] // /category/{category} resolves on the slug, not the id.
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * The articles filed under this category.
     *
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * The category colour, guaranteed to be a plain 6-digit hex.
     *
     * Templates drop this straight into inline `style` attributes, so a bad
     * value in the database falls back to the brand colour rather than
     * injecting arbitrary CSS.
     */
    public function displayColor(): string
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $this->color) === 1
            ? $this->color
            : '#5B2BEF';
    }
}
