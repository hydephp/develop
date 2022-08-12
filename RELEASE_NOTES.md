## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added `getRouteKey` method to `PageContract` and `AbstractPage`

### Changed
- Blog posts now have the same opengraph title format as other pages
- Merged deprecated method `getRoutesForModel` into `getRoutes` in `RouteCollection`

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed deprecated legacy `Compiler` class from the Hyde Realtime Compiler
- Removed deprecated `stylePath` method from `AssetService` (deprecated in v0.50)
- Removed deprecated `getHomeLink` method from `NavigationMenu` (deprecated in v0.50)
- Removed deprecated `parseFile` method from `MarkdownDocument` (deprecated in v0.56)
- Removed deprecated `$body` property from `MarkdownDocument`
- Removed deprecated `getRoutesForModel` method from `RouteCollection`

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.
