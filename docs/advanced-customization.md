---
label: "Advanced Customization"
priority: 30
category: "Digging Deeper"
---

# Advanced Customization

## Introduction & Warning

>danger Danger lies ahead! Read this before you proceed.

This page covers advanced usage of potentially experimental and unstable features and is intended for developers
who know what they are doing and can handle the risk of breaking things. The article will also cover things
that you _can_ do, but that you maybe should not. With great power comes great responsibility. You have been warned.

### Emoji legend
Each section is marked with an emoji that indicates the level of risk. Note that pretty much all of these
are experimental features, and are not at all supported. Use at your own risk.

- 🧪 = Indicates experimental features bound to change at any time.
- ⚠ = Exercise caution when using this feature.
- 💔 = This could seriously break things

## Customizing source directories 🧪

>warning This may cause integrations such as the realtime compiler to break.

The source directory paths are stored in the PageModel objects. 
You can change them by modifying the static property, for example in a service provider.

Internally, the paths are registered in the HydeServiceProvider using the following method:

```php
// filepath Hyde\Framework\HydeServiceProvider
use Hyde\Framework\Concerns\RegistersDefaultDirectories;

public function register(): void
{
    $this->registerDefaultDirectories([
        BladePage::class => '_pages',
        MarkdownPage::class => '_pages',
        MarkdownPost::class => '_posts',
        DocumentationPage::class => '_docs',
    ]);
}
```

## Customizing the output directory 💔

>danger Hyde deletes all files in the output directory before compiling the site. Don't set this path to a directory that contains important files!

_The internal workings of this process is being rewritten, and such the documentation is deferred until the implementation._
