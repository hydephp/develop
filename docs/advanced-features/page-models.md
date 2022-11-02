---
title: Page models
---
# The Hyde Page Models

## Introduction

The Hyde page models are an integral part of how HydePHP creates your static site. Each page in your site is represented
by a page model. These are simply PHP classes that in addition to holding both the source content and computed data
for your pages, also houses instructions to Hyde on how to parse, process, and render the pages to static HTML.

In this article, you'll get a high-level overview of the page models, and some code examples to give you a look inside.

## The short version

#### Page models are classes that have two primary concerns:

1. They act as blueprints containing _static_ instructions for how to parse, process, and, render pages.
2. Each class _instance_ also holds the page source contents, as well as the computed data.

#### Other key points:

- HydePHP, at the time of writing, comes with five different page classes, one for each supported type.
- You don't construct page models yourself. HydePHP does it for you by the [autodiscovery process](autodiscovery).
- Page models are just PHP classes. You can extend them, and you can implement your own.

## The Page Model

To give you an idea of what a page model class looks like, here's a simplified version of the base `MarkdownPost` class,
Don't worry if you don't understand everything yet, we'll talk more about these parts later.

```php
class MarkdownPost extends BaseMarkdownPage
{
    public static string $sourceDirectory = '_posts';
    public static string $outputDirectory = 'posts';
    public static string $fileExtension = '.md';
    public static string $template = 'post';
    
    public string $identifier;
    public string $routeKey;
    public string $title;
    
    public FrontMatter $matter;
    public Markdown $markdown;
}
```

_Note that since Hyde pages are modular and class inheritance and traits, this example has been simplified and
edited to show all the relevant parts inlined into one class._

## Page Models as Blueprints

All page models have some static properties (that is, they belong to the class, not the instance) that are used as
blueprints, defining information for Hyde to know how to parse a file, and what data around it should be generated.

Let's again take the simplified `MarkdownPost` class as an example, this time only showing the static properties:

```php
class MarkdownPost extends BaseMarkdownPage
{
    public static string $sourceDirectory = '_posts';
    public static string $outputDirectory = 'posts';
    public static string $fileExtension = '.md';
    public static string $template = 'post';
}
```

#### What each property does

The properties should be self-explanatory, but here's a quick rundown to give some context how they are used,
and how the paths relate to each other. So for the class above, Hyde will thanks to this blueprint know to:
* Look for files in the `_posts` directory, with the `.md` extension
* Compile the page using the `post` Blade template
* Output the compiled page to the `_site/posts` directory

## Page Models as Data Containers

As mentioned above, each page model instance also holds the page source contents, as well as the computed data.

Let's again take the simplified `MarkdownPost` class as an example, this time only showing the instance properties:

```php
class MarkdownPost extends BaseMarkdownPage
{
    public string $identifier;
    public string $routeKey;
    public string $title;
    
    public FrontMatter $matter;
    public Markdown $markdown;
}
```

There are some more properties than shown here, for example various metadata properties, but these are the most common
and important ones.

While the static data gives instructions to Hyde on how to process all pages of the type, the instance data tells Hyde
how to process a specific page. For example, the identifier property is used to uniquely identify the page, and
the routeKey property is used to generate the URL for the page.

The matter and markdown properties as I'm sure you can guess, hold the page's front matter and markdown content.
These can then also be processed by [page factories](dynamic-data-discovery.md) to generate the computed data like the
title property.
