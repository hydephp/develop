---
title: Page models
---
# The Hyde Page Models

## Introduction

The Hyde page models are an integral part of how HydePHP creates your static site. Each page in your site is represented
by a page model. These are simply PHP classes that in addition to holding both the source content and computed data
for your pages, also houses instructions to Hyde on how to parse, process, and render the pages to static HTML.

In this article, you'll get a high-level overview of the page models, and some code examples to give you a look inside.
As all good things <sup>(apparently)</sup> comes in threes, we will break down the responsibilities and usages of
the important parts.

### The short version

In short, page models are classes that acts as blueprints containing instructions for how to parse, process,
and render a page, in the form of static properties. Each class instance also holds the source data and computed data for the page.

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

## The Three Data Categories and Usages

Each supported page type in Hyde has a page model class. At the time of writing, there are five of those.
For this article, we'll use the `BladePage` and `MarkdownPost` classes as examples. In this section we'll take a look
the data housed within these classes, and how Hyde uses them.

### Page Blueprint Data

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

