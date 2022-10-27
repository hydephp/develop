<aside id="sidebar" x-cloak :class="sidebarOpen ? 'visible left-0' : 'invisible -left-64 md:visible md:left-0'"
       class="bg-gray-100 dark:bg-gray-800 dark:text-gray-200 h-screen w-64 fixed z-30 md:block shadow-lg md:shadow-none transition-all duration-300">
    <header id="sidebar-header" class="h-16">
        @include('hyde::components.docs.sidebar-brand')
    </header>
    <nav id="sidebar-navigation"
         class="p-4 overflow-y-auto border-y border-gray-300 dark:border-[#1b2533] h-[calc(100vh_-_8rem)]">
        @php
            $sidebar = \Hyde\Navigation\DocumentationSidebar::create();
        @endphp

        @if($sidebar->hasGroups())
            @include('hyde::components.docs.grouped-sidebar-navigation')
        @else
            @include('hyde::components.docs.sidebar-navigation')
        @endif
    </nav>
    <footer id="sidebar-footer" class="h-16 absolute p-4 w-full bottom-0 left-0 text-center leading-8">
        @include('hyde::components.docs.sidebar-footer')
    </footer>
</aside>