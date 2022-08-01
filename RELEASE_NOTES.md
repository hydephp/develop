## [Unreleased] - YYYY-MM-DD

### About

This release refactors and cleans up a large part of the internal code base. For most end users, this will not have any visible effect. If you have developed integrations that depend on methods you may want to take a closer look at the associated pull requests as it is not practical to list them all here.

#### Overview

Here is a short overview of the areas that are impacted. If you don't know what any of these mean, they don't affect you.

- HydeKernel has been internally separated into foundation classes
- DiscoveryService has been refactored
- Page compiling logic are now handled within the page models


### Added
- internal: Adds methods to the HydeKernelContract interface
- Added new filesystem helpers, `Hyde::touch()`, and `Hyde::unlink()`

### Changed
- internal: The HydeKernel has been refactored to move related logic to service classes. This does not change the end usage as the Hyde facade still works the same
- `DiscoveryService::getSourceFileListForModel()` now throws an exception instead of returning false when given an invalid model class
- `DiscoveryService::getFilePathForModelClassFiles` method was renamed to `DiscoveryService::getModelSourceDirectory`
- `DiscoveryService::getFileExtensionForModelFiles` method was renamed to `DiscoveryService::getModelFileExtension`
- The `Hyde::copy()` helper now always uses paths relative to the project
- The `Hyde::copy()` helper will always overwrite existing files
- Replaced `SitemapService::canGenerateSitemap()` with `Features::sitemap()`
- Replaced `RssFeedService::canGenerateFeed()` with `Features::rss()`
- RSS feed is now always present on all pages, see reasoning in [`a93e30020`](https://github.com/hydephp/develop/commit/a93e30020e2a791398d95afb5da493285541708a)

### Deprecated
- Deprecated trait `HasMarkdownFeatures.php`

### Removed
- Removed deprecated `Hyde::uriPath()` helper
- Removed deprecated `CollectionService::findModelFromFilePath()`

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.


### Upgrade tips

When refactoring the Hyde::copy() helper change, you have two options (that you can combine). If one or more of your inputs are already qualified Hyde paths, use the native copy helper. If you don't want to overwrite existing files, make that check first.
