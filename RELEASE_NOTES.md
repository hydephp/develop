## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added support for setting custom content when calling source file creator actions directly in https://github.com/hydephp/develop/pull/1393
- Added support for setting a custom post date when calling post file creator action directly in https://github.com/hydephp/develop/pull/1393
- Added optional `FileNotFoundException` constructor parameter to set a custom exception message https://github.com/hydephp/develop/pull/1398
- The realtime compiler dashboard is now interactive, and allows you to make edits to your project right from the browser https://github.com/hydephp/develop/pull/1392

### Changed
- Realtime Compiler: The `DashboardController` class is now marked as internal, as it is not intended to be used outside of the package https://github.com/hydephp/develop/pull/1394
- Updated the realtime compiler server configuration options in https://github.com/hydephp/develop/pull/1395 (backwards compatible)
- Updated the realtime compiler to generate the documentation search index each time it's requested in https://github.com/hydephp/develop/pull/1405 (fixes https://github.com/hydephp/develop/issues/1404)
- Updated the navigation menu generator to remove duplicates after running the sorting method in https://github.com/hydephp/develop/pull/1407 (fixes https://github.com/hydephp/develop/issues/1406)

### Deprecated
- for soon-to-be removed features.

### Removed
- for now removed features.

### Fixed
- Catch RealtimeCompiler dashboard OutOfBoundsException in https://github.com/hydephp/develop/pull/1384
- Updated dropdown navigation menus to support setting priority in config in https://github.com/hydephp/develop/pull/1387 (fixing https://github.com/hydephp/hyde/issues/229)
- Updated the vendor publish command to support parent Laravel Prompts implementation in https://github.com/hydephp/develop/pull/1388
- Fixed wrong version constant in https://github.com/hydephp/develop/pull/1391
- Fixed improperly formatted exception message in https://github.com/hydephp/develop/pull/1399
- Fixed missing support for missing and out of date search indexes when previewing site https://github.com/hydephp/develop/issues/1404 in https://github.com/hydephp/develop/pull/1405
- Fixed duplicate navigation items not giving precedence to config defined items https://github.com/hydephp/develop/issues/1406 in https://github.com/hydephp/develop/pull/1407

### Security
- in case of vulnerabilities.
