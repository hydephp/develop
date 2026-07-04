# HydePHP v3 — `publish` Command Specification

Status: implementation-ready
Scope: reimplement publishing from scratch for v3
Owner: Emma / HydePHP core

---

## 1. Goals

Collapse the current four-command publishing surface into a small, Laravel-shaped set:

- `php hyde publish` — a **flag-driven, views-centric** command for Hyde Blade
  customizations, with an optional `--page` side path for starter pages.
- `php hyde vendor:publish` — unchanged Laravel path for providers/tags/packages.
  **Config files live here** (see §6).

Design constraints (HydePHP philosophy doc):
- Simplicity first — one entry point for the common case (views).
- Feel Laravel-y — optional flags, no positional sub-arguments.
- Curated over generic — `publish` never exposes tag/provider/path publishing.
- Power still available — advanced users drop to `vendor:publish`.
- Safe by default — never destroy user-modified files silently.

---

## 2. Command surface

```
php hyde publish [--layouts] [--components] [--all] [--page[=NAME]] [--to=PATH] [--force]
```

All flags are **optional**. With no flags, `publish` runs an interactive wizard.
Each flag simply **skips a wizard step**.

| Flag           | Meaning                                                       |
|----------------|--------------------------------------------------------------|
| `--layouts`    | Scope to layout views (skip the "what?" step)                |
| `--components` | Scope to component views                                     |
| `--all`        | Publish **all views**, skip the picker                       |
| `--page`       | Skip to the starter-page picker                              |
| `--page=NAME`  | Publish a specific starter page directly                     |
| `--to=PATH`    | Destination for a published page (**pages only**)           |
| `--force`      | Overwrite user-modified files                                |

**Hard guardrails**

- `publish` MUST reject `--tag`, `--provider`, and arbitrary source paths:
  - `php hyde publish --tag=foo` → `Use php hyde vendor:publish --tag=foo for tag/provider publishing.`
- `--layouts` and `--components` are mutually exclusive → fail with guidance.
- `--to` is valid only with `--page` → else `--to is only valid when publishing a page.`
- `--all` means **all views**; it does not apply to pages.
- Config is not a `publish` concept → `php hyde publish --config` fails and points
  to `vendor:publish --tag=hyde-config`.

---

## 3. Interactive flow (no flags)

Step 1 — what to publish:

```
What do you want to publish?

  › Views          Customize Hyde Blade layouts/components
  › A starter page Copy a homepage, 404, or other default page
  › Cancel
```

- Choosing **Views** → the views multi-select picker (§4).
- Choosing **A starter page** → the page flow (§5).

Flags skip Step 1 (and sometimes Step 2):

- `--layouts` / `--components` → jump to the views picker, prefiltered.
- `--all` → publish all views, no picker.
- `--page` → jump to the page picker.
- `--page=NAME` → publish that page, no picker.

Non-interactive with no flags → fail helpfully:

```
Nothing to publish. Try:
  php hyde publish --all
  php hyde publish --layouts
  php hyde publish --page=welcome
```

---

## 4. Views

Publishes Hyde Blade overrides to `resources/views/vendor/hyde/`.
Two declared groups (fixed 1:1 file lists — **no per-item class**):

- `layouts`    → `resources/views/vendor/hyde/layouts/*`
- `components` → `resources/views/vendor/hyde/components/*`

### Behavior

```
php hyde publish                    # wizard → Views → multi-select picker
php hyde publish --layouts          # picker prefiltered to layouts
php hyde publish --components        # picker prefiltered to components
php hyde publish --layouts --all     # all layouts, no picker
php hyde publish --all              # all views, no picker
php hyde publish --force            # overwrite modified
```

### Multi-select picker (one grouped picker — no required narrowing step)

```
Select Hyde views to publish

  [ ] All views
  Layouts
  [ ] layouts/app.blade.php
  [ ] layouts/page.blade.php
  [ ] layouts/post.blade.php

  Components
  [ ] components/markdown-heading.blade.php
  [ ] components/docs/sidebar.blade.php
```

`--layouts` / `--components` only prefilter the list. The user may select one,
many, or all files.

### Output (cardinality-aware)

```
Published 1 view to resources/views/vendor/hyde/components/markdown-heading.blade.php
Published 3 views to resources/views/vendor/hyde/layouts
Published all 42 views to resources/views/vendor/hyde
```

---

## 5. Pages (the `--page` side path)

Publishes starter/default page templates into `_pages/`.
Pages stay in `publish` (not `vendor:publish`) because a template can have
**multiple valid destinations** and carries **display metadata** — neither of
which `vendor:publish` can express.

### 5.1 `PublishablePage` value object + registry

```php
final class PublishablePage
{
    public function __construct(
        public string $key,                  // 'posts'
        public string $label,                // 'Posts feed'
        public string $description,          // short help text
        public string $source,               // stub path within the framework
        public string $defaultTarget,        // '_pages/posts.blade.php'
        /** @var array<string,string> path => human label */
        public array  $alternativeTargets = [],
        public bool   $allowCustomTarget = true,
    ) {}
}

final class PublishablePages
{
    /** @return array<string,PublishablePage> */
    public static function all(): array;
    public static function get(string $key): ?PublishablePage;
    public static function register(PublishablePage $page): void; // extension point
}
```

Only pages get a class. Views are fixed 1:1 file lists (a declared map suffices);
pages need branching destinations + metadata, and the registry lets Hyde Cloud /
plugins register their own publishable pages.

### 5.2 Initial catalog (illustrative — final set TBD by v3 page work)

| key       | default target           | alternatives                          | custom? |
|-----------|--------------------------|---------------------------------------|---------|
| `welcome` | `_pages/index.blade.php` | —                                     | yes     |
| `posts`   | `_pages/posts.blade.php` | `_pages/index.blade.php` (as homepage)| yes     |
| `blank`   | `_pages/index.blade.php` | —                                     | yes     |
| `404`     | `_pages/404.blade.php`   | —                                     | **no**  |

No dedicated "homepage" concept: setting a homepage = publishing a
homepage-capable page to `_pages/index.blade.php`.

### 5.3 Behavior

```
php hyde publish --page                       # page picker
php hyde publish --page=welcome               # publish welcome, resolve destination
php hyde publish --page=posts --to=_pages/index.blade.php
php hyde publish --page=welcome --force
```

### 5.4 Destination resolution (per selected page)

1. `--to=PATH` → use it. Must resolve under `_pages/` and end in `.blade.php`, else fail.
2. Non-interactive, no `--to` → use `defaultTarget`.
3. Interactive AND (`alternativeTargets` non-empty OR `allowCustomTarget`) → prompt:

   ```
   Where should "Posts feed" be published?

     › _pages/posts.blade.php   (default — served at /posts)
     › _pages/index.blade.php   (use as your site homepage)
     › Custom path…
   ```
4. Otherwise → `defaultTarget`.

### 5.5 Interactive page flow (select → resolve → confirm)

```
Select pages to publish

  [ ] Welcome page    → _pages/index.blade.php
  [ ] Posts feed      → _pages/posts.blade.php
  [ ] 404 page        → _pages/404.blade.php
```

Resolve ambiguous destinations (§5.4), then confirm:

```
Ready to publish:
  Welcome page → _pages/index.blade.php
  404 page     → _pages/404.blade.php

Proceed? [yes]
```

### 5.6 Destination conflict detection

If two selected pages resolve to the same target:

```
Welcome page and Blank page both target _pages/index.blade.php.
Pick one, or set --to for each.
```

### 5.7 Optional rebuild (interactive only)

After a successful page publish in interactive mode:

```
Rebuild the site now? [no]
```

Non-interactive mode NEVER rebuilds automatically.

---

## 6. Config — moved to `vendor:publish`

Config publishing is rare and has fixed destinations, so it becomes a plain tag:

```
php hyde vendor:publish --tag=hyde-config
```

`hyde-config` publishes the Hyde-owned config files:
`hyde.php`, `docs.php`, `markdown.php`, `view.php`, `cache.php`, `commands.php`.

- Torchlight is **not** included — it is obtained via Torchlight's own package tag.
- Granular tags (e.g. per-file) may still be registered for power users, but the
  single `hyde-config` tag is the documented path.
- `publish` has no config concept. `php hyde publish --config` fails and points here.

> (Exact tag name `hyde-config` is bikesheddable; keep it singular and Hyde-scoped.)

---

## 7. Overwrite policy (unified across views + pages)

Identical rule everywhere. **No historical-checksum manifest.**

| Destination state                  | Action                                   |
|------------------------------------|------------------------------------------|
| Missing                            | copy                                     |
| Byte-identical to current source   | skip (`already current`)                 |
| Exists and differs (user-modified) | require interactive confirm OR `--force` |

Interactive conflict prompt:

```
2 selected files already exist and appear modified.

  › Skip modified files
  › Overwrite modified files
  › Cancel
```

Non-interactive conflict:

```
Cannot overwrite modified files without --force:
  resources/views/vendor/hyde/layouts/app.blade.php

Run again with --force to overwrite.
```

---

## 8. Deprecated aliases (kept for v3, removed from primary docs)

| Old command                   | Maps to                                   |
|-------------------------------|-------------------------------------------|
| `publish:views [group]`       | `publish --layouts` / `--components`      |
| `publish:configs`             | `vendor:publish --tag=hyde-config`        |
| `publish:homepage [template]` | `publish --page=[template]`               |

Each prints a one-line deprecation notice, e.g.:

```
publish:configs is deprecated. Use php hyde vendor:publish --tag=hyde-config instead.
```

Aliases keep working through v3; target removal in v4.

---

## 9. Errors & guardrails (summary)

- `publish --tag=…` / `--provider=…` / raw path → redirect to `vendor:publish`.
- `publish --config` → redirect to `vendor:publish --tag=hyde-config`.
- `--layouts` + `--components` together → mutually exclusive error.
- `--to` without `--page` → `--to is only valid when publishing a page.`
- `--to` path outside `_pages/` or wrong extension → fail with a valid example.
- Non-interactive with no actionable flags → fail with usage examples.

---

## 10. Architecture summary — what to build

1. `PublishCommand` — single flag-driven command; routes to views or page flow.
2. `ViewsPublisher` — reads declared `layouts`/`components` groups.
3. `PagesPublisher` — reads `PublishablePages` registry, resolves destinations,
   detects conflicts.
4. `PublishablePage` value object + `PublishablePages` registry (extension point).
5. Shared `OverwritePolicy` service (missing / identical / modified).
6. Shared interactive multi-select + confirmation UI component.
7. Deprecated alias commands delegating to `PublishCommand` / `vendor:publish`.
8. Register the `hyde-config` publish tag on the relevant service provider.

Views stay declarative file-group lists (no per-item class); only pages use the
value-object + registry model.

---

## 11. Acceptance criteria

1. `php hyde publish` is flag-driven with no positional sub-arguments.
2. With no flags, `publish` runs the wizard (Views / A starter page).
3. `--layouts`, `--components`, `--all`, `--page`, `--page=NAME`, `--to`, `--force`
   each behave per §2 and skip the appropriate wizard step.
4. `--layouts` + `--components` together is rejected.
5. `publish` never exposes provider/tag/path publishing; `--tag`/`--provider`
   fail and point to `vendor:publish`.
6. `publish` has no config path; `--config` redirects to `vendor:publish --tag=hyde-config`.
7. `vendor:publish --tag=hyde-config` publishes the six Hyde-owned configs and
   NOT `torchlight.php`.
8. Views use one grouped multi-select picker; one/many/all all work; output is
   cardinality-aware.
9. Pages are backed by the `PublishablePages` registry.
10. A page with multiple valid targets resolves via `--to`, default, or an
    interactive destination prompt (custom paths allowed where declared).
11. `--to` is valid only with `--page`; `--all` applies only to views.
12. Two selected pages resolving to the same destination are detected before any write.
13. Overwrite policy is identical for views and pages: missing→copy,
    identical→skip, modified→confirm-or-`--force`. No checksum manifest.
14. Modified files are never overwritten without interactive confirm or `--force`.
15. Non-interactive mode never prompts and fails helpfully on ambiguity.
16. `publish:views`, `publish:configs`, `publish:homepage` still work, print a
    deprecation notice, and are absent from primary docs.

---

## 12. Docs cleanup

- Remove the reference to `php hyde publish:components` in
  `docs/digging-deeper/advanced-markdown.md` (command does not exist).
  Replace with `php hyde publish --components`.
- Rewrite the publishing docs around `php hyde publish` (views + `--page`) as the
  primary command, and `php hyde vendor:publish --tag=hyde-config` for config.
  Document `vendor:publish` as the advanced Laravel path; list deprecated aliases
  in a migration note only.
