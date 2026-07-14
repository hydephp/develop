# HydePHP v3.0 Upgrade Guide

## Overview

HydePHP v3 adds `InMemoryPage::file()` for creating virtual pages whose identifier is used as the exact output path,
allowing files such as `robots.txt`, `site.webmanifest`, nested JSON files, and extensionless outputs without extension
inference. Normal `InMemoryPage::make()` construction retains its historical HTML behavior:

```php
InMemoryPage::make('about', contents: $html);
// _site/about.html

InMemoryPage::make('robots.txt', contents: $text);
// _site/robots.txt.html

InMemoryPage::file('robots.txt', contents: $text);
// _site/robots.txt
```

Route keys are always derived from the resolved output path by removing only its final `.html` extension. This also
applies to exact-path files, so the page and file constructors intentionally differ for an identifier ending in HTML:

```php
InMemoryPage::file('download.html');
// output: download.html, route key: download

InMemoryPage::make('download.html');
// output: download.html.html, route key: download.html
```

Custom pages that override `getOutputPath()` no longer need to override `getRouteKey()`. The route key is resolved
lazily, so output paths backed by instance state are safe to initialize after calling the parent constructor.
The first route read fixes that identity for the rest of the page lifecycle, including when a `Route` is constructed.

The historical public readonly `routeKey` property is now a protected compatibility declaration backed by
an internal lazy cache. Direct reads such as `$page->routeKey` remain supported through the magic accessor, but new
code should call `$page->getRouteKey()`. Code that reflects public properties, uses `get_object_vars()`, inspects property visibility,
or depends on the native serialized property shape must account for that runtime compatibility change.

## Before You Begin

### Prerequisites

//

### Backup Your Project

Before starting the upgrade process, it's **strongly recommended** to:

- **Commit all changes to Git** - This allows you to easily revert if needed
- **Create a backup** of your entire project directory
- **Have a previous site build** so you can compare output

If you're not already using Git for version control, now is an excellent time to initialize a repository:

```bash
git init
git add .
git commit -m "Pre-upgrade backup before HydePHP v3.0"
```

### Estimated Time

//

## Step 1: Update Dependencies

### Update Composer Dependencies

//

### Update Node Dependencies

HydePHP v3 upgrades the bundled `vite` dependency from v7 to v8. Update your `package.json` `devDependencies` to require the new major version:

```json
{
    "devDependencies": {
        "vite": "^8.0.0"
    }
}
```

Then run `npm install` (or your package manager's equivalent) to pick up the update.

If you have a custom `vite.config.js` that overrides `build.rollupOptions`, note that Vite 8 builds with Rolldown by default. The `hyde-vite-plugin` now configures its own build options under `build.rolldownOptions` rather than `build.rollupOptions` — if your custom config only sets `rollupOptions`, double check your output still ends up where you expect after upgrading.

## Step 2: Review the Markdown Trust Defaults

HydePHP v3 enables both raw HTML and Blade in Markdown by default. The existing `markdown.enable_blade` setting controls both
`[Blade]:` directives and the new executable `blade render` and `blade component(name)` fenced code blocks. New
projects and projects without explicit settings can render arbitrary HTML and execute PHP during a build.

Existing projects normally keep their published `config/markdown.php` file during a dependency update. If yours
currently sets either option to `false`, that behavior remains disabled until you change it:

```php
// filepath: config/markdown.php
'allow_html' => true,
'enable_blade' => true,
```

When enabled, the following fences are executable. A fence using only `blade` remains an ordinary syntax-highlighted
code sample.

- `blade render`
- `blade component(name)`

The v3 defaults are intended for sites where Markdown is part of the trusted, reviewed project source. If you ingest
Markdown from users or another untrusted source, or your CI builds pull requests before review, disable raw HTML and
Blade in Markdown:

```php
// filepath: config/markdown.php
'allow_html' => false,
'enable_blade' => false,
```

These settings are not a security boundary for contributors who can add arbitrary project files, since they could add a
malicious `.blade.php` file instead. Review source changes before building them in a privileged environment.

## Step 3: Replace the Removed `rebuild` Command

The `rebuild` command has been removed in v3.0. It had no remaining internal consumers now that the realtime compiler renders pages entirely in-memory, and building a single page could silently leave aggregate outputs (sitemap, RSS, search index, navigation) stale while looking like a complete build.

**Before:**
```bash
php hyde rebuild _posts/hello-world.md
```

**After:**

If you need to build a single page programmatically, use `StaticPageBuilder::handle()` directly:

```php
use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;

StaticPageBuilder::handle(Pages::getPage('_posts/hello-world.md'));
```

Note that this only produces a correct `_site` when the page is self-contained. For anything that touches aggregate outputs, run `php hyde build` to rebuild the whole site instead.

## Optional: Adopt Versioned Documentation

HydePHP v3 adds native support for hosting multiple versions of your documentation side by side. This feature is entirely opt-in — if you do nothing, your documentation site works exactly as before.

To enable it:

1. Move your documentation source files into version subdirectories, for example `_docs/1.x/` and `_docs/2.x/`.
2. Register the versions in `config/docs.php`:

```php
// filepath: config/docs.php
'versions' => [
    '1.x',
    '2.x',
],
```

Each version is compiled to a matching subdirectory of the documentation output directory (`docs/1.x`, `docs/2.x`), with its own sidebar, search index, and search page. A version switcher is shown in the sidebar, and `docs/index.html` is generated as a redirect to the default version's index page (the last entry in the list, or set `docs.default_version` explicitly).

Versioning is all or nothing: once you register versions, every documentation page must live in a version directory. Make sure step 1 is complete, as any Markdown files left directly in `_docs` are ignored, and will no longer be compiled. Each ignored file is reported as a build warning, so if you miss one, `php hyde build` tells you which:

```
Ignoring unversioned documentation file "_docs/installation.md" as documentation versioning is enabled. Move it into a registered version directory to include it in the site.
```

If you want a page at the documentation root, create it in your normal page source directory instead, for example `_pages/docs/index.md`, which then replaces the generated redirect.

Your existing `docs.sidebar.order`, `docs.sidebar.labels`, `docs.sidebar.exclude`, and `docs.exclude_from_search` entries keep working without version prefixes — they apply to the matching page in every version. Prefix an entry with a version (like `2.x/readme` or `docs/2.x/readme`) to target a single version.

If you previously implemented multi-version documentation with custom page classes or extensions (like early versions of HydePHP.com did), you can now remove that custom code in favor of the `docs.versions` configuration, keeping your existing `_docs/<version>` directory layout as-is.

## Step 4: Move Redirects Into Configuration

Redirects are now part of the normal site build and must be declared in `config/hyde.php`. The `Redirect::create()` and
`Redirect::store()` methods have been removed because they wrote directly to the output directory outside the kernel's
build graph.

**Before:**

```php
use Hyde\Support\Models\Redirect;

Redirect::create('old-page', 'new-page');
```

**After:**

```php
// filepath: config/hyde.php
'redirects' => [
    'old-page' => 'new-page',
],
```

Configured redirects are included in `route:list` and generated by `php hyde build`. They are automatically excluded
from navigation menus and the sitemap. Redirect pages always include a visible fallback link, so the previous
`showText` constructor argument is no longer available.

## Step 5: Review Sitemap and RSS Feed Customizations

The sitemap and RSS feed are now generated as regular pages instead of by post-build tasks, so `sitemap.xml` and
the RSS feed (`feed.xml`, or your configured `hyde.rss.filename`) are served by `php hyde serve`, listed in
`route:list`, and included in the build manifest. Sites that just enable or disable these features through
`hyde.generate_sitemap`, `hyde.rss`, and `hyde.url` need no changes.

The `GenerateSitemap` and `GenerateRssFeed` post-build task classes have been removed. If you overrode one with a
same-basename build task, or referenced the classes directly, customize the output through one of the replacement
tiers instead:

- Rebind the generator in the service container to change the output while keeping the page registration:

```php
use Hyde\Framework\Features\XmlGenerators\SitemapGenerator;

app()->bind(SitemapGenerator::class, MyCustomSitemapGenerator::class);
```

The same works for `RssFeedGenerator`.

- Or register your own page with the same route key (`sitemap.xml`, or the configured feed filename) from a
  service provider, booting callback, or extension, which replaces the generated page entirely:

```php
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;

Hyde::kernel()->booting(function ($kernel): void {
    $kernel->pages()->addPage(InMemoryPage::file('sitemap.xml', contents: $myXml));
});
```

Framework fallback pages are now registered only after every extension has completed `discoverPages()`. If an extension
previously inspected or modified a framework-generated page from that handler, move that work to
`discoverDefaultPages()` or a later lifecycle hook. Default handlers still run in extension registration order.

The `build:sitemap` and `build:rss` commands still work and now compile the registered pages. When the output
cannot be generated (no base URL, disabled in the configuration, or — for the feed — no Markdown posts), they
fail with an error instead of generating an empty or unwanted file. `build:sitemap` reports this
failure with exit code 1 instead of 3. If you registered your own page under the route key, the commands build
it regardless of these conditions.

## Step 6: Review the New Generated robots.txt

Hyde now generates a `robots.txt` file by default, allowing all crawlers and linking to the sitemap when that
feature is enabled. Most sites want this and need no changes. If you already publish a `robots.txt` through your
own tooling — a deploy step or web server configuration that would conflict with the generated file — either
disable the feature with `hyde.robots.enabled => false`, or move the contents into Hyde by registering your own
`robots.txt` page (which replaces the generated one, using the same registration pattern as the sitemap example
above). Crawl rules can be added to the `hyde.robots.disallow` configuration array without any custom code.

## Step 7: Decide Whether to Publish the New llms.txt

Hyde now generates an [`llms.txt`](https://llmstxt.org/) file by default, indexing your site's content for AI
services and agents. It requires a site base URL, since the file links to your pages with absolute URLs, so
sites without one are unaffected. The file indexes only material you already publish — it lists nothing your
sitemap does not, and grants no access to anything private — but it is a deliberate invitation for AI services
to read your site. That is a choice worth making consciously: if you would rather not extend that invitation,
set `hyde.llms.enabled` to `false`. Note that leaving pages out of this file does not stop AI crawlers from
reading them; crawler access is governed by your `robots.txt`, not by llms.txt.

If you do keep it, it needs no configuration. Pages are grouped into a section per page type and listed in the
same order as your sitemap, and each link is described by the page's `abstract` front matter, falling back to
its `description`, so filling those in improves the file. A page is listed when it is included in the sitemap,
so anything already carrying `sitemap: false` stays out of this file too — there is no separate front matter key
to learn. As with the sitemap and robots.txt, you can replace the file wholesale by registering your own
`llms.txt` page, or adjust the sections and output by extending the `LlmsTxtGenerator` class and rebinding it in
the service container.

Be aware that llms.txt is an emerging standard which is still subject to change. We cannot make a backwards
compatibility promise for the generated output while the specification is still moving, and we expect to change
the file format in minor and patch releases as the standard evolves. If you depend on the exact output, pin the
format by registering your own page.

## Step 8: Rename Page File Extension References

The static page class property `$fileExtension` has been renamed to `$sourceExtension`, along with the
`fileExtension()` and `setFileExtension()` methods, which are now `sourceExtension()` and `setSourceExtension()`.
The rename pairs the source extension with the new `$outputExtension` property (defaulting to `.html`), which
page classes can override to compile to non-HTML output files.

This only affects projects with custom page classes or code calling these APIs. Update property declarations,
call sites, and any methods that override `fileExtension()` or `setFileExtension()` — the methods are public
and non-final, and an un-renamed override silently stops being called now that the framework calls
`sourceExtension()`:

**Before:**

```php
class CustomPage extends HydePage
{
    public static string $fileExtension = '.md';
}

$extension = MarkdownPage::fileExtension();
```

**After:**

```php
class CustomPage extends HydePage
{
    public static string $sourceExtension = '.md';
}

$extension = MarkdownPage::sourceExtension();
```

The automated upgrade script will handle this rename for ordinary property declarations, property accesses,
method calls, and overridden method declarations. Dynamic references — variable method or property names,
reflection, and string-based access — must be updated manually.

You do not need to hunt for affected classes: page discovery fails fast with an exception naming any
registered page class that still uses the old API, instead of silently skipping the class during builds.

## Migration Checklist

Use this checklist to track your upgrade progress:

- [ ] Reviewed `markdown.allow_html` and `markdown.enable_blade` and explicitly selected the appropriate trust policy
- [ ] Replaced any `php hyde rebuild <path>` usage with `StaticPageBuilder::handle()` or a full `php hyde build`
- [ ] Moved calls to `Redirect::create()` or `Redirect::store()` into the `hyde.redirects` configuration array
- [ ] Replaced any references to the removed `GenerateSitemap` and `GenerateRssFeed` build tasks with a generator container rebind or a user-defined page
- [ ] Confirmed the new generated `robots.txt` does not conflict with an existing one, or disabled it with `hyde.robots.enabled`
- [ ] Decided whether to publish the new generated `llms.txt` for AI services, or disabled it with `hyde.llms.enabled`
- [ ] Renamed `$fileExtension`, `fileExtension()`, and `setFileExtension()` to `$sourceExtension`, `sourceExtension()`, and `setSourceExtension()` in custom page classes and call sites

## Troubleshooting


## Getting Help

If you encounter issues during the upgrade:

- **Documentation**: [https://hydephp.com/docs/3.x](https://hydephp.com/docs/3.x)
- **GitHub Issues**: [https://github.com/hydephp/hyde/issues](https://github.com/hydephp/hyde/issues)
- **Community Discord**: [https://discord.hydephp.com](https://discord.hydephp.com)

For the complete changelog with all pull request references, see the [full release notes](https://github.com/hydephp/hyde/releases/tag/v3.0.0).
