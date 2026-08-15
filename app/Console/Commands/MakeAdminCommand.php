<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates a newsroom administrator, or promotes an existing user.
 *
 * There is no public registration form — this command is the only way in.
 */
#[Signature('nyvora:make-admin {email? : The administrator email address}')]
#[Description('Create a newsroom administrator (or promote an existing user)')]
class MakeAdminCommand extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email') ?: text(
            label: 'Email address',
            required: true,
        );

        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            $existing->update(['is_admin' => true]);
            $this->components->info("Promoted {$existing->email} to administrator.");

            return self::SUCCESS;
        }

        $name = text(label: 'Full name', required: true);
        $plainPassword = password(label: 'Password (min 8 characters)', required: true);

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $plainPassword],
            [
                'email' => ['required', 'email', 'unique:users,email'],
                'name' => ['required', 'string', 'max:120'],
                'password' => ['required', 'string', 'min:8'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        // The User model casts `password` to "hashed", so this is stored safely.
        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $plainPassword,
            'is_admin' => true,
        ]);

        $this->components->info("Administrator {$email} created. Sign in at /login.");

        return self::SUCCESS;
    }
}
