{{--
    Publish-state badge for the admin lists: Live / Scheduled / Draft.

    Usage: <x-admin.status-pill :article="$article" />
--}}
@props(['article'])

@php
    if ($article->published_at === null) {
        $tone = 'bg-amber-100 text-amber-800';
        $text = 'Draft';
    } elseif ($article->published_at->isFuture()) {
        $tone = 'bg-sky-100 text-sky-800';
        $text = 'Scheduled';
    } else {
        $tone = 'bg-emerald-100 text-emerald-800';
        $text = 'Live';
    }
@endphp

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider',
    $tone,
]) }}>
    {{ $text }}
</span>
