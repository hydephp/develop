## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Updated the `HydeKernel` array representation to include the Hyde version in https://github.com/hydephp/develop/pull/1786

### Changed
- Updated the `Serializable` trait to provide a default automatic `toArray` method in https://github.com/hydephp/develop/pull/1791
- Updated the `PostAuthor` class's `name` property to fall back to the `username` property if the `name` property is not set in https://github.com/hydephp/develop/pull/1794
- Removed the nullable type hint from the `PostAuthor` class's `name` property as it is now always set in https://github.com/hydephp/develop/pull/1794
- Improved the accessibility of the heading permalinks feature in https://github.com/hydephp/develop/pull/1803
- Updated to HydeFront v3.4 in https://github.com/hydephp/develop/pull/1803

### Deprecated
- The `PostAuthor::getName()` method is now deprecated and will be removed in v2. (use `$author->name` instead) in https://github.com/hydephp/develop/pull/1794

### Removed
- for now removed features.

### Fixed
- Added missing collection key types in Hyde facade method annotations in https://github.com/hydephp/develop/pull/1784
- Fixed heading permalinks button text showing in Google Search previews https://github.com/hydephp/develop/issues/1801 in https://github.com/hydephp/develop/pull/1803
- Realtime Compiler: Updated the exception handler to match HTTP exception codes when sending error responses in https://github.com/hydephp/develop/pull/1853

### Security
- in case of vulnerabilities.
