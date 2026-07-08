# Refactor Plan: Hybrid Pages → Blade Blocks Markdown Processor

## Goal

Turn the `.hmd` **HybridPage** feature into a general **Markdown processor** that works on
*every* Markdown page, as a sister feature to `BladeDownProcessor` (which may later be
deprecated). We keep the block-oriented OOP design exactly as engineered — an abstract
block with `render()`/`compile()`, a `render` variant, and a `component(name)` variant,
plus the fence-scanning extractor — and simply relocate it from a bespoke page-compiler
into the standard Markdown pre/post-processing pipeline.

No new page type. No `.hmd` extension. Any `.md`, blog post, or docs page can use
```` ```blade render ```` and ```` ```blade component(name) ```` when the feature is enabled.

## Why this maps cleanly onto the processor pipeline

`MarkdownService::parse()` already runs: **pre-processors → CommonMark convert → post-processors**.
Our feature is that exact shape:

- **Pre-process:** scan the raw Markdown for fenced blade blocks, pull each into a block
  object, and leave behind its `<!-- HYDE[...] -->` signature comment. (This is today's
  `HybridPageBlockExtractor::handle()`.)
- **Convert:** CommonMark passes the signature comment through untouched (same guarantee
  `BladeDownProcessor` already relies on).
- **Post-process:** replace each signature comment with `$block->compile()`. (This is
  today's `HybridPageCompiler::injectCompiledBlocks()`.)

So `BladeDownProcessor` is the precedent: one class implementing both
`MarkdownPreProcessorContract` and `MarkdownPostProcessorContract`, registered in
`SetsUpMarkdownConverter` behind a config flag.

## The one architectural change: getting the page

Blocks currently receive a `HybridPage $page` in their constructor and use it to (a) expose
`$page` to the rendered Blade and (b) pick the page class for compiling a component's
Markdown slot. A static processor has no page argument.

**Solution:** obtain it from render context via `Render::getPage()`
(`Hyde\Support\Facades\Render`). The kernel calls `Render::setPage($page)` before a page is
compiled (`ManagesViewData::shareViewData()`), so during Markdown compilation the current
page — and thus `Render::getPage()::class` — is available. Blocks resolve it lazily at
render time (inside `render()`), not at construction (which now happens in pre-processing).

Consequences:
- Blocks no longer depend on the concrete `HybridPage`; they decouple from any specific page
  type. This *is* the generalization.
- `Render::getPage()` may be `null` when Markdown is rendered outside a page compile (e.g.
  standalone `Markdown::render()` in tests). Blocks must tolerate a null page: pass
  `'page' => Render::getPage()` (Blade sees `null`) and fall back to a null page class for
  slot rendering. This is strictly more robust than today.

## State handoff between pre and post

The rich block objects created in `preprocess()` must survive to `postprocess()`.
`BladeDownProcessor` sidesteps this by encoding everything into the comment, but our blocks
are multi-line and carry behavior, so we keep them as objects in a **static registry keyed
by signature**:

- `preprocess()` stores `static::$blocks[$signature] = $block` and returns markdown with the
  signatures inlined.
- `postprocess()` iterates the registry; for each signature present in the given HTML, it
  `unset()`s it from the registry *before* calling `$block->compile()`, then replaces it.

Signatures are already globally unique (content hash + monotonic sequence, see
`HybridPageBlock::getHashableContent()`), so the registry is collision-free.

**Reentrancy note (important):** a component block's `compile()` calls
`Markdown::render($slot)`, which spins up a *nested* `MarkdownService::parse()` that runs this
same processor on the slot. The registry is shared and static, so:
- Consume-only-what's-in-this-HTML + `unset` before `compile()` keeps the outer loop from
  touching inner blocks.
- `foreach` over the array iterates a snapshot, so entries the nested run adds/removes don't
  disturb the outer iteration.
- Because signatures are unique, nested and outer blocks never alias.

This is a genuine behavior change worth calling out: blade blocks nested *inside* a component
Markdown slot will now be processed (today's out-of-pipeline compiler renders slots without
hybrid processing). This is arguably more correct and consistent, but should be a conscious
decision (and eventually a test).

## Naming (recommendation — final call is yours)

Move from `Hyde\Pages\HybridPages\*` to live beside `BladeDownProcessor` in
`Hyde\Markdown\Processing\*`:

| Today (`Hyde\Pages\HybridPages`) | Proposed (`Hyde\Markdown\Processing`) | Role |
| --- | --- | --- |
| `HybridPageCompiler`      | `BladeBlockProcessor`  | pre/post processor entry point (replaces the compiler) |
| `HybridPageBlockExtractor`| `BladeBlockExtractor`  | fence scanner, unchanged logic |
| `HybridPageBlock` (abstract) | `BladeBlock`        | abstract block: `signature`, `compile()`, `render()` |
| `BladePageBlock`          | `BladeRenderBlock`     | ```` ```blade render ```` → `Blade::render()` |
| `ComponentPageBlock`      | `BladeComponentBlock`  | ```` ```blade component(name) ```` |

If you'd rather preserve the "Hybrid" vocabulary, keep the class names and just move the
namespace; the plan is identical either way. I lean toward the `BladeBlock*` names because
the feature is no longer tied to a "page."

## Config / enablement

Follow `BladeDownProcessor`'s pattern in `SetsUpMarkdownConverter`:

```php
$this->registerPreProcessor(BladeBlockProcessor::class, Config::getBool('markdown.enable_blade_blocks', false));
// ...
$this->registerPostProcessor(BladeBlockProcessor::class, Config::getBool('markdown.enable_blade_blocks', false));
```

- **Recommend a new flag** `markdown.enable_blade_blocks` (default `false`, opt-in, matching
  `enable_blade`). A distinct flag lets this live and be toggled independently of BladeDown,
  which matters given you may deprecate BladeDown separately. Add it to `config/markdown.php`
  next to `enable_blade` with a short doc comment.
- Alternative: reuse `markdown.enable_blade`. Simpler, but couples the two features'
  lifecycles — not recommended.

**Pre-processor ordering:** register the extractor early (before `CodeblockFilepathProcessor`)
so fenced blade blocks are pulled out before any other processor inspects code fences. The
extractor already ignores non-blade fences, so it is otherwise order-insensitive.

## Step-by-step

1. **Create `BladeBlock` (abstract)** in `Hyde\Markdown\Processing` — same as
   `HybridPageBlock` but drop the `HybridPage $page` constructor property/param. Keep
   `signature`, the static `$sequence`, `compile()` (the `hybrid-container not-prose` wrapper),
   and `getHashableContent()`. `abstract protected function render(): string;` stays.
   - Decide the wrapper div class name: keep `hybrid-container` or rename to e.g.
     `blade-block` for consistency with the new naming. (Cosmetic; your call. Note it changes
     any CSS hooks in the spec.)

2. **`BladeRenderBlock`** (was `BladePageBlock`): `render()` returns
   `Blade::render($this->content, ['page' => Render::getPage()])`.
   - Improvement over today: the current `BladePageBlock` passes no data, so `$page` isn't
     available inside a `blade render` block despite the spec promising it. This fixes that.

3. **`BladeComponentBlock`** (was `ComponentPageBlock`): unchanged parsing (front matter vs
   bare YAML, `hasFrontMatter`, `getHashableContent` appends `$name`). In `render()`:
   - slot: `Markdown::render($this->body, Render::getPage()?->getClass() /* or ::class */)` —
     resolve the page class from render context, null-safe.
   - `'page' => Render::getPage()` in the Blade data.
   - Confirm the correct accessor for a page's class-string (e.g. `$page::class`).

4. **`BladeBlockExtractor`** (was `HybridPageBlockExtractor`): identical fence-scanning and
   `makeBlock()` logic (including the new `blade` / `blade render` / `blade component(name)`
   info-string rules already implemented). Only change: constructor no longer takes a page;
   `makeBlock()` instantiates blocks without a page argument. `handle(string $markdown)`
   returns `[array<signature,BladeBlock>, string]` as before.

5. **`BladeBlockProcessor`** (replaces `HybridPageCompiler`): implements
   `MarkdownPreProcessorContract` + `MarkdownPostProcessorContract`.
   - `protected static array $blocks = [];`
   - `public static function preprocess(string $markdown): string` → runs
     `BladeBlockExtractor`, merges returned blocks into `static::$blocks`, returns the
     rewritten markdown.
   - `public static function postprocess(string $html): string` → for each
     `static::$blocks` whose signature is in `$html`: `unset` it, then
     `str_replace($signature, $block->compile(), $html)`. Return `$html`.
   - Mirror `BladeDownProcessor`'s file/docblock style.

6. **Wire into the pipeline:** register pre + post in
   `SetsUpMarkdownConverter::registerPreProcessors()` / `registerPostProcessors()` behind the
   new config flag (see Config section). Add `markdown.enable_blade_blocks` to
   `config/markdown.php`.

7. **Delete the page type and its wiring:**
   - Remove `packages/framework/src/Pages/HybridPage.php`.
   - Remove the whole `packages/framework/src/Pages/HybridPages/` directory (its classes are
     migrated in steps 1–5).
   - Revert the `HybridPage::class => true` entry + `use` in
     `HydeCoreExtension::getPageClasses()`.
   - Revert the "Register directories for HybridPage" TODO in `HydeServiceProvider`.
   - Drop the `.hmd` extension entirely (no longer a page type).

8. **Docs / demo page:** rename `_pages/spec.hmd` → `_pages/spec.md` (or move it under the
   docs site). Because it's now an ordinary Markdown page, the four-backtick escaping it
   already uses keeps the examples visible. Add a note that the feature requires
   `markdown.enable_blade_blocks` to be enabled. Update wording that refers to a "Hybrid
   page"/".hmd file" to "any Markdown page." Keep the `component-name` demo component at
   `resources/views/components/component-name.blade.php` (or relocate to a test fixture).

9. **Sweep for references:** grep for `HybridPage`, `HybridPages`, `.hmd`, `hybrid-container`
   and update/remove. Confirm nothing else imports the deleted classes.

## Behavior/edge cases to keep in mind (future tests)

- `Render::getPage()` null path (standalone Markdown render) → `$page` is null, no crash.
- Nested blade blocks inside a component slot now render (reentrancy) — decide if intended.
- Equal blocks still compile independently (the `$sequence` in the hash already guarantees
  this; it must remain a per-process monotonic counter).
- Signature comment survival through CommonMark with `allow_html` both on and off (comments
  are permitted regardless, as BladeDown demonstrates).
- Processor is skipped entirely when the flag is off (no signatures emitted, zero overhead).
- Interaction/ordering with `BladeDownProcessor` when *both* are enabled on the same page.

## Out of scope (explicitly)

- Deprecating `BladeDownProcessor` — left for later, per your note.
- Tests — none exist yet on this branch; the edge-case list above is the eventual test
  checklist.
