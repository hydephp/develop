## [Unreleased] - YYYY-MM-DD

### About

This release performs some refactors in preparation for the 1.0 release. Many of these refactors are breaking as classes are moved around to new namespaces.

In general, these changes should only affect those who have written custom code that interacts with the framework, though you may need to update your configuration files, and any Blade components you may have published.

### Added
- Added a JSON build information manifest automatically generated after a site build [#465](https://github.com/hydephp/develop/pull/465)
- Adds a helper class to get an object representation of the front matter schemas and their supported types [#484](https://github.com/hydephp/develop/pull/484)

### Changed
- Moved class StaticPageBuilder to Actions namespace
- Moved class AbstractBuildTask to Concerns namespace
- Moved class AbstractMarkdownPage to Concerns namespace
- Moved class AbstractPage to Concerns namespace
- Moved class Application into Foundation namespace
- Moved class Includes to Helpers namespace
- Moved class Asset to Helpers namespace
- Moved class DocumentationSidebar into Navigation namespace
- Moved class NavigationMenu into Navigation namespace
- Moved class NavItem into Navigation namespace
- Moved class FindsContentLengthForImageObject into Constructors namespace
- Merged interface RouteFacadeContract into existing interface RouteContract
- Renamed HydeBuildStaticSiteCommand to HydeBuildSiteCommand
- Renamed legacy FileCacheService to ViewDiffService
- Renamed method `Hyde::getSiteOutputPath()` to `Hyde::sitePath()`
- Extracted all constructor methods in page schema traits to a new single trait ConstructPageSchemas
- The `StaticPageBuilder::$outputPath` property is now a relative path instead of absolute
  
### Deprecated
- for soon-to-be removed features.

### Removed
- Removed interface IncludeFacadeContract
- Removed deprecated interface AssetServiceContract
- Removed deprecated interface HydeKernelContract
- Removed deprecated and unused abstract class ActionCommand
- Removed unused function `array_map_unique`
- Removed interface RouteFacadeContract (merged into existing RouteContract)
- Using absolute paths for site output directories is no longer supported (use build tasks to move files around after build if needed)

### Fixed
- Fixed validation bug in the rebuild command

### Security
- in case of vulnerabilities.
