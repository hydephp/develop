@props([
'item',
'active' => false,
])

<li @class([ 'sidebar-navigation-item -ml-4 pl-4' , 'active -ml-8 pl-8 bg-darken'=> $active]) {!! $active ? '' : '' !!} role="listitem">
	@if(! $active)
	<a class="-ml-8 pl-4 py-1 px-2  block  border-l-[0.325rem] border-transparent transition-colors duration-300	ease-in-out hover:bg-darken	"
		href="{{ Hyde::pageLink($item->destination . '.html') }}">{{ $item->label }}</a>
	@else
	<a class="-ml-8 pl-4 py-1 px-2  block  text-indigo-600 border-l-[0.325rem] border-indigo-500 transition-colors duration-300	ease-in-out	"
		href="{{ Hyde::pageLink($item->destination . '.html') }}" aria-current="true">{{ $item->label }}</a>

	@isset($page->tableOfContents)
	<span class="sr-only">Table of contents</span>
	{!! ($page->tableOfContents) !!}
	@endif
	@endif
</li>