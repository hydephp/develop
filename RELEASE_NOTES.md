## [Unreleased] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added
- Added support for using HTML comments to create Markdown code block filepath labels in https://github.com/hydephp/develop/pull/1693
- Added a config option to disable the theme toggle buttons to automatically use browser settings in https://github.com/hydephp/develop/pull/1697
- You can now specify which path to open when using the `--open` option in the serve command in https://github.com/hydephp/develop/pull/1694
- Added a `--format=json` option to the `route:list` command in https://github.com/hydephp/develop/pull/1724

### Changed
- When a navigation group is set in front matter, it will now be used regardless of the subdirectory configuration in https://github.com/hydephp/develop/pull/1703 (fixes https://github.com/hydephp/develop/issues/1515)
- Use late static bindings to support overriding data collections file finding in https://github.com/hydephp/develop/pull/1717 (fixes https://github.com/hydephp/develop/issues/1716)

### Deprecated
- for soon-to-be removed features.

### Removed
- for now removed features.

### Fixed
- Fixed explicitly set front matter navigation group behavior being dependent on subdirectory configuration, fixing https://github.com/hydephp/develop/issues/1515 in https://github.com/hydephp/develop/pull/1703
- Fixed DataCollections file finding method not being able to be overridden https://github.com/hydephp/develop/issues/1716 in https://github.com/hydephp/develop/pull/1717
- Fixed PHP warning when trying to parse a Markdown file with just front matter without body https://github.com/hydephp/develop/issues/1705 in https://github.com/hydephp/develop/pull/1728

### Security
- in case of vulnerabilities.
