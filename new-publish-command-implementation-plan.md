# HydePHP v3 — `publish` Implementation Plan

Companion to `hyde-v3-publish-spec.md`. Drive this **one step at a time**.
Hand the agent a single step + the spec — never the whole plan.

---

## Rules of engagement (paste as standing instructions to the agent)

1. **One step only.** You are working on the single step I give you. Do not start,
   scaffold, or "prepare for" any other step.
2. **Plan before code.** First restate the step's scope in your own words and list
   the exact files you will create or edit. Wait for my approval before writing code.
3. **Stay in the box.** Touch only the files listed for this step. No unrelated
   refactors, renames, reformatting, or "while I'm here" changes. If you believe
   another file must change, STOP and ask — don't do it.
4. **Implement to the spec, not to invention.** Follow the referenced spec section
   exactly. Do not add flags, commands, names, options, or behaviors that aren't in
   the spec. If the spec is ambiguous or seems wrong, STOP and ask — don't guess.
5. **Green means done.** The step is complete only when: (a) tests pinning this
   step's acceptance checks pass, and (b) the existing suite still passes. Use the
   project's existing test framework and conventions.
6. **One commit per step**, message referencing the step number. I review the diff
   before we move on.
7. If I say "out of scope for this step," drop it without argument.

### Per-step prompt template

> Implement **Step N** from the implementation plan (pasted below), following the
> spec section it references (pasted below). Obey the rules of engagement.
> Start by restating the scope and listing the files you'll touch, then wait for me.
>
> [paste the step block]
> [paste the referenced spec section]

---

## Step 1 — `OverwritePolicy` service

- **Goal:** the shared, pure decision logic for whether to copy/skip/protect a file.
- **In scope:** a service that, given a source path and destination path, returns
  one of `copy` (missing), `skip` (byte-identical), `blocked` (exists & differs).
  No console UI. No knowledge of views/pages.
- **Out of scope:** any picker, any command wiring, any checksum manifest / historical
  version detection (explicitly excluded by the spec).
- **Files:** the new policy service class + its unit test. Nothing else.
- **Depends on:** nothing.
- **Done when:** unit tests cover all three states (missing / identical / modified),
  and the suite is green.
- **Spec ref:** §7.

## Step 2 — `PublishablePage` value object + `PublishablePages` registry

- **Goal:** the data model for starter pages.
- **In scope:** the immutable `PublishablePage` value object (§5.1 shape), the
  `PublishablePages` registry (`all()`, `get()`, `register()`), and the initial
  catalog from §5.2 (welcome / posts / blank / 404) registered as defaults.
- **Out of scope:** destination resolution logic, any command or picker, any writing
  of files. This step is data + registry only.
- **Files:** the value object, the registry, the catalog registration, and their tests.
- **Depends on:** nothing.
- **Done when:** tests assert the catalog contents (keys, default targets,
  alternatives, `allowCustomTarget`) and that `register()` adds a page. Suite green.
- **Spec ref:** §5.1, §5.2.

## Step 3 — `PublishCommand` spine (flags, guardrails, wizard routing to stubs)

- **Goal:** the command shell with the full flag surface, all error/guardrail
  behavior, and the interactive wizard step 1 — routing to **stub** handlers.
- **In scope:** register `php hyde publish`; define `--layouts --components --all
  --page[=NAME] --to=PATH --force`; implement §2 guardrails (reject `--tag`/`--provider`
  with a redirect message; `--layouts` + `--components` mutually exclusive; `--to`
  only with `--page`; `--config` → redirect to `vendor:publish --tag=hyde-config`;
  non-interactive with no actionable flags → usage error); implement the §3 wizard
  step-1 menu routing to two stub methods (`publishViews()`, `publishPage()`) that
  currently just print "not yet implemented".
- **Out of scope:** real views logic, real pages logic, real config tag. Handlers are
  stubs. Do not implement OverwritePolicy calls yet.
- **Files:** the `PublishCommand` class, its service-provider registration, and the
  command's guardrail/routing tests.
- **Depends on:** nothing (stubs stand in for Steps 4–5).
- **Done when:** tests cover every guardrail/redirect in §9 and the wizard routing,
  against the stubbed handlers. Suite green.
- **Spec ref:** §2, §3, §9.

## Step 4 — Views flow

- **Goal:** replace the `publishViews()` stub with the real views publisher.
- **In scope:** `ViewsPublisher` reading the declared `layouts` / `components` groups;
  the single grouped multi-select picker (§4) with an "All views" option;
  `--layouts` / `--components` prefiltering; `--all` skipping the picker;
  cardinality-aware output; wiring `OverwritePolicy` + `--force` and the interactive
  conflict prompt / non-interactive `--force` error from §7.
- **Out of scope:** anything pages, config, or deprecation-related. Don't touch the
  page stub.
- **Files:** `ViewsPublisher` (+ any small multiselect helper), the group declarations,
  edits to `PublishCommand`'s `publishViews()` only, and views-flow tests.
- **Depends on:** Steps 1, 3.
- **Done when:** tests cover single/many/all selection, both group prefilters,
  cardinality-aware output strings, and the overwrite/force behavior. Suite green.
- **Spec ref:** §4, §7.

## Step 5 — Pages flow

- **Goal:** replace the `publishPage()` stub with the real pages publisher.
- **In scope:** `PagesPublisher` using the registry; destination resolution precedence
  (§5.4: `--to` → non-interactive default → interactive prompt → default);
  `--page` (picker) and `--page=NAME` (direct); the interactive select→resolve→confirm
  flow (§5.5); conflict detection for two pages resolving to the same target (§5.6);
  optional post-publish rebuild in interactive mode only (§5.7); `OverwritePolicy` +
  `--force`; `--to` validation (under `_pages/`, `.blade.php`).
- **Out of scope:** views, config, deprecation. Reuse the multiselect helper from
  Step 4 — do not fork or rewrite it.
- **Files:** `PagesPublisher`, edits to `PublishCommand`'s `publishPage()` only, and
  pages-flow tests.
- **Depends on:** Steps 1, 2, 3 (and the helper from 4).
- **Done when:** tests cover each resolution branch, `--to` validation failures,
  conflict detection, and that rebuild only offers in interactive mode. Suite green.
- **Spec ref:** §5.

## Step 6 — `hyde-config` publish tag

- **Goal:** move config publishing to `vendor:publish` as a single tag.
- **In scope:** register a `hyde-config` publish tag on the relevant service provider
  that publishes exactly the six Hyde-owned configs (hyde, docs, markdown, view, cache,
  commands) and **not** `torchlight.php`; confirm the `--config` redirect message from
  Step 3 names this tag.
- **Out of scope:** touching the `publish` command's views/pages logic; adding config
  back into `publish`; anything about Torchlight's own tag.
- **Files:** the service-provider tag registration + a test asserting the tag's file set
  (and excluding torchlight).
- **Depends on:** Step 3 (for the redirect message target).
- **Done when:** a test asserts `vendor:publish --tag=hyde-config` maps to exactly the
  six files. Suite green.
- **Spec ref:** §6.

## Step 7 — Docs cleanup

- **Goal:** align the docs with the new command surface.
- **In scope:** fix the nonexistent `php hyde publish:components` reference in
  `docs/digging-deeper/advanced-markdown.md` (→ `php hyde publish --components`);
  rewrite the publishing docs around `php hyde publish` (views + `--page`) and
  `php hyde vendor:publish --tag=hyde-config`; add a short migration note listing the
  removed legacy commands and their replacements.
- **Out of scope:** any code changes. Docs only.
- **Files:** the affected docs pages only.
- **Depends on:** Steps 4–6.
- **Done when:** no doc references a nonexistent command; the removed legacy commands
  appear only in the migration note, not the primary flow.
- **Spec ref:** §12, §8.

## Step 8 (optional) — Acceptance sweep

- **Goal:** verify nothing drifted across steps.
- **In scope:** walk §11 criteria 1–16 one by one; for any not already covered by an
  existing test, add a focused test or fix the gap. No new behavior.
- **Out of scope:** new features, refactors, scope beyond §11.
- **Files:** test files (+ minimal fixes if a criterion fails).
- **Depends on:** Steps 1–8.
- **Done when:** every §11 criterion has a passing test or a demonstrated pass.
- **Spec ref:** §11.

---

## Suggested review checklist (you, between steps)

- Does the diff touch only the files the step named?
- Did it add any flag / command / behavior not in the spec? (If yes → revert it.)
- Do the new tests actually assert the spec's wording (output strings, error text)?
- Does the full suite still pass?
- One commit, referencing the step number?
