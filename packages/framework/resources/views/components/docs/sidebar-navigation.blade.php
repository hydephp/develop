<ul id="sidebar-navigation" role="list">
	@foreach ($sidebar->getItems() as $item)
	<li @class(['sidebar-navigation-item -ml-4 pl-4' , 'active bg-black/5 dark:bg-black/10'=> $item->route->getRouteKey() === $currentRoute->getRouteKey()])>
		@if($item->route->getRouteKey() === $currentRoute->getRouteKey())
			<a href="{{ $item->route }}" aria-current="true" class="-ml-4 p-2 block hover:bg-black/5 dark:hover:bg-black/10  text-indigo-600 dark:text-indigo-400 dark:font-medium border-l-[0.325rem] border-indigo-500 transition-colors duration-300	ease-in-out">
				{{ $item->label }}
			</a>

			@if(config('docs.table_of_contents.enabled', true))
				<span class="sr-only">Table of contents</span>
				{!! ($page->getTableOfContents()) !!}
			@endif
		@else
			<a href="{{ $item->route }}" class="block -ml-4 p-2 border-l-[0.325rem] border-transparent hover:bg-black/5 dark:hover:bg-black/10">{{ $item->label }}</a>
		@endif
	</li>
	@endforeach
</ul>