# Epic: First-class non-HTML pages (robots.txt, llms.txt, sitemap, RSS)

> Status: Draft — v3 development branch
>
> Theme: Make non-HTML output files (txt, xml, json) first-class pages instead of
> build-task side effects, so they flow through routing, the build pipeline, the
> realtime compiler, and user-land extension points like every other page.

## Motivation

There is currently no easy way to add plain-text files like `robots.txt` or `llms.txt`
to a Hyde site. While investigating this, we found that the framework already contains
three different mechanisms for emitting non-HTML files, each with its own tradeoffs:

1. **Post-build tasks** (`GenerateSitemap`, `GenerateRssFeed`) write directly to
   `_site/` with `file_put_contents`, bypassing the page/route system entirely.
   Consequence: `hyde serve` cannot serve `sitemap.xml` or `feed.xml` at all, and
   they are invisible to `route:list` and the build manifest.
2. **Virtual pages** (`DocumentationSearchIndex extends InMemoryPage`) participate in
   routing and the build, but only by manually overriding `getOutputPath()` because
   `HydePage::outputPath()` hardcodes the `.html` suffix
   (`packages/framework/src/Pages/Concerns/HydePage.php:213`). The realtime compiler
   needs a hardcoded exemption to serve it
   (`packages/realtime-compiler/src/Routing/Router.php:72-75`).
3. **Verbatim source files** (`HtmlPage`) are autodiscovered from `_pages` and copied
   as-is — but only for `.html`.

Unifying these under one model gives us `robots.txt`/`llms.txt` support nearly for
free, fixes real bugs, and simplifies the framework.

### Bugs and gaps this epic fixes

- **`search.json` leaks into sitemaps in production.** `SitemapGenerator::generate()`
  excludes only `Redirect` pages, so every other route is included. Verified live:
  `https://hydephp.com/sitemap.xml` contains `docs/1.x/search.json` and
  `docs/2.x/search.json`. There is no per-page sitemap exclusion mechanism at all.
- **`hyde serve` does not serve `sitemap.xml` or the RSS feed**, because they only
  exist as post-build artifacts.
- **`InMemoryPage::make('robots.txt', contents: ...)` outputs `robots.txt.html`**,
  making the natural "assemble it in code" escape hatch a trap.
- **The realtime compiler special-cases `search.json` by string suffix** instead of
  asking the route system.

### What already works in our favor

- `PageRouter::getContentType()` already maps `json`/`xml`/`txt` output paths to
  correct Content-Type headers (`packages/realtime-compiler/src/Routing/PageRouter.php:59-69`).
- `BuildService::getPageTypes()` derives page classes from the live page collection,
  and `StaticPageBuilder` writes whatever `getOutputPath()` returns — dynamic pages
  need zero build-service changes.
- `HydeCoreExtension::discoverPages()` is the proven registration point for
  feature-gated virtual pages (search index, redirects), and users/packages can do
  the same via `Hyde::kernel()->booting()` callbacks or a `HydeExtension`.
- The route-key-with-extension convention is already de facto established:
  `DocumentationSearchIndex` uses route key `docs/search.json` equal to its output path.

## Design decisions

### D1: Route key equals output path; only `.html` is implicit

Formalize the convention `DocumentationSearchIndex` already uses: a page's route key
is its output path, except that HTML pages drop the `.html` suffix (pretty URLs).
So `robots.txt`, `sitemap.xml`, and `docs/search.json` are route keys as-is, while
`about.html` keeps route key `about`.

This is preferred over a "clean route key + separate output extension" model because
it is proven in production, requires no realtime-compiler lookup changes
(`PageRouter::normalizePath()` only strips `.html`), and lets `docs/search` (page)
and `docs/search.json` (index) coexist as distinct routes, which they already do.

> **Implemented (PR 1):** `RouteKey::fromPage()` appends the page class's declared
> non-HTML output extension to the key (skipping it when the identifier already ends
> with it), so classes like PR 3's `TextPage` are D1-compliant out of the box and
> PR 2 can rely on "route key == request path with only `.html` stripped" universally.

### D2: Output extension is declared, not inferred

Do **not** infer "this identifier has an extension" from a dot in the identifier —
versioned docs route keys like `docs/1.x/index` would false-positive
(`pathinfo('docs/1.x')['extension'] === 'x'`). Instead the extension is declared:

- File-discovered classes declare it statically, mirroring the existing
  `HtmlPage::$sourceExtension` pattern: `TextPage::$outputExtension = '.txt'`.
- `HydePage` gets `public static string $outputExtension = '.html'` (or an
  instance-level hook), and `outputPath()` uses it instead of the hardcoded
  `'.html'`. This removes the `getOutputPath()` override dance.
- For `InMemoryPage`, decide in the PR whether to (a) accept a small allowlist of
  trailing extensions in the identifier (`.txt`, `.json`, `.xml`), or (b) add an
  explicit constructor/`make()` parameter. Leaning (a) with allowlist since
  `InMemoryPage::make('robots.txt', contents: $txt)` is the DX we actually want.

> **Decided (PR 1):** option (a). The allowlist (`.json`, `.txt`, `.xml`) is the
> `InMemoryPage::EXPLICIT_OUTPUT_EXTENSIONS` constant, checked by
> `identifierHasExplicitOutputExtension()`; subclasses customize the recognized
> extensions by overriding the constant rather than the algorithm.
> `Redirect` rejects source paths ending in a recognized non-HTML extension with
> an `InvalidConfigurationException` (revised during PR 1 review from the earlier
> "inherit with documented limitation" decision): a meta-refresh redirect cannot
> work when the file is served as non-HTML, and neither the old unreachable
> double-extension output nor an as-is file delivers the advertised redirect, so
> the configuration fails fast instead of silently producing a broken page.

### D3: Sitemap inclusion becomes a page-level concern

Replace the `instanceof Redirect` filter in `SitemapGenerator` with a
`HydePage::showInSitemap(): bool` method backed by front matter (`sitemap: false`),
with defaults: `false` for redirects and for pages whose output is not `.html`,
`true` otherwise. This fixes the `search.json` leak, prevents the new
sitemap/feed/robots pages from self-listing, and gives users per-page opt-out —
a standalone feature in its own right.

### D4: Generators become pages; generator actions stay

`SitemapPage`, `RssFeedPage` (and later `RobotsTxtPage`, `LlmsTxtPage`) extend
`InMemoryPage` with a `compile()` that delegates to the existing generator classes —
exactly the `DocumentationSearchIndex` → `GeneratesDocumentationSearchIndex` split.
The XML generator actions (`SitemapGenerator`, `RssFeedGenerator`) are untouched.
Registration happens in `HydeCoreExtension::discoverPages()` behind the existing
`Features::hasSitemap()` / `Features::hasRss()` conditions, replacing the
registrations in `BuildTaskService::registerFrameworkTasks()`.

### D5: Source files beat generators

If a user provides `_pages/robots.txt`, the framework does not register its generated
`RobotsTxtPage`. Same pattern as `discoverDocumentationRootRedirect()`, which skips
when a user-defined route exists. This gives a smooth escalation path:
feature default → config tweaks → file on disk → fully dynamic page in code.

## Work breakdown (one PR each, in dependency order)

### PR 1 — Foundation: declared output extensions on `HydePage` ✅ Implemented

Goal: any page class can emit a non-`.html` file without overriding `getOutputPath()`.

- Add `$outputExtension` (default `'.html'`) to `HydePage`; use it in
  `outputPath()` (`HydePage.php:211-214`).
- Route keys follow D1; audit `RouteKey` and `Route` for assumptions.
- Let `InMemoryPage` respect a declared/allowlisted extension per D2, so
  `InMemoryPage::make('robots.txt', contents: ...)` outputs `robots.txt`.
- Refactor `DocumentationSearchIndex` to drop its `getOutputPath()` override.
- Pure refactor for existing sites: no compiled-output changes.

Implementation notes (branch `v3/non-html-pages-foundation`):

- An `outputExtension()` accessor accompanies the property, matching the other
  static accessors, and is part of the `BaseHydePageUnitTest` contract. No setter
  was added — the existing setters exist for config-driven source customization,
  which does not apply here; subclasses redeclare the property.
- Review outcome: the existing `$fileExtension` API was renamed to `$sourceExtension`
  (with `fileExtension()`/`setFileExtension()` becoming `sourceExtension()`/
  `setSourceExtension()`) so the source/output pair reads symmetrically — the old
  name really meant the source extension, and fixing the vocabulary before later
  page types (`TextPage`, sitemap, RSS, robots, llms) build on it avoids much larger
  churn. Clean break, no compatibility aliases: independently redeclared static
  properties cannot alias each other without precedence/synchronization hacks.
  The mechanical migration is recorded in `HYDEPHP_V3_PLANNING.md` under
  "Upgrade script rules" for the release-time Rector script.
- Non-HTML extension handling was placed in `RouteKey::fromPage()` (see D1 note)
  rather than only in `outputPath()`, so route keys and output paths cannot drift.
- One qualification to "no compiled-output changes": ordinary in-memory page
  identifiers that already end in an allowlisted extension previously produced
  double-extension output (`data.json.html`); they now compile to the declared
  path as-is. `hyde.redirects` paths using those extensions are instead rejected
  (see the D2 note), since their HTML meta-refresh content cannot work when served
  as non-HTML. Both recorded in the v3 release notes as breaking changes, though
  the old outputs were almost certainly never intended or relied upon.

### PR 2 — Realtime compiler: route-first resolution for non-HTML paths ✅ Implemented

Goal: `hyde serve` serves any registered route regardless of extension; no
filename special cases.

- In `Router::shouldProxy()`, replace the `search.json` suffix check with a generic
  "is there a registered route for this path?" check (`PageRouter::hasRoute()`),
  so pages win over asset proxying.
- Regression tests: versioned-docs dotted paths (`docs/1.x/...`), media assets,
  missing-asset 404s, and `search.json` still served.
- `PageRouter::getContentType()` already handles txt/xml/json; extend the map only
  if new types come up.

Implementation notes (branch `v3/non-html-pages-realtime-compiler`):

- The route lookup needs the booted application, but `shouldProxy()` ran before
  booting, so the predicate was dissolved into `Router::handle()` instead of
  booting inside it: the `/media/` prefix remains the only boot-free fast path,
  and any other asset-like path is proxied only when no registered route matches.
  Missing assets fall through to the 404 in `proxyStatic()`, which absorbed the
  previous separate missing-asset branch (same response either way).
- Perf consequence: requests for existing static files outside `media/` now boot
  the app before being proxied, since routes must be consulted first. Such files
  are rare (Hyde assets live under `media/`), and every non-proxied request
  already booted.
- Behavior fix beyond the search.json generalization: a static file whose path
  shadowed a registered dotted route (like a `_media/9.x` file next to a
  `9.x/index` page) was previously served instead of the page; the page now wins.
  Conversely, a routeless file like `_media/search.json` requested as
  `/search.json` was previously 404'd by the suffix special case and is now
  proxied like any other asset.
- `getContentType()` untouched — no new content types came up.

### PR 3 — `TextPage` class (the headline feature)

Goal: drop `_pages/robots.txt` in, get `_site/robots.txt` out.

- New `TextPage extends HydePage`: `$sourceDirectory = '_pages'`,
  `$outputDirectory = ''`, `$sourceExtension = '.txt'`,
  `$outputExtension = '.txt'`; `compile()` returns file contents verbatim
  (mirror `HtmlPage`).
- Register in `HydeCoreExtension::getPageClasses()`. No `Feature::TextPages` enum
  case — the feature is always on, since it is inert without source files.
- Hidden from navigation menus by default; excluded from sitemap via PR 4's
  mechanism (or an interim guard if PR 4 lands later).
- Docs: static-pages page gains a "Text pages" section, including the in-code
  variant (service provider / `booting()` callback assembling contents dynamically,
  e.g. looping over routes to build a robots.txt — the `InMemoryPage` example).

### PR 4 — Sitemap inclusion policy

Goal: pages control their own sitemap presence; fix the production `search.json` leak.

- `HydePage::showInSitemap()` per D3 + `sitemap: false` front matter support.
- `SitemapGenerator::generate()` filters on it instead of `instanceof Redirect`.
- Changelog note: search indexes no longer appear in sitemaps (bugfix).
- Independent of PRs 1-3; must land before or with PR 5.

### PR 5 — Convert sitemap and RSS from build tasks to pages

Goal: `sitemap.xml` and `feed.xml` are routes — served by `hyde serve`, listed in
`route:list`, included in the build manifest, overridable in user land.

- `SitemapPage` / `RssFeedPage` extend `InMemoryPage`, `compile()` delegates to the
  existing generators; RSS route key comes from `RssFeedGenerator::getFilename()`
  (config `hyde.rss.filename`).
- Register in `HydeCoreExtension::discoverPages()` behind `Features::hasSitemap()` /
  `Features::hasRss()`; remove `GenerateSitemap`/`GenerateRssFeed` from
  `BuildTaskService::registerFrameworkTasks()` (evaluate deprecation vs. removal —
  v3 allows breaking changes, but third-party code may reference the task classes).
- Rewire `build:sitemap` / `build:rss` commands to `StaticPageBuilder::handle(new …Page())`.
- Verify `GlobalMetadataBag` head links and the `hyde.url` requirements still hold
  (`Features::hasSitemap()` already requires a site URL).
- Nice side effect: build output shows them under "Dynamic Pages" with the standard
  progress display.

### PR 6 — Generated `robots.txt`

Goal: sensible robots.txt out of the box, zero config.

- `RobotsTxtPage extends InMemoryPage`, route key `robots.txt`; default output
  `User-agent: * / Allow: /` plus a `Sitemap:` line when `Features::hasSitemap()`.
- Config (e.g. `hyde.robots`) for disallow rules / disabling; source-file precedence
  per D5 (a `_pages/robots.txt` `TextPage` wins).
- Depends on PRs 1, 2, 5 patterns.

### PR 7 — Generated `llms.txt`

Goal: best-in-class llms.txt support — no other SSG generates this well out of the box.

- `GeneratesLlmsTxt` action per the llms.txt spec: site name as H1, `hyde.description`
  ("about" blockquote), sections of route links using page titles and the new
  documentation page abstracts (#2523) as link descriptions.
- `LlmsTxtPage extends InMemoryPage` wired like robots.txt (feature-gated, config
  for section grouping/exclusions, source-file precedence).
- Consider `llms-full.txt` (full page contents) as a follow-up, not in scope.

### PR 8 — Documentation & release notes

- Document `TextPage`, in-code virtual pages, `sitemap: false` front matter,
  robots/llms config, and the "source file beats generator" rule.
- Update `HYDEPHP_V3_PLANNING.md` release notes: new features (TextPage, robots.txt,
  llms.txt, serve support for sitemap/RSS), breaking changes (build task classes
  removed/relocated, search.json removed from sitemaps).

## Out of scope (noted for later)

- Blade-processed text files (`robots.blade.txt`) analogous to `BladePage` — wait for
  demand; the in-code `InMemoryPage` path covers dynamic cases.
- `llms-full.txt` / per-page markdown exports.
- Generalizing `GenerateBuildManifest` or search-index generation commands beyond
  what PR 5 requires.
- Reconsidering the page-type `Feature` enum cases (`HtmlPages`, `BladePages`, etc.)
  altogether — arguably redundant since not creating source files has the same
  effect. Worth a separate v3 discussion; this epic simply doesn't add new ones.
