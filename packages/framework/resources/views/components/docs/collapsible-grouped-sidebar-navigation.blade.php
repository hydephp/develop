@php /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */ @endphp
<ul id="sidebar-navigation-items" role="list">
	@foreach ($sidebar->getGroups() as $group)
        <li class="sidebar-group" role="listitem" x-data="{ groupOpen: {{ $sidebar->isGroupActive($group) ? 'true' : 'false' }} }">
            <header class="sidebar-group-header p-2 px-4 -ml-2 flex justify-between items-center group hover:bg-black/10" @click="groupOpen = ! groupOpen">
                <h4 class="sidebar-group-heading text-base font-semibold cursor-pointer dark:group-hover:text-white">{{ Hyde::makeTitle($group) }}</h4>
                <button class="sidebar-group-toggle opacity-50 group-hover:opacity-100">
                    <svg class="sidebar-group-toggle-icon sidebar-group-toggle-icon-open" x-show="groupOpen" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 12L12 8L4 8L8 12Z" fill="currentColor" />
                    </svg>
                    <svg class="sidebar-group-toggle-icon sidebar-group-toggle-icon-closed" x-show="! groupOpen" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8L8 12L8 4L12 8Z" fill="currentColor" />
                    </svg>
                </button>
            </header>
            <ul class="sidebar-group-list ml-4 px-2 mb-2" role="list" x-show="groupOpen">
                @foreach ($sidebar->getItemsInGroup($group) as $item)
                    <x-hyde::docs.grouped-sidebar-item :item="$item" :active="$item->route->getRouteKey() === $currentRoute->getRouteKey()" />
                @endforeach
            </ul>
        </li>
	@endforeach
</ul>