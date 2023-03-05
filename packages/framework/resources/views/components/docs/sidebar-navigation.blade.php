@php /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */ @endphp
@props(['grouped' => false])
@if(! $grouped)
<ul id="sidebar-navigation-items" role="list" class="pl-2">
    @foreach ($sidebar->items as $item)
        @include('hyde::components.docs.sidebar-item')
    @endforeach
</ul>
@endif