---
priority: 10
category: "Getting Started"
---

# Some key concepts in Hyde

## The HydeCLI

When you are not writing Markdown and Blade, most of your interactions with Hyde will be through the
Hyde Command Line Interface (CLI). 
Since the CLI is based on the Laravel Artisan Console, so you may actually already be familiar with it.

You should take a look at [the Console Commands page](console-commands.html)
to learn more and see the available commands and their usage.

```bash
php hyde <command> [--help]
```

## Directory structure

To take full advantage of the framework, it may first be good to familiarize ourselves with the directory structure.

```
// torchlight! {"lineNumbers": false}
├── _docs  // For documentation pages              
├── _posts // For blog posts
├── _pages // For static Markdown and Blade pages
├── _media // Store static assets to be copied to the build directory
├── _site  // The build directory where your compiled site will be stored
├── config // Configuration files for Hyde and integrations
├── resources/assets // Location for Laravel Mix source files (optional)
└── resources/views/components // Location for Blade components (optional)
```

> Note that the `_site` directory is emptied every time you run the `hyde build` command.
> It's intended that you keep the directory out of your VCS, for example by adding it to your .gitignore file.


## File Autodiscovery

Content files, meaning source Markdown and Blade files, are automatically
discovered by Hyde and compiled to HTML when building the site.
This means that you don't need to worry about routing and controllers!

The directory a source file is in will determine the Blade template that is used to render it.

Here is an overview of the content source directories, the output directory of the compiled files,
and the file extensions supported by each. Files starting with an `_underscore` are ignored.

| Page/File Type | Source Directory | Output Directory | File Extensions     |
|----------------|------------------|------------------|---------------------|
| Static Pages   | `_pages/`        | `_site/`         | `.md`, `.blade.php` |
| Blog Posts     | `_posts/`        | `_site/posts/`   | `.md`               |
| Documentation  | `_docs/`         | `_site/docs/`    | `.md`               |
| Media Assets   | `_media/`        | `_site/media/`   | See full list below |

<small>
<blockquote>
Default media file types supported: `.png`, `.svg`, `.jpg`, `.jpeg`, `.gif`, `.ico`, `.css`, `.js`. Can be changed using the `hyde.media_extensions` config setting.
</blockquote>
</small>

## Convention over Configuration

Hyde favours the "Convention over Configuration" paradigm and thus comes preconfigured with sensible defaults.
However, Hyde also strives to be modular and endlessly customizable hackable if you need it. 
Take a look at the [customization and configuration guide](customization.html) to see the endless options available!

## Front Matter

All Markdown content files support Front Matter. Blog posts for example make heavy use of it.

The specific usage and schemas used for pages are documented in their respective documentation,
however, here is a primer on the fundamentals.

- Front matter is stored in a block of YAML that starts and ends with a `---` line.
- The front matter should be the very first thing in the Markdown file.
- Each key-pair value should be on its own line.

**Example:**
```markdown
---
title: "My New Post"
author:
  name: "John Doe"
  website: https://mrhyde.example.com
---

## Markdown comes here

Lorem ipsum dolor sit amet, etc.
```


## Automatic Routing

>info This covers an intermediate topic which is not required for basic usage, but is useful if you want to use the framework to design custom Blade templates.

### High-level overview

If you've ever worked in a MVC framework, you are probably familiar with the concept of routing. And you are probably also familiar with how boring and tedious it can be. Hyde takes the pain out of routing through the Hyde Autodiscovery process.

Internally, when booting the Hyde application, Hyde will automatically discover all of the content files in the source directory and create a routing index for them. This index works as a two-way link between source files and compiled files.

Don't worry if this sounds complex, as the key takeaway is that the index is created and maintained automatically. There is currently no way to manually add or remove files from the index. Making it function more like a source map than a proper router. Nevertheless, the routing system provides several helpers that you can optionally use in your Blade views to automatically resolve relative links and other useful features.

### Accessing routes

Each route in your site is represented by a Route object. It's very easy to get a Route object instance from the Router's index. There are a few ways to do this, but most commonly you'll use the Route facade's `get()` method where you provide a route key, and it will return the Route object. The route key is generally `<output-directory/slug>`. Here are some examples:

```php
// Source file: _pages/index.md/index.blade.php
// Compiled file: _site/index.html
Route::get('index') 

// Source file: _posts/my-post.md
// Compiled file: _site/posts/my-post.html
Route::get('posts.my-post')

// Source file: _docs/readme.md
// Compiled file: _site/docs/readme.html
Route::get('docs.readme')
```

### Using the `x-link` component

When designing Blade layouts it can be useful to use the `x-link` component to automatically resolve relative links.

You can of course, use it just like a normal anchor tag like so:
```blade
<x-link href="index.html">Home</x-link>
```

But where it really shines is when you supply a route. This will then resolve the proper relative link, and format it to use pretty URLs if your site is configured to use them.

```blade
<x-link href="Route::get('index')">Home</x-link>
```

You can of course, also supply extra attributes like classes:
```blade
<x-link href="Route::get('index')" class="btn btn-primary">Home</x-link>
```
