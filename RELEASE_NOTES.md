## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- for new features.

### Changed
- internal: DiscoveryService.php is no longer deprecated
- internal: CollectionService.php was merged into DiscoveryService

### Deprecated
- for soon-to-be removed features.

### Removed
- internal: CollectionService.php has been removed, all its functionality has been moved to DiscoveryService
- internal: The `$currentPage` parameter of a few methods has been removed, it is no longer necessary due to it being inferred from the view being rendered

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.
