## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added a `@head` stack to the `head.blade.php` component in https://github.com/hydephp/develop/pull/1567
- Added a `Hyde::route()` helper to the `Hyde` facade in https://github.com/hydephp/develop/pull/1591
- Added new global helper functions (`asset()`, `route()`, `url()`) in https://github.com/hydephp/develop/pull/1592
- Added a new `Feature` enum to improve the `Features` facade in https://github.com/hydephp/develop/pull/1650

### Changed
- The `features` array in the `config/hyde.php` configuration file is now an array of `Feature` enums in https://github.com/hydephp/develop/pull/1650
- Internal: Updated the `Features::toArray` representation to use `camelCase` instead of `kebab-case` https://github.com/hydephp/develop/pull/1652 (the class is marked as internal, thus the change is not considered breaking)

### Deprecated
- Deprecated the static `Features` flag methods used in the configuration files in https://github.com/hydephp/develop/pull/1650 and will be removed in HydePHP v2.0

### Removed
- for now removed features.

### Fixed
- Realtime Compiler: Fixed responsive dashboard table issue in https://github.com/hydephp/develop/pull/1595

### Security
- in case of vulnerabilities.

### Upgrade Path

In order to prepare your project for HydePHP v2.0, you should update your `config/hyde.php` configuration file to use the new `Feature` enum for the `features` array.

Your new config array should look like this:

```php
    // Make sure to import the new Feature enum at the top of the file 
    use Hyde\Enums\Feature;
    
    // Then replace your enabled features with the new Feature enum cases
    'features' => [
        // Page Modules
        Feature::HtmlPages,
        Feature::MarkdownPosts,
        Feature::BladePages,
        Feature::MarkdownPages,
        Feature::DocumentationPages,

        // Frontend Features
        Feature::Darkmode,
        Feature::DocumentationSearch,

        // Integrations
        Feature::Torchlight,
    ],
```

If you need more help, you can see detailed upgrade instructions with screenshots in the pull request https://github.com/hydephp/develop/pull/1650
