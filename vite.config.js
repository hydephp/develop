import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/assets/app.css',
            'resources/assets/app.js',
        ]),
    ],
});
