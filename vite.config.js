// Using Vite is optional, as the styles you need to get started are already included.
// However, if you customize existing or add new Tailwind classes, you can use Vite
// to compile the assets. See https://hydephp.com/docs/1.x/managing-assets.html.

import { defineConfig } from 'vite';
import tailwindcss from "@tailwindcss/vite";

// Import the HydePHP Vite plugin
// In a real-world scenario, this would be:
// import hyde from 'hyde-vite-plugin';
import hyde from './packages/vite-plugin/dist/index.js';

export default defineConfig({
    plugins: [
        hyde({
            input: ['resources/assets/app.css', 'resources/assets/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
