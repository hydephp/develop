## [Unreleased] - YYYY-MM-DD

### Improved Patch Release Strategy

This release experiments some changes into how releases are handled to clarify the patch versioning of distributed packages compared to the monorepo source versioning.

In short: We are now experimenting with rolling patch releases, where patches are released as soon as they're ready, leading to faster rollout of bugfixes.
This means that the patch version discrepancy between the monorepo and the distributed packages will be increased, but hopefully the end results will still be clearer,
thanks to the second related change: Prefixing the subpackage changes in this changelog with the package name.

All this to say, please keep in mind that when the monorepo gets a new minor version, the prefixed changes may already have been released as patches in their respective packages.

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added the existing `media_extensions` option to the `hyde` configuration file in https://github.com/hydephp/develop/pull/1531
- Added configuration options to add custom HTML to the `<head>` and `<script>` sections in https://github.com/hydephp/develop/pull/1542
- Added support for adding custom HTML to the `<head>` and `<script>` sections using HTML includes in https://github.com/hydephp/develop/pull/1554
- Added an `html` helper to the `Includes` facade in https://github.com/hydephp/develop/pull/1552

### Changed
- Renamed local template variable `$document` to `$article` to better match the usage in https://github.com/hydephp/develop/pull/1506
- Updated semantic documentation article component to support existing variables in https://github.com/hydephp/develop/pull/1506
- HydeFront: Changed `<code>` styling to display as inline instead of inline-block in https://github.com/hydephp/develop/pull/1525
- Realtime Compiler: Add strict type declarations in https://github.com/hydephp/develop/pull/1555/files

### Deprecated
- Deprecated the `BuildService::transferMediaAssets()` method in https://github.com/hydephp/develop/pull/1533, as it will be moved into a build task in v2.0.

### Removed
- for now removed features.

### Fixed
- Fixed icons not being considered as images by dashboard viewer in https://github.com/hydephp/develop/pull/1512
- HydeFront: Fixed bug where heading permalink buttons were included in text represented output in https://github.com/hydephp/develop/pull/1519
- HydeFront: Fix visual overflow bug in inline code blocks within blockquotes https://github.com/hydephp/hydefront/issues/65 in https://github.com/hydephp/develop/pull/1525
- Realtime Compiler: Fixes visual dashboard bugs https://github.com/hydephp/realtime-compiler/issues/23 and https://github.com/hydephp/realtime-compiler/issues/24 in https://github.com/hydephp/develop/pull/1528

### Security
- in case of vulnerabilities.
