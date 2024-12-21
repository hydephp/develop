## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Updated the `HydeKernel` array representation to include the Hyde version in https://github.com/hydephp/develop/pull/1786
- Registered the `cache:clear` command to make it easier to clear the cache in https://github.com/hydephp/develop/pull/1881
- Added a new `Hyperlinks::isRemote()` helper method to check if a URL is remote in https://github.com/hydephp/develop/pull/1882
- All page types now support the `description` front matter field (used in page metadata) in https://github.com/hydephp/develop/pull/1884
- Added a new `Filesystem::findFiles()` method to find files in a directory in https://github.com/hydephp/develop/pull/2064
- Added `webp` to the list of default media extensions in https://github.com/hydephp/framework/pull/663

### Changed
- Changed the `Hyde` facade to use a `@mixin` annotation instead of single method annotations in https://github.com/hydephp/develop/pull/1919
- Updated the `Serializable` trait to provide a default automatic `toArray` method in https://github.com/hydephp/develop/pull/1791
- Updated the `PostAuthor` class's `name` property to fall back to the `username` property if the `name` property is not set in https://github.com/hydephp/develop/pull/1794
- Removed the nullable type hint from the `PostAuthor` class's `name` property as it is now always set in https://github.com/hydephp/develop/pull/1794
- Improved the accessibility of the heading permalinks feature in https://github.com/hydephp/develop/pull/1803
- The `torchlight:install` command is now hidden from the command list as it's already installed in https://github.com/hydephp/develop/pull/1879
- Updated the home page fallback link in the 404 template to lead to the site root in https://github.com/hydephp/develop/pull/1880 (fixes https://github.com/hydephp/develop/issues/1781)
- Normalized remote URL checks so that protocol relative URLs `//` are consistently considered to be remote in all places in https://github.com/hydephp/develop/pull/1882 (fixes https://github.com/hydephp/develop/issues/1788)
- Replaced internal usages of glob functions with our improved file finder in https://github.com/hydephp/develop/pull/2064
- Updated to HydeFront v3.4 in https://github.com/hydephp/develop/pull/1803
- Realtime Compiler: Virtual routes are now managed through the service container in https://github.com/hydephp/develop/pull/1858
- Realtime Compiler: Improved the dashboard layout in https://github.com/hydephp/develop/pull/1866
- Realtime Compiler: Shorten the realtime compiler server start message from "Press Ctrl+C to stop" to "Use Ctrl+C to stop" to better fit 80 column terminals in https://github.com/hydephp/develop/pull/2017

### Deprecated
- The `PostAuthor::getName()` method is now deprecated and will be removed in v2. (use `$author->name` instead) in https://github.com/hydephp/develop/pull/1794
- Deprecated the `FeaturedImage::isRemote()` method in favor of the new `Hyperlinks::isRemote()` method in https://github.com/hydephp/develop/pull/1882
- Deprecated the `Hyde::mediaLink()` method in favor of the `Hyde::asset()` method in https://github.com/hydephp/develop/pull/1993

### Removed
- for now removed features.

### Fixed
- Fixed Tailwind content paths for nested Blade pages in https://github.com/hydephp/develop/pull/2042
- Added missing collection key types in Hyde facade method annotations in https://github.com/hydephp/develop/pull/1784
- Fixed heading permalinks button text showing in Google Search previews https://github.com/hydephp/develop/issues/1801 in https://github.com/hydephp/develop/pull/1803
- Fixed routing issues with nested 404 pages where an index page does not exist https://github.com/hydephp/develop/issues/1781 in https://github.com/hydephp/develop/pull/1880
- Fixed URL metadata for blog posts not using customized post output directories in https://github.com/hydephp/develop/pull/1889
- Improved printed documentation views in https://github.com/hydephp/develop/pull/2005
- Fixed "BuildService finding non-existent files to copy in Debian" https://github.com/hydephp/framework/issues/662 in https://github.com/hydephp/develop/pull/2064
- Fixed "Undefined constant `Hyde\Foundation\Kernel\GLOB_BRACE`" https://github.com/hydephp/hyde/issues/270 in https://github.com/hydephp/develop/pull/2064
- Realtime Compiler: Updated the exception handler to match HTTP exception codes when sending error responses in https://github.com/hydephp/develop/pull/1853
- Realtime Compiler: Improved routing for nested index pages in https://github.com/hydephp/develop/pull/1852
- Realtime Compiler: Improved the dashboard https://github.com/hydephp/develop/pull/1866 fixing https://github.com/hydephp/realtime-compiler/issues/22 and https://github.com/hydephp/realtime-compiler/issues/29
- Realtime Compiler: Fixed support for serving media assets in subdirectories https://github.com/hydephp/realtime-compiler/issues/26 in https://github.com/hydephp/develop/pull/1872

### Security
- in case of vulnerabilities.
