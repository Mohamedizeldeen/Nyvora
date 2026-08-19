<?php

use Illuminate\View\Compilers\BladeCompiler;

/**
 * Blade ignores an @directive that sits immediately after a word character —
 * "...at any time@if(...)" renders the directive as literal text instead of
 * running it, and it desynchronises the directives that follow. It is silent:
 * the page still returns 200, it just shows "@if (...)" to the reader.
 *
 * This has bitten this codebase twice, so it is checked rather than remembered.
 */
it('compiles every Blade directive, leaving none as literal text', function () {
    $compiler = new BladeCompiler(app('files'), sys_get_temp_dir());

    $directives = implode('|', [
        'if', 'elseif', 'else', 'endif', 'unless', 'endunless',
        'foreach', 'endforeach', 'forelse', 'endforelse', 'empty', 'endempty',
        'for', 'endfor', 'while', 'endwhile', 'php', 'endphp',
        'error', 'enderror', 'auth', 'endauth', 'guest', 'endguest',
        'push', 'endpush', 'prepend', 'endprepend', 'isset', 'endisset',
        'continue', 'break', 'hasSection', 'sectionMissing',
        'section', 'endsection', 'yield', 'extends', 'include',
        'csrf', 'method', 'class', 'style', 'checked', 'selected', 'disabled', 'props',
    ]);

    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('views'))
    );

    foreach ($files as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $compiled = $compiler->compileString(file_get_contents($file->getPathname()));

        if (preg_match_all('/@('.$directives.')\b/', $compiled, $matches)) {
            $relative = str_replace(base_path().'/', '', $file->getPathname());

            foreach (array_unique($matches[0]) as $directive) {
                $offenders[] = "{$relative}: {$directive}";
            }
        }
    }

    expect($offenders)->toBe([]);
});
