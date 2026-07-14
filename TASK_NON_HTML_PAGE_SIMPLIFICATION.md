# Non-HTML Page Simplification Task Specification

## Objective

Reduce the accidental complexity left by the first-class non-HTML page work while preserving its user-facing behavior:

- generated files remain registered pages and routes;
- `InMemoryPage::file()` remains the explicit arbitrary-file API;
- registered routes continue to win over static assets in the realtime compiler;
- sitemap inclusion remains a page policy through `showInSitemap()`;
- generated-file generators remain replaceable through the service container;
- user-defined pages continue to replace framework-generated files;
- the existing `build:sitemap` and `build:rss` commands remain available.

The implementation is split into isolated issues so each change can be reviewed, tested, and committed independently.

## Issue 1: Derive route keys from resolved output paths

### Problem in the current code

`HydePage` constructs its stored route key with `RouteKey::fromPage()` before the instance output path is considered. `RouteKey::fromPage()` independently combines the page class output directory, identifier, and static `$outputExtension`. `InMemoryPage::file()` then overrides `getOutputPath()` at the instance level.

This creates two representations of the same output semantics:

- `packages/framework/src/Support/Models/RouteKey.php` derives a route from class-level declarations;
- `packages/framework/src/Pages/InMemoryPage.php` can resolve a different exact instance output path.

`DocumentationPage` also overrides both `getRouteKey()` and `getOutputPath()` to keep flattened documentation routes aligned, illustrating the same duplication.

### Implementation

1. Add `RouteKey::fromOutputPath(string $outputPath)`, with the single route rule: remove a trailing `.html`; retain every other path and extension verbatim.
2. Change `HydePage` construction so the stored route key is derived from the instance's resolved `getOutputPath()`.
3. Change `HydePage::outputPath()` to construct the output path directly from the normalized page identifier, output directory, and output extension instead of round-tripping through a route key.
4. Keep `RouteKey::fromPage()` as a compatibility/helper API, but implement it by passing the page class's resolved static output path to `fromOutputPath()`.
5. Remove `DocumentationPage::getRouteKey()` once its resolved `getOutputPath()` automatically determines the stored route key.

### Acceptance criteria

- HTML output `foo/bar.html` has route key `foo/bar`.
- Non-HTML output `foo/bar.json` has route key `foo/bar.json`.
- Extensionless exact output `feed` has route key `feed`.
- An exact-path `InMemoryPage::file('robots.txt')` has output path and route key `robots.txt`.
- A page subclass overriding only `getOutputPath()` receives a matching route key without also overriding `getRouteKey()`.
- Documentation numerical-prefix stripping, post date-prefix stripping, output directories, non-HTML extension de-duplication, and flattened documentation output keep their existing behavior.
- Existing page, route, build, manifest, and realtime compiler tests remain green.

## Issue 2: Consolidate generated-file page wiring

### Problem in the current code

The following internal classes repeat the same page shell:

- `Framework/Features/XmlGenerators/SitemapPage.php`
- `Framework/Features/XmlGenerators/RssFeedPage.php`
- `Framework/Features/TextGenerators/RobotsTxtPage.php`
- `Framework/Features/TextGenerators/LlmsTxtPage.php`

Each class supplies an exact path, hidden navigation, a container-resolved generator, and a small `compile()` adapter. `HydeCoreExtension` repeats a feature check and discovery method for each shell. XML generators return themselves from `generate()` and require `getXml()`, while text generators return their final strings directly.

### Implementation

1. Add an internal `GeneratedFileGenerator` contract with one final rendering method returning a string.
2. Implement the contract in the text generators and `BaseXmlGenerator` through compatibility adapters, retaining the existing public `generate()` behavior for generator subclasses and callers.
3. Add one internal `GeneratedFilePage` that stores an exact output path and generator class, hides itself from navigation, resolves the generator from the container at compile time, and returns the contract's final string.
4. Add a generated-file definition/registry containing the four feature checks, output paths, and generator bindings. The configured RSS filename is resolved when definitions are created.
5. Make `HydeCoreExtension` loop over the registry and add only missing routes.
6. Update `build:sitemap`, `build:rss`, and the robots sitemap link to use registry paths instead of specialized page classes.
7. Remove the four specialized generated page classes and consolidate their tests around `GeneratedFilePage` plus route behavior.

### Acceptance criteria

- The same four generated routes appear under the same feature conditions and configured RSS filename.
- Generated pages are exact-path pages, hidden from navigation, and excluded from the sitemap by default.
- All four files compile through the standard build and remain present in the build manifest and route list.
- Rebinding any concrete generator in the service container changes the compiled route output.
- Existing `generate()` and XML `getXml()` generator APIs continue to work.
- `build:sitemap` and `build:rss` retain their current success, failure, configured-filename, and user-page override behavior.

## Issue 3: Register framework defaults after user pages

### Problem in the current code

`PageCollection::runExtensionHandlers()` invokes extensions in registration order. `HydeCoreExtension` is registered first, so generated pages are added before user extensions. A user extension currently wins because adding an exact-path page happens to replace the generated page under the same page-collection source key, or later wins in the route collection.

That makes “user pages beat framework defaults” dependent on collection keys and timing rather than a lifecycle guarantee.

### Implementation

1. Add a documented `discoverDefaultPages(PageCollection $collection)` extension hook for fallback pages.
2. Run all normal `discoverPages()` hooks first, then all `discoverDefaultPages()` hooks.
3. Move the generated-file registry loop from `HydeCoreExtension::discoverPages()` into `discoverDefaultPages()`.
4. Keep each generated definition's route-existence check so defaults only fill gaps.
5. Add a regression test where a user extension registers the same route under a different page-collection source key; assert the default is not added at all.

### Acceptance criteria

- Source-discovered pages, booting-callback pages, and normal extension pages exist before framework defaults are evaluated.
- A user page with a generated route key suppresses that default regardless of its source path/collection key.
- Only one page and one route exist for an overridden generated output.
- Existing extension discovery order remains unchanged within each phase.
- Other core-discovered pages (redirects and documentation search pages) retain their existing discovery behavior.

## Verification

For each issue:

1. Run its focused unit and feature tests before committing.
2. Run the complete framework test suite after the final issue.
3. Run realtime compiler tests because route/output semantics cross package boundaries.
4. Run the repository formatting/static checks available for changed PHP files.
5. Confirm the worktree is clean after the final commit.

## Deliberate non-goals

- Do not introduce an `OutputPath` value object in this refactor. Existing framework APIs expose paths as strings; changing all of them would add migration surface without being necessary to establish one source of truth.
- Do not add a public general-purpose `hyde build:route` command. Removing command-driven page subclasses solves the immediate coupling; a new CLI API should be evaluated separately.
- Do not revisit the `$fileExtension` to `$sourceExtension` rename here. It is an independent v3 API cleanup already implemented on this branch, not a dependency of these simplifications.
- Do not change route-first realtime compiler behavior, sitemap policy, exact-path validation, generated content formats, or feature configuration.
