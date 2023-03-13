# InMemoryPages

## Introduction

This class is a special page class that is not backed by a file on disk, but rather generated at runtime. While it will
probably not be useful for the majority of users, it's a great class to know about if you are a package developer,
but feel free to skip this section if you're not interested in this.

### When to use

This class is especially useful for one-off custom pages. But if your usage grows, or if you want to utilize Hyde
autodiscovery, you may benefit from creating a custom page class instead, as that will give you full control.

### About discovery

Since the InMemoryPages are not present in the filesystem, they cannot be found by the auto-discovery process,
thus it's up to the developer to manually register them. If you are developing for your own project, you can do this in
the `boot` method of a service provider, such as the `AppServiceProvider` which is already present in your `app/` directory.

If you are developing a package, you may instead want to register the page in your package extension class, within the
page collection callback. In either case, if you want your page to be able to be fully processed by Hyde, you need to
make sure you register it before the full application is booted so that routes can be generated.

_To see how to register the page, see the examples below, first we must look at how to actually create the page._


## Creating the page

To create an InMemoryPage, you need to instantiate it, and pass it the required parameters.

A page would not be useful without any content to render. The class offers two content options through the constructor.

You can either pass a string to the `$contents` parameter, Hyde will then save that literally as the page's contents.

```php
$page = new InMemoryPage(contents: 'Hello World!');
```

Alternatively, you can pass a Blade view name to the `$view` parameter, and Hyde will use that view to render the page
contents with the supplied front matter during the static site build process.

>warning Note that `$contents` take precedence over `$view`, so if you pass both, only `$contents` will be used.

You can also register a macro with the name `'compile'` to overload the default compile method.


## API Reference

To see all the methods available, please see the [InMemoryPage API reference](hyde-pages#inmemorypage).
