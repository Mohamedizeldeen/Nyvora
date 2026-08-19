<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Messages readers send through the site's forms. They are stored here and
     * read in the newsroom dashboard rather than emailed anywhere — the
     * publication has no mailbox, and a form that silently posts into a void
     * would be worse than the mailto: links it replaces.
     */
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            // Which of the old addresses this would have gone to.
            $table->string('topic', 40)->index();
            $table->string('name', 120);
            $table->string('email', 254);
            $table->text('body');
            // Null until someone in the newsroom opens it.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // The inbox lists newest first, and filters by topic and unread.
            $table->index(['created_at', 'id']);
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
