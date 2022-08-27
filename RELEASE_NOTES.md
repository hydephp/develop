## [Unreleased] - YYYY-MM-DD

### About

This update deprecates two interfaces (contracts) and inlines them into their implementations.

The following interfaces are affected: `HydeKernelContract` and `AssetServiceContract`. These interfaces were used to access the service container bindings. Instead, you would now type hint the implementation class instead of the contract.

This update will only affect those who have written custom code that uses or type hints these interfaces, which is unlikely. If this does affect you, you can see this diff to see how to upgrade. https://github.com/hydephp/develop/pull/428/commits/68d2974d54345ec7c12fedb098f6030b2c2e85ee. In short, simply replace `HydeKernelContract` and `AssetServiceContract` with `HydeKernel` and `AssetService`.

### Added
- for new features.

### Changed
- The documentation page layout has been internally refactored to utilize more Blade components. This only affects those who have extended or customized the documentation components. Some documentation components have also been renamed.

### Deprecated
- Deprecate interface HydeKernelContract, type hint the HydeKernel::class instead
- Deprecate interface AssetServiceContract, type hint the AssetService::class instead
  
### Removed
- Removed legacy `.js-enabled` class from documentation pages

### Fixed
- The list element of the documentation page sidebar had a conflicting ID (`#sidebar`) and has now been changed to `#sidebar-navigation` which may break edge cases where this component is styled or interacted with outside of the framework.
- Fix documentation page flickering [#388](https://github.com/hydephp/develop/issues/388)

### Security
- in case of vulnerabilities.
