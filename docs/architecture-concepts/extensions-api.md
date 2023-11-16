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


