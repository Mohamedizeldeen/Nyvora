<?php

/**
 * `npm run dev` leaves public/hot behind when it stops. While that file exists
 * every page asks a Vite dev server for its assets and, once that server is
 * gone, loads no CSS at all — while still returning 200. It has caused a
 * silently unstyled site twice, so it is asserted rather than remembered.
 */
it('serves built assets, not a dead dev server', function () {
    expect(file_exists(public_path('hot')))
        ->toBeFalse('public/hot exists — delete it and run `npm run build`, or the site loads no CSS');

    expect(file_exists(public_path('build/manifest.json')))
        ->toBeTrue('Assets are not built — run `npm run build`');

    $this->get('/')
        ->assertOk()
        ->assertSee('/build/assets/', escape: false)
        ->assertDontSee(':5173', escape: false);
});
