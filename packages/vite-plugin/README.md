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
            // Files to watch for changes, in addition to input paths
            watch: ['_pages', '_posts', '_docs'],
        }),
        tailwindcss(),
    ],
});
```

## Options

| Option    | Type       | Default                                                   | Description                              |
|-----------|------------|-----------------------------------------------------------|------------------------------------------|
| `input`   | `string[]` | `['resources/assets/app.css', 'resources/assets/app.js']` | Asset entry points to process            |
| `watch`   | `string[]` | `['_pages', '_posts', '_docs']`                           | Content directories to watch for changes |
| `refresh` | `boolean`  | `true`                                                    | Enable hot reloading for content files   |

## Contributing

Contributions to this package are made in the development monorepo [hydephp/develop](https://github.com/hydephp/develop) in the `packages/vite-plugin` directory. Make sure to link your local NPM package!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
