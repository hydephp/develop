# Welcome to the HydePHP v3 planning document!

Having this document in code lets us know the devlopment state at any given point in the development tree.

## Planned features

- Change all HydePHP reposotiries to use `main` instead of `master` as the default branch. This change will be executed around the time of the release.

## Checklist before release:

- Publish new major version of the Vite plugin (due to Vite 8 upgrade) then revert the monorepo loading the local file https://github.com/hydephp/develop/pull/2414/changes/42e745675c0eec12b42376dcb445f592bbd0d650

## Changes requires to this branch

## Changes required to the v2 branch

---

## Release Notes

### New Features

- Added native support for versioned documentation pages. Register versions in the new `docs.versions` configuration option, and store the pages for each version in a matching subdirectory of the documentation source directory (like `_docs/1.x` and `_docs/2.x`). Each version is compiled to a matching subdirectory of the documentation output directory, and gets its own sidebar, search index, and search page. A version switcher dropdown is shown in the documentation sidebar, the main navigation links to the default version's index page, and a redirect page is generated at the documentation root pointing to the default version. Sidebar and search configuration entries (`docs.sidebar.order`, `docs.sidebar.labels`, `docs.sidebar.exclude`, and `docs.exclude_from_search`) match version-agnostic identifiers and route keys, so a single entry applies to the page in every version, while full versioned keys allow version-specific overrides. Enabling the feature is all or nothing: documentation source files stored outside the version directories are ignored, so pages that should live at the documentation root belong in the normal page source directory (like `_pages/docs/index.md`). Versioning is disabled by default, and single-version sites are unaffected. ([#2516](https://github.com/hydephp/develop/pull/2516))
- Redirects can now be declared as source and destination path pairs in the `hyde.redirects` configuration array. Hyde registers them with the kernel, includes them in `route:list`, and generates them through the normal site build.
- Added Blade Blocks for rendering Blade and Blade components from fenced code blocks in Markdown pages. The supported directives are `blade render` and `blade component(name)`, and the feature is controlled by `markdown.enable_blade`. ([#2504](https://github.com/hydephp/develop/pull/2504))
- Pages can now compile to non-HTML output files. Page classes declare their output file extension through the new static `$outputExtension` property (defaulting to `.html`), and in-memory page identifiers can declare a `.json`, `.txt`, or `.xml` extension directly, so `InMemoryPage::make('robots.txt', contents: ...)` compiles to `_site/robots.txt` through the standard site build. Only the HTML extension is implicit in route keys: pages compiled to non-HTML files keep their extension in the route key, formalizing the convention already used by the documentation search index.
- Pages can now control their own sitemap inclusion. Set `sitemap: false` in a page's front matter to exclude it from the generated `sitemap.xml`, or override the new `HydePage::showInSitemap()` method in custom page classes. Pages compiled to non-HTML output files (like `robots.txt`) are excluded by default, and `sitemap: true` front matter opts such a page back in.
- The sitemap and RSS feed are now first-class pages instead of post-build side effects: when the respective feature is enabled, `sitemap.xml` and the RSS feed (`feed.xml`, or the configured `hyde.rss.filename`) are registered as routes, so they are served by `hyde serve`, listed in `route:list`, included in the build manifest, and compiled through the standard site build. The output can be customized by rebinding the `SitemapGenerator` or `RssFeedGenerator` class in the service container, and registering a user-defined page with the same route key (from a service provider, booting callback, or extension) replaces the generated page entirely.

### Feature Changes

- Blade in Markdown is now enabled by default. The `markdown.enable_blade` option controls both `[Blade]:` directives and executable Blade Blocks. Hyde sites generally treat project content as trusted and reviewed; sites that compile untrusted or unreviewed Markdown can disable both forms with this option.
- Raw HTML in Markdown is now enabled by default. Hyde sites generally treat project content as trusted and reviewed; sites that compile untrusted or unreviewed Markdown can set `markdown.allow_html` to `false` to strip potentially unsafe HTML tags.

### Minor Changes and Cleanup

- Fixed documentation search index files leaking into the generated sitemap: `search.json` (and any other page compiled to a non-HTML output file) no longer appears in `sitemap.xml`. The sitemap generator now asks each page through `HydePage::showInSitemap()` instead of only filtering out redirect pages.
- The `Redirect` page class constructor now accepts an optional `$matter` parameter, used by the framework to hide the generated documentation root redirect from navigation menus. Existing usages are unaffected.
- The realtime compiler now resolves registered page routes before proxying static assets, replacing the hardcoded `search.json` exemption, so `hyde serve` serves any registered route regardless of its output extension. Registered pages now always win over a static file at the same path; the previous behavior of serving such a shadowing file only affected the dev server and no real setups are expected to be affected.

- Removed the legacy `checkForDeprecatedRunMixCommandUsage` check and the placeholder `--run-dev`/`--run-prod` options from the `build` command, which were kept in v2 only to surface a helpful error message. ([#2461](https://github.com/hydephp/develop/pull/2461))
- Removed the deprecated `hyde.server.dashboard` boolean config check from `DashboardController::enabled()`, which was kept in v2 for backwards compatibility but had since then been replaced with `hyde.server.dashboard.enabled`. ([#2461](https://github.com/hydephp/develop/pull/2462))
- Upgraded the bundled `vite` dependency from v7 to v8, and widened the `hyde-vite-plugin`'s `vite` peer dependency range from `>=6.3.5 <8.0.0` to `>=6.3.5 <9.0.0` so downstream projects can adopt Vite 8 without waiting for a new plugin major. The plugin's build config now targets Vite 8's Rolldown-based bundler (`rolldownOptions` instead of `rollupOptions`). ([#2414](https://github.com/hydephp/develop/pull/2414))

### Breaking Changes

- Renamed the static page class property `$fileExtension` to `$sourceExtension`, and the `fileExtension()` and `setFileExtension()` methods to `sourceExtension()` and `setSourceExtension()`. The old name was ambiguous now that page classes also declare an output extension through the new `$outputExtension` property, and the renamed pair makes the source/output distinction explicit. Custom page classes and code calling these APIs need the mechanical rename, which the planned automated upgrade script will handle (see the upgrade script rules section at the end of this document). Page discovery fails fast with an actionable exception for registered page classes that still use the old API, instead of silently skipping them during builds.
- In-memory page identifiers ending in `.json`, `.txt`, or `.xml` now compile to that path as-is instead of gaining a second `.html` extension. The old double-extension outputs (like `data.json.html`) were almost certainly never intended, so no real sites are expected to be affected.
- Redirect source paths declared in `hyde.redirects` ending in `.json`, `.txt`, or `.xml` are now rejected with an exception, since a meta refresh redirect cannot work for files served as non-HTML content. Previously such entries silently produced an unreachable `legacy.json.html` file, so no working configuration is affected.
- Removed the `GenerateSitemap` post-build task, as the sitemap is now generated through the page and route system. Sites that just enable or disable the sitemap through configuration are unaffected. Code referencing the task class — like a user-land `GenerateSitemap` build task relying on the same-basename override mechanism to replace the framework task — should register a custom `sitemap.xml` page or rebind `SitemapGenerator` in the container instead. The `build:sitemap` command now compiles the registered page, and reports failure with exit code 1 instead of 3 when no base URL is configured.
- Removed the `GenerateRssFeed` post-build task, as the RSS feed is now generated through the page and route system. Sites that just enable or disable the feed through configuration are unaffected. Code referencing the task class — like a user-land `GenerateRssFeed` build task relying on the same-basename override mechanism to replace the framework task — should register a custom page with the configured feed route key or rebind `RssFeedGenerator` in the container instead. The `build:rss` command now compiles the registered page, and still generates the feed regardless of the feature conditions when invoked explicitly.
- Removed `Redirect::create()`, `Redirect::store()`, and the `Redirect` constructor's `showText` argument. Redirects must now be declared in `hyde.redirects`, keeping all generated output inside the kernel-owned build graph. Redirect routes are intrinsically excluded from navigation menus and sitemaps, and always include an accessible fallback link.

- Removed the `rebuild` command (`RebuildPageCommand`). It was originally added to build a single file to disk before the realtime compiler existed, and later used internally by the RC to build-and-serve a path, but the RC now renders everything in-memory, leaving `rebuild` with no remaining consumer. It also had no safe user-facing use case: a single-page build only produces a correct `_site` when the page is self-contained, while a page change routinely invalidates aggregate outputs (sitemap, RSS, search index, post listings, navigation), so single-path building could silently leave a stale output directory that looked complete. The underlying single-page build capability remains available internally via the `StaticPageBuilder` action. ([#2490](https://github.com/hydephp/develop/pull/2490))

### Upgrade guide

Please fill in UPGRADE.md as you make changes.

- Blade in Markdown is now enabled by default, including `[Blade]:` directives and the new executable `blade render` and `blade component(name)` fenced code blocks. Existing projects with a published `config/markdown.php` retain their current `markdown.enable_blade` setting; set it to `true` to adopt the v3 default, or keep it `false` to disable both forms when compiling untrusted or unreviewed Markdown.
- Raw HTML in Markdown is now enabled by default. Existing projects with a published `config/markdown.php` retain their current `markdown.allow_html` setting; set it to `true` to adopt the v3 default, or keep it `false` when compiling untrusted or unreviewed Markdown.
- The `rebuild` command has been removed. If you need to build a single page programmatically, use `Hyde\Framework\Actions\StaticPageBuilder::handle()` instead.
- Move any calls to `Redirect::create()` or `Redirect::store()` into the `redirects` array in `config/hyde.php`, using the old path as the key and the destination as the value.
- Rename `$fileExtension` to `$sourceExtension` in custom page classes, and update any calls to `fileExtension()` or `setFileExtension()` to `sourceExtension()` and `setSourceExtension()`.
- If you referenced the removed `GenerateSitemap` or `GenerateRssFeed` build task classes (for example to override one with a same-basename user-land task), customize the output by rebinding `SitemapGenerator` or `RssFeedGenerator` in the service container or by registering your own page with the same route key instead.

---

## Upgrade script rules

We will provide an automated upgrade script (likely Rector-based) when we finalize the release.
Until then, this section collects the rules that script needs to implement, so we don't lose
track of them. Add an entry here whenever a change requires mechanical migration of user code.

- Rename the static page class property `$fileExtension` to `$sourceExtension`, and the
  `fileExtension()` and `setFileExtension()` methods to `sourceExtension()` and
  `setSourceExtension()`. This covers property declarations in page classes
  (`public static string $fileExtension = '.md';`), direct static property access
  (`MarkdownPage::$fileExtension`, `$pageClass::$fileExtension`), static method calls
  (`MarkdownPage::fileExtension()`), and method declarations that override these methods
  in page classes (`public static function fileExtension(): string`) — the methods are
  public and non-final, and an un-renamed override would silently stop being called once
  the framework calls `sourceExtension()`. The rule must be scoped to
  `Hyde\Pages\Concerns\HydePage` subclasses (or known Hyde symbols) — it must not rename
  unrelated properties or methods that happen to share the name.
- The rename must also cover named arguments: `Page::setFileExtension(fileExtension: '.md')`
  becomes `Page::setSourceExtension(sourceExtension: '.md')`, since the parameter was renamed
  along with the method. Include a dedicated Rector fixture for this case.
- Dynamic references cannot be migrated automatically and should be called out as manual
  upgrade cases: variable method/property names (`$method = 'fileExtension';
  $pageClass::$method()`), reflection, and string-based access.
