import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Inter, pulled from Google Fonts at build time and then served
                // from our own origin — no runtime request to a third party.
                // Emitted into the page by the @fonts directive in the layout.
                google('Inter', {
                    weights: [400, 500, 600, 700, 800, 900],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
