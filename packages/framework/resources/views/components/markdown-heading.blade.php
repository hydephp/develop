@props([
    'level' => 1,
    'id' => null,
    'extraAttributes' => [],
    'addPermalink' => config('markdown.permalinks.enabled', true),
])

@php
    $tag = 'h' . $level;
    $id = $id ?? \Illuminate\Support\Str::slug($slot);
@endphp

<{{ $tag }} {{ $attributes->merge(['id' => $id, ...$extraAttributes]) }}>
    {!! $slot !!}
    @if($addPermalink === true)
        <a href="#{{ $id }}" class="heading-permalink" title="Permalink"></a>
    @endif
</{{ $tag }}> 