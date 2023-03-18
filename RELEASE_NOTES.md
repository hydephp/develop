## [Unreleased] - YYYY-MM-DD

### About

This release is the first since the official release of HydePHP 1.0.0. It contains a number of bug fixes and improvements, but no breaking changes as the project has reached general availability and adheres to the semantic versioning backwards compatibility promise.

### Added
- Added a RealtimeCompiler config option to disable rendered pages being stored to disk in https://github.com/hydephp/develop/pull/1334

### Changed
- Updated discovery exception message to include the causing exception message in https://github.com/hydephp/develop/pull/1305
- Cleaned up `PageDataFactory`, `NavigationDataFactory`, and `BlogPostDataFactory` internals for better type safety in https://github.com/hydephp/develop/pull/1312
- Refactored internals to use the `View` facade over the `view` function for better type safety in https://github.com/hydephp/develop/pull/1310
- Updated to HydeFront v3.3.0 in https://github.com/hydephp/develop/pull/1329

### Deprecated
- for soon-to-be removed features.

### Removed
- Classes `PageDataFactory`, `NavigationDataFactory`, and `BlogPostDataFactory` no longer use the `InteractsWithFrontMatter` trait

### Fixed
- Fixed https://github.com/hydephp/develop/issues/1301 in https://github.com/hydephp/develop/pull/1302
- Fixed https://github.com/hydephp/develop/issues/1313 in https://github.com/hydephp/develop/commit/134776a1e4af395dab5c15d611fc64c9ebce8596
- Fixed https://github.com/hydephp/develop/issues/1316 in https://github.com/hydephp/develop/pull/1317
- Fixed https://github.com/hydephp/develop/issues/1318 in https://github.com/hydephp/develop/pull/1319
- Fixed https://github.com/hydephp/develop/issues/1320 in https://github.com/hydephp/develop/pull/1321
- Fixed https://github.com/hydephp/develop/issues/1322 in https://github.com/hydephp/develop/issues/1323
- Fixed https://github.com/hydephp/develop/issues/1324 in https://github.com/hydephp/develop/pull/1325
- Fixed https://github.com/hydephp/develop/issues/1326 in https://github.com/hydephp/develop/pull/1327
- Fixed https://github.com/hydephp/develop/issues/1330 in https://github.com/hydephp/develop/pull/1331
- Fixed navigation dropdowns flickering on page load in Fixed https://github.com/hydephp/develop/issues/1330 in https://github.com/hydephp/develop/pull/1332
- Added missing function imports in https://github.com/hydephp/develop/pull/1309

### Security
- in case of vulnerabilities.
