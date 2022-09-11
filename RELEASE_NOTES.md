## [Unreleased] - YYYY-MM-DD

### About

This release performs a large amount of refactors and naming changes in preparation for the 1.0 release. Many of these refactors are breaking as several classes are moved around to new namespaces, several are merged, methods renamed, interfaces updated, and more, so forth, etc.

In general, these changes should only affect those who have written custom code that interacts with the framework, though you may need to update your configuration files, and any Blade components you may have published.

### Added
- Added a JSON build information manifest automatically generated after a site build [#465](https://github.com/hydephp/develop/pull/465)
- Adds a helper class to get an object representation of the front matter schemas and their supported types [#484](https://github.com/hydephp/develop/pull/484)

### Changed

#### General

- Merged interface PageContract into abstract class AbstractPage
- Merged interface RouteFacadeContract into existing interface RouteContract
- Merged `getCurrentPagePath()` method into existing `getRouteKey()` method in PageContract and AbstractPage
- Replaced schema traits with interfaces, see https://github.com/hydephp/develop/pull/485
- Extracted all constructor methods in page schema traits to a new single trait ConstructPageSchemas
- The `StaticPageBuilder::$outputPath` property is now a relative path instead of absolute

#### Class and method renames
- Renamed base class AbstractPage to HydePage
- Renamed base class AbstractMarkdownPage to BaseMarkdownPage
- Renamed command class HydeBuildStaticSiteCommand to HydeBuildSiteCommand
- Renamed legacy class FileCacheService to ViewDiffService
- Renamed method `Hyde::getSiteOutputPath()` to `Hyde::sitePath()`

#### Namespace changes
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

#### Page-model specific
- Renamed method outputLocation to outputPath in HydePage.php
- Renamed method qualifyBasename to sourcePath in HydePage.php
- Renamed method getOutputLocation to outputLocation in HydePage.php
- Renamed method getFileExtension to fileExtension in HydePage.php
- Renamed method getOutputDirectory to outputDirectory in HydePage.php
- Renamed method getSourceDirectory to sourceDirectory in HydePage.php
- Changed named variable $basename to $identifier in HydePage.php
- Removed $strict option from the has() method HydePage.php

#### Documentation page front matter changes

- Deprecated property `$label` in `DocumentationPage.php`. Use `$navigation['title']` instead.
- Deprecated property `$hidden` in `DocumentationPage.php`. Use `$navigation['hidden']` instead.
- Deprecated property `$priority` in `DocumentationPage.php`. Use `$navigation['priority']` instead.

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed all experimental schema traits
- Removed interface IncludeFacadeContract
- Removed interface PageContract (merged into abstract class AbstractPage)
- Removed interface RouteFacadeContract (merged into existing RouteContract)
- Removed deprecated interface AssetServiceContract
- Removed deprecated interface HydeKernelContract
- Removed deprecated and unused abstract class ActionCommand
- Removed unused function `array_map_unique`
- Removed method `PageContract::getCurrentPagePath()` (merged into `getRouteKey()` in the same class)
- Removed method `AbstractPage::getCurrentPagePath()` (merged into `getRouteKey()` in the same class)
- Using absolute paths for site output directories is no longer supported (use build tasks to move files around after build if needed)

### Fixed
- Fixed validation bug in the rebuild command
- Hide x-cloak elements using inline style in styles.blade.php to prevent flashes until stylesheets are loaded

### Security
- in case of vulnerabilities.
