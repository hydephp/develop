# Agent instructions for the HydePHP monorepo

This is the hydephp/develop monorepo. Framework code lives in `packages/framework`,
shared test utilities in `packages/testing`, the dev server in `packages/realtime-compiler`.
We are developing HydePHP v3; the main branch for PRs is `2.x`.

## Epic-driven workflow

- Multi-PR efforts are specified in epic documents at the repo root (like
  `EPIC_NON_HTML_PAGES.md`). The epic is the source of truth: before implementing,
  re-read the relevant PR section and design decisions; after implementing, check the
  diff against them line by line.
- Deviations from the design and decisions the epic left open MUST be written back
  into the epic in the same PR, and implemented sections marked (like "✅ Implemented").
- Before deviating, verify it is a *good* deviation: check side effects against the
  rest of the epic (later PRs, design decisions) so the change doesn't drift from the
  overall design. Also watch for silent under-delivery — implementing a design rule
  only for the cases the current PR exercises will break later PRs that rely on it.
- Work on the epic happens in `v3/<epic-name>-*` branches off the epic base branch.
  Check `git branch --list 'v3/*'` and the epic's status annotations (which may only
  exist on PR branches) before assuming a PR is unimplemented.

## Commits

- Make atomic commits as you go: one logical change per commit (implementation,
  tests, docs/release notes separately when they are separable). Do not batch a
  whole PR into one commit at the end.

## Testing

The goal is full confidence: feature tests give 100% coverage by exercising all user
paths end-to-end (e.g. register a page, run the real `build` command, assert the
output file), and unit tests cover every code unit.

- Run suites with `vendor/bin/pest --testsuite FeatureFramework|UnitFramework|FeatureHyde`.
  Use `--filter` for targeted runs. Run `php monorepo/HydeStan/run.php` before finishing.
- Every public method on page classes is part of the `BaseHydePageUnitTest` contract
  (`packages/testing/src/Common/`): adding public API to `HydePage` means adding an
  abstract test there and implementing it in all page unit tests in
  `packages/framework/tests/Unit/Pages/`. `TestAllPageTypesHaveUnitTestsTest` enforces
  one unit test class per page class.
- The suites read the real project root and pollute the working tree: they delete
  `_media/app.css` and leave untracked junk (`_assets/`, `_docs/docs.md`, `_docs/index.md`,
  `_pages/root.md`, `_pages/root1.md`, `_posts/my-new-post.md`, `_media/app.js`, `_site/`).
  Dozens of environmental failures follow from that state — they are not regressions.
  Restore with `git checkout -- _media/app.css` and delete the junk between runs.
- To prove a change introduces no regressions: run the suite at HEAD, then
  `git checkout HEAD~N -- packages` (pre-change baseline), clean the tree, re-run,
  and diff the sorted `FAILED` lines. Identical lists means no regressions. Restore
  with `git checkout HEAD -- packages`. Don't pipe long pest runs through `tail` —
  redirect to a file and grep it.
- Known pre-existing failure: `FeaturedImageUnitTest` on PHP 8.5 (`MediaFile::findHash()`).

## Release notes and docs

- Add release-notes entries to `HYDEPHP_V3_PLANNING.md` and upgrade steps to
  `UPGRADE.md` as part of the PR that makes the change.
- Breaking-change notes must describe realistic impact. If the note describes a
  scenario nobody would plausibly be in (like relying on double-extension output
  such as `data.json.html`), say that no real impact is expected instead of
  prescribing a migration.

## Code comments

If you catch yourself writing a code comment, stop and think about why. A comment is
usually a smell that the code is doing something weird — step back and look at what
you are actually trying to do before reaching for a comment. Comments are only for:

1. Adding better type support (docblocks, `@param`/`@var` annotations).
2. Documenting public APIs.
3. Explaining why unusual-looking code has to be that way, when there is no other option.

Never write comments that narrate what the next line does, where code came from, or
why a change is correct — that belongs in the PR description, not the code.

## Developer Experience

HydePHP treats Developer Experience as a design constraint, not a layer of polish added after a feature works. The framework exists to make creating content-focused websites feel simple without taking away the power developers expect from Laravel. Its guiding promise is that users should be able to begin with Markdown and sensible defaults, while retaining the freedom to use Blade, customize the frontend, replace conventions, or extend the build process when their project demands it.

The default path should therefore be the shortest path. A common task should work without configuration, manual registration, or knowledge of internal architecture. HydePHP favors convention over configuration, automatic discovery, appropriate default layouts, generated navigation, scaffolding commands, and ready-to-use frontend assets. Configuration and extension points are still important, but they should remain optional until the user has a reason to reach for them. A new feature is aligned with HydePHP when its basic use feels obvious and its advanced use remains possible.

HydePHP also aims to reuse mental models its users already know. APIs should follow Laravel conventions where practical, and features should compose naturally with familiar tools such as collections, facades, Blade components, console commands, configuration files, service providers, and lifecycle callbacks. Naming should describe what an operation does rather than expose how Hyde implements it. Before inventing a new abstraction, an agent should look for the closest existing HydePHP or Laravel pattern and extend that vocabulary consistently.

Good Developer Experience includes the failure path. HydePHP should validate assumptions early, produce actionable error messages, and avoid letting mistakes silently reach the generated site. Recent work on the asset and data systems reflects this approach through automatic validation, clearer exception handling, syntax checking, and helpers that remove repetitive filesystem work. Commands should explain what they are doing, generated files should be predictable, and errors should tell the developer what needs to change.

Performance and feedback speed matter as well. Improvements such as realtime compilation, Vite integration, hot module replacement, intelligent caching, and faster document processing are Developer Experience features because they shorten the distance between an edit and a trustworthy result. Agents should avoid unnecessary work in normal builds, preserve deterministic output, and prefer lazy or cached computation where it reduces repeated cost without making behavior harder to understand.

Finally, a feature is not complete when its implementation compiles. HydePHP requires focused changes, tests that demonstrate the intended behavior, and documentation for changes users can observe. Backward compatibility and the appropriate release branch must also be considered. Tests protect the experience from regression, while documentation confirms that the public API can be explained clearly. When an API is difficult to test or document, that is often evidence that it is also difficult to use.

An AI coding agent working on HydePHP should evaluate every feature with a simple standard: does this make the common case joyful, preserve control for advanced users, behave like the rest of the Laravel ecosystem, fail helpfully, and remain understandable through tests and documentation? The most Hyde-like implementation is rarely the one with the most options or abstractions. It is the one that removes the most friction while introducing the least surprise.
