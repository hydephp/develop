@props([
'item',
'active' => false,
])

<li @class(['sidebar-navigation-item -ml-4 pl-4', 'active -ml-8 pl-8 bg-black/5 dark:bg-black/10'=> $active]) role="listitem">
	@if(! $active)
	<a class="-ml-8 pl-4 py-1 px-2 block border-l-[0.325rem] border-transparent transition-colors duration-300 ease-in-out hover:bg-black/10"
		href="{{ $item->route }}">{{ $item->label }}</a>
	@else
	<a class="-ml-8 pl-4 py-1 px-2 block text-indigo-600 dark:text-indigo-400 dark:font-medium border-l-[0.325rem] border-indigo-500 transition-colors duration-300 ease-in-out hover:bg-black/10"
		href="{{ $item->route }}" aria-current="true">{{ $item->label }}</a>
		@if(config('docs.table_of_contents.enabled', true))
		<span class="sr-only">Table of contents</span>
		{!! ($page->getTableOfContents()) !!}
		@endif
	@endif
</li>