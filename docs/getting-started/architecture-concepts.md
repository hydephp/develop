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

## Page Types

All pages in HydePHP are internally represented by a page object. The object instance contains all the data HydePHP has parsed and discovered.

HydePHP ships with a few different page types. Each page type is defined as its own page class; all of which extend the base HydePage class.

The page classes are very important and fill two roles:

1. The classes themselves, act as blueprints, defining information for Hyde to use when parsing and compiling the page.
2. The instances of the classes, contain the actual data HydePHP has parsed and discovered.

### Overview of a page class

As an example of what they look like, here is a simplified version of the MarkdownPost class.

The static properties define the blueprint for the entire page type, and the instance properties are the actual data HydePHP has parsed or generated from the source file.

```php
class MarkdownPost extends BaseMarkdownPage
{
    public static string $sourceDirectory = '_posts'; // The directory where HydePHP will look for source files
    public static string $outputDirectory = 'posts'; // The directory where HydePHP will output compiled files
    public static string $fileExtension = '.md'; // The file extension HydePHP will look for

    public FrontMatter $matter; // The parsed front matter data
    public Markdown $markdown; // The parsed Markdown content

    public readonly string $identifier; // The unique identifier for the page
    public readonly string $routeKey; // The key used to access the page in the routing system
}
```

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

## Importance of paths

Since HydePHP automatically discovers and compiles content files, it is important to understand how HydePHP handles paths,
as the file names and directories they are in will directly influence how the page will be compiled.

**There are a few terms that are important to define before we explore their correlations.**

- **Path:** The full path to a file, including the file name, directory, and extension.
- **Identifier:** The unique identifier for a page. Unique only within the same page type.
- **Route key:** The key used to access the page in the routing system. Unique across all site pages.

**Now that we have defined the terms, let's explore how they are related.**

If you remember our `MarkdownPost` class from above, you'll recall that we have a few important properties:

```php
static $sourceDirectory = '_posts';
static $outputDirectory = 'posts';
static $fileExtension = '.md';
$identifier; // For example: "my-new-post"
$routeKey;  // For example: "posts/my-new-post"
```

You may also have noticed that we have no source path property here, this is because all other properties are enough
to determine all needed data, without needing to store extra information.

Now, we can finally look at some examples of how the data is related, by providing examples of how the properties are generated.

```php
// The source path relative to the project root:
$path = '_posts/my-new-post.md';

// Identifier from path:
$identifier = String::between($path, $sourceDirectory, $fileExtension) = 'my-new-post';

// Route key from identifier:
$routeKey = $outputDirectory.'/'.$identifier = 'posts/my-new-post';

// Output path from route key:
$outputPath = $routeKey.'.html' = 'posts/my-new-post.html';

// Source path from identifier:
$sourcePath = $sourceDirectory.'/'.$identifier.$fileExtension = '_posts/my-new-post.md';
```

#### Notes on duplicate and changing identifiers:

Since identifiers are unique to the page type, it is possible to have duplicate identifiers across different page types.
For example, it's perfectly valid to have a blog post with the identifier `about` and a static page with the identifier `about`,
as the route key will differ due to the different output directories. However, if you customize the output directories,
you need to keep this in mind. It also means that route keys will change when you change the output directory which can break links.

#### Notes on nested directories:

Since identifiers contain everything between the source directory and the file extension, it is possible to have nested directories.
These will be retained in the route key. So if you have a page with the path `_pages/about/contact.md`, the identifier will be `about/contact`,
and the route key (and thus the output path) will be `about/contact`, and a blog post stored as `_posts/2023/my-post.md`
will have the identifier `2023/my-post` and the route key `posts/2023/my-post`.

In some cases you can configure Hyde to handle nested directories differently, for example, documentation pages can
utilize the subdirectory name to automatically create a sidebar group, and Markdown/Blade pages can be placed in dropdowns.

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

**To use Front Matter, add Yaml to the top of your Markdown file:**

```markdown
---
title: "My New Post"
author:
  name: "John Doe"
  website: https://example.com
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

>info **In a nutshell:** Hyde will automatically create routes for your source files.

If you've ever worked in an MVC framework, you are probably familiar with the concept of routing.
And you are probably also familiar with how boring and tedious it can be. Thankfully, Hyde takes the pain out of routing by doing it for you!

During the Autodiscovery process. Hyde will automatically discover all the content files in the source directories,
and create routes for all of them, and store them in an index which works as a two-way link between source files and compiled files.

You can see all the routes and their corresponding source files by running the `hyde route:list` command.

```bash
php hyde route:list
```

To access routes in your code, simply use the Routes facade and specify the route key for the desired page.

```php
Routes::get('posts/my-post')
```

To learn more about the routing system, please visit the [routing documentation](automatic-routing).

## Terminology

In this quick reference, we'll briefly go over some terminology and concepts used in HydePHP.
This will help you understand the documentation and codebase better, as well as helping you know what to search for when you need help.

### Table of contents

<div class="lg:flex">

<div style="margin-right: 2rem;">

#### Tools

- [HydePHP](#hydephp)
- [Laravel](#laravel)
- [Composer](#composer)
- [Tailwind CSS](#tailwind-css)

</div>

<div style="margin-right: 2rem;">

#### Languages

- [Front Matter](#front-matter)
- [Markdown](#markdown)
- [Blade](#blade)
- [YAML](#yaml)
- [PHP](#php)
- [HTML](#html)

</div>

<div style="margin-right: 2rem;">

#### General Concepts

- [Static Sites](#static-sites)
- [Version Control](#version-control)
- [Git](#git)

</div>

<div style="margin-right: 2rem;">

#### HydePHP Features

- [Autodiscovery](#autodiscovery)
- [Page Types](#page-types)
- [Page Identifiers](#page-identifiers)
- [Routes](#routes)
- [Route Keys](#route-keys)

</div>

</div>

[//]: # (Languages and Tools)

### HydePHP

HydePHP is a static site generator written in PHP, designed to make it easy for developers to build fast and secure websites.
It uses a simple directory structure and templating system to generate static websites from content files,
and can be easily extended using PHP libraries and packages.

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

### PHP

PHP is a popular server-side scripting language used for web development that can be embedded in HTML.
HydePHP is built on top of PHP and utilizes its powerful capabilities for generating static websites.

### HTML

HTML (Hypertext Markup Language) is the standard markup language used to create web pages and web applications.
HydePHP uses HTML to render the static websites generated from its content files and templates.

### Tailwind CSS

Tailwind CSS is a utility-first CSS framework used for rapidly building custom user interfaces.
HydePHP supports Tailwind CSS out of the box through the built-in Blade templates,
making it easy for developers to create beautiful and responsive websites without writing custom CSS.

### Composer

Composer is a dependency manager for PHP that simplifies the process of installing and managing packages required by
PHP applications. HydePHP uses Composer to manage its own dependencies and make it easy for users to install and use the software.

### Static Sites

A static website is a collection of HTML web pages that are delivered to the user's web browser exactly as they are stored
on the web server. HydePHP generates static websites, making them fast, secure, and easy to deploy.

### Version Control

HydePHP can be easily integrated with Git to manage website source files and track changes over time,
as one of the many benefits with static sites is that they are designed to be version controlled.

### Git

Git is a free and open-source distributed version control system that is widely used for software development.
Git repositories can be hosted on GitHub, GitLab, BitBucket, or any other Git hosting service.

[//]: # (HydePHP Features)

### Autodiscovery

Content files, including Markdown and Blade files, are automatically discovered and compiled to HTML during site builds.
During autodiscovery, Hyde also generates dynamic data to enrich your content based on the page type.

In short the autodiscovery is split into three steps:
`File discovery -> Page parsing -> Route generation`

### Page Types

All pages in HydePHP are internally represented by a page object that extends the HydePage class. Each page type has its
own page class which acts as a blueprint defining information for the framework to parse a file and generate relevant data.

### Page Identifiers

The page identifier is the name of the file without the file extension, relative to the page type's source directory.
The identifier is used to generate the route key, which is used to generate the file name for the compiled HTML file.

### Routes

All pages are internally bound to a Route object, through the route key. During the build process, each route is
compiled to HTML using the page object's data, and saved to the output directory with a file name created from the route key.
Since routes are generated automatically during autodiscovery, there is no need to create them manually.

### Route Keys

The route key is the URL path relative to the site webroot, without the file extension. The route key is the common
identifier binding Page objects to Route objects, and is used to generate the file name for the compiled HTML file.

Route keys generation can be visualised as follows: `<PageClass::OutputDirectory>/<PageIdentifier>`
