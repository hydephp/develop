@php /** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */ @endphp
<ul id="sidebar-navigation-items" role="list" class="pl-2">
    @foreach ($sidebar->items as $item)
        @include('hyde::components.docs.sidebar-item', [
            'activeListClasses' => 'active -ml-8 pl-8 bg-black/5 dark:bg-black/10',
            'activeItemClasses' => '-ml-8 pl-4 py-1 px-2 block text-indigo-600 dark:text-indigo-400 dark:font-medium border-l-[0.325rem] border-indigo-500 transition-colors duration-300 ease-in-out hover:bg-black/10',
            'itemClasses' => '-ml-8 pl-4 py-1 px-2 block border-l-[0.325rem] border-transparent transition-colors duration-300 ease-in-out hover:bg-black/10',
        ])
    @endforeach
</ul>