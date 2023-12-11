---
navigation:
    label: "Realtime Compiler"
    priority: 81
---

# Realtime Compiler

The Hyde Realtime Compiler is included with Hyde installations and is what powers the `php hyde serve` command,
allowing you to preview your static site on a local development server without having to rebuild the site.

### Usage

To start the server, run the following command from a terminal in your project directory:

```bash
php hyde serve
```

This will start a local development server at `http://localhost:8080`

>warning Please note that the server is designed for local development, and should not be used on a public network.

### Configuration

The server can be configured in the `config/hyde.php` file to change the port, host, and to customize its features.

```php
// filepath config/hyde.php

'server' => [
    'port' => env('SERVER_PORT', 8080),
    'host' => env('SERVER_HOST', 'localhost'),
    'save_preview' => true,
],
```

### Live dashboard

#### Usage

The realtime compiler comes with a live dashboard that you can access at `http://localhost:8080/dashboard`.

From here, you can visually interact with your site content, including creating new pages and posts.

The live dashboard is not saved to your static site, and is only available through the development server.

#### Configuration

The dashboard can be customized, and disabled, in the `config/hyde.php` file.

```php
// filepath config/hyde.php

'server' => [
    'dashboard' => [
        'enabled' => env('SERVER_DASHBOARD', true),
        'interactive' => true,
        'tips' => true,
    ],
],
```

_The dashboard was added in Realtime Compiler v3.0.0 (March 2023), with interactive features added in v3.1.0 (October 2023)_

### Live edit

#### Usage

The live edit feature allows you to quickly edit Markdown-based pages (posts, docs, and pages) directly in the browser.

To enter the live editor, simply double-click on the article you want to edit, and it will be replaced with a text editor.
When you're done, click the save button to save the changes to the page's source file.

#### Shortcuts

The live editor supports the following keyboard shortcuts:
- `Ctrl + E` - Enter/Exit editor
- `Ctrl + S` - Save changes
- `esc` - Exit editor if active

#### Configuration

The live editor can be disabled in the `config/hyde.php` file.
The live editor plugin code will not be saved to your static site.

```php
// filepath config/hyde.php

'server' => [
    'live_edit' => env('SERVER_LIVE_EDIT', true),
],
```

_The live editor was added in Hyde Realtime Compiler Server v3.2.0 (December 2023)_

### Source code

- **GitHub**: [hydephp/realtime-compiler](https://github.com/hydephp/realtime-compiler)
- **Packagist**: [hydephp/realtime-compiler](https://packagist.org/packages/hyde/realtime-compiler)
