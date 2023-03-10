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

Unlike other frameworks, in general you don't instantiate pages yourself in Hyde, instead, the page models acts as
blueprints defining information for Hyde to know how to parse a file, and what data around it should be generated.

To create a parsed file instance, you'd typically just create a source file, and you can then access the parsed file
from the HydeKernel's page index.

In Blade views, you can always access the current page instance being rendered using the `$page` variable.

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

[Blade]: {{ Hyde\Markdown\Models\Markdown::fromFile(DocumentationPage::sourcePath('_data/partials/hyde-pages-api/interacts-with-front-matter-methods'))->toHtml($page::class) }}


## BaseMarkdownPage

The base class for all Markdown-based page models, with additional helpers tailored for Markdown pages.

### Quick Reference

| Class Name         | Namespace             | Source Code                                                                                                | API Docs                                                                                                             |
|--------------------|-----------------------|------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------|
| `BaseMarkdownPage` | `Hyde\Pages\Concerns` | [Open in GitHub](https://github.com/hydephp/framework/blob/master/src/Pages/Concerns/BaseMarkdownPage.php) | [Live API Docs](https://hydephp.github.io/develop/master/api-docs/classes/Hyde-Pages-Concerns-BaseMarkdownPage.html) |

### Base Structure

```php
/**
 * The base class for all Markdown-based page models. Here simplified for the sake of brevity.
 */
abstract class BaseMarkdownPage extends HydePage
{
    public Markdown $markdown;

    public static string $fileExtension = '.md';
}
```

### Methods

[Blade]: {{ Hyde\Markdown\Models\Markdown::fromFile(DocumentationPage::sourcePath('_data/partials/hyde-pages-api/base-markdown-page-methods'))->toHtml($page::class) }}


## InMemoryPage

Before we take a look at the common page classes, you'll usually use, let's first take a look at one that's quite interesting.

This class is especially useful for one-off custom pages. But if your usage grows, or if you want to utilize Hyde
autodiscovery, you may benefit from creating a custom page class instead, as that will give you full control.

You can learn more about the InMemoryPage class in the [InMemoryPage documentation](in-memory-pages).

### Quick Reference

| Class Name     | Namespace    | Source Code                                                                                   | API Docs                                                                                                |
|----------------|--------------|-----------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `InMemoryPage` | `Hyde\Pages` | [Open in GitHub](https://github.com/hydephp/framework/blob/master/src/Pages/InMemoryPage.php) | [Live API Docs](https://hydephp.github.io/develop/master/api-docs/classes/Hyde-Pages-InMemoryPage.html) |

### Base Structure

As the class is not discoverable, the static path properties are not initialized. Instead, you solely rely on the contents/view properties.

You can also define macros which allow you to both add methods to the instance, but also to overload some built-in ones like the `compile` method.

```php
/**
 * The InMemoryPage class, here simplified for the sake of brevity.
 */
class InMemoryPage extends HydePage
{
    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;

    protected string $contents;
    protected string $view;

    /** @var array<string, callable> */
    protected array $macros = [];
}
```

### Methods

[Blade]: {{ Hyde\Markdown\Models\Markdown::fromFile(DocumentationPage::sourcePath('_data/partials/hyde-pages-api/in-memory-page-methods'))->toHtml($page::class) }}


## BladePage

Page class for Blade pages.

Blade pages are stored in the _`pages` directory and using the `.blade.php` extension.
They will be compiled using the Laravel Blade engine the `_site/` directory.

### Quick Reference

| Class Name  | Namespace    | Source Code                                                                                | API Docs                                                                                             |
|-------------|--------------|--------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------|
| `BladePage` | `Hyde\Pages` | [Open in GitHub](https://github.com/hydephp/framework/blob/master/src/Pages/BladePage.php) | [Live API Docs](https://hydephp.github.io/develop/master/api-docs/classes/Hyde-Pages-BladePage.html) |

### Base Structure

```php
class BladePage extends HydePage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $fileExtension = '.blade.php';
}
```

### Methods

[Blade]: {{ Hyde\Markdown\Models\Markdown::fromFile(DocumentationPage::sourcePath('_data/partials/hyde-pages-api/blade-page-methods'))->toHtml($page::class) }}

## MarkdownPage

Page class for Markdown pages.

Markdown pages are stored in the _`pages` directory and using the `.md` extension.
The Markdown will be compiled to HTML using a minimalistic layout to the `_site/` directory.

### Quick Reference

| Class Name     | Namespace    | Source Code                                                                                   | API Docs                                                                                                |
|----------------|--------------|-----------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `MarkdownPage` | `Hyde\Pages` | [Open in GitHub](https://github.com/hydephp/framework/blob/master/src/Pages/MarkdownPage.php) | [Live API Docs](https://hydephp.github.io/develop/master/api-docs/classes/Hyde-Pages-MarkdownPage.html) |

### Base Structure

```php
class MarkdownPage extends BaseMarkdownPage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $template = 'hyde::layouts/page';
}
```

### Methods

This class does not define any additional methods.

## MarkdownPost

Page class for Markdown blog posts.

Markdown posts are stored in the `_posts` directory and using the `.md` extension.
The Markdown will be compiled to HTML using the blog post layout to the `_site/posts/` directory.

### Quick Reference

| Class Name     | Namespace    | Source Code                                                                                   | API Docs                                                                                                |
|----------------|--------------|-----------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `MarkdownPost` | `Hyde\Pages` | [Open in GitHub](https://github.com/hydephp/framework/blob/master/src/Pages/MarkdownPost.php) | [Live API Docs](https://hydephp.github.io/develop/master/api-docs/classes/Hyde-Pages-MarkdownPost.html) |
