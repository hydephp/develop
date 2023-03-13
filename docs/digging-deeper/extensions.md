---
navigation:
    label: "Extensions & Integrations"
    priority: 28
---

# Extensions and Integrations

## First party extensions

### Realtime Compiler

The Hyde Realtime Compiler is now included with Hyde
installations and is what powers the `php hyde serve` command.

- **GitHub**: [hydephp/realtime-compiler](https://github.com/hydephp/realtime-compiler)
- **Packagist**: [hydephp/realtime-compiler](https://packagist.org/packages/hyde/realtime-compiler)
- **YouTube video**: [Introducing the Hyde Realtime Compiler](https://www.youtube.com/watch?v=1ZM4fQMKi64)


## Integrations with third-party tools

### Torchlight

Torchlight is an amazing API for syntax highlighting, and is used by this site. I cannot recommend it highly enough,
especially for documentation sites and code-heavy blogs! As such, HydePHP has built-in support for Torchlight,
which is automatically enabled once you add an API token to your `.env` file. Nothing else needs to be done!

#### Getting started

To get started you need an API token which you can get at [Torchlight.dev](https://torchlight.dev/).
It is entirely free for personal and open source projects, as seen on their [pricing page](https://torchlight.dev/#pricing).

When you have an API token, set it in the `.env` file in the root directory of your project.
Once a token is set, Hyde will automatically enable the CommonMark extension.

```env
TORCHLIGHT_TOKEN=torch_<your-api-token>
```

#### Attribution and configuration

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
