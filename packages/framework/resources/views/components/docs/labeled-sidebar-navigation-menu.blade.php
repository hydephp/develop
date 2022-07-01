<ul id="sidebar-navigation-menu" role="list">
	@foreach ($sidebar->getCategories() as $category)
	<li class="sidebar-category" role="listitem">
		<h4 class="sidebar-category-heading">{{ Hyde::makeTitle($category) }}</h4>
		<ul class="sidebar-category-list" role="list">
			@foreach ($sidebar->getItemsInCategory($category) as $item)
			<li @class([ 'sidebar-navigation-item' , 'active'=> $item->destination === basename($currentPage)]) role="listitem">
				@if($item->destination === basename($currentPage))
				<a href="{{ Hyde::pageLink($item->destination . '.html') }}" aria-current="true">{{ $item->label }}</a>
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
	</li>
	@endforeach
</ul>
