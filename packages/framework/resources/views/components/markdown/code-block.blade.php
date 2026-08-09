@php($hasLabel = isset($label) && (string) $label !== '')
@php($labelStyle = $hasLabel ? \Hyde\Facades\Config::getString('markdown.code_block_label_style', 'header') : 'header')
<div @class([
    'hyde-code-block my-4 [&>pre]:my-0',
    'overflow-hidden rounded-lg [&>pre]:rounded-none' => $hasLabel && $labelStyle === 'header',
    'relative' => ! $hasLabel || $labelStyle === 'badge',
])>
@if ($hasLabel && $labelStyle === 'header')
<header class="hyde-code-block-label not-prose bg-[color:var(--tw-prose-pre-bg)] px-4 py-2.5 font-mono text-xs leading-normal text-[color:var(--tw-prose-pre-code)] [overflow-wrap:anywhere]"><span class="sr-only">Title: </span>{{ $label }}</header>
@elseif ($hasLabel)
<small class="hyde-code-block-label not-prose absolute right-4 top-3 z-10 hidden font-mono text-xs text-[color:var(--tw-prose-pre-code)] opacity-50 transition-opacity duration-250 hover:opacity-100 md:block"><span class="sr-only">Title: </span>{{ $label }}</small>
@endif
{!! $contents !!}
</div>
