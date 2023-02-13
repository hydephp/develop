# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Upcoming changes

Please see the [RELEASE_NOTES.md](RELEASE_NOTES.md) for the changelog for the upcoming release.

## About the release cycle

HydePHP consists of two primary components, Hyde/Hyde and Hyde/Framework. Development is made in the [Hyde/Develop Monorepo](https://github.com/hydephp/develop). Major and Minor release versions are made in the Develop project. These releases are synced to the Hyde and Framework projects, and are what this changelog file tracks. Patch release versions are made in the Framework and Hyde projects independently. See https://github.com/hydephp/develop#releases for more information.


<!-- CHANGELOG_START -->

## [v0.64.0-beta](https://github.com/hydephp/develop/releases/tag/v0.64.0-beta) - 2022-10-18

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
- Renamed legacy class FileCacheService to ChecksumService
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


## [v0.63.0-beta](https://github.com/hydephp/develop/releases/tag/v0.63.0-beta) - 2022-09-01

### About

This release contains breaking changes regarding the PostBuildTasks that may require your attention if you have created custom tasks.

### Added
- Added the option to define some site configuration settings in a `hyde.yml` file. See [#449](https://github.com/hydephp/develop/pull/449)
- Build tasks are now automatically registered when placed in the app/Actions directory and end with BuildTask.php

### Changed
- **Breaking changes to build hooks/tasks**:
  - Rename BuildHookService to BuildTaskService
  - AbstractBuildTask::handle and BuildTaskContract::handle now returns null by default instead of void. It can also return an exit code
  - The way auxiliary build actions are handled internally has been changed to use build tasks, see [PR #453](https://github.com/hydephp/develop/pull/453)
  - The documentation has been updated to consistently refer to these as tasks instead of hooks
- The RSS feed related generators are now only enabled when there are blog posts
  - This means that no feed.xml will be generated, nor will there be any references (like meta tags) to it when there are no blog posts
- The documentation search related generators are now only enabled when there are documentation pages
  - This means that no search.json nor search.html nor any references to them will be generated when there are no documentation pages
- The methods in InteractsWithDirectories.php are now static, this does not affect existing usages
- Renamed HydeSmartDocs.php to SemanticDocumentationArticle.php
- Cleans up the Author model class and makes the constructors final

### Deprecated
- Deprecated ActionCommand.php as it is no longer used. It will be removed in the next release.

### Fixed
- Fixed [#443](https://github.com/hydephp/develop/issues/443): RSS feed meta link should not be added if there is not a feed


## [v0.62.0-beta](https://github.com/hydephp/develop/releases/tag/v0.62.0-beta) - 2022-08-27

### About

This update deprecates two interfaces (contracts) and inlines them into their implementations. It also refactors the documentation page layout to use more Blade components which may cause you to need to republish any manually published components.

The following interfaces are affected: `HydeKernelContract` and `AssetServiceContract`. These interfaces were used to access the service container bindings. Instead, you would now type hint the implementation class instead of the contract. This change will only affect those who have written custom code that uses or type hints these interfaces, which is unlikely. If this does affect you, you can see this diff to see how to upgrade. https://github.com/hydephp/develop/pull/428/commits/68d2974d54345ec7c12fedb098f6030b2c2e85ee. In short, simply replace `HydeKernelContract` and `AssetServiceContract` with `HydeKernel` and `AssetService`.


### Changed
- The documentation page layout has been internally refactored to utilize more Blade components. This only affects those who have extended or customized the documentation components. Some documentation components have also been renamed.

### Deprecated
- Deprecate interface HydeKernelContract, type hint the HydeKernel::class instead
- Deprecate interface AssetServiceContract, type hint the AssetService::class instead
  
### Removed
- Removed legacy `.js-enabled` class from documentation pages

### Fixed
- The list element of the documentation page sidebar had a conflicting ID (`#sidebar`) and has now been changed to `#sidebar-navigation` which may break edge cases where this component is styled or interacted with outside of the framework.
- Fix documentation page flickering [#388](https://github.com/hydephp/develop/issues/388)


## [v0.61.0-beta](https://github.com/hydephp/develop/releases/tag/v0.61.0-beta) - 2022-08-17

### About

Creates a new foundation class, the FileCollection. Which like the other foundation collections, discovers all the files. Running this part of the autodiscovery will further enrich the Hyde Kernel, and allow greater insight into the application. The end user experience should not be affected by this.

### Added
- Adds a new FileCollection class to hold all discovered source and asset files
- Adds a new File model as an object-oriented way of representing a project file

### Changed
- Move class PageCollection into Foundation namespace
- Move class RouteCollection into Foundation namespace

### Fixed
- Fix [#424](https://github.com/hydephp/develop/issues/424) AbstractMarkdownPage save method should use Hyde::path()

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


## [v0.60.0-beta](https://github.com/hydephp/develop/releases/tag/v0.60.0-beta) - 2022-08-12

### About

This release continues refactoring the internal codebase. As part of this, a large part of deprecated code has been removed and the package has been updated accordingly.

### Added
- Added `getRouteKey` method to `PageContract` and `AbstractPage`

### Changed
- Blog posts now have the same open graph title format as other pages
- Merged deprecated method `getRoutesForModel` into `getRoutes` in `RouteCollection`
- Cleans up and refactors `GeneratesDocumentationSearchIndexFile`, and marks it as internal
- Changed MarkdownFileParser to expect that the supplied filepath is relative to the root of the project (this may break method calls where an absolute path is supplied, see upgrade guide)
- internal: Inline deprecated internal method usage `getOutputPath` replacing it `Hyde::pages()` helper with in `HydeRebuildStaticSiteCommand`

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
- Undefined variable $currentRoute in search.html [#421](https://github.com/hydephp/develop/issues/421)
- Fixes issues in the documentation `search.json` and `search.html` when using custom output directories

### Upgrade Guide

#### MarkdownFileParser path change 
This class now expects the supplied filepath to be relative to the root of the project. This will only affect you if you have written any custom code that uses this class. All internal Hyde code is already updated to use the new path format.

To upgrade, change any calls you may have like follows:

```diff
-return (new MarkdownFileParser(Hyde::path('_posts/foo.md')))->get();
+return (new MarkdownFileParser('_posts/foo.md'))->get();
```


## [v0.59.0-beta](https://github.com/hydephp/develop/releases/tag/v0.59.0-beta) - 2022-08-11

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
- internal: A large part of the codebase has been refactored and cleaned up while making an effort to maintain compatibility with existing code

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
- Fix [#410](https://github.com/hydephp/develop/issues/410): Search index generator breaks when storing documentation page source files in subdirectories


### Upgrade notes

#### Route keys are now used in navigation config

Prior to this release, the navigation menu priorities were based on the page slug. This has been changed to the route key. A route key in Hyde is in short the compiled page's path, relative to the site's root. For example, `_site/foo/bar.html` has the route key `foo/bar`.

This change is breaking as the order of navigation items may be changed unless the configuration is updated. However, this is really easy. Just change `docs` to `docs/index` in the `config/hyde.php` file.

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

If you have used the config to hide the documentation page from the navigation menu, you also need to use the route key by changing `'exclude' => ['docs']` to `'exclude' => ['docs/index']`.
The same goes if you have used the config to change the navigation titles for the home and documentation pages.


## [v0.58.0-beta](https://github.com/hydephp/develop/releases/tag/v0.58.0-beta) - 2022-08-08

### About

This update contains **breaking changes** to the internal API regarding page models. This should only affect you directly if you've written any code that interacts with the internal page models, such as constructing them using non-built-in Hyde helpers.

The update makes large changes to how dynamic data is constructed. Instead of generating page data at runtime, now the data is generated when constructing a page object. This gives the major benefit of being able to see all dynamic data right away, without having to render the page.

The way metadata tags are handled internally is also refactored. The rendered result should not be affected.

### Added
- Added `compile()` method to `Facades\Markdown`, replacing the `parse()` method of the same class
- Adds new actions to handle complex dynamic constructors
- Adds new front matter schema traits to define the public API for front matter and hold their data
- Adds new Meta::link() helper to create `<link>` tags
- Adds new Meta::get() helper to get the metadata array
- Adds a new system for creating and storing page metadata
- Adds several new metadata model classes

### Changed
- Breaking: Rename AbstractMarkdownPage constructor parameter `slug` to `identifier`
- Breaking: Rename AbstractPage property `slug` to `identifier`
- Breaking: Change `AbstractMarkdownPage` constructor argument positions, putting `identifier` first
- Breaking: Splits Markdown data from MarkdownDocument into new Markdown model class
- Breaking: The default `config/hyde.php` file now uses `Models\Author` instead of `Helpers\Author`
- Major: Restructure internal page data to use new front matter schema traits 
- Begin changing references to slugs to identifiers, see motivation below
- Makes some helpers in SourceFileParser public static allowing them to be used outside the class
- Page metadata is now stored as a page property, making it easier to see and understand
- Page metadata is now generated at compile time instead of build time
- Page metadata types are now strongly typed, however all types are String able, so end usage is not affected

### Deprecated
- Deprecated `Facades\Markdown::parse()`, use `Facades\Markdown::render()` instead
- Deprecated `Facades\Markdown.php`, will be merged into `Models\Markdown.php` 

### Removed
- Removed `Facades\Markdown.php`, merged into `Models\Markdown.php`
- Removed `body()` method from `MarkdownDocumentContract` interface and all its implementations. Use `markdown()->body()` (or cast to string) instead
- Removed `body` property from Markdown pages. Use `markdown()->body()` (or cast to string) instead
- Removed deprecated `Helpers\Author` (fully merged into `Models\Author`, simply swap namespace to upgrade)
- Removed metadata constructor helpers from the MarkdownPost class as it is now handled in the new metadata class
- Several internal single-use helper traits have been merged into their respective classes

### Fixed
- Fix Path property in Image model should be relative to media directory [#359](https://github.com/hydephp/develop/issues/359)
- Fix Add toString method to Image model to get the link [#370](https://github.com/hydephp/develop/issues/370)
- Fix Blog post OpenGraph images must be resolved relatively [#374](https://github.com/hydephp/develop/issues/374)
- Fix PageContract needs compile method [#366]((https://github.com/hydephp/develop/issues/366))


### Upgrade guide and extra information

#### Rename slugs to identifiers

Previously internally called `slug(s)`, are now called `identifier(s)`. In all honestly, this has 90% to do with the fact that I hate the word "slug".
I considered using `basename` as an alternative, but that does not fit with nested pages. Here instead is the definition of an `identifier` in the context of HydePHP:

> An identifier is a string that is in essence everything in the filepath between the source directory and the file extension.

So, for example, a page source file stored as `_pages/foo/bar.md` would have the identifier `foo/bar`. Each page type can only have one identifier of the same name.
But since you could have a file with the same identifier in the `_posts` directory, we internally always need to specify what source model we are using.

The identifier property is closely related to the page model's route key property, which consists of the site output directory followed by the identifier. 

#### Heavily refactor constructors of Markdown-based page models

Adds a new interface to the Markdown page model constructors, that expects instantiated FrontMatter and MarkdownDocument objects. Normally you would use the SourceFileParser to create the object.

This means that the constructor for all Markdown-based pages is completely changed. To use a format matching the old behaviour, you can use the `MarkdownPageModel::make` method.

#### Title property has been removed from page model constructors

The following syntax has been removed: `new MarkdownPage(title: 'Foo Bar')`
Instead, you can add it with front matter: `MarkdownPage::make(matter: ['title' => 'Foo Bar'])`

#### Markdown pages now have front matter in an object instead of array

This means that instead of the following `$post->matter['title']`, you would use `$post->matter('title')`, which allows you to add a fallback like so: `$post->matter('title', 'Untitled')`

#### Author helper has been merged into the model

The deprecated `Helpers\Author` has been fully merged into `Models\Author`. Simply swap namespaces to upgrade.

```diff
-use Hyde\Framework\Helpers\Author;
+use Hyde\Framework\Models\Author;
```


## [v0.57.0-beta](https://github.com/hydephp/develop/releases/tag/v0.57.0-beta) - 2022-08-03

### About

This update refactors the internal page source model parsing. This will likely not affect you directly, however, if you have written custom code that interacts with any class relating to the PageParser contract, you'll want to take a closer look at the changes.

### Added
- Added a new static shorthand to quickly parse Markdown files into MarkdownDocuments (`MarkdownFileParser::parse()`)
- Added `toArray()` method to MarkdownDocuments, which returns an array of all the body lines

### Changed
- All source model parsing is now handled by the new SourceFileParser action
- Blog post front matter no longer includes merged slug
- MarkdownDocument now implements the `Arrayable` interface
- Markdown page models no longer includes the slug merged into the front matter 
- All Markdown page models now have the title property inferred when parsing
- internal: The DocumentationPage slug now behaves like other pages, and the basename is produced at runtime, see below
- internal: Refactor search index generator to use route system

### Deprecated
- Deprecated `MarkdownDocument::parseFile()`, will be renamed to `MarkdownDocument::parse()`

### Removed
- The PageParserContract interface, and all of its implementations have been removed
- Removed `$localPath` property from DocumentationPage class, see above
- Removed trait HasDynamicTitle


## [v0.56.0-beta](https://github.com/hydephp/develop/releases/tag/v0.56.0-beta) - 2022-08-03

### About

This update makes changes to the internal Markdown services. If you have written code or integrations that uses any of these services, you may want to take a closer look. Otherwise, this should not affect you much.

Many Markdown related classes have been moved to a new namespace, and the classes themselves have been restructured. Again, this only affects those who in the past have used these classes outside of what Hyde normally provides.

Due to the nature of this refactor, where so much have been changed, not everything is documented here. See the attached pull request for the full Markdown change diff: https://github.com/hydephp/develop/pull/318

### Added
- Added model FrontMatter.php
- Create MarkdownConverter.php
- Create MarkdownServiceProvider.php
- internal: Added Benchmarking framework

### Changed

- Move `Markdown::hasTableOfContents()` to `DocumentationPage::hasTableOfContents() `
- Move most Markdown related classes into `Modules\Markdown` namespace
- Rename MarkdownConverterService to MarkdownService
- Rename MarkdownFileService to MarkdownFileParser
- Replace CommonMarkConverter with Hyde MarkdownConverter

### Removed
- Remove old MarkdownConverter action
- Delete HasMarkdownFeatures.php


## [v0.55.0-beta](https://github.com/hydephp/develop/releases/tag/v0.55.0-beta) - 2022-08-01

### About

This update removes the deprecated LegacyPageRouter class from the Hyde Realtime Compiler (HydeRC). Along with this release, the HydeRC is now on version 2.5, and requires Hyde version 0.48.0-beta or higher.

### Changed
- hyde/hyde now requires HydeRC version 2.5 or higher.
- hyde/realtime-compiler no longer supports Framework versions older than v0.48.0-beta.

### Removed
- Remove the deprecated LegacyPageRouter class from the HydeRC.


## [v0.54.0-beta](https://github.com/hydephp/develop/releases/tag/v0.54.0-beta) - 2022-08-01

### About

This release refactors and cleans up a large part of the internal code base. For most end users, this will not have any visible effect. If you have developed integrations that depend on methods you may want to take a closer look at the associated pull requests as it is not practical to list them all here.

#### Overview

Here is a short overview of the areas that are impacted. If you don't know what any of these mean, they don't affect you.

- HydeKernel has been internally separated into foundation classes
- DiscoveryService has been refactored
- Page compiling logic are now handled within the page models


### Added
- internal: Adds methods to the HydeKernelContract interface
- Added new filesystem helpers, `Hyde::touch()`, and `Hyde::unlink()`

### Changed
- internal: The HydeKernel has been refactored to move related logic to service classes. This does not change the end usage as the Hyde facade still works the same
- `DiscoveryService::getSourceFileListForModel()` now throws an exception instead of returning false when given an invalid model class
- `DiscoveryService::getFilePathForModelClassFiles` method was renamed to `DiscoveryService::getModelSourceDirectory`
- `DiscoveryService::getFileExtensionForModelFiles` method was renamed to `DiscoveryService::getModelFileExtension`
- The `Hyde::copy()` helper now always uses paths relative to the project
- The `Hyde::copy()` helper will always overwrite existing files
- Replaced `SitemapService::canGenerateSitemap()` with `Features::sitemap()`
- Replaced `RssFeedService::canGenerateFeed()` with `Features::rss()`
- RSS feed is now always present on all pages, see reasoning in [`a93e30020`](https://github.com/hydephp/develop/commit/a93e30020e2a791398d95afb5da493285541708a)

### Deprecated
- Deprecated trait `HasMarkdownFeatures.php`

### Removed
- Removed deprecated `Hyde::uriPath()` helper
- Removed deprecated `CollectionService::findModelFromFilePath()`


### Upgrade tips

When refactoring the Hyde::copy() helper change, you have two options (that you can combine). If one or more of your inputs are already qualified Hyde paths, use the native copy helper. If you don't want to overwrite existing files, make that check first.


## [v0.53.0-beta](https://github.com/hydephp/develop/releases/tag/v0.53.0-beta) - 2022-07-30

### About

This release refactors some internal code. If you have published any Blade views or created any custom integrations, you may want to take a closer look at the changes. Otherwise, this should not affect most existing sites.

### Added
- Added `Hyde::url()` and `Hyde::hasSiteUrl()` helpers, replacing now deprecated `Hyde::uriPath()` helper

### Changed
- The HTML page titles are now generated in the page object, using the new `htmlTitle()` helper
- Renamed helper `Hyde::pageLink()` to `Hyde::formatHtmlPath()`
- internal: DiscoveryService.php is no longer deprecated
- internal: CollectionService.php was merged into DiscoveryService
- internal: Renamed trait GeneratesPageMetadata to HasArticleMetadata

### Deprecated
- Deprecated `Hyde::uriPath()`, use `Hyde::url()` or `Hyde::hasSiteUrl()` instead
- Deprecated `Helpers\Author.php`, will be merged into `Models\Author.php`

### Removed
- internal: CollectionService.php has been removed, all its functionality has been moved to DiscoveryService
- internal: The `$currentPage` parameter of a few methods has been removed, it is no longer necessary due to it being inferred from the view being rendered


## [v0.52.0-beta](https://github.com/hydephp/develop/releases/tag/v0.52.0-beta) - 2022-07-29

### About

This update internally refactors how documentation sidebars are handled. If you have published Blade views relating to these, or built framework integrations you may want to take a closer look at the changed files.

### Added
- Hyde now supports nested pages!

### Changed
- internal: Refactor how documentation sidebars are generated and handled
- internal: (Sidebar) categories are now internally referred to as "groups"
- internal: The sidebar related Blade views have been renamed
- `DocumentationPage::indexPath()` was renamed to `DocumentationPage::home()` and now returns a `Route` instead of a URL. It no longer resolves to README files.


## [v0.51.0-beta](https://github.com/hydephp/develop/releases/tag/v0.51.0-beta) - 2022-07-28

### Added
- Add Laravel Tinker as a development dependency for the Monorepo
- Improved the `hyde make:page` command to add page type selection shorthands

### Removed
- Removed test files from the hyde/hyde sub repository


## [v0.50.0-beta](https://github.com/hydephp/develop/releases/tag/v0.50.0-beta) - 2022-07-26

### About

This update makes breaking changes to the configuration. You will need to update your configuration to continue using the new changes. Each one has been documented in this changelog entry, which at the end has an upgrade guide.

### Overview of major changes

As there are a lot of changes, here is first a quick overview of the major ones. See the full list after this section.

- Alpine.js is now used for interactions.
- HydeFront has been rewritten and is now on version 2.x.
- The hyde.css and hyde.js files have now for all intents and purposes been merge into app.css and refactored to Alpine.js, respectively.
- The documentation pages are now styled using TailwindCSS instead of Lagrafo.
- Moved some configuration options from hyde.php to site.php
- Moved Composer dependencies, you will laravel-zero/framework added to your Hyde composer.json file.

Note that the goal with this release is to make the framework more stable and developer friendly, but without it affecting the end user experience. For example, the visual experience as well as the interactions of the refactored documentation pages are minimal and most users won't notice any change. However, for developers, the changes are significant and will reduce a lot of complexity in the future.

### Added
- Added [Alpine.js](https://alpinejs.dev/) to the default HydePHP layout
- Added a new configuration file, `config/site.php`, see below
- Added RSS feed configuration stubs to `config/site.php`
- Added an `Includes` facade that can quickly import partials
- Added an automatic option to load footer Markdown from partial
- Added the `hyde.load_app_styles_from_cdn` option to load `_media/app.css` from the CDN

### Changed

- Move laravel-zero/framework Composer dependency to hyde/hyde package
- Moved site specific configuration settings to `config/site.php`
  - Moved config option `hyde.name` to `site.name`
  - Moved config option `hyde.site_url` to `site.url`
  - Moved config option `hyde.pretty_urls` to `site.pretty_urls`
  - Moved config option `hyde.generate_sitemap` to `site.generate_sitemap`
  - Moved config option `hyde.language` to `site.language`
  - Moved config option `hyde.output_directory` to `site.output_directory`
- The default `site.url` is now `http://localhost` instead of `null`
- Merged configuration options for the footer, see below
- Rebrand `lagrafo` documentation driver to `HydeDocs`
- Hyde now requires a minimum version of HydeFront v2.x, see release notes below
- internal: Refactor navigation menu components and improve link helpers
- internal: The main Hyde facade class has been split to house the logic in the HydeKernel class, but all methods are still available through the new facade with the same namespace  
- internal: Move tests foundation to new testing package
- internal: Renamed `GeneratesTableOfContents.php` to `GeneratesSidebarTableOfContents.php`

### Removed
- Removed `\Hyde\Framework\Facades\Route`. You can swap out usages with `\Hyde\Framework\Models\Route` without side effects.
- Removed ConvertsFooterMarkdown.php
- Removed internal `$siteName` config variable from `config/hyde.php`

### Fixed
- Fixed bug [#260](https://github.com/hydephp/develop/issues/260) where the command to publish a homepage did not display the selected value when it was supplied as a parameter
- Fixed bug [#272](https://github.com/hydephp/develop/issues/272), only generate the table of contents when and where it is actually used
- Fixed bug [#41](https://github.com/hydephp/develop/issues/41) where search window does not work reliably on Safari

### Upgrade Guide

Here are some instructions for upgrading an existing project.
You should also read the standard upgrade guide first for general advice, https://hydephp.com/docs/master/updating-hyde.

If you use Git, you may be able to automatically configure some of these by merging https://github.com/hydephp/hyde into your project. Alternatively, you can download the release and unzip it into your project directory, and using GitHub Desktop or VS Code (or whatever you use) to stage the new changes without affecting your project's configuration.

#### Core file changes

Here is an overview of the core files that have changed and that you will most likely need to update. Some of these have detailed instructions further down.

- `config/site.php` (new)
- `config/hyde.php` (changed)
- `config/app.php` (changed)
- `app/bootstrap.php` (changed)
- `composer.json` (changed)
- `package.json` (changed)
- `resources\assets\app.css` (changed)
- `_pages\404.blade.php` (changed)

A large number of Blade views have also changed. You may want to update pretty much all of them. See the diff for a list of files that have changed.

#### Updating Composer

When updating an existing project, you may need to add laravel-zero/framework to your Hyde composer.json file.

```json
    "require": {
        "php": "^8.0",
        "hyde/framework": "^0.50",
        "laravel-zero/framework": "^9.1"
    },
```

#### Using the new site config

Site-specific config options have been moved from `config/hyde.php` to `config/site.php`. The Hyde config is now used to configure behaviour of the site, while the site config is used to customize the look and feel, the presentation, of the site.

The following configuration options have been moved. The actual usages remain the same, so you can upgrade by using copying over these options to the new file.

- `hyde.name`
- `hyde.site_url` (is now just `site.url`)
- `hyde.pretty_urls`
- `hyde.generate_sitemap`
- `hyde.language`
- `hyde.output_directory`

If you have published and Blade views or written custom code that uses the config options, you may need to update them. You can do this by republishing the Blade views, and/or using search and replace across your code. VSCode has a useful feature to make this a breeze: `CMD/CTRL+Shift+F`.

#### Using the new footer config

The footer configuration options have been merged. Prior to this update, the config option looked as follows:
```php
// filepath: config/hyde.php
'footer' => [
  'enabled' => true,
  'markdown' => 'Markdown text...'
],
```

Now, the config option looks as follows:
```php
// filepath: config/hyde.php

// To use Markdown text
'footer' => 'Markdown text...',

// To disable it completely
'footer' => false,
```

As you can see, the new config option is a string or the boolean false instead of an array. We use the same option for both the Markdown text and the footer disabled state.

#### Updating Blade Documentation Views

This release rewrites almost all of the documentation page components to use TailwindCSS. In most cases you won't need to do anything to update, however, if you have previously published the documentation views, you will need to update them.

### Release Notes for HydeFront v2.x

HydeFront version 2.0 is a major release and has several breaking changes.
It is not compatible with HydePHP versions lower than v0.50.0-beta. HydePHP versions equal to or later than v0.50.0-beta require HydeFront version 2.0 or higher.

Many files have been removed, as HydePHP now uses Alpine.js for interactions, and TailwindCSS for the documentation pages.

HydeFront v1.x will receive security fixes only.


## [v0.49.0-beta](https://github.com/hydephp/develop/releases/tag/v0.49.0-beta) - 2022-07-15

### Added
- Added configuration option to quickly enable HTML tags in Markdown

### Changed
- The DataCollection module now no longers filters out files starting with an underscore
- Moves the scripts that create the documentation page search window to HydeFront CDN
- Updated autoloaded HydeFront version to 1.13.x


## [v0.48.0-beta](https://github.com/hydephp/develop/releases/tag/v0.48.0-beta) - 2022-07-10 - Internal Pseudo-Router Service Refactoring

### About

This release brings a massive refactor in the way the HydePHP auto-discovery process works. It does this by centralizing all discovery logic to the new pseudo-router module which discovers and maps all source files and output paths.

The update also refactors related code to use the router. Part of this is a major rewrite of the navigation menu generation. If you have set any custom navigation links you will need to update your configuration files as the syntax has changed to use the NavItem model instead of array keys.

You will also need to update navigation related Blade templates, if you have previously published them.

### Added
- Added a pseudo-router module which will internally be used to improve Hyde auto-discovery
- Added a Route facade that allows you to quickly get a route instance from a route key or path
- Added a new NavItem model to represent navigation menu items
- Added a new configuration array for customizing the navigation menu, see the `hyde.navigation` array config

### Changed
- Changed how the navigation menu is generated, configuration files and published views must be updated
- Changed bootstrap.php to Stt Hyde base path using dirname instead of getcwd 
- Reversed deprecation for `StaticPageBuilder::$outputPath`
- internal refactor: Creates a new build service to handle the build process

### Deprecated
- Deprecated `DiscoveryService::findModelFromFilePath()` - Use the Router instead.
- Deprecated `DiscoveryService.php` - Use the Router instead. (Some helpers may be moved to FluentPathHelpers.php)

### Removed
- The "no pages found, skipping" message has been removed as the build loop no longer recieves empty collections.
- Removed the `hyde.navigation_menu_links` and `hyde.navigation_menu_blacklist` configuration options, see new addition above.


## [v0.47.0-beta](https://github.com/hydephp/develop/releases/tag/v0.47.0-beta) - 2022-07-05

### Added
- Add macroable trait to Hyde facade


## [v0.46.0-beta](https://github.com/hydephp/develop/releases/tag/v0.46.0-beta) - 2022-07-03

### Added
- Added `DocumentationPage::indexPath()`, replacing `Hyde::docsIndexPath()`

### Changed
- internal: Move service provider helper methods to the RegistersFileLocations trait
- internal: Add helpers.php to reduce repeated code and boilerplate
- internal: Change internal monorepo scripts for semi-automating the release process
- Added `DocumentationPage` as a class alias, allowing you to use it directly in Blade views, without having to add full namespace.

### Removed
- Remove deprecated `Hyde::getDocumentationOutputDirectory()`, replaced with `DocumentationPage::getOutputDirectory()`
- Remove deprecated `Hyde::docsIndexPath()`, replaced with `DocumentationPage::indexPath()`
- Remove deprecated `DocumentationPage::getDocumentationOutputPath()`, use `DocumentationPage::getOutputPath()` instead

### Fixed
- Fix minor bug in Blade view registry where merged array was not unique


## v0.45.0-beta - 2022-07-03

### Added
- Add dummy file to persist base Tailwind utilities https://github.com/hydephp/develop/pull/141
- Add configuration feature for DataCollections to enable/disable automatic _data directory generation https://github.com/hydephp/develop/pull/142

### Changed
- DataCollections are now disabled by default
- Rename internal trait RegistersDefaultDirectories to RegistersFileLocations

### Removed
- Removes the automatic check to see if the configuration file is up to date https://github.com/hydephp/develop/pull/143
- Remove deprecated `Hyde::titleFromSlug()` helper, use `Hyde::makeTitle()` instead
- Removed deprecated CollectionService::getBladePageList, is renamed to getBladePageFiles
- Removed deprecated CollectionService::getMarkdownPageList, is renamed to getMarkdownPageFiles
- Removed deprecated CollectionService::getMarkdownPostList, is renamed to getMarkdownPostFiles
- Removed deprecated CollectionService::getDocumentationPageList, is renamed to getDocumentationPageFiles

### Fixed
- Fix bug causing files starting with underscores to add empty values to the file collection array https://github.com/hydephp/develop/pull/140


## v0.44.0-beta - 2022-07-02 - Internal code restructuring

### About

This release mainly makes internal changes to the Framework API. If you are an end user, most of the changes are not relevant.
However, if you are a package developer, or if you have published Blade views or otherwise extended Hyde you may want to take a look as there are internal breaking changes.

### Added
- Added Hyde::makeTitle() helper, an improved version of Hyde::titleFromSlug()
- Added new helper method render() to MarkdownDocuments to compile the Markdown to HTML, fixes https://github.com/hydephp/develop/issues/109
- Added `MarkdownPost` as a class alias, allowing you to use it directly in Blade views, without having to add full namespace.

### Changed
- Update default HydeFront version to v1.12.x
- Updates the codebase to use the new Hyde::makeTitle() helper
- Several internal changes to how page models are structured, https://github.com/hydephp/develop/pull/122
- Internal: Separate the MarkdownDocument into a dedicated abstract page class, https://github.com/hydephp/develop/pull/126
- Moved `Hyde\Framework\Models\BladePage` to new namespace `Hyde\Framework\Models\Pages\BladePage`
- Moved `Hyde\Framework\Models\MarkdownPage` to new namespace `Hyde\Framework\Models\Pages\MarkdownPage`
- Moved `Hyde\Framework\Models\MarkdownPost` to new namespace `Hyde\Framework\Models\Pages\MarkdownPost`
- Moved `Hyde\Framework\Models\DocumentationPage` to new namespace `Hyde\Framework\Models\Pages\DocumentationPage`
- Improves how the site output directory is emptied, helping prevent accidental deletion of files https://github.com/hydephp/develop/pull/135
- The emptying of the site output directory can now be disabled by setting the new config option `hyde.empty_output_directory` to false https://github.com/hydephp/develop/pull/136

### Deprecated
- Deprecated Hyde::titleFromSlug(), use Hyde::makeTitle() instead
- Deprecate DocumentationPage::getDocumentationOutputPath()
- Deprecate Hyde::docsIndexPath()
- Deprecate Hyde::getDocumentationOutputDirectory()
- Deprecate RegistersDefaultDirectories.php pending rename
- Deprecated CollectionService::getBladePageList, is renamed to getBladePageFiles
- Deprecated CollectionService::getMarkdownPageList, is renamed to getMarkdownPageFiles
- Deprecated CollectionService::getMarkdownPostList, is renamed to getMarkdownPostFiles
- Deprecated CollectionService::getDocumentationPageList, is renamed to getDocumentationPageFiles

### Removed
- Remove unused `$withoutNavigation` variable from the app layout
- Removed deprecated 'hyde.site_output_path' config option (use `hyde.output_directory` instead)
- Remove long deprecated `hyde.version` and `framework.version` service container bindings
- Removed deprecated StarterFileService which was deprecated in v0.20.x 

### Fixed
- Fix style bug https://github.com/hydephp/develop/issues/117, Hyde title helper should not capitalize non-principal words


## v0.43.0-beta - 2022-06-25 - File-based Collections

### Added
- Added configuration option `hyde.media_extensions` to allow you to specify additional comma separated media file types. https://github.com/hydephp/develop/issues/39
- Adds a safer config option `hyde.output_directory` for customizing the output directory
- Adds a file-based way to create and interact with collections, https://hydephp.com/docs/master/collections


### Removed
- Removed the `--pretty` build command option which was deprecated in v0.25.x
- Removed deprecated internal AssetManager trait which was replaced with the Asset facade

### Fixed
- HydeRC: Fixes a bug in the auxiliary exception handler leading to unintentional recursion causing out of memory errors in both the browser and the PHP server.


## v0.42.0-beta - 2022-06-24

### Added
- Added a `@section` hook to the docs layout to allow yielding content
- HydeRC: Add ping route to check if a HydeRC server is running https://github.com/hydephp/realtime-compiler/issues/9
- internal: Added an HtmlResponse object to the realtime compiler

### Changed
- Change the the Prettier integration to only modify HTML files https://github.com/hydephp/develop/issues/102
- Change how the `docs/search.html` page is rendered, by handling page logic in the view, to decouple it from the build search command

### Fixed
- HydeRC: Rewrite request docs to docs/index to fix https://github.com/hydephp/realtime-compiler/issues/10 
- Fix bug https://github.com/hydephp/develop/issues/93 where styles were missing on search.html when changing the output directory to root


## v0.41.0-beta - 2022-06-24 - Add an Asset facade

### About

This release refactors and improves the Asset Service, adding auto-configuration features and a new Asset facade.

#### Using the Asset facade in Blade views

Instead of the long syntax `Hyde::assetManager()` you can now use the `Asset` facade directly. See this example, which both do the exact same thing using the same underlying service:

```blade
Hyde::assetManager()->hasMediaFile('app.css')
Asset::hasMediaFile('app.css')
```

If you don't know what any of this means, good news! You don't have to worry about it. Hyde's got your back.

### Added
- Added feature to dynamically load hyde.css and hyde.js if they exist locally
- Added the Asset facade to be used instead of `Hyde::assetManager()`
- Added the Asset facade as a class alias to `config/app.css`

### Changed
- Changed `scripts.blade.php` and `styles.blade.php` to use the Asset facade

### Deprecated
- Deprecated AssetManager.php (`Hyde::assetManager()`). Use the Asset facade instead



## v0.40.0-beta - 2022-06-22

### Added
- Added back the AppServiceProvider
- Added system for defining easy to use post-build hooks https://github.com/hydephp/develop/issues/79
- Added configuration option to exclude documentation pages from showing up in the JSON search index

### Changed
- Changelog files in the documentation source directory are now ignored by the JSON search index by default
- Adds a fallback which removes the search modal popup and redirects to the search.html page when the dialogue element is not supported.

### Deprecated
- Deprecate the site_output_path option in the Hyde config file. Will be handled by the HydeServiceProvider.

### Removed
- Removed the deprecated bootstrap directory
- Removed default .gitkeep from the _site directory

### Security
- Bump guzzlehttp/guzzle from 7.4.4 to 7.4.5

## v0.39.0-beta - 2022-06-20

### Added
- Added a helper to all page models to get an array of all its source files https://github.com/hydephp/develop/issues/44
- Added a helper to all page models to parse source files directly into an object https://github.com/hydephp/develop/issues/40
- Adds the MarkdownDocumentContract interface to markdown based pages to keep a consistent and predictable state
- Adds .gitkeep files to persist empty directories
- internal: Add more tests
- internal: Add packages/hyde/composer.json for persisted data instead of removed update script

### Changed
- Changed welcome page title https://github.com/hydephp/develop/issues/52
- Add `rel="nofollow"` to the image author links https://github.com/hydephp/develop/issues/19
- Changed the default position of the automatic navigation menu link to the right, also making it configurable
- Renamed deprecated Hyde::docsDirectory() helper to suggested Hyde::getDocumentationOutputDirectory()
- Makes the constructor arguments for Markdown page models optional https://github.com/hydephp/develop/issues/65
- Added the Hyde/Framework composer.lock to .gitignore as we keep a master lock file in the monorepo
- Changed namespace for Hyde/Framework tests from `Hyde\Testing\Framework` to `Hyde\Framework\Testing`
- Directories are created when needed, instead of each time the service provider boots up
- internal: Add back codecov.io to pull request tests https://github.com/hydephp/develop/issues/37
- internal: Refactor test that interact with the filesystem to be more granular
- internal: Update Monorepo structure to move persisted data for the Hyde package into the packages directory

### Removed
- Removed the Hyde::getLatestPosts() helper which was deprecated in v0.34.x and was replaced with MarkdownPost::getLatestPosts()
- Removes the long deprecated CreatesDefaultDirectories class
- internal: Removed composer update script

### Fixed
- Add changelog to export-ignore, https://github.com/hydephp/framework/issues/537


## v0.38.0-beta - 2022-06-18

### About

This release refactors the test suite, compartmentalizing test code into the respective package directories. 
This does not affect the behavior of the library, but it does affect how package developers run the test suites.

### Added
- internal: Adds high level tests for the Hyde package.
- internal: Add GitHub test workflows for Hyde/Hyde and Hyde/Framework

### Changed
- Formats code to the PSR-2 standard.

- internal: Move Framework tests from the monorepo into the Framework package.
- internal: Rename monorepo workflow `build-test.yml` to `continuous-integration.yml`.
- internal: Change testing namespaces update `phpunit.xml.dist` correspondingly.
- internal: Add static analysis tests to the continuous integration workflow.
- internal: Add matrix test runners to the continuous integration workflow.


## v0.37.2-beta - 2022-06-17

### About

This release brings internal restructuring to the Hyde monorepo,
adding a helper command to manage the new release cycle.

### Added
- Add internal `monorepo:release` command 

### Changed
- Changed to keep only a single `CHANGELOG.md` file for Hyde/Hyde and Hyde/Framework


## v0.37.1-beta - 2022-06-16 - Update validation test

### About

If there are no documentation pages there is no need for an index page, and the test can safely be skipped.

### What's Changed
* v0.37.0-beta - Create custom validator test framework by @caendesilva in https://github.com/hydephp/develop/pull/45
* Skip documentation index validation test if the _docs directory is empty by @caendesilva in https://github.com/hydephp/develop/pull/48


**Full Changelog**: https://github.com/hydephp/develop/compare/v0.36.0-beta...v0.37.1-beta


## v0.37.0-beta - 2022-06-16 - Replace dependency with custom validator implementation

### What's Changed
* v0.37.0-beta - Create custom validator test framework by @caendesilva in https://github.com/hydephp/develop/pull/45


**Full Changelog**: https://github.com/hydephp/develop/compare/v0.36.0-beta...v0.37.0-beta.1


## v0.36.0-beta - 2022-06-16 - Add package auto-discovery

### What's Changed
* Improve transformation of the hyde/hyde composer.json in the monorepo split job by @caendesilva in https://github.com/hydephp/develop/pull/33
* v0.36.x - Add package auto-discovery by @caendesilva in https://github.com/hydephp/develop/pull/35


**Full Changelog**: https://github.com/hydephp/develop/compare/v0.35.0-beta.1...v0.36.0-beta


## v0.35.0-beta - 2022-06-14 - Initial Monorepo Release

### What's Changed

* Restore master project by @caendesilva in https://github.com/hydephp/develop/pull/1
* Merge Hyde/Framework into packages/framework by @caendesilva in https://github.com/hydephp/develop/pull/2
* Refactor test suite, moving tests into Hyde root and updating some of them by @caendesilva in https://github.com/hydephp/develop/pull/3
* Remove default AppServiceProvider.php, fix #5 by @caendesilva in https://github.com/hydephp/develop/pull/6
* Fix #7: Remove unrelated configuration files from the framework package by @caendesilva in https://github.com/hydephp/develop/pull/8
* Refactor bootstrapping process by @caendesilva in https://github.com/hydephp/develop/pull/9
* Remove layover framework test files by @caendesilva in https://github.com/hydephp/develop/pull/10
* Import hydefront package by @caendesilva in https://github.com/hydephp/develop/pull/11
* Import hydephp/realtime-compiler to packages/ by @caendesilva in https://github.com/hydephp/develop/pull/16
* Handle moving of the bootstrap file to provide backwards compatibility for the migration period by @caendesilva in https://github.com/hydephp/develop/pull/17
* Import hydephp/docs by @caendesilva in https://github.com/hydephp/develop/pull/18
* Create readonly mirrors by @caendesilva in https://github.com/hydephp/develop/pull/21
* Add Rocket dashboard subrepository by @caendesilva in https://github.com/hydephp/develop/pull/25
* Work in progress single-file dashboard for the HydeRC by @caendesilva in https://github.com/hydephp/develop/pull/26
* Create dashboard template by @caendesilva in https://github.com/hydephp/develop/pull/27


**Full Changelog**: https://github.com/hydephp/develop/commits/v0.35.0-beta


<!-- CHANGELOG_END -->

---

## Archive (pre v0.35.0)

In v0.35.0 the Hyde project source was moved into the [HydePHP/Develop monorepo](https://github.com/hydephp/develop) where the changelog is now handled. Releases in Hyde/Hyde and Hyde/Framework are synced one-to-one since this change.

- [Hyde/Hyde Archive (pre v0.35.0)](#hydehyde-archive-pre-v0350)
- [Hyde/Framework Archive (pre v0.35.0)](#hydeframework-archive-pre-v0350)

### Hyde/Hyde Archive (pre v0.35.0)



All notable changes to this project will be documented in this file. Dates are displayed in UTC.

Generated by [`auto-changelog`](https://github.com/CookPete/auto-changelog).

#### [v0.34.1-beta](https://github.com/hydephp/hyde/compare/v0.34.0-beta...v0.34.1-beta)

> 11 June 2022

- Bump guzzlehttp/guzzle from 7.4.3 to 7.4.4 [`#187`](https://github.com/hydephp/hyde/pull/187)

#### [v0.34.0-beta](https://github.com/hydephp/hyde/compare/v0.33.0-beta...v0.34.0-beta)

> 6 June 2022

- Update Framework to v0.34.x [`c5c4f05`](https://github.com/hydephp/hyde/commit/c5c4f05fb65306768df1b7db53e41c19c0a34915)
- Update README.md [`6cbcf8b`](https://github.com/hydephp/hyde/commit/6cbcf8b4eb99dde9ac09d94125ccb71c857b5ddd)

#### [v0.33.0-beta](https://github.com/hydephp/hyde/compare/v0.32.3-beta...v0.33.0-beta)

> 4 June 2022


#### [v0.32.3-beta](https://github.com/hydephp/hyde/compare/v0.32.2-beta...v0.32.3-beta)

> 4 June 2022

- Move back hyde/realtime-compiler to hyde/hyde [`#184`](https://github.com/hydephp/hyde/pull/184)
- Update composer.lock [`b36937d`](https://github.com/hydephp/hyde/commit/b36937d5a2c354fd102d5bb4244bb1e167f0dde7)

#### [v0.32.2-beta](https://github.com/hydephp/hyde/compare/v0.32.1-beta...v0.32.2-beta)

> 4 June 2022

- Persist file cache data directory [`347e393`](https://github.com/hydephp/hyde/commit/347e3936f4914c73eb10c9ecf637521dc43d3308)
- Create cache .gitignore [`14e57b6`](https://github.com/hydephp/hyde/commit/14e57b6846e551cf138afc2313254a2c226ecc5f)

#### [v0.32.1-beta](https://github.com/hydephp/hyde/compare/v0.32.0-beta...v0.32.1-beta)

> 4 June 2022

- Update frontend and framework files [`#180`](https://github.com/hydephp/hyde/pull/180)
- Update composer.lock [`62b9b4e`](https://github.com/hydephp/hyde/commit/62b9b4e697fa0687a24f3a50e0700419f10b9b01)
- Automatic build update [`626983a`](https://github.com/hydephp/hyde/commit/626983ae06299d467acdeac07564db603ae017fc)

#### [v0.32.0-beta](https://github.com/hydephp/hyde/compare/v0.31.0-beta...v0.32.0-beta)

> 4 June 2022

- Update composer.lock [`e26171a`](https://github.com/hydephp/hyde/commit/e26171a3f9bda19a589c59f8d06b1432d100459c)
- Remove composer requirements handled by Framework [`b04754b`](https://github.com/hydephp/hyde/commit/b04754b9021011e8e7ae13deb83b3cee97bfc0b2)
- Update Hyde/Framework to v0.32.x [`4cd1161`](https://github.com/hydephp/hyde/commit/4cd11619225acf73971960b7a3caca74abdfc08b)

#### [v0.31.0-beta](https://github.com/hydephp/hyde/compare/v0.30.1-beta...v0.31.0-beta)

> 4 June 2022

- Update frontend and framework files [`#177`](https://github.com/hydephp/hyde/pull/177)
- Update to Framework 0.31.x [`2da64b4`](https://github.com/hydephp/hyde/commit/2da64b47738f08fc4119db43fc90a2c848f2f162)
- Automatic build update [`9a59cb6`](https://github.com/hydephp/hyde/commit/9a59cb60d8f80fc51bd9fe31e86902c4e63667d6)

#### [v0.30.1-beta](https://github.com/hydephp/hyde/compare/v0.30.0-beta...v0.30.1-beta)

> 31 May 2022

- Fix package.json version formatting error [`#175`](https://github.com/hydephp/hyde/pull/175)

#### [v0.30.0-beta](https://github.com/hydephp/hyde/compare/v0.29.0-beta...v0.30.0-beta)

> 31 May 2022

- Update Hyde to v0.30.x [`80d91b5`](https://github.com/hydephp/hyde/commit/80d91b5e050de7ff7eb9e97508f5ccef97d91525)

#### [v0.29.0-beta](https://github.com/hydephp/hyde/compare/v0.28.1-beta...v0.29.0-beta)

> 30 May 2022

- Update frontend and framework files [`#172`](https://github.com/hydephp/hyde/pull/172)
- Fix #169: remove white-space: pre from &lt;code&gt;, allowing it to wrap [`#170`](https://github.com/hydephp/hyde/pull/170)
- Merge pull request #170 from hydephp/Update-tailwind-config-to-allow-code-tags-to-wrap [`#169`](https://github.com/hydephp/hyde/issues/169)
- Fix #169: remove white-space: pre from &lt;code&gt; [`#169`](https://github.com/hydephp/hyde/issues/169)
- Update lock file [`f037c4d`](https://github.com/hydephp/hyde/commit/f037c4d3fd6d677d742ae57c18c4a712e59e6cef)
- Update hyde/realtime-compiler to v1.3.0 [`91e822a`](https://github.com/hydephp/hyde/commit/91e822a29efeb02b6d1d481c0123a8130e6b6685)
- Update to Framework v0.29.0-beta [`9a624de`](https://github.com/hydephp/hyde/commit/9a624de76b573dc5fc9911a4aae907cf9a5f2b1e)

#### [v0.28.1-beta](https://github.com/hydephp/hyde/compare/v0.28.0-beta...v0.28.1-beta)

> 29 May 2022

- Bump guzzlehttp/guzzle from 7.4.2 to 7.4.3 [`#167`](https://github.com/hydephp/hyde/pull/167)

#### [v0.28.0-beta](https://github.com/hydephp/hyde/compare/v0.27.1-beta...v0.28.0-beta)

> 23 May 2022

- Update Hyde to v0.28.x [`16da0b3`](https://github.com/hydephp/hyde/commit/16da0b3fc6791f6c8abd1b31d213a47e9d7bb5cb)
- Create FUNDING.yml [`1272144`](https://github.com/hydephp/hyde/commit/1272144340f0f123db9a3b9e002383409a255050)

#### [v0.27.1-beta](https://github.com/hydephp/hyde/compare/v0.27.0-beta...v0.27.1-beta)

> 21 May 2022

- Upgrade hyde/framework v0.27.5-beta =&gt; v0.27.11-beta [`809c700`](https://github.com/hydephp/hyde/commit/809c7000595af120fa39971d9c5f6770d046c4a5)

#### [v0.27.0-beta](https://github.com/hydephp/hyde/compare/v0.26.0-beta...v0.27.0-beta)

> 19 May 2022

- Update to v0.27.x [`ecc8fd1`](https://github.com/hydephp/hyde/commit/ecc8fd1cf052d4f2ac8ae798c2a8a778a46d605d)

#### [v0.26.0-beta](https://github.com/hydephp/hyde/compare/v0.25.0-beta...v0.26.0-beta)

> 18 May 2022

- Update to v0.26.x [`123bdeb`](https://github.com/hydephp/hyde/commit/123bdebd7d08541507a1ef3f92c5c0ea115f2edc)
- Breaking: Update config to v0.26.x-dev-master [`268b2a6`](https://github.com/hydephp/hyde/commit/268b2a6ed8be4a8f9efa7f977ae9ba25fd963bc3)
- Update framework to dev-master [`ddc37cf`](https://github.com/hydephp/hyde/commit/ddc37cf6d3e1ade1a3a121b165dc75b45e743d72)

#### [v0.25.0-beta](https://github.com/hydephp/hyde/compare/v0.24.1-beta...v0.25.0-beta)

> 17 May 2022

- Update frontend and framework files [`#161`](https://github.com/hydephp/hyde/pull/161)
- Update frontend and framework files [`#159`](https://github.com/hydephp/hyde/pull/159)
- Update to v0.25.x [`497d540`](https://github.com/hydephp/hyde/commit/497d540953c95adc8ec08df9424ff33328477d91)
- Automatic build update [`64c2bd6`](https://github.com/hydephp/hyde/commit/64c2bd6b106f19d0255c0ed2bf599e555a2cdd69)
- Automatic build update [`915cab8`](https://github.com/hydephp/hyde/commit/915cab8802758a7fbb2d31df7d8bfba3d1a781d2)

#### [v0.24.1-beta](https://github.com/hydephp/hyde/compare/v0.24.0-beta...v0.24.1-beta)

> 11 May 2022

- Update frontend and framework files [`#157`](https://github.com/hydephp/hyde/pull/157)
- Disable cache [`8a48e3e`](https://github.com/hydephp/hyde/commit/8a48e3e54bdda7b0568b189fd609bacb05d1298e)
- Update SECURITY.md [`958d92e`](https://github.com/hydephp/hyde/commit/958d92e4782a37b9f655e0fee50ad3941354fce2)
- Bump Hyde/Framework [`cfed837`](https://github.com/hydephp/hyde/commit/cfed837ad3b16b2691d83b4c8af70697df317e9b)

#### [v0.24.0-beta](https://github.com/hydephp/hyde/compare/v0.23.0-beta...v0.24.0-beta)

> 11 May 2022

- Update dependencies for release [`575338d`](https://github.com/hydephp/hyde/commit/575338d6caaafeb60aa14e94286726a1d590ddb5)

#### [v0.23.0-beta](https://github.com/hydephp/hyde/compare/v0.22.0-beta...v0.23.0-beta)

> 6 May 2022

- Update frontend and framework files [`#152`](https://github.com/hydephp/hyde/pull/152)
- Run cache after installing [`25f8581`](https://github.com/hydephp/hyde/commit/25f8581739417ff4e3f642cebdeb2ffca5cd5924)
- Update hyde/framework [`cc66395`](https://github.com/hydephp/hyde/commit/cc6639558289c3cd5c4d6307fbca380016b6ae57)

#### [v0.22.0-beta](https://github.com/hydephp/hyde/compare/v0.21.0-beta...v0.22.0-beta)

> 4 May 2022

- Update frontend and framework files [`#149`](https://github.com/hydephp/hyde/pull/149)
- Fix #146 by adding _pages to Tailwind content [`#148`](https://github.com/hydephp/hyde/pull/148)
- Add back _site to Tailwind content array [`#147`](https://github.com/hydephp/hyde/pull/147)
- Update frontend and framework files [`#143`](https://github.com/hydephp/hyde/pull/143)
- Merge pull request #148 from hydephp/caendesilva-patch-1 [`#146`](https://github.com/hydephp/hyde/issues/146)
- Fix #146 by adding _pages to Tailwind content [`#146`](https://github.com/hydephp/hyde/issues/146)
- Automatic build update [`5f656d0`](https://github.com/hydephp/hyde/commit/5f656d04ea94fc8d9fa63b99d681c561a503d1aa)
- Remove reliance on deprecated service [`71bb359`](https://github.com/hydephp/hyde/commit/71bb359fa959b17f175adff406dd86b9bfa6dfd5)

#### [v0.21.0-beta](https://github.com/hydephp/hyde/compare/v0.20.0-beta...v0.21.0-beta)

> 3 May 2022


#### [v0.20.0-beta](https://github.com/hydephp/hyde/compare/v0.19.0-beta...v0.20.0-beta)

> 3 May 2022

- Update max-width for blog posts [`#139`](https://github.com/hydephp/hyde/pull/139)
- Update config to v0.20.x [`87c6748`](https://github.com/hydephp/hyde/commit/87c6748825247bcc558fdbf1dbdad6cac70748b3)
- Rename workflow and jobs [`c67f2c0`](https://github.com/hydephp/hyde/commit/c67f2c08a25d28a7ec8520a2ccb79e3fa2227c11)

#### [v0.19.0-beta](https://github.com/hydephp/hyde/compare/v0.18.0-beta...v0.19.0-beta)

> 1 May 2022

- Update frontend assets [`#136`](https://github.com/hydephp/hyde/pull/136)
- Update frontend assets [`#134`](https://github.com/hydephp/hyde/pull/134)
- Add Laravel Mix #124 [`#129`](https://github.com/hydephp/hyde/pull/129)
- Fix #127 [`#127`](https://github.com/hydephp/hyde/issues/127)
- Clone repo directly to fix #133 [`#133`](https://github.com/hydephp/hyde/issues/133)
- Fix #131 [`#131`](https://github.com/hydephp/hyde/issues/131)
- Add Laravel Mix [`dc62438`](https://github.com/hydephp/hyde/commit/dc624383bdb4ca8d3e88209dc10c66d2c9e082a1)
- Remove laminas/laminas-text [`fa23c60`](https://github.com/hydephp/hyde/commit/fa23c60cfaa42805dfc6a04efb3794620d0239dc)
- Add PostCSS [`db399c4`](https://github.com/hydephp/hyde/commit/db399c439b0fe8f07ba45566a5d8825545160de7)

#### [v0.18.0-beta](https://github.com/hydephp/hyde/compare/v0.17.1-beta...v0.18.0-beta)

> 29 April 2022

- Fix https://github.com/hydephp/hydefront/issues/1 [`#1`](https://github.com/hydephp/hydefront/issues/1)
- Fix https://github.com/hydephp/hyde/issues/120 [`#120`](https://github.com/hydephp/hyde/issues/120)
- Reset the application only once [`8d2dec7`](https://github.com/hydephp/hyde/commit/8d2dec786d52117e778d6d2ed38595f3a1a887e4)
- Create build-and-update-hydefront.yml [`eca910c`](https://github.com/hydephp/hyde/commit/eca910c7bd45b47b4f4d8a369468c810533fba35)
- Implement https://github.com/hydephp/framework/issues/182 [`f0b6da0`](https://github.com/hydephp/hyde/commit/f0b6da09caa8e906dba549792cc6dfb48fdeee41)

#### [v0.17.1-beta](https://github.com/hydephp/hyde/compare/v0.17.0-beta...v0.17.1-beta)

> 28 April 2022

- Remove compiled files and fix wrong homepage layout [`f92e4b7`](https://github.com/hydephp/hyde/commit/f92e4b7afee871dd69570a6a1f5915a6bc26a041)

#### [v0.17.0-beta](https://github.com/hydephp/hyde/compare/v0.16.1-beta...v0.17.0-beta)

> 28 April 2022

- Remove GitHub test workflows from Hyde/Hyde, moving them into Hyde/Framework  [`#116`](https://github.com/hydephp/hyde/pull/116)
- Move Framework tests to the Hyde/Framework package [`#115`](https://github.com/hydephp/hyde/pull/115)
- Resolve https://github.com/hydephp/framework/issues/186 [`#186`](https://github.com/hydephp/framework/issues/186)
- Move tests to Framework [`eba459c`](https://github.com/hydephp/hyde/commit/eba459c6ea9ce717ae1d7490eeb73b8523c0834a)
- Remove deprecated trait [`87f1659`](https://github.com/hydephp/hyde/commit/87f1659179be5254d6650fed5bd1ded6ce312df5)
- Remove deprecated Setup directory [`f5a9be5`](https://github.com/hydephp/hyde/commit/f5a9be5218b7dc1378928d12bd7fafc80a377b1a)

#### [v0.16.1-beta](https://github.com/hydephp/hyde/compare/v0.16.0-beta...v0.16.1-beta)

> 28 April 2022

- Delete codeql as the JS has moved to HydeFront [`ef7e94e`](https://github.com/hydephp/hyde/commit/ef7e94e07c870e672ef39811addf87964c21df1c)
- Change test coverage to code reports [`b1fd3e9`](https://github.com/hydephp/hyde/commit/b1fd3e94221d1468d932a5eecee729662729fe34)
- Add more reporting outputs [`a4ac8e6`](https://github.com/hydephp/hyde/commit/a4ac8e696e31f7b4f4fd81c1be042853495267d8)

#### [v0.16.0-beta](https://github.com/hydephp/hyde/compare/v0.15.0-beta...v0.16.0-beta)

> 27 April 2022

- Update namespace [`50695fc`](https://github.com/hydephp/hyde/commit/50695fc097f3bab0d4380e6a07e2a87f7b937675)
- 0.15.x Update namespace [`8c084b9`](https://github.com/hydephp/hyde/commit/8c084b9e866f814d16e8085be65d9e0cb28d9663)

#### [v0.15.0-beta](https://github.com/hydephp/hyde/compare/v0.14.0-beta...v0.15.0-beta)

> 27 April 2022

- Remove files moved to CDN [`521d790`](https://github.com/hydephp/hyde/commit/521d79005537edb65ed5e740091385bd1a9d96c3)
- Update tests for removed frontend assets [`fa68c37`](https://github.com/hydephp/hyde/commit/fa68c3718b1454663089e3a53791b6996f5d1a1d)

#### [v0.14.0-beta](https://github.com/hydephp/hyde/compare/v0.13.0-beta...v0.14.0-beta)

> 21 April 2022

- Change update:resources command signature to update:assets [`#100`](https://github.com/hydephp/hyde/pull/100)
- Rename directory resources/frontend to resources/assets [`#99`](https://github.com/hydephp/hyde/pull/99)
- Fix https://github.com/hydephp/framework/issues/156 [`#156`](https://github.com/hydephp/framework/issues/156)
- Publish the assets [`330971d`](https://github.com/hydephp/hyde/commit/330971d30bc77dc8ff89c4b724af6bd6f9600eb3)
- Add the Markdown features tests [`71c0936`](https://github.com/hydephp/hyde/commit/71c093657af227ea7c58167a6ceaca9b291ecf38)
- Update composer dependencies [`8a1ebe4`](https://github.com/hydephp/hyde/commit/8a1ebe4480f9bc6fea553d8136c5c68fcec18b95)

#### [v0.13.0-beta](https://github.com/hydephp/hyde/compare/v0.12.0-beta...v0.13.0-beta)

> 20 April 2022

- Remove BrowserSync and other dependencies [`#93`](https://github.com/hydephp/hyde/pull/93)
- Create the tests [`f30c375`](https://github.com/hydephp/hyde/commit/f30c375c9c1c88182bff58d31ac430ce0029f35b)
- Republish the config [`c290249`](https://github.com/hydephp/hyde/commit/c290249cd52a0cc07211290bb49b4223a4687217)
- Update tests for 0.13.x [`f6de746`](https://github.com/hydephp/hyde/commit/f6de7469594dc3d40a94fa461a201371b0124e63)

#### [v0.12.0-beta](https://github.com/hydephp/hyde/compare/v0.11.0-beta...v0.12.0-beta)

> 19 April 2022

- Clean up Readme [`5eb9e5e`](https://github.com/hydephp/hyde/commit/5eb9e5ee114a55a76984fdd163f683770b53afad)
- Create the test [`7a02299`](https://github.com/hydephp/hyde/commit/7a02299b41ba45ac1b7fc25e0081ae891c136ca2)
- Add Features section [`85e8a4f`](https://github.com/hydephp/hyde/commit/85e8a4fd8275f8cf5b979dfed118d55164ecea92)

#### [v0.11.0-beta](https://github.com/hydephp/hyde/compare/v0.10.0-beta...v0.11.0-beta)

> 17 April 2022

- Add the realtime compiler extension [`90989c1`](https://github.com/hydephp/hyde/commit/90989c13a7cf87ef637a58e545acee16d8607716)
- Streamline Readme [`5860c04`](https://github.com/hydephp/hyde/commit/5860c0447e85571b5ead852ac1dfc5e3ff88fd80)

#### [v0.10.0-beta](https://github.com/hydephp/hyde/compare/v0.9.0-alpha...v0.10.0-beta)

> 12 April 2022

- Add darkmode support [`#86`](https://github.com/hydephp/hyde/pull/86)
- Remove the deprecated and unused service provider [`#85`](https://github.com/hydephp/hyde/pull/85)
- Updates the frontend and adds the tests for https://github.com/hydephp/framework/pull/102 [`#84`](https://github.com/hydephp/hyde/pull/84)
- Refactor tests [`#82`](https://github.com/hydephp/hyde/pull/82)
- Clean up repo [`#79`](https://github.com/hydephp/hyde/pull/79)
- Change blade source directory [`#75`](https://github.com/hydephp/hyde/pull/75)
- Bump composer packages [`#72`](https://github.com/hydephp/hyde/pull/72)
- Companion branch to https://github.com/hydephp/framework/pull/84 [`#71`](https://github.com/hydephp/hyde/pull/71)
- Internal build service refactor tie in [`#65`](https://github.com/hydephp/hyde/pull/65)
- Remove versioning from matrix, fix https://github.com/hydephp/framework/issues/93 [`#93`](https://github.com/hydephp/framework/issues/93)
- Publish the compiled assets [`7a24814`](https://github.com/hydephp/hyde/commit/7a248146f4ce3ba2296171af26760a141cbd912f)
- Update tests for Build Service refactor [`06c8048`](https://github.com/hydephp/hyde/commit/06c80480a6a5fc2221e4fd3d457c06a3748cbedb)
- Update the test [`874c2a4`](https://github.com/hydephp/hyde/commit/874c2a41aebaab4510e70e2430dfb283607129b1)

#### [v0.9.0-alpha](https://github.com/hydephp/hyde/compare/v0.8.0-alpha...v0.9.0-alpha)

> 7 April 2022

- Change where and how stylesheets and scripts are stored and handled [`#63`](https://github.com/hydephp/hyde/pull/63)
- Move the resource files [`fb3b660`](https://github.com/hydephp/hyde/commit/fb3b660bd5377d60658bdd83ac38a6bbab80fe0e)
- Add the test [`51d99b2`](https://github.com/hydephp/hyde/commit/51d99b2e0d752266d4cf6161cb6c5b57ada153de)
- Publish the resources [`bf3b20d`](https://github.com/hydephp/hyde/commit/bf3b20d99d62b913d8d96bd6250e47de8e2a126a)

#### [v0.8.0-alpha](https://github.com/hydephp/hyde/compare/v0.7.3-alpha...v0.8.0-alpha)

> 3 April 2022

- Clean up test code and fix mismatched test namespace [`#59`](https://github.com/hydephp/hyde/pull/59)
- Update the navigation menu frontend [`#58`](https://github.com/hydephp/hyde/pull/58)
- Add Changelog.md [`9bff522`](https://github.com/hydephp/hyde/commit/9bff522faa4970d5742d3238f11f0d3b0335fa77)
- Create CODE_OF_CONDUCT.md [`ffde383`](https://github.com/hydephp/hyde/commit/ffde383cdd51d9e8a691f3d17c7d09fb5d174a33)
- Create a test runner with a backup feature [`605ed46`](https://github.com/hydephp/hyde/commit/605ed463809e7da716512709017f8a62b8d93167)

#### [v0.7.3-alpha](https://github.com/hydephp/hyde/compare/v0.7.2-alpha...v0.7.3-alpha)

> 1 April 2022

- Fix outdated welcome page links [`323ea17`](https://github.com/hydephp/hyde/commit/323ea176294f517a335e0c8ee7e1c1af0b46981d)

#### [v0.7.2-alpha](https://github.com/hydephp/hyde/compare/v0.7.1-alpha...v0.7.2-alpha)

> 1 April 2022

- Create the test [`0a8a00e`](https://github.com/hydephp/hyde/commit/0a8a00e9b6516f655495dc0fd1365a92283917ef)
- Create the test [`f445bd5`](https://github.com/hydephp/hyde/commit/f445bd57aff02d1619320ecd5dcbea9a09112c68)
- Implement the --force option [`2fe366c`](https://github.com/hydephp/hyde/commit/2fe366cfc7ee418eeb4305cfa4dfecac0053a0ab)

#### [v0.7.1-alpha](https://github.com/hydephp/hyde/compare/v0.7.0-alpha...v0.7.1-alpha)

> 1 April 2022

- Add SASS as a dev dependency [`#55`](https://github.com/hydephp/hyde/pull/55)

#### [v0.7.0-alpha](https://github.com/hydephp/hyde/compare/v0.6.0-alpha...v0.7.0-alpha)

> 1 April 2022

- Remove _authors and _drafts directories #48 [`#53`](https://github.com/hydephp/hyde/pull/53)
- Create the first two tests [`fdd197c`](https://github.com/hydephp/hyde/commit/fdd197c0e8ef6657f289ac3a3c20dbc654a6c9f8)
- Create the test [`6c43c41`](https://github.com/hydephp/hyde/commit/6c43c414a2bb029e61160067c5f479d357271c98)
- Update author yml config path [`67af952`](https://github.com/hydephp/hyde/commit/67af952e2d5f5c909e68ec8138a601c8349fe61c)

#### [v0.6.0-alpha](https://github.com/hydephp/hyde/compare/v0.5.0-alpha...v0.6.0-alpha)

> 30 March 2022

- Move scripts into app.js [`#51`](https://github.com/hydephp/hyde/pull/51)
- Update command class names [`#49`](https://github.com/hydephp/hyde/pull/49)
- Update to latest Framework version [`24d666d`](https://github.com/hydephp/hyde/commit/24d666d4d326dfcab92e7a21aca7c0d0e551897a)
- Add the test [`3554c33`](https://github.com/hydephp/hyde/commit/3554c332756e7b44dad0921f45cb00075519125f)
- 0.6.0 Add the test [`0e99c56`](https://github.com/hydephp/hyde/commit/0e99c569e7fe8bb866890e7869bf5de74a987eab)

#### [v0.5.0-alpha](https://github.com/hydephp/hyde/compare/v0.4.1-alpha...v0.5.0-alpha)

> 25 March 2022

- Remove legacy test [`7cee208`](https://github.com/hydephp/hyde/commit/7cee2080f57a3d8bc9cd4a6479ab71486dfdabf6)
- Add tests for installer [`dfed2d2`](https://github.com/hydephp/hyde/commit/dfed2d2ee55bf51694f138f8aa1e7ef2794c7fbf)
- #37 Add more tests: DebugCommand [`ab1758f`](https://github.com/hydephp/hyde/commit/ab1758fb5781b92e6814382b2e0613ad8ab27fd7)

#### [v0.4.1-alpha](https://github.com/hydephp/hyde/compare/v0.4.0-alpha...v0.4.1-alpha)

> 25 March 2022

- Bump minimist from 1.2.5 to 1.2.6 [`#47`](https://github.com/hydephp/hyde/pull/47)
- #37 Add more tests: HydeServiceProvider [`ae8673f`](https://github.com/hydephp/hyde/commit/ae8673f6ae4afde43395e52ea2023a48b3bf73b4)
- Inline the stream variable to fix missing file error [`bca234c`](https://github.com/hydephp/hyde/commit/bca234c40ba35f3d90c4a4cb756d75070938ec9d)
- Update to Framework 0.5.1 [`449e051`](https://github.com/hydephp/hyde/commit/449e0511e43b9b735ca4cc0cea6e3da037c8c572)

#### [v0.4.0-alpha](https://github.com/hydephp/hyde/compare/v0.3.3-alpha...v0.4.0-alpha)

> 24 March 2022

- 0.4.0 Update which adds several new tests tying into framework v0.5.0 [`#46`](https://github.com/hydephp/hyde/pull/46)
- Format tests to PSR2 [`e08aba6`](https://github.com/hydephp/hyde/commit/e08aba6ef4d7214990dc5f8f3869761e61ee553f)
- Create test for publish homepage command [`4e0f828`](https://github.com/hydephp/hyde/commit/4e0f828bc7284c38c10e46ed1e47a48b7c316c60)
- Update framework version to tie into new release [`e4944c8`](https://github.com/hydephp/hyde/commit/e4944c8442a992b26585d8933fe0d01282a5ee7e)

#### [v0.3.3-alpha](https://github.com/hydephp/hyde/compare/v0.3.2-alpha...v0.3.3-alpha)

> 23 March 2022

- Unlock framework version to patch error in last release [`4423513`](https://github.com/hydephp/hyde/commit/4423513ba82672c4bf19042330e8d684559209bb)
- Update config [`a480b0a`](https://github.com/hydephp/hyde/commit/a480b0a876b260d7a6e271713ad5489fb99581e2)

#### [v0.3.2-alpha](https://github.com/hydephp/hyde/compare/v0.3.1-alpha...v0.3.2-alpha)

> 23 March 2022

- Increase link contrast to fix accessibility issue [`#45`](https://github.com/hydephp/hyde/pull/45)
- Add the Site URL setting [`05211b9`](https://github.com/hydephp/hyde/commit/05211b9c55859588ba402b48c38c5d5e2fdd21a5)
- Update config [`8c0d331`](https://github.com/hydephp/hyde/commit/8c0d33158941579cf309847e865c8a0dda3772ad)
- Remove dev files from gitignore [`f52d471`](https://github.com/hydephp/hyde/commit/f52d4718b7764e4e379cd931bbd195be4369ffba)

#### [v0.3.1-alpha](https://github.com/hydephp/hyde/compare/v0.3.0-alpha...v0.3.1-alpha)

> 23 March 2022

- Replace the default empty blog listing index page with a new welcome screen [`#44`](https://github.com/hydephp/hyde/pull/44)
- Replace the default page [`d747290`](https://github.com/hydephp/hyde/commit/d74729050fa36d988b36a978cedaba1bf3e77a4f)
- Add the links [`b8cd49c`](https://github.com/hydephp/hyde/commit/b8cd49c2927c70b0c11e6d990c47adebdcdba8e4)
- Add info about the new build --clean option [`efca81f`](https://github.com/hydephp/hyde/commit/efca81f9d222a8409bdd079f67d1048e48c9d30a)

#### [v0.3.0-alpha](https://github.com/hydephp/hyde/compare/v0.2.1-alpha...v0.3.0-alpha)

> 22 March 2022

- v0.3 - Hyde Core Separation - Contains breaking changes [`#36`](https://github.com/hydephp/hyde/pull/36)
- Hyde Core Separation - Contains breaking changes [`#35`](https://github.com/hydephp/hyde/pull/35)
- Allow the view source directory to be modified at runtime [`#34`](https://github.com/hydephp/hyde/pull/34)
- Add a path helper to unify path referencing [`#33`](https://github.com/hydephp/hyde/pull/33)
- Successfully moved Core into temporary package [`d5a8dc1`](https://github.com/hydephp/hyde/commit/d5a8dc15db87a980144fe3330d778ad69e9e61aa)
- Move app font to vendor [`e43da1d`](https://github.com/hydephp/hyde/commit/e43da1d1845f2837af0273df59f9f8623cce8b2b)
- Remove legacy stubs and test [`8740cc2`](https://github.com/hydephp/hyde/commit/8740cc2996fc9e66df40b2ff6b0f58ec0c32fc34)

#### [v0.2.1-alpha](https://github.com/hydephp/hyde/compare/v0.2.0-alpha...v0.2.1-alpha)

> 21 March 2022

- Add a customizable footer [`#31`](https://github.com/hydephp/hyde/pull/31)
- Adds a customizable footer [`09813cf`](https://github.com/hydephp/hyde/commit/09813cf6810a8e94e8b4301e9c460aa794ad656d)
- Clarify comments in configuration file [`09a7e64`](https://github.com/hydephp/hyde/commit/09a7e6480db8ff72af02db85d160c656ca19fee7)
- Compile frontend assets [`fdb68d5`](https://github.com/hydephp/hyde/commit/fdb68d537e7dd671c033c295815a7c9994e5381b)

#### [v0.2.0-alpha](https://github.com/hydephp/hyde/compare/v0.1.1-pre.patch...v0.2.0-alpha)

> 21 March 2022

- Add responsive navigation to resolve #7 [`#30`](https://github.com/hydephp/hyde/pull/30)
- Add support for images [`#29`](https://github.com/hydephp/hyde/pull/29)
- Fix bug #22 where the feed was not sorting the posts by date [`#28`](https://github.com/hydephp/hyde/pull/28)
- Overhaul the navigation menu to add configuration options [`#27`](https://github.com/hydephp/hyde/pull/27)
- Improve the front matter parser to fix #21 [`#23`](https://github.com/hydephp/hyde/pull/23)
- Check for the app env in the .env file [`#20`](https://github.com/hydephp/hyde/pull/20)
- Add the Torchlight badge automatically [`#19`](https://github.com/hydephp/hyde/pull/19)
- #14 Add publishable 404 pages [`#18`](https://github.com/hydephp/hyde/pull/18)
- Create Validator command to help catch any issues in the setup [`#17`](https://github.com/hydephp/hyde/pull/17)
- Merge pull request #30 from hydephp/7-feature-make-the-navigation-menu-responsive [`#7`](https://github.com/hydephp/hyde/issues/7)
- Add a navigation menu blacklist, fixes #26 [`#26`](https://github.com/hydephp/hyde/issues/26)
- Fix #25, automatically add link to docs [`#25`](https://github.com/hydephp/hyde/issues/25)
- Merge pull request #23 from hydephp/21-bug-front-matter-parser-not-stripping-quotes [`#21`](https://github.com/hydephp/hyde/issues/21)
- Improve the front matter parser to fix #21 [`#21`](https://github.com/hydephp/hyde/issues/21)
- Fix #15, remove redundant values from created file [`#15`](https://github.com/hydephp/hyde/issues/15)
- Add the stubs [`5416fd2`](https://github.com/hydephp/hyde/commit/5416fd22198cc3e5912911aa8547e3a3aa92f734)
- Add tests [`9284a5a`](https://github.com/hydephp/hyde/commit/9284a5a037c8a6afd72dca12d4109b308a432d0b)
- Implement  #16, add custom navigation links [`1007d0d`](https://github.com/hydephp/hyde/commit/1007d0de0a15064157ad7bf4bb801abfb1d2281e)

#### [v0.1.1-pre.patch](https://github.com/hydephp/hyde/compare/v0.1.1-pre...v0.1.1-pre.patch)

> 19 March 2022

- Patches #12, Sev2 Bug: Compiler not using Markdown [`ad640de`](https://github.com/hydephp/hyde/commit/ad640de7bc603330846e025588ea477df55f3962)

#### [v0.1.1-pre](https://github.com/hydephp/hyde/compare/v0.1.0-pre...v0.1.1-pre)

> 19 March 2022

- Merge 1.x [`#2`](https://github.com/hydephp/hyde/pull/2)
- Fix #6, handle missing docs index [`#6`](https://github.com/hydephp/hyde/issues/6)
- Update installation instructions [`785a450`](https://github.com/hydephp/hyde/commit/785a450c2f72faeb3c87a4863e3a27564eece60a)
- Add command for making arbitrary navigation links [`3970d57`](https://github.com/hydephp/hyde/commit/3970d5712da4478b5ce2adb828e7cedd1b443526)
- Create codeql-analysis.yml [`5a6f7ad`](https://github.com/hydephp/hyde/commit/5a6f7ad1ae218a1138f955faf569fe9aecb54e2f)

#### v0.1.0-pre

> 18 March 2022

- Delete _pages directory [`#1`](https://github.com/hydephp/hyde/pull/1)
- Initial Commit [`109bddb`](https://github.com/hydephp/hyde/commit/109bddb7b6144ba704e283c220754759276f1a23)
- Add Torchlight support [`300e06f`](https://github.com/hydephp/hyde/commit/300e06fc6d8600ed8db1b3407a500b5189236eff)
- Add the Logo [`1d06347`](https://github.com/hydephp/hyde/commit/1d063479460fdb4cf621f606b2beafaa7d7d0c61)

### Hyde/Framework Archive (pre v0.35.0)


All notable changes to this project will be documented in this file. Dates are displayed in UTC.

Generated by [`auto-changelog`](https://github.com/CookPete/auto-changelog).

#### [v0.34.0](https://github.com/hydephp/framework/compare/v0.33.0-beta...v0.34.0)

> 6 June 2022

- Deprecate Hyde::features(), use Hyde::hasFeature() instead [`#523`](https://github.com/hydephp/framework/pull/523)
- Create image link helper, fix #434 [`#522`](https://github.com/hydephp/framework/pull/522)
- Create a PageModel contract and helpers to get parsed model collections [`#521`](https://github.com/hydephp/framework/pull/521)
- Merge pull request #522 from hydephp/create-image-file-object [`#434`](https://github.com/hydephp/framework/issues/434)
- Add image path helper, fix #434 [`#434`](https://github.com/hydephp/framework/issues/434)
- Fix #516 Add Composer validation to the test suite [`#516`](https://github.com/hydephp/framework/issues/516)
- Move the static::all() helper to AbstractPage [`c726ad7`](https://github.com/hydephp/framework/commit/c726ad73cf30eff59bc2425f8c35eacbe499f2e4)
- Create MarkdownPost::latest() [`e6d9e4a`](https://github.com/hydephp/framework/commit/e6d9e4a1b2689e58cef44d7109c0594bc3df972f)
- Implement MarkdownPost::all() [`cda2010`](https://github.com/hydephp/framework/commit/cda201052547935d545869d651bc297f45617011)

#### [v0.33.0-beta](https://github.com/hydephp/framework/compare/v0.32.1-beta...v0.33.0-beta)

> 4 June 2022


#### [v0.32.1-beta](https://github.com/hydephp/framework/compare/v0.32.0-beta...v0.32.1-beta)

> 4 June 2022

- Move back hyde/realtime-compiler to hyde/hyde [`#517`](https://github.com/hydephp/framework/pull/517)
- Update composer.lock [`246da42`](https://github.com/hydephp/framework/commit/246da42b693a07175e861bf653763cfa9af42ec2)
- Update composer.lock [`9e835b6`](https://github.com/hydephp/framework/commit/9e835b6cec2ef64fb81d9378a3bab3c090f460bf)

#### [v0.32.0-beta](https://github.com/hydephp/framework/compare/v0.31.1-beta...v0.32.0-beta)

> 4 June 2022

- Refactor to use Laravel cache helper instead of custom implementation [`#514`](https://github.com/hydephp/framework/pull/514)
- Improve metadata for featured post images [`#512`](https://github.com/hydephp/framework/pull/512)
- Skip generating auxiliary files in the main built loop when there is no underlying content [`#511`](https://github.com/hydephp/framework/pull/511)
- Fix: #506: Move ext-simplexml in composer.json to suggest as it is not a strict dependency [`#510`](https://github.com/hydephp/framework/pull/510)
- Rewrite Realtime Compiler [`#508`](https://github.com/hydephp/framework/pull/508)
- Fix #496: Missing image "contentUrl" metadata [`#496`](https://github.com/hydephp/framework/issues/496)
- Don't create search files when there are no pages [`#482`](https://github.com/hydephp/framework/issues/482)
- Update Hyde Realtime Compiler to v2.0 [`f917319`](https://github.com/hydephp/framework/commit/f917319149bfce3249f9921b6bc3ecf0a6307f42)
- Delete RELEASE-NOTES-DRAFT.md [`9853526`](https://github.com/hydephp/framework/commit/9853526ec23b8fe7a325126d8e740e354a1b4eb2)
- Remove pre-check as package is always included [`076a1be`](https://github.com/hydephp/framework/commit/076a1bef2ae68117d092b38d9ee8d6f2fef64172)

#### [v0.31.1-beta](https://github.com/hydephp/framework/compare/v0.31.0-beta...v0.31.1-beta)

> 3 June 2022


#### [v0.31.0-beta](https://github.com/hydephp/framework/compare/v0.30.1-beta...v0.31.0-beta)

> 2 June 2022

- Fix #499: Make the search dialog positioning fixed [`#503`](https://github.com/hydephp/framework/pull/503)
- Make documentation pages smarter [`#501`](https://github.com/hydephp/framework/pull/501)
- Link to markdown source files [`#498`](https://github.com/hydephp/framework/pull/498)
- Fix #490 Make heading permalinks visible [`#493`](https://github.com/hydephp/framework/pull/493)
- Add Markdown Post/Preprocessors  [`#488`](https://github.com/hydephp/framework/pull/488)
- Merge pull request #503 from hydephp/499-make-the-search-menu-dialog-position-fixed [`#499`](https://github.com/hydephp/framework/issues/499)
- Fix #499: Make the search dialog positioning fixed [`#499`](https://github.com/hydephp/framework/issues/499)
- Merge pull request #493 from hydephp/make-heading-permalinks-visible [`#490`](https://github.com/hydephp/framework/issues/490)
- Fix #490 Make heading permalinks visible [`#490`](https://github.com/hydephp/framework/issues/490)
- Merge unit tests into single feature test [`c455d1c`](https://github.com/hydephp/framework/commit/c455d1c0246d9361fd2115528ce616ea797915ea)
- Use the same static transformation instead of DOM [`bdba273`](https://github.com/hydephp/framework/commit/bdba27386df05a46ca27071601aed1f6f3f00b59)
- Document the edit button feature [`dc0d9d7`](https://github.com/hydephp/framework/commit/dc0d9d750e0b61701557472ebd1ce1b1e556058a)

#### [v0.30.1-beta](https://github.com/hydephp/framework/compare/v0.30.0-beta...v0.30.1-beta)

> 31 May 2022

- Fix support for outputting documentation pages to root output directory [`#480`](https://github.com/hydephp/framework/pull/480)
- Fix https://github.com/hydephp/framework/issues/462#issuecomment-1142408337 [`#462`](https://github.com/hydephp/framework/issues/462)
- Fix bug #462 caused by trailing slash in docs path [`6be5055`](https://github.com/hydephp/framework/commit/6be5055633b4ef9be358fdc82dfcc5fc1aad068b)

#### [v0.30.0-beta](https://github.com/hydephp/framework/compare/v0.29.5-beta...v0.30.0-beta)

> 31 May 2022

- Add inline Blade support to markdown [`#478`](https://github.com/hydephp/framework/pull/478)
- Create page and document Blade-supported Markdown [`0d7ae0f`](https://github.com/hydephp/framework/commit/0d7ae0f213eba74a61257f9207f184169a36127d)
- Add base tests [`ae4b0dc`](https://github.com/hydephp/framework/commit/ae4b0dc24568483ed2be4330c290839f4382571a)
- Sketch out the service class [`4b88214`](https://github.com/hydephp/framework/commit/4b8821447f7375d2cae20a685b76a8102972ee40)

#### [v0.29.5-beta](https://github.com/hydephp/framework/compare/v0.29.4-beta...v0.29.5-beta)

> 31 May 2022

- Bump HydeFront to v1.10 [`0f28947`](https://github.com/hydephp/framework/commit/0f28947f2b197177b1b30626d161408d46f71335)

#### [v0.29.4-beta](https://github.com/hydephp/framework/compare/v0.29.3-beta...v0.29.4-beta)

> 30 May 2022

- Add color-scheme meta, fix #460 [`#460`](https://github.com/hydephp/framework/issues/460)
- Try to figure out why Codecov is not working [`9d3371c`](https://github.com/hydephp/framework/commit/9d3371cab5606c280261c2f3e209beeff3289f5a)
- Revert codecov changes [`b253969`](https://github.com/hydephp/framework/commit/b2539690fa4d013b06082a162f05816eff99e6bd)

#### [v0.29.3-beta](https://github.com/hydephp/framework/compare/v0.29.2-beta...v0.29.3-beta)

> 30 May 2022

- Fix Bug #471: og:title and twitter:title should use the page title, and only use config one as fallback [`#473`](https://github.com/hydephp/framework/pull/473)
- Fix bug #471, make title metadata dynamic [`b9ac1c8`](https://github.com/hydephp/framework/commit/b9ac1c8d1fa0c484d2d95fad891c2c3c5c7f039c)
- Make dynamic meta title use title property instead [`6aaa612`](https://github.com/hydephp/framework/commit/6aaa612b80600ae4ef8136fb779623b66206119b)

#### [v0.29.2-beta](https://github.com/hydephp/framework/compare/v0.29.1-beta...v0.29.2-beta)

> 30 May 2022

- Add !important to style override [`3e28b1d`](https://github.com/hydephp/framework/commit/3e28b1dcd91802596edf5dfa454fe2178432688f)

#### [v0.29.1-beta](https://github.com/hydephp/framework/compare/v0.29.0-beta...v0.29.1-beta)

> 30 May 2022

- Use the config defined output path [`927072e`](https://github.com/hydephp/framework/commit/927072e725624d39239845a681e744e2d309694c)
- Update Readme heading to "The Core Framework" [`7a89486`](https://github.com/hydephp/framework/commit/7a89486509ea68071cb5268956dd771766bf327a)

#### [v0.29.0-beta](https://github.com/hydephp/framework/compare/v0.28.1-beta...v0.29.0-beta)

> 30 May 2022

- Load HydeFront v1.9.x needed for HydeSearch [`#468`](https://github.com/hydephp/framework/pull/468)
- Make the search feature configurable and toggleable [`#467`](https://github.com/hydephp/framework/pull/467)
- Add the HydeSearch frontend integration for documentation pages [`#465`](https://github.com/hydephp/framework/pull/465)
- Create the backend search index generation for documentation pages [`#459`](https://github.com/hydephp/framework/pull/459)
- Bump guzzlehttp/guzzle from 7.4.2 to 7.4.3 [`#456`](https://github.com/hydephp/framework/pull/456)
- Refactor inline styles to HydeFront Sass [`86fff1d`](https://github.com/hydephp/framework/commit/86fff1d9dc7f5d5e5ca171cf79517af0d2fb1639)
- Begin sketching out the class [`ed131bd`](https://github.com/hydephp/framework/commit/ed131bd4196afbcf1684bb999c5a7fe98d1948b8)
- Extract search widget to component [`420f662`](https://github.com/hydephp/framework/commit/420f662a3040cc4c0f3f8e48d6a350686fb02803)

#### [v0.28.1-beta](https://github.com/hydephp/framework/compare/v0.28.0-beta-pre...v0.28.1-beta)

> 25 May 2022

- Fix #450: Add custom exceptions [`#454`](https://github.com/hydephp/framework/pull/454)
- Refactor author configuration system [`#449`](https://github.com/hydephp/framework/pull/449)
- Merge pull request #454 from hydephp/450-add-custom-exceptions [`#450`](https://github.com/hydephp/framework/issues/450)
- Remove AuthorService [`9f9d64d`](https://github.com/hydephp/framework/commit/9f9d64dc4f232e6c0a695088cd43dc28f8535fc3)
- Clean up code [`f8452b9`](https://github.com/hydephp/framework/commit/f8452b9d697505733f147bf3f59e92abfb307727)
- Create FileConflictException [`02d534c`](https://github.com/hydephp/framework/commit/02d534cd801ed19fac264373685c30b3f6858c34)

#### [v0.28.0-beta-pre](https://github.com/hydephp/framework/compare/v0.28.0-beta...v0.28.0-beta-pre)

> 22 May 2022

#### [v0.28.0-beta](https://github.com/hydephp/framework/compare/v0.27.12-beta...v0.28.0-beta)

> 23 May 2022

- Refactor author configuration system [`#449`](https://github.com/hydephp/framework/pull/449)
- Refactor configuration to use snake_case for all options, and extract documentation settings to own file [`#444`](https://github.com/hydephp/framework/pull/444)
- Remove AuthorService [`9f9d64d`](https://github.com/hydephp/framework/commit/9f9d64dc4f232e6c0a695088cd43dc28f8535fc3)
- Extract documentation configuration options to docs.php [`92b9ae5`](https://github.com/hydephp/framework/commit/92b9ae5fc4f2c7743206ebcfce48d81e4df7746d)
- Use the snake_case config format [`f578855`](https://github.com/hydephp/framework/commit/f578855047113c3181c9869f1ec9d4d521c3bd62)

#### [v0.27.12-beta](https://github.com/hydephp/framework/compare/v0.27.11-beta...v0.27.12-beta)

> 22 May 2022

- Code cleanup without affecting functionality  [`#440`](https://github.com/hydephp/framework/pull/440)
- Add missing return type declarations [`684b792`](https://github.com/hydephp/framework/commit/684b792796e330c958a312d914057771eb72f2da)
- Add PHPDoc comments with @throws tags [`ae44806`](https://github.com/hydephp/framework/commit/ae44806cb3c23249bc68a39bd1ede6fa0c4e8e56)

#### [v0.27.11-beta](https://github.com/hydephp/framework/compare/v0.27.10-beta...v0.27.11-beta)

> 21 May 2022

- Fix #429: Add page priorities to sitemap generation [`#437`](https://github.com/hydephp/framework/pull/437)
- Merge pull request #437 from hydephp/add-dynamic-page-priorities-for-sitemap [`#429`](https://github.com/hydephp/framework/issues/429)
- Add page priority support [`0bfbbba`](https://github.com/hydephp/framework/commit/0bfbbba07fd8d1720fe6a693089e62dbc0dc018a)

#### [v0.27.10-beta](https://github.com/hydephp/framework/compare/v0.27.9-beta...v0.27.10-beta)

> 20 May 2022

- Improve RSS image handling and feed and sitemap generation processes [`#435`](https://github.com/hydephp/framework/pull/435)
- Create HydeBuildRssFeedCommand.php [`ac4788f`](https://github.com/hydephp/framework/commit/ac4788f987cb517d51a6d0a4fddc5684777c9a0a)
- Create build:sitemap command [`82c73a3`](https://github.com/hydephp/framework/commit/82c73a392350dff171b496220d8d1f70d363102d)
- Fetch information for local images [`a10c1c3`](https://github.com/hydephp/framework/commit/a10c1c361852154e9eb52947b003a65ede09c3ef)

#### [v0.27.9-beta](https://github.com/hydephp/framework/compare/v0.27.8-beta...v0.27.9-beta)

> 20 May 2022

- Rename and restructure internal hooks [`0562ae3`](https://github.com/hydephp/framework/commit/0562ae3558363afddfeb63a7148f967940ed4966)
- Update test code formatting [`1a9dcaf`](https://github.com/hydephp/framework/commit/1a9dcaf670a9757985013c7c3a3e01fa93f75579)
- Add sitemap link test [`9ba7b10`](https://github.com/hydephp/framework/commit/9ba7b109560881867ee9ba81a5e37bb10b370616)

#### [v0.27.8-beta](https://github.com/hydephp/framework/compare/v0.27.7-beta...v0.27.8-beta)

> 19 May 2022

- Update the tests [`a80593e`](https://github.com/hydephp/framework/commit/a80593e1ac6fc79c3d78ea2d736c89955e6b6805)

#### [v0.27.7-beta](https://github.com/hydephp/framework/compare/v0.27.6-beta...v0.27.7-beta)

> 19 May 2022

- Normalize the site URL [`a4b9ce7`](https://github.com/hydephp/framework/commit/a4b9ce7a32321e3e67df5aaed477fbfc54c6c524)

#### [v0.27.6-beta](https://github.com/hydephp/framework/compare/v0.27.5-beta...v0.27.6-beta)

> 19 May 2022

- Add deployment documentation [`4b188f2`](https://github.com/hydephp/framework/commit/4b188f20848e87cd3b3e77af9cdde5b373e2e4d3)
- Merge sections to be more compact [`baadd48`](https://github.com/hydephp/framework/commit/baadd4891d719123720f8bc79a1a82a4837e547e)
- Restructure document flow [`40f4a3d`](https://github.com/hydephp/framework/commit/40f4a3d37b835b40b392f5f72a4ab46563df5042)

#### [v0.27.5-beta](https://github.com/hydephp/framework/compare/v0.27.4-beta...v0.27.5-beta)

> 19 May 2022

- Fix bug where categorized documentation sidebar items were not sorted [`#422`](https://github.com/hydephp/framework/pull/422)
- Fix #367: Add upcoming documentation files [`#367`](https://github.com/hydephp/framework/issues/367)
- Create building-your-site.md [`6989bd5`](https://github.com/hydephp/framework/commit/6989bd59d33d84cebf3e0ef134f4107d149c6fd5)
- Update documentation page orders [`b38c58b`](https://github.com/hydephp/framework/commit/b38c58bba32312d932d1a005b3015b3ce9dd7329)

#### [v0.27.4-beta](https://github.com/hydephp/framework/compare/v0.27.3-beta...v0.27.4-beta)

> 19 May 2022

- Fix #419: Add meta links to the RSS feed [`#419`](https://github.com/hydephp/framework/issues/419)
- Refactor internal helpers to be public static [`283e5d2`](https://github.com/hydephp/framework/commit/283e5d2154862f114e82f1e5e036924d449e7ebf)
- Add page slug for compatibility, fixing bug where Blade pages did not get canonical link tags [`d3ac8e4`](https://github.com/hydephp/framework/commit/d3ac8e492bb01ba538111ba8c7f4dfb48cbc5785)

#### [v0.27.3-beta](https://github.com/hydephp/framework/compare/v0.27.2-beta...v0.27.3-beta)

> 19 May 2022

- Add unit test for fluent Markdown post helpers [`2a3b90b`](https://github.com/hydephp/framework/commit/2a3b90bbf2ffab9709a49447b9a4aa80cd14ca9e)
- Add Author::getName() unit test [`64616a6`](https://github.com/hydephp/framework/commit/64616a6d24d8335e890bde35c8fafa37ef9bb4ba)
- Change RSS feed default filename to feed.xml [`d545b07`](https://github.com/hydephp/framework/commit/d545b07130cb58c42cb9701b3c2322ac133e617e)

#### [v0.27.2-beta](https://github.com/hydephp/framework/compare/v0.27.1-beta...v0.27.2-beta)

> 19 May 2022

- Add RSS feed for Markdown blog posts [`#413`](https://github.com/hydephp/framework/pull/413)
- Add the RSSFeedService test [`a21596f`](https://github.com/hydephp/framework/commit/a21596f68792d313c551789f713950a6c2410975)
- Add the initial channel items [`9cb9b30`](https://github.com/hydephp/framework/commit/9cb9b302662de3d1dc80ba0ea09a48c3a53f2e78)
- Update sitemap tests and add rss feed tests [`fe93f5b`](https://github.com/hydephp/framework/commit/fe93f5b7cd1dea1f3bbb5a851b8185e5288f50de)

#### [v0.27.1-beta](https://github.com/hydephp/framework/compare/v0.27.0-beta...v0.27.1-beta)

> 18 May 2022

- Fix #403: Remove @HydeConfigVersion annotation from config/hyde.php [`#408`](https://github.com/hydephp/framework/pull/408)
- Merge pull request #408 from hydephp/remove-hydeconfigversion-annotation-from-hyde-config [`#403`](https://github.com/hydephp/framework/issues/403)
- Remove HydeConfigVersion annotation [`84b1602`](https://github.com/hydephp/framework/commit/84b1602fc3280ef66637799c8aaa9d9513c3142c)

#### [v0.27.0-beta](https://github.com/hydephp/framework/compare/v0.26.0-beta...v0.27.0-beta)

> 18 May 2022

- Add sitemap.xml generation [`#404`](https://github.com/hydephp/framework/pull/404)
- Add SitemapService tests [`ce5d8ed`](https://github.com/hydephp/framework/commit/ce5d8ed089546a8262e637d3ce399bf190672ba0)
- Refactor shared code into new helper [`46f41d6`](https://github.com/hydephp/framework/commit/46f41d6848a5562006f4290aa00df221d25d815a)
- Create basic sitemap generator [`1f66928`](https://github.com/hydephp/framework/commit/1f669282d727042df5074f0182bf5e0563d07a91)

#### [v0.26.0-beta](https://github.com/hydephp/framework/compare/v0.25.0-beta...v0.26.0-beta)

> 18 May 2022

- Fix #398: Remove the deprecated Metadata model [`#400`](https://github.com/hydephp/framework/pull/400)
- Fix #379: Extract menu logo to component [`#396`](https://github.com/hydephp/framework/pull/396)
- Update helper namespaces [`#395`](https://github.com/hydephp/framework/pull/395)
- Fix #385: Move page parsers into models/parsers namespace [`#394`](https://github.com/hydephp/framework/pull/394)
- Remove redundancy and merge Meta and Metadata models #384 [`#390`](https://github.com/hydephp/framework/pull/390)
- Unify the $page property and add a fluent metadata helper  [`#388`](https://github.com/hydephp/framework/pull/388)
- Merge pull request #400 from hydephp/398-remove-legacy-metadata-model [`#398`](https://github.com/hydephp/framework/issues/398)
- Merge pull request #396 from hydephp/extract-navigation-menu-logo-to-component-to-make-it-easier-to-customize [`#379`](https://github.com/hydephp/framework/issues/379)
- Fix #379: Extract menu logo to component [`#379`](https://github.com/hydephp/framework/issues/379) [`#379`](https://github.com/hydephp/framework/issues/379)
- Merge pull request #394 from hydephp/385-move-page-parsers-into-a-namespace [`#385`](https://github.com/hydephp/framework/issues/385)
- Fix #385: Move page parsers into a namespace [`#385`](https://github.com/hydephp/framework/issues/385)
- Fix #382: Unify the $page property [`#382`](https://github.com/hydephp/framework/issues/382)
- Fix #375, Add config option to add og:properties [`#375`](https://github.com/hydephp/framework/issues/375)
- Extract metadata helpers to concern [`72b1356`](https://github.com/hydephp/framework/commit/72b1356298ae0537356a88630e144df07fc6adf8)
- Add test for, and improve Meta helper [`15ccd27`](https://github.com/hydephp/framework/commit/15ccd271706ddf38f8011287ed28f04a60cd4076)
- Refactor concern to not be dependent on Metadata model [`b247bb0`](https://github.com/hydephp/framework/commit/b247bb0627dc481c08bb8e47f7c38ec57816154a)

#### [v0.25.0-beta](https://github.com/hydephp/framework/compare/v0.24.0-beta...v0.25.0-beta)

> 17 May 2022

- Load asset service from the service container [`#373`](https://github.com/hydephp/framework/pull/373)
- Rename --pretty option to --run-prettier to distinguish it better in build command  [`#368`](https://github.com/hydephp/framework/pull/368)
- Allow site output directory to be customized [`#362`](https://github.com/hydephp/framework/pull/362)
- Configuration and autodiscovery improvements [`#340`](https://github.com/hydephp/framework/pull/340)
- Add configurable "pretty URLs" [`#354`](https://github.com/hydephp/framework/pull/354)
- Add sidebar config offset, fix #307 [`#348`](https://github.com/hydephp/framework/pull/348)
- Change BuildService to DiscoveryService [`#347`](https://github.com/hydephp/framework/pull/347)
- Fix #361 Rename --pretty option to --run-prettier [`#361`](https://github.com/hydephp/framework/issues/361)
- Fix #350, Use the model path properties [`#350`](https://github.com/hydephp/framework/issues/350)
- Add option for pretty urls fix #330 [`#330`](https://github.com/hydephp/framework/issues/330)
- Rewrite index docs path to pretty url, fix #353 [`#353`](https://github.com/hydephp/framework/issues/353)
- Fix #330, Create helper to make pretty URLs if enabled [`#330`](https://github.com/hydephp/framework/issues/330)
- Merge pull request #348 from hydephp/add-sidebar-priority-offset-for-config-defined-values [`#307`](https://github.com/hydephp/framework/issues/307)
- Add sidebar config offset, fix #307 [`#307`](https://github.com/hydephp/framework/issues/307)
- Fix #343 [`#343`](https://github.com/hydephp/framework/issues/343)
- Restructure the tests [`41bd056`](https://github.com/hydephp/framework/commit/41bd0560fb014e3a042909e3162e2a2da28c0b77)
- Add helpers to make it easier to refactor source paths [`10e145e`](https://github.com/hydephp/framework/commit/10e145ea345d2aca22c81ec15d7af073c5ee803c)
- Utalize the $sourceDirectory property in build services [`9d9cbff`](https://github.com/hydephp/framework/commit/9d9cbff800d1422461dfcee6f3983662c51c5606)

#### [v0.24.0-beta](https://github.com/hydephp/framework/compare/v0.23.5-beta...v0.24.0-beta)

> 11 May 2022

- Add documentation sidebar category labels, fixes #309 [`#326`](https://github.com/hydephp/framework/pull/326)
- Merge pull request #326 from hydephp/309-add-documentation-sidebar-category-labels [`#309`](https://github.com/hydephp/framework/issues/309)
- Sketch out the files for the category integration [`d6c81bb`](https://github.com/hydephp/framework/commit/d6c81bbcce78f0d72f131f49e1c61716e0cd26d6)
- Implement category creation [`70448b1`](https://github.com/hydephp/framework/commit/70448b14ac6d8be3c8162ec78d12901f7a5c7579)
- Set category of uncategorized items [`9f0feb3`](https://github.com/hydephp/framework/commit/9f0feb364a0fa8be9401a5453d8a1ded4b0ae40a)

#### [v0.23.5-beta](https://github.com/hydephp/framework/compare/v0.23.4-beta...v0.23.5-beta)

> 11 May 2022

- Add back skip to content button to Lagrafo docs layout, fix #300 [`#322`](https://github.com/hydephp/framework/pull/322)
- Change max prose width of markdown pages to match blog posts, fix #303 [`#321`](https://github.com/hydephp/framework/pull/321)
- Fix #153, bug where config option uses app name instead of Hyde name. [`#320`](https://github.com/hydephp/framework/pull/320)
- Add option to mark site as installed, fix #289 [`#289`](https://github.com/hydephp/framework/issues/289)
- Merge pull request #322 from hydephp/300-add-back-skip-to-content-button-to-lagrafo-docs-layout [`#300`](https://github.com/hydephp/framework/issues/300)
- Add skip to content button docs layout, fix #300 [`#300`](https://github.com/hydephp/framework/issues/300)
- Merge pull request #321 from hydephp/303-change-max-width-of-markdown-pages-to-match-blog-posts [`#303`](https://github.com/hydephp/framework/issues/303)
- Change max width to match blog posts, fix #303 [`#303`](https://github.com/hydephp/framework/issues/303)
- Merge pull request #320 from hydephp/294-fix-bug-where-config-option-uses-app-name-instead-of-hyde-name [`#153`](https://github.com/hydephp/framework/issues/153)
- #153 Fix bug where config option uses app name instead of Hyde name. [`c90977c`](https://github.com/hydephp/framework/commit/c90977cf942cad214b8ea8218be3d5773d1fc633)
- Update install command for new site name syntax [`0687351`](https://github.com/hydephp/framework/commit/06873511064dd2b5ed2faa6ff1ad87c3210185ea)

#### [v0.23.4-beta](https://github.com/hydephp/framework/compare/v0.23.3-beta...v0.23.4-beta)

> 11 May 2022

- Refactor post excerpt component to be less reliant on directly using front matter and add view test [`#318`](https://github.com/hydephp/framework/pull/318)
- Formatting: Add newline after console output when running build without API calls, fix #313 [`#316`](https://github.com/hydephp/framework/pull/316)
- Fix #314, add background color fallback to documentation page body [`#315`](https://github.com/hydephp/framework/pull/315)
- Restructure and format component, fix #306 [`#306`](https://github.com/hydephp/framework/issues/306)
- Merge pull request #316 from hydephp/313-formatting-add-newline-after-disabling-external-api-calls-in-build-command [`#313`](https://github.com/hydephp/framework/issues/313)
- Formatting: Add newline after --no-api info, fix #313 [`#313`](https://github.com/hydephp/framework/issues/313)
- Merge pull request #315 from hydephp/314-add-dark-mode-background-to-body-in-documentation-pages-to-prevent-fouc [`#314`](https://github.com/hydephp/framework/issues/314)
- Fix #314, add background color fallback to docs body [`#314`](https://github.com/hydephp/framework/issues/314)
- Implement hidden: true front matter to hide documentation pages from sidebar, fix #310 [`#310`](https://github.com/hydephp/framework/issues/310)
- Create ArticleExcerptViewTest.php [`4a3ecaa`](https://github.com/hydephp/framework/commit/4a3ecaa02134583c36d3b8685fa5005f586f4293)
- Add tests for the fluent date-author string [`30f7f67`](https://github.com/hydephp/framework/commit/30f7f6762c6481c148908c26a5930f6e2daf1d80)

#### [v0.23.3-beta](https://github.com/hydephp/framework/compare/v0.23.2-beta...v0.23.3-beta)

> 10 May 2022

- Fix #310, allow documentation pages to be hidden from sidebar using front matter [`#311`](https://github.com/hydephp/framework/pull/311)
- Merge pull request #311 from hydephp/310-implement-hidden-true-front-matter-to-hide-documentation-pages-from-sidebar [`#310`](https://github.com/hydephp/framework/issues/310)
- Fix #310, allow items to be hidden from sidebar with front matter [`#310`](https://github.com/hydephp/framework/issues/310)

#### [v0.23.2-beta](https://github.com/hydephp/framework/compare/v0.23.1-beta...v0.23.2-beta)

> 7 May 2022

- Refactor documentation sidebar internals [`#299`](https://github.com/hydephp/framework/pull/299)
- Create feature test for the new sidebar service [`0adf948`](https://github.com/hydephp/framework/commit/0adf94889c36e0b77fb63018221b16c7f1fc8374)
- Remove deprecated action [`063a85a`](https://github.com/hydephp/framework/commit/063a85aa8979fa5780ba5622c9d9f395c2c159b3)
- Create the sidebar models [`fbcae7c`](https://github.com/hydephp/framework/commit/fbcae7cacd100267440b362a97f97d7bbdee09a9)

#### [v0.23.1-beta](https://github.com/hydephp/framework/compare/v0.23.0-beta...v0.23.1-beta)

> 6 May 2022

- Add the test helper files [`3cd5a56`](https://github.com/hydephp/framework/commit/3cd5a56aec24fde17bc1a40c6760d6fc24db3113)
- Test description has warning for out of date config [`a90c0b1`](https://github.com/hydephp/framework/commit/a90c0b17663683737cea8fa75dd3d3d39e743f66)
- Delete .run directory [`8cd71fc`](https://github.com/hydephp/framework/commit/8cd71fc4f98efb514c9995a665e5f47f839fa940)

#### [v0.23.0-beta](https://github.com/hydephp/framework/compare/v0.22.0-beta...v0.23.0-beta)

> 6 May 2022

- Refactor docs layout to use Lagrafo instead of Laradocgen [`#292`](https://github.com/hydephp/framework/pull/292)
- Port lagrafo (wip) [`6ca2309`](https://github.com/hydephp/framework/commit/6ca230964211c79fe19df5954a65ad846500ba5e)
- Move all head tags into blade component [`3093ebf`](https://github.com/hydephp/framework/commit/3093ebf65556e185649a40fd8459caa3fa250d7d)
- Use the Hyde layout [`e09e301`](https://github.com/hydephp/framework/commit/e09e301dba196f6d3336a3f9cf8a265c8939af6c)

#### [v0.22.0-beta](https://github.com/hydephp/framework/compare/v0.21.6-beta...v0.22.0-beta)

> 5 May 2022

- Update HydeFront version to v1.5.x [`#287`](https://github.com/hydephp/framework/pull/287)
- Refactor script interactions [`#286`](https://github.com/hydephp/framework/pull/286)
- Hide the install command once it has been run, fix #280 [`#280`](https://github.com/hydephp/framework/issues/280)
- Hide the install command once it has been run, fix #280 [`#280`](https://github.com/hydephp/framework/issues/280)
- Replace onclick with element IDs [`e97d545`](https://github.com/hydephp/framework/commit/e97d5457117e4980425d12fea97bb0dc81eae904)
- Move dark mode switch [`9f6fdf8`](https://github.com/hydephp/framework/commit/9f6fdf83561f4f4e1f8d2e5d4b44e0a923963c94)

#### [v0.21.6-beta](https://github.com/hydephp/framework/compare/v0.21.5-beta...v0.21.6-beta)

> 4 May 2022

- Create installer command, fix #149 [`#279`](https://github.com/hydephp/framework/pull/279)
- Merge pull request #279 from hydephp/149-create-installer-command [`#149`](https://github.com/hydephp/framework/issues/149)
- Create Install command that can publish a homepage [`b890eb7`](https://github.com/hydephp/framework/commit/b890eb790fddc7ad8e23785b3677e304343b6616)
- Use installer to set the site name in config [`3f0c843`](https://github.com/hydephp/framework/commit/3f0c843955b8dbfa0cc14879771c50397670cae0)
- Use installer to set the site URL in config [`d5f56ac`](https://github.com/hydephp/framework/commit/d5f56ac20d82eb362363b382695c016157f66e42)

#### [v0.21.5-beta](https://github.com/hydephp/framework/compare/v0.21.4-beta...v0.21.5-beta)

> 3 May 2022

- Update the test to fix updated exception output and remove comments [`cd5a70d`](https://github.com/hydephp/framework/commit/cd5a70d3f8a7b9cf0d97e584191d35ebc642cf5a)

#### [v0.21.4-beta](https://github.com/hydephp/framework/compare/v0.21.3-beta...v0.21.4-beta)

> 3 May 2022

- Fix #231 [`#231`](https://github.com/hydephp/framework/issues/231)

#### [v0.21.3-beta](https://github.com/hydephp/framework/compare/v0.21.2-beta...v0.21.3-beta)

> 3 May 2022

- Allow documentation pages to be scaffolded using the make:page command [`#273`](https://github.com/hydephp/framework/pull/273)
- Allow documentation pages to be scaffolded using the command [`7bbe012`](https://github.com/hydephp/framework/commit/7bbe0123f0e7b609954ca8e52216d19453c96f1a)

#### [v0.21.2-beta](https://github.com/hydephp/framework/compare/v0.21.1-beta...v0.21.2-beta)

> 3 May 2022

- Send a non-intrusive warning when the config file is out of date [`#270`](https://github.com/hydephp/framework/pull/270)
- Create crude action to check if a config file is up to date [`e31210f`](https://github.com/hydephp/framework/commit/e31210f055dea4ca6d76750b9b2ad24c61c05850)
- Create FileCacheServiceTest [`d9141cc`](https://github.com/hydephp/framework/commit/d9141cca4125c055f927c53edf7bf2b7bde9c9d0)
- Add the test [`ee4a64d`](https://github.com/hydephp/framework/commit/ee4a64d9a22314339b002bbd856b2f79c08bffea)

#### [v0.21.1-beta](https://github.com/hydephp/framework/compare/v0.21.0-beta...v0.21.1-beta)

> 3 May 2022

- Create filecache at runtime instead of relying on a JSON file that needs to be up to date [`#265`](https://github.com/hydephp/framework/pull/265)
- Create the filecache at runtime, resolves #243, #246 [`#243`](https://github.com/hydephp/framework/issues/243)
- Remove deprecated filecache store and generator [`7a1eb32`](https://github.com/hydephp/framework/commit/7a1eb32aae22f749611ed95bc6b2fb1fce36bd20)
- Remove "Update Filecache" workflow [`81564c0`](https://github.com/hydephp/framework/commit/81564c0d19ca6d622a4949830e2007ed10731e99)
- Remove legacy try/catch [`34733dd`](https://github.com/hydephp/framework/commit/34733ddfb5a53463688ae20f1357f09b8aec33f2)

#### [v0.21.0-beta](https://github.com/hydephp/framework/compare/v0.20.0-beta...v0.21.0-beta)

> 3 May 2022

- Always empty the _site directory when running the static site build command [`#262`](https://github.com/hydephp/framework/pull/262)
- Always purge output directory when running builder [`a86ad7d`](https://github.com/hydephp/framework/commit/a86ad7d56cbe42bc4541224d951cdf349b5a84ed)

#### [v0.20.0-beta](https://github.com/hydephp/framework/compare/v0.19.0-beta...v0.20.0-beta)

> 2 May 2022

- Update Filecache [`#258`](https://github.com/hydephp/framework/pull/258)
- Remove HydeFront from being bundled as a subrepo [`#257`](https://github.com/hydephp/framework/pull/257)
- Change the action used to create pull requests [`#255`](https://github.com/hydephp/framework/pull/255)
- Exclude files starting with an  underscore from being compiled into pages, fix #220 [`#254`](https://github.com/hydephp/framework/pull/254)
- Create .gitattributes, fixes #223 [`#250`](https://github.com/hydephp/framework/pull/250)
- Deprecate filecache.json and related services [`#248`](https://github.com/hydephp/framework/pull/248)
- Allow documentation sidebar header name to be changed [`#245`](https://github.com/hydephp/framework/pull/245)
- Update Filecache [`#242`](https://github.com/hydephp/framework/pull/242)
- Fix bugs in article and excerpts not fluently constructing descriptions [`#241`](https://github.com/hydephp/framework/pull/241)
- Handle undefined array key title in article-excerpt.blade.php  [`#238`](https://github.com/hydephp/framework/pull/238)
- Fix test matrix not fetching proper branch on PRs [`#235`](https://github.com/hydephp/framework/pull/235)
- Fix sidebar ordering bug by using null coalescing operator instead of elvis operator [`#234`](https://github.com/hydephp/framework/pull/234)
- Add unit test for hasDarkmode, fix #259 [`#259`](https://github.com/hydephp/framework/issues/259)
- Add the test, resolves #259 [`#259`](https://github.com/hydephp/framework/issues/259)
- Merge pull request #254 from hydephp/220-exclude-files-starting-with-an-_underscore-from-being-compiled-into-pages [`#220`](https://github.com/hydephp/framework/issues/220)
- Merge pull request #250 from hydephp/add-gitattributes [`#223`](https://github.com/hydephp/framework/issues/223)
- Create .gitattributes, fixes #223 [`#223`](https://github.com/hydephp/framework/issues/223)
- Make category nullable, fixes #230 [`#230`](https://github.com/hydephp/framework/issues/230)
- Fix #240 [`#240`](https://github.com/hydephp/framework/issues/240)
- Handle undefined array key, fixes #229 [`#229`](https://github.com/hydephp/framework/issues/229)
- Remove the HydeFront subrepo [`d406202`](https://github.com/hydephp/framework/commit/d406202d5f24d0cb543ac02fd2b9dc980c86d966)
- Add test to ensure that post front matter can be omitted [`875c6d4`](https://github.com/hydephp/framework/commit/875c6d46b822a7e5d02b1f281ca00189a222d06b)
- Exclude files starting with an _underscore from being discovered [`0dcdcb6`](https://github.com/hydephp/framework/commit/0dcdcb6a35969094533429345a0108915db388f4)

#### [v0.19.0-beta](https://github.com/hydephp/framework/compare/v0.18.0-beta...v0.19.0-beta)

> 1 May 2022

- Update Filecache [`#226`](https://github.com/hydephp/framework/pull/226)
- Add config option to disable dark mode [`#225`](https://github.com/hydephp/framework/pull/225)
- Update Filecache [`#222`](https://github.com/hydephp/framework/pull/222)
- Refactor assets managing, allowing for Laravel Mix, removing CDN support for Tailwind [`#221`](https://github.com/hydephp/framework/pull/221)
- Fix #211 [`#211`](https://github.com/hydephp/framework/issues/211)
- Add test and clean up docs for HasMetadata [`976cb6c`](https://github.com/hydephp/framework/commit/976cb6c39c2bc7fffcbe160987fa8ba08146f9b0)
- Revert "Update update-filecache.yml" [`abc21e7`](https://github.com/hydephp/framework/commit/abc21e7fcf07d28dc09b99afdafd2764c131936c)
- Update update-filecache.yml [`c25196a`](https://github.com/hydephp/framework/commit/c25196aebb77e8f052a604681523b54f3fc978b7)

#### [v0.18.0-beta](https://github.com/hydephp/framework/compare/v0.17.0-beta...v0.18.0-beta)

> 29 April 2022

- Update Filecache [`#201`](https://github.com/hydephp/framework/pull/201)
- Update Filecache [`#199`](https://github.com/hydephp/framework/pull/199)
- Update Filecache [`#197`](https://github.com/hydephp/framework/pull/197)
- Change priority of stylesheets [`#195`](https://github.com/hydephp/framework/pull/195)
- Update Filecache [`#194`](https://github.com/hydephp/framework/pull/194)
- Switch jsDelivr source to NPM, fix #200 [`#200`](https://github.com/hydephp/framework/issues/200)
- Update dependencies [`b505726`](https://github.com/hydephp/framework/commit/b5057268abd0a9b0aa128cc169e606d1a7a4ebfb)
- Switch to using TypeScript [`6fa9e6c`](https://github.com/hydephp/framework/commit/6fa9e6c4a762f16eac328648d9ad15dc977e4097)
- Create service class to help with #182 [`fb0033c`](https://github.com/hydephp/framework/commit/fb0033c4a9da66e7ee6dcdd9b8a137fe37c82a2f)

#### [v0.17.0-beta](https://github.com/hydephp/framework/compare/v0.16.1-beta...v0.17.0-beta)

> 28 April 2022

- Add the code reports workflow [`#191`](https://github.com/hydephp/framework/pull/191)
- Move test suite actions to framework [`#190`](https://github.com/hydephp/framework/pull/190)
- Merge with master [`#189`](https://github.com/hydephp/framework/pull/189)
- Add matrix tests [`#188`](https://github.com/hydephp/framework/pull/188)
- Move part one of the test suite [`#187`](https://github.com/hydephp/framework/pull/187)
- Move Framework tests from Hyde/Hyde to the Hyde/Framework package [`#185`](https://github.com/hydephp/framework/pull/185)
- Move tests from Hyde to Framework [`22ca673`](https://github.com/hydephp/framework/commit/22ca6731a489b576f578186cd777df4bda9e52d0)
- Format YAML [`e6da9ad`](https://github.com/hydephp/framework/commit/e6da9ada1f83c3e2540dec9f719ce59f2169bcf0)
- Add the workflow [`b20cbd6`](https://github.com/hydephp/framework/commit/b20cbd6c9341c5f0666fdda25ebb472bc512654a)

#### [v0.16.1-beta](https://github.com/hydephp/framework/compare/v0.16.0-beta...v0.16.1-beta)

> 28 April 2022

- Manage asset logic in service class [`c72905f`](https://github.com/hydephp/framework/commit/c72905fcbe8bfd748ec84536e836e8fe154230ec)

#### [v0.16.0-beta](https://github.com/hydephp/framework/compare/v0.15.0-beta...v0.16.0-beta)

> 27 April 2022

- Refactor internal codebase by sorting traits into relevant namespaces [`#175`](https://github.com/hydephp/framework/pull/175)
- Refactor: Move Hyde facade methods to traits [`9b5e4ca`](https://github.com/hydephp/framework/commit/9b5e4ca31a21a858c26c712f73021504ab99b019)
- Refactor: Update namespaces [`96c73aa`](https://github.com/hydephp/framework/commit/96c73aa01946e5f6b862dbf66ffd974d65a3b97f)
- Docs: Remove PHPDocs [`ef2f446`](https://github.com/hydephp/framework/commit/ef2f44604e61e109dcf6d03e96a4ab20cbce8b81)

#### [v0.15.0-beta](https://github.com/hydephp/framework/compare/v0.14.0-beta...v0.15.0-beta)

> 27 April 2022

- Update Filecache [`#170`](https://github.com/hydephp/framework/pull/170)
- Merge HydeFront v1.3.1 [`727c8f3`](https://github.com/hydephp/framework/commit/727c8f3b96f595b6b8a13ba7427106765583ce4c)
- Remove asset publishing commands [`0f49d16`](https://github.com/hydephp/framework/commit/0f49d16105d211df7990ec6f75c042c4bf530071)
- Rework internals, loading styles from CDN [`c5283c0`](https://github.com/hydephp/framework/commit/c5283c011b078a28117477a201ac56a1179dcf1b)

#### [v0.14.0-beta](https://github.com/hydephp/framework/compare/v0.13.0-beta...v0.14.0-beta)

> 21 April 2022

- Update Filecache [`#154`](https://github.com/hydephp/framework/pull/154)
- Change update:resources command signature to update:assets [`#153`](https://github.com/hydephp/framework/pull/153)
- Update Filecache [`#152`](https://github.com/hydephp/framework/pull/152)
- Change resources/frontend to resources/assets [`#151`](https://github.com/hydephp/framework/pull/151)
- Update Filecache [`#148`](https://github.com/hydephp/framework/pull/148)
- Update Filecache [`#147`](https://github.com/hydephp/framework/pull/147)
- Overhaul the Markdown Converter Service to make it easier to customize and extend [`#146`](https://github.com/hydephp/framework/pull/146)
- Refactor to fix https://github.com/hydephp/framework/issues/161 [`#161`](https://github.com/hydephp/framework/issues/161)
- Fix https://github.com/hydephp/framework/issues/156 [`#156`](https://github.com/hydephp/framework/issues/156)
- Move frontend files to resources/assets [`e850367`](https://github.com/hydephp/framework/commit/e85036765df5ce1398da370c50b489bd72bef797)
- Add back asset files [`bd218df`](https://github.com/hydephp/framework/commit/bd218df813c8f1496edc09500016bb21be5164b5)
- Merge with Hydefront [`8b477de`](https://github.com/hydephp/framework/commit/8b477de5793194bb9e5c4c39dee762b0f7934930)

#### [v0.13.0-beta](https://github.com/hydephp/framework/compare/v0.12.0-beta...v0.13.0-beta)

> 20 April 2022

- Update Filecache [`#141`](https://github.com/hydephp/framework/pull/141)
- Add table of contents to the documentation page sidebar [`#140`](https://github.com/hydephp/framework/pull/140)
- Add the table of contents to the frontend [`f728810`](https://github.com/hydephp/framework/commit/f728810ff34cb6a5b9f88552f5ca58b27d61e0dc)
- Add the table of contents generation [`2c4c1b9`](https://github.com/hydephp/framework/commit/2c4c1b9a7a45d527a876474af4c692bdeec1b502)
- Allow table of contents to be disabled in config [`fc9cba1`](https://github.com/hydephp/framework/commit/fc9cba16e92baf584c360e0b6a230a7e99c605e9)

#### [v0.12.0-beta](https://github.com/hydephp/framework/compare/v0.11.0-beta...v0.12.0-beta)

> 19 April 2022

- Update Filecache [`#135`](https://github.com/hydephp/framework/pull/135)
- Update Filecache [`#134`](https://github.com/hydephp/framework/pull/134)
- Allow author array data to be added in front matter [`#133`](https://github.com/hydephp/framework/pull/133)
- Strip front matter from documentation pages [`#130`](https://github.com/hydephp/framework/pull/130)
- Add trait to handle Authors in the data layer [`62f3793`](https://github.com/hydephp/framework/commit/62f3793138a108478a72e8e8176c8ca0c680be20)
- Update the views to move logic to data layer [`2ebc62c`](https://github.com/hydephp/framework/commit/2ebc62c0927ee13e1a01395e59a2316b0f826427)
- Parse the documentation pages using the fileservice [`041bf98`](https://github.com/hydephp/framework/commit/041bf98d8b20b6874cfd8c2edc7fa43bb88d2844)

#### [v0.11.0-beta](https://github.com/hydephp/framework/compare/v0.10.0-beta...v0.11.0-beta)

> 17 April 2022

- Add command for the new realtime compiler [`9be80eb`](https://github.com/hydephp/framework/commit/9be80eb34ed1415654465d4cd1b485d17086f59d)
- Allow the host and port to be specified [`e54a394`](https://github.com/hydephp/framework/commit/e54a394665213d712a6ce30cec98a84045d42738)

#### [v0.10.0-beta](https://github.com/hydephp/framework/compare/v0.9.0-beta...v0.10.0-beta)

> 12 April 2022

- Update Filecache [`#124`](https://github.com/hydephp/framework/pull/124)
- Update Filecache [`#122`](https://github.com/hydephp/framework/pull/122)
- Update Filecache [`#120`](https://github.com/hydephp/framework/pull/120)
- Update Filecache [`#118`](https://github.com/hydephp/framework/pull/118)
- Update Filecache [`#117`](https://github.com/hydephp/framework/pull/117)
- Add darkmode support and refactor blade components [`#116`](https://github.com/hydephp/framework/pull/116)
- Add skip to content link [`#113`](https://github.com/hydephp/framework/pull/113)
- Update the welcome page to be more accessible [`#112`](https://github.com/hydephp/framework/pull/112)
- Remove the deprecated and unused service provider [`#108`](https://github.com/hydephp/framework/pull/108)
- Update Blade components, internal data handling, add a11y features [`#102`](https://github.com/hydephp/framework/pull/102)
- Refactor tests [`#98`](https://github.com/hydephp/framework/pull/98)
- Deprecate internal abstract class HydeBasePublishingCommand [`#97`](https://github.com/hydephp/framework/pull/97)
- Update and simplify the command and rename signature from publish:configs to update:configs, making overwriting files the default. [`#95`](https://github.com/hydephp/framework/pull/95)
- Change blade source directory to _pages [`#90`](https://github.com/hydephp/framework/pull/90)
- Fix line ending sequence issue in checksums [`#86`](https://github.com/hydephp/framework/pull/86)
- Refactor internal file handling logic to be more intelligent to provide a safer, more intuitive, user experience  [`#84`](https://github.com/hydephp/framework/pull/84)
- Fix improper article ID usage - remember to re-publish styles [`#81`](https://github.com/hydephp/framework/pull/81)
- Fix #63, update component to show formatted dates [`#80`](https://github.com/hydephp/framework/pull/80)
- Update Spatie YAML Front Matter Package to fix #36 [`#79`](https://github.com/hydephp/framework/pull/79)
- Add base styles to documentation layout [`#77`](https://github.com/hydephp/framework/pull/77)
- Refactor code to extend base classes and remove shared code [`#74`](https://github.com/hydephp/framework/pull/74)
- Refactor the backend structure of the static page builder command process [`#72`](https://github.com/hydephp/framework/pull/72)
- Supply `_media` as the path argument in the `hyde:rebuild` command to copy all media files. [`#71`](https://github.com/hydephp/framework/pull/71)
- Add more relevant targets for the skip to content link, fix #123 [`#123`](https://github.com/hydephp/framework/issues/123)
- Add the image model, fix #100 [`#100`](https://github.com/hydephp/framework/issues/100)
- Merge pull request #80 from hydephp/63-fix-up-the-post-date-component-to-show-the-readable-name [`#63`](https://github.com/hydephp/framework/issues/63)
- Fix #63, update component to show formatted dates [`#63`](https://github.com/hydephp/framework/issues/63)
- Merge pull request #79 from hydephp/36-spatie-yaml-front-matter-package-not-properly-handling-markdown-documents-with-markdown-inside [`#36`](https://github.com/hydephp/framework/issues/36)
- Compress CSS, 5.48 KB to 3.37 KB (38.56%) [`d7f2054`](https://github.com/hydephp/framework/commit/d7f2054420f6c8a6ac786a705e2a0fc472bc4b92)
- Update dependencies [`f851978`](https://github.com/hydephp/framework/commit/f851978e0e2bf733a933504880333aebfd052fb1)
- Remove the deprecated and now unused base command [`0f137c8`](https://github.com/hydephp/framework/commit/0f137c8303cc6041011b82f80a67906b2bccfc8a)

#### [v0.9.0-beta](https://github.com/hydephp/framework/compare/v0.8.1-beta...v0.9.0-beta)

> 7 April 2022

- Rework how frontend assets (stylesheets and main script) are handled [`#69`](https://github.com/hydephp/framework/pull/69)
- Move the resource files [`7c70467`](https://github.com/hydephp/framework/commit/7c70467499c429d99813e095f0e775bf74ff0c68)
- Add the update frontend resources command [`551df0a`](https://github.com/hydephp/framework/commit/551df0a3813963aaecad3b11e5d7c1f15248241a)
- Add the action to publish the frontend resources [`e2c82fb`](https://github.com/hydephp/framework/commit/e2c82fbc6dda89c6144c949d92f1d8b147f4ab69)

#### [v0.8.1-beta](https://github.com/hydephp/framework/compare/v0.8.0-beta...v0.8.1-beta)

> 3 April 2022

- Add --no-api option to disable Torchlight at runtime, fix #53 [`#53`](https://github.com/hydephp/framework/issues/53)
- Add Changelog.md [`fe2fdf8`](https://github.com/hydephp/framework/commit/fe2fdf8e4e3a43cfcde766ac84bbcbb2c55d4890)
- Create CODE_OF_CONDUCT.md [`9361d1d`](https://github.com/hydephp/framework/commit/9361d1df2615048f01448ab26ef09e5b2de75eb0)
- Create CONTRIBUTING.md [`a581146`](https://github.com/hydephp/framework/commit/a5811466c4ee67ea5ab4b819959ab80984da6770)

#### [v0.8.0-beta](https://github.com/hydephp/framework/compare/v0.7.5-alpha...v0.8.0-beta)

> 2 April 2022

- Rewrite main navigation menu [`#60`](https://github.com/hydephp/framework/pull/60)
- Fix #59, unify sidebar elements [`#59`](https://github.com/hydephp/framework/issues/59)
- Unify the navigation menu [`f0e6cfc`](https://github.com/hydephp/framework/commit/f0e6cfc28eae7c0325a89ab0cce4ab67329e3be5)
- Add the interaction [`c5b4f7e`](https://github.com/hydephp/framework/commit/c5b4f7eb71166bce556b19ddf84861798ea2bda4)

#### [v0.7.5-alpha](https://github.com/hydephp/framework/compare/v0.7.4-alpha...v0.7.5-alpha)

> 2 April 2022

- Fix broken meta url in schema prop [`b54cfe4`](https://github.com/hydephp/framework/commit/b54cfe4a1aa1441584cd0b209fcb89a99fa4ce7a)
- Fix broken meta url in schema prop [`80b5523`](https://github.com/hydephp/framework/commit/80b552305c3d5730a951ae2f5115bed21c9a4b84)

#### [v0.7.4-alpha](https://github.com/hydephp/framework/compare/v0.7.3-alpha...v0.7.4-alpha)

> 1 April 2022

- Fix bug #47 [`b7cdaf6`](https://github.com/hydephp/framework/commit/b7cdaf67e626855c3df7513dd0b58a563f9030be)

#### [v0.7.3-alpha](https://github.com/hydephp/framework/compare/v0.7.2-alpha...v0.7.3-alpha)

> 1 April 2022

- Fix #58 [`#58`](https://github.com/hydephp/framework/issues/58)

#### [v0.7.2-alpha](https://github.com/hydephp/framework/compare/v0.7.1-alpha...v0.7.2-alpha)

> 1 April 2022

- Create new command to scaffold pages [`#55`](https://github.com/hydephp/framework/pull/55)
- Create the action [`b788de2`](https://github.com/hydephp/framework/commit/b788de22a3175c3b09eadf15249d152026f0a160)
- Create the command [`eac5258`](https://github.com/hydephp/framework/commit/eac5258268a152496cf10bff05a23aa2977617eb)
- Clean up and format code [`dc5c5ee`](https://github.com/hydephp/framework/commit/dc5c5eef20df88b729bf749004001a0832d31302)

#### [v0.7.1-alpha](https://github.com/hydephp/framework/compare/v0.7.0-alpha...v0.7.1-alpha)

> 1 April 2022

- Add a favicon link automatically if the file exists [`#54`](https://github.com/hydephp/framework/pull/54)
- Create LICENSE.md [`57d4a1b`](https://github.com/hydephp/framework/commit/57d4a1b6122e7fcef021d84bff76a97b53424d0a)
- Use getPrettyVersion for composer version [`7569fb7`](https://github.com/hydephp/framework/commit/7569fb7616bcbaa22b30aad00bf559cb81578feb)
- Change version to the (pretty) framework version [`973cc74`](https://github.com/hydephp/framework/commit/973cc7414c8a398801e2cb52364f9eb44269cf3e)

#### [v0.7.0-alpha](https://github.com/hydephp/framework/compare/v0.6.2-alpha...v0.7.0-alpha)

> 1 April 2022

- Fix bug #47 StaticPageBuilder not able to create nested documentation directories [`#51`](https://github.com/hydephp/framework/pull/51)
- Remove _authors and _drafts directories #48 [`#49`](https://github.com/hydephp/framework/pull/49)
- Delete phpdoc.dist.xml [`b28afb7`](https://github.com/hydephp/framework/commit/b28afb712f7ea522e1fb9b2175223812d910b3a0)
- Remove _data directory [`a11ff92`](https://github.com/hydephp/framework/commit/a11ff9266ff3086c4e7a3ed17f7320e90cbd8788)
- Update author yml config path [`e0578bb`](https://github.com/hydephp/framework/commit/e0578bb8938c48b62540573fa88240932e629b4f)

#### [v0.6.2-alpha](https://github.com/hydephp/framework/compare/v0.6.1-alpha...v0.6.2-alpha)

> 30 March 2022

- Fix the documentation page header link [`#46`](https://github.com/hydephp/framework/pull/46)
- Use the indexpath basename for the doc header [`e188eb5`](https://github.com/hydephp/framework/commit/e188eb54f7d5c4fdc784fc16ffd7a60ad9ab458c)

#### [v0.6.1-alpha](https://github.com/hydephp/framework/compare/v0.6.0-alpha...v0.6.1-alpha)

> 30 March 2022

- Use relative path helper for links [`#45`](https://github.com/hydephp/framework/pull/45)
- Add support for nesting the documentation pages [`#42`](https://github.com/hydephp/framework/pull/42)

#### [v0.6.0-alpha](https://github.com/hydephp/framework/compare/v0.5.3-alpha...v0.6.0-alpha)

> 30 March 2022

- Fix the 404 route bug [`#41`](https://github.com/hydephp/framework/pull/41)
- #38 Add a rebuild command to the Hyde CLI to rebuild a specific file [`#39`](https://github.com/hydephp/framework/pull/39)
- Move scripts into app.js [`#35`](https://github.com/hydephp/framework/pull/35)
- #32 refactor command class names to be consistent [`#33`](https://github.com/hydephp/framework/pull/33)
- Add internal PHPDoc class descriptions [`#30`](https://github.com/hydephp/framework/pull/30)
- Require Torchlight [`#27`](https://github.com/hydephp/framework/pull/27)
- Restructure backend models [`#26`](https://github.com/hydephp/framework/pull/26)
- Rework how Markdown files are handled to improve maintainability and testing [`#25`](https://github.com/hydephp/framework/pull/25)
- 0.6.0 Remove support for Front Matter in Markdown Pages [`#24`](https://github.com/hydephp/framework/pull/24)
- Fix #21 by dynamically routing to the docs index [`#23`](https://github.com/hydephp/framework/pull/23)
- Merge pull request #23 from hydephp/21-bug-documentation-sidebar-header-should-link-to-readme-if-that-exists-but-an-index-does-not [`#21`](https://github.com/hydephp/framework/issues/21)
- Fix #21 by dynamically routing to the docs index [`#21`](https://github.com/hydephp/framework/issues/21)
- Add PHPUnit [`0d59ea0`](https://github.com/hydephp/framework/commit/0d59ea032a8b2be2f5c09db06563ab504e233d05)
- Create the HydeRebuildStaticSiteCommand [`92b1d20`](https://github.com/hydephp/framework/commit/92b1d20069482f851ee18629a0845a69e8f5a43a)
- Refactor to use the MarkdownFileService [`48a27a2`](https://github.com/hydephp/framework/commit/48a27a2799fd6a27e3bfa55417c2eb7fda3a4c43)

#### [v0.5.3-alpha](https://github.com/hydephp/framework/compare/v0.5.2-alpha...v0.5.3-alpha)

> 26 March 2022

- Remove deprecated methods [`#19`](https://github.com/hydephp/framework/pull/19)
- Make the command extend the base command [`eaba9da`](https://github.com/hydephp/framework/commit/eaba9dac5a9849804ccfdfc2798129fbe5cb0daf)
- Remove deprecated class [`24753c1`](https://github.com/hydephp/framework/commit/24753c1776c5f887baed82c93f02b632032ffde1)
- Format to PSR2 [`8307b65`](https://github.com/hydephp/framework/commit/8307b65087f73c3bbb40ecc7eb469db83c7777be)

#### [v0.5.2-alpha](https://github.com/hydephp/framework/compare/v0.5.1-alpha...v0.5.2-alpha)

> 25 March 2022

- Remove the Hyde installer [`#18`](https://github.com/hydephp/framework/pull/18)
- 0.6.x Remove deprecated command [`#17`](https://github.com/hydephp/framework/pull/17)
- Improve Docgen Feature by allowing the output directory to be dynamically changed [`#16`](https://github.com/hydephp/framework/pull/16)
- Rework installer prompts and fix wrong directory [`c15a4ac`](https://github.com/hydephp/framework/commit/c15a4acdf76e71221f3ba4c8d028ce2d0a7e3b0a)
- Allow the documentation output directory to be changed [`6cf07a3`](https://github.com/hydephp/framework/commit/6cf07a35aa3517d6691da3bb0ded266dea0e812a)
- Allow the homepage argument to be set from cli [`ab8dedd`](https://github.com/hydephp/framework/commit/ab8deddbebd73e458712cbde51a8c40a33fae38e)

#### [v0.5.1-alpha](https://github.com/hydephp/framework/compare/v0.5.0-alpha...v0.5.1-alpha)

> 24 March 2022

- Fix visual bug caused by setting max-width on body instead of article [`#15`](https://github.com/hydephp/framework/pull/15)
- Load commands in service provider instead of config/commands.php [`#13`](https://github.com/hydephp/framework/pull/13)
- Load commands in service provider instead of config [`46397fd`](https://github.com/hydephp/framework/commit/46397fd28e6cec25ec92ce44e047183b87346331)

#### [v0.5.0-alpha](https://github.com/hydephp/framework/compare/v0.4.3-alpha...v0.5.0-alpha)

> 24 March 2022

- Merge 0.5.0 into Master - Adds a multitude of new tests, code refactors and quality of life features [`#12`](https://github.com/hydephp/framework/pull/12)
- Sync branch with Master [`#11`](https://github.com/hydephp/framework/pull/11)
- Merge 0.5.x progress [`#10`](https://github.com/hydephp/framework/pull/10)
- Add _data directory and Authors object as well as stubs to aid in testing [`#9`](https://github.com/hydephp/framework/pull/9)
- Add required depedency to framework [`e5f0ec5`](https://github.com/hydephp/framework/commit/e5f0ec58df1163ef1de85a0b3233a347c45be136)
- Implement the Authors backend feature [`d7679f5`](https://github.com/hydephp/framework/commit/d7679f5b8d9ac900229a91d59099974cb82568e1)
- Add Commonmark as an explicit dependency [`bf915b1`](https://github.com/hydephp/framework/commit/bf915b130f418433ee2b47cc158229614883b090)

#### [v0.4.3-alpha](https://github.com/hydephp/framework/compare/v0.4.2-alpha...v0.4.3-alpha)

> 23 March 2022

- Add bindings for the package versions [`a9ce58d`](https://github.com/hydephp/framework/commit/a9ce58d2a9583866c05451ecf0da1dac4f84260b)
- Get version from facade [`465bafc`](https://github.com/hydephp/framework/commit/465bafc59fd0d20c5df91148d148d4c89a36e988)
- Replace Git version with Hyde version [`bcb7357`](https://github.com/hydephp/framework/commit/bcb7357f637138239bbee3ece007ff45564718bd)

#### [v0.4.2-alpha](https://github.com/hydephp/framework/compare/v0.4.1-alpha...v0.4.2-alpha)

> 23 March 2022

- v0.4.2-alpha Adds new meta tags and more data rich HTML [`#8`](https://github.com/hydephp/framework/pull/8)
- Add new meta tag options [`78a74c7`](https://github.com/hydephp/framework/commit/78a74c7c5342d6a8b528134022ba822e506cb12e)
- Add the Site URL feature, remember to update config! [`ee2f5c6`](https://github.com/hydephp/framework/commit/ee2f5c6b542ec3eb20412a8ef718b11cc1a9e23c)
- Add more rich HTML content [`8eb6778`](https://github.com/hydephp/framework/commit/8eb677849a655a30dffe5bfb3d48921ff4b24821)

#### [v0.4.1-alpha](https://github.com/hydephp/framework/compare/v0.4.0-alpha...v0.4.1-alpha)

> 22 March 2022

- Add the Hyde::getLatestPosts() shorthand to get the latest posts collection [`#4`](https://github.com/hydephp/framework/pull/4)
- Add new options to the build command to improve the user experience  [`#3`](https://github.com/hydephp/framework/pull/3)
- Remove progress bar from empty collections [`40d3203`](https://github.com/hydephp/framework/commit/40d3203d5494d37cea1b921f2a4447bc924d18d7)
- Add option to remove old files before building [`2650997`](https://github.com/hydephp/framework/commit/26509974c02a0c2d14f6fec490bdedc89a9b7725)
- Add options to automatically build frontend assets [`f789c2f`](https://github.com/hydephp/framework/commit/f789c2fc840e5bbffbf1df2b6a56576a846d48f5)

#### [v0.4.0-alpha](https://github.com/hydephp/framework/compare/v0.3.1-alpha...v0.4.0-alpha)

> 22 March 2022

- Add the console logo font [`2683a4b`](https://github.com/hydephp/framework/commit/2683a4b06d6ea646d2d3f6eaab32746df8a02da0)
- Add the config files [`47e9044`](https://github.com/hydephp/framework/commit/47e9044c3f63a02c8c5858d0a32861031126387c)
- Add the 404 page [`962cbe2`](https://github.com/hydephp/framework/commit/962cbe2886f2815a5c46de56b73e594cd3b12d1b)

#### [v0.3.1-alpha](https://github.com/hydephp/framework/compare/v0.3.0-alpha...v0.3.1-alpha)

> 22 March 2022

- Delete vendor directory [`4f96627`](https://github.com/hydephp/framework/commit/4f96627679a2e6de95520010a6f1bc98f30bca9f)
- 0.3.1 Move commands to framework [`70dd8df`](https://github.com/hydephp/framework/commit/70dd8df956e7fc1bc1c9b67a14e2b23a8fea4d76)
- Add php 8 require, and suggest hyde/hyde [`a8ff6ad`](https://github.com/hydephp/framework/commit/a8ff6ad9b3db7fe5bf69c638dd03b21309b85e42)

#### v0.3.0-alpha

> 21 March 2022

- Add the Core files (with temporary namespace) [`816ad3a`](https://github.com/hydephp/framework/commit/816ad3a24e5f95dff5aa1f1cfd581764fd1a1389)
- Initial Commit [`fa00787`](https://github.com/hydephp/framework/commit/fa007876e36ca6588147b05d44f927d7e8fbf997)
- Successfully move namespace Core to Framework [`0c9160f`](https://github.com/hydephp/framework/commit/0c9160f33124701e6ed21a1e5b2bd70f46aaa65a)
