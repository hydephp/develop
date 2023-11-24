---
navigation:
    label: "Extensions & Integrations"
    priority: 80
---

# Extensions and Integrations

# First party extensions


## Realtime Compiler

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

_The live editor was added in Hyde Realtime Compiler Server v3.2.0 (November 2023)_

### Source code

- **GitHub**: [hydephp/realtime-compiler](https://github.com/hydephp/realtime-compiler)
- **Packagist**: [hydephp/realtime-compiler](https://packagist.org/packages/hyde/realtime-compiler)

---

# Integrations with third-party tools


## Torchlight

Torchlight is an amazing API for syntax highlighting, and is used by this site. I cannot recommend it highly enough,
especially for documentation sites and code-heavy blogs! As such, HydePHP has built-in support for Torchlight,
which is automatically enabled once you add an API token to your `.env` file. Nothing else needs to be done!

### Getting started

To get started you need an API token which you can get at [Torchlight.dev](https://torchlight.dev/).
It is entirely free for personal and open source projects, as seen on their [pricing page](https://torchlight.dev/#pricing).

When you have an API token, set it in the `.env` file in the root directory of your project.
Once a token is set, Hyde will automatically enable the CommonMark extension.

```env
TORCHLIGHT_TOKEN=torch_<your-api-token>
```

### Attribution and configuration

Note that for the free plan you need to provide an attribution link. Thankfully Hyde injects a customizable link
automatically to all pages that use Torchlight. You can of course disable and customize this in the `config/torchlight.php` file.

```php
'attribution' => [
    'enabled' => true,
    'markdown' => 'Syntax highlighting by <a href="https://torchlight.dev/" rel="noopener nofollow">Torchlight.dev</a>',
],
```

Don't have this file? Run `php hyde vendor:publish` to publish it.


## Contribute

Have an idea for an extension or integration? Let me know! I'd love to hear from you.

Get in touch on [GitHub](https://github.com/hydephp/hyde) or send me a DM on [Twitter](https://twitter.com/CodeWithCaen).
