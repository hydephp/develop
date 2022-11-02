# Autodiscovery

## Introduction

HydePHP aims to reduce the amount of configuration you need to do to get a site up and running.
To that end, Hyde uses a process called autodiscovery to automatically find and register your pages.

This article will go into detail about how autodiscovery works as well as the lifecycle of a site build.

### The short version

Hyde will use the information in the page model classes to scan the source directories for matching files which are
parsed using instructions from the model's class, resulting in data used to construct objects that get stored in the HydeKernel.

### Prerequisites

Before reading this article, you should be familiar with the following concepts:
-  [Page Models](page-models)

## The HydeKernel

In the centre, or should I say _core_, of HydePHP is the HydeKernel. The kernel encapsulates a HydePHP project and
provides helpful methods for interacting with it. You can think of it as the heart of HydePHP, if you're a romantic.

The HydeKernel is so important that you have probably used it already. The main entry point for the HydePHP
API is the Hyde facade, which calls methods on the kernel.

```php
use Hyde\Hyde;
use Hyde\Foundation\HydeKernel;

Hyde::version() === app(HydeKernel::class)->version();
```

The kernel is created very early on in the application lifecycle, in the bootstrap.php file, where it is also bound
as a singleton into the application service container.

At this point you might be wondering why we're talking about the kernel when this article is about autodiscovery.
Well, as you'll see in just a moment, the kernel is responsible for initiating the autodiscovery process.
The kernel is also where the discovered data is stored in memory, so it's important to understand how it works.

### The kernel lifecycle

Now that we know the role of the HydeKernel, let's take a look at its lifecycle. The kernel is "lazy-booted", meaning
that the all the heavy lifting only happens when you actually need it. Once booted, the kernel data will stay in memory
until the application is terminated.

The kernel data is primarily stored in three collections that get generated during the kernel's boot process.
Let's take a look at the kernel's boot method to see how this works.

```php
public function boot(): void
{
    $this->booted = true;

    $this->files = FileCollection::boot($this);
    $this->pages = PageCollection::boot($this);
    $this->routes = RouteCollection::boot($this);
}
```

Here you'll see that we boot the three collections. This is where all the autodiscovery magic happens!
We'll take a closer look at each of these in a second, but first, here's how the "lazy-booting" works.

```php
// This will boot the kernel if it hasn't been booted yet
protected function needsToBeBooted(): void
{
    if (! $this->booted) {
        $this->boot();
    }
}

// And here's an example of how it's used
public function pages(): PageCollection
{
    $this->needsToBeBooted();

    return $this->pages;
}
```

Yeah, it's really unglamorous I know. But it works! Having it like this will ensure that any time you call `Hyde::pages()`,
that underlying collection will always have been booted and be ready to use.