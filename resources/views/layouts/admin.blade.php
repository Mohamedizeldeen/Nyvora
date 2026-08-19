{{--
    Shell for everything under /admin.

    Child views supply:
      @section('title')   — shown in the browser tab and as the page heading
      @section('actions') — buttons rendered beside the heading
      @section('content') — the page body
--}}
@php
    // Unread count for the sidebar badge; these are read nowhere else.
    $unreadMessages = \App\Models\ContactMessage::query()->unread()->count();
    // Comments waiting for approval — the queue that needs a human.
    $pendingComments = \App\Models\Comment::query()->pending()->count();

    $nav = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'match' => 'admin.dashboard', 'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75'],
        ['route' => 'admin.reports.index', 'label' => 'Reports', 'match' => 'admin.reports.*', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
        ['route' => 'admin.articles.index', 'label' => 'Stories', 'match' => 'admin.articles.*', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
        ['route' => 'admin.categories.index', 'label' => 'Sections', 'match' => 'admin.categories.*', 'icon' => 'M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122'],
        ['route' => 'admin.authors.index', 'label' => 'Authors', 'match' => 'admin.authors.*', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['route' => 'admin.comments.index', 'label' => 'Comments', 'match' => 'admin.comments.*', 'icon' => 'M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z'],
        ['route' => 'admin.messages.index', 'label' => 'Messages', 'match' => 'admin.messages.*', 'icon' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z'],
        ['route' => 'admin.subscribers.index', 'label' => 'Subscribers', 'match' => 'admin.subscribers.*', 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75'],
        ['route' => 'admin.settings.edit', 'label' => 'Settings', 'match' => 'admin.settings.*', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · {{ config('app.name') }} Admin</title>

    {{-- The dashboard must never be indexed. --}}
    <meta name="robots" content="noindex, nofollow">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-paper-soft font-sans text-ink">
<div class="flex min-h-screen flex-col lg:flex-row">

    {{-- ===================== Sidebar ===================== --}}
    <div class="bg-ink text-white lg:flex lg:w-64 lg:shrink-0 lg:flex-col">
        <div class="flex items-center justify-between px-5 py-4 lg:block">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-black uppercase tracking-tight">
                {{ config('app.name') }}<span class="text-brand">.</span>
            </a>
            <p class="hidden text-[10px] font-bold uppercase tracking-[0.2em] text-white/35 lg:mt-1 lg:block">
                Newsroom admin
            </p>

            {{-- Compact account menu on small screens --}}
            <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                @csrf
                <button type="submit" class="text-xs font-bold uppercase tracking-wider text-white/60 hover:text-white">
                    Sign out
                </button>
            </form>
        </div>

        {{-- Nav: a scrollable strip on mobile, a column on desktop --}}
        <nav aria-label="Admin" class="border-t border-ink-line lg:border-t-0">
            <ul class="flex overflow-x-auto px-3 py-2 lg:flex-col lg:gap-1 lg:overflow-visible lg:px-3 lg:py-2">
                @foreach ($nav as $item)
                    @php($active = request()->routeIs($item['match']))
                    <li class="shrink-0 lg:shrink">
                        <a href="{{ route($item['route']) }}"
                           @class([
                               'flex items-center gap-2.5 whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors',
                               'bg-brand text-white' => $active,
                               'text-white/65 hover:bg-white/10 hover:text-white' => ! $active,
                           ])
                           @if ($active) aria-current="page" @endif>
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                            {{ $item['label'] }}
                            @php($badge = match ($item['route']) {
                                'admin.messages.index' => $unreadMessages,
                                'admin.comments.index' => $pendingComments,
                                default => 0,
                            })
                            @if ($badge > 0)
                                <span class="ml-auto rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-bold tabular-nums">
                                    {{ $badge }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Account block, desktop only --}}
        <div class="mt-auto hidden border-t border-ink-line p-4 lg:block">
            <p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-white/45">{{ auth()->user()->email }}</p>

            <div class="mt-3 flex flex-col gap-2">
                <a href="{{ route('home') }}" target="_blank" rel="noopener"
                   class="text-xs font-semibold text-white/60 transition-colors hover:text-brand-light">
                    View site &rarr;
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-white/60 transition-colors hover:text-white">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== Content ===================== --}}
    <main class="min-w-0 flex-1">
        <header class="border-b border-rule bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-5 sm:px-8">
                <h1 class="text-2xl font-black tracking-tight">@yield('title', 'Dashboard')</h1>
                <div class="flex flex-wrap items-center gap-2">@yield('actions')</div>
            </div>
        </header>

        <div class="px-5 py-6 sm:px-8">
            {{-- Flash messages --}}
            @if (session('status'))
                <p role="status" class="mb-5 rounded-lg border border-brand/25 bg-brand/5 px-4 py-3 text-sm font-semibold text-brand-dark">
                    {{ session('status') }}
                </p>
            @endif

            @if (session('error'))
                <p role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    {{ session('error') }}
                </p>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-bold">Please fix the following:</p>
                    <ul class="mt-1.5 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
