<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One row per article per day. `articles.views_count` is a running total
     * and cannot answer "how did last week compare to this one", which is the
     * whole point of a reports page.
     *
     * Aggregate counts only — no reader, no session, no IP address is recorded,
     * so this stays consistent with what the privacy policy promises.
     */
    public function up(): void
    {
        Schema::create('article_view_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->date('viewed_on');
            $table->unsignedInteger('views')->default(0);

            // One row per article per day, and the upsert target.
            $table->unique(['article_id', 'viewed_on']);
            // Reports scan by date first.
            $table->index('viewed_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_view_daily');
    }
};
