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


### About the reference

This document is heavily based around the actual source code, as I believe the best way to understand the code is to read it.
However, large parts of the code are simplified for brevity and illustration. The code is not meant to be copy-pasted, but
rather used as a reference so that you know what to look for in the actual source code, if you want to dig deeper.

#### Inheritance

Since all HydePages extend the base `HydePage` class, those shared methods are only listed once,
under the `HydePage` class documentation which is conveniently located just below this section.


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

### Quick Reference

| Class Name | Namespace             | Source Code                                                                                        | API Docs                                                                                                     |
|------------|-----------------------|----------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------|
| `HydePage` | `Hyde\Pages\Concerns` | [Open in GitHub](https://github.com/hydephp/framework/blob/master/src/Pages/Concerns/HydePage.php) | [Live API Docs](https://hydephp.github.io/develop/master/api-docs/classes/Hyde-Pages-Concerns-HydePage.html) |

### Base Structure

```php
/**
 * The base class for all Hyde pages. Here simplified for the sake of brevity.
 */
abstract class HydePage
{
    /**
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
}
```

### Methods

>warning <p class="p-2" style="padding-right: 1rem"><strong>Heads up!</strong> The following methods are defined in the <code>HydePage</code> class, and are thus available to all page classes. Since the HydePage class is abstract, you cannot instantiate it directly, and many of the static methods are also only callable from the child classes.</p>

[Blade]: {{ Hyde\Markdown\Models\Markdown::fromFile(DocumentationPage::sourcePath('_data/partials/hyde-pages-api/hyde-page-methods'))->toHtml($page::class) }} 


## BaseMarkdownPage

The base class for all Markdown-based page models, with additional helpers tailored for Markdown pages.

