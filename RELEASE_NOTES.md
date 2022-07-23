## [Unreleased] - YYYY-MM-DD

### About

This update makes breaking changes to the configuration. You will need to update your configuration to continue using the new changes. Each one has been documented in this changelog entry, which at the end has an upgrade guide.

### Added
- Added a new configuration file, `config/site.php`, see below
- Added RSS feed configuration stubs to `config/site.php`
- Added an `Includes` facade that can quickly import partials
- Added an automatic option to load footer Markdown from partial
- Added the `hyde.load_app_styles_from_cdn` option to load `_media/app.css` from the CDN

### Changed
- internal: Refactor navigation menu components and improve link helpers
- internal: The main Hyde facade class has been split to house the logic in the HydeKernel class, but all methods are still available through the new facade with the same namespace  
- internal: Move tests foundation to new testing package
- Move laravel-zero/framework Composer dependency to hyde/hyde package

- Moved config option `hyde.name` to `site.name`
- Moved config option `hyde.site_url` to `site.url`
- Moved config option `hyde.pretty_urls` to `site.pretty_urls`
- Moved config option `hyde.generate_sitemap` to `site.generate_sitemap`
- Moved config option `hyde.language` to `site.language`
- Moved config option `hyde.output_directory` to `site.output_directory`

- The default `site.url` is now `http://localhost` instead of `null`
- Merged configuration options for the footer, see below

### Deprecated
- for soon-to-be removed features.

### Removed
- Removed `\Hyde\Framework\Facades\Route`. You can swap out usages with `\Hyde\Framework\Models\Route` without side effects.

- Removed internal `$siteName` config variable from `config/hyde.php`
- Removed ConvertsFooterMarkdown.php

### Fixed
- Fixed bug [#260](https://github.com/hydephp/develop/issues/260) where the command to publish a homepage did not display the selected value when it was supplied as a parameter

### Security
- in case of vulnerabilities.


### Upgrade Guide

#### Using the new site config

Site-specific config options have been moved from `config/hyde.php` to `config/site.php`. The Hyde config is now used to configure behaviour of the site, while the site config is used to customize the look and feel, the presentation, of the site.

The following configuration options have been moved. The actual usages remain the same, so you can upgrade by using copying over these options to the new file.

- `hyde.name`
- `hyde.site_url` (is now just `site.url`)
- `hyde.pretty_urls`
- `hyde.generate_sitemap`
- `hyde.language`
- `hyde.output_directory`

If you have published and Blade views or written custom code that uses the config options, you may need to update them. You can do this by republishing the Blade views, and/or using search and replace across your code. VSCode has a useful feature to make this a breeze: `CMD/CTRL+Shift+F`.

#### Using the new footer config

The footer configuration options have been merged. Prior to this update, the config option looked as follows:
```php
// filepath: config/hyde.php
'footer' => [
  'enabled' => true,
  'markdown' => 'Markdown text...'
],
```

Now, the config option looks as follows:
```php
// filepath: config/hyde.php

// To use Markdown text
'footer' => 'Markdown text...',

// To disable it completely
'footer' => false,
```

As you can see, the new config option is a string or the boolean false instead of an array. We use the same option for both the Markdown text and the footer disabled state.
