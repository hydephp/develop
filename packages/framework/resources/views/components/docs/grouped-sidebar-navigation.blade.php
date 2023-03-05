@php /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */ @endphp
<ul id="sidebar-navigation-items" role="list">
    @foreach ($sidebar->getGroups() as $group)
        <li class="sidebar-navigation-group" role="listitem">
            <header class="sidebar-navigation-group-header p-2 px-4 -ml-2 flex justify-between items-center">
                <h4 class="sidebar-navigation-group-heading text-base font-semibold">{{ Hyde::makeTitle($group) }}</h4>
            </header>
            <ul class="sidebar-navigation-group-list ml-4 px-2 mb-2" role="list">
                @foreach ($sidebar->getItemsInGroup($group) as $item)
                    @include('hyde::components.docs.grouped-sidebar-item', [
                        'activeListClasses' => 'active -ml-8 pl-8 bg-black/5 dark:bg-black/10',
                        'activeItemClasses' => '-ml-8 pl-4 py-1 px-2 block text-indigo-600 dark:text-indigo-400 dark:font-medium border-l-[0.325rem] border-indigo-500 transition-colors duration-300 ease-in-out hover:bg-black/10',
                        'itemClasses' => '-ml-8 pl-4 py-1 px-2 block border-l-[0.325rem] border-transparent transition-colors duration-300 ease-in-out hover:bg-black/10',
                    ])
                @endforeach
            </ul>
        </li>
    @endforeach
</ul>