@php /** @var \Hyde\Framework\Features\Navigation\NavItem $item */ @endphp
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