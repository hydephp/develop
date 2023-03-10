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

| Class Name | Namespace             | Source Code                                                                                         | API Docs                                                                                                     |
|------------|-----------------------|-----------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------|
| `HydePage` | `Hyde\Pages\Concerns` | [Open in GitHub](https://github.com/hydephp/framework/blob/master//src/Pages/Concerns/HydePage.php) | [Live API Docs](https://hydephp.github.io/develop/master/api-docs/classes/Hyde-Pages-Concerns-HydePage.html) |

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

#### `make()`

No description provided.

```php
// torchlight! {"lineNumbers": false}
HydePage::make(string $identifier, Hyde\Markdown\Models\FrontMatter|array $matter): static
```

#### `isDiscoverable()`

No description provided.

```php
// torchlight! {"lineNumbers": false}
HydePage::isDiscoverable(): bool
```

#### `get()`

Get a page instance from the Kernel's page index by its identifier.

```php
// torchlight! {"lineNumbers": false}
HydePage::get(string $identifier): Hyde\Pages\Concerns\HydePage
```

- **Throws:** \Hyde\Framework\Exceptions\FileNotFoundException If the page does not exist.

#### `parse()`

Parse a source file into a page model instance.

```php
// torchlight! {"lineNumbers": false}
HydePage::parse(string parse.): static New page model instance for the parsed source file.
```

- **Throws:** \Hyde\Framework\Exceptions\FileNotFoundException If the file does not exist.

#### `files()`

Get an array of all the source file identifiers for the model.

Note that the values do not include the source directory or file extension.

```php
// torchlight! {"lineNumbers": false}
HydePage::files(): array<string>
```

#### `all()`

Get a collection of all pages, parsed into page models.

```php
// torchlight! {"lineNumbers": false}
HydePage::all(): \Hyde\Foundation\Kernel\PageCollection<static>
```

#### `sourceDirectory()`

Get the directory in where source files are stored.

```php
// torchlight! {"lineNumbers": false}
HydePage::sourceDirectory(): string
```

#### `outputDirectory()`

Get the output subdirectory to store compiled HTML.

```php
// torchlight! {"lineNumbers": false}
HydePage::outputDirectory(): string
```

#### `fileExtension()`

Get the file extension of the source files.

```php
// torchlight! {"lineNumbers": false}
HydePage::fileExtension(): string
```

#### `setSourceDirectory()`

Set the output directory for the HydePage class.

```php
// torchlight! {"lineNumbers": false}
HydePage::setSourceDirectory(string $sourceDirectory): void
```

#### `setOutputDirectory()`

Set the source directory for the HydePage class.

```php
// torchlight! {"lineNumbers": false}
HydePage::setOutputDirectory(string $outputDirectory): void
```

#### `setFileExtension()`

Set the file extension for the HydePage class.

```php
// torchlight! {"lineNumbers": false}
HydePage::setFileExtension(string $fileExtension): void
```

#### `sourcePath()`

Qualify a page identifier into a local file path for the page source file relative to the project root.

```php
// torchlight! {"lineNumbers": false}
HydePage::sourcePath(string $identifier): string
```

#### `outputPath()`

Qualify a page identifier into a target output file path relative to the _site output directory.

```php
// torchlight! {"lineNumbers": false}
HydePage::outputPath(string $identifier): string
```

#### `path()`

Get an absolute file path to the page's source directory, or a file within it.

```php
// torchlight! {"lineNumbers": false}
HydePage::path(string $path): string
```

#### `pathToIdentifier()`

Format a filename to an identifier for a given model. Unlike the basename function, any nested paths within the source directory are retained in order to satisfy the page identifier definition.

```php
// torchlight! {"lineNumbers": false}
HydePage::pathToIdentifier(string index.blade.php): string Example: index
```

#### `baseRouteKey()`

Get the route key base for the page model.

```php
// torchlight! {"lineNumbers": false}
HydePage::baseRouteKey(): string
```

#### `__construct()`

No description provided.

```php
// torchlight! {"lineNumbers": false}
$page = new HydePage(string $identifier, Hyde\Markdown\Models\FrontMatter|array $matter): void
```

#### `compile()`

Compile the page into static HTML.

```php
// torchlight! {"lineNumbers": false}
$page->compile(): string The compiled HTML for the page.
```

#### `toArray()`

Get the instance as an array.

```php
// torchlight! {"lineNumbers": false}
$page->toArray(): array
```

#### `getSourcePath()`

Get the path to the instance source file, relative to the project root.

```php
// torchlight! {"lineNumbers": false}
$page->getSourcePath(): string
```

#### `getOutputPath()`

Get the path where the compiled page will be saved.

```php
// torchlight! {"lineNumbers": false}
$page->getOutputPath(): string Path relative to the site output directory.
```

#### `getRouteKey()`

Get the route key for the page.

The route key is the URL path relative to the site root.

For example, if the compiled page will be saved to _site/docs/index.html, then this method will return 'docs/index'. Route keys are used to identify pages, similar to how named routes work in Laravel, only that here the name is not just arbitrary, but also defines the output location.

```php
// torchlight! {"lineNumbers": false}
$page->getRouteKey(): string The page's route key.
```

#### `getRoute()`

Get the route for the page.

```php
// torchlight! {"lineNumbers": false}
$page->getRoute(): \Hyde\Support\Models\Route The page's route.
```

#### `getLink()`

Format the page instance to a URL path (relative to site root) with support for pretty URLs if enabled.

```php
// torchlight! {"lineNumbers": false}
$page->getLink(): string
```

#### `getIdentifier()`

Get the page model's identifier property.

The identifier is the part between the source directory and the file extension. It may also be known as a 'slug', or previously 'basename'.

For example, the identifier of a source file stored as '_pages/about/contact.md' would be 'about/contact', and 'pages/about.md' would simply be 'about'.

```php
// torchlight! {"lineNumbers": false}
$page->getIdentifier(): string The page's identifier.
```

#### `getBladeView()`

Get the Blade template for the page.

```php
// torchlight! {"lineNumbers": false}
$page->getBladeView(): string Blade template/view key.
```

#### `title()`

Get the page title to display in HTML tags like <title> and <meta> tags.

```php
// torchlight! {"lineNumbers": false}
$page->title(): string
```

#### `metadata()`

No description provided.

```php
// torchlight! {"lineNumbers": false}
$page->metadata(): Hyde\Framework\Features\Metadata\PageMetadataBag
```

#### `showInNavigation()`

No description provided.

```php
// torchlight! {"lineNumbers": false}
$page->showInNavigation(): bool
```

#### `navigationMenuPriority()`

No description provided.

```php
// torchlight! {"lineNumbers": false}
$page->navigationMenuPriority(): int
```

#### `navigationMenuLabel()`

No description provided.

```php
// torchlight! {"lineNumbers": false}
$page->navigationMenuLabel(): string
```

#### `navigationMenuGroup()`

No description provided.

```php
// torchlight! {"lineNumbers": false}
$page->navigationMenuGroup(): string
```
