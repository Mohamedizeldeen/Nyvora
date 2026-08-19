<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Authors get a slug so each byline has its own public page at
     * /author/{slug} — a real SEO win for a publication, since a reporter's
     * name is often what people search for.
     */
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Backfill existing rows, keeping slugs unique when two people share a name.
        $seen = [];

        foreach (DB::table('authors')->orderBy('id')->get() as $author) {
            $base = Str::slug($author->name) ?: 'author';
            $slug = $base;
            $suffix = 2;

            while (in_array($slug, $seen, true)) {
                $slug = $base.'-'.$suffix++;
            }

            $seen[] = $slug;

            DB::table('authors')->where('id', $author->id)->update(['slug' => $slug]);
        }

        // Only enforce uniqueness once every row has a value.
        Schema::table('authors', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
