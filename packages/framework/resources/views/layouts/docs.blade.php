<!DOCTYPE html>
<html lang="{{ config('site.language', 'en') }}">
<head>
    @include('hyde::layouts.head')
</head>
<body id="hyde-docs"
      class="bg-white dark:bg-gray-900 dark:text-white min-h-screen w-screen relative overflow-x-hidden overflow-y-auto"
      x-data="{ sidebarOpen: false, searchWindowOpen: false }"
      x-on:keydown.escape="searchWindowOpen = false; sidebarOpen = false" x-on:keydown.slash="searchWindowOpen = true">

@include('hyde::components.skip-to-content-button')

@include('hyde::components.docs.mobile-navigation')
@include('hyde::components.docs.sidebar')

<main id="content"
      class="dark:bg-gray-900 min-h-screen bg-gray-50 md:bg-white absolute top-16 md:top-0 w-screen md:left-64 md:w-[calc(100vw_-_16rem)]">
    <x-hyde::docs.documentation-article
            :document="\Hyde\Framework\Services\SemanticDocumentationArticle::create($page, $markdown)"/>
</main>

<div id="support">
    @include('hyde::components.docs.sidebar-backdrop')

    @if(Hyde\Facades\Features::hasDocumentationSearch())
        @include('hyde::components.docs.search-widget')
        @include('hyde::components.docs.search-scripts')
    @endif
</div>

@include('hyde::layouts.scripts')
</body>
</html>
