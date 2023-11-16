---
navigation.priority: 1000
---

# The Extensions API

## Introduction

The Extensions API a powerful interface designed for package developers who want to extend the functionality of HydePHP.

Using the API, you can hook directly into the HydePHP Kernel and extend sites with custom page types and new features.

### Prerequisites

Before creating your extension, it will certainly be helpful if you first become familiar with 
the basic internal architecture of HydePHP, as well as how the auto-discovery system works,
so you can understand how your code works with the internals.

- [Core concepts overview](core-concepts)
- [Architecture concepts](architecture-concepts)
- [Autodiscovery](autodiscovery)

This documentation page will function heavily through examples, so it's recommended that you first read the sections in order.

### The why and how of the Extensions API

HydePHP being a static site generator, the Extensions API is centered around [Page Models](page-models),
which you are hopefully already familiar with, otherwise you should read up on them first.

What the Extensions API does is to allow you to create custom page types, and tell HydePHP how to discover them.
This may sound like a small thing, but it's actually incredibly powerful as the page models are the foundation
of HydePHP's functionality. They tell the system how to discover pages, how to render them,
and how they interact with the site.

Any other functionality you want to add to HydePHP, such as new commands, new configuration options,
that can all be added the same way as you would in Laravel, and are thus not part of the Extensions API.

You may want to read up on the [Laravel package development guide](https://laravel.com/docs/10.x/packages)

## Creating your Extension class

The entry point for your extension is your Extensions class. Within this, you can register the custom page classes for your extension.
If needed, you can also register discovery handlers which can run custom logic at various points in the boot process.

In this article we will create an extension that registers a new type of page, a `JsonPageExtension`.

The first step is to create a class that extends the `HydeExtension` class:

```php
use Hyde\Foundation\Concerns\HydeExtension;

class JsonPageExtension extends HydeExtension {
    //
}
```

In here, we will register our extension class name in the `getPageClasses` method:

```php
class JsonPageExtension extends HydeExtension {
    public static function getPageClasses(): array {
        return [
            JsonPage::class,
        ];
    }
}
```

Hyde will then use the information from the `JsonPage` class to automatically discover the pages when booting the Kernel.

However, if our page model has more complex requirements we can add a discovery handler, so let's take a quick look at that next.

### Discovery handlers

The discovery handlers lets you run code at various points of the booting process. This is usually only needed if your
page models don't contain the information required for Hyde run the standard auto-discovery, for example if you need
something custom. And while you usually only in that case need to add files to the Kernel `FileCollection`, the
`HydeExtension` class offers following three discovery handlers, in case you need them:

```php
/** Runs during file discovery */
public function discoverFiles(FileCollection $collection): void;

/** Runs during page discovery */
public function discoverPages(PageCollection $collection): void;

/** Runs during route discovery */
public function discoverRoutes(RouteCollection $collection): void;
```

Any of these can be implemented in your extension class, and they will be called during the discovery. As you can see,
the instance of the discovery collection is injected into the method for you to interact with.

#### Discovery handler example

Let's go crazy and implement a discovery handler to collect `JsonPage` files from an external API! We will do this
by implementing the `discoverPages` method in our extension class, and from there inject pages retrieved from our API.

```php
class JsonPageExtension extends HydeExtension {
    public function discoverPages(PageCollection $collection): void {
        $pages = Http::get('https://example.com/my-api')->collect();

        $pages->each(function (array $page) use ($collection): void {
            $collection->addPage(JsonPage::fromArray($page));
        });
    }
}
```

Since the discovery steps are handled sequentially, the added pages will automatically be discovered as routes without
us having to implement that handler method.