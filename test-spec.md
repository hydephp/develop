# Test Specification — Blade Blocks Markdown Processor

Spec for the test suite covering the new **Blade Blocks** feature
(`BladeBlockProcessor` + `BladeBlockExtractor` + `BladeBlock` / `BladeRenderBlock` /
`BladeComponentBlock`), registered behind `markdown.enable_blade_blocks`.

## Philosophy & strategy

- **Feature tests drive coverage.** They exercise the real pipeline through
  `Hyde\Markdown\Models\Markdown::render()` (pre-processors → CommonMark → post-processors),
  never the private internals. The goal is that the feature file alone gives ~100% line/branch
  coverage.
- **Unit tests complement, never duplicate.** They pin fence-parsing edge cases and the
  signature/`compile()` contract using the extractor's public `handle()` seam and a stub
  subclass — no Blade rendering, no app container.
- **We do not test implementation.** No assertions on exact signature hashes, sequence numbers,
  or internal method calls. Tests assert observable behavior and the stable public contract.

## Public API surface (what to lock as regression, what stays free to refactor)

Treat these as the **stable contract** and regression-test them:

- The config flag **`markdown.enable_blade_blocks`**, default **`false`** (opt-in).
- The **syntaxes**: `` ```blade `` (highlighted sample, not executed), `` ```blade render ``,
  `` ```blade component(name) ``, and the invalid-syntax → exception behavior.
- The **output wrapper**: every executed block is wrapped in
  `<div class="blade-block not-prose">…</div>`.
- The feature works on **any** Markdown page (page, post, docs) and via standalone
  `Markdown::render()`.

Treat these as **internal** — test only via behavior, keep free to rename/move:

- `Hyde\Markdown\Processing\BladeBlockProcessor`, `…\BladeBlocks\*`, the signature scheme,
  the static registry, the `handle()` return shape.

## Base classes & locations

- **Feature file:** `packages/framework/tests/Feature/BladeBlocksTest.php`
  namespace `Hyde\Framework\Testing\Feature`, extends `Hyde\Testing\TestCase` (full app).
- **Unit files:** `packages/framework/tests/Unit/BladeBlockExtractorTest.php` and
  `packages/framework/tests/Unit/BladeBlockTest.php`,
  namespace `Hyde\Framework\Testing\Unit`, extend `Hyde\Testing\UnitTestCase`.

`#[CoversClass]` on the feature file: `BladeBlockProcessor`, `BladeBlock`, `BladeRenderBlock`,
`BladeComponentBlock`, `BladeBlockExtractor`.

---

## Decisions to make before implementing (see intro)

| # | Issue | Recommended action | Test impact |
|---|-------|--------------------|-------------|
| 1 | `$page` not passed to any block view (contradicts `spec.md`) | Pass `'page' => Render::getPage()` in both `render()` methods | `testPageVariableIsAvailableInBladeRenderBlock` currently fails |
| 2 | `$name === ''` guard is unreachable dead code | Delete the guard | Removes the only line feature tests can't cover |
| 3 | Static registry leaks if a valid block precedes a throwing one | Make extraction atomic (register only after full parse) | `testStaticStateIsNotLeakedAfterFailedRender` guards this |
| 4 | Front-matter data exposed as `$attributes` bag, not `$foo` variables (contradicts `spec.md`) | Decide & document; extract props or amend the doc | `testComponentDataIsAvailableAsViewVariables` locks the choice |
| 5 | Component names can't contain whitespace (`preg_split`) — `component(foo bar)` throws | Probably fine; document | `testComponentNameWithWhitespaceThrows` documents it |

---

## Test fixtures & harness (feature file)

In `setUp()`:

- `parent::setUp();`
- `config(['markdown.enable_blade_blocks' => true]);` (individual tests that need it off set it
  back to `false` explicitly).
- Create two component fixtures under `resources/views/components/` (use the `file()`
  helper / write to `resource_path`, cleaned up by `CreatesTemporaryFiles` in `tearDown`):
  - **`blade-block-fixture.blade.php`** →
    `data=[{{ $attributes->get('foo') }}] slot=[{{ $slot }}]`
    (reads data via the attribute bag — robust, never triggers undefined-variable errors, works
    for empty data).
  - **`blade-block-props-fixture.blade.php`** →
    `value=[{{ $foo ?? 'UNDEFINED' }}]`
    (used only by the Decision #4 test that verifies the `spec.md` "variables passed to view"
    promise).

Add a small private helper `render(string $markdown): string` that returns
`Markdown::render($markdown)` for readability. `MarkdownService` builds a fresh converter per
call, so runtime `config()` changes are honored.

---

## Feature tests — `BladeBlocksTest`

Each entry: **method name** — behavior it asserts.

### Feature toggle

- **`testFeatureIsDisabledByDefault`**
  Set the flag to `false`. Render `` ```blade render `` containing `{{ "Hello World!" }}`.
  Assert the output does **not** contain `<div class="blade-block`, and that the literal source
  (`{{`) survives inside a code block — i.e. the block was left untouched and rendered as an
  ordinary fenced code sample. Covers the "processor not registered" path.

- **`testPlainMarkdownIsUnaffectedWhenFeatureEnabled`**
  Flag on. Render ordinary Markdown with no blade fences (`# Title\n\nBody`). Assert normal HTML
  output and no `blade-block` div. Covers `postprocess()` with an empty replacement map and the
  no-blocks `preprocess()` path.

### `blade render`

- **`testBladeRenderBlockIsExecuted`**
  Flag on. Render `` ```blade render `` with body `{{ "Hello World!" }}`.
  Assert output contains `<div class="blade-block not-prose">Hello World!</div>`.
  Core happy path: extractor → `makeBlock` render branch → `BladeRenderBlock::render()` →
  `BladeBlock::compile()` → processor pre/post.

- **`testBladeRenderBlockExecutesArbitraryBladeAndPhp`**
  Body: `@php($world = 'world')` + blank line + `{{ "Hello $world" }}`.
  Assert the div contains `Hello world`. Confirms full Blade evaluation, not just echo.

- **`testPageVariableIsAvailableInBladeRenderBlock`** *(Decision #1 — currently fails)*
  Compile a real page whose content is `` ```blade render `` with body
  `{{ $page->identifier ?? 'NO-PAGE' }}`. Assert the identifier appears. Marks the `spec.md`
  promise. If left failing, `@group known-gap` or skip with a note.

### Bare `blade` and ordinary code blocks

- **`testBareBladeBlockIsNotExecuted`**
  `` ```blade `` with body `{{ "This is not executed" }}`. Assert the literal `{{` survives and
  no `blade-block` div is produced. Covers `makeBlock` `count === 1` → `null`, verbatim re-emit,
  and CommonMark rendering it as a highlighted sample.

- **`testOrdinaryCodeBlocksAreUnaffected`**
  A `` ```php `` block with `<h1>Hello</h1>`. Assert it renders as a normal (escaped) code block,
  unchanged. Covers `makeBlock` `tokens[0] !== 'blade'` → `null`.

### `blade component(name)`

- **`testComponentBlockWithBareYamlData`**
  `` ```blade component(blade-block-fixture) `` with body `foo: bar` (no triple dashes).
  Assert output contains `data=[bar]` and `slot=[]`. Covers `render()` empty-slot branch,
  `parse()` bare-YAML branch, `is_array($matter) === true`.

- **`testComponentBlockWithFrontMatterAndMarkdownSlot`**
  Body: `---\nfoo: bar\n---\n\n# Heading\n\nSome **bold** text`.
  Assert `data=[bar]`, and that the slot rendered as Markdown: contains `<h1>Heading</h1>` and
  `<strong>bold</strong>`. Covers `render()` filled-slot branch, `parse()` front-matter branch,
  `Markdown::render()` of the slot, and (standalone render) the `pageClass()` null branch.

- **`testComponentWithNonMappingBodyYieldsNoData`**
  `` ```blade component(blade-block-fixture) `` with body `just some text` (no colon, no dashes).
  `Yaml::parse()` returns a string → not an array. Assert `data=[]` and `slot=[]`.
  Covers the `is_array($matter) === false` branch.

- **`testComponentDataIsAvailableAsViewVariables`** *(Decision #4)*
  `` ```blade component(blade-block-props-fixture) `` with body `foo: bar`.
  Assert output is `value=[bar]` (not `value=[UNDEFINED]`). Locks whether front-matter keys are
  extracted as `$foo`. Will fail if only `$attributes` exposure is implemented.

### Error handling

- **`testUnknownDirectiveThrows`**
  `` ```blade foo `` → expect `InvalidArgumentException`, message about expecting
  `` ```blade render `` or `` ```blade component(...) ``. Covers the generic `else` throw.

- **`testComponentWithoutParenthesesThrows`**
  `` ```blade component `` → expect `InvalidArgumentException`. (Same throw line; kept as a
  distinct named regression for a distinct user mistake.)

- **`testComponentWithEmptyParenthesesThrows`**
  `` ```blade component() `` → expect `InvalidArgumentException`. (Regex fails to match → generic
  throw. Note: this does **not** reach the `$name === ''` guard — see Decision #2.)

- **`testComponentNameWithWhitespaceThrows`** *(Decision #5)*
  `` ```blade component(foo bar) `` → expect `InvalidArgumentException`. Documents that
  whitespace in names is unsupported because of `preg_split`.

### Fence parsing behavior (through the real pipeline)

- **`testFourBacktickFenceEscapesBladeBlock`**
  A four-backtick fence wrapping an inner `` ```blade render `` block. Assert the inner block is
  shown **literally** (its `{{ … }}` survives) and is not executed. Covers the closing-fence
  length rule (`>= $length`) and the empty-info → `null` re-emit.

- **`testTildeFencedBladeBlockIsExecuted`**
  `~~~blade render` … `~~~` with body `{{ "Tilde" }}`. Assert `blade-block` div with `Tilde`.
  Covers the `char !== '`'` branch and the skipped backtick-in-info check.

- **`testBacktickInInfoStringIsNotTreatedAsFence`**
  A line whose info string contains a backtick (e.g. `` ```foo`bar ``). Assert it is left as
  ordinary Markdown (not extracted, not executed). Covers the
  `$char === '`' && str_contains($info, '`')` passthrough.

- **`testIndentedBladeRenderBlockIsExecuted`**
  A `` ```blade render `` fence indented by up to 3 spaces, body `{{ "Indented" }}` indented to
  match. Assert `blade-block` div with `Indented` (body correctly dedented). Covers `dedent()`
  with `indent > 0`.

- **`testUnterminatedBladeBlockIsStillExecuted`**
  `` ```blade render `` opened with **no** closing fence before EOF, body `{{ "EOF" }}`.
  Assert the block still executes. Covers `$closed === false` + block-made + resume-at-EOF.

- **`testUnterminatedOrdinaryFenceIsPreserved`**
  `` ```php `` opened with no closing fence. Assert it is re-emitted verbatim with no synthetic
  closing line. Covers `$closed === false` + `null` block + no-closing-line-append.

### Independence, reentrancy, integration

- **`testEqualBladeBlocksAreCompiledIndependently`**
  Two identical `` ```blade render `` blocks, each `{{ "X" }}`. Assert
  `substr_count($html, '<div class="blade-block not-prose">X</div>') === 2`. If signatures
  collided, one would be lost. Covers per-block signature uniqueness via the sequence.

- **`testBladeBlocksNestedInComponentSlotAreProcessed`**
  A `` ```blade component(blade-block-fixture) `` whose front-matter slot body contains a nested
  `` ```blade render `` block. Assert the nested block is executed inside the slot. Covers the
  reentrancy path (snapshot + clear in `postprocess()`, nested `Markdown::render()`).

- **`testComponentSlotUsesPageClassWhenCompiledWithinPage`**
  Write a `_pages/*.md` page containing a component-with-slot block; compile it via
  `StaticPageBuilder::handle(...)` (mirror `MarkdownPageTest`). Assert the built HTML contains
  the rendered slot. During page compile `Render::getPage()` is set, so this covers the
  `pageClass()` **non-null** branch (complementing the standalone null branch above).

- **`testFeatureWorksAcrossPageTypes`**
  Parametrize (or repeat) over `MarkdownPage`, `MarkdownPost`, `DocumentationPage`: each with a
  `blade render` block, compiled and asserted to contain the `blade-block` div. Regression lock
  that the feature is page-type agnostic (public contract).

- **`testBladeDownAndBladeBlocksCanBeEnabledTogether`**
  Enable both `markdown.enable_blade` and `markdown.enable_blade_blocks`. Render a document with
  a `[Blade]:` line **and** a `` ```blade render `` block. Assert both are processed. Covers
  the co-registration/ordering in `SetsUpMarkdownConverter`.

- **`testStaticStateIsNotLeakedAfterFailedRender`** *(Decision #3)*
  Render a document with a valid `blade render` block followed by an invalid `` ```blade foo ``
  (expect the exception, caught). Then render a clean document and assert its output contains no
  stray `blade-block` div from the previous failed render. Guards the static registry.

---

## Unit tests — `BladeBlockExtractorTest`

Extends `UnitTestCase` (no `$needs*` flags required; `handle()` constructs blocks but does not
render). `#[CoversClass(BladeBlockExtractor::class)]`. All assertions are on the returned
`[array<signature, BladeBlock>, string]` — class of each block, count, distinct keys, and the
rewritten Markdown (presence of the `<!-- HYDE[BladeBlock]… -->` comment, absence of the block
body). Never assert exact hash values.

- **`testReturnsEmptyBlocksAndUnchangedMarkdownWhenNoFences`** — plain text → `[]` and identical
  Markdown returned.
- **`testLeavesOrdinaryCodeBlocksUntouched`** — a `` ```php `` block → no blocks; Markdown
  unchanged.
- **`testLeavesBareBladeBlockUntouched`** — `` ```blade `` → no blocks; Markdown unchanged.
- **`testExtractsRenderBlock`** — `` ```blade render `` → exactly one block, `instanceof
  BladeRenderBlock`.
- **`testExtractsComponentBlock`** — `` ```blade component(x) `` → one `BladeComponentBlock`.
- **`testReplacesExtractedBlockWithSignatureComment`** — after `handle()`, the returned Markdown
  contains a standalone `<!-- HYDE[BladeBlock]` comment and no longer contains the original block
  body.
- **`testEqualBlocksProduceDistinctSignatures`** — two identical `` ```blade render `` blocks →
  two entries with distinct keys.
- **`testCarriageReturnLineEndingsAreNormalized`** — input using `\r\n` around a blade block →
  still extracted. Covers the `str_replace(["\r\n","\r"], "\n", …)`.
- **`testTildeFenceIsSupported`** — `~~~blade render` → one `BladeRenderBlock`.
- **`testBacktickInInfoStringIsIgnored`** — a `` ```foo`bar `` line → not extracted; Markdown
  unchanged.
- **`testFourBacktickFenceDoesNotExtractInnerTripleFence`** — four-backtick fence containing an
  inner `` ```blade render `` → no blocks extracted (outer empty-info block ignored, inner
  preserved).
- **`testLongerClosingFenceRequiredToClose`** — `` ```blade render `` closed only by a shorter
  run of the same char earlier → treated as still open; the real (equal-or-longer) fence closes
  it. Documents `strlen($m[1]) >= $length`.
- **`testClosingFenceMustMatchOpeningCharacter`** — open with ```` ``` ````, "close" with `~~~` →
  not closed; body runs to EOF (unterminated). One block still produced.
- **`testUnterminatedFenceExtractsToEndOfInput`** — `` ```blade render `` with no closing fence →
  one block; the returned Markdown has the signature and no trailing fence line.
- **`testThrowsOnUnknownDirective`** — `` ```blade foo `` → `InvalidArgumentException`.
- **`testThrowsOnComponentWithoutName`** — `` ```blade component `` → `InvalidArgumentException`.
- **`testThrowsOnComponentWithEmptyParentheses`** — `` ```blade component() `` →
  `InvalidArgumentException`.

> Note: `dedent()` with `indent > 0` and the precise dedented content are best asserted at the
> feature level (`testIndentedBladeRenderBlockIsExecuted`), since `BladeBlock::$content` is
> protected. The unit test only needs to confirm the indented fence is still recognized.

---

## Unit tests — `BladeBlockTest`

Extends `UnitTestCase`. `#[CoversClass(BladeBlock::class)]`. Define a private stub subclass at
the bottom of the file (mirroring how the existing suite defines helper classes) whose
`render()` returns a fixed literal (e.g. `'STUB'`) — no Blade, no app.

- **`testSignatureIsAnHtmlComment`** — a stub block's `signature` matches
  `/^<!-- HYDE\[BladeBlock\][0-9a-f]{64} -->$/`.
- **`testCompileWrapsRenderOutputInBladeBlockDiv`** — `compile()` returns
  `<div class="blade-block not-prose">STUB</div>`.
- **`testIdenticalContentProducesDistinctSignatures`** — two stubs with identical content have
  different signatures (the sequence disambiguates). Do not assert the numeric sequence.
- **`testDifferentContentProducesDistinctSignatures`** — two stubs with different content have
  different signatures.
- **`testComponentBlockAddsNameToHashableContent`** *(optional)* — two `BladeComponentBlock`s
  with identical body but different names produce different signatures. Confirms the
  `getHashableContent()` override contributes the name. (Constructs the real component block; no
  render, so no app needed.)

---

## Coverage matrix (feature tests → code)

Demonstrates the feature file alone covers every executable branch except the dead guard.

| Code location / branch | Covered by feature test |
|---|---|
| `SetsUpMarkdownConverter` register (flag on / off) | `testBladeRenderBlockIsExecuted` / `testFeatureIsDisabledByDefault` |
| Both processors co-registered | `testBladeDownAndBladeBlocksCanBeEnabledTogether` |
| `BladeBlockProcessor::preprocess()` (blocks / none) | render tests / `testPlainMarkdownIsUnaffectedWhenFeatureEnabled` |
| `BladeBlockProcessor::postprocess()` (map / empty map) | render tests / `testPlainMarkdownIsUnaffectedWhenFeatureEnabled` |
| `BladeBlock::__construct`, `compile`, `getHashableContent` | `testBladeRenderBlockIsExecuted` |
| `BladeBlock` component `getHashableContent` override | `testComponentBlockWithBareYamlData` |
| `BladeRenderBlock::render` | `testBladeRenderBlockIsExecuted` |
| `BladeComponentBlock::render` filled slot / empty slot | `testComponentBlockWithFrontMatterAndMarkdownSlot` / `testComponentBlockWithBareYamlData` |
| `BladeComponentBlock::parse` front-matter / bare-YAML | `…WithFrontMatterAndMarkdownSlot` / `…WithBareYamlData` |
| `BladeComponentBlock::parse` `is_array` false | `testComponentWithNonMappingBodyYieldsNoData` |
| `hasFrontMatter` true / false | `…WithFrontMatterAndMarkdownSlot` / `…WithBareYamlData` |
| `pageClass()` non-null / null | `testComponentSlotUsesPageClassWhenCompiledWithinPage` / `…WithFrontMatterAndMarkdownSlot` |
| Extractor: not-a-fence passthrough | every test with prose |
| Extractor: backtick-in-info passthrough | `testBacktickInInfoStringIsNotTreatedAsFence` |
| Extractor: tilde branch | `testTildeFencedBladeBlockIsExecuted` |
| Extractor: closed / unterminated | render tests / `testUnterminatedBladeBlockIsStillExecuted` |
| Extractor: null block + closing line / + no closing line | `testOrdinaryCodeBlocksAreUnaffected` / `testUnterminatedOrdinaryFenceIsPreserved` |
| Extractor: closing length rule | `testFourBacktickFenceEscapesBladeBlock` |
| `dedent()` indent 0 / >0 | most tests / `testIndentedBladeRenderBlockIsExecuted` |
| `makeBlock` non-blade → null | `testOrdinaryCodeBlocksAreUnaffected` |
| `makeBlock` bare blade → null | `testBareBladeBlockIsNotExecuted` |
| `makeBlock` render / component | render / component tests |
| `makeBlock` generic throw | `testUnknownDirectiveThrows` (+ the other throw tests) |
| `makeBlock` `$name === ''` throw | **UNREACHABLE — Decision #2** |

The single uncovered line is the dead `$name === ''` guard. Remove it (recommended) for 100%,
or exclude it with `@codeCoverageIgnore`.
