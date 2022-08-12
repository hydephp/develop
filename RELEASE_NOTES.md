## [Unreleased] - YYYY-MM-DD

### About

This release continues refactoring the internal codebase. As part of this, a large part of deprecated code has been removed.

### Added
- Added `getRouteKey` method to `PageContract` and `AbstractPage`

### Changed
- Blog posts now have the same open graph title format as other pages
- Merged deprecated method `getRoutesForModel` into `getRoutes` in `RouteCollection`
- Cleans up and refactors `GeneratesDocumentationSearchIndexFile`, and marks it as internal
- Changed MarkdownFileParser to expect that the supplied filepath is relative to the root of the project (this may break method calls where an absolute path is supplied, see upgrade guide)
- internal: Inline deprecated internal method usage `getOutputPath` replacing it `Hyde::pages()` helper with in `HydeRebuildStaticSiteCommand`

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed class `RoutingService` as it is no longer used
- Removed deprecated legacy class `Compiler`  from the Hyde Realtime Compiler
- Removed deprecated interface `RoutingServiceContract` (deprecated in v0.59)
- Removed deprecated method `stylePath` from `AssetService` (deprecated in v0.50)
- Removed deprecated method `getHomeLink` from `NavigationMenu` (deprecated in v0.50)
- Removed deprecated method `parseFile` from `MarkdownDocument` (deprecated in v0.56)
- Removed deprecated method `getPostDescription` from `MarkdownPost` (deprecated in v0.58)
- Removed deprecated method `getCanonicalLink` from `MarkdownPost` (deprecated in v0.58)
- Removed deprecated method `getInstance` from `RoutingService` (deprecated in v0.59)
- Removed deprecated method `getRoutesForModel` from `RouteCollection`
- Removed deprecated method `getOutputPath` from `HydeRebuildStaticSiteCommand`
- Removed deprecated property `$body`  from `MarkdownDocument`
- internal: Remove deprecated testing helper functions `backup` and `restore`

### Fixed
- MarkdownFileParser not using the Hyde path [#399](https://github.com/hydephp/develop/issues/399)

### Security
- in case of vulnerabilities.

### Upgrade Guide

#### MarkdownFileParser path change 
This class now expects the supplied filepath to be relative to the root of the project. This will only affect you if you have written any custom code that uses this class. All internal Hyde code is already updated to use the new path format.

To upgrade, change any calls you may have like follows:

```diff
-return (new MarkdownFileParser(Hyde::path('_posts/foo.md')))->get();
+return (new MarkdownFileParser('_posts/foo.md'))->get();
```
