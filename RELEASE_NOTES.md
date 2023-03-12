## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added new method `HydePage::getCanonicalUrl()` to replace deprecated `HydePage::$canonicalUrl` property.

### Changed
- Added default RSS feed description value to the config stub in [#1253](https://github.com/hydephp/develop/pull/1253)
- Changed the RSS feed configuration structure to be an array of feed configurations in [#1258](https://github.com/hydephp/develop/pull/1258)
  - Replaced option `hyde.generate_rss_feed` with `hyde.rss.enabled`
  - Replaced option `hyde.rss_filename` with `hyde.rss.filename`
  - Replaced option `hyde.rss_description` with `hyde.rss.description`

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed `RouteKey::normalize` method deprecated in v1.0.0-RC.2
- Removed `RenderData:.getCurrentPage` method deprecated in v1.0.0-RC.2
- Removed `RenderData:.getCurrentRoute` method deprecated in v1.0.0-RC.2
- Removed deprecated `HydePage::$canonicalUrl` property (replaced with `HydePage::getCanonicalUrl()`).
- Removed deprecated `SourceFile::withoutDirectoryPrefix` method only used in one test.
- Removed deprecated `CreatesNewPageSourceFile::getOutputPath` method as the save method now returns the path.

### Fixed
- Fixed the blog post article view where metadata assembly used legacy hard-coded paths instead of dynamic path information.

### Security
- in case of vulnerabilities.
