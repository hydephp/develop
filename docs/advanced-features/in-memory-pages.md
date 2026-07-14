---
abstract: "InMemoryPages are HydePHP pages generated at runtime rather than backed by a file on disk, handy for package developers and one-off custom pages."
---

# InMemoryPages

## Introduction

This class is a special page class that is not backed by a file on disk, but rather generated at runtime. While it will
probably not be useful for the majority of users, it's a great class to know about if you are a package developer,
but feel free to skip this section if you're not interested in this.

### When to use

This class is especially useful for one-off custom pages. But if your usage grows, or if you want to utilize Hyde
autodiscovery, you may benefit from creating a custom page class instead, as that will give you full control.

### About discovery

Since the InMemoryPages are not present in the filesystem, they cannot be found by the auto-discovery process.
Instead, it's up to the developer to manually register them. If you are working on your own project, you can do this in
the `boot` method of a service provider, such as the `AppServiceProvider` which is already present in your `app/` directory.

If you are developing a package, you may instead want to register the page in your package extension class, within the
page collection callback. In either case, if you want your page to be able to be fully processed by Hyde, you need to
make sure you register it before the full application is booted so that routes can be generated.

_To see how to register the page, see the examples below. But first we must look at how to actually create the page._

## Creating the Page

To create an InMemoryPage, you need to instantiate it with the required parameters.

The constructor supports three content strategies: literal string contents, lazy closure contents, and Blade view rendering.

Pass a string to the `$contents` parameter when the page contents are already available. Hyde saves the string literally.

```php
$page = new InMemoryPage('robots.txt', contents: "User-agent: *\n");
```

Pass a closure when the contents should be generated lazily during compilation. The closure is resolved again for each
compilation, which makes it useful for pages generated from the current application state.

```php
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;
use Hyde\Pages\InMemoryPage;

$page = new InMemoryPage(
    'sitemap.xml',
    ['navigation' => ['hidden' => true]],
    fn (SitemapGenerator $generator): string => $generator->generate()->getXml(),
);
```

When contents are provided as a closure, Hyde resolves and invokes it through the application container each time the
contents are requested. Dependencies declared as closure parameters are injected lazily. Hyde does not rebind content
closures, so first-class callable closures preserve their original object binding. The `$contents` parameter accepts
only a string or closure, not arbitrary callables.

Alternatively, pass a Blade view name or arbitrary `.blade.php` file to the `$view` parameter. Hyde renders the view
with the supplied front matter during the static site build process.

>warning Note that `$contents` take precedence over `$view`, so if you pass both, only `$contents` will be used.
> This includes a closure that returns an empty string. A view is used only when literal contents are the empty string.

Instance macros remain supported for adding extra methods to a specific page, but a macro named `compile` does not
override the real `compile()` method. Use closure contents for lazy dynamic output. For complete class-level behavior
changes, extend `InMemoryPage` and override `compile()` in your custom page class.

## Registering the Page

### In a Hyde project

Register the page in the `boot` method of your `AppServiceProvider`. The `booting` callback runs before Hyde's discovery
process, allowing the route to be generated automatically.

```php
// filepath: app/Providers/AppServiceProvider.php

namespace App\Providers;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\HydeKernel;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Hyde::booting(function (HydeKernel $hyde): void {
            $hyde->pages()->addPage(new InMemoryPage(
                identifier: 'hello',
                matter: ['title' => 'Hello'],
                contents: '<h1>Hello World!</h1>',
            ));
        });
    }
}
```

The page will be written to `_site/hello.html` and can be referenced using the `hello` route key.

### In a package extension

Package extensions can register the page directly in the page discovery callback. Pages added at this stage are
subsequently discovered as routes.

```php
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\Kernel\PageCollection;

class ExampleExtension extends HydeExtension
{
    public function discoverPages(PageCollection $collection): void
    {
        $collection->addPage(new InMemoryPage(
            identifier: 'hello',
            matter: ['title' => 'Hello'],
            contents: '<h1>Hello World!</h1>',
        ));
    }
}
```

## API Reference

To see all the methods available, please see the [InMemoryPage API reference](hyde-pages#inmemorypage).
