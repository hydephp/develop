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

We also have a MediaFile class that looks like this:

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
