@php /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */ @endphp
<ul id="sidebar-navigation-items" role="list">
    @foreach ($sidebar->getGroups() as $group)
        <li class="sidebar-group" role="listitem">
            <header class="sidebar-group-header p-2 px-4 -ml-2 flex justify-between items-center">
                <h4 class="sidebar-group-heading text-base font-semibold">{{ Hyde::makeTitle($group) }}</h4>
            </header>
            <ul class="sidebar-group-list ml-4 px-2 mb-2" role="list">
                @foreach ($sidebar->getItemsInGroup($group) as $item)
                    <x-hyde::docs.grouped-sidebar-item :item="$item" :active="$item->route->getRouteKey() === $currentRoute->getRouteKey()" />
                @endforeach
            </ul>
        </li>
    @endforeach
</ul>