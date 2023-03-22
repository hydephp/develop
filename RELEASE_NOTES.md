## [Unreleased] - YYYY-MM-DD

### About

This release is the first since the official release of HydePHP 1.0.0. It contains a number of bug fixes and improvements, but no breaking changes as the project has reached general availability and adheres to the semantic versioning backwards compatibility promise.

### Added
- Added a RealtimeCompiler config option to disable rendered pages being stored to disk in https://github.com/hydephp/develop/pull/1334
- Documentation sidebars now support table of contents when using Setext headings in https://github.com/hydephp/develop/pull/1343

### Changed
- Updated discovery exception message to include the causing exception message in https://github.com/hydephp/develop/pull/1305
- Cleaned up `PageDataFactory`, `NavigationDataFactory`, and `BlogPostDataFactory` internals for better type safety in https://github.com/hydephp/develop/pull/1312
- Refactored internals to use the `View` facade over the `view` function for better type safety in https://github.com/hydephp/develop/pull/1310
- Refactored the `GeneratesTableOfContents` class internals to be more accurate in https://github.com/hydephp/develop/pull/1343
- Updated to HydeFront v3.3.0 in https://github.com/hydephp/develop/pull/1329

### Deprecated
- for soon-to-be removed features.

### Removed
- Classes `PageDataFactory`, `NavigationDataFactory`, and `BlogPostDataFactory` no longer use the `InteractsWithFrontMatter` trait

### Fixed
- Fixed "[#1301 Build-time `--pretty-urls` flag is not respected by canonical URLs](https://github.com/hydephp/develop/issues/1301)" in [#1302](https://github.com/hydephp/develop/pull/1302)
- Fixed "[#1316 Bug: The blog post feed component does not work on nested pages due to not using routes](https://github.com/hydephp/develop/issues/1316)" in [#1317](https://github.com/hydephp/develop/pull/1317)
- Fixed "[#1318 Nested index pages not showing in navigation](https://github.com/hydephp/develop/issues/1318)" in [#1319](https://github.com/hydephp/develop/pull/1319)
- Fixed "[#1320 The RSS feed generator does not respect the `--no-api` flag when getting content length for remote images](https://github.com/hydephp/develop/issues/1320)" in [#1321](https://github.com/hydephp/develop/pull/1321)
- Fixed "[#1322 Wrong HydeFront version constant](https://github.com/hydephp/develop/issues/1322)" in [#1323](https://github.com/hydephp/develop/pull/1323)
- Fixed "[#1324 Navigation dropdowns should not wrap over multiple lines](https://github.com/hydephp/develop/issues/1324)" in [#1325](https://github.com/hydephp/develop/pull/1325)
- Fixed "[#1326 PlayCDN integration should work gracefully when there is no Tailwind config file](https://github.com/hydephp/develop/issues/1326)" in [#1327](https://github.com/hydephp/develop/pull/1327)
- Fixed "[#1330 Clicking outside an activated dropdown should close it](https://github.com/hydephp/develop/issues/1330)" in [#1331](https://github.com/hydephp/develop/pull/1331)
- Fixed "[#1337 Sidebar table of contents are unable to be generated when using Setext headers](https://github.com/hydephp/develop/issues/1337)" in [#1343](https://github.com/hydephp/develop/pull/1343)
- Fixed "[#1330 Clicking outside an activated dropdown should close it](https://github.com/hydephp/develop/issues/1330)" in [#1332](https://github.com/hydephp/develop/pull/1332)
- Fixed "[#1340 Search index is not resolvable when using root documentation page output combined with subdirectory deployment](https://github.com/hydephp/develop/issues/1340)" in [#1345](https://github.com/hydephp/develop/pull/1345)
- Fixed "[#1313 Bug: Uppercase input is improperly reformatted in the makeTitle Helper](https://github.com/hydephp/develop/issues/1313)" in [`134776a`](https://github.com/hydephp/develop/commit/134776a1e4af395dab5c15d611fc64c9ebce8596)
- Fixed navigation dropdowns flickering on page load in [#1332](https://github.com/hydephp/develop/pull/1332)
- Added missing function imports in [#1309](https://github.com/hydephp/develop/pull/1309)

### Security
- in case of vulnerabilities.
