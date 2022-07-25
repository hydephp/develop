<!DOCTYPE html>
<html id="lagrafo-app" lang="{{ config('site.language', 'en') }}">
<head>
	@include('hyde::layouts.head')
	<style> html [id], body [id] { scroll-margin: 1rem; } </style>
</head>

<body id="hyde-docs"
	class="bg-white dark:bg-gray-900 dark:text-white min-h-screen w-screen relative overflow-x-hidden overflow-y-auto"
	x-data="{ sidebarOpen: false, searchWindowOpen: false }"
	x-on:keydown.escape="searchWindowOpen = false; sidebarOpen = false" x-on:keydown.slash="searchWindowOpen = true">
	@include('hyde::components.skip-to-content-button')

	<script>
		document.body.classList.add('js-enabled');
	</script>

	<nav id="mobile-navigation"
		class="bg-white dark:bg-gray-800 md:hidden flex justify-between w-full h-16 z-40 fixed left-0 top-0 p-4 leading-8 shadow-lg">
		<strong class="px-2 mr-auto">
			@if(DocumentationPage::indexPath() !== false)
			<a href="{{ Hyde::relativeLink(DocumentationPage::indexPath(), $currentPage) }}">
				{{ config('docs.header_title', 'Documentation') }}
			</a>
			@else
			{{ config('docs.header_title', 'Documentation') }}
			@endif
		</strong>
		<ul class="flex items-center">
			<li class="h-8 flex mr-1">
				<x-hyde::navigation.theme-toggle-button class="opacity-75 hover:opacity-100" />
			</li>
			<li class="h-8 flex">
				<button id="sidebar-toggle" title="Toggle sidebar" aria-label="Toggle sidebar navigation menu"
					@click="sidebarOpen = ! sidebarOpen">
					<span class="icon-bar dark:bg-white h-0" role="presentation"></span>
					<span class="icon-bar dark:bg-white h-0" role="presentation"></span>
					<span class="icon-bar dark:bg-white h-0" role="presentation"></span>
					<span class="icon-bar dark:bg-white h-0" role="presentation"></span>
				</button>
			</li>
		</ul>
	</nav>
	<aside id="sidebar"
		class="bg-gray-100 dark:bg-gray-800 dark:text-gray-200 h-screen w-64 fixed z-30 md:block shadow-lg md:shadow-none transition-all duration-300"
		:class="sidebarOpen ? 'visible left-0' : 'invisible -left-64 md:visible md:left-0'" x-cloak>
		<header id="sidebar-header" class="h-16">
			<div id="sidebar-brand" class="flex items-center justify-between h-16 py-4 px-2">
				<strong class="px-2">
					@if(DocumentationPage::indexPath() !== false)
					<a href="{{ Hyde::relativeLink(DocumentationPage::indexPath(), $currentPage) }}">
						{{ config('docs.header_title', 'Documentation') }}
					</a>
					@else
					{{ config('docs.header_title', 'Documentation') }}
					@endif
				</strong>
				<x-hyde::navigation.theme-toggle-button class="opacity-75 hover:opacity-100" />
			</div>
		</header>
		<nav id="sidebar-navigation"
			class="p-4 overflow-y-auto border-y border-gray-300 dark:border-[#1b2533] h-[calc(100vh_-_8rem)]">
			@php
				$sidebar = Hyde\Framework\Services\DocumentationSidebarService::create();
			@endphp

			@if($sidebar->hasCategories())
				@include('hyde::components.docs.labeled-sidebar-navigation-menu')
			@else
				@include('hyde::components.docs.sidebar-navigation-menu')
			@endif
		</nav>
		<footer id="sidebar-footer" class="h-16 absolute p-4 w-full bottom-0 left-0 text-center leading-8">
			<p>
				<a href="{{ Hyde::relativeLink('index.html', $currentPage) }}">Back to home page</a>
			</p>
		</footer>
	</aside>
	<main id="content"
		class="dark:bg-gray-900 min-h-screen bg-gray-50 md:bg-white absolute top-16 md:top-0 w-screen md:left-64 md:w-[calc(100vw_-_16rem)]">

		@php
			$document = \Hyde\Framework\Services\HydeSmartDocs::create($page, $markdown);
		@endphp
		<article id="document" itemscope itemtype="http://schema.org/Article" @class(['mx-auto lg:ml-8 prose
			dark:prose-invert max-w-3xl p-12 md:px-16 max-w-[1000px] min-h-[calc(100vh_-_4rem)]', 'torchlight-enabled'=>
			$document->hasTorchlight()])>
			@yield('content')

			<header id="document-header" class="flex items-center flex-wrap">
				{!! $document->renderHeader() !!}
			</header>
			<section id="document-main-content" itemprop="articleBody">
				{!! $document->renderBody() !!}
			</section>
			<footer id="document-footer" class="flex items-center flex-wrap mt-8 justify-between text-[90%]">
				{!! $document->renderFooter() !!}
			</footer>
		</article>
	</main>

	@if(Hyde\Framework\Helpers\Features::hasDocumentationSearch())
		@include('hyde::components.docs.search')
		<script src="https://cdn.jsdelivr.net/npm/hydesearch@0.2.1/dist/HydeSearch.min.js" defer></script>
		<script>
			window.addEventListener('load', function() {
				const searchIndexLocation = 'search.json';
				const Search = new HydeSearch(searchIndexLocation);

				Search.init();
			});
		</script>
	@endif

	<div id="support">
		<div id="sidebar-backdrop" x-show="sidebarOpen" x-transition @click="sidebarOpen = false"
			title="Click to close sidebar" class="w-screen h-screen fixed top-0 left-0 cursor-pointer z-10 bg-black/50">
		</div>
		<div id="search-window-backdrop" x-show="searchWindowOpen" @click="searchWindowOpen = false"
			title="Click to close search window"
			class="w-screen h-screen fixed top-0 left-0 cursor-pointer z-40 bg-black/50"></div>
	</div>

	@include('hyde::layouts.scripts')
</body>
</html>