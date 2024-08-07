<section id="hyde-page-methods">

<!-- Start generated docs for Hyde\Pages\Concerns\HydePage -->
<!-- Generated by HydePHP DocGen script at 2024-07-23 15:23:26 in 4.01ms -->

#### `make()`

Create a new page instance. Static alias for the constructor.

```php
HydePage::make(string $identifier, Hyde\Markdown\Models\FrontMatter|array $matter): static
```

#### `isDiscoverable()`

Returns whether the page type is discoverable through auto-discovery.

```php
HydePage::isDiscoverable(): bool
```

#### `get()`

Get a page instance from the Kernel&#039;s page index by its identifier.

```php
HydePage::get(string $identifier): static
```

- **Throws:** \Hyde\Framework\Exceptions\FileNotFoundException If the page does not exist.

#### `parse()`

Parse a source file into a new page model instance.

```php
/** @param string $identifier The identifier of the page to parse. */
HydePage::parse(string $identifier): static // New page model instance for the parsed source file.
```

- **Throws:** \Hyde\Framework\Exceptions\FileNotFoundException If the file does not exist.

#### `files()`

Get an array of all the source file identifiers for the model.

Note that the values do not include the source directory or file extension.

```php
HydePage::files(): array<string>
```

#### `all()`

Get a collection of all pages, parsed into page models.

```php
HydePage::all(): \Hyde\Foundation\Kernel\PageCollection<static>
```

#### `sourceDirectory()`

Get the directory where source files are stored for the page type.

```php
HydePage::sourceDirectory(): string
```

#### `outputDirectory()`

Get the output subdirectory to store compiled HTML files for the page type.

```php
HydePage::outputDirectory(): string
```

#### `fileExtension()`

Get the file extension of the source files for the page type.

```php
HydePage::fileExtension(): string
```

#### `setSourceDirectory()`

Set the output directory for the page type.

```php
HydePage::setSourceDirectory(string $sourceDirectory): void
```

#### `setOutputDirectory()`

Set the source directory for the page type.

```php
HydePage::setOutputDirectory(string $outputDirectory): void
```

#### `setFileExtension()`

Set the file extension for the page type.

```php
HydePage::setFileExtension(string $fileExtension): void
```

#### `sourcePath()`

Qualify a page identifier into file path to the source file, relative to the project root.

```php
HydePage::sourcePath(string $identifier): string
```

#### `outputPath()`

Qualify a page identifier into a target output file path, relative to the _site output directory.

```php
HydePage::outputPath(string $identifier): string
```

#### `path()`

Get an absolute file path to the page&#039;s source directory, or a file within it.

```php
HydePage::path(string $path): string
```

#### `pathToIdentifier()`

Format a filename to an identifier for a given model. Unlike the basename function, any nested paths within the source directory are retained in order to satisfy the page identifier definition.

```php
/** @param string $path Example: index.blade.php */
HydePage::pathToIdentifier(string $path): string // Example: index
```

#### `baseRouteKey()`

Get the route key base for the page model.

This is the same value as the output directory.

```php
HydePage::baseRouteKey(): string
```

#### `__construct()`

Construct a new page instance.

```php
$page = new HydePage(string $identifier, Hyde\Markdown\Models\FrontMatter|array $matter): void
```

#### `compile()`

Compile the page into static HTML.

```php
$page->compile(): string // The compiled HTML for the page.
```

#### `toArray()`

Get the instance as an array.

```php
$page->toArray(): array
```

#### `getSourcePath()`

Get the path to the instance source file, relative to the project root.

```php
$page->getSourcePath(): string
```

#### `getOutputPath()`

Get the path where the compiled page will be saved.

```php
$page->getOutputPath(): string // Path relative to the site output directory.
```

#### `getRouteKey()`

Get the route key for the page.

The route key is the page URL path, relative to the site root, but without any file extensions. For example, if the page will be saved to `_site/docs/index.html`, the key is `docs/index`.

Route keys are used to identify page routes, similar to how named routes work in Laravel, only that here the name is not just arbitrary, but also defines the output location, as the route key is used to determine the output path which is `$routeKey.html`.

```php
$page->getRouteKey(): string
```

#### `getRoute()`

Get the route object for the page.

```php
$page->getRoute(): Hyde\Support\Models\Route
```

#### `getLink()`

Format the page instance to a URL path, with support for pretty URLs if enabled.

Note that the link is always relative to site root, and does not contain `../` segments.

```php
$page->getLink(): string
```

#### `getIdentifier()`

Get the page model&#039;s identifier property.

The identifier is the part between the source directory and the file extension. It may also be known as a &#039;slug&#039;, or previously &#039;basename&#039;, but it retains the nested path structure if the page is stored in a subdirectory.

For example, the identifier of a source file stored as &#039;_pages/about/contact.md&#039; would be &#039;about/contact&#039;, and &#039;pages/about.md&#039; would simply be &#039;about&#039;.

```php
$page->getIdentifier(): string
```

#### `getBladeView()`

Get the Blade template/view key for the page.

```php
$page->getBladeView(): string
```

#### `title()`

Get the page title to display in HTML tags like `<title>` and `<meta>` tags.

```php
$page->title(): string
```

#### `metadata()`

Get the generated metadata for the page.

```php
$page->metadata(): Hyde\Framework\Features\Metadata\PageMetadataBag
```

#### `showInNavigation()`

Can the page be shown in the navigation menu?

```php
$page->showInNavigation(): bool
```

#### `navigationMenuPriority()`

Get the priority of the page in the navigation menu.

```php
$page->navigationMenuPriority(): int
```

#### `navigationMenuLabel()`

Get the label of the page in the navigation menu.

```php
$page->navigationMenuLabel(): string
```

#### `navigationMenuGroup()`

Get the group of the page in the navigation menu, if any.

```php
$page->navigationMenuGroup(): string
```

#### `getCanonicalUrl()`

Get the canonical URL for the page to use in the `<link rel=&quot;canonical&quot;>` tag.

It can be explicitly set in the front matter using the `canonicalUrl` key, otherwise it will be generated based on the site URL and the output path, unless there is no configured base URL, leading to this returning null.

```php
$page->getCanonicalUrl(): string
```

<!-- End generated docs for Hyde\Pages\Concerns\HydePage -->

</section>
