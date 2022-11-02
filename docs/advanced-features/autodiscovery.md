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
