<!DOCTYPE html>
<html lang="{{ config('site.language', 'en') }}">
    <head>
        @include('hyde::layouts.head')
    </head>
    <body id="hyde-docs" class="bg-white dark:bg-gray-900 dark:text-white min-h-screen w-screen relative overflow-x-hidden overflow-y-auto"
          x-data="{ sidebarOpen: false, searchWindowOpen: false }"
          x-on:keydown.escape="searchWindowOpen = false; sidebarOpen = false" x-on:keydown.slash="searchWindowOpen = true">

        @include('hyde::components.skip-to-content-button')

        <script>
            document.body.classList.add('js-enabled');
        </script>

        <nav id="mobile-navigation"
             class="bg-white dark:bg-gray-800 md:hidden flex justify-between w-full h-16 z-40 fixed left-0 top-0 p-4 leading-8 shadow-lg">
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
        <x-hyde::docs.documentation-sidebar />
        <main id="content" class="dark:bg-gray-900 min-h-screen bg-gray-50 md:bg-white absolute top-16 md:top-0 w-screen md:left-64 md:w-[calc(100vw_-_16rem)]">
            <x-hyde::docs.documentation-article :document="\Hyde\Framework\Services\HydeSmartDocs::create($page, $markdown)"/>
        </main>

        <div id="support">
            <div id="sidebar-backdrop" x-show="sidebarOpen" x-transition @click="sidebarOpen = false"
                 title="Click to close sidebar" class="w-screen h-screen fixed top-0 left-0 cursor-pointer z-10 bg-black/50">
            </div>
            @if(Hyde\Framework\Helpers\Features::hasDocumentationSearch())
                @include('hyde::components.docs.search-widget')
                @include('hyde::components.docs.search-scripts')
            @endif
        </div>

        @include('hyde::layouts.scripts')
    </body>
</html>