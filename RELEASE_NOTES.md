## [Unreleased] - YYYY-MM-DD

### Note from the maintainer
First of all, I'm really sorry for the just insane amount of breaking changes in this update. I believe they are
necessary in order to make v1.0 a great and stable release. I hope you'll understand. Most of the changes
are likely to not affect normal usage, with the exception of the front matter navigation key changes.

### About

This release performs a large amount of refactors and naming changes in preparation for the 1.0 release. Many of these refactors are breaking as several classes are moved around to new namespaces, several are merged, methods renamed, interfaces updated, and more, so forth, etc.

In general, these changes should only affect those who have written custom code that interacts with the framework, though you may need to update your configuration files, and any Blade components you may have published.

#### What you can expect to break

This update **requires the configuration file to be updated**.

The most high impact change is change of sidebar front matter options, and related areas. Please try updating your site in a test environment first, to see if you need to update any of your front matter.

### Added
- Added a JSON build information manifest automatically generated after a site build [#465](https://github.com/hydephp/develop/pull/465)
- Added a NavigationData object to HydePage.php
- Added a Route::is() method to determine if a given route or route key matches the instance it's called on
- Added a Site model [#506](https://github.com/hydephp/develop/pull/506)
- Added a route:list command [#516](https://github.com/hydephp/develop/pull/516)
- Added support for "dot notation" to the `HydePage::get()` method [#497](https://github.com/hydephp/develop/pull/497)
- Added support for "dot notation" to route key retrievals in the `Route` facade [#513](https://github.com/hydephp/develop/pull/513)
- Added support for plain HTML pages that are copied from the _pages to the _site directory [#519](https://github.com/hydephp/develop/pull/519)
- Added class aliases for all page types so they can be used in Blade components without the full namespace [#518](https://github.com/hydephp/develop/pull/518)
- Added a Redirect helper to create custom static HTML redirects [#527](https://github.com/hydephp/develop/pull/527)
- Added automatic cache busting to the Asset helper [#530](https://github.com/hydephp/develop/pull/530)

### Changed

#### Major breaking changes

**A very large number the changes in this update are breaking**, as such, not all are marked as breaking. The really major changes that require especially close attention are here listed, please scroll down to see the rest as well as the concrete changes of this high level overview.

- Rename `Features::blogPosts` to `Features::markdownPosts` - This means you must update your hyde.php config, otherwise blog posts might not be generated
- Rename `Features::hasBlogPosts` to `Features::hasMarkdownPosts`
- Renamed base class AbstractPage to HydePage
- Renamed base class AbstractMarkdownPage to BaseMarkdownPage
- Renamed several HydePage methods to be more consistent
- Renamed property $title to $label in NavItem.php
- Renamed property $uri to $url in Image.php
- Removed both RouteContract interfaces (inlined into Route.php, which you now type hint against instead)
- Changed front matter key `navigation.title` to `navigation.label`
- Changed front matter key `image.uri` to `image.url` for blog posts

##### Navigation schema changes
If you are using any of the following front matter properties, you will likely need to update them:

- `navigation.title` is now `navigation.label`
- The `label` setting has been removed from documentation pages, use `navigation.label` instead 
- The `hidden` setting has been removed from documentation pages, use `navigation.hidden` instead 
- The `priority` setting has been removed from documentation pages, use `navigation.priority` instead 

This change also bubbles to the HydePage accessors, though that will only affect you if you have written or published custom code that interacts with the framework.

#### General

- Merged interface PageContract into abstract class AbstractPage
- Merged interface RouteFacadeContract into the Route model implementation
- Merged interface RouteContract into the Route model implementation
- Merged `getCurrentPagePath()` method into existing `getRouteKey()` method in PageContract and AbstractPage
- Replaced schema traits with interfaces, see https://github.com/hydephp/develop/pull/485
- Extracted all constructor methods in page schema traits to a new single trait ConstructPageSchemas
- The `StaticPageBuilder::$outputPath` property is now a relative path instead of absolute
- Refactored how navigation and sidebar data are handled, unifying the API, see below for more details
- The algorithm for finding the navigation and sidebar orders has been updated, this may affect the order of your pages, and may require you to re-tweak any custom priorities.
- The navigation link to documentation index page now has default priority 500 instead of 100
- All usages where the RouteContract was type hinted with have been updated to type hint against the Route model implementation instead
- Changed Blade component identifier class 'sidebar-category' to 'sidebar-group'
- Changed Blade component identifier class 'sidebar-category-heading' to 'sidebar-group-heading'
- Changed Blade component identifier class 'sidebar-category-list' to 'sidebar-group-list'
- Changed the Route::toArray schema 
- Split the page metadata handling so that global metadata is now handled by the Site model (meta.blade.php must be updated if you have published it)
- The MetadataBag class now implements Htmlable, so you can use it directly in Blade templates without calling `render()`
- BladePage $view constructor argument is now optional
- internal: Move responsibility for filtering documentation pages to the navigation menus (this means that documentation pages that are not 'index' are no longer regarded as hidden)
- internal: The HydePage::$navigation property is now a NavigationData object instead of an array, however the object extends ArrayObject, so it should be mostly compatible with existing code

#### Class and method renames
- Renamed base class AbstractPage to HydePage
- Renamed base class AbstractMarkdownPage to BaseMarkdownPage
- Renamed command class HydeBuildStaticSiteCommand to HydeBuildSiteCommand
- Renamed legacy class FileCacheService to CheckSumService
- Renamed method `Hyde::getSiteOutputPath()` to `Hyde::sitePath()`
- Renamed method `Hyde::formatHtmlPath()` to `Hyde::formatLink()`
- Renamed class Metadata to MetadataBag

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
- Moved class Markdown into Models\Markdown namespace
- Moved class Markdown into Models\Markdown namespace
- Moved class FrontMatter into Models\Markdown namespace
- Moved class Author into Models\Support namespace
- Moved class DateString into Models\Support namespace
- Moved class File into Models\Support namespace
- Moved class Image into Models\Support namespace
- Moved class Route into Models\Support namespace
- Moved class Site into Models\Support namespace
- Moved class ValidationResult into Models\Support namespace
- Moved class MarkdownConverter into Actions namespace
- Moved class MarkdownFileParser into Actions namespace

#### Page-model specific
- Removed action class FindsNavigationDataForPage.php (merged into HydePage.php via the GeneratesNavigationData trait)
- Renamed method outputLocation to outputPath in HydePage.php
- Renamed method qualifyBasename to sourcePath in HydePage.php
- Renamed method getOutputLocation to outputLocation in HydePage.php
- Renamed method getFileExtension to fileExtension in HydePage.php
- Renamed method getOutputDirectory to outputDirectory in HydePage.php
- Renamed method getSourceDirectory to sourceDirectory in HydePage.php
- Changed named variable $basename to $identifier in HydePage.php
- Removed $strict option from the has() method HydePage.php
- Removed method renderPageMetadata from HydePage.php (use metadata() and/or metadata()->render() instead)

#### Documentation page front matter changes

- Removed property `$label` in `DocumentationPage.php` (use `$navigation['title']` instead)
- Removed property `$hidden` in `DocumentationPage.php` (use `$navigation['hidden']` instead)
- Removed property `$priority` in `DocumentationPage.php` (use `$navigation['priority']` instead)
- Removed property `$category` in `DocumentationPage.php` (use `$navigation['group']` instead)
- Removed front matter option`label` (use `navigation.label` instead)
- Removed front matter option`hidden` (use `navigation.hidden` instead)
- Removed front matter option`priority` (use `navigation.priority` instead)
- Removed front matter option`category` (use `navigation.group` instead)
- To access the sidebar label setting via class property, use `$navigation['label']` instead of `$label`, etc.
- To access the sidebar label setting via front matter getters, use `navigation.label` instead of `label`, etc.

#### Markdown post/pre-processor changes

If you have not written any custom Markdown processors or any custom codes that interacts with default ones, you can ignore this section. Note that list may not be exhaustive.

- Removed interface MarkdownProcessorContract (use MarkdownPreProcessorContract or MarkdownPostProcessorContract instead)
- ShortcodeProcessor now implements MarkdownPreProcessorContract instead of MarkdownProcessorContract
- Renamed method `ShortcodeProcessor::process()` to `ShortcodeProcessor::preprocess()`
- Renamed class AbstractColoredBlockquote to ColoredBlockquotes
- Refactored a large part of the MarkdownService class

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed MarkdownServiceProvider (inlined into HydeServiceProvider)
- Removed interface IncludeFacadeContract
- Removed interface PageContract (merged into abstract class AbstractPage)
- Removed interface MarkdownPageContract (merged into abstract class BaseMarkdownPage)
- Removed interface RouteFacadeContract (merged into the Route.php implementation)
- Removed interface RouteContract (merged into the Route.php implementation)
- Removed deprecated interface AssetServiceContract
- Removed deprecated interface HydeKernelContract
- Removed deprecated and unused abstract class ActionCommand
- Removed unused function `array_map_unique`
- Removed method `PageContract::getCurrentPagePath()` (merged into `getRouteKey()` in the same class)
- Removed method `AbstractPage::getCurrentPagePath()` (merged into `getRouteKey()` in the same class)
- Removed method `Route::getSourceFilePath()` (use new `Route::getSourcePath()` instead)
- Removed method `Route::getOutputFilePath()` (use new `Route::getOutputPath()` instead)
- Removed unused $default parameter from Hyde::url method
- Using absolute paths for site output directories is no longer supported (use build tasks to move files around after build if needed)
- RealtimeCompiler: Removed support for the legacy bootstrapping file removed in Hyde v0.40
- Removed all experimental schema traits

### Fixed
- Fixed validation bug in the rebuild command
- Hide x-cloak elements using inline style in styles.blade.php to prevent flashes until stylesheets are loaded
- Configuration defined navigation labels were documented but not implemented

### Security
- in case of vulnerabilities.
