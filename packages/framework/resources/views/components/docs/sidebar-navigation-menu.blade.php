<ul id="sidebar-navigation-menu" role="list">
	@foreach ($sidebar->getSortedSidebar() as $item)
	<li @class(['sidebar-navigation-item -ml-4 pl-4' , 'active bg-darken'=> $item->destination === basename($currentPage)])>
		@if($item->destination === basename($currentPage))
		<a href="{{ Hyde::pageLink($item->destination . '.html') }}" aria-current="true">{{
			$item->label }}</a>

		@if(isset($page->tableOfContents))
		<span class="sr-only">Table of contents</span>
		{!! ($page->tableOfContents) !!}
		@endif
		@else
		<a href="{{ Hyde::pageLink($item->destination . '.html') }}">{{ $item->label }}</a>
		@endif
	</li>
	@endforeach
</ul>