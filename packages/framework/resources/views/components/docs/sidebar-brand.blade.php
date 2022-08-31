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