## [Unreleased] - YYYY-MM-DD

### About

This release refactors and cleans up a large part of the internal code base. For most end users, this will not have any visible effect. If you have developed integrations that depend on methods you may want to take a closer look at the associated pull requests as it is not practical to list them all here.

#### Overview

Here is a short overview of the areas that are impacted. If you don't know what any of these mean, they don't affect you.

- HydeKernel has been internally separated into foundation classes
- DiscoveryService has been refactored
- Page compiling logic are now handled within the page models


### Added
- internal: Adds methods to the HydeKernelContract interface

### Changed
- internal: The HydeKernel has been refactored to move related logic to service classes. This does not change the end usage as the Hyde facade still works the same
- `DiscoveryService::getSourceFileListForModel()` now throws an exception instead of returning false when given an invalid model class
- `DiscoveryService::getFilePathForModelClassFiles` method was renamed to `DiscoveryService::getModelSourceDirectory`
- `DiscoveryService::getFileExtensionForModelFiles` method was renamed to `DiscoveryService::getModelFileExtension`
- The `Hyde::copy()` helper now always uses paths relative to the project
- The `Hyde::copy()` helper will always overwrite existing files

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed deprecated `Hyde::uriPath()` helper
- Removed deprecated `CollectionService::findModelFromFilePath()`

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.
