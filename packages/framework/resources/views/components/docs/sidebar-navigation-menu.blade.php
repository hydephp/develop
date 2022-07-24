<ul id="sidebar-navigation-menu" role="list">
	@foreach ($sidebar->getSortedSidebar() as $item)
	<li @class(['sidebar-navigation-item -ml-4 pl-4' , 'active bg-darken'=> $item->destination === basename($currentPage)])>
		@if($item->destination === basename($currentPage))
		<a href="{{ Hyde::pageLink($item->destination . '.html') }}" aria-current="true" class="-ml-4 p-2 block font-medium text-indigo-400 border-l-[0.325rem] border-indigo-500 transition-colors duration-300	ease-in-out">{{
			$item->label }}</a>

		@if(isset($page->tableOfContents))
		<span class="sr-only">Table of contents</span>
		{!! ($page->tableOfContents) !!}
		@endif
		@else
		<a href="{{ Hyde::pageLink($item->destination . '.html') }}" class="block -ml-4 p-2 border-l-[0.325rem] border-transparent">{{ $item->label }}</a>
		@endif
	</li>
	@endforeach
</ul>