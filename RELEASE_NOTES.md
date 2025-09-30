## [v2-dev] - YYYY-MM-DD

### About

Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.

### Added

- **Added a new consolidated Asset API to better handle media files.** in [#2006](https://github.com/hydephp/develop/pull/2006)
  - Added several new fluent methods to the `MediaFile` class, like `getLink()`, `getLength()`, `getMimeType()`, etc
  - Added new `HydeFront` facade to handle CDN links and Tailwind config injection
  - Added method `Asset::exists()` to check if a media file exists
  - Added a `Hyde::assets()` method to get all media file instances in the site
- **Improved Routes facade API with more intuitive method names** in [#2179](https://github.com/hydephp/develop/pull/2179)
  - **Breaking:** Renamed `Routes::get()` to `Routes::find()` to better indicate it may return null
  - **Breaking:** Renamed `Routes::getOrFail()` to `Routes::get()` to make the exception-throwing behavior the default and match Laravel conventions
  - This change requires code updates if you were using these methods - see upgrade guide below
- **Many MediaFile related helpers have been changed or completely rewritten** to provide a simplified API for interacting with media files
  - **Note:** For most end users, the changes will have minimal direct impact, but if you have custom code that interacts with media files, you may need to update it
  - The `Asset` facade has been restructured to be more scoped and easier to use, splitting out a separate `HydeFront` facade and inlining the `AssetService` class
  - All asset retrieval methods now return a `MediaFile` instance, which can be fluently interacted with, or cast to a string to get the link (which was the previous behavior)
  - The `Hyde::asset()` method and `asset()` function now return `MediaFile` instances instead of strings, and will throw an exception if the asset does not exist
  - Renamed method `Asset::hasMediaFile` to `Asset::exists` in [#1957](https://github.com/hydephp/develop/pull/1957)
  - Renamed method `MediaFile::getContentLength` to `MediaFile::getLength` in [#1904](https://github.com/hydephp/develop/pull/1904)
  - Replaced method `Hyde::mediaPath` with `MediaFile::sourcePath` in [#1911](https://github.com/hydephp/develop/pull/1911)
  - Replaced method `Hyde::siteMediaPath` with `MediaFile::outputPath` in [#1911](https://github.com/hydephp/develop/pull/1911)
  - An exception will now be thrown if you try to get a media file that does not exist in order to prevent missing assets from going unnoticed in [#1932](https://github.com/hydephp/develop/pull/1932)
- **MediaFile performance improvements:**
  - Media assets are now cached in the HydeKernel, giving a massive performance boost and making it easier to access the instances in [#1917](https://github.com/hydephp/develop/pull/1917)
  - Media file metadata is now lazy loaded and then cached in memory, providing performance improvements for files that may not be used in a build in [#1933](https://github.com/hydephp/develop/pull/1933)
  - We now use the much faster `CRC32` hashing algorithm instead of `MD5` for cache busting keys in [#1918](https://github.com/hydephp/develop/pull/1918)
- **Ported the HydeSearch plugin used for the documentation search to be an Alpine.js implementation** in [#2029](https://github.com/hydephp/develop/pull/2029)
  - Renamed Blade component `hyde::components.docs.search-widget` to `hyde::components.docs.search-modal` in [#2029](https://github.com/hydephp/develop/pull/2029)
  - Added support for customizing the search implementation by creating a `resources/js/HydeSearch.js` file in [#2031](https://github.com/hydephp/develop/pull/2031)
- **Replaced Laravel Mix with Vite for frontend asset compilation** in [#2010](https://github.com/hydephp/develop/pull/2010)
  - **Breaking:** You must now use `npm run build` to compile your assets, instead of `npm run prod`
  - Bundled assets are now compiled directly into the `_media` folder, and will not be copied to the `_site/media` folder by the NPM command in [#2011](https://github.com/hydephp/develop/pull/2011)
- Added Vite as a build tool in [#2010](https://github.com/hydephp/develop/pull/2010)
- Added Vite facade in [#2016](https://github.com/hydephp/develop/pull/2016)
- Added a Vite HMR support for the realtime compiler in [#2016](https://github.com/hydephp/develop/pull/2016)
- Added a `Feature::fromName()` enum helper in [#1895](https://github.com/hydephp/develop/pull/1895)
- Added a custom Blade-based heading renderer for Markdown conversions in [#2047](https://github.com/hydephp/develop/pull/2047)
- Added a new Hyde Vite plugin in [#2160](https://github.com/hydephp/develop/pull/2160)
- Added a new `\Hyde\Framework\Actions\PreBuildTasks\TransferMediaAssets` build task to handle media assets transfers for site builds in [#1536](https://github.com/hydephp/develop/pull/1536)
- Added a new `\Hyde\Framework\Exceptions\InvalidConfigurationException` exception class to handle invalid configuration exceptions in [#1799](https://github.com/hydephp/develop/pull/1799)
- Added a new `\Hyde\Framework\Exceptions\ParseException` exception class to handle parsing exceptions in data collection files in [#1732](https://github.com/hydephp/develop/pull/1732)
- Added a new simplified blog post image front matter schema using a new "caption" field in [#2175](https://github.com/hydephp/develop/pull/2175)
- Added environment variable support for saving previews in [#1996](https://github.com/hydephp/develop/pull/1996)
- Added new `npm run build` command for compiling frontend assets with Vite in [#2010](https://github.com/hydephp/develop/pull/2010)
- Added support for PHP 8.4 in [#2141](https://github.com/hydephp/develop/pull/2141)
- Added support for resolving dynamic links to source files in Markdown documents in [#1590](https://github.com/hydephp/develop/pull/1590)
- Added support for setting `booting()` and `booted()` callbacks in `HydeExtension` classes, allowing extension developers to hook into the kernel boot process more easily in [#1847](https://github.com/hydephp/develop/pull/1847)
- Added support for setting custom navigation items in the YAML configuration in [#1818](https://github.com/hydephp/develop/pull/1818)
- Added support for setting extra attributes for navigation items in [#1824](https://github.com/hydephp/develop/pull/1824)
- Added support for setting the blog post publishing date as a prefix in the source file name in [#2000](https://github.com/hydephp/develop/pull/2000)
- Added support for specifying features in the YAML configuration in [#1896](https://github.com/hydephp/develop/pull/1896)
- Introduced a new navigation config builder class to simplify navigation configuration in [#1827](https://github.com/hydephp/develop/pull/1827)
- Markdown headings are now compiled using our custom Blade-based heading renderer in [#2047](https://github.com/hydephp/develop/pull/2047) - The `id` attributes for heading permalinks have been moved from the anchor to the heading element in [#2052](https://github.com/hydephp/develop/pull/2052)
- The `\Hyde\Facades\Features` class is no longer marked as internal, and is now thus part of the public API in [#1647](https://github.com/hydephp/develop/pull/1647)
- The `publish:views` command is now interactive on Unix-like systems in [#2062](https://github.com/hydephp/develop/pull/2062)
- You can now add custom posts to the blog post feed component when including it directly in [#1893](https://github.com/hydephp/develop/pull/1893)
- You can now specify sidebar item priorities by adding a numeric prefix to documentation page source file names in [#1709](https://github.com/hydephp/develop/pull/1709)
- You can now forward method calls to the underlying `DateTime` instance in `DateString` instances in [#2235](https://github.com/hydephp/develop/pull/2235)
- Added `Filesystem::ensureParentDirectoryExists()` method to reduce repetitive code when ensuring parent directories exist in [#2293](https://github.com/hydephp/develop/pull/2293)

### Changed

- **Breaking:** Renamed class `DataCollections` to `DataCollection` in [#1732](https://github.com/hydephp/develop/pull/1732)  For more information, see below.
- **Breaking:** Renamed the `hyde.enable_cache_busting` configuration option to `hyde.cache_busting` in [#1980](https://github.com/hydephp/develop/pull/1980)
- **Breaking:** Renamed the `hyde.navigation.subdirectories` configuration option to `hyde.navigation.subdirectory_display` in [#1818](https://github.com/hydephp/develop/pull/1818)
- **Breaking:** Replaced `--run-dev` and `--run-prod` build command flags with a single `--vite` flag that uses Vite to build assets in [#2013](https://github.com/hydephp/develop/pull/2013)
- **Breaking:** Removed `--run-prettier` build command flag and Prettier dependency in [#2312](https://github.com/hydephp/develop/pull/2312)
- **Breaking:** The `Author::create()` method now returns an array instead of a `PostAuthor` instance in [#1798](https://github.com/hydephp/develop/pull/1798) For more information, see below.
- **Breaking:** The `Author::get()` method now returns `null` if an author is not found, rather than creating a new instance in [#1798](https://github.com/hydephp/develop/pull/1798)  For more information, see below.
- **Breaking:** The `hyde.authors` config setting should now be keyed by the usernames in [#1782](https://github.com/hydephp/develop/pull/1782) For more information, see below.
- **Breaking:** The `hyde.features` configuration format has changed to use Enums instead of static method calls in [#1649](https://github.com/hydephp/develop/pull/1649)  For more information, see below.
- **Breaking:** The custom navigation item configuration now uses array inputs instead of the previous format in [#1818](https://github.com/hydephp/develop/pull/1818)  For more information, see the upgrade guide below.
- **Breaking:** The navigation system internals have been rewritten into a new Navigation API in [#1568](https://github.com/hydephp/develop/pull/1568) This change is breaking for custom navigation implementations. For more information, see below.
- **Breaking:** We now support PHP 8.2–8.4 instead of 8.1–8.3 in [#2141](https://github.com/hydephp/develop/pull/2141)
- **Breaking:** We switched from using CJS to ESM in the frontend tool scaffolding. If you have custom script includes you need to migrate them. See below and the pull request for details in [#2159](https://github.com/hydephp/develop/pull/2159)
- **Breaking:** We upgraded from the TailwindCSS version from v3 to v4 in [#2146](https://github.com/hydephp/develop/pull/2146) You may want to run `npx @tailwindcss/upgrade` in your project if you have custom Tailwind classes in your project. See the information below for details.
- **Dependency:** Switched to forked version of the Torchlight client in [#2141](https://github.com/hydephp/develop/pull/2141)
- **Dependency:** Updated Symfony/yaml to ^7.0 in [#2141](https://github.com/hydephp/develop/pull/2141)
- **Dependency:** Updated illuminate/support and illuminate/view to ^11.0 in [#2141](https://github.com/hydephp/develop/pull/2141)
- **Dependency:** Updated minimum PHP requirement to 8.2 in [#2141](https://github.com/hydephp/develop/pull/2141)
- **Dependency:** Upgraded from Laravel 10 to Laravel 11 in [#2141](https://github.com/hydephp/develop/pull/2141)
- **Dependency:** Upgraded to HydeFront v4 in [#2214](https://github.com/hydephp/develop/pull/2214)
- **Medium:** The `route` function will now throw a `RouteNotFoundException` if the route does not exist in [#1741](https://github.com/hydephp/develop/pull/1741)
- **Minor:** Changed the default build task message to make it more concise in [#1659](https://github.com/hydephp/develop/pull/1659)
- **Minor:** Data collection files are now validated for syntax errors during discovery in [#1732](https://github.com/hydephp/develop/pull/1732)
- **Minor:** Due to changes in the navigation system, it is possible that existing configuration files will need to be adjusted in order for menus to look the same (in terms of ordering etc.)
- **Minor:** Methods in the `Includes` facade now return `HtmlString` objects instead of `string` in [#1738](https://github.com/hydephp/develop/pull/1738) For more information, see below.
- **Minor:** Navigation menu items are now no longer filtered by duplicates (meaning two items with the same label can now exist in the same menu) in [#1573](https://github.com/hydephp/develop/pull/1573)
- **Minor:** The `processing_time_ms` attribute in the `sitemap.xml` file has now been removed in [#1744](https://github.com/hydephp/develop/pull/1744)
- **Minor:** The documentation article component now supports disabling the semantic rendering using a falsy value in [#1566](https://github.com/hydephp/develop/pull/1566)
- **Minor:** Updated the `Hyde::url()` helper throw a `BadMethodCallException` instead `BaseUrlNotSetException` when no site URL is set and no path was provided to the method in [#1890](https://github.com/hydephp/develop/pull/1890)
- **Minor:** Updated the blog post layout and post feed component to use the `BlogPosting` Schema.org type instead of `Article` in [#1887](https://github.com/hydephp/develop/pull/1887)
- **Minor:** `Includes::path()` and `Includes::get()` methods now normalize paths to be basenames to match the behavior of the other include methods in [#1738](https://github.com/hydephp/develop/pull/1738) This means that nested directories are no longer supported, as you should use a data collection for these
- Added more rich markup data to blog post components in [#1888](https://github.com/hydephp/develop/pull/1888) (Note that this inevitably changes the HTML output of the blog post components, and that any customized templates will need to be republished to reflect these changes)
- Calling the `DataCollection` methods will no longer create the data collections directory in [#1732](https://github.com/hydephp/develop/pull/1732)
- Calling the `Include::path()` method will no longer create the includes directory in [#1707](https://github.com/hydephp/develop/pull/1707)
- Changed how the documentation search is generated, to be an `InMemoryPage` instead of a post-build task in [#1498](https://github.com/hydephp/develop/pull/1498)
- Colored Markdown blockquotes are now rendered using Blade and TailwindCSS in [#2056](https://github.com/hydephp/develop/pull/2056) This change is not visible in the rendered result, but the HTML output has changed
- Extracted CSS component partials in HydeFront in [#2038](https://github.com/hydephp/develop/pull/2038)
- Improved how the `MarkdownService` class is accessed by binding it into the service container in [#1922](https://github.com/hydephp/develop/pull/1922)
- Improved the media asset transfer build task to have better output in [#1904](https://github.com/hydephp/develop/pull/1904)
- Improved the sitemap data generation to be smarter and more dynamic in [#1744](https://github.com/hydephp/develop/pull/1744)
- Markdown includes are now converted to HTML using the custom HydePHP Markdown service, meaning they now support full GFM spec and custom Hyde features like colored blockquotes and code block filepath labels in [#1738](https://github.com/hydephp/develop/pull/1738)
- Markdown returned from includes are now trimmed of trailing whitespace and newlines in [#1738](https://github.com/hydephp/develop/pull/1738)
- Media asset files are now copied using the new build task instead of the deprecated `BuildService::transferMediaAssets()` method in [#2024](https://github.com/hydephp/develop/pull/2024)
- Moved Blade view `hyde::pages.documentation-search` to `hyde::pages.docs.search` in [#2033](https://github.com/hydephp/develop/pull/2033)
- Moved the Vite build step to run before the site build to prevent duplicate media asset transfers in [#2013](https://github.com/hydephp/develop/pull/2013)
- Moved the sidebar documentation to the documentation pages section for better organization
- Normalized default Tailwind Typography Prose code block styles to match Torchlight's theme, ensuring consistent styling across Markdown and Torchlight code blocks in [#2036](https://github.com/hydephp/develop/pull/2036)
- Overhauled the blog post author feature in [#1782](https://github.com/hydephp/develop/pull/1782)
- Renamed the parameter `category` to `group` in the `publish:views` command in [#2166](https://github.com/hydephp/develop/pull/2166)
- Reorganized and cleaned up the navigation and sidebar documentation for improved clarity
- Replaced HydeFront styles with Tailwind in [#2024](https://github.com/hydephp/develop/pull/2024)
- Skipped build tasks will now exit with an exit code of 3 instead of 0 in [#1749](https://github.com/hydephp/develop/pull/1749)
- The `Markdown::render()` method will now always render Markdown using the custom HydePHP Markdown service (thus getting smart features like our Markdown processors) in [#1900](https://github.com/hydephp/develop/pull/1900)
- The `hasFeature` method on the Hyde facade and HydeKernel now only accepts a Feature enum value instead of a string for its parameter in [#1650](https://github.com/hydephp/develop/pull/1650)
- The build command now groups together all `InMemoryPage` instances under one progress bar group in [#1897](https://github.com/hydephp/develop/pull/1897)
- The full page documentation search now generates its heading using smarter natural language processing based on the configured sidebar header in [#2032](https://github.com/hydephp/develop/pull/2032)
- The realtime compiler now only serves assets from the media source directory (`_media`), and no longer checks the site output directory (`_site/media`) in [#2012](https://github.com/hydephp/develop/pull/2012)
- Updated default configuration to no longer save previewed pages in [#1995](https://github.com/hydephp/develop/pull/1995)

### Fixed

- Added missing collection key types in Hyde facade method annotations in [#1784](https://github.com/hydephp/develop/pull/1784)
- The `app.css` file will no longer be copied to the media output directory when app styles are configured to be loaded from a CDN in [#2180](https://github.com/hydephp/develop/pull/2180)
- The `app.js` file will now only be compiled if it has scripts in [#2028](https://github.com/hydephp/develop/pull/2028)

### Removed

- Added missing collection key types in Hyde facade method annotations in [#1784](https://github.com/hydephp/develop/pull/1784)
- The `app.css` file will no longer be copied to the media output directory when app styles are configured to be loaded from a CDN in [#2180](https://github.com/hydephp/develop/pull/2180)
- The `app.js` file will now only be compiled if it has scripts in [#2028](https://github.com/hydephp/develop/pull/2028)

### Package updates

#### Realtime Compiler

- Simplified the asset file locator to only serve files from the media source directory in https://github.com/hydephp/develop/pull/2012
- Added Vite HMR support in https://github.com/hydephp/develop/pull/2016
- Added Laravel Herd support in https://github.com/hydephp/develop/pull/2227

#### HydeFront

- Removed all Sass styles after porting everything to Tailwind in https://github.com/hydephp/develop/pull/2024
- Removed the `hyde.css` file in https://github.com/hydephp/develop/pull/2037 as all its styles were refactored to Tailwind in https://github.com/hydephp/develop/pull/2024
- Extracted CSS component partials in https://github.com/hydephp/develop/pull/2038

### Upgrade Guide

Please see the "Breaking changes & upgrade guide" section below for more information.

## Breaking changes & upgrade guide

<!-- Editors note: The following will be part of the documentation and not the changelog, which is why the heading levels are reset. -->

Please read through this section to ensure your site upgrades smoothly.

## Before you start

Before you start, please upgrade your application to at least HydePHP v1.6 as that version contains helpers to make the upgrade process easier.

## TailwindCSS v4

We upgraded the TailwindCSS version from v3 to v4. If you have written custom markup with Tailwind classes you should read the Tailwind v4 [Upgrade Guide](https://tailwindcss.com/docs/upgrade-guide#changes-from-v3) so you know which breaking changes were introduced. Thankfully, the team behind Tailwind provide an automated [Upgrade Tool](https://tailwindcss.com/docs/upgrade-guide#using-the-upgrade-tool). Run it with the following command:

```bash
$ npx @tailwindcss/upgrade
```

## High impact

### Switch frontend tooling to full ESM support

The frontend tooling has been switched from CommonJS to ESM. This means that all JavaScript files are now ESM modules, and you will need to update your custom scripts to use ESM syntax.

If you only used the default HydePHP frontend (without custom JavaScript), no action is needed. Otherwise, please read the upgrade guide here: https://github.com/hydephp/develop/pull/2159

### Navigation system rewrite

The navigation system has been rewritten into a new Navigation API. This change is breaking for custom navigation implementations, see more down below.

For most users, the only impact will be that configuration files need to be updated to use the new configuration format. Due to the internal changes,
it's also possible that menu items will be in a slightly different order than before, depending on your setup. Please verify that your site's menus
look as expected after upgrading, and adjust the configuration files if necessary, before deploying to production.

### Navigation and sidebar configuration changes

The navigation and sidebar configuration files have been updated to use the new Navigation API.
This means that you will need to update your configuration files to use the new format.

The easiest way to upgrade is to publish updated configuration files (`hyde.php` and `docs.php`) and copy over your customizations.

The following configuration entries have been updated:

- Changed configuration option `docs.sidebar_order` to `docs.sidebar.order` in https://github.com/hydephp/develop/pull/1583
- Upgrade path: Move the `sidebar_order` option's array in the `config/docs.php` file into the `sidebar` array in the same file.

- Changed configuration option `docs.table_of_contents` to `docs.sidebar.table_of_contents` in https://github.com/hydephp/develop/pull/1584
- Upgrade path: Move the `table_of_contents` option's array in the `config/docs.php` file into the `sidebar` array in the same file.

### Features configuration changes

The `hyde.features` configuration format has changed to use Enums instead of static method calls. This change is breaking as it will require you to update your `config/hyde.php` file.

#### Instead of

```php
// filepath: config/hyde.php

'features' => [
    // Page Modules
    Features::htmlPages(),
    Features::markdownPosts(),
    Features::bladePages(),
    Features::markdownPages(),
    Features::documentationPages(),

    // Frontend Features
    Features::darkmode(),
    Features::documentationSearch(),

    // Integrations
    Features::torchlight(),
],
```

#### Use instead

```php
// filepath: config/hyde.php

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

Of course, if you have disabled any of the features, do not include them in the new array.

## General impact

### Post Author changes

This release makes major improvements to the usability and design of the blog post author feature.

Here is the full list of changes:

- Breaking: The `hyde.authors` config setting must now be keyed by the usernames, instead of providing the username in the author facade constructor.
- Breaking: The `Author::create()` method now returns an array instead of a `PostAuthor` instance. This only affects custom code that uses the `Author` facade.
- Breaking: The `Author::get()` method now returns `null` if an author is not found, rather than creating a new instance. This only affects custom code that uses the `Author` facade.
- Removed: The deprecated `PostAuthor::getName()` method has been removed (use `$author->name` instead).
- Changed: Author usernames are now automatically normalized (converted to lowercase and spaces replaced with underscores in order to ensure URL routability).
- Changed: If an author display name is not provided, it is now intelligently generated from the username.
- Feature: Authors can now be set in the YAML configuration.
- Feature: Added a `$author->getPosts()` method to get all of an author's posts.
- Feature: Authors now support custom biographies, avatars, and social media links. Note that these are not currently used in any of the default templates, but you can use them in your custom views.
- The collection of site authors is now stored in the HydeKernel, meaning authors can be accessed through `Hyde::authors()`.
- The `PostAuthor` class is now Arrayable and JsonSerializable.

#### Upgrade guide:

1. Update your `config/hyde.php` file to use the new author configuration format:

   ```php
   'authors' => [
       'username' => Author::create(
           name: 'Display Name',
           website: 'https://example.com',
           bio: 'Author bio',
           avatar: 'avatar.png',
           socials: ['twitter' => '@username']
       ),
   ],
   ```

2. Review and update any code that uses the `Author` facade:

- The `create()` method now returns an array instead of a `PostAuthor` instance.
- The `get()` method may return `null`, so handle this case in your code.

3. Check your blog post front matter and ensure that `author` fields match the new username keys in your configuration.

4. If you have custom templates that use author data, update them to:

- Optional: Feel free to use the new available fields: `bio`, `avatar`, and `socials`.
- Account for usernames now being lowercase with underscores which may lead to changed HTML or URL paths.

5. If you were relying on `Author::get()` to create new authors on the fly, update your code to handle `null` returns or create authors explicitly.

For more information, see https://github.com/hydephp/develop/pull/1782 and https://github.com/hydephp/develop/pull/1798

### Documentation search page changes

The documentation search page and search index have been changed to be generated as `InMemoryPages` instead of a post-build task.

The main impact noticeable to most users by this is the implicit changes, like the pages showing up in the dashboard and route list command.

In case you have customized the `GenerateSearch` post-build task you may, depending on what you were trying to do,
want to adapt your code to interact with the new `InMemoryPage`, which is generated in the `HydeCoreExtension` class.

For more information, see https://github.com/hydephp/develop/pull/1498.

## Medium impact

### Features class method renames

The following methods in the `Features` class have been renamed to follow a more consistent naming convention:

- `Features::enabled()` has been renamed to `Features::has()`
- `Features::sitemap()` has been renamed to `Features::hasSitemap()`
- `Features::rss()` has been renamed to `Features::hasRss()`

Note that this class was previously marked as internal in v1, but the change is logged here in case it was used in configuration files or custom code.

### Asset API Changes

#### Overview

For most end users, the changes to the Asset API in HydePHP 2.x will have minimal direct impact. However, if you have custom code that interacts with media files, you may need to update it.

The most important thing to note is that all asset retrieval methods now return a `MediaFile` instance, which can be fluently interacted with, or cast to a string to get the link (which was the previous behavior).

#### Side effects to consider

Regardless of if you need to make changes to your code, there are a few side effects to consider:

- All cache busting keys will have changed since we changed the hashing algorithm from `MD5` to `CRC32`.
- Media file getters now return MediaFile instances instead of strings. But these can still be used the same way in Blade `{{ }}` tags, as they can be cast to strings.
- Due to the internal normalizations, we will consistently use cache busting keys and use qualified paths when site URLs are set.
- An exception will be thrown if you try to get a media file that does not exist in order to prevent missing assets from going unnoticed.

These side effects should not have any negative impact on your site, but may cause the generated HTML to look slightly different.

#### Impact on Your Code

If you are using strict type declarations, you may need to update your code to expect a `MediaFile` instance instead of a string path; or you should cast the `MediaFile` instance to a string when needed.

Most changes were made in https://github.com/hydephp/develop/pull/1904 which contains extra information and the reasoning behind the changes.

#### Updating Your Code

Once you have determined that you need to update your code, here are the steps you should take:

1. Update calls to renamed methods:

   ```php
   // Replace this:                      With this:
   Hyde::mediaLink('image.png')      =>  Hyde::asset('image.png');
   Asset::mediaLink('image.png')     =>  Asset::get('image.png');
   Asset::hasMediaFile('image.png')  =>  Asset::exists('image.png');
   Asset::cdnLink('app.css')         =>  HydeFront::cdnLink('app.css');
   Asset::injectTailwindConfig()     =>  HydeFront::injectTailwindConfig();
   FeaturedImage::isRemote($source)  =>  Hyperlinks::isRemote($source);
   ```

2. Rename the option `hyde.enable_cache_busting` to `hyde.cache_busting` in your configuration file.

3. Remove any references to `hyde.hydefront_version` and `hyde.hydefront_cdn_url` in your config files as these options have been removed.

4. If you were using `AssetService` directly, refactor your code to use the new `Asset` facade, `MediaFile` class, or `HydeFront` facade as appropriate.

These changes simplify the Asset API and provide more robust handling of media files. The new `MediaFile` class offers additional functionality for working with assets.

## Low impact

### Navigation internal changes

The navigation system has been rewritten into a new Navigation API. This change is breaking for custom navigation implementations.

If you have previously in your custom code done any of the following, or similar, you will need to adapt your code to use the new Navigation API:

- Created custom navigation menus or Blade components
- Extended or called the navigation related classes directly
- Customized the navigation system in any way beyond the standard configuration

#### Upgrade guide

Due to the scope of the rewrite, the easiest and fastest way to upgrade your code is to recreate it using the new Navigation API.

- For a full comparison of the changes, you may see the PR that introduced the new API: https://github.com/hydephp/develop/pull/1568/files
- For information on how to use the new Navigation API, see the documentation: https://hydephp.com/docs/2.x/navigation-api
- If you use DataCollections, you should read the upgrade path below as there are breaking changes to the DataCollection API.

### HTML ID changes

Some HTML IDs have been renamed to follow a more consistent naming convention.

If you have used any of the following selectors in custom code you wrote yourself, you will need to update to use the new changed IDs.

#### https://github.com/hydephp/develop/pull/1622

- Rename HTML ID `#searchMenu` to `#search-menu`
- Rename HTML ID `#searchMenuButton` to `#search-menu-button`
- Rename HTML ID `#searchMenuButtonMobile` to `#search-menu-button-mobile`

### New documentation search implementation

As the new documentation search implementation brings changes to their code API you may need to adapt your code
according to the information below in case you wrote custom code that interacted with these parts of the codebase.

- The `GenerateSearch` post-build task has been removed. If you have previously extended or customized this class,
  you will need to adapt your code, as the search index files are now handled implicitly during the standard build process,
  as the search pages are now added to the kernel page and route collection. (https://github.com/hydephp/develop/pull/1498)

- If your site has a custom documentation search page, for example `_docs/search.md` or `_pages/docs/search.blade.php`,
  that page will no longer be built when using the specific `build:search` command. It will, of course,
  be built using the standard `build` command. https://github.com/hydephp/develop/commit/82dc71f4a0e7b6be7a9f8d822fbebe39d2289ced

- In the highly unlikely event your site customizes any of the search pages by replacing them in the kernel route collection,
  you would now need to do that in the kernel page collection due to the search pages being generated earlier in the lifecycle.
  https://github.com/hydephp/develop/commit/82dc71f4a0e7b6be7a9f8d822fbebe39d2289ced

### Media asset transfer implementation changes

The internals of how media asset files are copied during the build process have been changed. For most users, this change
has no impact. However, if you have previously extended this method, or called it directly from your custom code,
you will need to adapt your code to use the new `TransferMediaAssets` build task.

For example, if you triggered the media transfer with a build service method call, use the new build task instead:

```php
(new BuildService)->transferMediaAssets();

(new TransferMediaAssets())->run();
```

### Includes facade changes

The following methods in the `Includes` facade now return `HtmlString` objects instead of `string`:

- `Includes::html()`
- `Includes::blade()`
- `Includes::markdown()`

- This means that you no longer need to use `{!! !!}` to render the output of these methods in Blade templates, instead just use `{{ }}`.
- If you have used the return value of these methods in custom code, you may need to adjust your code to work with the new return type.

For more information, see the RFC that proposed this change: https://github.com/hydephp/develop/issues/1734
The RFC was implemented in https://github.com/hydephp/develop/pull/1738

#### Remember to escape output if necessary

**Note:** Remember that this means that includes are **no longer escaped** by default, so make sure to escape the output if necessary, for example if the content is user-generated.

- (Use `{{ e(Includes::html('foo')) }}` instead of `{{ Includes::html('foo') }}` to escape the output, matching the previous behavior.)

### DataCollection API changes

The DataCollection feature has been reworked to improve the developer experience and make it more consistent with the rest of the API.

Unfortunately, this means that existing setups may need to be adjusted to work with the new API.

#### Upgrade guide

- The `DataCollections` class has been renamed to `DataCollection`. If you have used the `DataCollections` class in your code, you will need to update your code to use the new class name.

#### Changes

- Calling the `DataCollection` methods will no longer create the data collections directory automatically.
- The `DataCollection` class now validates the syntax of all data collection files during discovery, and throws a `ParseException` if the syntax is invalid.

#### Issues that may arise

If you start getting a `ParseException` when using the `DataCollection` class, it may be due to malformed data collection files.
Starting from this version, we validate the syntax of JSON and YAML in data files during discovery, including any front matter in Markdown data files.
We do this to help you catch errors early. See https://github.com/hydephp/develop/issues/1736 for more information.

For example, an empty or malformed JSON file will now throw an exception like this:

```php
\Hyde\Framework\Exceptions\ParseException: Invalid JSON in file: 'foo/baz.json' (Syntax error)
```

In order to normalize the thrown exceptions, we now rethrow the `ParseException` from `Symfony/Yaml` as our custom `ParseException` to match the JSON and Markdown validation.
Additionally, an exception will be thrown if a data file is empty, as this is unlikely to be intentional. Markdown files can have an empty body if front matter is present.

### Removal of `FeaturedImage::isRemote()` method

The `FeaturedImage::isRemote()` method has been removed in v2.0. This method was deprecated in v1.8.0 and has now been completely removed.

#### Upgrade guide

If you were using `FeaturedImage::isRemote()` in your code, you should replace it with `Hyperlinks::isRemote()`. Here's how to update your code:

```php
// Old code
FeaturedImage::isRemote($source);

// New code
use Hyde\Foundation\Kernel\Hyperlinks;

Hyperlinks::isRemote($source);
```

This change was implemented in https://github.com/hydephp/develop/pull/1883. Make sure to update any instances of `FeaturedImage::isRemote()` in your codebase to ensure compatibility with HydePHP v2.0.

### Routes facade API changes

The Routes facade API has been improved to better follow Laravel naming conventions and make the API more intuitive. This change affects code that directly uses the Routes facade methods.

#### Changes

- The `Routes::get()` method has been renamed to `Routes::find()` to better indicate that it may return null if a route is not found
- The `Routes::getOrFail()` method has been renamed to `Routes::get()` to make the exception-throwing behavior the default, matching Laravel conventions

#### Upgrade guide

If you have used the Routes facade in your custom code, update it as follows:

```php
// Old code:
$route = Routes::get('some-route'); // Returns null if not found
$route = Routes::getOrFail('some-route'); // Throws exception if not found

// New code:
$route = Routes::find('some-route'); // Returns null if not found
$route = Routes::get('some-route'); // Throws exception if not found
```

This change provides more intuitive method names and better type safety, with `find()` returning `?Route` and `get()` returning `Route`.

This change was implemented in https://github.com/hydephp/develop/pull/2179.

## New Asset System

### Abstract

The new asset system is a complete rewrite of the HydeFront asset handling system, replacing Laravel Mix with Vite, and favouring Blade-based components with Tailwind classes over CSS partials and custom stylesheets.

### Enhancements

- **Replaced Laravel Mix with Vite for frontend asset compilation.** ([#2010], [#2011], [#2012], [#2013], [#2016], [#2021])
    - Bundled assets are now compiled directly into the `_media` folder.
    - The realtime compiler now only serves assets from the media source directory (`_media`).
    - Added a new `npm run build` command for compiling frontend assets with Vite.
    - Added Vite facade for Blade templates.
    - Added Vite Hot Module Replacement (HMR) support to the realtime compiler.
    - Build command now uses Vite to compile assets when the `--vite` flag is passed.

- **Improved HydeFront integration.** ([#2024], [#2029], [#2031], [#2036], [#2037], [#2038], [#2039])
    - HydeFront styles are now refactored into Tailwind.
    - HydeFront now acts as a component library with granular Tailwind styles in `app.css`.
    - HydeSearch plugin ported to Alpine.js, improving performance and customizability.
    - Normalized Tailwind Typography Prose code block styles to match Torchlight.
    - Extracted CSS component partials in HydeFront.
    - Removed `hyde.css` from HydeFront, as all styles are now included in `app.css`.

- **Implemented a custom Blade-based heading renderer for Markdown.** ([#2047], [#2052])
    - Improves permalink handling and customization options.
    - `id` attributes for heading permalinks have been moved from the anchor to the heading element.
- **Colored Markdown blockquotes are now rendered using Blade and Tailwind CSS.** ([#2056])
- The `app.js` file will now only be compiled if it has scripts. ([#2028])


### Breaking Changes

- Replaced Laravel Mix with Vite. ([#2010])
    - You must now use `npm run build` to compile your assets, instead of `npm run prod`.
- Removed `--run-dev` and `--run-prod` build command flags, replaced by `--vite`. ([#2013])
- Removed `--run-prettier` build command flag.
- Removed `DocumentationPage::getTableOfContents()` method. Table of contents are now generated using a Blade component. ([#2045])
- Removed `hyde.css` from HydeFront, requiring recompilation of assets if you were extending it. ([#2037])
- Changed how HydeFront is included in projects.  Instead of separate `hyde.css` and `app.css`, all styles are now in `app.css`. ([#2024])


### Removals

- Removed Laravel Mix as a dependency. ([#2010])
- Removed `npm run prod` command. ([#2010])
- Removed CDN include for HydeSearch plugin. ([#2029])
- Removed the `<x-hyde::docs.search-input />` and `<x-hyde::docs.search-scripts />` Blade components, replaced by `<x-hyde::docs.hyde-search />`. ([#2029])
- Removed the `.torchlight-enabled` CSS class. ([#2036])
- Removed the `MarkdownService::withPermalinks` and `MarkdownService::canEnablePermalinks` methods. ([#2047])


### Blade-based table of contents generator

The way we generate table of contents for documentation pages have been changed from a helper method to a Blade component.

This new system is much easier to customize and style, and is up to 40 times faster than the old system.

See https://github.com/hydephp/develop/pull/2045 for more information.

#### Scope

The likelihood of impact is low, but if any of the following are true, you may need to update your code:

- If you have used the `Hyde\Framework\Actions\GeneratesTableOfContents` class in custom code, you will likely need to update that code for the rewritten class.
- If you have published the `resources/views/components/docs/sidebar-item.blade.php` component, you will need to update it to call the new component instead of the old generator rendering.
- If you have called the now removed `getTableOfContents` method of the `DocumentationPage` class in custom code, you will need to update that usage as to possibly call the new Blade component directly, depending on your use case.
- If you have called the now removed `hasTableOfContents` method of the `DocumentationPage` class in custom code you will need to replace the method call with `Config::getBool('docs.sidebar.table_of_contents.enabled', true)`

#### Changes
- Adds a new `resources/views/components/docs/table-of-contents.blade.php` component containing the structure and styles for the table of contents
- Rewrites the `GeneratesTableOfContents` class to use a custom implementation instead of using CommonMark
- The `execute` method of the `GeneratesTableOfContents` class now returns an array of data, instead of a string of HTML. This data should be fed into the new component
- Removed the `table-of-contents.css` file as styles are now made using Tailwind
- Removed the `heading-permalinks.css` file as styles are now made using Tailwind
- Removed the `blockquotes.css` file as styles are now made using Tailwind

## New features

<!-- Editors note: Todo: Maybe move to the relevant docs... -->

### Navigation configuration changes

The custom navigation item configuration format has been updated to use array inputs. This change allows for more flexibility and consistency in defining navigation items.

#### Old format:

```php
'navigation' => [
    'custom_items' => [
        'Custom Item' => '/custom-page',
    ],
],
```

#### New format:

```php
'navigation' => [
    'custom_items' => [
        ['label' => 'Custom Item', 'destination' => '/custom-page'],
    ],
],
```

Additionally, the `hyde.navigation.subdirectories` configuration option has been renamed to `hyde.navigation.subdirectory_display`. Update your configuration files accordingly.

### YAML configuration for navigation items

You can now set custom navigation items directly in your YAML configuration files. This provides an alternative to defining them in the PHP configuration files.

Example:

```yaml
navigation:
  custom_items:
    - label: Custom Item
      destination: /custom-page
```

### Extra attributes for navigation items

Navigation items now support extra attributes, allowing you to add custom data or styling to your navigation elements. You can set these attributes in both PHP and YAML configurations.

Example in PHP:

```php
'navigation' => [
    'custom_items' => [
        [
            'label' => 'Custom Item',
            'destination' => '/custom-page',
            'attributes' => ['class' => 'special-link', 'target' => '_blank'],
        ],
    ],
],
```

Example in YAML:

```yaml
navigation:
  custom_items:
    - label: Custom Item
      destination: /custom-page
      attributes:
        class: special-link
        target: _blank
```

These changes provide more flexibility and control over your site's navigation structure. Make sure to update your configuration files and any custom code that interacts with navigation items to align with these new formats and features.
