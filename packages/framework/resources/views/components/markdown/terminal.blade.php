<figure class="hyde-terminal not-prose my-4 overflow-hidden rounded-md bg-[#292D3E] text-[#A6ACCD]">
    @if (($title ?? 'Terminal') !== '')
        <figcaption class="hyde-terminal-header bg-[#212529] px-4 py-2.5 font-sans text-xs leading-none">
            <span>{{ $title ?? 'Terminal' }}</span>
        </figcaption>
    @endif
    <pre class="hyde-terminal-body m-0 overflow-x-auto rounded-none bg-[#292D3E] p-4 text-[#A6ACCD]"><code class="block whitespace-pre font-mono text-sm leading-relaxed">{!! $contents !!}</code></pre>
</figure>
