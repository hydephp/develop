## [Unreleased] - YYYY-MM-DD

### About

This release continues refactoring the internal codebase. As part of this, a large part of deprecated code has been removed.

### Added
- Added `getRouteKey` method to `PageContract` and `AbstractPage`

### Changed
- Blog posts now have the same open graph title format as other pages
- Merged deprecated method `getRoutesForModel` into `getRoutes` in `RouteCollection`

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed deprecated legacy class `Compiler`  from the Hyde Realtime Compiler
- Removed deprecated interface `RoutingServiceContract` (deprecated in v0.59)
- Removed deprecated method `stylePath` from `AssetService` (deprecated in v0.50)
- Removed deprecated method `getHomeLink` from `NavigationMenu` (deprecated in v0.50)
- Removed deprecated method `parseFile` from `MarkdownDocument` (deprecated in v0.56)
- Removed deprecated method `getPostDescription` from `MarkdownPost` (deprecated in v0.58)
- Removed deprecated method `getRoutesForModel` from `RouteCollection`
- Removed deprecated property `$body`  from `MarkdownDocument`

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.
