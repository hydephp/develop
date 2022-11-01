---
title: Page models
---
# The Hyde Page Models

## Introduction

The Hyde page models are an integral part of how HydePHP creates your static site. Each page in your site is represented
by a page model. These are simply PHP classes that in addition to holding both the source content and computed data
for your pages, also houses instructions to Hyde on how to parse, process, and render the pages to static HTML.

In this article, you'll get a high-level overview of the page models, and some code examples to give you a look inside.

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
