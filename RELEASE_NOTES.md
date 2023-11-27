## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Adds a new fancy output for the realtime compiler serve command in https://github.com/hydephp/develop/pull/1444
- Added support for dot notation in the Yaml configuration files in https://github.com/hydephp/develop/pull/1478
- Added a config option to customize automatic sidebar navigation group names in https://github.com/hydephp/develop/pull/1481

### Changed
- The `docs.sidebar.footer` config option now accepts a Markdown string to replace the default footer in https://github.com/hydephp/develop/pull/1477
- Links in the `sitemap.xml` file are now relative when a site URL is not set, instead of using the default localhost URL in https://github.com/hydephp/develop/pull/1479

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed unhelpful boilerplate from the `hyde/hyde` `package.json` in https://github.com/hydephp/develop/pull/1436

### Fixed
- Fixed dot notation in Yaml configuration not being expanded (https://github.com/hydephp/develop/issues/1471) in https://github.com/hydephp/develop/pull/1478

### Security
- in case of vulnerabilities.
