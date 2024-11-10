import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        outDir: '_site/media',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'resources/assets/app.js'),
                style: resolve(__dirname, 'resources/assets/app.css')
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]'
            }
        }
    },
    plugins: [
        {
            name: 'copy-media',
            writeBundle() {
                // Copy files from _site/media to _media
                // You may want to use fs.copyFileSync or similar here
            }
        }
    ]
});