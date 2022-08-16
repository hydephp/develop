## [Unreleased] - YYYY-MM-DD

### About

Creates a new foundation class, the FileCollection. Which like the other foundation collections, discovers all the files. Running this part of the autodiscovery will further enrich the Hyde Kernel, and allow greater insight into the application. The end user experience should not be affected by this.

### Added
- Adds a new FileCollection class to hold all discovered source and asset files
- Adds a new File model as an object-oriented way of representing a project file

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
