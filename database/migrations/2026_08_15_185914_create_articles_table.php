<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('thumbnail_url')->nullable();

            // Deleting a category or author is blocked while it still has articles,
            // so a stray delete can never silently wipe published content.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->constrained()->restrictOnDelete();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            // Nullable so an article can sit unpublished (a draft) until it is given a date.
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // The feed and archive pages both filter on "published" and sort newest first.
            $table->index(['published_at', 'id']);
            // "Most Popular" / "Top Headlines" widgets sort on these.
            $table->index('views_count');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
