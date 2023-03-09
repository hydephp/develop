---
navigation:
    label: "HydePage API"
---

# HydePage API Reference

>warning This article covers advanced information, and you are expected to already be familiar with the [Page Models](page-models).


## Abstract

This page contains the full API references for the built-in HydePage classes. Most users will not need to know about
the inner workings of classes, but if you're interested in extending HydePHP, or just curious, this page is for you.
It is especially useful if you're looking to implement your own page classes, or if you are creating advanced Blade templates.

### Table of Contents

| Class                                   | Description                            |
|-----------------------------------------|----------------------------------------|
| [HydePage](#hydepage)                   | The base class for all Hyde pages.     |
| [BaseMarkdownPage](#basemarkdownpage)   | The base class for all Markdown pages. |
| [InMemoryPage](#inmemorypage)           | Extendable class for in-memory pages.  |
| [BladePage](#markdownpage)              | Class for Blade pages.                 |
| [MarkdownPage](#markdownpage)           | Class for Markdown pages.              |
| [MarkdownPost](#markdownpost)           | Class for Markdown posts.              |
| [DocumentationPage](#documentationpage) | Class for documentation pages.         |
| [HtmlPage](#htmlpage)                   | Class for HTML pages.                  |

## HydePage

The base class for all Hyde pages, all other page classes extend this class.

### Base Structure

```php
/**
 * The base class for all Hyde pages. Here simplified for the sake of brevity.
 */
abstract class HydePage
{    /**
     * The directory in which source files are stored. Relative to the project root.
     */
    public static string $sourceDirectory;

    /**
     * The output subdirectory to store compiled HTML. Relative to the _site output directory.
     */
    public static string $outputDirectory;

    /**
     * The file extension of the source files.
     */
    public static string $fileExtension;

    /**
     * The default template to use for rendering the page.
     */
    public static string $template;

    /**
     * The page instance identifier.
     */
    public readonly string $identifier;

    /**
     * The page instance route key.
     */
    public readonly string $routeKey;

    /**
     * The parsed front matter.
     */
    public FrontMatter $matter;

    /**
     * The generated page metadata.
     */
    public PageMetadataBag $metadata;
    
    /**
     * The generated page navigation data.
     */
    public NavigationData $navigation;

    public string $title;
    public ?string $canonicalUrl;
}
```
