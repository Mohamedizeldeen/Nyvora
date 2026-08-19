{{--
    Opens the contact popup with a topic preselected. Replaces what used to be
    a mailto: link.

    It is a real link to the contact page, not a <button>: without JavaScript
    it still takes the reader somewhere useful, and it is meaningful to a
    crawler. The JS intercepts the click and opens the dialog instead.

    Usage: <x-contact-button topic="tip">Send a story tip</x-contact-button>
           <x-contact-button topic="privacy" variant="link">privacy request</x-contact-button>
--}}
@props([
    'topic' => 'general',
    'variant' => 'button', // button | link
])

<a href="{{ route('contact') }}#contact-form"
   data-contact-open
   data-contact-topic="{{ $topic }}"
   {{ $attributes->class([
       'btn-primary' => $variant === 'button',
       'font-semibold text-brand underline underline-offset-2 transition-colors hover:text-brand-dark' => $variant === 'link',
   ]) }}>
    {{ $slot }}
</a>
