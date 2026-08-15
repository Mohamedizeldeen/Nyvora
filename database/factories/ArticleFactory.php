<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(fake()->numberBetween(6, 11)), '.');
        $slug = Str::slug($title);

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => fake()->sentence(20),
            'body' => collect(fake()->paragraphs(6))->implode("\n\n"),
            // Seeding the URL with the slug keeps each article's image stable across re-seeds.
            'thumbnail_url' => "https://picsum.photos/seed/{$slug}/1200/800",
            'category_id' => Category::factory(),
            'author_id' => Author::factory(),
            'views_count' => fake()->numberBetween(0, 50_000),
            'is_featured' => false,
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Mark the article as featured so it is eligible for the homepage hero.
     */
    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    /**
     * An unpublished draft — hidden from every public query by Article::published().
     */
    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }

    /**
     * Scheduled for the future — also hidden until its publish date arrives.
     */
    public function scheduled(): static
    {
        return $this->state(['published_at' => now()->addDays(7)]);
    }
}
