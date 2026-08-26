@php($hasLabel = isset($label) && (string) $label !== '')
@if ($hasLabel)
<figure class="hyde-code-block my-4 [&>pre]:my-0 overflow-hidden rounded-lg [&>pre]:rounded-none">
<figcaption class="hyde-code-block-label not-prose bg-[#212529] px-4 py-2.5 font-sans text-xs leading-none text-[#A6ACCD] [overflow-wrap:anywhere]">{{ $label }}</figcaption>
{!! $contents !!}
</figure>
@else
<div class="hyde-code-block my-4 [&>pre]:my-0">
{!! $contents !!}
</div>
@endif
