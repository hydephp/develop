---
label: Quickstart Guide
priority: 1
category: "Getting Started"
---

# Quickstart Guide

## Installing HydePHP using Composer 
The recommended method of installing Hyde is using Composer.
```bash
// torchlight! {"lineNumbers": false}
composer create-project hyde/hyde --stability=dev
```

## Using the Hyde CLI
The main way to interact with Hyde is through HydeCLI.

If you are familiar with Laravel Artisan you will feel right at home.

Learn more about the HydeCLI in the [console commands](console-commands.html) documentation.

## Starting a development server

To make previewing your site a breeze you can use the real-time compiler
which builds your pages on the fly. Start it using the HydeCLI:
```bash
php hyde serve
```

## Creating content

### Directory structure

Creating content with Hyde is easy. Simply place Markdown files in one of the source directories, which are as follows:
```
// torchlight! {"lineNumbers": false}
├── _docs // For documentation pages              
├── _posts // For blog posts
├── _pages // For static Markdown and Blade pages
└── config // Configuration files for Hyde and integrations
```

### Scaffolding files

You can scaffold blog post files using the `php hyde make:post` command with automatically creates the front matter based on your input selections.

You can also scaffold pages with the `php hyde make:page` command.

```bash
php hyde make:page "Page Title" # Markdown is the default page type
php hyde make:page --type=blade # Creates a file extending the default layout
php hyde make:page --type=docs  # Quickly creates a documentation page
```

### Autodiscovery

When building the site, Hyde will your source directories for files and
compile them into static HTML using the appropriate layout depending
on what kind of page it is. You don't have to worry about routing
as Hyde takes care of that, including creating navigation menus!

## Compiling to static HTML

Now that you have some amazing content, you'll want to compile your site into static HTML.

This is as easy as executing the `build` command:
```bash
php hyde build
```

**Your site is then stored in the `_site` directory.**

### Managing assets

Hyde comes bundled with a precompiled and minified `app.css` containing all the Tailwind you need for the default views meaning that you don't even need to use NPM. However, Hyde is already configured to use Laravel Mix to compile your assets if you feel like there's a need to. See more on the [Managing Assets](managing-assets.html) page.

### Deploying your site

You are now ready to show your site to the world!

Simply copy the `_site` directory to your web server's document root and you're ready to go.

You can even use GitHub pages to host your site for free. That's what the Hyde website does,
using a CI that automatically builds and deploys this site.


## Further reading

Here's some ideas of what to read next:

- [Architecture Concepts](architecture-concepts.html)
- [Console Commands](console-commands.html)
- [Creating Blog Posts](blog-posts.html)