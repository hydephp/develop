## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- for new features.

### Changed
- Move class PageCollection into Foundation namespace
- Move class RouteCollection into Foundation namespace

### Deprecated
- for soon-to-be removed features.

### Removed
- for now removed features.

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.

### Upgrade guide

#### Collection namespace change

> You only need to do this if you have written custom code that uses the old namespace.

To upgrade the moved collection namespaces, simply replace the following namespace imports:

```diff
-use Hyde\Framework\PageCollection;
+use Hyde\Framework\Foundation\PageCollection;
-use Hyde\Framework\RouteCollection;
+use Hyde\Framework\Foundation\RouteCollection;
```
