## [v2-dev] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- for new features.

### Changed
- Changed how the documentation search is generated, to be an `InMemoryPage` instead of a post-build task.

### Deprecated
- for soon-to-be removed features.

### Removed
- Breaking: Removed the build task `\Hyde\Framework\Actions\PostBuildTasks\GenerateSearch` (see upgrade guide below)

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.

### Upgrade Guide

If there are any breaking changes, include an upgrade guide here.

#### Documentation search page changes

The documentation search page and search index have been changed to be generated as `InMemoryPages` instead of a post-build task.

The main impact noticeable to most users by this is the implicit changes, like the pages showing up in the dashboard and route list command.

In case you have customized the `GenerateSearch` post-build task you may, depending on what you were trying to do, 
want to adapt your code to interact with the new `InMemoryPage`, which is generated in the `HydeCoreExtension` class.

For more information, see https://github.com/hydephp/develop/pull/1498.

### Breaking changes

#### Low impact

- The `GenerateSearch` post-build task has been removed. If you have previously extended or customized this class, 
  you will need to adapt your code, as the search index files are now handled implicitly during the standard build process,
  as the search pages are now added to the kernel page and route collection. (https://github.com/hydephp/develop/pull/1498)

- If your site has a custom documentation search page, for example `_docs/search.md` or `_pages/docs/search.blade.php`,
  that page will no longer be built when using the specific `build:search` command. It will, of course, 
  be built using the standard `build` command. https://github.com/hydephp/develop/commit/82dc71f4a0e7b6be7a9f8d822fbebe39d2289ced
 
- In the highly unlikely event your site customizes any of the search pages by replacing them in the kernel route collection,
  you would now need to do that in the kernel page collection due to the search pages being generated earlier in the lifecycle.
  https://github.com/hydephp/develop/commit/82dc71f4a0e7b6be7a9f8d822fbebe39d2289ced
