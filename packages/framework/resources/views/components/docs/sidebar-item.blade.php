@php /** @var \Hyde\Framework\Features\Navigation\NavItem $item */ @endphp
@php
    $standard_activeListClasses = 'active bg-black/5 dark:bg-black/10';
    $standard_activeItemClasses = '-ml-4 p-2 block hover:bg-black/5 dark:hover:bg-black/10 text-indigo-600 dark:text-indigo-400 dark:font-medium border-l-[0.325rem] border-indigo-500 transition-colors duration-300 ease-in-out';
    $standard_itemClasses = 'block -ml-4 p-2 border-l-[0.325rem] border-transparent hover:bg-black/5 dark:hover:bg-black/10';
    $grouped_activeListClasses = 'active -ml-8 pl-8 bg-black/5 dark:bg-black/10';
    $grouped_activeItemClasses = '-ml-8 pl-4 py-1 px-2 block text-indigo-600 dark:text-indigo-400 dark:font-medium border-l-[0.325rem] border-indigo-500 transition-colors duration-300 ease-in-out hover:bg-black/10';
    $grouped_itemClasses = '-ml-8 pl-4 py-1 px-2 block border-l-[0.325rem] border-transparent transition-colors duration-300 ease-in-out hover:bg-black/10';
@endphp
<li @class(['sidebar-navigation-item -ml-4 pl-4', $activeListClasses => $item->isCurrent()]) role="listitem">
    @if($item->isCurrent())
        <a href="{{ $item->destination }}" aria-current="true" @class([$activeItemClasses])>
            {{ $item->label }}
        </a>

        @if(config('docs.table_of_contents.enabled', true))
            <span class="sr-only">Table of contents</span>
            {!! ($page->getTableOfContents()) !!}
        @endif
    @else
        <a href="{{ $item->destination }}" @class([$itemClasses])>
            {{ $item->label }}
        </a>
    @endif
</li>