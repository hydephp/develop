## [Unreleased] - YYYY-MM-DD

### About

This update makes breaking changes to the configuration. You will need to update your configuration to continue using the new changes. Each one has been documented in this changelog entry, which at the end has an upgrade guide.

### Added
- Added a new configuration file, `config/site.php`, see below
- Added RSS feed configuration stubs to `config/site.php`
- Added an `Includes` facade that can quickly import partials
- Added an automatic option to load footer Markdown from partial

### Changed
- internal: Refactor navigation menu components and improve link helpers

- Moved config option `hyde.name` to `site.name`
- Moved config option `hyde.site_url` to `site.url`
- Moved config option `hyde.pretty_urls` to `site.pretty_urls`
- Moved config option `hyde.generate_sitemap` to `site.generate_sitemap`
- Moved config option `hyde.language` to `site.language`
- Moved config option `hyde.output_directory` to `site.output_directory`

- The default `site.url` is now `http://localhost` instead of `null`

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed `\Hyde\Framework\Facades\Route`. You can swap out usages with `\Hyde\Framework\Models\Route` without side effects.

- Removed internal `$siteName` config variable from `config/hyde.php`

### Fixed
- for any bug fixes.

### Security
- in case of vulnerabilities.


### Upgrade Guide

Site-specific config options have been moved from `config/hyde.php` to `config/site.php`. The Hyde config is now used to configure behaviour of the site, while the site config is used to customize the look and feel, the presentation, of the site.

The following configuration options have been moved. The actual usages remain the same, so you can upgrade by using copying over these options to the new file.

- `hyde.name`
- `hyde.site_url` (is now just `site.url`)
- `hyde.pretty_urls`
- `hyde.generate_sitemap`
- `hyde.language`
- `hyde.output_directory`

If you have published and Blade views or written custom code that uses the config options, you may need to update them. You can do this by republishing the Blade views, and/or using search and replace across your code. VSCode has a useful feature to make this a breeze: `CMD/CTRL+Shift+F`.