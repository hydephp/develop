<div class="hyde-code-block relative">
@isset($label)
<small class="hyde-code-block-label not-prose absolute right-4 top-3 z-10 hidden font-mono text-xs text-[color:var(--tw-prose-pre-code)] opacity-50 transition-opacity duration-250 hover:opacity-100 md:block"><span class="sr-only">Title: </span>{{ $label }}</small>
@endisset
{!! $contents !!}
</div>
