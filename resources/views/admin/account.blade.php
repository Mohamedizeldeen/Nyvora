@extends('layouts.admin')

@section('title', 'Your account')

@section('actions')
    <a href="{{ route('admin.settings.edit') }}" class="btn-ghost">Site settings</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- The two forms post to different routes, so an error in one never wipes
         the other. Laravel keeps errors in one bag by default, which would make
         "wrong password" appear under both — hence the named bags below. --}}

    {{-- ============ Sign-in details ============ --}}
    <section class="admin-card space-y-4">
        <div>
            <h2 class="text-sm font-black uppercase tracking-wider">Sign-in details</h2>
            <p class="admin-hint !mt-1">
                The email address you use to sign in, and the name shown in this sidebar.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.account.profile') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="admin-label">Name</label>
                <input id="name" type="text" name="name" required maxlength="120" autocomplete="name"
                       value="{{ old('name', $user->name) }}"
                       @class(['admin-input', 'admin-input-invalid' => $errors->profile->has('name')])>
                @error('name', 'profile')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="admin-label">Email address</label>
                <input id="email" type="email" name="email" required maxlength="254" autocomplete="username"
                       value="{{ old('email', $user->email) }}"
                       @class(['admin-input', 'admin-input-invalid' => $errors->profile->has('email')])>
                <p class="admin-hint">
                    This is your username &mdash; you will sign in with the new address from now on.
                </p>
                @error('email', 'profile')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="border-t border-rule pt-4">
                <label for="profile_current_password" class="admin-label">Confirm with your current password</label>
                <input id="profile_current_password" type="password" name="current_password" required
                       autocomplete="current-password"
                       @class(['admin-input max-w-sm', 'admin-input-invalid' => $errors->profile->has('current_password')])>
                @error('current_password', 'profile')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Save details</button>
            </div>
        </form>
    </section>

    {{-- ============ Password ============ --}}
    <section class="admin-card space-y-4">
        <div>
            <h2 class="text-sm font-black uppercase tracking-wider">Password</h2>
            <p class="admin-hint !mt-1">
                At least 12 characters, and not one that has appeared in a known data breach.
                Changing it signs you out everywhere else.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.account.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="password_current_password" class="admin-label">Current password</label>
                <input id="password_current_password" type="password" name="current_password" required
                       autocomplete="current-password"
                       @class(['admin-input max-w-sm', 'admin-input-invalid' => $errors->password->has('current_password')])>
                @error('current_password', 'password')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="admin-label">New password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       @class(['admin-input max-w-sm', 'admin-input-invalid' => $errors->password->has('password')])>
                @error('password', 'password')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="admin-label">Repeat new password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       autocomplete="new-password"
                       @class(['admin-input max-w-sm'])>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Change password</button>
            </div>
        </form>
    </section>

    {{-- ============ Session ============ --}}
    <section class="admin-card">
        <h2 class="text-sm font-black uppercase tracking-wider">Signed in as</h2>
        <p class="admin-hint !mt-1">
            {{ $user->email }} &mdash; administrator since {{ $user->created_at->format('j F Y') }}.
        </p>
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn-ghost">Sign out</button>
        </form>
    </section>
</div>
@endsection
