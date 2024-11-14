// Using Vite is optional, as the styles you need to get started are already included.
// However, if you customize existing or add new Tailwind classes, you can use Vite
// to compile the assets. See https://hydephp.com/docs/1.x/managing-assets.html.

import { defineConfig } from 'vite';
import { resolve } from 'path';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';
import fs from 'fs';
import path from 'path';

const hydeVitePlugin = () => ({
    name: 'hyde-vite',
    configureServer(server) {
        // Create hot file when Vite server starts
        fs.writeFileSync(path.resolve(process.cwd(), 'app/storage/framework/cache/vite.hot'), '');

        // Remove hot file when Vite server closes
        process.on('SIGINT', () => {
            fs.rmSync(path.resolve(process.cwd(), 'app/storage/framework/cache/vite.hot'));
            process.exit();
        });

        // Render the Vite index page when the root URL is requested
        server.middlewares.use((req, res, next) => {
            if (req.url === '/') {
                res.end(fs.readFileSync(
                    path.resolve(__dirname, 'vendor/hyde/realtime-compiler/resources/vite-index-page.html'),
                    'utf-8'
                ));
            } else {
                next();
            }
        });
    }
});

export default defineConfig({
    server: {
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        middlewareMode: false,
    },
    plugins: [hydeVitePlugin()],
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
        emptyOutDir: false,
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
