<ul id="sidebar-navigation-menu" role="list">
	@foreach ($sidebar->getGroups() as $group)
	<li class="sidebar-category mb-4 mt-4 first:mt-0" role="listitem">
		<h4 class="sidebar-category-heading text-base font-semibold mb-2 -ml-1">{{ Hyde::makeTitle($group ?? 'Other') }}</h4>
		<ul class="sidebar-category-list ml-4" role="list">
				<x-hyde::docs.labeled-sidebar-navigation-menu-item :item="$item" :active="$item->destination === basename($currentPage)" />
			@foreach ($sidebar->getItemsInGroup($group) as $item)
			@endforeach
		</ul>
	</li>
	@endforeach
</ul>
