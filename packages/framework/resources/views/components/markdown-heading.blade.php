@props(['level' => 1, 'id' => null, 'addPermalink' => config('markdown.features.permalinks', true)])

@php
    $tag = 'h' . $level;
    $id = $id ?? \Illuminate\Support\Str::slug($slot);
@endphp

<{{ $tag }} {{ $attributes->merge(['id' => $id]) }}>
    {!! $slot !!}
    @if($addPermalink === true)
        <a href="#{{ $id }}" class="heading-permalink" aria-label="Permalink for this section"></a>
    @endif
</{{ $tag }}> 