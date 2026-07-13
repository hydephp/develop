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
