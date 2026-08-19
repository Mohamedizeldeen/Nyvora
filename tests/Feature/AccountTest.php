<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * The admin's own account page: changing the sign-in email and the password.
 *
 * Both changes are gated behind the current password, so most of what is worth
 * testing here is what happens when that gate is wrong or missing.
 */
function accountAdmin(string $password = 'correct-horse-battery'): User
{
    return User::factory()->create([
        'is_admin' => true,
        'password' => $password,
    ]);
}

/*
|--------------------------------------------------------------------------
| Access
|--------------------------------------------------------------------------
*/

it('sends guests to the login page', function () {
    $this->get('/admin/account')->assertRedirect(route('login'));
});

it('refuses a signed-in reader who is not an administrator', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin/account')
        ->assertForbidden();
});

it('shows the account page with both forms', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)
        ->get('/admin/account')
        ->assertOk()
        ->assertSee('Sign-in details')
        ->assertSee('Password')
        ->assertSee($admin->email);
});

it('is reachable from the admin sidebar', function () {
    $this->actingAs(accountAdmin())
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.account.edit'), escape: false);
});

/*
|--------------------------------------------------------------------------
| Changing the email address
|--------------------------------------------------------------------------
*/

it('changes the name and email when the current password is right', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)
        ->put('/admin/account', [
            'name' => 'Mohamed Izz',
            'email' => 'editor@ny-vora.com',
            'current_password' => 'correct-horse-battery',
        ])
        ->assertRedirect(route('admin.account.edit'))
        ->assertSessionHas('status');

    expect($admin->fresh())
        ->name->toBe('Mohamed Izz')
        ->email->toBe('editor@ny-vora.com');
});

it('refuses an email change when the current password is wrong', function () {
    $admin = accountAdmin();
    $before = $admin->email;

    $this->actingAs($admin)
        ->put('/admin/account', [
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'current_password' => 'not-the-password',
        ])
        ->assertSessionHasErrors('current_password', errorBag: 'profile');

    expect($admin->fresh()->email)->toBe($before);
});

it('refuses an email already used by another account', function () {
    $admin = accountAdmin();
    $other = User::factory()->create(['email' => 'taken@ny-vora.com']);

    $this->actingAs($admin)
        ->put('/admin/account', [
            'name' => $admin->name,
            'email' => $other->email,
            'current_password' => 'correct-horse-battery',
        ])
        ->assertSessionHasErrors('email', errorBag: 'profile');
});

it('lets an admin re-save their own email without a unique error', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)
        ->put('/admin/account', [
            'name' => 'Renamed',
            'email' => $admin->email,
            'current_password' => 'correct-horse-battery',
        ])
        ->assertSessionHasNoErrors();

    expect($admin->fresh()->name)->toBe('Renamed');
});

it('normalises the email to lower case', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)->put('/admin/account', [
        'name' => $admin->name,
        'email' => '  Editor@NY-Vora.COM ',
        'current_password' => 'correct-horse-battery',
    ]);

    expect($admin->fresh()->email)->toBe('editor@ny-vora.com');
});

/*
|--------------------------------------------------------------------------
| Changing the password
|--------------------------------------------------------------------------
*/

it('changes the password when the current one is right', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)
        ->put('/admin/account/password', [
            'current_password' => 'correct-horse-battery',
            'password' => 'a-long-enough-new-secret',
            'password_confirmation' => 'a-long-enough-new-secret',
        ])
        ->assertRedirect(route('admin.account.edit'))
        ->assertSessionHas('status');

    expect(Hash::check('a-long-enough-new-secret', $admin->fresh()->password))->toBeTrue();
});

it('refuses a password change when the current password is wrong', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)
        ->put('/admin/account/password', [
            'current_password' => 'not-the-password',
            'password' => 'a-long-enough-new-secret',
            'password_confirmation' => 'a-long-enough-new-secret',
        ])
        ->assertSessionHasErrors('current_password', errorBag: 'password');

    expect(Hash::check('correct-horse-battery', $admin->fresh()->password))->toBeTrue();
});

it('refuses a new password that is not repeated correctly', function () {
    $this->actingAs(accountAdmin())
        ->put('/admin/account/password', [
            'current_password' => 'correct-horse-battery',
            'password' => 'a-long-enough-new-secret',
            'password_confirmation' => 'something-else-entirely',
        ])
        ->assertSessionHasErrors('password', errorBag: 'password');
});

it('refuses a new password shorter than twelve characters', function () {
    $this->actingAs(accountAdmin())
        ->put('/admin/account/password', [
            'current_password' => 'correct-horse-battery',
            'password' => 'short1234',
            'password_confirmation' => 'short1234',
        ])
        ->assertSessionHasErrors('password', errorBag: 'password');
});

it('keeps the admin signed in after changing their password', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)->put('/admin/account/password', [
        'current_password' => 'correct-horse-battery',
        'password' => 'a-long-enough-new-secret',
        'password_confirmation' => 'a-long-enough-new-secret',
    ]);

    // Changing your own password must not lock you out of the tab you did it in.
    $this->get('/admin/account')->assertOk();
});

it('can sign in with the new password afterwards', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)->put('/admin/account/password', [
        'current_password' => 'correct-horse-battery',
        'password' => 'a-long-enough-new-secret',
        'password_confirmation' => 'a-long-enough-new-secret',
    ]);

    auth()->logout();

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'a-long-enough-new-secret',
    ])->assertRedirect();

    expect(auth()->check())->toBeTrue();
});

it('can sign in with the new email address afterwards', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)->put('/admin/account', [
        'name' => $admin->name,
        'email' => 'editor@ny-vora.com',
        'current_password' => 'correct-horse-battery',
    ]);

    auth()->logout();

    $this->post('/login', [
        'email' => 'editor@ny-vora.com',
        'password' => 'correct-horse-battery',
    ])->assertRedirect();

    expect(auth()->check())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Other sessions
|--------------------------------------------------------------------------
*/

it('signs out a session that still carries the old password', function () {
    $admin = accountAdmin();
    $oldHash = $admin->password;

    $this->actingAs($admin)->put('/admin/account/password', [
        'current_password' => 'correct-horse-battery',
        'password' => 'a-long-enough-new-secret',
        'password_confirmation' => 'a-long-enough-new-secret',
    ]);

    // Stand in for a second browser: a session stamped with the pre-change hash.
    // AuthenticateSession compares that stamp on every admin request, so this
    // is exactly what the other device sends after the password changed.
    session(['password_hash_web' => $oldHash]);

    $this->get('/admin')->assertRedirect(route('login'));
});

it('does not disturb a session carrying the current password', function () {
    $admin = accountAdmin();

    $this->actingAs($admin)->get('/admin')->assertOk();

    session(['password_hash_web' => $admin->fresh()->password]);

    $this->get('/admin')->assertOk();
});
