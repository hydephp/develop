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
        outDir: '_site/media',
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
    },
    plugins: [
        {
            name: 'copy-media',
            writeBundle() {
                // Copy files from _site/media to _media
                const fs = require('fs');
                const path = require('path');

                const sourceDir = '_site/media';
                const targetDir = '_media';

                if (!fs.existsSync(targetDir)) {
                    fs.mkdirSync(targetDir, { recursive: true });
                }

                // Copy all files recursively
                function copyRecursively(source, target) {
                    const files = fs.readdirSync(source);

                    files.forEach(file => {
                        const sourcePath = path.join(source, file);
                        const targetPath = path.join(target, file);

                        if (fs.lstatSync(sourcePath).isDirectory()) {
                            if (!fs.existsSync(targetPath)) {
                                fs.mkdirSync(targetPath, { recursive: true });
                            }
                            copyRecursively(sourcePath, targetPath);
                        } else {
                            fs.copyFileSync(sourcePath, targetPath);
                        }
                    });
                }

                copyRecursively(sourceDir, targetDir);
            }
        }
    ]
});