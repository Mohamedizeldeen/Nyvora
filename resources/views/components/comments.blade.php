{{--
    Reader comments under an article.

    Only approved comments are passed in — a comment awaiting moderation is
    never rendered, not even to the person who wrote it, because there is no
    way to tell them apart without cookies.

    Usage: <x-comments :article="$article" :comments="$comments" />
--}}
@props([
    'article',
    'comments' => [],
])

<section id="comments" class="mt-14 scroll-mt-24" aria-labelledby="comments-heading">
    <x-section-heading id="comments-heading" :accent="$article->category?->displayColor()">
        {{ count($comments) }} {{ Str::plural('comment', count($comments)) }}
    </x-section-heading>

    {{-- ============ Existing comments ============ --}}
    @if (count($comments))
        <ol class="space-y-6">
            @foreach ($comments as $comment)
                <li class="flex gap-4">
                    <span aria-hidden="true"
                          class="flex size-10 shrink-0 items-center justify-center rounded-full bg-ink/5 text-xs font-bold text-ink/50">
                        {{ $comment->initials() }}
                    </span>

                    <div class="min-w-0 flex-1 border-b border-rule pb-6">
                        <div class="flex flex-wrap items-baseline gap-x-2.5">
                            <p class="text-sm font-extrabold tracking-tight text-ink">{{ $comment->name }}</p>
                            <time datetime="{{ $comment->approved_at->toIso8601String() }}"
                                  title="{{ $comment->approved_at->format('j F Y, H:i') }}"
                                  class="text-xs text-ink/40">
                                {{ $comment->approved_at->diffForHumans() }}
                            </time>
                        </div>

                        {{-- Escaped, with line breaks preserved. Nothing a reader
                             types can inject markup. --}}
                        <p class="mt-1.5 text-sm leading-relaxed whitespace-pre-line text-ink/75">{{ $comment->body }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        <p class="rounded-xl border border-dashed border-rule bg-paper-soft px-6 py-10 text-center text-sm text-ink/50">
            No comments yet.
            @if ($article->acceptsComments())
                Be the first to say something.
            @endif
        </p>
    @endif

    {{-- ============ Leave a comment ============ ============ --}}
    @if ($article->acceptsComments())
        <div class="mt-10">
            @if (session('comment_posted'))
                <p role="status"
                   class="rounded-xl border border-brand/25 bg-brand/5 px-5 py-4 text-sm font-semibold text-brand-dark">
                    {{ session('comment_posted') }}
                </p>
            @else
                <h3 class="text-lg font-black uppercase tracking-tight text-ink">Leave a comment</h3>
                <p class="mt-1.5 text-sm text-ink/55">
                    Comments are read by our editors before they appear. Just a name and what you
                    want to say — we do not ask for an email address.
                </p>

                <form method="POST" action="{{ route('comments.store', $article) }}" class="mt-5 space-y-4">
                    @csrf

                    {{-- Honeypot --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="comment-website">Website</label>
                        <input id="comment-website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    @if ($errors->any())
                        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="max-w-sm">
                        <label for="comment-name" class="admin-label">Your name</label>
                        <input id="comment-name" type="text" name="name" required maxlength="80"
                               value="{{ old('name') }}" autocomplete="name"
                               @class(['admin-input', 'admin-input-invalid' => $errors->has('name')])>
                    </div>

                    <div>
                        <label for="comment-body" class="admin-label">Your comment</label>
                        <textarea id="comment-body" name="body" rows="5" required minlength="3" maxlength="2000"
                                  @class(['admin-input resize-y', 'admin-input-invalid' => $errors->has('body')])>{{ old('body') }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="max-w-sm text-xs leading-relaxed text-ink/45">
                            Your name and comment are published on this page once approved. See our
                            <a href="{{ route('privacy-policy') }}" class="underline underline-offset-2 hover:text-brand">privacy policy</a>.
                        </p>

                        <button type="submit" class="btn-primary shrink-0">Post comment</button>
                    </div>
                </form>
            @endif
        </div>
    @else
        <p class="mt-8 text-sm text-ink/45">
            Comments are closed on this story.
        </p>
    @endif
</section>
