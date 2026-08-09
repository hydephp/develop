@php($hasLabel = isset($label) && (string) $label !== '')
@php($labelStyle = $hasLabel ? \Hyde\Facades\Config::getString('markdown.code_block_label_style', 'header') : 'header')
@if ($hasLabel)
<figure @class([
    'hyde-code-block my-4 [&>pre]:my-0',
    'overflow-hidden rounded-lg [&>pre]:rounded-none' => $labelStyle === 'header',
    'relative' => $labelStyle === 'badge',
])>
@if ($labelStyle === 'header')
<figcaption class="hyde-code-block-label not-prose bg-[color:var(--tw-prose-pre-bg)] px-4 py-2.5 font-mono text-xs leading-normal text-[color:var(--tw-prose-pre-code)] [overflow-wrap:anywhere]">{{ $label }}</figcaption>
@else
<figcaption class="hyde-code-block-label not-prose absolute right-4 top-3 z-10 hidden font-mono text-xs text-[color:var(--tw-prose-pre-code)] opacity-50 transition-opacity duration-250 hover:opacity-100 md:block">{{ $label }}</figcaption>
@endif
{!! $contents !!}
</figure>
@else
<div class="hyde-code-block relative my-4 [&>pre]:my-0">
{!! $contents !!}
</div>
@endif
