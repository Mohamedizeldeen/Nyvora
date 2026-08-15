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
     * Moves the newsletter to double opt-in: a signup is only a real
     * subscriber once the address has been confirmed by clicking the link in
     * the email Mailgun delivers.
     */
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            // Unguessable id used in the confirm and unsubscribe links.
            $table->string('token', 64)->nullable()->unique()->after('email');
            $table->timestamp('confirmed_at')->nullable()->after('subscribed_at');
            $table->timestamp('unsubscribed_at')->nullable()->after('confirmed_at');
        });

        // Existing rows predate double opt-in. Give them a token, and treat
        // them as already confirmed rather than silently dropping them.
        DB::table('subscribers')->whereNull('token')->orderBy('id')->each(function ($subscriber) {
            DB::table('subscribers')->where('id', $subscriber->id)->update([
                'token' => Str::random(48),
                'confirmed_at' => $subscriber->subscribed_at ?? now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['token', 'confirmed_at', 'unsubscribed_at']);
        });
    }
};
