// Using Vite is optional as the styles you need to get started are already included.
// However, if you add new Tailwind classes, or any customizations, you can use
// Vite to compile the assets. See https://hydephp.com/docs/master/managing-assets.html.

// To compile the assets using Vite, run `php hyde vite` or `npm run build`.

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/assets/app.css',
            'resources/assets/app.js',
        ]),
    ],
    build: {
        outDir: '_media',
        emptyOutDir: false,
        rollupOptions: {
            output: {
                assetFileNames: '[name][extname]',
                chunkFileNames: '[name].js',
                entryFileNames: '[name].js',
            }
        }
    },
});
