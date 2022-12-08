@php/** @var \Hyde\Framework\Features\Navigation\DocumentationSidebar $sidebar */@endphp
<ul id="sidebar-navigation" role="list">
	@foreach ($sidebar->getGroups() as $group)
	<li class="sidebar-group mb-4 mt-4 first:mt-0" role="listitem">
		<h4 class="sidebar-group-heading text-base font-semibold mb-2 -ml-1">{{ Hyde::makeTitle($group) }}</h4>
		<ul class="sidebar-group-list ml-4" role="list">
			@foreach ($sidebar->getItemsInGroup($group) as $item)
				<x-hyde::docs.grouped-sidebar-item :item="$item" :active="$item->route->getRouteKey() === $currentRoute->getRouteKey()" />
			@endforeach
		</ul>
	</li>
	@endforeach
</ul>