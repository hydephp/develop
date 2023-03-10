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
-  [The HydeKernel](the-hydekernel)


## Booting pipeline

The autodiscovery is run when the HydeKernel boots. It does so in three distinct steps, which run in sequence as each
step depends on the previous one. Each discovery step runs in a `FoundationCollection` which both runs the actual
discovery process and stores the discovered data in memory.

#### The steps are as follows:

1. **Step one:** The file collection discovers all the source files and stores them in memory
2. **Step two:** The page collection parses all the source files into page model objects
3. **Step three:** The route collection generates route objects for all the pages

#### Interacting with the collections

Usually, you will interact with the collection data through intermediaries.
* For example, if you call `MarkdownPost::get('my-post')`, Hyde will retrieve that page from the page collection.
* If you call `Routes::get('index')`, Hyde will retrieve that route from the route collection.

## The HydeKernel

If you have not yet read the [HydeKernel Documentation](the-hydekernel), here's a quick recap:

The HydeKernel encapsulates a HydePHP project, providing helpful methods for interacting with it.
It is also responsible for booting the application, which includes the autodiscovery process.

The kernel is created very early on in the application lifecycle, in the bootstrap.php file, where it is also bound
as a singleton into the application service container.

At this point you might be wondering why we're talking about the kernel when this article is about autodiscovery.
Well, as you'll see in just a moment, the kernel is responsible for initiating the autodiscovery process.
The kernel is also where the discovered data is stored in memory, so it's important to understand how it works.

### The kernel lifecycle

Now that we know the role of the HydeKernel, let's take a look at its lifecycle. The kernel is "lazy-booted", meaning
that the all the heavy lifting only happens when you actually need it. Once booted, the kernel data will stay in memory
until the console application is terminated.

The kernel data is primarily stored in three collections that get generated during the kernel's boot process.
Let's take a look at a simplified version of the kernel's boot method to see how this works.

```php
public function boot(): void
{
    $this->files = FileCollection::boot($this);
    $this->pages = PageCollection::boot($this);
    $this->routes = RouteCollection::boot($this);

    // Scroll down to see what this is used for    
    $this->booted = true;
}
```

Here you'll see that we boot the three collections. This is where all the autodiscovery magic happens!

#### Deep dive into lazy-booting

If you're curious about how the kernel is lazy-booted, here's how it works!
Feel free to skip this section if this doesn't interest you.

```php
// This will boot the kernel if it hasn't been booted yet
public function pages(): PageCollection
{
    $this->needsToBeBooted();

    return $this->pages;
}

// This is the method that triggers the boot process
protected function needsToBeBooted(): void
{
    if (! $this->booted) {
        $this->boot();
    }
}
```

Yeah, it's really unglamorous I know. But it works! Having it like this will ensure that any time you call `Hyde::pages()`,
that underlying collection will always have been booted and be ready to use.
