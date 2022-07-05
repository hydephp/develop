@php
/** @var \Hyde\Framework\Modules\Navigation\NavigationMenuItemContract $item */
/** @var \Hyde\Framework\Contracts\PageContract $page */
$current = $page->getRoute() === $item->getRoute();
@endphp

<a href="{{ $item->getRoute()->getOutputFilePath() }}" {{ $current ? 'aria-current="page"' : '' }}
	@class(['block my-2 md:my-0 md:inline-block py-1 text-gray-700 hover:text-gray-900 dark:text-gray-100'
	, 'border-l-4 border-indigo-500 md:border-none font-medium -ml-6 pl-5 md:ml-0 md:pl-0 bg-gray-100 dark:bg-gray-800 md:bg-transparent dark:md:bg-transparent'=> $current
	])>
    {{ $item->navigationMenuTitle() }}
</a>