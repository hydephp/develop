@php /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */ @endphp
<ul id="sidebar-navigation-items" role="list">
    @foreach ($sidebar->getGroups() as $group)
        <li class="sidebar-navigation-group" role="listitem">
            <header class="sidebar-navigation-group-header p-2 px-4 -ml-2 flex justify-between items-center">
                <h4 class="sidebar-navigation-group-heading text-base font-semibold">{{ Hyde::makeTitle($group) }}</h4>
            </header>
            <ul class="sidebar-navigation-group-list ml-4 px-2 mb-2" role="list">
                @foreach ($sidebar->getItemsInGroup($group) as $item)
                    @include('hyde::components.docs.grouped-sidebar-item')
                @endforeach
            </ul>
        </li>
    @endforeach
</ul>