# Versioned Documentation Pages — Design & Implementation Handoff

**Branch:** `v3/support-versioned-documentation-pages` (targets `2.x` a.k.a. the v3 dev line)
**Status:** Implementation complete — see the checklist at the bottom.

This document serves two purposes: it records the design decisions for native versioned
documentation support in HydePHP v3, and it is a handoff file so that anyone (human or agent)
can resume the implementation mid-way with full context.

## Feature summary

Native support for versioned documentation pages, replacing the userland workaround used on
HydePHP.com (duplicated `DocumentationPage` subclasses, class-filtered sidebars, and hand-rolled
per-version search pages).

The design is **filesystem-first**: versions are subdirectories of `_docs` (e.g. `_docs/1.x`,
`_docs/2.x`), compiled to matching subdirectories of the docs output directory (e.g. `docs/1.x`).
A version registry in core drives routing, sidebars, search, the version switcher UI, and the
docs-root redirect. Single-version sites (the default) are completely unaffected.

## Configuration API (`config/docs.php`)

```php
// Empty array (default) = versioning disabled, everything behaves as in v2.
'versions' => [
    // '1.x',
    // '2.x',
],

// Optional. Defaults to the last entry in the versions list (append-newest workflow).
'default_version' => null,
```

Version names must match `/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/` (no slashes); violations throw
`InvalidConfigurationException`. The default version must be in the versions list.

## Architecture

New namespace: `Hyde\Framework\Features\Documentation\Versioning`

- **`DocumentationVersion`** — readonly value object: `name`, `isDefault()` (derived through the
  registry, as defaultness belongs to the configuration), `routeKeyPrefix()` (e.g. `docs/1.x`),
  `homeRouteName()` (e.g. `docs/1.x/index`), `home()`.
- **`DocumentationVersions`** — static registry over config: `enabled()`, `all()`,
  `get(string $name)`, `default()` (null when versioning is disabled),
  `fromIdentifier(string $identifier)` (maps a page identifier's first path segment to a registered
  version, or null), `getEquivalentRoute(DocumentationPage $page, DocumentationVersion $target)`
  (same logical page in another version, null when it does not exist there).
- **`HasDocumentationVersion`** — interface with `getDocumentationVersion(): ?DocumentationVersion`,
  implemented by `DocumentationPage` and the versioned search pages, so the sidebar/search views
  can resolve the version of whatever page is being rendered (docs pages *and* in-memory search
  pages) without class checks.

### Key decisions and their rationale

1. **Version identity comes from the identifier's first path segment**, checked against the
   registry. A directory `_docs/getting-started` is *not* a version unless listed in config.
   **Versioning is all or nothing:** when versions are registered, every documentation page must
   live in a version directory, and source files outside them are discarded during file discovery
   (`HydeCoreExtension::discoverFiles()`). Supporting mixed layouts would make them a permanent
   compatibility promise, and required nullable page versions, default-sidebar and default-search
   fallbacks, and switcher suppression — a lot of policy for pages that the per-version sidebar,
   switcher, and search shards cannot meaningfully serve anyway. Sites wanting a page at the docs
   root can put one in `_pages/docs/index.md`, which overrides the generated redirect.
2. **No new page class.** `DocumentationPage` itself becomes version-aware. A separate
   `VersionedDocumentationPage` would force users (and Hyde internals: `Routes::getRoutes()`,
   navigation, factories) to handle two classes. HydePHP.com's pain came precisely from
   class-based version identity.
3. **Flattened output paths keep the version prefix.** With `docs.flattened_output_paths: true`
   (default), `_docs/2.x/getting-started/installation.md` → route key `docs/2.x/installation`.
   The version segment survives flattening; only the intra-version structure flattens. This keeps
   HydePHP.com-style URLs and avoids cross-version collisions.
4. **The home-route abstraction has a single owner.** `DocumentationPage::home()` and
   `homeRouteName()` keep their v2 semantics: they always point at the documentation root
   (`docs/index`), which under versioning is the generated redirect. Per-version homes belong to
   the version object (`$version->home()`, `$version->homeRouteName()`). Consumers that need the
   default version's home ask the registry for it explicitly. Keeping the legacy root route stable
   is also what lets the published `config/docs.php` call `DocumentationPage::homeRouteName()`
   while the configuration is still loading, without the route helper having to know about the
   configuration lifecycle.
5. **Sidebar config keys are version-agnostic by default.** `docs.sidebar.order/labels/exclude`
   and `docs.exclude_from_search` entries match both the full identifier (`2.x/readme`) and the
   version-stripped identifier (`readme`), so one config applies to all versions, with
   version-specific overrides possible via the full identifier.
6. **Automatic sidebar grouping skips the version segment.** `2.x/getting-started/foo` groups
   under `getting-started`; `2.x/foo` is ungrouped (top level of the version).
7. **Per-version sidebars are container singletons** — `navigation.sidebar.{version}` registered
   in `NavigationServiceProvider`, with the existing `navigation.sidebar` aliased to the default
   version's binding, so both names resolve one instance rather than generating a second identical
   sidebar. The version-specific service names are an internal convention, not public API.
   `DocumentationSidebar::get()` picks the right one from the current render context, and the
   sidebar Blade view calls it instead of `app('navigation.sidebar')`.
8. **Per-version search shards.** `HydeCoreExtension::discoverPages()` registers one
   `DocumentationSearchIndex` + `DocumentationSearchPage` per version (`docs/2.x/search.json`,
   `docs/2.x/search.html`). `GeneratesDocumentationSearchIndex` takes an optional version and
   filters pages to it. The `hyde-search` component resolves the index path from the rendered
   page's version. No root `docs/search.json` is generated when versioning is enabled.
9. **Docs root redirect.** When versioning is enabled, an `InMemoryPage`-based redirect is
   registered at `docs/index` pointing at the default version's home, unless the user has their
   own page at that route key. Version pages remain self-canonical; no hreflang, no duplicate
   HTML at the root.
10. **Version switcher** is a Blade component (`hyde::components.docs.version-switcher`) rendered
    in the sidebar header when versioning is enabled. Alpine dropdown with real anchor links;
    links to the equivalent page in the target version, falling back to that version's home.

### Integration points (files touched)

| File | Change |
| --- | --- |
| `packages/framework/src/Framework/Features/Documentation/Versioning/*` | New classes (registry, value object, interface) |
| `packages/framework/src/Pages/DocumentationPage.php` | `getDocumentationVersion()`, version-preserving flattening |
| `packages/framework/src/Framework/Factories/NavigationDataFactory.php` | Version-stripped identifier in group/label/order/exclude lookups; `homeRouteName()` for default nav order |
| `packages/framework/src/Framework/Features/Navigation/NavigationMenuGenerator.php` | Optional version filter for sidebar generation, version-aware home exclusion and empty-sidebar fallback |
| `packages/framework/src/Framework/Features/Navigation/DocumentationSidebar.php` | Render-context-aware `get()` |
| `packages/framework/src/Foundation/Providers/NavigationServiceProvider.php` | Per-version sidebar singletons |
| `packages/framework/src/Framework/Actions/GeneratesDocumentationSearchIndex.php` | Optional version filter, version-stripped exclude matching |
| `packages/framework/src/Framework/Features/Documentation/DocumentationSearchIndex.php` + `DocumentationSearchPage.php` | Optional version (route key, output path, enabled check) |
| `packages/framework/src/Foundation/HydeCoreExtension.php` | Discarding unversioned source files, per-version search page registration, docs root redirect |
| `packages/framework/resources/views/components/docs/sidebar.blade.php` | Use `DocumentationSidebar::get()` |
| `packages/framework/resources/views/components/docs/sidebar-brand.blade.php` | Include version switcher; version-aware home link |
| `packages/framework/resources/views/components/docs/version-switcher.blade.php` | New component |
| `packages/framework/resources/views/components/docs/hyde-search.blade.php` | Version-aware search index path |
| `config/docs.php` | New `versions` + `default_version` settings |

### Out of scope (documented future enhancements)

- `noindex` policies for legacy versions (`docs.versions_noindex` or per-version metadata).
- Per-page redirects from unversioned URLs (`docs/foo` → `docs/2.x/foo`) for sites migrating
  from unversioned to versioned docs.
- A combined cross-version search index with version facets.
- `make:page --version` flag (you can simply pass the version directory in the page name:
  `php hyde make:page "2.x/installation" --docs`).
- Version aliases (`latest`) — can be emulated with a version named `latest` or a redirect.

## Testing

- New `packages/framework/tests/Feature/VersionedDocumentationTest.php` — end-to-end feature
  coverage (registry, route keys, sidebars, search shards, redirect, switcher).
- Existing suites must stay green with versioning disabled (default) — particularly
  `DocumentationPageTest`, `DocumentationSearchIndexTest`, `DocumentationSearchPageTest`,
  `AutomaticNavigationConfigurationsTest`, sidebar view tests.
- Run: `vendor/bin/phpunit --testsuite FeatureFramework --filter <Name>` from the repo root.

## Implementation checklist (update as you go!)

- [x] 1. Design document committed (this file)
- [x] 2. `DocumentationVersion` + `DocumentationVersions` registry + config + tests
- [x] 3. Version-aware `DocumentationPage` (route keys, output paths, home routes) + tests
- [x] 4. Version-aware sidebars/navigation (factory, generator, provider, views) + tests
- [x] 5. Per-version search index + search pages + build:search verification + tests
- [x] 6. Version switcher component + version-aware search view
- [x] 7. Docs root redirect
- [x] 8. Release notes (`HYDEPHP_V3_PLANNING.md`), `UPGRADE.md`, broad test run, final doc pass

Each step is one commit (or a few small ones). If you are resuming: `git log --oneline` against
this checklist tells you where things stand; the checklist boxes are updated in the same commit
as the work they describe.

## Implementation notes (deviations from the original sketch)

- The version registry helpers gained `fromRouteKey()`, `stripVersionPrefix()`, and
  `stripVersionPrefixFromRouteKey()`, used by navigation/search to make configuration
  entries version-agnostic. The interface method is named `getDocumentationVersion()`.
- `HydePageDataFactory::findTitleFromParentIdentifier()` strips the version prefix so `2.x/index`
  is not titled "2.X".
- `DocumentationSearchIndex::outputPath()` was renamed to `routeKey()`, mirroring
  `DocumentationSearchPage::routeKey()`. The inherited `HydePage::outputPath(string $identifier)`
  signature would otherwise have forced a `DocumentationVersion|string` union on the parameter.
- Documentation site docs (hydephp/docs repo) still need a "Versioned documentation" section —
  that repo is separate from this monorepo.
- Future enhancements deliberately left out (see "Out of scope") remain unimplemented.
