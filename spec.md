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
