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
- Method `Hyde::hasSiteUrl()` now returns false if the site URL is for localhost in https://github.com/hydephp/develop/pull/1726
- Method `Hyde::url()` will now return a relative URL instead of throwing an exception when supplied a path even if the site URL is not set in https://github.com/hydephp/develop/pull/1726

### Deprecated
- for soon-to-be removed features.

### Removed
- for now removed features.

### Fixed
- Fixed explicitly set front matter navigation group behavior being dependent on subdirectory configuration, fixing https://github.com/hydephp/develop/issues/1515 in https://github.com/hydephp/develop/pull/1703
- Fixed DataCollections file finding method not being able to be overridden https://github.com/hydephp/develop/issues/1716 in https://github.com/hydephp/develop/pull/1717
- Fixed PHP warning when trying to parse a Markdown file with just front matter without body https://github.com/hydephp/develop/issues/1705 in https://github.com/hydephp/develop/pull/1728
- Yaml data files no longer need to start with triple dashes to be parsed by DataCollections in https://github.com/hydephp/develop/pull/1733

### Security
- in case of vulnerabilities.

### Extra information

This release contains changes to how HydePHP behaves when a site URL is not set by the user.

These changes are made to reduce the chance of the default `localhost` value showing up in production environments.

Most notably, HydePHP now considers that default site URL `localhost` to mean that a site URL is not set, as the user has not set it.
This means that things like automatic canonical URLs will not be added, as Hyde won't know how to make them without a site URL. 
The previous behaviour was that Hyde used `localhost` in canonical URLs, which is never useful in production environments.

For this reason, we felt it worth it to make this change in a minor release, as it has a such large benefit for sites.

You can read more about the details and design decisions of this change in the following pull request https://github.com/hydephp/develop/pull/1726.
