{{--
    The small uppercase category tag, tinted with the category's `color` column.

    Variants:
      text — coloured text, used on light backgrounds (feed rows, article header)
      chip — filled pill, used on top of images and dark backgrounds

    Usage: <x-category-label :category="$article->category" variant="chip" />
--}}
@props([
    'category',
    'variant' => 'text',
])

@php
    $color = $category->displayColor();
@endphp

<a href="{{ route('category.show', $category) }}"
   {{ $attributes
        ->class([
            'inline-block text-[11px] font-bold uppercase tracking-[0.12em] transition-opacity hover:opacity-70',
            'rounded-sm px-2 py-1 text-white' => $variant === 'chip',
        ])
        ->style([
            "color: {$color}" => $variant === 'text',
            "background-color: {$color}" => $variant === 'chip',
        ]) }}>
    {{ $category->name }}
</a>
