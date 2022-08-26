<nav id="mobile-navigation" class="bg-white dark:bg-gray-800 md:hidden flex justify-between w-full h-16 z-40 fixed left-0 top-0 p-4 leading-8 shadow-lg">
    <strong class="px-2 mr-auto">
        @if(DocumentationPage::home() !== null)
            <a href="{{ DocumentationPage::home() }}">
                {{ config('docs.header_title', 'Documentation') }}
            </a>
        @else
            {{ config('docs.header_title', 'Documentation') }}
        @endif
    </strong>
    <ul class="flex items-center">
        <li class="h-8 flex mr-1">
            <x-hyde::navigation.theme-toggle-button class="opacity-75 hover:opacity-100"/>
        </li>
        <li class="h-8 flex">
            <button id="sidebar-toggle" title="Toggle sidebar" aria-label="Toggle sidebar navigation menu"
                    @click="sidebarOpen = ! sidebarOpen" :class="{'active' : sidebarOpen}">
                <span class="icon-bar dark:bg-white h-0" role="presentation"></span>
                <span class="icon-bar dark:bg-white h-0" role="presentation"></span>
                <span class="icon-bar dark:bg-white h-0" role="presentation"></span>
                <span class="icon-bar dark:bg-white h-0" role="presentation"></span>
            </button>
        </li>
    </ul>
</nav>