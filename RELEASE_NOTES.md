## [Unreleased] - YYYY-MM-DD

### About

This release refactors the internal routing system. Unless you have written custom code that directly uses these classes and methods, updating should be fairly smooth. If not, you may want to read through the following overview.

The route index has been decoupled from page index and is split into two new collection classes, PageCollection and RouteCollection. The PageCollection contains all the site's parsed pages, and the RouteCollection contains all the page routes.

The RoutingService class remains for compatibility with existing code, but now only forwards calls to the new RouteCollection. The RoutingServiceContract interface is now deprecated.

### Added
- Adds a new RouteCollection class
- Adds a new PageCollection class
- Adds a $routeKey property to the AbstractPage class
- The page and route collections are now stored as properties of the HydeKernel
- Adds an option to the `Hyde::image()` helper to request the returned image path use the configured base URL if it's set
- Adds a new `save()` method to Markdown-based pages, to save the page object to the filesystem
- Added new internal helpers to improve serialization of object models

### Changed
- **breaking**: Navigation menu priorities now use route keys instead of slugs, see upgrade notes below
- Removed constructor from RoutingServiceContract interface
- Refactored RoutingService to use the new RouteCollection class
- AbstractPage::all() now returns a PageCollection, and includes the source file path as the array key
- Improved ConvertsArrayToFrontMatter action, which now supports nested arrays
- An exception is now thrown when attempting to get the path to an Image without a defined source path or URI
- internal: The HydeKernel is now stored as a singleton within the kernel class, instead of the service container
- internal: Refactor commands with shared code to extend new abstract base class

### Deprecated
- Deprecated interface RoutingServiceContract
- Deprecated RoutingServiceContract::getInstance()

### Removed
- Removed all non public-contract methods from RoutingService

### Fixed
- Fix [#383](https://github.com/hydephp/develop/issues/383): Navigation menu titles can't be set in BladeMatter
- Fix [#385](https://github.com/hydephp/develop/issues/385): `DocumentationPage::home()` did not work for custom documentation page output directories
- Fix [#386](https://github.com/hydephp/develop/issues/386): Documentation page sidebar labels were not constructed from front matter
- Fix bugs relating to the documentation sidebar labels that appeared in the last release

### Security
- in case of vulnerabilities.

### Upgrade notes

#### Route keys are now used in navigation config

Prior to this release, the navigation menu priorities were based on the page slug. This has been changed to the route key. A route key in Hyde is in short the compiled page's path, relative to the site's root. For example, `_site/foo/bar.html` has the route key `foo/bar`.

This change is breaking as it requires the configuration to be updated. However, this is really easy. Just change `docs` to `docs/index` in the `config/hyde.php` file.

```diff
'navigation' => [
	'order' => [
		'index' => 0,
		'posts' => 10,
-		'docs' => 100,
+		'docs/index' => 100,
	],
],
```
