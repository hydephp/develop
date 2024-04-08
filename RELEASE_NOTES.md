## [v2-dev] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added a new `\Hyde\Framework\Actions\PreBuildTasks\TransferMediaAssets` build task handle media assets transfers for site builds.
- The `\Hyde\Facades\Features` class is no longer marked as internal, and is now thus part of the public API.

### Changed
- **Breaking:** The internals of the navigation system has been rewritten into a new Navigation API. This change is breaking for custom navigation implementations. For more information, see below.
- Minor: Navigation menu items are now no longer filtered by duplicates (meaning two items with the same label can now exist in the same menu) in https://github.com/hydephp/develop/pull/1573
- Minor: Due to changes in the navigation system, it is possible that existing configuration files will need to be adjusted in order for menus to look the same (in terms of ordering etc.)
- Minor: The documentation article component now supports disabling the semantic rendering using a falsy value in https://github.com/hydephp/develop/pull/1566
- Changed how the documentation search is generated, to be an `InMemoryPage` instead of a post-build task.
- Media asset files are now copied using the new build task instead of the deprecated `BuildService::transferMediaAssets()` method.

### Deprecated
- for soon-to-be removed features.

### Removed
- Breaking: Removed the build task `\Hyde\Framework\Actions\PostBuildTasks\GenerateSearch` (see upgrade guide below)
- Breaking: Removed the deprecated `\Hyde\Framework\Services\BuildService::transferMediaAssets()` method (see upgrade guide below)
- Internal: Removed the internal `DocumentationSearchPage::generate()` method as it was unused in https://github.com/hydephp/develop/pull/1569

### Fixed
- Realtime Compiler: Fixed responsive dashboard table issue in https://github.com/hydephp/develop/pull/1595

### Security
- in case of vulnerabilities.

### Upgrade Guide

Please see the "Breaking changes & upgrade guide" section below for more information.

## Breaking changes & upgrade guide

<!-- Editors note: The following will be part of the documentation and not the changelog, which is why the heading levels are reset. -->

Please read through this section to ensure your site upgrades smoothly.

## High impact

### Navigation system rewrite

The navigation system has been rewritten into a new Navigation API. This change is breaking for custom navigation implementations, see more down below.

For most users, the only impact will be that configuration files need to be updated to use the new configuration format. Due to the internal changes,
it's also possible that menu items will be in a slightly different order than before, depending on your setup. Please verify that your site's menus
look as expected after upgrading, and adjust the configuration files if necessary, before deploying to production.

### Navigation and sidebar configuration changes

The navigation and sidebar configuration files have been updated to use the new Navigation API.
This means that you will need to update your configuration files to use the new format.

The easiest way to upgrade is to publish updated configuration files (`hyde.php` and `docs.php`) and copy over your customizations.

The following configuration entries have been updated:

-  Changed configuration option `docs.sidebar_order` to `docs.sidebar.order` in https://github.com/hydephp/develop/pull/1583
-  Upgrade path: Move the `sidebar_order` option's array in the `config/docs.php` file into the `sidebar` array in the same file.

-  Changed configuration option `docs.table_of_contents` to `docs.sidebar.table_of_contents` in https://github.com/hydephp/develop/pull/1584
-  Upgrade path: Move the `table_of_contents` option's array in the `config/docs.php` file into the `sidebar` array in the same file.


## General impact

### Documentation search page changes

The documentation search page and search index have been changed to be generated as `InMemoryPages` instead of a post-build task.

The main impact noticeable to most users by this is the implicit changes, like the pages showing up in the dashboard and route list command.

In case you have customized the `GenerateSearch` post-build task you may, depending on what you were trying to do,
want to adapt your code to interact with the new `InMemoryPage`, which is generated in the `HydeCoreExtension` class.

For more information, see https://github.com/hydephp/develop/pull/1498.

## Medium impact

### Features class method renames

The following methods in the `Features` class have been renamed to follow a more consistent naming convention:

- `Features::sitemap()` has been renamed to `Features::hasSitemap()`
- `Features::rss()` has been renamed to `Features::hasRss()`

Note that this class was previously marked as internal in v1, but the change is logged here in case it was used in configuration files or custom code.

## Low impact

### Navigation internal changes

The navigation system has been rewritten into a new Navigation API. This change is breaking for custom navigation implementations.

If you have previously in your custom code done any of the following, or similar, you will need to adapt your code to use the new Navigation API:
- Created custom navigation menus or Blade components
- Extended or called the navigation related classes directly
- Customized the navigation system in any way beyond the standard configuration


#### Upgrade guide

Due to the scope of the rewrite, the easiest and fastest way to upgrade your code is to recreate it using the new Navigation API.

- For a full comparison of the changes, you may see the PR that introduced the new API: https://github.com/hydephp/develop/pull/1568/files
- For information on how to use the new Navigation API, see the documentation: https://hydephp.com/docs/2.x/navigation-api

### HTML ID changes

Some HTML IDs have been renamed to follow a more consistent naming convention.

If you have used any of the following selectors in custom code you wrote yourself, you will need to update to use the new changed IDs.

#### https://github.com/hydephp/develop/pull/1622
- Rename HTML ID `#searchMenu` to `#search-menu`
- Rename HTML ID `#searchMenuButton` to `#search-menu-button`
- Rename HTML ID `#searchMenuButtonMobile` to `#search-menu-button-mobile`


### New documentation search implementation

As the new documentation search implementation brings changes to their code API you may need to adapt your code
according to the information below in case you wrote custom code that interacted with these parts of the codebase.

- The `GenerateSearch` post-build task has been removed. If you have previously extended or customized this class,
  you will need to adapt your code, as the search index files are now handled implicitly during the standard build process,
  as the search pages are now added to the kernel page and route collection. (https://github.com/hydephp/develop/pull/1498)

- If your site has a custom documentation search page, for example `_docs/search.md` or `_pages/docs/search.blade.php`,
  that page will no longer be built when using the specific `build:search` command. It will, of course,
  be built using the standard `build` command. https://github.com/hydephp/develop/commit/82dc71f4a0e7b6be7a9f8d822fbebe39d2289ced

- In the highly unlikely event your site customizes any of the search pages by replacing them in the kernel route collection,
  you would now need to do that in the kernel page collection due to the search pages being generated earlier in the lifecycle.
  https://github.com/hydephp/develop/commit/82dc71f4a0e7b6be7a9f8d822fbebe39d2289ced

### Media asset transfer implementation changes

The internals of how media asset files are copied during the build process have been changed. For most users, this change
has no impact. However, if you have previously extended this method, or called it directly from your custom code,
you will need to adapt your code to use the new `TransferMediaAssets` build task.

For example, if you triggered the media transfer with a build service method call, use the new build task instead:

```php
(new BuildService)->transferMediaAssets();

(new TransferMediaAssets())->run();
```
