<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo administrator. Change the password immediately, or create a real
        // one with: php artisan nyvora:make-admin
        User::query()->updateOrCreate(
            ['email' => 'admin@nyvora.test'],
            [
                'name' => 'Nyvora Admin',
                'password' => 'password',
                'is_admin' => true,
            ],
        );

        // Categories, authors and the demo newsroom.
        $this->call(NewsSeeder::class);

        // Drop any settings cached from a previous run.
        Setting::flush();
    }
}
