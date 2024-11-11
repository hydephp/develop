// Using Vite is optional, as the styles you need to get started are already included.
// However, if you customize existing or add new Tailwind classes, you can use Vite
// to compile the assets. See https://hydephp.com/docs/1.x/managing-assets.html.

import { defineConfig } from 'vite';
import { resolve } from 'path';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';

export default defineConfig({
    css: {
        postcss: {
            plugins: [
                tailwindcss,
                autoprefixer
            ]
        }
    },
    build: {
        outDir: '_media',
        emptyOutDir: true,
        rollupOptions: {
            input: [
                resolve(__dirname, 'resources/assets/app.js'),
                resolve(__dirname, 'resources/assets/app.css')
            ],
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]'
            }
        }
    }
});