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

> **Open decision — the allowlist relocates the trap, it does not eliminate it.**
> The allowlist correctly avoids the `docs/1.x` false-positive that killed dot-inference,
> but it is conservative in the false-*negative* direction: an identifier the allowlist
> does not recognize still gets the `.html` suffix. So `make('site.webmanifest')` →
> `site.webmanifest.html` and `make('sitemap.xsl')` → `sitemap.xsl.html` — the exact
> `robots.txt.html` bug this decision fixed, one extension over, and both are plausible
> power-user cases (PWA manifest, sitemap stylesheet). The allowlist stays as the
> zero-config default, but it should not be the *only* path. Two mitigations, at least
> one required before PR 8:
> 1. **(Preferred) Keep D2 option (b) available alongside (a):** an explicit
     >    `make(..., outputExtension: '.xsl')` / constructor parameter that bypasses the
     >    allowlist for any extension. Allowlist for ergonomics, explicit param for
     >    everything else — this actually closes the trap instead of moving it.
> 2. **(Minimum) Document the `EXPLICIT_OUTPUT_EXTENSIONS` override clearly** so a user
     >    can subclass and extend the recognized list, and ensure the appended-`.html`
     >    output is at least discoverable/greppable rather than silently wrong.
     > Decide in PR 5/6 whether the first-party generated files need any extension outside
     > the allowlist (none currently do — all are `.txt`/`.xml`), which determines whether
     > (1) is needed for the framework itself or only for the secondary audience.
     > *(PR 5 part A: confirmed for `sitemap.xml` — within the allowlist. `feed.xml`,
     > `robots.txt`, and `llms.txt` are too, so the framework itself will not need
     > option (b); the remaining call in PR 8 is only for the power-user audience.)*
     > *(PR 5 part B qualification: the RSS filename is user-configurable, and the old
     > task wrote any `hyde.rss.filename` verbatim — so `RssFeedPage` overrides
     > `identifierHasExplicitOutputExtension()` to always treat the configured filename
     > as the literal output path, keeping `feed.rss` (or an extensionless name)
     > working. This confirms the subclass override is a workable escape hatch for
     > first-party pages, but does not settle option (b) for user-land `make()`
     > callers, which remains the PR 8 call.)*
     > *(PR 7: confirmed for `llms.txt` — within the allowlist, needing no override.
     > All four first-party generated files have now landed inside the allowlist, so
     > the framework never needed option (b); the PR 8 call is purely about the
     > power-user `make()` audience.)*

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

> **Implemented (PR 4):** `HydePage::showInSitemap()` reads the `sitemap` front
> matter key, defaulting to whether the resolved output path (`getOutputPath()`)
> ends in `.html`, per the constraint above. Front matter wins in both directions,
> so `sitemap: true` opts a non-HTML page back in. One refinement to "defaults
> `false` for redirects": `Redirect` overrides `showInSitemap()` to return `false`
> unconditionally, mirroring its `showInNavigation()` and the already-recorded
> release note that redirect routes are intrinsically excluded from navigation and
> sitemaps — a redirect page has no front matter channel in `hyde.redirects`, and
> listing redirects in a sitemap is an SEO anti-pattern, so an opt-in would only
> be a trap.

> **Reused, not duplicated (PR 7): llms.txt inclusion *is* sitemap inclusion.** No new
> page method and no new front matter key were added. `LlmsTxtGenerator::shouldListPage()`
> is simply `$page->showInSitemap() && $page->getIdentifier() !== '404'`.
>
> This landed in two cuts. The first PR 7 implementation added a mirror
> `HydePage::showInLlmsTxt()` (plus a `Redirect` override, a `BaseHydePageUnitTest`
> contract entry, and six page unit test implementations); that was cut because
> `showInSitemap()` already answers the exact question llms.txt asks — "is this page part
> of the machine-readable index of my site" — and its resolved-output-path default already
> excludes every generated non-HTML page and redirect for free. The interim version kept an
> `llms` front matter key (`matter('llms', $page->showInSitemap())`) to preserve decoupling;
> that was cut too, on the grounds that **front matter is public API we must support
> forever, so it has to earn its place.** The decisive argument is that llms.txt is not a
> control plane: omitting a page from it does not stop any AI service from reading that
> page (only `robots.txt` speaks to crawler access), so `llms: false` could never mean
> "hide this from AI" — it could only mean "curate my index", which is precisely what
> `sitemap: false` already means. A second key with near-identical semantics would mostly
> generate the question "which one do I use?".
>
> The coupling is therefore a feature, not a compromise, and it is the *less* surprising
> default: a user who hides a page from search engines does not expect it advertised to AI
> agents. Should a concrete need for decoupling appear, reintroducing `llms:` front matter
> (or promoting it to a `showInLlmsTxt()` method) is additive and non-breaking — so waiting
> for that evidence costs nothing, while shipping the key speculatively costs us the
> support burden forever.

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

> **Decided (PR 5 part A): thin subclass.** The command wiring is the concrete need
> the deferral anticipated: `build:sitemap` builds the registered route's page and
> must fall back to a fresh instance when the route is not registered (mirroring how
> `BuildSearchCommand` falls back to `new DocumentationSearchIndex()`), and a plain
> page would need that construction logic exported from a shared factory anyway.
> `SitemapPage extends InMemoryPage` lives next to its generator in
> `Hyde\Framework\Features\XmlGenerators`, keeps the construction in one place, and
> stays out of `Hyde\Pages` so it is exempt from the discovered-page unit test
> contract, exactly like `DocumentationSearchIndex`. The D4 swappability tier is
> unaffected: `compile()` resolves `SitemapGenerator` from the container, verified
> by a rebind test. Part B should mirror this with `RssFeedPage`.

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

> **Timing caveat — the skip check is ordering-sensitive.** "Is a `robots.txt` route
> already registered" is evaluated at `discoverPages()` time, so whether a user's page
> wins depends on it being visible at that moment. A page registered via a late
> `booting()` callback may or may not be present depending on boot ordering. The
> `discoverDocumentationRootRedirect()` precedent suggests this is fine, but "fine and
> ordering-dependent" is exactly what passes in our tests and breaks for the one user
> who registers late. PR 5/6 MUST include an end-to-end test asserting that a
> user-registered `robots.txt` (via both a `HydeExtension` page class and a `booting()`
> `addPage()` callback) suppresses the generated one — this is the D5 contract and the
> most likely silent failure for the power-user audience.

> **Verified for the sitemap (PR 5 part A):** both user paths win, through two
> different mechanisms that the tests pin down end-to-end. `booting()` callbacks run
> before the page collection boots (`BootsHydeKernel::boot()`), so a callback-registered
> `sitemap.xml` page is visible to the core extension's `hasPageWithRouteKey()` skip
> check. A user `HydeExtension` runs *after* the core extension (registration order),
> so the skip check cannot see its pages; instead the user page replaces the generated
> one under the same collection key (`addPage()` keys by source path). Both are
> asserted through the real `build` command output. The robots.txt equivalent remains
> mandatory for PR 6. *(Part B: both paths verified the same way for the feed page.)*
> *(PR 6: both paths verified the same way for the robots.txt page.)*
> *(PR 7: both paths verified the same way for the llms.txt page.)*

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

### PR 4 — Sitemap inclusion policy ✅ Implemented

Goal: pages control their own sitemap presence; fix the production `search.json` leak.

- `HydePage::showInSitemap()` per D3 + `sitemap: false` front matter support.
- **Derive the non-HTML default from `getOutputPath()`, not `outputExtension()`**
  (see the D3 implementation constraint) so InMemoryPage-backed generated pages
  self-exclude correctly.
- `SitemapGenerator::generate()` filters on it instead of `instanceof Redirect`.
- Changelog note: search indexes no longer appear in sitemaps (bugfix).
- Independent of PRs 1-3; must land before or with PR 5.

Implementation notes (branch `v3/non-html-pages-sitemap-inclusion-policy`):

- Implemented exactly per D3 (see the D3 "Implemented" note for the front matter
  semantics and the `Redirect` refinement). `showInSitemap()` joined the
  `BaseHydePageUnitTest` contract; the InMemoryPage unit test covers the
  identifier-encoded non-HTML default that the static-extension tests cannot.
- The non-HTML self-exclusion is verified end-to-end: a registered `robots.txt`
  `InMemoryPage` is built by the real `build` command and asserted absent from
  the built `sitemap.xml`, guarding the D3 resolved-output-path constraint
  against regression by construction rather than only at the unit level.
- Two existing tests asserted the leak as expected behavior and were flipped:
  `SitemapServiceTest` now asserts the docs search *page* stays while the search
  *index* is excluded, and the `SitemapFeatureTest` expected XML dropped its
  `docs/search.json` entry (it also gained a `sitemap: false` page proving the
  front matter opt-out through the `build:sitemap` command).
- No UPGRADE.md entry: the fix requires no user action, and nothing realistic
  depended on search indexes appearing in sitemaps.

### PR 5 — Convert sitemap and RSS from build tasks to pages ✅ Implemented

Goal: `sitemap.xml` and `feed.xml` are routes — served by `hyde serve`, listed in
`route:list`, included in the build manifest, overridable in user land.

> **Split during implementation:** part A converted the sitemap, part B converted
> the RSS feed the same way. The bullets below describe both; the notes at the end
> of this section record what landed in each part.

- Register `sitemap.xml` / `feed.xml` as `InMemoryPage`s per D4, with a lazy
  `compile` that resolves the generator from the container
  (`app(SitemapGenerator::class)->generate()` / `app(RssFeedGenerator::class)->generate()`),
  so the implementation is swappable via container rebind. RSS route key comes from
  `RssFeedGenerator::getFilename()` (config `hyde.rss.filename`).
- **Decide plain `InMemoryPage` (compile macro) vs. thin subclass here** (D4). Default
  to plain + container binding unless type identity or command wiring forces a
  subclass; note that D3 already handles sitemap self-exclusion either way.
- **Verify the generators are actually container-resolvable** before advertising the
  rebind: no unresolvable constructor dependencies, not `final` (or the swap can't be
  bound). D4's whole swappability tier is a lie if `app(SitemapGenerator::class)`
  can't be rebound. Add a test that rebinds the generator and asserts the page's
  compiled output changes.
- Confirm none of the first-party generated files (`sitemap.xml`, `feed.xml`,
  `robots.txt`, `llms.txt`) need an extension outside the D2 allowlist — they don't
  today, which settles the D2 "open decision" in favor of the framework not needing
  option (b) for its own use (the power-user escape hatch is a separate call).
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

Implementation notes, part A (branch `v3/non-html-pages-convert-sitemap`):

- `SitemapPage extends InMemoryPage` per the D4 "thin subclass" decision (see the D4
  note for the rationale), hiding itself from navigation like
  `DocumentationSearchIndex` and self-excluding from the sitemap via the D3 non-HTML
  default. Registered at the end of `HydeCoreExtension::discoverPages()` behind
  `Features::hasSitemap()` with the D5 skip check (see the D5 note for the verified
  override ordering semantics).
- `SitemapGenerator` verified container-resolvable and rebindable: not `final`, no
  constructor dependencies, and a test rebinds it and asserts the page's compiled
  output changes.
- `GenerateSitemap` was removed rather than deprecated: a kept-but-registered task
  would generate the sitemap twice, and a kept-but-unregistered task is dead code
  that still breaks anyone re-registering it (double generation) — a clean removal
  with release-notes guidance to the rebind/override tiers is more honest. Recorded
  as a v3 breaking change with the realistic impact being same-basename user-land
  task overrides.
- `build:sitemap` builds the registered route's page via `StaticPageBuilder`. The
  route-first lookup means a user-defined `sitemap.xml` page wins here too. Skip
  exit code changed from 3 (task-runner semantics) to 1.
  *(Revised in review, applies to both commands: an initial implementation fell back
  to a fresh page instance when the route was not registered, for strict
  backwards compatibility with the old tasks — `build:sitemap` generated even with
  `hyde.generate_sitemap` disabled, and `build:rss` had no guard at all, emitting an
  empty feed with zero posts. That silently overriding the user's own configuration
  or producing a useless file is a trap, not a feature: the commands now fail with
  a generic "feature is not enabled" error when the route is not registered. Because
  the lookup is route-first rather than feature-flag-first, a user-defined page under
  the route key is still built even when the feature conditions are unmet — the only
  behavior the fallback enabled that anyone could plausibly want, preserved without
  it.)*
  *(Revised again in a second review pass: the first revision reported the specific
  unmet condition — no base URL, disabled in config, no posts, missing SimpleXML —
  by re-checking the `Features::hasSitemap()`/`hasRss()` conditions in the command.
  That mirror was dropped for a single static message: its final SimpleXML branch
  attributed the failure by elimination rather than observation, so any drift in the
  mirrored conditions or an extension removing the page would blame SimpleXML on a
  system where it is fine, and these commands fail too rarely to justify carrying
  duplicated feature logic for a nicer message.)*
- `GlobalMetadataBag` verified: the sitemap head link is emitted under the same
  `Features::hasSitemap()` condition that registers the page — no drift possible.
- Realtime compiler needed no changes (PR 2's route-first resolution); a serve test
  asserts `sitemap.xml` returns the generated XML with `application/xml`.
- Heads-up for part B: `BuildTaskServiceUnitTest`'s framework-task fixtures were
  migrated from `GenerateSitemap` to `GenerateBuildManifest` (not `GenerateRssFeed`)
  so removing the RSS task won't churn them again. Part B should mirror everything
  here with `RssFeedPage`, taking its route key from `RssFeedGenerator::getFilename()`.

Implementation notes, part B (branch `v3/non-html-pages-convert-rss-feed`):

- `RssFeedPage` mirrors `SitemapPage` throughout: thin subclass in `XmlGenerators`,
  container-resolved `compile()` (rebind verified by test), registered behind
  `Features::hasRss()` with the D5 skip check, hidden from navigation, D3-excluded
  from the sitemap, and both user override paths verified end-to-end.
- One divergence: the route key comes from `RssFeedGenerator::getFilename()`
  (config `hyde.rss.filename`), and since the removed task wrote any configured
  filename verbatim, `RssFeedPage` overrides `identifierHasExplicitOutputExtension()`
  to always use the filename as the literal output path — `feed.rss` or an
  extensionless name would otherwise regress to `.html`-suffixed output (see the
  D2 part B qualification).
- `build:rss` builds the registered route's page like `build:sitemap`, and fails
  with the generic "feature is not enabled" error when the route is not registered
  (see the revised-in-review notes in the part A section — the old task's no-guard
  semantics, where an explicit invocation emitted an empty feed with zero posts,
  were deliberately not preserved).
- `BuildTaskService` no longer registers any feature-gated tasks; the `Features`
  facade import went with the last one. The remaining framework tasks
  (clean/transfer/manifest) are all config-gated.

### PR 6 — Generated `robots.txt` ✅ Implemented

Goal: sensible robots.txt out of the box, zero config.

- `robots.txt` registered as an `InMemoryPage` per D4; default output
  `User-agent: * / Allow: /` plus a `Sitemap:` line when `Features::hasSitemap()`.
- Config (e.g. `hyde.robots`) for disallow rules / disabling; user-defined page
  precedence per D5 (an explicitly registered `robots.txt` page wins).
- Depends on PRs 1, 2, 5 patterns.

Implementation notes (branch `v3/non-html-pages-robots`):

- `RobotsTxtPage extends InMemoryPage` mirrors `SitemapPage`/`RssFeedPage` throughout:
  thin subclass, container-resolved generator in `compile()` (rebind verified by test),
  registered in `HydeCoreExtension::discoverPages()` with the D5 skip check, hidden
  from navigation, D3-excluded from the sitemap via the non-HTML default, and both
  user override paths (booting callback and extension) verified end-to-end through
  the real `build` command per the D5 mandate.
- The page and its `RobotsTxtGenerator` live in a new
  `Hyde\Framework\Features\TextGenerators` namespace mirroring `XmlGenerators`
  (and likewise outside `Hyde\Pages`, exempting the page from the discovered-page
  unit test contract). PR 7's llms.txt generator and page should land there too —
  as `LlmsTxtGenerator`/`LlmsTxtPage` for symmetry, superseding the epic's earlier
  `GeneratesLlmsTxt` working name.
- Feature gate: `Features::hasRobotsTxt()` reads only `hyde.robots.enabled`
  (default `true`). Unlike `hasSitemap()`/`hasRss()` there is no site URL
  requirement — robots.txt directives are relative, and the one absolute URL (the
  `Sitemap:` line) is gated separately inside the generator by `Features::hasSitemap()`,
  the same condition that registers the sitemap page and emits its head link
  (the `GlobalMetadataBag` no-drift precedent). Consequence: the page registers
  unconditionally on default config, so it appears in zero-config builds — several
  existing tests asserting exact collections gained a `hyde.robots.enabled => false`
  in their setup, alongside their existing sitemap/RSS switches.
- Generator output: `User-agent: *`, then verbatim `Disallow:` lines from the
  `hyde.robots.disallow` config array, or `Allow: /` when there are none (a group
  needs at least one rule; an unconditional `Allow: /` next to disallow rules would
  be noise). The config entries are *rule values*, not filesystem paths — named and
  documented as such in the config stubs and generator — and are deliberately not
  normalized (no leading-slash fixup, trimming, or empty-string removal):
  normalization would guess intent and break valid values like wildcard patterns or
  the empty string (a valid "allow everything" rule). The verbatim contract is
  string-only, validated per entry: a non-string value throws an
  `InvalidConfigurationException` naming `hyde.robots.disallow` and the offending
  index, instead of surfacing as a PHP-level type error at build time. Later
  generated text pages copying this pattern (llms.txt) should keep both halves —
  verbatim strings, explicit validation.
- `robots.txt` is within the D2 allowlist, consistent with the PR 5 confirmation
  that first-party generated files do not need option (b).
- No `build:robots` command: the sitemap/RSS commands exist only as carry-overs of
  the removed post-build tasks; robots.txt never had one, and the standard build
  and realtime compiler (serve test asserts `text/plain`) cover the lifecycle.

### PR 7 — Generated `llms.txt` ✅ Implemented

Goal: best-in-class llms.txt support — no other SSG generates this well out of the box.

- `GeneratesLlmsTxt` action per the llms.txt spec: site name as H1, `hyde.description`
  ("about" blockquote), sections of route links using page titles and the new
  documentation page abstracts (#2523) as link descriptions.
- `llms.txt` registered as an `InMemoryPage` wired like robots.txt (feature-gated,
  config for section grouping/exclusions, container-resolved generator, user-defined
  page precedence).
- **Make the default state (on vs. off) a deliberate decision with a clean opt-out**,
  not an afterthought. Some of our audience is privacy/OPSEC-minded and will have
  opinions about surfacing content to AI crawlers; the feature flag (and its default)
  should be a first-class, documented choice, mirroring how `robots.txt` disabling
  works, rather than something a user has to discover.
- Consider `llms-full.txt` (full page contents) as a follow-up, not in scope.

Implementation notes (branch `v3/non-html-pages-llms-txt`):

- `LlmsTxtPage` + `LlmsTxtGenerator` land in `Hyde\Framework\Features\TextGenerators`
  next to the robots.txt pair (superseding the `GeneratesLlmsTxt` working name, as
  PR 6 anticipated), and mirror `RobotsTxtPage` throughout: thin `InMemoryPage`
  subclass, container-resolved generator in `compile()` (rebind verified by test),
  registered in `HydeCoreExtension::discoverPages()` with the D5 skip check, hidden
  from navigation, D3-excluded from the sitemap, and both user override paths verified
  end-to-end through the real `build` command. No `build:llms` command, for the same
  reason PR 6 added no `build:robots`.
- **Default on, with the opt-out as the documented choice.** `Features::hasLlmsTxt()`
  reads `hyde.llms.enabled` (default `true`), so the file ships by default. The
  decision the epic demanded: llms.txt lists only already-published pages and surfaces
  nothing the sitemap does not, sitemap/RSS/robots are all on by default, and the
  actual crawler control plane is robots.txt, not llms.txt — so an opt-*in* would
  bury the feature for the majority to protect a minority that a `false` in the config
  serves just as well. The opt-out is called out in the config stub, the release notes,
  and its own UPGRADE.md step rather than being left for users to discover.
- **Emerging-standard caveat, recorded deliberately.** llms.txt is a proposal, not a
  ratified standard, so the generated *format* carries no backwards-compatibility
  promise: we expect to change it in minor and patch releases as the spec moves. This
  is stated in the config stub, the generator docblock, the release notes, and
  UPGRADE.md (which points users who need a frozen format at the user-defined page
  tier). Shipping an imperfect llms.txt is judged better than shipping none.
- **Deviation — site URL is required** (unlike robots.txt, which deliberately is not
  gated on one). `hasLlmsTxt()` requires `Hyde::hasSiteUrl()`, putting llms.txt in the
  sitemap/RSS camp: the file's entire payload is links, and relative links in a file
  fetched by an arbitrary agent are a degraded product. Consequence: zero-config sites
  without a base URL get no llms.txt, exactly as they get no sitemap. Under
  `hyde serve` the realtime compiler overrides the site URL, so the page *is* served
  locally (asserted by a `text/plain` serve test).
- **Deviation — there is no section configuration at all.** The epic (and the research
  doc) asked for "config for section grouping/exclusions", sketched as route-key globs.
  An initial implementation shipped a `hyde.llms.sections` map of page class to section
  heading; it was cut in review. Hyde already *knows* its page types, so grouping needs
  no user input to be correct, and the config bought only heading renames and bulk
  exclusion — rare needs, paid for by every user in config-file surface (a five-entry
  array, entry validation, an exception path, and a comment explaining that omitting a
  class silently drops those pages, which is a trap the framework did not previously
  have). The section map now lives as a `protected sections()` method on the generator:
  page classes are matched with `instanceof` in declaration order — the same semantics
  `PageCollection::getPages()` and `RouteCollection::getRoutes()` use — so a user's
  `GuidePage extends MarkdownPage` lands in the `Pages` section, while every
  `InMemoryPage` descendant (the generated pages, redirects, and the documentation
  search page) is absent from the map and therefore never listed. Users who genuinely
  need different sections have the D4 tier already advertised for exactly this:
  override the generator and rebind it in the container. The config surface is now two
  keys, `enabled` and `description`, matching the size of the `rss` and `robots` blocks.
  A method rather than a constant because overriding the generator *is* the advertised
  customization tier, and a method lets an override compute its sections from config,
  installed extensions, or runtime state, which a constant expression cannot.
  *Design rule this records: a configuration option must be justified against the
  container-rebind tier that already exists, not merely be useful in principle.*
- **Page ordering is route order, deliberately.** Sections are emitted in the order
  `sections()` declares them, and pages within a section in route-collection order —
  the same order the sitemap lists and the build compiles them in. This is a chosen and
  tested contract, not an accident of discovery: `FileFinder` sorts its results by path,
  so the order is deterministic and platform-independent, and because Hyde strips
  numeric filename prefixes from route keys while still discovering by path, a
  `01-installation.md` / `02-usage.md` docs set lands in the file in its intended reading
  order with clean URLs. Navigation priority was considered as the ordering key and
  rejected: it would couple the file to navigation config, and it is meaningless for the
  blog posts and nav-hidden pages that make up much of the listing.
- **Deviation — `hyde.description` does not exist.** The epic assumed a site-level
  description config key; there is none (only `hyde.rss.description`). Added
  `hyde.llms.description`, mirroring the RSS key rather than inventing a global one,
  which would have pulled in the `hyde.meta` description tag and page metadata
  generation — a cross-cutting change that does not belong in this PR. It is nullable,
  and the summary blockquote is omitted when unset (only the H1 is required by the
  spec).
  *Follow-up recorded (out of scope): site identity metadata is fragmenting.* The site
  name, base URL, language, and now two separate descriptions (`hyde.rss.description`
  and `hyde.llms.description`) all describe the same site identity from different config
  keys. A coherent site-metadata object — name, canonical URL, description, language,
  author/organization — with feature-specific overrides would consolidate them. That is
  its own architectural change, not scope for this epic; this PR deliberately mirrored
  the existing RSS key rather than pre-empting that design.
- **Markdown-significant characters in titles are escaped.** A page titled
  `Arrays [Advanced]` would otherwise emit `- [Arrays [Advanced]](url)`, a malformed
  link. `escapeLinkLabel()` escapes `[`, `]`, and `\` in the label. Link *descriptions*
  are not escaped: they are prose trailing the link rather than delimiter-sensitive
  syntax.
- **Link descriptions:** the `abstract` front matter added by #2523, falling back to
  `description`. #2523 only added `abstract` to the docs *content* — there is no
  framework schema support for it, and consistent with PR 4 (which did not add
  `sitemap` to `PageSchema::PAGE_SCHEMA` either), `abstract` was not added to the
  schema; it is documented on the generator that reads it. Note that this PR adds **no
  new front matter keys at all** — it only consumes `abstract`, `description`, and
  `sitemap`, which all already existed. Whitespace
  in descriptions is collapsed to a single line, since a multi-line YAML block scalar
  would otherwise emit a broken list item — this is *not* a "verbatim string" case like
  the robots.txt disallow rules, where PR 6 correctly refused to normalize, because
  here the value is prose embedded in a line-oriented format rather than an exact-match
  rule value. With the sections config gone, no llms config entry needs validation: both
  remaining keys are scalars read through the typed `Config` accessors.
- **Deviation — 404 pages are never listed.** An error page is not content, and every
  real-world llms.txt excludes it. Filtered in the generator by identifier, mirroring
  the `$identifier === '404'` special case `SitemapGenerator` already carries. This is a
  generator-level curation concern rather than a page-level default (the sitemap
  precedent likewise keeps its 404 handling in the generator), and it is the reason the
  sitemap-derived inclusion rule is not a bare alias for `showInSitemap()`.
- Everything else the epic left implicit held: `llms.txt` is within the D2 allowlist,
  and the generated page self-excludes from its own listing (and the sitemap) through
  the D3 resolved-output-path default.

> **Scope correction (post-implementation review).** The first cut of this PR was
> overbuilt for the value delivered, and three pieces were cut back before merge: the
> `hyde.llms.sections` config (see the sections deviation above), the
> `HydePage::showInLlmsTxt()` page method, and the `llms` front matter key that briefly
> replaced it (both in the D3 "Reused, not duplicated" note). Between them they added a
> public method to every page class, an entry in the `BaseHydePageUnitTest` contract with
> six implementations, a front matter key we would have to support for the life of v3, a
> config array with its own validation and exception path, and a config comment long
> enough to advertise that the option was not simple. All of it served needs that the
> existing `sitemap: false` front matter and the D4 container-rebind tier already served.
> The feature's user-facing capability is materially unchanged; only the surface shrank.
> The final shape adds **no new front matter, no page-model API, and two scalar config
> keys.**
>
> Three rules worth carrying into PR 8 and any future generated-page work:
> 1. **The D4 rebind tier is the default answer for customization.** A new config key or
>    page-model method has to beat it, not merely be useful.
> 2. **Front matter is forever.** A key we introduce is public API we must support and
>    document for the life of the major version, so a speculative one is a real liability.
>    Adding a key later is additive and non-breaking, which makes "wait for the evidence"
>    the cheap option and "ship it just in case" the expensive one.
> 3. **A long explanatory comment in a config stub is a design smell,** not diligence. If
>    an option needs paragraphs to explain, the option is usually the problem.

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