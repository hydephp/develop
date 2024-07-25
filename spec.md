# Asset API Overview

## Abstract

In order to rework the Asset API, we need to analyze the current state of the API, to find what can be improved.

### Background

Remember the original problem: The current system is confusing and unintuitive with multiple and inconsistent ways to access assets.

Additionally, keep in mind that most ways users will commonly use this API is to link to assets in the `_media` directory, and to check if they exist.

For example: Consider these Blade snippets from the default views, showing common usages that actual end users may use:

```blade
{{-- The compiled Tailwind/App styles --}}
@if(config('hyde.load_app_styles_from_cdn', false))
    <link rel="stylesheet" href="{{ Asset::cdnLink('app.css') }}">
@elseif(Asset::hasMediaFile('app.css'))
    <link rel="stylesheet" href="{{ Asset::mediaLink('app.css') }}">
@endif

{{-- The compiled Laravel Mix scripts --}}
@if(Asset::hasMediaFile('app.js'))
    <script defer src="{{ Asset::mediaLink('app.js') }}"></script>
@endif

@if (file_exists(Hyde::mediaPath('favicon.ico')))
    <link rel="shortcut icon" href="{{ Hyde::relativeLink('media/favicon.ico') }}" type="image/x-icon">
@endif
```

Some other example usages could be things like:

```blade
<img src="{{ asset('images/logo.png') }}" alt="Logo">
```

We also use the Asset API helpers in the internal codebase that actually powers the static site generator. These kinds of usages would usually only be used by us, package developers, and serious power users. They have an important, but secondary role in the API design. Here are some examples from our codebase:

```php
// Code that transfers media assets from the source directory to the site output directory
$this->createDirectoryifItDoesNotExist(Hyde::siteMediaPath());
$this->mapWithProgressBar(MediaFile::files(), function (string $identifier): void {
   copy(Hyde::mediaPath($identifier), Hyde::siteMediaPath($identifier));
});

// Helpers in the featured image class
public function getSource(): string {
    return Hyde::mediaLink($this->source);
}

protected function setSource(string $source): string {
   // Normalize away any leading media path prefixes.
   return Str::after($source, Hyde::getMediaDirectory().'/');
}
```

## Current State

### Asset Facade

```php
Asset::cdnLink(string $file) // Gets remote URL to any file in /dist/ in the HydeFront version
Asset::mediaLink(string $file) // Returns Hyde::mediaLink but with a cache buster
Asset::hasMediaFile(string $file) // Returns file_exists(Hyde::mediaPath($file))

/**
 * Real implementations of the facade methods:
 * 
 * @see \Hyde\Framework\Services\AssetService::cdnLink
 * @see \Hyde\Framework\Services\AssetService::mediaLink
 * @see \Hyde\Framework\Services\AssetService::hasMediaFile
 */
```

### Hyde Facade

```php
Hyde::mediaPath() // Get the absolute path to the media source directory, or a file within it. (Intended as a sibling to Hyde::path and Hyde::sitePath helpers, and the HydePage::path methods)
Hyde::mediaLink() // Gets a relative web link to the given file stored in the _site/media folder. (Intended as a sibling to Hyde::relativeLink, which is called by this) (A second $validate parameter will return throw an exception if the file does not exist)
Hyde::asset() // Gets a relative web link to the given image stored in the _site/media folder. (But leaves remote URLs alone) (A second $preferQualifiedUrl parameter will return a fully qualified URL if a site URL is set)
Hyde::siteMediaPath() //  Get the absolute path to the compiled site's media directory, or a file within it. (Intended as a sibling to Hyde::sitePath)

/**
 * Real implementations of the facade methods:
 * 
 * @see \Hyde\Foundation\HydeKernel::mediaPath {@see \Hyde\Foundation\Kernel\Filesystem::mediaPath}
 * @see \Hyde\Foundation\HydeKernel::mediaLink {@see \Hyde\Foundation\Kernel\Hyperlinks::mediaLink}
 * @see \Hyde\Foundation\HydeKernel::asset {@see \Hyde\Foundation\Kernel\Hyperlinks::asset}
 * @see \Hyde\Foundation\HydeKernel::siteMediaPath {@see \Hyde\Foundation\Kernel\Filesystem::siteMediaPath}
 */
```

### Helper Functions

```php
asset() // Calls Hyde::asset 
```

## AI Context

Please provide feedback on how we for HydePHP v2 can improve the Asset API, designed to make it easy to interact with Asset files stored in the `_media` directory.

For context: This is for static site generator HydePHP, based on Laravel.

Motto: "Simplicity first. Power when you need it. Quality always."
Tagline: Create static websites with the tools you already know and love with HydePHP.

The Hyde Philosophy:
> Developing Hyde sites, and contributing to the framework should be a joy. Great Developer Experience (DX) is our top priority.
> Code should make sense and be intuitive, especially user-facing APIs. Convention over configuration, but not at the expense of flexibility.
> Making sites should not be boring and repetitive - Hyde is all about taking focus away from boilerplate, and letting users focus on the content.

Considerations: While HydePHP targets developers, not all users necessarily are familiar with Laravel, or PHP. So while we want to provide a familiar interface for Laravel/PHP users, we also want to make sure that the API is intuitive and easy to use for all users.

For additional context on the naming conventions, here is the entire Hyde facade methods:

```php
 * @method static string path(string $path = '')
 * @method static string vendorPath(string $path = '', string $package = 'framework')
 * @method static string pathToAbsolute(string $path)
 * @method static string pathToRelative(string $path)
 * @method static string sitePath(string $path = '')
 * @method static string mediaPath(string $path = '')
 * @method static string siteMediaPath(string $path = '')
 * @method static string formatLink(string $destination)
 * @method static string relativeLink(string $destination)
 * @method static string mediaLink(string $destination, bool $validate = false)
 * @method static string asset(string $name, bool $preferQualifiedUrl = false)
 * @method static string url(string $path = '')
 * @method static Route|null route(string $key)
 * @method static string makeTitle(string $value)
 * @method static string normalizeNewlines(string $string)
 * @method static string stripNewlines(string $string)
 * @method static string trimSlashes(string $string)
 * @method static HtmlString markdown(string $text, bool $stripIndentation = false)
 * @method static string currentPage()
 * @method static string currentRouteKey()
 * @method static string getBasePath()
 * @method static string getSourceRoot()
 * @method static string getOutputDirectory()
 * @method static string getMediaDirectory()
 * @method static string getMediaOutputDirectory()
 * @method static Features features()
 * @method static Collection<string, PostAuthor> authors()
 * @method static FileCollection files()
 * @method static PageCollection pages()
 * @method static RouteCollection routes()
 * @method static Route|null currentRoute()
 * @method static HydeKernel getInstance()
 * @method static Filesystem filesystem()
 * @method static array getRegisteredExtensions()
 * @method static bool hasFeature(Feature $feature)
 * @method static bool hasSiteUrl()
 * @method static void setInstance(HydeKernel $instance)
 * @method static void setBasePath(string $basePath)
 * @method static void setOutputDirectory(string $outputDirectory)
 * @method static void setMediaDirectory(string $mediaDirectory)
 * @method static void setSourceRoot(string $sourceRoot)
 * @method static void shareViewData(HydePage $page)
 * @method static array toArray()
 * @method static bool isBooted()
 * @method static void boot()
```

We also have a MediaFile class that looks like this, maybe we can unify this as well:

```php
/**
 * File abstraction for a project media file.
 */
class MediaFile extends ProjectFile
{
    /** @var array<string> The default extensions for media types */
    final public const EXTENSIONS = ['png', 'svg', 'jpg', 'jpeg', 'gif', 'ico', 'css', 'js'];

    /** @return array<string, \Hyde\Support\Filesystem\MediaFile> The array keys are the filenames relative to the _media/ directory */
    public static function all(): array
    {
        return static::discoverMediaAssetFiles();
    }

    /** @return array<string> Array of filenames relative to the _media/ directory */
    public static function files(): array
    {
        return array_keys(static::all());
    }

    public function getIdentifier(): string
    {
        return Str::after($this->getPath(), Hyde::getMediaDirectory().'/');
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'length' => $this->getContentLength(),
            'mimeType' => $this->getMimeType(),
        ]);
    }

    public function getContentLength(): int
    {
        if (! is_file($this->getAbsolutePath())) {
            throw new FileNotFoundException($this->path);
        }

        return filesize($this->getAbsolutePath());
    }

    public function getMimeType(): string
    {
        return mime_content_type($this->getAbsolutePath());
    }

    protected static function discoverMediaAssetFiles(): array
    {
        return collect(static::getMediaAssetFiles())->mapWithKeys(function (string $path): array {
            $file = static::make($path);

            return [$file->getIdentifier() => $file];
        })->all();
    }

    protected static function getMediaAssetFiles(): array
    {
        return glob(Hyde::path(static::getMediaGlobPattern()), GLOB_BRACE) ?: [];
    }

    protected static function getMediaGlobPattern(): string
    {
        return sprintf(Hyde::getMediaDirectory().'/{*,**/*,**/*/*}.{%s}', implode(',',
            Config::getArray('hyde.media_extensions', self::EXTENSIONS)
        ));
    }
}
```

## Proposals

### Fluent Asset API

Author: Claude 3.5 Sonnet
Link: https://claude.ai/chat/3b4529e1-79f5-48b9-ac8f-d35fa705a377

Based on the provided information and the goals of HydePHP, here are some suggestions to improve the Asset API for version 2:

1. Consolidate and simplify the API:
   The current API is spread across multiple facades (Asset, Hyde) and helper functions. To improve clarity and ease of use, consider consolidating these into a single, intuitive API.



```php
class Asset
{
    // Get the absolute path to a media file
    public static function path(string $file = ''): string
    {
        // Implementation
    }

    // Get a relative web link to a media file
    public static function link(string $file, bool $validate = false): string
    {
        // Implementation
    }

    // Get a CDN link for a file
    public static function cdnLink(string $file): string
    {
        // Implementation
    }

    // Check if a media file exists
    public static function exists(string $file): bool
    {
        // Implementation
    }

    // Get all media files
    public static function all(): array
    {
        // Implementation using MediaFile::all()
    }

    // Get a MediaFile instance
    public static function file(string $file): MediaFile
    {
        // Implementation
    }
}

```

This consolidated API provides a cleaner, more intuitive interface for working with assets. It combines functionality from the current Asset and Hyde facades, making it easier for developers to find and use the methods they need.

2. Improve method naming:
   Some current method names like `cdnLink` and `mediaLink` might be confusing. Consider using more descriptive names that clearly indicate their purpose.

3. Consistent return types:
   Ensure that all methods have consistent return types. For example, always return strings for paths and links, and use type hinting to make the API more predictable.

4. Fluent interface:
   Consider implementing a fluent interface for chaining operations on assets. This can make the API more flexible and expressive.



```php
class AssetFile
{
    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function path(): string
    {
        // Implementation
    }

    public function link(bool $validate = false): string
    {
        // Implementation
    }

    public function cdnLink(): string
    {
        // Implementation
    }

    public function exists(): bool
    {
        // Implementation
    }

    public function content(): string
    {
        // Implementation
    }
}

class Asset
{
    public static function file(string $file): AssetFile
    {
        return new AssetFile($file);
    }

    // Other static methods...
}

// Usage example
$assetUrl = Asset::file('image.jpg')->cdnLink();
$assetContent = Asset::file('styles.css')->content();

```

5. Better integration with MediaFile:
   Leverage the existing MediaFile class more prominently in the API. This can provide a richer set of operations on individual files.

6. Consistent error handling:
   Implement a consistent approach to error handling. Consider using custom exceptions that provide clear, actionable error messages.

7. Caching:
   Implement intelligent caching for asset operations to improve performance, especially for repeated operations.

8. Configuration:
   Provide a clear way to configure asset-related settings, such as CDN URLs or custom media directories, through a dedicated configuration file or section.

These improvements aim to create a more intuitive, powerful, and flexible Asset API that aligns with HydePHP's philosophy and goals. The consolidated and fluent interfaces should make it easier for both Laravel-familiar and new users to work with assets effectively.

### Fluent Asset API (Refined)

Author: Claude 3.5 Sonnet
Link: https://claude.ai/chat/3b4529e1-79f5-48b9-ac8f-d35fa705a377

Thank you for providing those additional considerations and the detailed API reference. Let's refine the proposal based on this new information. We'll focus on merging with MediaFiles, adjusting for the limited CDN use case, and aligning with the existing Hyde page classes API structure.



```php
abstract class Asset
{
    protected string $identifier;
    protected string $sourceDirectory = '_media';
    protected string $outputDirectory = 'media';
    protected string $fileExtension;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
        $this->fileExtension = pathinfo($identifier, PATHINFO_EXTENSION);
    }

    public static function make(string $identifier): static
    {
        return new static($identifier);
    }

    public static function isDiscoverable(): bool
    {
        return true;
    }

    public static function get(string $identifier): static
    {
        if (!static::exists($identifier)) {
            throw new FileNotFoundException("Asset not found: {$identifier}");
        }
        return new static($identifier);
    }

    public static function parse(string $identifier): static
    {
        return static::get($identifier);
    }

    public static function files(): array
    {
        // Implementation to get all asset file identifiers
    }

    public static function all(): AssetCollection
    {
        // Implementation to get all assets as a collection
    }

    public static function sourceDirectory(): string
    {
        return (new static(''))->sourceDirectory;
    }

    public static function outputDirectory(): string
    {
        return (new static(''))->outputDirectory;
    }

    public static function setSourceDirectory(string $sourceDirectory): void
    {
        (new static(''))->sourceDirectory = $sourceDirectory;
    }

    public static function setOutputDirectory(string $outputDirectory): void
    {
        (new static(''))->outputDirectory = $outputDirectory;
    }

    public function sourcePath(): string
    {
        return Hyde::path($this->sourceDirectory . '/' . $this->identifier);
    }

    public function outputPath(): string
    {
        return Hyde::sitePath($this->outputDirectory . '/' . $this->identifier);
    }

    public static function exists(string $identifier): bool
    {
        return file_exists((new static($identifier))->sourcePath());
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLink(): string
    {
        return '/' . $this->outputDirectory . '/' . $this->identifier;
    }

    public function getContents(): string
    {
        return file_get_contents($this->sourcePath());
    }

    public function getMimeType(): string
    {
        return mime_content_type($this->sourcePath());
    }

    public function getSize(): int
    {
        return filesize($this->sourcePath());
    }

    public function copy(): bool
    {
        return copy($this->sourcePath(), $this->outputPath());
    }

    // HydeFront specific method
    public static function cdnLink(string $file): string
    {
        // Implementation for HydeFront CDN links
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->getIdentifier(),
            'sourcePath' => $this->sourcePath(),
            'outputPath' => $this->outputPath(),
            'link' => $this->getLink(),
            'mimeType' => $this->getMimeType(),
            'size' => $this->getSize(),
        ];
    }
}

class AssetCollection extends Collection
{
    // Implement any asset-specific collection methods here
}

```

This refined Asset API proposal addresses the considerations you mentioned:

1. Merging with MediaFiles:
   The new `Asset` class incorporates functionality from both the current Asset facade and the MediaFile class. It provides methods for working with individual assets as well as collections of assets.

2. CDN links:
   The `cdnLink` method is retained but as a static method, reflecting its specific use for HydeFront files. This keeps the functionality available without overemphasizing its importance for general asset handling.

3. Alignment with Hyde page classes:
   The API structure closely follows the pattern established by the Hyde page classes. It includes similar static methods for file discovery and management, as well as instance methods for working with individual assets.

Key improvements and features:

1. Consistent API:
   The new API provides a consistent interface for working with assets, following patterns familiar to users of other Hyde classes.

2. Flexible file handling:
   Methods like `sourcePath()`, `outputPath()`, `getLink()`, and `copy()` provide flexible ways to work with asset files throughout the build process.

3. Asset information:
   Methods like `getMimeType()`, `getSize()`, and `getContents()` provide easy access to important asset information.

4. Collection support:
   The `AssetCollection` class allows for easy management and manipulation of multiple assets.

5. Extensibility:
   As an abstract class, `Asset` can be extended for specific asset types if needed in the future.

Usage examples:

```php
// Get all assets
$assets = Asset::all();

// Get a specific asset
$logo = Asset::get('images/logo.png');

// Get the web-accessible link to an asset
$logoUrl = $logo->getLink();

// Copy an asset to the output directory
$logo->copy();

// Get asset information
$size = $logo->getSize();
$mimeType = $logo->getMimeType();

// HydeFront CDN link (for app.js or app.css)
$appJsUrl = Asset::cdnLink('app.js');
```

This API maintains the simplicity-first approach of Hyde while providing power when needed. It should be intuitive for both Laravel-familiar developers and those new to the framework, aligning well with Hyde's philosophy and goals.

### Unified Asset API

Author: Claude 3.5 Sonnet
Link: https://claude.ai/chat/9c1c98cc-db92-41cc-9a8e-1f51fd9a04d5

Certainly, I'd be happy to help you improve the Asset API for HydePHP v2. Based on your requirements and the HydePHP philosophy, I've come up with a proposal for a new, more unified Asset API. Here's the proposed new API and implementation plan:

```php
<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Contracts\AssetContract;
use Hyde\Framework\Services\AssetService;

/**
 * @method static string get(string $path, bool $absolute = false)
 * @method static string url(string $path)
 * @method static bool exists(string $path)
 * @method static int size(string $path)
 * @method static string mime(string $path)
 * @method static string|null hash(string $path)
 * @method static AssetContract instance(string $path)
 */
class Asset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AssetService::class;
    }
}

// AssetService.php
class AssetService implements AssetContract
{
    protected $validateExistence;

    public function __construct(bool $validateExistence = false)
    {
        $this->validateExistence = $validateExistence;
    }

    public function get(string $path, bool $absolute = false): string
    {
        if ($this->validateExistence && !$this->exists($path)) {
            throw new AssetNotFoundException("Asset not found: {$path}");
        }

        return $absolute ? $this->getAbsolutePath($path) : $this->getRelativePath($path);
    }

    public function url(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Hyde::url($this->get($path));
    }

    public function exists(string $path): bool
    {
        return file_exists($this->getAbsolutePath($path));
    }

    public function size(string $path): int
    {
        return filesize($this->getAbsolutePath($path));
    }

    public function mime(string $path): string
    {
        return mime_content_type($this->getAbsolutePath($path));
    }

    public function hash(string $path): ?string
    {
        $file = $this->getAbsolutePath($path);
        return file_exists($file) ? md5_file($file) : null;
    }

    public function instance(string $path): AssetContract
    {
        return new Asset($path);
    }

    protected function getAbsolutePath(string $path): string
    {
        return Hyde::mediaPath($path);
    }

    protected function getRelativePath(string $path): string
    {
        return Hyde::mediaLink($path);
    }
}

// Asset.php (implements AssetContract)
class Asset implements AssetContract
{
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function get(bool $absolute = false): string
    {
        return app(AssetService::class)->get($this->path, $absolute);
    }

    public function url(): string
    {
        return app(AssetService::class)->url($this->path);
    }

    public function exists(): bool
    {
        return app(AssetService::class)->exists($this->path);
    }

    public function size(): int
    {
        return app(AssetService::class)->size($this->path);
    }

    public function mime(): string
    {
        return app(AssetService::class)->mime($this->path);
    }

    public function hash(): ?string
    {
        return app(AssetService::class)->hash($this->path);
    }
}

// Helper function
function asset(string $path, bool $absolute = false): string
{
    return Asset::get($path, $absolute);
}

```

This new API proposal aims to simplify and unify the asset-related functions while maintaining flexibility and adhering to the HydePHP philosophy. Here's a breakdown of the changes and the implementation plan:

1. Unified API:
   - All asset-related functions are now under the `Asset` facade.
   - The `get()` method replaces both `mediaLink()` and `asset()`, with an optional parameter for absolute paths.
   - Added `url()` method to handle both local and remote URLs.
   - Included utility methods like `exists()`, `size()`, `mime()`, and `hash()` for common operations.

2. Validation:
   - Instead of a parameter to validate, we've added a constructor parameter in the `AssetService` that can be set via configuration or environment variable.

3. Flexibility:
   - The `instance()` method allows for object-oriented usage when needed.

Implementation Plan:

1. Deprecation process in v1:
   - Add deprecation notices to the existing methods in the `Asset` and `Hyde` facades.
   - Introduce the new `Asset` facade with the proposed methods, marking them as `@since 1.x`.
   - Update the documentation to recommend using the new `Asset` facade.

2. Implementation in v2:
   - Remove the deprecated methods from the `Asset` and `Hyde` facades.
   - Fully implement the new `AssetService` and `Asset` classes.
   - Update all internal usage to use the new API.

3. Configuration:
   - Add a new configuration option in `config/hyde.php`:
     ```php
     'assets' => [
         'validate_existence' => env('HYDE_VALIDATE_ASSETS', false),
     ],
     ```

4. Release Notes:
   ```markdown
   # HydePHP v2.0 Release Notes

   ## Asset API Overhaul

   We've completely revamped the Asset API to provide a more intuitive and unified interface for handling media files. Key changes include:

   - New `Asset` facade with streamlined methods
   - Simplified asset retrieval with `Asset::get()`
   - Built-in URL handling with `Asset::url()`
   - Additional utility methods: `exists()`, `size()`, `mime()`, `hash()`
   - Object-oriented usage available through `Asset::instance()`
   - Global `asset()` helper function updated

   These changes aim to improve developer experience and make asset handling more straightforward and consistent.
   ```

5. Upgrade Guide:
   ```markdown
   # Upgrading to HydePHP v2.0

   ## Asset API Changes

   1. Replace all usage of `Hyde::mediaLink()`, `Hyde::asset()`, and `Asset::mediaLink()` with `Asset::get()`:

      ```php
      // Old
      $path = Hyde::mediaLink('image.jpg');
      $path = Hyde::asset('image.jpg');
      $path = Asset::mediaLink('image.jpg');

      // New
      $path = Asset::get('image.jpg');
      ```

   2. For absolute paths, use the second parameter of `Asset::get()`:

      ```php
      $absolutePath = Asset::get('image.jpg', true);
      ```

   3. Use `Asset::url()` for handling both local and remote URLs:

      ```php
      $url = Asset::url('image.jpg');
      $url = Asset::url('https://example.com/image.jpg');
      ```

   4. Take advantage of new utility methods:

      ```php
      if (Asset::exists('image.jpg')) {
          $size = Asset::size('image.jpg');
          $mime = Asset::mime('image.jpg');
          $hash = Asset::hash('image.jpg');
      }
      ```

   5. For object-oriented usage, use `Asset::instance()`:

      ```php
      $asset = Asset::instance('image.jpg');
      $path = $asset->get();
      $exists = $asset->exists();
      ```

   6. Update your configuration file to include the new asset validation option:

      ```php
      // config/hyde.php
      'assets' => [
          'validate_existence' => env('HYDE_VALIDATE_ASSETS', false),
      ],
      ```

   These changes will ensure your project is compatible with HydePHP v2.0 and takes full advantage of the new Asset API.

This proposal aims to simplify the Asset API while maintaining flexibility and power. It adheres to the HydePHP philosophy by providing an intuitive interface that should be easy to use for both Laravel developers and those less familiar with the framework. The implementation plan ensures a smooth transition from v1 to v2, with clear deprecation notices and comprehensive upgrade instructions.

### Proposal Summary and Analysis

Independent Claude 3.5 Sonnet executive summary of the three proposals for improving the Asset API in HydePHP v2:

1. Fluent Asset API

This proposal introduces a consolidated `Asset` class with both static and instance methods, integrating functionality from the current Asset and Hyde facades. It offers a fluent interface for chaining operations on assets.

Benefits:
- Simplified, intuitive API
- Fluent interface for expressive chaining
- Consistent with Laravel patterns

Drawbacks:
- Significant departure from current API, potentially high upgrade friction
- Increased complexity with new classes (AssetFile, AssetCollection)

2. Fluent Asset API (Refined)

This refined proposal merges functionality from the Asset facade and MediaFile class into an abstract `Asset` class. It aligns more closely with existing Hyde page classes and retains some current naming conventions.

Benefits:
- Better alignment with existing Hyde patterns
- Comprehensive asset handling (discovery, manipulation, information)
- Extensibility through abstract class

Drawbacks:
- Still represents a major change from current API
- Potential learning curve for users unfamiliar with Hyde patterns

3. Unified Asset API

This proposal focuses on unifying asset-related functions under a single `Asset` facade, with a backing `AssetService`. It aims for a balance between simplification and maintaining familiarity.

Benefits:
- Maintains facade pattern, reducing upgrade friction
- Simplifies API while retaining familiar method names
- Includes a clear deprecation and upgrade path

Drawbacks:
- Less dramatic improvement in DX compared to fluent proposals
- Retains some potential for confusion with multiple ways to access assets

#### Final Recommendation:

Based on the analysis, the Unified Asset API (Proposal 3) appears to be the most balanced approach. It offers significant improvements in developer experience and API consistency while minimizing upgrade friction and implementation complexity. The clear deprecation process and upgrade path will ease the transition for existing users.

This proposal aligns well with HydePHP's philosophy of "Simplicity first. Power when you need it." It provides a more intuitive API without a complete overhaul, allowing for gradual adoption of new features. The retained facade pattern and familiar method names will help maintain accessibility for developers of varying experience levels.

While the fluent API proposals offer more dramatic improvements in expressiveness, the associated increase in complexity and potential for confusion may outweigh these benefits. The Unified Asset API strikes a good balance between enhancing DX and maintaining continuity with the current system.

Recommendation: Proceed with implementing the Unified Asset API (Proposal 3), ensuring thorough documentation and clear upgrade guides to support users through the transition.
