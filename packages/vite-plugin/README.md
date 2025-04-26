# HydePHP Vite Plugin

Official Vite plugin for HydePHP's realtime compiler integration.

## Installation

```bash
npm install hyde-vite-plugin --save-dev
```

## Usage

```js
// vite.config.js
import { defineConfig } from 'vite';
import hyde from 'hyde-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        hyde({
            // Optional configuration
            input: ['resources/assets/app.css', 'resources/assets/app.js'],
        }),
        tailwindcss(),
    ],
});
```

## Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `input` | `string[]` | `['resources/assets/app.css', 'resources/assets/app.js']` | Asset entry points to process |
| `refresh` | `boolean` | `true` | Enable hot reloading for content files |

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
