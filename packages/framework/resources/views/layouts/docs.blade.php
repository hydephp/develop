<!DOCTYPE html>
<html lang="{{ config('hyde.language', 'en') }}">
<head>
    @include('hyde::layouts.head')
</head>
	
<body id="lagrafo-app" class="bg-white dark:bg-gray-900">
	<a href="#content" id="skip-to-content">Skip to content</a>
	
	<script>
		document.body.classList.add('js-enabled');
	</script>

	<nav id="mobile-navigation">
		<strong class="mr-auto">
			@if(Hyde::docsIndexPath() !== false)
			<a href="{{ Hyde::relativeLink(Hyde::docsIndexPath(), $currentPage) }}">
				{{ config('docs.header_title', 'Documentation') }}
			</a>
			@else
			{{ config('docs.header_title', 'Documentation') }}
			@endif
		</strong>
        @include('hyde::components.navigation.theme-toggle-button')
		<button id="sidebar-toggle" title="Toggle sidebar" aria-label="Toggle sidebar navigation menu">
			<span class="icon-bar" role="presentation"></span>
			<span class="icon-bar" role="presentation"></span>
			<span class="icon-bar" role="presentation"></span>
			<span class="icon-bar" role="presentation"></span>
		</button>
	</nav>
	<aside id="sidebar">
		<header id="sidebar-header">
			<div id="sidebar-brand">
				<strong>
					@if(Hyde::docsIndexPath() !== false)
					<a href="{{ Hyde::relativeLink(Hyde::docsIndexPath(), $currentPage) }}">
						{{ config('docs.header_title', 'Documentation') }}
					</a>
					@else
					{{ config('docs.header_title', 'Documentation') }}
					@endif
				</strong>
				@include('hyde::components.navigation.theme-toggle-button')
			</div>
		</header>
		<nav id="sidebar-navigation">
			@php
				$sidebar = Hyde\Framework\Services\DocumentationSidebarService::create();
			@endphp

			@if($sidebar->hasCategories())
			@include('hyde::components.docs.labeled-sidebar-navigation-menu')
			@else
			@include('hyde::components.docs.sidebar-navigation-menu')
			@endif
		</nav>
		<footer id="sidebar-footer">
			<p>
				<a href="{{ Hyde::relativeLink('index.html', $currentPage) }}">Back to home page</a>
			</p>
		</footer>
	</aside>
	<main id="content">
		@php
		$document = \Hyde\Framework\Services\HydeSmartDocs::create($page, $markdown);
		@endphp
		<article id="document" itemscope itemtype="http://schema.org/Article" @class(['mx-auto lg:ml-8 prose dark:prose-invert
			max-w-3xl', 'torchlight-enabled'=> $document->hasTorchlight()])>
			
			@yield('content')

			<header id="document-header">
				{!! $document->renderHeader() !!}
			</header>
			<section id="document-main-content" itemprop="articleBody">
				{!! $document->renderBody() !!}
			</section>
			<footer id="document-footer">
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

	@include('hyde::layouts.scripts')
</body>
</html>