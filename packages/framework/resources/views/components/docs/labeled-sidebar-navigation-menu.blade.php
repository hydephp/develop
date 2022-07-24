<ul id="sidebar-navigation-menu" role="list">
	@foreach ($sidebar->getCategories() as $category)
	<li class="sidebar-category mb-4 mt-4 first:mt-0" role="listitem">
		<h4 class="sidebar-category-heading text-base font-semibold mb-2 -ml-1">{{ Hyde::makeTitle($category) }}</h4>
		<ul class="sidebar-category-list ml-4" role="list">
			@foreach ($sidebar->getItemsInCategory($category) as $item)
				<x-hyde::docs.labeled-sidebar-navigation-menu-item :item="$item" :active="$item->destination === basename($currentPage)" />
			@endforeach
		</ul>
	</li>
	@endforeach
</ul>
