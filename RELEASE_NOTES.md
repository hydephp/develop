## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added a new `HydeKernel::currentPage()` method to return the page being rendered.

### Changed
- Renamed global `$currentRoute` and `$currentPage` variables to `$route` and `$routeKey` respectively.
- Renamed `Render::getCurrentRoute()` to `Render::getRoute()` to match renamed property.
- Renamed `Render::getCurrentPage()` to `Render::getRouteKey()` to match renamed property.

### Deprecated
- Deprecate `RouteKey::normalize` method as it no longer performs any normalization.
- Deprecate `RenderData::getCurrentRoute()` as it is renamed to `getRoute()` to match renamed property.
  - This change affects the `Render::getCurrentRoute()` and `Hyde::currentRoute()` facade methods.
- Deprecate `RenderData::getCurrentPage()` as it is renamed to `getRouteKey()` to match renamed property.
  - This change affects the `Render::getCurrentPage()` and `Hyde::currentPage()` facade methods. 

### Removed
- Remove RouteKey normalization for dot notation support by @caendesilva in https://github.com/hydephp/develop/pull/1241

### Fixed
- Update MarkdownPost::getLatestPosts helper to sort using the DateTime object timestamp by @caendesilva in https://github.com/hydephp/develop/pull/1235
- Update PostAuthor::all() to map entries into array keyed by username by @caendesilva in https://github.com/hydephp/develop/pull/1236
- Normalize internal author array keys to lowercase to make author usernames case-insensitive by @caendesilva in https://github.com/hydephp/develop/pull/1237
- Update pretty relative index links to rewrite to `./` instead of `/` by @caendesilva in https://github.com/hydephp/develop/pull/1238
- Fixed https://github.com/hydephp/develop/issues/1240

### Security
- in case of vulnerabilities.
