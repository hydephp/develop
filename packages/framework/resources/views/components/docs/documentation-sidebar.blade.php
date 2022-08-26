<aside id="sidebar" x-cloak :class="sidebarOpen ? 'visible left-0' : 'invisible -left-64 md:visible md:left-0'"
       class="bg-gray-100 dark:bg-gray-800 dark:text-gray-200 h-screen w-64 fixed z-30 md:block shadow-lg md:shadow-none transition-all duration-300">
    <header id="sidebar-header" class="h-16">
        <div id="sidebar-brand" class="flex items-center justify-between h-16 py-4 px-2">
            <strong class="px-2">
                @if(DocumentationPage::home() !== null)
                    <a href="{{ DocumentationPage::home() }}">
                        {{ config('docs.header_title', 'Documentation') }}
                    </a>
                @else
                    {{ config('docs.header_title', 'Documentation') }}
                @endif
            </strong>
            <x-hyde::navigation.theme-toggle-button class="opacity-75 hover:opacity-100"/>
        </div>
    </header>
    <nav id="sidebar-navigation"
         class="p-4 overflow-y-auto border-y border-gray-300 dark:border-[#1b2533] h-[calc(100vh_-_8rem)]">
        @php
            $sidebar = \Hyde\Framework\Models\DocumentationSidebar::create();
        @endphp

        @if($sidebar->hasGroups())
            @include('hyde::components.docs.grouped-sidebar')
        @else
            @include('hyde::components.docs.sidebar')
        @endif
    </nav>
    <footer id="sidebar-footer" class="h-16 absolute p-4 w-full bottom-0 left-0 text-center leading-8">
        <p>
            <a href="{{ Hyde::relativeLink('index.html') }}">Back to home page</a>
        </p>
    </footer>
</aside>