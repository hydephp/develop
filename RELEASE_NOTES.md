## [v2-dev] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added a new `\Hyde\Framework\Actions\PreBuildTasks\TransferMediaAssets` build task handle media assets transfers for site builds.
- Added a new `ExternalRoute` class to represent external routes.
- Added a new `NavItem::getLink()` method contain the previous `NavItem::getDestination()` logic, to return the link URL.

### Changed
- Changed how the documentation search is generated, to be an `InMemoryPage` instead of a post-build task.
- Media asset files are now copied using the new build task instead of the deprecated `BuildService::transferMediaAssets()` method.
- Minor: The documentation article component now supports disabling the semantic rendering using a falsy value in https://github.com/hydephp/develop/pull/1566
- Navigation menu items are now no longer filtered by duplicates (meaning two items with the same label can now exist in the same menu) in https://github.com/hydephp/develop/pull/1573
- Breaking: The `NavItem` class now always stores the destination as a `Route` instance.
- Breaking: The `NavItem::getDestination()` method now returns its `Route` instance.

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

## General impact

### Documentation search page changes

The documentation search page and search index have been changed to be generated as `InMemoryPages` instead of a post-build task.

The main impact noticeable to most users by this is the implicit changes, like the pages showing up in the dashboard and route list command.

In case you have customized the `GenerateSearch` post-build task you may, depending on what you were trying to do, 
want to adapt your code to interact with the new `InMemoryPage`, which is generated in the `HydeCoreExtension` class.

For more information, see https://github.com/hydephp/develop/pull/1498.

## Low impact

### Navigation item changes

The `NavItem::getDestination()` method now returns its `Route` instance. This allows for deferring the route evaluation.

If you have previously used this method directly and expected a string to be returned, you may need to adapt your code to handle the new return type.

If you want to retain the previous state where a string is always returned, you can use the new `NavItem::getLink()` method instead, which will resolve the route immediately.

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
