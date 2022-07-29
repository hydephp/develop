<ul id="sidebar-navigation-menu" role="list">
	@foreach ($sidebar->getGroups() as $group)
	<li class="sidebar-category mb-4 mt-4 first:mt-0" role="listitem">
		<h4 class="sidebar-category-heading text-base font-semibold mb-2 -ml-1">{{ Hyde::makeTitle($group ?? 'Other') }}</h4>
		<ul class="sidebar-category-list ml-4" role="list">
			@foreach ($sidebar->getItemsInGroup($group) as $item)
				<x-hyde::docs.grouped-sidebar-item :item="$item" :active="$item->route->getRouteKey() === $currentRoute->getRouteKey()" />
			@endforeach
		</ul>
	</li>
	@endforeach
</ul>
