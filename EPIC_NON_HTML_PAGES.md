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
   as-is, but this is a source-file convenience specific to HTML rather than a
   requirement for supporting non-HTML output.

Making the output format part of the page model gives us `robots.txt`/`llms.txt`
support nearly for free, fixes real bugs, and simplifies the framework. It does not
require every output format to have a matching filesystem-discovered page class.

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
> with it), so custom page classes declaring a non-HTML output extension are
> D1-compliant out of the box and PR 2 can rely on "route key == request path with
> only `.html` stripped" universally.

### D2: Output extension is declared, not inferred

Do **not** infer "this identifier has an extension" from a dot in the identifier —
versioned docs route keys like `docs/1.x/index` would false-positive
(`pathinfo('docs/1.x')['extension'] === 'x'`). Instead the extension is declared:

- File-discovered custom classes declare it statically, mirroring the existing
  `HtmlPage::$sourceExtension` pattern.
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

> **Two output mechanisms, both intended (PR 1):** the output extension is *per-class
> static* (`$outputExtension`) for file-discovered custom page classes, and
> *per-instance identifier-encoded* for `InMemoryPage` (detected from the identifier
> via the allowlist). The latter is deliberate: output is not needed at discovery
> time the way source paths are, so it does not have to be static. This is what lets
> a single `InMemoryPage` class emit different extensions per instance —
> `new InMemoryPage('robots.txt')` and `make('data.json')` — without a subclass, which
> is exactly what the generated pages (D4) and the user `make()` escape hatch need.
> Consequence recorded for D3: an `InMemoryPage`'s *static* `outputExtension()` stays
> `.html` even when the instance compiles to `robots.txt`; the real output extension
> lives in the resolved output path.

### D3: Sitemap inclusion becomes a page-level concern

Replace the `instanceof Redirect` filter in `SitemapGenerator` with a
`HydePage::showInSitemap(): bool` method backed by front matter (`sitemap: false`),
with defaults: `false` for redirects and for pages whose output is not `.html`,
`true` otherwise. This fixes the `search.json` leak, prevents the new
sitemap/feed/robots pages from self-listing, and gives users per-page opt-out —
a standalone feature in its own right.

> **Implementation constraint (from PR 1) — read before writing `showInSitemap()`:**
> the "output is not `.html`" default MUST be derived from the page's *resolved
> output path* (e.g. the extension of `getOutputPath()`), not from the static
> `outputExtension()` accessor. For file-discovered custom pages the two agree, but
> per D2 an `InMemoryPage` encodes its extension in the identifier while its static
> `outputExtension()` stays `.html`. So a `robots.txt` / `sitemap.xml` / `llms.txt`
> `InMemoryPage` reports `.html` statically despite compiling to a non-HTML file.
> Keying the non-HTML default off `getOutputPath()` makes all four generated pages
> self-exclude correctly; keying it off `outputExtension()` would silently
> re-introduce the exact `search.json` leak this epic exists to fix. This is the
> kind of "rule implemented only for the cases the current PR exercised" trap the
> agent workflow warns about — the discovered-page tests would pass while the
> InMemoryPage-backed generated pages regress.

### D4: Generators become container-resolved pages; generator actions stay

Each generated file is registered as an `InMemoryPage` whose compiled contents
resolve its generator **from the container at build time**, e.g. a `sitemap.xml`
page whose compile step returns `app(SitemapGenerator::class)->generate()`. The XML
generator actions (`SitemapGenerator`, `RssFeedGenerator`) are untouched and remain
the default implementations — this is still the `DocumentationSearchIndex` →
`GeneratesDocumentationSearchIndex` split, but the generator is *resolved*, not
`new`'d.

Resolving through the container is the point: a user can rebind `SitemapGenerator`
(or the page's content closure) to swap the output without replacing the page — a
lighter-weight customization tier than D5's full page override. Contents must be
produced **lazily** (a `compile` macro / closure or a thin subclass `compile()`),
never eager string content, since generation must run at build time against the
final route set.

Registration happens in `HydeCoreExtension::discoverPages()` behind the existing
`Features::hasSitemap()` / `Features::hasRss()` conditions, replacing the
registrations in `BuildTaskService::registerFrameworkTasks()`.

**Plain page vs. thin subclass is deferred to PR 5.** D3 already defaults non-HTML
pages out of the sitemap, so a subclass is *not* needed merely to stop a generated
page self-listing. The only remaining reasons to introduce `SitemapPage extends
InMemoryPage` (mirroring `DocumentationSearchIndex`) are type identity (`instanceof`)
and giving the `build:sitemap` / `build:rss` commands a concrete class to
instantiate. Lean toward a plain `InMemoryPage` registered with a container-bound
`compile` closure unless PR 5 surfaces a concrete need for the subclass.

### D5: User-defined pages beat generators

If the page collection already contains a user-defined page with a route key such
as `robots.txt`, the framework does not register its generated `RobotsTxtPage`.
This follows the pattern of `discoverDocumentationRootRedirect()`, which skips when
a user-defined route exists.
Users can register an `InMemoryPage` from a service provider or provide a custom page
class through an extension. Combined with D4's container-resolved generators, this
gives a smooth escalation path:
feature default → config tweaks → rebind the generator (or content closure) in the
container → fully custom page in code.

### D6: No built-in `TextPage` or `.txt` autodiscovery

First-class non-HTML support is about a page's output path and participation in the
route/build/serve lifecycle; it does not require a dedicated source-backed page class
for each file extension. `InMemoryPage::make('robots.txt', contents: ...)` already
provides the full lifecycle integration and is a better fit for the dynamic content
advanced users commonly need, while the planned generated robots and llms pages cover
the common cases without any source file at all.

A core `TextPage` would add only the convenience of autodiscovering `_pages/*.txt`,
while creating pressure for parallel `XmlPage`, `JsonPage`, and similar classes.
Plain-text files also cannot carry front matter, requiring page-type defaults or
special handling for navigation and sitemap behavior. That framework surface is not
justified by the narrow drop-a-file use case. If demand emerges for filesystem-backed
verbatim files, it should be designed as a generic raw/public-file mechanism instead
of one page class per extension. Custom discoverable page classes remain supported as
an extension point.

## Work breakdown (planned PR sequence, in dependency order)

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
  non-HTML page types (sitemap, RSS, robots, llms) build on it avoids much larger
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
- **Post-implementation review: no changes required.** The two output-extension
  mechanisms now coexist and both are intended (see the D2 "two output mechanisms"
  note) — per-class static for discovered classes, per-instance identifier-encoded
  for `InMemoryPage`, so instance-based non-HTML output works without a subclass.
  The one constraint this places downstream is recorded in the D3 implementation
  note: sitemap / non-HTML detection must read the resolved output path, not the
  static `outputExtension()` accessor.

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
- **Post-implementation review: no changes required.** Route-first resolution is
  clean and the shadowing/`search.json` regression tests cover the behavior changes.
  Worth adding (if not already present elsewhere) an explicit versioned
  `docs/1.x/search.json` serve test alongside the un-versioned one, since the
  versioned dotted path is the case most likely to regress silently.

### PR 3 — `TextPage` autodiscovery ❌ Removed from scope

The proposed `TextPage` class was evaluated after the non-HTML foundation landed and
removed from the epic per D6. The foundation already makes a `.txt` `InMemoryPage`
first-class, the common robots/llms cases will have generated pages, and advanced
content is often dynamic. Adding a core class solely for `_pages/*.txt` discovery
would introduce extension-specific framework surface without solving the broader
verbatim-file problem. Documentation will instead show the service provider /
`booting()` registration pattern for custom text output.

### PR 4 — Sitemap inclusion policy

Goal: pages control their own sitemap presence; fix the production `search.json` leak.

- `HydePage::showInSitemap()` per D3 + `sitemap: false` front matter support.
- **Derive the non-HTML default from `getOutputPath()`, not `outputExtension()`**
  (see the D3 implementation constraint) so InMemoryPage-backed generated pages
  self-exclude correctly.
- `SitemapGenerator::generate()` filters on it instead of `instanceof Redirect`.
- Changelog note: search indexes no longer appear in sitemaps (bugfix).
- Independent of PRs 1-3; must land before or with PR 5.

### PR 5 — Convert sitemap and RSS from build tasks to pages

Goal: `sitemap.xml` and `feed.xml` are routes — served by `hyde serve`, listed in
`route:list`, included in the build manifest, overridable in user land.

- Register `sitemap.xml` / `feed.xml` as `InMemoryPage`s per D4, with a lazy
  `compile` that resolves the generator from the container
  (`app(SitemapGenerator::class)->generate()` / `app(RssFeedGenerator::class)->generate()`),
  so the implementation is swappable via container rebind. RSS route key comes from
  `RssFeedGenerator::getFilename()` (config `hyde.rss.filename`).
- **Decide plain `InMemoryPage` (compile macro) vs. thin subclass here** (D4). Default
  to plain + container binding unless type identity or command wiring forces a
  subclass; note that D3 already handles sitemap self-exclusion either way.
- Register in `HydeCoreExtension::discoverPages()` behind `Features::hasSitemap()` /
  `Features::hasRss()`; remove `GenerateSitemap`/`GenerateRssFeed` from
  `BuildTaskService::registerFrameworkTasks()` (evaluate deprecation vs. removal —
  v3 allows breaking changes, but third-party code may reference the task classes).
- Rewire `build:sitemap` / `build:rss` commands to build the same registered page
  via `StaticPageBuilder::handle(...)` (a shared factory if the pages are plain
  `InMemoryPage`s, or `new …Page()` if subclassed).
- Verify `GlobalMetadataBag` head links and the `hyde.url` requirements still hold
  (`Features::hasSitemap()` already requires a site URL).
- Nice side effect: build output shows them under "Dynamic Pages" with the standard
  progress display.

### PR 6 — Generated `robots.txt`

Goal: sensible robots.txt out of the box, zero config.

- `robots.txt` registered as an `InMemoryPage` per D4; default output
  `User-agent: * / Allow: /` plus a `Sitemap:` line when `Features::hasSitemap()`.
- Config (e.g. `hyde.robots`) for disallow rules / disabling; user-defined page
  precedence per D5 (an explicitly registered `robots.txt` page wins).
- Depends on PRs 1, 2, 5 patterns.

### PR 7 — Generated `llms.txt`

Goal: best-in-class llms.txt support — no other SSG generates this well out of the box.

- `GeneratesLlmsTxt` action per the llms.txt spec: site name as H1, `hyde.description`
  ("about" blockquote), sections of route links using page titles and the new
  documentation page abstracts (#2523) as link descriptions.
- `llms.txt` registered as an `InMemoryPage` wired like robots.txt (feature-gated,
  config for section grouping/exclusions, container-resolved generator, user-defined
  page precedence).
- Consider `llms-full.txt` (full page contents) as a follow-up, not in scope.

### PR 8 — Documentation & release notes

- Document in-code virtual pages, `sitemap: false` front matter, robots/llms config,
  the container-rebind customization tier for generated pages, and the "user-defined
  page beats generator" rule.
- Update `HYDEPHP_V3_PLANNING.md` release notes: new features (robots.txt, llms.txt,
  serve support for sitemap/RSS), breaking changes (build task classes
  removed/relocated, search.json removed from sitemaps).

## Out of scope (noted for later)

- Filesystem autodiscovery for verbatim or Blade-processed text files
  (`robots.txt`, `robots.blade.txt`) — wait for demand; the in-code `InMemoryPage`
  path covers custom and dynamic cases. If added later, prefer a generic mechanism
  for raw/public files over extension-specific page classes.
- `llms-full.txt` / per-page markdown exports.
- Generalizing `GenerateBuildManifest` or search-index generation commands beyond
  what PR 5 requires.
- Reconsidering the page-type `Feature` enum cases (`HtmlPages`, `BladePages`, etc.)
  altogether — arguably redundant since not creating source files has the same
  effect. Worth a separate v3 discussion; this epic simply doesn't add new ones.
