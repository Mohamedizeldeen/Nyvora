{{-- Newsroom sign-in. Standalone page — no public header or footer. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-ink px-4 py-12 font-sans">
    <div class="w-full max-w-sm">

        <div class="text-center">
            <a href="{{ route('home') }}" class="text-3xl font-black uppercase tracking-tight text-white">
                {{ config('app.name') }}<span class="text-brand">.</span>
            </a>
            <p class="mt-2 text-xs font-bold uppercase tracking-[0.2em] text-white/40">Newsroom admin</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="mt-8 rounded-xl bg-white p-6 shadow-xl">
            @csrf

            <div>
                <label for="email" class="admin-label">Email address</label>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="username"
                       @class(['admin-input', 'admin-input-invalid' => $errors->has('email')])>
                @error('email')
                    <p class="admin-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="password" class="admin-label">Password</label>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       @class(['admin-input', 'admin-input-invalid' => $errors->has('password')])>
                @error('password')
                    <p class="admin-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="mt-4 flex items-center gap-2.5 text-sm text-ink/70">
                <input type="checkbox" name="remember" value="1"
                       class="size-4 rounded border-rule text-brand focus:ring-brand/30">
                Keep me signed in
            </label>

            <button type="submit" class="btn-primary mt-6 w-full">Sign in</button>
        </form>

        <p class="mt-6 text-center text-xs text-white/35">
            Accounts are created from the command line:
            <code class="text-white/55">php artisan nyvora:make-admin</code>
        </p>
    </div>
</body>
</html>
