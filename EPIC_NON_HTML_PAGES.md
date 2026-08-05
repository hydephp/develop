# Epic: First-class non-HTML pages (robots.txt, llms.txt, sitemap, RSS)

> Status: In progress — PRs 1, 2, 4, 5, 9 implemented on the v3 development branch.
> PR 6 (robots.txt) and PR 7 (llms.txt) were implemented, then reverted as
> overengineered; both are being redesigned from scratch.
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
- **Custom page classes cannot declare a non-HTML output format**, forcing them to
  override path resolution and making route-key and output-path drift possible.
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

> **Implemented (PR 1):** `RouteKey::fromPage()` appends the page class's configured
> non-HTML output extension to the key (skipping it when the identifier already ends
> with it), so custom page classes configured with a non-HTML output extension are
> D1-compliant out of the box and PR 2 can rely on "route key == request path with
> only `.html` stripped" universally.

### D2: In-memory output formats are inferred from the identifier

`InMemoryPage::outputPath()` uses `pathinfo($identifier, PATHINFO_EXTENSION)` directly.
When the result is empty it appends `.html`; otherwise it keeps the identifier unchanged.
The inherited instance `getOutputPath()` calls this same static method, so static and
instance path resolution cannot disagree.

This final design followed three rejected approaches:

1. **Extension allowlist inference.** Treating a fixed set such as `.txt`, `.xml`, and
   `.json` as files made those formats convenient but merely moved the ambiguity. An
   identifier ending in `.webmanifest` or any future format unexpectedly became HTML.
2. **`InMemoryPage::file()` plus `$exactOutputPath`.** The explicit instance flag
   supported arbitrary filenames, but violated the static/instance path contract:
   `InMemoryPage::outputPath('robots.txt')` produced `robots.txt.html` while the flagged
   instance's `getOutputPath()` produced `robots.txt`. Compiler integrations may resolve
   output paths statically, so that mismatch was unsafe.
3. **`$outputExtension` subclasses.** A class-level extension restored static/instance
   symmetry, but the developer experience was poor: every one-off file required a
   boilerplate subclass, callers had to omit the extension from the identifier, and a
   configurable RSS filename still needed its own path override.

Pure `pathinfo` inference keeps arbitrary extensions, needs no flag or subclass, and
preserves symmetry. The earlier concern that versioned documentation paths would be
false positives was based on a misunderstanding: `pathinfo` examines the basename, so
`docs/1.x/index` has no extension and correctly becomes `docs/1.x/index.html`. While
`docs/1.x` itself has extension `x`, that path is not a valid route key for the version's
root index; the valid identifier ends in `index`.

### D3: Sitemap inclusion becomes a page-level concern

Replace the `instanceof Redirect` filter in `SitemapGenerator` with a
`HydePage::showInSitemap(): bool` method backed by front matter (`sitemap: false`),
with defaults: `false` for redirects and for pages whose output is not `.html`,
`true` otherwise. This fixes the `search.json` leak, prevents the new
sitemap/feed/robots pages from self-listing, and gives users per-page opt-out —
a standalone feature in its own right.

> **Implementation constraint (from PR 1) — read before writing `showInSitemap()`:**
> the "output is not `.html`" default MUST be derived from the page's *resolved
> output path* (`getOutputPath()`), not merely from the declared extension. The
> resolved path is the canonical answer and also covers generated pages such as
> the RSS feed, whose configurable filename cannot be represented by one fixed
> extension. Keying the default off `getOutputPath()` makes all generated pages
> self-exclude correctly and prevents the `search.json` leak from returning.

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
> agents.
>
> The known cost is the converse case: a page kept out of the sitemap for SEO reasons
> (thin content, a duplicate, a paginated archive) that would still be useful to an agent.
> That site is not stuck today — overriding `shouldListPage()` on the generator and
> rebinding it is exactly the D4 tier, and it is a three-line override. The trade is
> deliberate: the edge case pays at the generator tier instead of every site paying for a
> second front matter key. Should the case turn out to be common, reintroducing `llms:`
> front matter (or promoting it to a `showInLlmsTxt()` method) is additive and
> non-breaking — so waiting
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

**Decided: plain `InMemoryPage`.** D3 already defaults non-HTML pages out of the
sitemap, and the commands only build registered routes. The temporary thin subclasses
were justified by an early command fallback that instantiated fresh pages, but that
fallback was removed in review. With no type-identity consumer remaining, generated
pages are ordinary `InMemoryPage` instances registered with container-resolved
`compile` macros. The D4 swappability tier is preserved and verified by rebind tests.

### D5: User-defined pages beat generators

If the page collection already contains a user-defined page with a route key such
as `robots.txt`, the framework does not register its generated page.
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
> asserted through the real `build` command output. *(Part B: both paths verified the
> same way for the feed page.)* The robots.txt and llms.txt equivalents remain
> mandatory for PR 6 and PR 7 whenever they are rebuilt.

### D6: No built-in `TextPage` or `.txt` autodiscovery

First-class non-HTML support is about a page's output path and participation in the
route/build/serve lifecycle; it does not require a dedicated source-backed page class
for each file extension. A plain `InMemoryPage` whose identifier includes the desired
extension provides the full lifecycle integration and is a better fit for dynamic
content, while the generated robots and llms pages will cover the common cases without source files.

A core `TextPage` would add only the convenience of autodiscovering `_pages/*.txt`,
while creating pressure for parallel `XmlPage`, `JsonPage`, and similar classes.
Plain-text files also cannot carry front matter, requiring page-type defaults or
special handling for navigation and sitemap behavior. That framework surface is not
justified by the narrow drop-a-file use case. If demand emerges for filesystem-backed
verbatim files, it should be designed as a generic raw/public-file mechanism instead
of one page class per extension. Custom discoverable page classes remain supported as
an extension point.

### D7: Navigation exclusion mirrors D3, and front matter outranks every default

Non-HTML pages are hidden from automatic navigation by the same rule D3 uses for the
sitemap: the default is derived from the *resolved output path* (`getOutputPath()`), not
the declared extension, so generated pages self-exclude for free. `robots.txt` and
`feed.xml` are not destinations a visitor navigates to, and D6 named exactly this
("page-type defaults or special handling for navigation and sitemap behavior") as a cost
of source-backed text pages — so the default belongs in `NavigationDataFactory`, where it
applies to every non-HTML page rather than to one page class.

The first implementation made front matter a three-state override of *only* this new
default: `navigation.hidden: false` re-showed a non-HTML page but could not re-show a blog
post, a route key in `hyde.navigation.exclude`, or a page in a hidden subdirectory, which
stayed hidden as they did in v2. That was reversed. Front matter now decides visibility
outright and the inferred default applies only when it is unset:

```php
return $this->searchForHiddenInFrontMatter() ?? $this->isHiddenByDefault();
```

Front matter is the most specific channel a user has, so it outranks both the conventions
Hyde infers membership from and the global `hyde.navigation.exclude` configuration. An
override that only works in one direction — and only against the newest of four rules — is
a rule users would have to memorize rather than derive. The `??` shape also matches
`makePriority()` and `makeLabel()` in the same factory, which already put front matter
ahead of configuration and convention, so `makeHidden()` was the outlier.

Consequence beyond this epic's scope: `navigation.visible: true` on a blog post, an
excluded route key, or a page in a hidden subdirectory is no longer a silent no-op. No
migration is expected — such matter did nothing in v2, so nobody set it to achieve the
v2 behavior — but it is recorded in `UPGRADE.md` because the front matter is inert in the
version users are upgrading from.

## Work breakdown (planned PR sequence, in dependency order)

### PR 1 — Foundation: page-class output extensions ✅ Implemented

Goal: any page class can emit a non-`.html` file without overriding `getOutputPath()`.

- Add `$outputExtension` (default `'.html'`) to `HydePage`; use it in
  `outputPath()` (`HydePage.php:211-214`).
- Route keys follow D1; audit `RouteKey` and `Route` for assumptions.
- Override `InMemoryPage::outputPath()` to infer the output suffix from the identifier.
- Keep `DocumentationSearchIndex`'s `.json` extension in its identifier.
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
- Page-class output extension handling was placed in `RouteKey::fromPage()` (see D1 note)
  rather than only in `outputPath()`, so route keys and output paths cannot drift.
- **Revised before PR 8, then finalized in the subsequent design pivot:** the
  allowlist-based inference and later instance-level exact-path factory were removed.
  A class-level extension approach briefly replaced them, but was also rejected for
  in-memory pages due to its boilerplate and poor call-site ergonomics. The final
  implementation uses unrestricted `pathinfo` inference (see D2).
- Sitemap / non-HTML detection reads the resolved output path, not just the static
  extension declaration, so specialized static path overrides remain supported.

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
- `getContentType()` gained one arm, `rss` → `application/rss+xml`, once PR 5B made
  `hyde.rss.filename` the feed's route key: `feed.rss` is a supported configuration
  that was otherwise served as `text/html`. The default `feed.xml` stays
  `application/xml`, which browsers render in place instead of prompting a download.
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
  `BaseHydePageUnitTest` contract.
- The non-HTML self-exclusion is verified end-to-end: an `InMemoryPage` with a `.txt`
  identifier is built by the real `build` command and asserted absent from the built
  `sitemap.xml`, guarding the D3 resolved-output-path constraint
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
- Use plain `InMemoryPage` instances with container-bound `compile` macros (D4);
  D3 already handles sitemap self-exclusion.
- **Verify the generators are actually container-resolvable** before advertising the
  rebind: no unresolvable constructor dependencies, not `final` (or the swap can't be
  bound). D4's whole swappability tier is a lie if `app(SitemapGenerator::class)`
  can't be rebound. Add a test that rebinds the generator and asserts the page's
  compiled output changes.
- Ensure first-party generated page identifiers include their output extension per D2;
  the configurable RSS filename then uses the shared inference without an override.
- Register in `HydeCoreExtension::discoverPages()` behind `Features::hasSitemap()` /
  `Features::hasRss()`; remove `GenerateSitemap`/`GenerateRssFeed` from
  `BuildTaskService::registerFrameworkTasks()` (evaluate deprecation vs. removal —
  v3 allows breaking changes, but third-party code may reference the task classes).
- Rewire `build:sitemap` / `build:rss` commands to build the same registered page
  via `StaticPageBuilder::handle(...)`.
- Verify `GlobalMetadataBag` head links and the `hyde.url` requirements still hold
  (`Features::hasSitemap()` already requires a site URL).
- Nice side effect: build output shows them under "Dynamic Pages" with the standard
  progress display.

Implementation notes, part A (branch `v3/non-html-pages-convert-sitemap`):

- A plain `InMemoryPage` with a container-resolved `compile` macro is hidden from
  navigation and self-excludes from the sitemap via the D3 non-HTML default.
  Registered at the end of `HydeCoreExtension::discoverPages()` behind
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
- This supersedes the pending virtual-route approach for serving the sitemap and feed
  (registering `/sitemap.xml` and `/feed.xml` handlers in the realtime compiler). Both
  routes are now ordinary pages, so the dev server needs no generator imports, no
  proxy exclusions, and no accepted asymmetry for a configured feed filename. A test
  covers the one property that approach was designed around: the site URL is overridden
  before the kernel discovers pages, so both are served locally without a production
  site URL configured.
- For part B, `BuildTaskServiceUnitTest`'s framework-task fixtures were
  migrated from `GenerateSitemap` to `GenerateBuildManifest` (not `GenerateRssFeed`)
  so removing the RSS task would not churn them again. The RSS route key comes from
  `RssFeedGenerator::getFilename()`.

Implementation notes, part B (branch `v3/non-html-pages-convert-rss-feed`):

- The RSS feed mirrors the sitemap throughout: a plain `InMemoryPage` with a
  container-resolved `compile` macro (rebind verified by test), registered behind
  `Features::hasRss()` with the D5 skip check, hidden from navigation, D3-excluded
  from the sitemap, and both user override paths verified end-to-end.
- One divergence: the route key comes from `RssFeedGenerator::getFilename()`
  (config `hyde.rss.filename`). The shared `InMemoryPage` inference keeps configured
  filenames with extensions verbatim and gives extensionless identifiers HTML output.
- `build:rss` builds the registered route's page like `build:sitemap`, and fails
  with the generic "feature is not enabled" error when the route is not registered
  (see the revised-in-review notes in the part A section — the old task's no-guard
  semantics, where an explicit invocation emitted an empty feed with zero posts,
  were deliberately not preserved).
- `BuildTaskService` no longer registers any feature-gated tasks; the `Features`
  facade import went with the last one. The remaining framework tasks
  (clean/transfer/manifest) are all config-gated.

### PR 6 — Generated `robots.txt` 🔄 Reverted, to be redesigned

Goal: sensible robots.txt out of the box, zero config.

- `robots.txt` registered as an `InMemoryPage` per D4; default output
  `User-agent: * / Allow: /` plus a `Sitemap:` line when `Features::hasSitemap()`.
- Config (e.g. `hyde.robots`) for disallow rules / disabling; user-defined page
  precedence per D5 (an explicitly registered `robots.txt` page wins).
- Depends on PRs 1, 2, 5 patterns.

**Reverted.** An implementation landed on branch `v3/non-html-pages-robots` (merged
as PR #2531): a plain `InMemoryPage` with a container-resolved `RobotsTxtGenerator`
compile macro, registered behind `Features::hasRobotsTxt()` (`hyde.robots.enabled`,
default `true`, no site URL requirement) with the D5 skip check. It was reverted as
overengineered; PR 6 will be redesigned from scratch. The goal and design constraints
above still stand.

### PR 7 — Generated `llms.txt` 🔄 Reverted, to be redesigned

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

**Reverted.** An implementation landed on branch `v3/non-html-pages-llms-txt` (merged
as PR #2534): a plain `InMemoryPage` with a container-resolved `LlmsTxtGenerator`
compile macro, registered behind `Features::hasLlmsTxt()` (`hyde.llms.enabled`,
default `false`, site URL required), reusing sitemap inclusion (`showInSitemap()`)
rather than adding a new page method or front matter key. It was reverted as
overengineered; PR 7 will be redesigned from scratch. The goal above still stands.

Three design lessons from that implementation are worth carrying into the redesign
and any future generated-page work:

1. **The D4 rebind tier is the default answer for customization.** A new config key or
   page-model method has to beat it, not merely be useful.
2. **Front matter is forever.** A key introduced here is public API that must be
   supported and documented for the life of the major version, so a speculative one is
   a real liability. Adding a key later is additive and non-breaking, which makes "wait
   for the evidence" the cheap option and "ship it just in case" the expensive one.
3. **A long explanatory comment in a config stub is a design smell,** not diligence. If
   an option needs paragraphs to explain, the option is usually the problem.

### PR 8 — Documentation & release notes ✅ Implemented

- Document in-code virtual pages, `sitemap: false` front matter, robots/llms config,
  the container-rebind customization tier for generated pages, and the "user-defined
  page beats generator" rule.
- Update `HYDEPHP_V3_PLANNING.md` release notes: new features (robots.txt, llms.txt,
  serve support for sitemap/RSS), breaking changes (build task classes
  removed/relocated, search.json removed from sitemaps).

Implementation notes (branch `v3/non-html-pages-documentation`):

- The public InMemoryPage and customization guides document exact non-HTML paths,
  navigation and sitemap defaults, all four generated pages, their feature conditions,
  robots/llms configuration, generator rebinding, and user-defined route precedence.
- The Build Tasks guide no longer describes sitemap/RSS generation as post-build tasks.
  The console command guide now records that `build:sitemap` and `build:rss` compile
  registered pages and fail when no matching page is registered.
- The audit found behavior worth documenting beyond the original checklist: llms.txt
  reuses sitemap inclusion, its format has no minor/patch compatibility promise while
  the proposal evolves, robots.txt controls crawler access while llms.txt does not,
  generated pages are hidden from automatic navigation, and sitemap/RSS registration
  depends on SimpleXML in addition to their documented content prerequisites.
- `HYDEPHP_V3_PLANNING.md` and `UPGRADE.md` already contain the feature, breaking-change,
  and migration entries added with PRs 1–7; this PR verified them rather than duplicating
  those notes.

**Note (PR 6/7 revert):** the robots.txt- and llms.txt-specific documentation and
upgrade/planning entries described above were removed when PR 6 and PR 7 were
reverted. This PR's remaining scope — in-code virtual pages, `sitemap: false` front
matter, the container-rebind customization tier, and the user-defined-page-beats-
generator rule for the sitemap and RSS feed — still stands and is still documented.

### PR 9 — Navigation exclusion for non-HTML pages ✅ Implemented

Unplanned; landed between PRs 7 and 8, so the documentation PR already describes it.
Added after the generated pages had shipped and each was individually hidden from
navigation, which made the missing general rule obvious: any non-HTML page, generated or
user-registered, has the same problem.

Implementation notes (branch `v3/non-html-pages-hide-from-navigation`):

- `NavigationDataFactory::makeHidden()` hides pages whose resolved output path does not
  end in `.html`, and front matter overrides every default rather than only this one, per
  D7. `isHiddenByDefault()` collects the four default rules behind the front matter check.
- Covered at both levels: `NavigationDataFactoryUnitTest` asserts the default and the
  override against each of the four rules, and `AutomaticNavigationConfigurationsTest`
  asserts through the real generated menu that a non-HTML `InMemoryPage` stays out unless
  opted in, and that front matter adds the pages the other three rules hide. The menu
  level is asserted separately because `makeHidden()` only sets the flag that
  `NavigationMenuGenerator` reads through `showInNavigation()`.
- The `visible`/`hidden` front matter pair was left as-is. Both spellings already existed
  and `searchForHiddenInFrontMatter()` already normalized them, so widening the override
  needed no new front matter key — per PR 7's rule 2, the cheapest key is the one not added.

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
