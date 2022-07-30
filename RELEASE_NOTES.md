## [Unreleased] - YYYY-MM-DD

### About

This release refactors some internal code. If you have published any Blade views or created any custom integrations, you may want to take a closer look at the changes. Otherwise, this should not affect most existing sites.

### Added
- Added `Hyde::url()` and `Hyde::hasSiteUrl()` helpers, replacing now deprecated `Hyde::uriPath()` helper

### Changed
- The HTML page titles are now generated in the page object, using the new `htmlTitle()` helper
- Renamed helper `Hyde::pageLink()` to `Hyde::formatHtmlPath()`
- internal: DiscoveryService.php is no longer deprecated
- internal: CollectionService.php was merged into DiscoveryService

### Deprecated
- Deprecated `Hyde::uriPath()`, use `Hyde::url()` or `Hyde::hasSiteUrl()` instead
- Deprecated `Helpers\Author.php`, will be merged into `Models\Author.php`

### Removed
- internal: CollectionService.php has been removed, all its functionality has been moved to DiscoveryService
- internal: The `$currentPage` parameter of a few methods has been removed, it is no longer necessary due to it being inferred from the view being rendered

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.
