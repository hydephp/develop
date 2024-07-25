# Asset API Overview

## Abstract

In order to rework the Asset API, we need to analyze the current state of the API, to find what can be improved.

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

/**
 * Real implementations of the facade methods:
 * 
 * @see \Hyde\Foundation\HydeKernel::mediaPath {@see \Hyde\Foundation\Kernel\Filesystem::mediaPath}
 * @see \Hyde\Foundation\HydeKernel::mediaLink {@see \Hyde\Foundation\Kernel\Hyperlinks::mediaLink}
 * @see \Hyde\Foundation\HydeKernel::asset {@see \Hyde\Foundation\Kernel\Hyperlinks::asset}
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

