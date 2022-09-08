## [Unreleased] - YYYY-MM-DD

### About

This release performs some refactors in preparation for the 1.0 release. Many of these refactors are breaking as classes are moved around to new namespaces.

In general, these changes should only affect those who have written custom code that interacts with the framework, though you may need to update your configuration files, and any Blade components you may have published.

### Added
- Added a JSON build information manifest automatically generated after a site build [#465](https://github.com/hydephp/develop/pull/465)

### Changed
- Moved class StaticPageBuilder to Actions namespace
- Moved class AbstractBuildTask to Concerns namespace
- Moved class AbstractMarkdownPage to Concerns namespace
- Moved class AbstractPage to Concerns namespace
- Moved class Application into Foundation namespace

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed interface IncludeFacadeContract
- Removed deprecated interface AssetServiceContract
- Removed deprecated interface HydeKernelContract
- Removed deprecated and unused abstract class ActionCommand
- Removed unused function `array_map_unique`

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.
