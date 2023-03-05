@php /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */ @endphp
<ul id="sidebar-navigation-items" role="list">
    @php ($collapsible = config('docs.sidebar.collapsible', true))
    @foreach ($sidebar->getGroups() as $group)
        <li class="sidebar-navigation-group" role="listitem" @if($collapsible) x-data="{ groupOpen: {{ $sidebar->isGroupActive($group) ? 'true' : 'false' }} }" @endif>
            <header class="sidebar-navigation-group-header p-2 px-4 -ml-2 flex justify-between items-center @if($collapsible) group hover:bg-black/10 @endif" @if($collapsible) @click="groupOpen = ! groupOpen" @endif>
                <h4 class="sidebar-navigation-group-heading text-base font-semibold @if($collapsible) cursor-pointer dark:group-hover:text-white @endif">{{ Hyde::makeTitle($group) }}</h4>
                @if($collapsible)
                    @include('hyde::components.docs.sidebar-navigation-group-toggle-button')
                @endif
            </header>
            <ul class="sidebar-navigation-group-items ml-4 px-2 mb-2" role="list" @if($collapsible) x-show="groupOpen" @endif>
                @foreach ($sidebar->getItemsInGroup($group) as $item)
                    @include('hyde::components.docs.sidebar-item', ['grouped' => true])
                @endforeach
            </ul>
        </li>
    @endforeach
</ul>