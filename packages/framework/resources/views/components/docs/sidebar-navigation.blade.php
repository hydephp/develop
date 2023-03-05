@php /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */ @endphp
<ul id="sidebar-navigation-items" role="list" class="pl-2">
    @foreach ($sidebar->items as $item)
        @include('hyde::components.docs.sidebar-item')
    @endforeach
</ul>