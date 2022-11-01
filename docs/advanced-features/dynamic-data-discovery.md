# Dynamic Data Discovery

[//]: # (Adds a pseudo-subtitle)
<h3 style="margin-top: 0px; margin-bottom: 20px;"><i>AKA: Front Matter & Filling in the Gaps</i></h3>

## Introduction

Hyde wants to allow developers to write less, and do more. This is also a major difference between HydePHP and JekyllRB.
Jekyll will only do what you _tell it to do_. Hyde, on the other hand, will try to do what you _want it to do_.

### Standard disclaimer
As with all other chapters in this category, you don't need to know about this to use Hyde -- that's the whole point!
However, if you're anything like me, you'll likely find this interesting to read about, even if you don't really need to know it.


Hyde makes great use of front matter in both Markdown and Blade files (it's true!). However, it can quickly get tedious
and quite frankly plain boring to have to write a bunch of front matter all the time. As Hyde wants you to focus on
your content, and not your markup, front matter is optional and Hyde will try to fill in the gaps for you.

If you're not happy with Hyde's generated data you can always override it by adding front matter to your files.

## How it Works

Now, to the fun part: getting into the nitty-gritty details of how Hyde does this!

To make things simple the dynamic data is created in a special stage where the page object is being created.
If you have not yet read the [page models chapter](page-models) you might want to do so now.
You might also want to read about the [autodiscovery lifecycle](autodiscovery) for some context as to when this happens.

### The factory pipeline, in short

After basic information about the page has been gathered, such as the source file information and the front matter,
the page model is run through a series of factories. These are just classes that work around the limited data
that is available at this point, and will assign the rich data needed to make your Hyde page awesome.

There are a few factory classes. The one we will be looking at here is the `HydePageDataFactory` class, which is
responsible for data applicable to all page models. Complex structures and data only relevant to some page types
have their own factories, making the code more modular and maintainable.

## In-depth overview of a page factory 

Let's take a look at how Hyde will discover the title of a page as an example. Since this is something used by all pages,
this discovery is done in the `HydePageDataFactory` class.

### Factory data input

The factory gets one input, a `CoreDataObject` class. Think of this like a DTO (Data Transfer Object) that holds
immutable data known from the start of the page construction process. It also has all the information needed
to identify the page and its source file. Here's a simplified version of the class:

```php
class CoreDataObject
{
    public readonly FrontMatter $matter;
    public readonly Markdown|false $markdown;

    public readonly string $pageClass;
    public readonly string $identifier;
    public readonly string $sourcePath;
    public readonly string $outputPath;
    public readonly string $routeKey;
}
```

### Processing the known data

Now that we have the input we pass it to the factory, where a simple algorithm is used to find the best title for the page.

I'm a big fan of using a lot of helper methods to describe my code, so let's first take a look at the entry-point
method that is responsible for outputting the data, then we'll take a closer look at the helper methods.

```php
private function findTitleForPage(): string
{
    return $this->matter('title')
        ?? $this->findTitleFromMarkdownHeadings()
        ?? Hyde::makeTitle(basename($this->identifier));
}
```

As you can see, we are using the null coalescing operator (`??`) to return the first non-null value. We always want the
user to be able to set any data explicitly, so we first check the front matter in all factory methods.

If no title is set in the matter the method will return null, and Hyde will try the next step which is to search the headings.
If that fails, the last step will generate a title from the file name. This ensures that no matter what, we always have a title.
