---
navigation:
    priority: 10
---

# Key HydePHP Concepts

## Introduction to the Hyde Framework

What makes HydePHP special are its "magic" features like autodiscovery and intelligent data generation.
All designed so that you can focus on your content, while the framework does the heavy lifting.

This page provides a high-level overview of the framework's capabilities, so you can quickly grasp its benefits.
As you delve deeper into the documentation, you'll discover the details of each feature and learn how to leverage them effectively.

## The HydeCLI

When you are not writing Markdown and Blade, most of your interactions with Hyde will be through the command line
using the **HydeCLI**, which is based on the Laravel Artisan Console that you may already be familiar with.

If you want to learn about the available commands and how to use them, you can visit the [Console Commands](console-commands) page,
or you can run any of the built-in help commands to get a list of available commands and their descriptions.

```bash
php hyde list
php hyde help <command>
php hyde <command> [--help]
```

## Directory structure

To take full advantage of the framework, it may first be good to familiarize ourselves with the directory structure.

| Directory                    | Purpose                                                     |
|------------------------------|-------------------------------------------------------------|
| `_docs`                      | For documentation pages                                     |
| `_posts`                     | For blog posts                                              |
| `_pages`                     | For static Markdown and Blade pages                         |
| `_media`                     | Store static assets to be copied to the build directory     |
| `_site`                      | The build directory where your compiled site will be stored |
| `config`                     | Configuration files for Hyde and integrations               |
| `resources/assets`           | Location for Laravel Mix source files (optional)            |
| `resources/views/components` | Location for Blade components (optional)                    |

## File Autodiscovery

Content files, meaning source Markdown and Blade files, are automatically discovered by Hyde and compiled to HTML when
building the site. This means that you don't need to worry about routing and controllers!

The directory a source file is in will determine the Blade template that is used to render it.

All source and output directories are configurable, but the defaults are as follows:

| Page/File Type | Source Directory | Output Directory | File Extensions     |
|----------------|------------------|------------------|---------------------|
| Static Pages   | `_pages/`        | `_site/`         | `.md`, `.blade.php` |
| Blog Posts     | `_posts/`        | `_site/posts/`   | `.md`               |
| Documentation  | `_docs/`         | `_site/docs/`    | `.md`               |
| Media Assets   | `_media/`        | `_site/media/`   | Common asset types  |

## Convention over Configuration

Hyde favours the "Convention over Configuration" paradigm and thus comes preconfigured with sensible defaults.
However, Hyde also strives to be modular and endlessly customizable hackable if you need it.
Take a look at the [customization and configuration guide](customization) to see the endless options available!

## Front Matter

>info **In a nutshell:** Front Matter is a block of YAML containing metadata, stored at the top of a Markdown file.

Front matter is heavily used in HydePHP to store metadata about pages. Hyde uses the front matter data to generate rich and dynamic content. For example, a blog post category, author website, or featured image.

Using front matter is optional, as Hyde will dynamically generate data based on the content itself. (Though any matter you provide will take precedence over the automatically generated data.)

### Front Matter in Markdown

All Markdown content files support Front Matter. Blog posts for example make heavy use of it.

The specific usage and schemas used for pages are documented in their respective documentation, however, here is a primer on the fundamentals.

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

### Front Matter in Blade

HydePHP has experimental support for creating front-matter in Blade templates, called BladeMatter.

The actual syntax does not use YAML; but instead PHP. However, the parsed end result is the same. Please note that
BladeMatter currently does not support multidimensional arrays or multi-line directives as the data is statically parsed.

To create BladeMatter, you simply use the default Laravel Blade `@php` directive to declare a variable in the template.

```blade
@php($title = 'BladeMatter Demo')
```

It will then be available through the global `$page` variable, `$page->matter('title')`.

## Automatic Routing

>info This covers an intermediate topic which is not required for basic usage, but is useful if you want to use the framework to design custom Blade templates.

### High-level overview

If you've ever worked in an MVC framework, you are probably familiar with the concept of routing.
And you are probably also familiar with how boring and tedious it can be. Thankfully, Hyde takes the pain out of routing
through the Hyde Autodiscovery process.

Internally, when booting the HydeCLI application, Hyde will automatically discover all the content files in the source
directories, and create a route index for all of them. This index works as a two-way link between source files and compiled files.

You can see all the routes and their corresponding source files by running the `hyde route:list` command.

```bash
php hyde route:list
```

[//]: # (TODO: Move below to a separate page and link to it.)

Don't worry if this sounds complex, as the key takeaway is that the index is created and maintained automatically.
Nevertheless, the routing system provides several helpers that you can optionally use in your Blade views to
automatically resolve relative links and other useful features.

### Accessing routes

Each route in your site is represented by a Route object. It's very easy to get a Route object instance from the Router's index.
There are a few ways to do this, but most commonly you'll use the Routes facade's `get()` method where you provide a route key, and it will return the Route object. The route key is generally `<output-directory/slug>`. Here are some examples:

```php
// Source file: _pages/index.md/index.blade.php
// Compiled file: _site/index.html
Routes::get('index')

// Source file: _posts/my-post.md
// Compiled file: _site/posts/my-post.html
Routes::get('posts/my-post')

// Source file: _docs/readme.md
// Compiled file: _site/docs/readme.html
Routes::get('docs/readme')
```

### Using the `x-link` component

When designing Blade layouts it can be useful to use the `x-link` component to automatically resolve relative links.

You can of course, use it just like a normal anchor tag like so:

```blade
<x-link href="index.html">Home</x-link>
```

But where it really shines is when you supply a route. This will then resolve the proper relative link, and format it to use pretty URLs if your site is configured to use them.

```blade
<x-link :href="Routes::get('index')">Home</x-link>
```

You can of course, also supply extra attributes like classes:

```blade
<x-link :href="Routes::get('index')" class="btn btn-primary">Home</x-link>
```

## Nested directories

### Introduction

Starting with Hyde v0.52.x-beta, there is limited support for nested directories, please be mindful that the behaviour of this may change until the next few versions. Please report any issues you encounter on [GitHub](https://github.com/hydephp/develop/issues).

#### First of, what do we mean by "nested directories"?

Simply put, a nested directory in Hyde is a source directory that contains a subdirectory. For example, if you have a directory _inside_ the `_pages` directory, that's a nested directory.

### Behaviour of nested pages

#### Automatically routed pages

As it is now, when you put a source file within a subdirectory of one of the following, it will be compiled into the corresponding output directory.

The following page types use this behaviour:
- Blade pages (`_pages/`)
- Markdown pages (`_pages/`)
- Markdown blog posts (`_posts/`)

For example, a source file stored as `_pages/about/contact.md` will be compiled into `_site/about/contact.html`, and a blog post stored as `_posts/2022/my-post.md` will be compiled into `_site/posts/2022/my-post.html`.

#### Documentation pages

Documentation pages behave a bit differently. Here, all documentation source files will still be compiled to the `_site/docs/` directory, but the subdirectory name will be used to assign a sidebar group/category to the page.

So for example, a source file stored as `_docs/getting-started/installation.md` will be compiled into `_site/docs/installation`, and placed in the sidebar group `Getting Started`.

You can learn more about this in the [documentation pages documentation](documentation-pages#using-sub-directories).


## Terminology

In this quick reference, we'll briefly go over some terminology and concepts used in HydePHP.
This will help you understand the documentation and codebase better, as well as helping you know what to search for when you need help.

### Laravel

Laravel is the PHP framework that HydePHP is built on top of. We use a specialized version called Laravel Zero,
which is optimized for command-line applications.

### Front Matter

Front Matter is a block of YAML, stored at the top of a Markdown file, enclosed by a set of triple-dashed lines.
It is commonly used to store metadata about the content, such as the title, author, date, etc.

### Markdown

Markdown is a lightweight markup language that uses plain text formatting syntax, designed to make it easy to create
structured content for the web. HydePHP uses Markdown as the base for most of its content files.

### Blade

Blade is the templating engine from Laravel, which allows developers to write clean and reusable code for the 
presentation layer of web applications. HydePHP uses Blade both for the built-in views and components,
as well as to provide powerful templating capabilities through Blade-based pages.

### YAML

YAML is a human-readable data serialization format used for configuration files and often used as the syntax for 
Front Matter in HydePHP content files. YAML is designed to be easily read by humans and parsed by machines,
making it a popular choice for many applications and frameworks.
