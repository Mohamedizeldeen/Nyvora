<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Reader comments. Every comment arrives unapproved and stays invisible
     * until someone in the newsroom approves it, so nothing a stranger writes
     * ever appears on the site unreviewed.
     *
     * Only a name and the comment are collected — no email, no IP address.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->text('body');
            // Null = waiting for moderation. Set = visible on the article.
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // The article page reads approved comments oldest-first.
            $table->index(['article_id', 'approved_at']);
            // The moderation queue reads pending ones newest-first.
            $table->index(['approved_at', 'created_at']);
        });

        Schema::table('articles', function (Blueprint $table) {
            // Lets an editor close comments on one story without switching the
            // whole feature off — useful on anything that attracts abuse.
            $table->boolean('comments_open')->default(true)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('comments_open');
        });

        Schema::dropIfExists('comments');
    }
};
