## [v2-dev] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Add experimental InMemoryPage::getOutputPath overload support

### Changed
- Changed how the documentation search is generated, to be an InMemoryPage instead of a post-build task.
- Updated the documentation article component to support existing $document instance.

### Deprecated
- \Hyde\Framework\Actions\PostBuildTasks\GenerateSearch

### Removed
- for now removed features.

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.

### Upgrade Guide

If there are any breaking changes, include an upgrade guide here.

#### Documentation search changes

The documentation search page and search index have been changed to be generated as InMemoryPages instead of a post-build task.

In case you have customized the GenerateSearch post-build task, you will need to adapt your code to the new InMemoryPage, which is generated in the HydeCoreExtension class.

For more information, see https://github.com/hydephp/develop/pull/1498.
