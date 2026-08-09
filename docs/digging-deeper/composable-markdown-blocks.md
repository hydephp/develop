---
navigation:
    label: "Composable Markdown Blocks"
    priority: 28
abstract: "A deep dive into the Markdown constructs that HydePHP renders through Blade views you can publish, edit, and extend — and how to build your own."
---

# Masterclass: Composable Markdown Blocks

## Introduction

Most Markdown parsers hand you HTML and wish you good luck. HydePHP takes a different approach for a growing set of
constructs: instead of hardcoding the markup, Hyde renders them through **Blade views that ship with the framework and
that you can publish and edit**.

We call these *composable Markdown blocks*. When you write this in a Markdown file:

````markdown
```terminal
$ php hyde build
```
````

Hyde doesn't emit a `<pre>` tag from a string template buried in a PHP class. It parses the block into a node, hands
that node to `components/markdown/terminal.blade.php`, and lets the view decide what the HTML looks like. Publish that
view, and the terminal windows on your site are yours.

This page is the reference for how that system works: which blocks are composable today, what data each one passes to
its view, how the rendering pipeline fits together, and how to add your own.

>info If you are looking for the day-to-day syntax of these features rather than their internals, the [Advanced Markdown](advanced-markdown) page is the friendlier starting point.

## The Idea

A composable block has three moving parts:

1. **A syntax** — something you write in Markdown. A fenced block with a language, a prefixed blockquote, a comment on
   the first line of a code block.
2. **A processor** — the Hyde code that recognizes the syntax and gathers the data out of it.
3. **A view** — a Blade template that turns that data into HTML.

The contract between the processor and the view is just an array of variables. That is the whole trick, and it's what
makes these blocks composable: you can replace the third part without touching the first two.

```
Markdown source  ─►  Processor  ─►  view data  ─►  Blade view  ─►  HTML
```

Because the view is a normal Blade template, everything you already know applies. You can use components, conditionals,
loops, `@include`, config calls, and the full Tailwind class set inside them.

## Blocks At a Glance

| Block                                   | Syntax                        | View                               | Mechanism            |
|-----------------------------------------|-------------------------------|------------------------------------|----------------------|
| [Code blocks](#code-blocks)             | ` ```php `                    | `markdown/code-block.blade.php`    | CommonMark renderer  |
| [Terminal blocks](#terminal-blocks)     | ` ```terminal `               | `markdown/terminal.blade.php`      | CommonMark renderer  |
| [Coloured blockquotes](#coloured-blockquotes) | `>info Text`            | `colored-blockquote.blade.php`     | Markdown pre-processor |
| [Headings](#headings)                   | `## Heading`                  | `markdown-heading.blade.php`       | CommonMark renderer  |
| [Blade component blocks](#blade-component-blocks) | ` ```blade component="x" ` | *any component you write*    | Markdown pre-processor |

All view paths are relative to `resources/views/components/` in the framework package, and to
`resources/views/vendor/hyde/components/` once published into your project.

## Publishing the Views

Every built-in block view is publishable through a single command:

```terminal
php hyde publish:views components
```

Running it without arguments prompts you for a group first. Unless you choose to publish everything, it then asks which
individual files you want, so you can take just the one view you care about instead of the whole set.

Published views land in `resources/views/vendor/hyde/components/`, mirroring the framework's directory structure:

```
resources/views/vendor/hyde/components/
├── colored-blockquote.blade.php
├── markdown-heading.blade.php
└── markdown/
    ├── code-block.blade.php
    └── terminal.blade.php
```

Laravel's view finder checks the published `vendor/hyde` directory before falling back to the framework's copy, so a
published file takes precedence automatically. There is nothing to register.

>warning Publishing a view **overwrites** any existing file at the target path. If you have already customized a view, publish to a scratch directory or check your diff before confirming.

### Recompiling your CSS

Hyde's default views are built with Tailwind utility classes. The project's `tailwind.config.js` already scans
`./resources/views/**/*.blade.php`, so your published views are picked up — but Tailwind still needs to re-run to
generate any classes you add that weren't already in the compiled stylesheet:

```terminal
npm run build
```

If a class you added has no visible effect, an un-recompiled stylesheet is almost always the reason.

### Styling without publishing

You don't always need to publish. Hyde's block markup includes stable, non-utility class hooks specifically so you can
restyle blocks from your own CSS:

```css title="resources/assets/app.css"
.hyde-terminal-body {
    background-color: #1a1b26;
    color: #a9b1d6;
}
```

Publishing gives you control over *markup*; the class hooks give you control over *styling*. Reach for the lighter tool
when it does the job.

## The Rendering Pipeline

Understanding when each block is rendered explains most of the sharp edges below. Hyde converts Markdown in three
phases, orchestrated by the `MarkdownService`:

**1. Pre-processors** run against the raw Markdown string, before the parser sees it.

- `BladeBlockProcessor` — extracts ` ```blade render ` and ` ```blade component="name" ` blocks, replacing each with a
  placeholder comment so nothing downstream tries to parse their contents.
- `BladeDownProcessor` — handles single-line `[Blade]:` directives.
- `ShortcodeProcessor` — expands coloured blockquotes into rendered HTML.

**2. CommonMark conversion** parses the Markdown into an abstract syntax tree and renders it.

- `TerminalExtension` is always registered. It converts matching fenced code nodes into `TerminalBlock` nodes and
  renders them through the terminal view.
- Fenced code nodes that are not terminal blocks are wrapped in the code block view, with the `title` modifier
  resolved into the label. Your highlighter still renders the code itself.
- `HeadingRenderer` replaces CommonMark's default heading renderer with Hyde's Blade-backed one.
- Any extensions listed in `markdown.extensions` are registered here too, as is the Torchlight extension when enabled.

**3. Post-processors** run against the resulting HTML string.

- `BladeBlockProcessor` swaps the placeholders back out for the rendered Blade output.
- `DynamicMarkdownLinkProcessor` resolves source-file links to routes.

The distinction that matters: **AST-based blocks** (code blocks, terminals, headings) are structurally aware — they
only ever match real Markdown nodes. **String-based blocks** (shortcodes, Blade blocks) work on lines of text and are
cheaper to implement, but they are not fence-aware. See [Limitations](#limitations-and-gotchas).

## Code Blocks

Fenced code blocks go through a Blade view. The view doesn't render the code itself, it receives the rendered code
block markup and decides what goes around it. Syntax highlighting is unaffected: whichever highlighter your site uses
still renders the code, and the view wraps it.

Indented code blocks are not affected.

See [Advanced Markdown](advanced-markdown#code-block-titles) for the `title` modifier that labels a block.

### View contract

**View:** `hyde::components.markdown.code-block`

| Variable    | Type                        | Description                                                                          |
|-------------|-----------------------------|--------------------------------------------------------------------------------------|
| `$contents` | `string`                    | The rendered code block markup, as your highlighter produced it. Echo with `{!! !!}`. |
| `$language` | `string`/`null`             | The fence language, or `null` when the block declared none. A fence labelled with a `title` modifier instead of a language is `plaintext`. |
| `$label`    | `HtmlString`/`string`/`null`| The resolved label, or `null` when the block set none. An `HtmlString` when `markdown.allow_html` is enabled, so the label can contain links. |

>danger `$contents` is finished markup, which is why the view echoes it unescaped. Do not re-escape it with `{{ }}` (you will see markup as text).

### Class hooks

| Class                     | Targets                                    |
|---------------------------|--------------------------------------------|
| `hyde-code-block`         | The outer `<div>` wrapping the code block   |
| `hyde-code-block-label`   | The block's title label                     |

### Customization example

Say you want a header bar above the code, showing the language next to the label:

```blade title="resources/views/vendor/hyde/components/markdown/code-block.blade.php"
<div class="hyde-code-block not-prose my-4 overflow-hidden rounded">
    @if($label || $language)
        <div class="flex items-center justify-between bg-gray-800 px-4 py-2 font-mono text-xs text-gray-300">
            <span>{{ $label }}</span>
            <span class="uppercase">{{ $language }}</span>
        </div>
    @endif
    {!! $contents !!}
</div>
```

## Terminal Blocks

Add the `terminal` language to a fenced code block to render it as a terminal window.

````markdown
```terminal
$ php hyde build

 Building your static site!
 Created 12 files in 0.4 seconds
```
````

```terminal
$ php hyde build

 Building your static site!
 Created 12 files in 0.4 seconds
```

Terminal blocks are a built-in Markdown feature and do not require a Torchlight API token.

### Modifiers

Terminal blocks support an optional `title` modifier:

```
terminal [title="…"]
```

#### Window titles

Without a `title` modifier, the title bar displays `Terminal`. The modifier replaces that label.

````markdown
```terminal title="Installing Hyde"
$ composer require hyde/framework
```
````

```terminal title="Installing Hyde"
$ composer require hyde/framework
```

Double quotes are canonical, matching how attributes are written in HTML and Blade, but single quotes are also
accepted, which is useful when the title contains a double quote. The title is HTML-escaped when rendered.

Set `title=""` to omit the title bar entirely. The `title` modifier must otherwise use a quoted value with no whitespace
around the `=`. Malformed title syntax causes the build to fail rather than being silently ignored.

#### Terminal formatting tags

Terminal blocks render formatting tags as styled output. The `<info>`, `<comment>`, `<question>`, and `<error>` tags
use Hyde's terminal theme colours. You can also set foreground and background colours or text formatting with the
`fg`, `bg`, and `options` attributes, closing the tag with `</>`.

````markdown
```terminal title="Build output"
<info>Hyde was installed successfully.</info>
<fg=gray>Created 12 files in 0.4 seconds</>
```
````

```terminal title="Build output"
<info>Hyde was installed successfully.</info>
<fg=gray>Created 12 files in 0.4 seconds</>
```

See [Advanced Markdown](advanced-markdown#terminal-formatting-tags) for the available colours and options. Everything
else is escaped as usual, including unknown tags and tags that are not closed in the order they were opened.

### View contract

**View:** `hyde::components.markdown.terminal`

| Variable    | Type            | Description                                                                    |
|-------------|-----------------|--------------------------------------------------------------------------------|
| `$contents` | `string`        | The pre-rendered, already-escaped HTML for the terminal body. Echo with `{!! !!}`. |
| `$title`    | `string`/`null` | The title set by the block, or `null` when it did not set one.                  |

The renderer does the per-line work before the view is involved: it escapes the raw text, wraps `$ ` prompts in
`hyde-terminal-command`/`hyde-terminal-prompt` spans, and converts the formatter tags into coloured spans. The view
receives a single finished string.

The title is passed through as it was written, so the view is what decides both how it is displayed and what an
untitled block falls back to. The shipped view escapes it with `{{ }}` and falls back to `Terminal`.

>danger `$contents` is already escaped by the renderer, which is why the view echoes it unescaped. If you build your own view, do not re-escape it with `{{ }}` (you will see markup as text), and do not pass unescaped user content into it from elsewhere.

### Class hooks

| Class                     | Targets                                            |
|---------------------------|----------------------------------------------------|
| `hyde-terminal`           | The outer `<figure>` container                     |
| `hyde-terminal-header`    | The title bar                                      |
| `hyde-terminal-body`      | The `<pre>` output area                            |
| `hyde-terminal-command`   | A line beginning with a `$ ` prompt                |
| `hyde-terminal-prompt`    | The `$ ` prompt itself                             |
| `hyde-terminal-info`      | `<info>` output                                    |
| `hyde-terminal-comment`   | `<comment>` output                                 |
| `hyde-terminal-question`  | `<question>` output                                |
| `hyde-terminal-error`     | `<error>` output                                   |
| `hyde-terminal-fg-*`      | A foreground colour, like `hyde-terminal-fg-gray`  |
| `hyde-terminal-bg-*`      | A background colour, like `hyde-terminal-bg-red`   |
| `hyde-terminal-<option>`  | An option, like `hyde-terminal-strikethrough`      |

### Customization example

Say you want blocks that set no title of their own to show the current working directory instead of the word
"Terminal". Publish the view and edit its fallback:

```blade title="resources/views/vendor/hyde/components/markdown/terminal.blade.php"
<figure class="hyde-terminal not-prose my-4 overflow-hidden rounded-md bg-[#292D3E] text-[#A6ACCD]">
    @if (($title ?? '~/my-project') !== '')
        <figcaption class="hyde-terminal-header bg-[#212529] px-4 py-2.5 font-sans text-xs leading-none">
            <span>{{ $title ?? '~/my-project' }}</span>
        </figcaption>
    @endif
    <pre class="hyde-terminal-body m-0 overflow-x-auto rounded-none bg-[#292D3E] p-4 text-[#A6ACCD]"><code class="block whitespace-pre font-mono text-sm leading-relaxed">{!! $contents !!}</code></pre>
</figure>
```

Because it's just Blade, you could go further: add a copy button, wire the title to a config value, or drop the window
chrome entirely for a minimal look.

## Coloured Blockquotes

Append a colour name directly after the `>` character to render a coloured blockquote.

```markdown
‎> Normal Blockquote
‎>info Info Blockquote
‎>warning Warning Blockquote
‎>danger Danger Blockquote
‎>success Success Blockquote
```

<div class="docs-default-blockquotes">

> Normal Blockquote
>info Info Blockquote
>warning Warning Blockquote
>danger Danger Blockquote
>success Success Blockquote

</div>

### View contract

**View:** `hyde::components.colored-blockquote`

| Variable    | Type     | Description                                                            |
|-------------|----------|------------------------------------------------------------------------|
| `$class`    | `string` | The colour keyword: `info`, `success`, `warning`, or `danger`.          |
| `$contents` | `string` | The blockquote body, already converted from Markdown to HTML.           |

The shipped view maps the keyword to a Tailwind border colour:

```blade title="resources/views/vendor/hyde/components/colored-blockquote.blade.php"
<blockquote @class([
        'border-blue-500' => $class === 'info',
        'border-green-500' => $class === 'success',
        'border-amber-500' => $class === 'warning',
        'border-red-600' => $class === 'danger',
    ])>
    {!! $contents !!}
</blockquote>
```

Since `$class` is passed through verbatim, the view is free to do more than set a border. A common customization is to
add an icon and a background tint per colour:

```blade title="resources/views/vendor/hyde/components/colored-blockquote.blade.php"
<blockquote @class([
        'flex gap-3 border-l-4 px-4 py-3',
        'border-blue-500 bg-blue-500/10' => $class === 'info',
        'border-green-500 bg-green-500/10' => $class === 'success',
        'border-amber-500 bg-amber-500/10' => $class === 'warning',
        'border-red-600 bg-red-600/10' => $class === 'danger',
    ])>
    <span class="select-none" aria-hidden="true">@switch($class)
        @case('info') ℹ️ @break
        @case('success') ✅ @break
        @case('warning') ⚠️ @break
        @case('danger') ⛔ @break
    @endswitch</span>
    <div>{!! $contents !!}</div>
</blockquote>
```

The colour keywords themselves are fixed by the shortcode's signatures, so the view can rely on receiving one of the
four values.

### Markdown inside blockquotes

The contents are run through the Markdown converter before they reach the view, so inline formatting works as expected:

```markdown
‎>info Formatting is **supported**!
```

>info Formatting is **supported**!

Note that coloured blockquotes are single-line only — see [Limitations](#limitations-and-gotchas).

## Headings

Every Markdown heading on every page goes through a Blade view. This is what powers the permalink anchors you see when
hovering over headings in these docs, but the view is rendered whether permalinks are enabled or not.

### View contract

**View:** `hyde::components.markdown-heading`

| Variable           | Type              | Description                                                                  |
|--------------------|-------------------|------------------------------------------------------------------------------|
| `$level`           | `int`             | The heading level, 1 through 6.                                              |
| `$slot`            | `string`          | The rendered heading contents as HTML.                                       |
| `$id`              | `string`          | A unique slug derived from the contents, deduplicated across the document.    |
| `$addPermalink`    | `bool`            | Whether this heading should get a permalink anchor.                          |
| `$extraAttributes` | `array`           | Attributes parsed from the Markdown, e.g. via the Attributes extension. Empty when there are none. |

`$addPermalink` is resolved by Hyde before the view runs, based on the heading level and the `markdown.permalinks`
config for the page type being rendered. Your view can honour it, ignore it, or add its own conditions.

Because the view receives the level as data rather than being a per-level template, you can do things like give `h2`
elements a divider rule, or render a self-linking anchor around the whole heading:

```blade title="resources/views/vendor/hyde/components/markdown-heading.blade.php"
@props([
    'level' => 1,
    'id' => null,
    'extraAttributes' => [],
    'addPermalink' => config('markdown.permalinks.enabled', true),
])

@php
    $tag = 'h'.$level;

    $attributes = $attributes->merge($extraAttributes)->class(['border-b pb-2' => $level === 2]);

    if ($addPermalink) {
        $attributes = $attributes->merge(['id' => $id]);
    }
@endphp

<{{ $tag }} {{ $attributes }}>
    @if($addPermalink)
        <a href="#{{ $id }}" class="no-underline">{!! $slot !!}</a>
    @else
        {!! $slot !!}
    @endif
</{{ $tag }}>
```

>warning The heading renderer post-processes the rendered output to tidy up empty attributes and collapse newlines. Keep your markup on the conservative side — deeply nested or whitespace-sensitive structures inside the heading tag can be affected.

## Blade Component Blocks

The blocks above are Hyde's own. The `blade component="name"` fenced block is the escape hatch that lets *any* component
you write become a Markdown block:

````markdown
```blade component="alert"
---
type: warning
title: Check this
---

This content is passed to the component **slot**.
```
````

The YAML front matter becomes the component's attribute bag, and the Markdown after it is converted to HTML and passed
as the slot. Either part is optional — front matter alone, or slot content alone, both work.

Given a component at `resources/views/components/alert.blade.php`:

```blade title="resources/views/components/alert.blade.php"
@props(['type' => 'info', 'title' => null])

<div @class(['rounded border-l-4 p-4', 'border-amber-500' => $type === 'warning'])>
    @if($title)<strong>{{ $title }}</strong>@endif
    <div>{{ $slot }}</div>
</div>
```

This is the right tool when the block is specific to your project. Reach for a
[custom CommonMark extension](#writing-your-own-composable-block) instead when you want a first-class syntax — a
dedicated fence language, custom parsing, or behaviour that shouldn't depend on `markdown.enable_blade` being on.

For the full syntax, including `blade render` blocks and the security considerations of executing Blade at build time,
see [Using Blade in Markdown](advanced-markdown#using-blade-in-markdown).

## Writing Your Own Composable Block

The framework's own blocks aren't special-cased — they are ordinary CommonMark extensions registered through
configuration. You can add your own the same way. Here is a complete `callout` block, modelled directly on how Hyde
implements terminal blocks.

### 1. The node

A node is a value object holding the data you parsed out of the Markdown.

```php title="app/Markdown/CalloutBlock.php"
<?php

namespace App\Markdown;

use League\CommonMark\Node\Block\AbstractBlock;

class CalloutBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $literal,
        public readonly string $type = 'note',
    ) {
        parent::__construct();
    }
}
```

### 2. The transformer

The transformer walks the parsed document and swaps matching nodes for yours. Using the `DocumentParsedEvent` means you
work on a real syntax tree, so you never have to worry about matching text inside other code blocks.

```php title="app/Markdown/TransformCalloutBlocks.php"
<?php

namespace App\Markdown;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;

class TransformCalloutBlocks
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        $matches = [];

        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof FencedCode && strtolower($node->getInfoWords()[0] ?? '') === 'callout') {
                $matches[] = $node;
            }
        }

        // Collect first, then replace, so we don't mutate the tree while iterating it
        foreach ($matches as $node) {
            $node->replaceWith(new CalloutBlock(
                $node->getLiteral(),
                strtolower($node->getInfoWords()[1] ?? 'note'),
            ));
        }
    }
}
```

### 3. The renderer

The renderer is where the block becomes composable: it gathers view data and delegates the markup to Blade.

```php title="app/Markdown/CalloutBlockRenderer.php"
<?php

namespace App\Markdown;

use Hyde\Markdown\Models\Markdown;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

class CalloutBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof CalloutBlock) {
            throw new \InvalidArgumentException('Incompatible node type: '.$node::class);
        }

        return view('components.callout', [
            'type' => $node->type,
            'contents' => Markdown::render($node->literal),
        ])->render();
    }
}
```

Note that `Markdown::render()` starts a nested conversion, which is what lets the callout body contain Markdown. If your
block's content should be treated as literal text instead, escape it with `e()` and skip the nested render — that is
what the terminal renderer does.

### 4. The extension

The extension wires the two together.

```php title="app/Markdown/CalloutExtension.php"
<?php

namespace App\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;

class CalloutExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addEventListener(DocumentParsedEvent::class, new TransformCalloutBlocks(), 100)
            ->addRenderer(CalloutBlock::class, new CalloutBlockRenderer());
    }
}
```

### 5. The view

```blade title="resources/views/components/callout.blade.php"
<aside @class([
        'my-callout my-4 rounded border-l-4 p-4',
        'border-blue-500' => $type === 'note',
        'border-amber-500' => $type === 'tip',
    ])>
    {!! $contents !!}
</aside>
```

### 6. Register it

```php title="config/markdown.php"
'extensions' => [
    \League\CommonMark\Extension\GithubFlavoredMarkdownExtension::class,
    \League\CommonMark\Extension\Attributes\AttributesExtension::class,
    \App\Markdown\CalloutExtension::class, // [tl! add]
],
```

That's it. You can now write:

````markdown
```callout tip
Blocks you build this way are **composable** in exactly the same way the built-in ones are.
```
````

### Design notes

A few conventions worth following, drawn from how the built-in blocks are written:

- **Escape in the renderer, echo raw in the view.** Decide once, in PHP, whether a value is trusted HTML or user text.
  A view that receives a mix of both is a view that eventually renders an injection.
- **Pass data, not markup.** Give the view the block's *type*, *level*, or *path* rather than a pre-baked class string.
  It costs nothing and it's the difference between a view that can be restyled and one that can only be replaced.
- **Add stable class hooks.** A non-utility class like `my-callout` on the root element lets people restyle your block
  without publishing anything.
- **Use `not-prose` where appropriate.** Rendered Markdown lives inside a Tailwind Typography `.prose` container. Blocks
  with their own visual design usually want to opt out of the prose styles.
- **Prefer AST-based extensions over string pre-processors.** They only match real Markdown nodes, so they can't
  accidentally fire inside a code sample.

## Limitations and Gotchas

### Pre-processors are not fence-aware

Coloured blockquotes are expanded line by line, before the Markdown parser runs. That means a line starting with
`>info` is expanded **even inside a fenced code block**. This is why the examples on this page prefix such lines with an
invisible `U+200E LEFT-TO-RIGHT MARK` character — it's the standard workaround for documenting the syntax without
triggering it.

Blocks implemented as CommonMark extensions, like code and terminal blocks, do not have this problem.

### Coloured blockquotes are single-line

The shortcode operates on one line at a time, so a coloured blockquote cannot span multiple lines. Inline Markdown works
fine; block-level Markdown inside the blockquote does not.

### Blade blocks depend on configuration

`blade render` and `blade component="name"` blocks are gated behind `markdown.enable_blade`. If your site builds Markdown
from outside your trusted review process, that setting should be off — and any block you built on top of Blade blocks
will stop rendering with it. Custom CommonMark extensions are unaffected.

### Publishing is a snapshot

A published view is a copy, frozen at the version you published it from. Framework updates that change the default
markup, add a class hook, or pass a new variable will not reach it. Re-run `php hyde publish:views components` after
major upgrades and diff your customizations against the new defaults.

### Your highlighter decides what `$contents` looks like

Torchlight and CommonMark's default renderer produce different markup for the same block, and a third-party
highlighter produces something different again. Your view receives whichever one as `$contents`, so a custom view that
digs into that markup expecting one structure will look wrong under another. Test both if your site toggles Torchlight.

## See Also

- [Advanced Markdown](advanced-markdown) — the syntax reference for these features
- [Advanced Customization](advanced-customization) — customizing Hyde beyond Markdown
- [Customization](customization#markdown-configuration) — the full `config/markdown.php` reference
- [CommonMark documentation](https://commonmark.thephpleague.com/) — the parser Hyde builds on
